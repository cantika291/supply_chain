<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatchlistController extends Controller
{
    public function index(): View
    {
        $watchlists = Watchlist::with([
            'country.latestEconomicData',
            'country.weatherCache',
            'country.riskScore',
        ])
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

        $countries = Country::orderBy('name')
            ->get(['id', 'name', 'cca3', 'flag_url']);

        return view('watchlist.index', compact('watchlists', 'countries'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cca3' => ['required', 'string', 'size:3', 'exists:countries,cca3'],
        ]);

        $country = Country::where('cca3', strtoupper($request->cca3))->firstOrFail();

        $exists = Watchlist::where('user_id', auth()->id())
            ->where('country_id', $country->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "{$country->name} sudah ada di daftar favorit kamu.",
            ], 422);
        }

        Watchlist::create([
            'user_id'    => auth()->id(),
            'country_id' => $country->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$country->name} berhasil ditambahkan ke Favorite Monitoring.",
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'cca3' => ['required', 'string', 'size:3'],
        ]);

        $country = Country::where('cca3', strtoupper($request->cca3))->first();

        if (! $country) {
            return response()->json(['success' => false, 'message' => 'Negara tidak ditemukan.'], 404);
        }

        $deleted = Watchlist::where('user_id', auth()->id())
            ->where('country_id', $country->id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => "{$country->name} dihapus dari Favorite Monitoring.",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Negara tidak ditemukan di daftar favorit.',
        ], 404);
    }
}