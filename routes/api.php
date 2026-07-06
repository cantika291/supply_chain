<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route API lengkap (GET /api/countries, /api/risk, /api/ports,
// /api/news, /api/currency) akan diisi penuh di Tahap 13.