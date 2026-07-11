<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function index(): View
    {
        $weatherData = WeatherCache::with('country')
            ->whereHas('country', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->latest('fetched_at')
            ->get()
            ->unique('country_id')
            ->values();

        $summary = [
            'high'   => $weatherData->where('storm_risk', 'high')->count(),
            'medium' => $weatherData->where('storm_risk', 'medium')->count(),
            'low'    => $weatherData->where('storm_risk', 'low')->count(),
            'total'  => $weatherData->count(),
        ];

        $mapData = $weatherData->map(fn($w) => [
            'name'        => $w->country->name,
            'cca3'        => $w->country->cca3,
            'lat'         => (float) $w->country->latitude,
            'lng'         => (float) $w->country->longitude,
            'temperature' => $w->temperature,
            'rainfall'    => $w->rainfall,
            'wind_speed'  => $w->wind_speed,
            'storm_risk'  => $w->storm_risk,
            'flag_url'    => $w->country->flag_url,
        ])->values();

        return view('weather.index', compact('summary', 'mapData'));
    }

    public function byCountry(Request $request): JsonResponse
    {
        $cca3    = strtoupper($request->query('cca3', 'IDN'));
        $country = Country::with('weatherCache')->where('cca3', $cca3)->first();

        if (! $country || ! $country->weatherCache) {
            return response()->json(['error' => 'Data cuaca tidak tersedia.'], 404);
        }

        $w = $country->weatherCache;

        return response()->json([
            'country'     => $country->name,
            'cca3'        => $country->cca3,
            'flag_url'    => $country->flag_url,
            'lat'         => (float) $country->latitude,
            'lng'         => (float) $country->longitude,
            'temperature' => $w->temperature,
            'rainfall'    => $w->rainfall,
            'wind_speed'  => $w->wind_speed,
            'storm_risk'  => $w->storm_risk,
            'fetched_at'  => $w->fetched_at?->diffForHumans(),
        ]);
    }
}