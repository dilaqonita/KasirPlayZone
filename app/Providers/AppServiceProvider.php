<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- 1. PASTIKAN BARIS INI ADA DI ATAS

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. TAMBAHKAN KODE INI DI DALAM METHOD BOOT
        if (config('app.env') === 'production' || env('APP_URL') !== 'http://localhost') {
            URL::forceScheme('https');
        }
    }
}