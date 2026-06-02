<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Kunci utama: Paksa semua URL & Route di semua Blade jadi HTTPS jika di Railway
        if (config('app.env') === 'production' || env('TRUST_PROXIES')) {
            URL::forceScheme('https');
        }
    }
}