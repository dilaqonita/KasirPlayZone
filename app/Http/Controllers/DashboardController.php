<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Checkin;
use App\Models\ActivityLog;
class DashboardController extends Controller
{
    const NGROK_BASE = 'https://sixties-pout-envoy.ngrok-free.dev/api';

    const NGROK_HDRS = [
        'Accept'                     => 'application/json',
        'Content-Type'               => 'application/json',
        'ngrok-skip-browser-warning' => 'true',
        'User-Agent'                 => 'PlayZone-Kasir/1.0',
    ];
    /* =========================================================
       INDEX
       ========================================================= */
    public function index()
    {
        try {
            $slotRes  = Http::withHeaders(self::NGROK_HDRS)->timeout(10)
                ->get(self::NGROK_BASE . '/slots/today');
            $slotData = $slotRes->json();
            $slotId   = $slotData['slot_id'] ?? null;

            $dailyCapacity = 0;
            if ($slotId) {
                $capRes        = Http::withHeaders(self::NGROK_HDRS)->timeout(10)
                    ->get(self::NGROK_BASE . '/capacity/' . $slotId);
                $capData       = $capRes->json();
                $dailyCapacity = $capData['kapasitas_maksimal']
                    ?? $capData['kapasitas']
                    ?? $capData['data']['kapasitas_maksimal']
                    ?? 0;
            }

$page = 1;
$transactions = collect();

do {

    $trxRes = Http::withHeaders(self::NGROK_HDRS)
        ->timeout(15)
        ->get(self::NGROK_BASE . '/transactions', [
            'page' => $page
        ]);

    $trxData = $trxRes->json();

    $transactions = $transactions->merge(
        $trxData['data'] ?? []
    );

    $lastPage = $trxData['last_page'] ?? 1;

    $page++;

} while ($page <= $lastPage);
            

            $today          = Carbon::today('Asia/Jakarta')->toDateString();
$bookingHariIni = $transactions

    ->filter(function ($trx) use ($today) {

        $tgl =
            $trx['tanggal_reservasi']
            ?? $trx['visit_date']
            ?? null;

        if ($tgl && strlen($tgl) > 10) {
            $tgl = substr($tgl, 0, 10);
        }

        return $tgl === $today;
    })

    ->flatMap(function ($trx) {

        return $trx['details'] ?? [];

    })

    ->reject(function ($detail) {

        return strtolower(
            $detail['status_ticket'] ?? ''
        ) === 'refunded';

    })

    ->count();

            $pendingVerification = $transactions
                ->flatMap(fn($trx) => $trx['details'] ?? [])
                ->filter(fn($d) =>
                    ($d['status_ticket'] ?? '') === 'aktif' &&
                    empty($d['waktu_masuk'])
                )->count();

            $activeVisitors      = Checkin::orderBy('waktu_masuk', 'desc')->get();
            $activeVisitorsCount = $activeVisitors->count();
$recentTransactions = $transactions
    ->flatMap(function ($trx) {

        return collect($trx['details'] ?? [])
            ->map(function ($detail) use ($trx) {

                return [

                    'kode_qr' =>
                        $detail['kode_qr'] ?? '-',

                    'nama_customer' =>
                        $trx['nama_customer'] ?? '-',

                    'nama_paket' =>
                        $detail['nama_paket'] ?? '-',

                    'total_harga' =>
                        $trx['total_harga'] ?? 0,

                    'status_pembayaran' =>
                        $trx['status_pembayaran'] ?? 'pending',
                    
                    'status_ticket' => 
                        $detail['status_ticket'] ?? '',

                    'created_at' =>
                        $trx['created_at']
                        ?? now(),

                    'waktu_masuk' =>
                        $detail['waktu_masuk'] ?? null,
                    'waktu_keluar' =>
                        $detail['waktu_keluar'] ?? null,
                ];
            });
    })
    ->take(5)
    ->values();

            $stats = [
                'total_bookings_today' => $bookingHariIni,
                'pending_verification' => $pendingVerification,
                'active_visitors'      => $activeVisitorsCount,
            ];
            $occupancyPct = $dailyCapacity > 0
    ? round(($bookingHariIni / $dailyCapacity) * 100)
    : 0;
            return view('dashboard', [
                'stats'              => $stats,
                'activeVisitors'     => $activeVisitors,
                'recentTransactions' => $recentTransactions,
                'dailyCapacity'      => $dailyCapacity,
                'occupancyPct'       => $occupancyPct,
                'slotId'             => $slotId,
                'capacityAlert'      => null,
            ]);
        } catch (\Exception $e) {
            return view('dashboard', [
                'stats'              => ['total_bookings_today' => 0, 'pending_verification' => 0, 'active_visitors' => 0],
                'activeVisitors'     => collect(),
                'recentTransactions' => collect(),
                'dailyCapacity'      => 0,
                'occupancyPct' => 0,
                'slotId'             => null,
                'capacityAlert'      => null,
            ]);
        }
    }

