<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ExchangeRate;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $favorites = Favorite::with([
            'country.latestEconomicData',
            'country.weatherCache',
            'country.riskScore',
        ])
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

        $data = $favorites->map(function (Favorite $fav) {
            $country = $fav->country;

            $exchangeRate = null;
            if ($country->currency_code) {
                $exchangeRate = ExchangeRate::where('currency_code', $country->currency_code)
                    ->latest('rate_date')
                    ->value('rate_to_usd');
            }

            return [
                'favorite_id'   => $fav->id,
                'cca3'          => $country->cca3,
                'name'          => $country->name,
                'flag_url'      => $country->flag_url,
                'capital'       => $country->capital,
                'region'        => $country->region,
                'gdp'           => $country->latestEconomicData?->gdp,
                'inflation'     => $country->latestEconomicData?->inflation_rate,
                'population'    => $country->latestEconomicData?->population,
                'temperature'   => $country->weatherCache?->temperature,
                'storm_risk'    => $country->weatherCache?->storm_risk,
                'risk_score'    => $country->riskScore?->total_score,
                'risk_level'    => $country->riskScore?->risk_level,
                'exchange_rate' => $exchangeRate,
                'currency_code' => $country->currency_code,
                'notes'         => $fav->notes,
            ];
        });

        return view('favorites.index', ['favorites' => $data]);
    }

    /**
     * Toggle favorite via AJAX. Menerima cca3 negara.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'cca3' => 'required|string|size:3',
        ]);

        $country = Country::where('cca3', strtoupper($request->cca3))->first();

        if (! $country) {
            return response()->json(['error' => 'Negara tidak ditemukan.'], 404);
        }

        $existing = Favorite::where('user_id', auth()->id())
            ->where('country_id', $country->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false, 'message' => "{$country->name} dihapus dari favorit."]);
        }

        Favorite::create([
            'user_id'    => auth()->id(),
            'country_id' => $country->id,
        ]);

        return response()->json(['favorited' => true, 'message' => "{$country->name} ditambahkan ke favorit."]);
    }

    public function destroy(Favorite $favorite): JsonResponse
    {
        if ($favorite->user_id !== auth()->id()) {
            return response()->json(['error' => 'Tidak diizinkan.'], 403);
        }

        $favorite->delete();

        return response()->json(['message' => 'Favorit dihapus.']);
    }

    /**
     * Kirim daftar cca3 yang sudah difavoritkan user (dipakai Country Dashboard untuk render status bintang).
     */
    public function myFavoriteCodes(): JsonResponse
    {
        $codes = Favorite::where('user_id', auth()->id())
            ->join('countries', 'countries.id', '=', 'favorites.country_id')
            ->pluck('countries.cca3');

        return response()->json($codes);
    }
}