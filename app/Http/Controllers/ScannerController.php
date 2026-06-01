<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
class ScannerController extends Controller
{
    const NGROK_BASE = 'https://sixties-pout-envoy.ngrok-free.dev/api';
    const NGROK_HDRS = [
        'Accept'                     => 'application/json',
        'ngrok-skip-browser-warning' => 'true',
        'User-Agent'                 => 'PlayZone-Kasir/1.0',
    ];
    /* ══════════════════════════════════════
       INDEX
       ═══════════════════════════════════════ */
    public function index()
    {
        $startOfDay  = Carbon::today('Asia/Jakarta')->startOfDay();
        $endOfDay    = Carbon::today('Asia/Jakarta')->endOfDay();

        $activityLogs = ActivityLog::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->latest()
            ->take(20)
            ->get();
        return view('scanner', compact('activityLogs'));
    }
    /* ═══════════════════════════════════════
       VALIDATE — cek kode QR ke API Ngrok
       ═══════════════════════════════════════ */
public function validate(Request $request)
{
    $request->validate(['code' => 'required|string']);
    $code  = strtoupper(trim($request->code));
    $token = Session::get('api_token');
    $response = Http::withoutVerifying()
        ->withHeaders(array_merge(self::NGROK_HDRS, [
            'Authorization' => 'Bearer ' . $token,
        ]))
        ->post(self::NGROK_BASE . '/tickets/validate', [
            'kode_qr' => $code,
        ]);
    $data = $response->json();
    Log::info('VALIDATE RESPONSE:', $data ?? []);
    $message = $data['message'] ?? 'Gagal memvalidasi tiket';
    $statusPembayaran = strtolower(
        $data['data']['status_pembayaran']
        ?? $data['status_pembayaran']
        ?? ''
    );
    $statusTicket = strtolower(
        $data['data']['status_ticket']
        ?? $data['status_ticket']
        ?? ''
    );
    if (
        in_array($statusTicket, ['refund', 'refunded', 'dibatalkan', 'cancelled']) ||
        in_array($statusPembayaran, ['refund', 'refunded', 'dibatalkan', 'cancelled']) ||
        str_contains(strtolower($message), 'refund')
    ) {
        return response()->json([
            'type'    => 'invalid',
            'title'   => '❌ Tiket Sudah Refund',
            'sub'     => 'Tiket sudah direfund dan tidak bisa digunakan',
            'cls'     => 'invalid',
            'actions' => ['reset'],
        ]);
    }
    if (($data['success'] ?? false) === true) {
        return response()->json([
            'type'           => 'success',
            'title'          => '✅ Tiket Valid',
            'sub'            => $message,
            'cls'            => 'success',
            'rows'           => [
                ['Customer', $data['data']['customer'] ?? '-'],
                ['Paket',    $data['data']['paket'] ?? '-'],
                ['Status',   $statusTicket ?: 'aktif'],
            ],
            'transaction_id' => $code,
            'kode_qr'        => $code,
            'customer'       => $data['data']['customer'] ?? '-',
            'paket'          => $data['data']['paket'] ?? '-',
            'actions'        => ['checkin', 'reset'],
        ]);
    }
    return response()->json([
        'type'    => 'invalid',
        'title'   => '❌ Tiket Tidak Valid',
        'sub'     => $message,
        'cls'     => 'invalid',
        'actions' => ['reset'],
    ]);
}

   /* ═══════════════════════════════════════
   CHECKIN — simpan ke local DB
   ═══════════════════════════════════════ */
public function checkin(Request $request)
{
    $request->validate([
        'transaction_id' => 'required|string',
        'customer'       => 'required|string',
        'paket'          => 'required|string',
    ]);
    $customerName = trim($request->customer);
    if (
        empty($customerName) ||
        strtolower($customerName) === 'guest'
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Nama customer tidak valid',
        ], 422);
    }
    try {
        Checkin::create([
            'transaction_id' => $request->transaction_id,
            'kode_qr'        => $request->transaction_id,
            'customer_name'  => $customerName,
            'nama_paket'          => $request->paket,
            'waktu_masuk'    => now('Asia/Jakarta'),
        ]);
        ActivityLog::create([
            'transaction_id' => $request->transaction_id,
            'customer_name'  => $customerName,
            'package_name'   => $request->paket,
            'type'           => 'checkin',
        ]);
        return response()->json([
            'success' => true
        ]);
    } catch (\Exception $e) {
        Log::error(
            'Checkin error: ' . $e->getMessage()
        );
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


/* ═══════════════════════════════════════
   CHECKOUT — hapus dari local DB
   ═══════════════════════════════════════ */
public function checkout(Request $request)
{
    $request->validate([
        'transaction_id' => 'required|string'
    ]);
    $checkin = Checkin::where(
        'transaction_id',
        $request->transaction_id
    )->first();
    if (!$checkin) {
        return response()->json([
            'success' => false,
            'message' => 'Data checkin tidak ditemukan',
        ], 404);
    }
    try {
        ActivityLog::create([
            'transaction_id' => $checkin->transaction_id,
            'customer_name'  => $checkin->customer_name,
            'package_name'   => $checkin->nama_paket,
            'type'           => 'checkout',
        ]);
        $checkin->delete();

        return response()->json([
            'success'       => true,
            'customer_name' => $checkin->customer_name,
        ]);
    } catch (\Exception $e) {
        Log::error(
            'Checkout error: ' . $e->getMessage()
        );
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}