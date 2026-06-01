<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransaksiController;

Route::prefix('v1')->group(function () {

    // Slot hari ini — dipanggil dashboard untuk live update
    // GET /api/v1/slots/today
    Route::get('/slots/today', function () {
        $startOfDay = \Carbon\Carbon::today()->startOfDay();
        $endOfDay   = \Carbon\Carbon::today()->endOfDay();
        $slot = \App\Models\TimeSlot::whereBetween('tanggal', [$startOfDay, $endOfDay])->first();

        if (!$slot) {
            return response()->json(['success' => false, 'message' => 'Slot tidak ditemukan'], 404);
        }

        return response()->json([
            'success'            => true,
            'slot_id'            => (string) $slot->_id,
            'kapasitas_maksimal' => (int) $slot->kapasitas_maksimal,
            'kapasitas_terpakai' => (int) $slot->kapasitas_terpakai,
            'persentase'         => $slot->kapasitas_maksimal > 0
                ? round(($slot->kapasitas_terpakai / $slot->kapasitas_maksimal) * 100)
                : 0,
            'list_jam' => $slot->list_jam ?? [],
        ]);
    });

    // Slot availability untuk dropdown jam di walkin
    // GET /api/v1/slots/availability?date=2026-04-21
    Route::get('/slots/availability', [KasirController::class, 'slotAvailability']);

    // Validasi tiket QR — dipanggil scanner
    // POST /api/v1/tickets/validate
    Route::post('/tickets/validate', [ScannerController::class, 'validateTicket']);

    // Buat transaksi — dipanggil dari mobile atau web
    // POST /api/v1/transactions/create
    Route::post('/transactions/create', [TransaksiController::class, 'store']);

    // Dashboard stats live
    // GET /api/v1/dashboard/stats
    Route::get('/dashboard/stats', [DashboardController::class, 'liveVisitors']);

});