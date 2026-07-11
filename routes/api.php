<?php

use App\Http\Controllers\Api\ApiCountryController;
use App\Http\Controllers\Api\ApiCurrencyController;
use App\Http\Controllers\Api\ApiNewsController;
use App\Http\Controllers\Api\ApiPortController;
use App\Http\Controllers\Api\ApiRiskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API Routes — Global Supply Chain Risk Intelligence Platform
|--------------------------------------------------------------------------
| Semua endpoint berikut sesuai spesifikasi PDF:
| GET /api/countries
| GET /api/risk
| GET /api/ports
| GET /api/news
| GET /api/currency
|--------------------------------------------------------------------------
*/

// Public API (tidak perlu auth untuk keperluan demo & testing)
Route::prefix('v1')->group(function () {

    // GET /api/v1/countries
    Route::get('/countries', [ApiCountryController::class, 'index']);
    Route::get('/countries/{cca3}', [ApiCountryController::class, 'show']);

    // GET /api/v1/risk
    Route::get('/risk', [ApiRiskController::class, 'index']);
    Route::get('/risk/{cca3}', [ApiRiskController::class, 'show']);

    // GET /api/v1/ports
    Route::get('/ports', [ApiPortController::class, 'index']);
    Route::get('/ports/search', [ApiPortController::class, 'search']);

    // GET /api/v1/news
    Route::get('/news', [ApiNewsController::class, 'index']);
    Route::get('/news/sentiment', [ApiNewsController::class, 'sentiment']);

    // GET /api/v1/currency
    Route::get('/currency', [ApiCurrencyController::class, 'index']);
    Route::get('/currency/{code}', [ApiCurrencyController::class, 'show']);
});

// Alias tanpa versi (sesuai permintaan PDF persis)
Route::get('/countries', [ApiCountryController::class, 'index']);
Route::get('/risk', [ApiRiskController::class, 'index']);
Route::get('/ports', [ApiPortController::class, 'index']);
Route::get('/news', [ApiNewsController::class, 'index']);
Route::get('/currency', [ApiCurrencyController::class, 'index']);