    /* =========================================================
       LIVE VISITORS — AJAX tiap 30 detik
       ========================================================= */
    public function liveVisitors()
    {
        $count        = Checkin::count();
        $bookingToday = 0;
        $pending      = 0;

        try {
            $trxRes = Http::withHeaders(self::NGROK_HDRS)->timeout(10)
                ->get(self::NGROK_BASE . '/transactions');
            $today  = Carbon::today('Asia/Jakarta')->toDateString();
            $allTrx = collect($trxRes->json()['data'] ?? []);

            $bookingToday = $allTrx->filter(function ($trx) use ($today) {
                $tgl = $trx['tanggal_reservasi'] ?? $trx['visit_date'] ?? null;
                if ($tgl && strlen($tgl) > 10) $tgl = substr($tgl, 0, 10);
                return $tgl === $today;
            })->count();

            $pending = $allTrx
                ->flatMap(fn($trx) => $trx['details'] ?? [])
                ->filter(fn($d) =>
                    ($d['status_ticket'] ?? '') === 'aktif' && empty($d['waktu_masuk'])
                )->count();

        } catch (\Exception $e) { /* silent */ }

        return response()->json([
            'count'         => $count,
            'booking_today' => $bookingToday,
            'pending'       => $pending,
        ]);
    }

    /* =========================================================
       NOTIFICATIONS — dipanggil JS tiap 30 detik
       Gabungkan: kapasitas hampir penuh (API) + checkin baru (local DB)
       ========================================================= */
    public function notifications()
    {
        $notifs = collect();

        // ── 1. KAPASITAS HAMPIR PENUH dari API Ngrok ──
        try {
            $slotRes  = Http::withHeaders(self::NGROK_HDRS)->timeout(8)
                ->get(self::NGROK_BASE . '/slots/today');
            $slotData = $slotRes->json();
            $listJam  = $slotData['list_jam'] ?? [];

            foreach ($listJam as $jam) {
                $kuota  = (int) ($jam['kuota']  ?? 0);
                $terisi = (int) ($jam['terisi'] ?? 0);
                $waktu  = $jam['waktu'] ?? '--:--';

                if ($kuota <= 0) continue;

                $pct = ($terisi / $kuota) * 100;

                if ($pct >= 90) {
                    $notifs->push([
                        'type'    => 'capacity',
                        'dot'     => 'orange',
                        'title'   => 'Kapasitas hampir penuh!',
                        'sub'     => "Jam {$waktu} sudah " . round($pct) . "% terisi ({$terisi}/{$kuota})",
                        'time_ts' => now('Asia/Jakarta')->timestamp * 1000,
                        'read'    => false,
                    ]);
                } elseif ($pct >= 70) {
                    $notifs->push([
                        'type'    => 'capacity',
                        'dot'     => 'blue',
                        'title'   => 'Kapasitas mulai ramai',
                        'sub'     => "Jam {$waktu} sudah " . round($pct) . "% terisi ({$terisi}/{$kuota})",
                        'time_ts' => now('Asia/Jakarta')->timestamp * 1000,
                        'read'    => false,
                    ]);
                }
            }
        } catch (\Exception $e) { /* silent */ }

        // ── 2. CHECKIN/CHECKOUT TERBARU dari local DB ──
        try {
            $startOfDay = Carbon::today('Asia/Jakarta')->startOfDay();
            $recentLogs = ActivityLog::where('created_at', '>=', $startOfDay)
                ->whereNotNull('customer_name')
                ->where('customer_name', '!=', '')
                ->where('customer_name', '!=', 'Guest')
                ->latest()
                ->take(10)
                ->get();

            foreach ($recentLogs as $log) {
                $isCheckin = $log->type === 'checkin';
                $notifs->push([
                    'type'    => $log->type,
                    'dot'     => $isCheckin ? 'green' : 'pink',
                    'title'   => $isCheckin
                        ? ($log->customer_name . ' baru check-in')
                        : ($log->customer_name . ' checkout'),
                    'sub'     => Carbon::parse($log->created_at)
                                    ->setTimezone('Asia/Jakarta')
                                    ->format('H:i') . ' WIB',
                    'time_ts' => Carbon::parse($log->created_at)
                                    ->setTimezone('Asia/Jakarta')
                                    ->timestamp * 1000,
                    'read'    => false,
                ]);
            }
        } catch (\Exception $e) { /* silent */ }

        // Urutkan terbaru dulu
        $sorted = $notifs->sortByDesc('time_ts')->values();

        return response()->json([
            'notifications' => $sorted,
            'unread_count'  => $sorted->where('read', false)->count(),
        ]);
    }

    /* =========================================================
       UPDATE CAPACITY
       ========================================================= */
  public function updateCapacity(Request $request)
    {
        $request->validate([
            'capacity' => 'required|integer|min:1',
            'slot_id'  => 'required|string',
        ]);
 
        try {
            $slotId = $request->slot_id;
 
            // URL persis sama seperti web admin teman
            $url = self::NGROK_BASE . '/capacity/update/' . $slotId;
 
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'ngrok-skip-browser-warning' => 'true',
            ])->put($url, [
                // field persis sama seperti web admin teman
                'kapasitas_maksimal' => (int) $request->capacity,
            ]);
            
 
            if ($response->successful()) {
                return response()->json(['success' => true]);
            }
 
            return response()->json([
                'success' => false,
                'message' => $response->body(),
            ], 500);
 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
 
}