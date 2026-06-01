<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class WalkinController extends Controller
{
    private const NGROK_BASE = 'https://sixties-pout-envoy.ngrok-free.dev/api';

    private const HEADERS = [
        'ngrok-skip-browser-warning' => '1',
        'User-Agent' => 'Mozilla/5.0',
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    /*
    |--------------------------------------------------------------------------
    | HALAMAN WALK-IN
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {
            $token = Session::get('api_token');
            $headers = array_merge(
                self::HEADERS,
                [
                    'Authorization' => 'Bearer ' . $token
                ]
            );

            // Ambil paket
            $resPkg = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get(self::NGROK_BASE . '/packages');

            // Ambil slot hari ini
            $resSlot = Http::withHeaders($headers)
                ->withoutVerifying()
                ->timeout(15)
                ->get(self::NGROK_BASE . '/slots/today');

            $packages = $resPkg->json()['data'] ?? [];
            $slots = $resSlot->json()['sessions'] ?? [];
            $slot_id = $resSlot->json()['slot_id'] ?? null;
        } catch (\Exception $e) {
            Log::error('Gagal ambil data API: ' . $e->getMessage());

            $packages = [];
            $slots    = [];
            $slot_id  = null;
        }

        return view(
            'walkin',
            compact(
                'packages',
                'slots',
                'slot_id'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN TRANSAKSI
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            $token = Session::get('api_token');
            Log::info('REQUEST MASUK:', $request->all());

            /*
            =========================
            VALIDASI DASAR
            =========================
            */
            if (!$request->visit_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'visit_date kosong'
                ], 422);
            }

            if (!$request->details || count($request->details) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'details kosong'
                ], 422);
            }

            /*
            =========================
            FORMAT DATA
            =========================
            */
            $tanggal = $request->visit_date;
            $hari = date('l', strtotime($tanggal));

            $details = collect($request->details)
                ->map(function ($detail) {
                    return [
                        'nama_paket' => $detail['nama_paket'] ?? '',
                        'harga' => (int) ($detail['harga'] ?? 0),
                        'jumlah' => (int) ($detail['jumlah'] ?? 1),
                        'subtotal' => (int) ($detail['subtotal'] ?? 0),
                        'jam_kunjungan' => $detail['jam_kunjungan'] ?? '',
                    ];
                })
                ->toArray();

            /*
            =========================
            PAYLOAD KE API
            =========================
            */
            $payload = [
                'slot_id' => $request->slot_id,
                'nama_customer' => $request->nama_customer,
                'telepon' => $request->telepon,
                'tanggal_reservasi' => $tanggal,
                'hari_reservasi' => $hari,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'paid',
                'total_harga' => (int) $request->total_harga,
                'details' => $details,
            ];

            Log::info('TRANSACTION PAYLOAD:', $payload);

            /*
            =========================
            HIT API TRANSACTION
            =========================
            */
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'ngrok-skip-browser-warning' => '1',
            ])
                ->withoutVerifying()
                ->post(
                    self::NGROK_BASE . '/transactions/create',
                    $payload
                );

            $apiData = $res->json();
            Log::info('TRANSACTION RESPONSE:', $apiData);

            /*
            =========================
            AMBIL KODE QR 
            =========================
            */
            $ticketCodes = [];

            if (isset($apiData['data'])) {
                // Kalau data berupa object
                if (isset($apiData['data']['details'])) {
                    foreach ($apiData['data']['details'] as $detail) {
                        if (isset($detail['kode_qr'])) {
                            $ticketCodes[] = $detail['kode_qr'];
                        }
                    }
                }
                // Kalau data berupa array
                elseif (is_array($apiData['data'])) {
                    foreach ($apiData['data'] as $trx) {
                        if (isset($trx['details'])) {
                            foreach ($trx['details'] as $detail) {
                                if (isset($detail['kode_qr'])) {
                                    $ticketCodes[] = $detail['kode_qr'];
                                }
                            }
                        }
                    }
                }
            }

            /*
            =========================
            VALIDASI QR
            =========================
            */
            if (empty($ticketCodes)) {
                Log::error('QR TIDAK DITEMUKAN:', $apiData);

                return response()->json([
                    'success' => false,
                    'message' => 'Kode tiket tidak ditemukan'
                ], 422);
            }

            /*
            =========================
            RESPONSE KE FRONTEND
            =========================
            */
            return response()->json([
                'success' => true,
                'ticket_codes' => $ticketCodes,
                'qr_urls' => collect($ticketCodes)
                    ->map(function ($code) {
                        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $code;
                    })
                    ->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('STORE ERROR: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}