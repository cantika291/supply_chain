<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Force HTTPS di production (Railway menggunakan HTTPS)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}