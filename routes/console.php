<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Laravel Scheduler — Near Real-Time Data Sync
|--------------------------------------------------------------------------
| Jalankan: php artisan schedule:work (development)
|           php artisan schedule:run (production via cron)
|--------------------------------------------------------------------------
*/

// Cuaca: update setiap 10 menit (Open-Meteo gratis, 10.000 req/hari)
// 250 negara × 144 kali/hari = 36.000 req/hari — TERLALU BANYAK
// Solusi: update 50 negara terpenting saja, atau tiap 1 jam
Schedule::command('sync:weather')->hourly()->withoutOverlapping();

// Kurs: update setiap 30 menit
// ExchangeRate free: 1.500 req/bulan = 50 req/hari
// 1 request = semua 166 mata uang sekaligus — EFISIEN
Schedule::command('sync:currency')->everyThirtyMinutes()->withoutOverlapping();

// Berita: update setiap 2 jam
// GNews free: 100 req/hari = 5 kategori × 20 kali/hari
// 5 request per sync × 12 sync/hari = 60 req/hari — AMAN
Schedule::command('sync:news')->everyTwoHours()->withoutOverlapping();

// Risk Score: hitung ulang setiap 2 jam (setelah cuaca & berita update)
Schedule::command('risk:calculate')->everyTwoHours()->withoutOverlapping();

// Log aktivitas scheduler
Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('Scheduler berjalan pada: ' . now()->format('Y-m-d H:i:s'));
})->everyFiveMinutes();