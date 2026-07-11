<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCountryController extends Controller
{
    /**
     * GET /api/countries
     * GET /api/v1/countries
     *
     * Mengembalikan daftar semua negara dengan data ekonomi terkini.
     * Query params:
     *   - region: filter berdasarkan region (Asia, Europe, dll)
     *   - search: cari berdasarkan nama
     *   - per_page: jumlah per halaman (default 50)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Country::with(['latestEconomicData', 'riskScore'])
            ->when($request->region, fn($q) => $q->where('region', $request->region))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name');

        $perPage = min((int) $request->get('per_page', 50), 250);
        $countries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'meta' => [
                'total'        => $countries->total(),
                'per_page'     => $countries->perPage(),
                'current_page' => $countries->currentPage(),
                'last_page'    => $countries->lastPage(),
            ],
            'data' => $countries->map(fn($c) => [
                'name'          => $c->name,
                'official_name' => $c->official_name,
                'cca3'          => $c->cca3,
                'cca2'          => $c->cca2,
                'region'        => $c->region,
                'subregion'     => $c->subregion,
                'capital'       => $c->capital,
                'currency_code' => $c->currency_code,
                'currency_name' => $c->currency_name,
                'language'      => $c->language,
                'latitude'      => (float) $c->latitude,
                'longitude'     => (float) $c->longitude,
                'flag_url'      => $c->flag_url,
                'economic_data' => $c->latestEconomicData ? [
                    'year'        => $c->latestEconomicData->year,
                    'gdp'         => (float) $c->latestEconomicData->gdp,
                    'inflation'   => (float) $c->latestEconomicData->inflation_rate,
                    'population'  => $c->latestEconomicData->population,
                    'exports'     => (float) $c->latestEconomicData->exports_value,
                    'imports'     => (float) $c->latestEconomicData->imports_value,
                ] : null,
                'risk_score' => $c->riskScore ? [
                    'total_score' => (float) $c->riskScore->total_score,
                    'risk_level'  => $c->riskScore->risk_level,
                ] : null,
            ]),
        ]);
    }

    /**
     * GET /api/v1/countries/{cca3}
     * Detail 1 negara berdasarkan kode ISO 3 huruf.
     */
    public function show(string $cca3): JsonResponse
    {
        $country = Country::with([
            'latestEconomicData',
            'weatherCache',
            'riskScore',
        ])->where('cca3', strtoupper($cca3))->first();

        if (! $country) {
            return response()->json([
                'success' => false,
                'message' => "Negara dengan kode '{$cca3}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name'          => $country->name,
                'official_name' => $country->official_name,
                'cca3'          => $country->cca3,
                'cca2'          => $country->cca2,
                'region'        => $country->region,
                'subregion'     => $country->subregion,
                'capital'       => $country->capital,
                'currency_code' => $country->currency_code,
                'currency_name' => $country->currency_name,
                'language'      => $country->language,
                'latitude'      => (float) $country->latitude,
                'longitude'     => (float) $country->longitude,
                'flag_url'      => $country->flag_url,
                'economic_data' => $country->latestEconomicData ? [
                    'year'       => $country->latestEconomicData->year,
                    'gdp'        => (float) $country->latestEconomicData->gdp,
                    'inflation'  => (float) $country->latestEconomicData->inflation_rate,
                    'population' => $country->latestEconomicData->population,
                    'exports'    => (float) $country->latestEconomicData->exports_value,
                    'imports'    => (float) $country->latestEconomicData->imports_value,
                ] : null,
                'weather' => $country->weatherCache ? [
                    'temperature' => (float) $country->weatherCache->temperature,
                    'rainfall'    => (float) $country->weatherCache->rainfall,
                    'wind_speed'  => (float) $country->weatherCache->wind_speed,
                    'storm_risk'  => $country->weatherCache->storm_risk,
                    'fetched_at'  => $country->weatherCache->fetched_at,
                ] : null,
                'risk_score' => $country->riskScore ? [
                    'weather_score'   => (float) $country->riskScore->weather_score,
                    'inflation_score' => (float) $country->riskScore->inflation_score,
                    'currency_score'  => (float) $country->riskScore->currency_score,
                    'news_score'      => (float) $country->riskScore->news_score,
                    'total_score'     => (float) $country->riskScore->total_score,
                    'risk_level'      => $country->riskScore->risk_level,
                    'calculated_at'   => $country->riskScore->calculated_at,
                ] : null,
            ],
        ]);
    }
}