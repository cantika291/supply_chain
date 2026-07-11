<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\CountryDashboardController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataVisualizationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Overview
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Country Dashboard
    Route::get('/countries', [CountryDashboardController::class, 'index'])->name('countries.index');
    Route::get('/countries/data', [CountryDashboardController::class, 'show'])->name('countries.show');

    // Risk Scoring
    Route::get('/risk-scoring', [RiskScoreController::class, 'index'])->name('risk.index');
    Route::get('/risk-scoring/detail', [RiskScoreController::class, 'show'])->name('risk.show');

    // Weather
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
    Route::get('/weather/country', [WeatherController::class, 'byCountry'])->name('weather.country');

    // Currency
    Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');
    Route::get('/currency/history', [CurrencyController::class, 'history'])->name('currency.history');

    // News
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/filter', [NewsController::class, 'filter'])->name('news.filter');

    // Ports
    Route::get('/ports', [PortController::class, 'index'])->name('ports.index');
    Route::get('/ports/search', [PortController::class, 'search'])->name('ports.search');

    // Data Visualization
    Route::get('/data-visualization', [DataVisualizationController::class, 'index'])->name('dataviz.index');
    Route::get('/data-visualization/data', [DataVisualizationController::class, 'getData'])->name('dataviz.data');

    // Comparison
    Route::get('/comparison', [ComparisonController::class, 'index'])->name('comparison.index');
    Route::get('/comparison/compare', [ComparisonController::class, 'compare'])->name('comparison.compare');

    // Watchlist
    Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');

    // Refresh endpoints (on-demand data update)
    Route::post('/refresh/weather', function () {
        $result = (new \App\Services\WeatherApiService())->syncAllWeather();
        return response()->json($result);
    })->name('refresh.weather');

    Route::post('/refresh/currency', function () {
        $result = (new \App\Services\ExchangeRateApiService())->syncAllRates();
        return response()->json($result);
    })->name('refresh.currency');

    Route::post('/refresh/news', function () {
        $result = (new \App\Services\GNewsApiService())->syncAllNews();
        (new \App\Services\SentimentAnalysisService())->analyzeAllNews();
        return response()->json($result);
    })->name('refresh.news');

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::get('/admin/articles', [AdminController::class, 'articles'])->name('admin.articles');
        Route::post('/admin/articles', [AdminController::class, 'storeArticle'])->name('admin.articles.store');
        Route::delete('/admin/articles/{article}', [AdminController::class, 'destroyArticle'])->name('admin.articles.destroy');
        Route::get('/admin/ports', [AdminController::class, 'ports'])->name('admin.ports');
        Route::post('/admin/ports', [AdminController::class, 'storePort'])->name('admin.ports.store');
        Route::delete('/admin/ports/{port}', [AdminController::class, 'destroyPort'])->name('admin.ports.destroy');
    });
});