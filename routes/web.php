<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\CountryDashboardController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/countries', [CountryDashboardController::class, 'index'])->name('countries.index');
    Route::get('/risk-scoring', [RiskScoreController::class, 'index'])->name('risk.index');
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/ports', [PortController::class, 'index'])->name('ports.index');
    Route::get('/comparison', [ComparisonController::class, 'index'])->name('comparison.index');
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');

    Route::middleware('admin')->group(function () {
        Route::get('/admin', function () {
            return view('admin.index');
        })->name('admin.dashboard');
    });
});