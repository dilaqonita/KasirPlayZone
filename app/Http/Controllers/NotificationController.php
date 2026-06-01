<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;
use App\Models\ActivityLog;

class NotificationController extends Controller
{
    const NGROK_BASE = 'https://sixties-pout-envoy.ngrok-free.dev/api';

    const NGROK_HDRS = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'ngrok-skip-browser-warning' => 'true',
    ];

public function index()
{
    $this->generateNotifications();

    $notifs = Notification::where('is_read', false)
        ->orderBy('time_ts', 'desc')
        ->take(10)
        ->get();

    return response()->json([
        'notifications' => $notifs,
        'unread_count' => $notifs->count(),
    ]);
}
public function markRead($id)
{
    Notification::where('_id', $id)->update([
        'is_read' => true
    ]);

    return response()->json([
        'success' => true
    ]);
}

public function markAllRead()
{
    $updated = Notification::where('is_read', false)
        ->update([
            'is_read' => true
        ]);

    return response()->json([
        'success' => true,
        'updated' => $updated
    ]);
}

    /* =====================================================
       GENERATE SEMUA NOTIF (PUSAT — TIDAK PERLU CONTROLLER LAIN)
       ===================================================== */
    private function generateNotifications()
    {
        $this->notifCapacity();
        $this->notifTransactions();
        $this->notifCheckin();
    }
    /* =========================
       KAPASITAS
       ========================= */
    private function notifCapacity()
    {
        try {
            $slotRes = Http::withHeaders(self::NGROK_HDRS)
                ->timeout(8)
                ->get(self::NGROK_BASE . '/slots/today');
            $slotId = $slotRes->json()['slot_id'] ?? null;
            if (!$slotId) return;
            $capRes = Http::withHeaders(self::NGROK_HDRS)
                ->timeout(8)
                ->get(self::NGROK_BASE . '/capacity/' . $slotId);
            $cap = $capRes->json();
            $maks = $cap['kapasitas_maksimal'] ?? 0;
            $pakai = $cap['kapasitas_terpakai'] ?? 0;
            $pct = $cap['persentase'] ?? 0;
            $time = now()->startOfMinute()->timestamp * 1000;
            if ($pct >= 100) {
                $this->saveOnce('capacity', 'Kapasitas Penuh', "{$pakai}/{$maks}", $time);
            } elseif ($pct >= 90) {
                $this->saveOnce('capacity', 'Kapasitas 90%', "{$pakai}/{$maks}", $time);
            } elseif ($pct >= 80) {
                $this->saveOnce('capacity', 'Kapasitas 80%', "{$pakai}/{$maks}", $time);
            }

        } catch (\Exception $e) {}
    }

    /* =========================
       TRANSAKSI
       ========================= */
    private function notifTransactions()
    {
        try {
            $trxRes = Http::withHeaders(self::NGROK_HDRS)
                ->timeout(10)
                ->get(self::NGROK_BASE . '/transactions');

            $trx = collect($trxRes->json()['data'] ?? []);

            $today = Carbon::today('Asia/Jakarta')->toDateString();

            $recent = $trx
                ->filter(function ($t) use ($today) {
                    $tgl = $t['tanggal_reservasi'] ?? null;
                    if ($tgl && strlen($tgl) > 10) $tgl = substr($tgl, 0, 10);

                    return $tgl === $today
                        && ($t['status_pembayaran'] ?? '') === 'paid';
                })
                ->sortByDesc('created_at')
                ->take(5);

            foreach ($recent as $t) {
                $time = Carbon::parse($t['created_at'])->timestamp * 1000;

                $this->saveOnce(
                    'transaction',
                    'Transaksi Baru',
                    ($t['nama_customer'] ?? 'Customer') .
                    ' - Rp ' . number_format($t['total_harga'] ?? 0),
                    $time
                );
            }

        } catch (\Exception $e) {}
    }

    /* =========================
       CHECKIN / CHECKOUT
       ========================= */
    private function notifCheckin()
    {
        try {
            $logs = ActivityLog::latest()->take(5)->get();

            foreach ($logs as $log) {

                if (!$log->customer_name || $log->customer_name === 'Guest') continue;

                $time = Carbon::parse($log->created_at)->timestamp * 1000;

                $this->saveOnce(
                    $log->type,
                    $log->type === 'checkin'
                        ? $log->customer_name . ' check-in'
                        : $log->customer_name . ' checkout',
                    Carbon::parse($log->created_at)->format('H:i') . ' WIB',
                    $time
                );
            }

        } catch (\Exception $e) {}
    }

    /* =========================
       ANTI DUPLIKAT
       ========================= */
    private function saveOnce($type, $title, $sub, $time)
    {
        $exists = Notification::where('title', $title)
            ->where('sub', $sub)
            ->where('time_ts', $time)
            ->first();

        if (!$exists) {
            Notification::create([
                'type' => $type,
                'title' => $title,
                'sub' => $sub,
                'time_ts' => $time,
                'is_read' => false,
            ]);
        }
    }
}