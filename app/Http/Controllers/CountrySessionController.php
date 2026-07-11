<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountrySessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:3'],
        ]);

        session([
            'current_country' => strtoupper($validated['country']),
        ]);

        return response()->json([
            'success' => true,
            'country' => session('current_country'),
        ]);
    }

    public function show(): JsonResponse
    {
        return response()->json([
            'country' => session('current_country', 'IDN'),
        ]);
    }
}