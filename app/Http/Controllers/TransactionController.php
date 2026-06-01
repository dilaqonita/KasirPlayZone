<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    private $apiUrl = 'https://sixties-pout-envoy.ngrok-free.dev/api/transactions';

    public function index(Request $request)
    {
        $page = 1;
        $allData = collect();
        $maxPage = 10;
        do {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'ngrok-skip-browser-warning' => 'true',
                ])
                ->timeout(15)
                ->get(
                    $this->apiUrl,
                    [
                        'page' => $page
                    ]
                );
            if (!$response->successful()) {
                break;
            }

            $json = $response->json();

            $data = $json['data'] ?? $json ?? [];

            $allData = $allData->merge($data);

            $lastPage = $json['last_page'] ?? 1;

            $page++;
        } while (
            $page <= $lastPage
            &&
            $page <= $maxPage
        );

        /*
        |--------------------------------------------------------------------------
        | MAPPING DATA PER PAKET
        |--------------------------------------------------------------------------
        */

        $transactions = $allData->flatMap(function ($tx) {
            return collect($tx['details'] ?? [])
                ->map(function ($detail) use ($tx) {
                    return (object) [
                        'id' => $tx['id'] ?? '-',
                        'transaction_id' => $detail['kode_qr'] ?? '-',
                        'user_name' => $tx['nama_customer'] ?? 'Guest',
                        'user_email' => $tx['telepon'] ?? '-',
                        'package_name' => $detail['nama_paket'] ?? '-',
                        'total' => $tx['total_harga'] ?? 0,
                        'status' => $tx['status_pembayaran'] ?? 'paid',
                        'date' => $tx['created_at'] ?? now(),
                        'hari' => $tx['hari_reservasi'] ?? '-',
                        'tanggal_reservasi' => $tx['tanggal_reservasi'] ?? '-',
                        'jam' => $detail['jam_kunjungan'] ?? '-',
                        'metode' => $tx['metode_pembayaran'] ?? '-',
                        'telepon' => $tx['telepon'] ?? '-',
                        'status_ticket' => $detail['status_ticket'] ?? '-',
                        'waktu_masuk' => $detail['waktu_masuk'] ?? null,
                    ];
                });
        });

        /*
        |--------------------------------------------------------------------------
        | FILTER 30 HARI
        |--------------------------------------------------------------------------
        */

        $oneMonthAgo = Carbon::now()->subDays(30);

        $transactions = $transactions->filter(function ($tx) use ($oneMonthAgo) {
            return Carbon::parse($tx->date) >= $oneMonthAgo;
        });

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {
            $search = strtolower($request->search);

            $transactions = $transactions->filter(function ($tx) use ($search) {
                return str_contains(
                    strtolower($tx->user_name),
                    $search
                )
                    ||
                    str_contains(
                        strtolower($tx->transaction_id),
                        $search
                    )
                    ||
                    str_contains(
                        strtolower($tx->package_name),
                        $search
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if (
            $request->status
            &&
            $request->status !== 'all'
        ) {
            $transactions = $transactions->filter(function ($tx) use ($request) {
                return strtolower($tx->status_ticket ?? '')
                    === strtolower($request->status);
            });
        }
        /*
        |--------------------------------------------------------------------------
        | FILTER PACKAGE
        |--------------------------------------------------------------------------
        */
        if (
            $request->package
            &&
            $request->package !== 'all'
        ) {
            $transactions = $transactions->filter(function ($tx) use ($request) {
                return strtolower($tx->package_name) == strtolower($request->package);
            });
        }
        /*
        |--------------------------------------------------------------------------
        | FILTER DATE
        |--------------------------------------------------------------------------
        */
        if ($request->date) {
            $transactions = $transactions->filter(function ($tx) use ($request) {
                return Carbon::parse($tx->tanggal_reservasi)
                    ->format('Y-m-d')
                    ===
                    $request->date;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | SORT TERBARU
        |--------------------------------------------------------------------------
        */

        $transactions = $transactions
            ->sortByDesc('date')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $paged = $transactions
            ->slice(
                ($page - 1) * $perPage,
                $perPage
            )
            ->values();
        $transactions = new LengthAwarePaginator(
            $paged,
            $transactions->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        $total = $transactions->total();
        /*
        |--------------------------------------------------------------------------
        | PACKAGES
        |--------------------------------------------------------------------------
        */

        $pkgResponse = Http::withoutVerifying()
            ->withHeaders([
                'Accept' => 'application/json',
                'ngrok-skip-browser-warning' => 'true',
            ])
            ->timeout(10)
            ->get('https://sixties-pout-envoy.ngrok-free.dev/api/packages');
        $packages = collect();
        if ($pkgResponse->successful()) {
            $pkgJson = $pkgResponse->json();
            $packages = collect($pkgJson['data'] ?? $pkgJson ?? []);
        }

        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */
        return view(
            'transaction',
            compact(
                'transactions',
                'total',
                'packages'
            )
        );
    }
    /*
    |--------------------------------------------------------------------------
    | DETAIL
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Accept' => 'application/json',
                'ngrok-skip-browser-warning' => 'true',
            ])
            ->get($this->apiUrl . '/' . $id);
        return response()->json($response->json());
    }
    /*
    |--------------------------------------------------------------------------
    | REFUND
    |--------------------------------------------------------------------------
    */
    public function refund(Request $request, $id)
    {
        try {
            $kodeQr = strtoupper(trim($request->kode_qr ?? $id));

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'ngrok-skip-browser-warning' => 'true',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(20)
                ->put(
                    'https://sixties-pout-envoy.ngrok-free.dev/api/transactions/refund/' . $id,
                    [
                        'kode_qr' => $kodeQr
                    ]
                );

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false) === true) {
                return response()->json([
                    'success' => true,
                    'message' => $data['message'] ?? 'Paket berhasil di-refund'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? $response->body() ?? 'Refund gagal'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

            /*
            |--------------------------------------------------------------------------
            | DEBUG RESPONSE
            |--------------------------------------------------------------------------
            */

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Refund berhasil'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? $response->body()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}