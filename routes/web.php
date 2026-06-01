<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalkinController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScannerController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {

    return view('auth.login');

})->name('login');


Route::post('/login-api',
    [AuthController::class,'login']);

Route::post('/logout',
    [AuthController::class,'logout']);


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return redirect('/dashboard');

});


/*
|--------------------------------------------------------------------------
| MAIN ROUTES (TANPA AUTH)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard',
    [DashboardController::class, 'index']
)->name('dashboard');


Route::get('/dashboard/live-visitors',
    [DashboardController::class, 'liveVisitors']
)->name('dashboard.live-visitors');


Route::get('/dashboard/live-stats',
    [DashboardController::class, 'liveStats']
)->name('dashboard.live-stats');

Route::post('/dashboard/capacity',
    [DashboardController::class, 'updateCapacity']
)->name('dashboard.capacity');


Route::get('/walk-in',
    [WalkinController::class, 'index']
)->name('walkin.index');


Route::post('/walk-in',
    [WalkinController::class, 'store']
)->name('walkin.store');


Route::post(
'/scanner/checkin',
[ScannerController::class,'checkin']
)->name('scanner.checkin');


Route::get('/scanner',
    [ScannerController::class, 'index']
)->name('scanner.index');


Route::post('/scanner/validate',
    [ScannerController::class, 'validate']
)->name('scanner.validate');


Route::post('/scanner/checkout',
    [ScannerController::class, 'checkout']
)->name('scanner.checkout');

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/read/{id}', [NotificationController::class, 'markRead']);
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

Route::get('/transaction',
    [TransactionController::class, 'index']
)->name('transaction');


Route::get('/transaction/{id}',
    [TransactionController::class, 'show']
)->name('transaction.show');


Route::put('/transaction/refund/{id}',
    [TransactionController::class, 'refund']
)->name('transaction.refund');

Route::get('/tiket/qr/{code}', function ($code) {

    $qr =
        QrCode::format('svg')
        ->size(200)
        ->margin(1)
        ->generate($code);

    return response($qr)
        ->header('Content-Type', 'image/svg+xml');

})->name('tiket.qr');