<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(): View
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3', 'flag_url']);

        return view('comparison.index', compact('countries'));
    }

    public function compare(Request $request): JsonResponse
    {
        $cca3A = strtoupper($request->query('country_a', 'DEU'));
        $cca3B = strtoupper($request->query('country_b', 'AUS'));

        $countryA = $this->getCountryData($cca3A);
        $countryB = $this->getCountryData($cca3B);

        if (! $countryA || ! $countryB) {
            return response()->json(['error' => 'Data negara tidak ditemukan.'], 404);
        }

        return response()->json([
            'country_a' => $countryA,
            'country_b' => $countryB,
        ]);
    }

    private function getCountryData(string $cca3): ?array
    {
        $country = Country::with([
            'latestEconomicData',
            'weatherCache',
            'riskScore',
        ])->where('cca3', $cca3)->first();

        if (! $country) {
            return null;
        }

        $exchangeRate = null;
        if ($country->currency_code) {
            $exchangeRate = ExchangeRate::where('currency_code', $country->currency_code)
                ->latest('rate_date')
                ->value('rate_to_usd');
        }

        return [
            'name'          => $country->name,
            'cca3'          => $country->cca3,
            'flag_url'      => $country->flag_url,
            'capital'       => $country->capital,
            'region'        => $country->region,
            'currency_code' => $country->currency_code,
            'economic'      => $country->latestEconomicData ? [
                'gdp'        => (float) $country->latestEconomicData->gdp,
                'gdp_formatted' => $this->formatGdp($country->latestEconomicData->gdp),
                'inflation'  => (float) $country->latestEconomicData->inflation_rate,
                'population' => $country->latestEconomicData->population,
            ] : null,
            'weather'       => $country->weatherCache ? [
                'temperature' => $country->weatherCache->temperature,
                'wind_speed'  => $country->weatherCache->wind_speed,
                'storm_risk'  => $country->weatherCache->storm_risk,
            ] : null,
            'risk'          => $country->riskScore ? [
                'total_score' => (float) $country->riskScore->total_score,
                'risk_level'  => $country->riskScore->risk_level,
            ] : null,
            'exchange_rate' => $exchangeRate,
        ];
    }

    private function formatGdp(?float $gdp): string
    {
        if (! $gdp) return 'N/A';
        if ($gdp >= 1_000_000_000_000) return number_format($gdp / 1_000_000_000_000, 2).' T USD';
        if ($gdp >= 1_000_000_000) return number_format($gdp / 1_000_000_000, 2).' B USD';
        return number_format($gdp / 1_000_000, 2).' M USD';
    }
}