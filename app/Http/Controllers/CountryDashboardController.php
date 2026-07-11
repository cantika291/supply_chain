<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CountryEconomicData;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryDashboardController extends Controller
{
    /**
     * Halaman utama Country Dashboard.
     * Mengirim daftar negara ke view untuk dropdown.
     */
    public function index(): View
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3', 'flag_url', 'region']);

        return view('countries.index', compact('countries'));
    }

    /**
     * AJAX endpoint — ambil data lengkap 1 negara.
     * Dipanggil saat user memilih negara dari dropdown.
     */
    public function show(Request $request): JsonResponse
    {
        $cca3 = strtoupper($request->query('cca3', 'IDN'));

        $country = Country::with([
            'latestEconomicData',
            'weatherCache',
            'riskScore',
        ])->where('cca3', $cca3)->first();

        if (! $country) {
            return response()->json(['error' => 'Negara tidak ditemukan.'], 404);
        }

        // Ambil histori data ekonomi 5 tahun terakhir untuk grafik tren
        $economicHistory = CountryEconomicData::where('country_id', $country->id)
            ->orderBy('year', 'desc')
            ->take(5)
            ->get(['year', 'gdp', 'inflation_rate', 'population'])
            ->sortBy('year')
            ->values();

        // Ambil kurs mata uang negara ini
        $exchangeRate = null;
        if ($country->currency_code) {
            $exchangeRate = ExchangeRate::where('currency_code', $country->currency_code)
                ->latest('rate_date')
                ->first();
        }

        return response()->json([
            'country' => [
                'name'          => $country->name,
                'official_name' => $country->official_name,
                'cca3'          => $country->cca3,
                'capital'       => $country->capital,
                'region'        => $country->region,
                'subregion'     => $country->subregion,
                'currency_code' => $country->currency_code,
                'currency_name' => $country->currency_name,
                'language'      => $country->language,
                'flag_url'      => $country->flag_url,
            ],
            'economic' => $country->latestEconomicData ? [
                'year'          => $country->latestEconomicData->year,
                'gdp'           => $country->latestEconomicData->gdp,
                'gdp_formatted' => $this->formatGdp($country->latestEconomicData->gdp),
                'inflation'     => $country->latestEconomicData->inflation_rate,
                'population'    => number_format($country->latestEconomicData->population),
                'exports'       => $country->latestEconomicData->exports_value,
                'imports'       => $country->latestEconomicData->imports_value,
            ] : null,
            'weather' => $country->weatherCache ? [
                'temperature' => $country->weatherCache->temperature,
                'rainfall'    => $country->weatherCache->rainfall,
                'wind_speed'  => $country->weatherCache->wind_speed,
                'storm_risk'  => $country->weatherCache->storm_risk,
            ] : null,
            'risk' => $country->riskScore ? [
                'total_score'     => $country->riskScore->total_score,
                'risk_level'      => $country->riskScore->risk_level,
                'weather_score'   => $country->riskScore->weather_score,
                'inflation_score' => $country->riskScore->inflation_score,
                'currency_score'  => $country->riskScore->currency_score,
                'news_score'      => $country->riskScore->news_score,
            ] : null,
            'exchange_rate' => $exchangeRate ? [
                'rate'      => $exchangeRate->rate_to_usd,
                'date'      => $exchangeRate->rate_date,
                'formatted' => '1 USD = '.number_format($exchangeRate->rate_to_usd, 2).' '.$country->currency_code,
            ] : null,
            'economic_history' => $economicHistory,
        ]);
    }

    /**
     * Format GDP ke format yang mudah dibaca manusia.
     * Contoh: 1445642584163.80 → "1.45 Trillion USD"
     */
    private function formatGdp(?float $gdp): string
    {
        if (! $gdp) {
            return 'N/A';
        }

        if ($gdp >= 1_000_000_000_000) {
            return number_format($gdp / 1_000_000_000_000, 2).' Trillion USD';
        }

        if ($gdp >= 1_000_000_000) {
            return number_format($gdp / 1_000_000_000, 2).' Billion USD';
        }

        return number_format($gdp / 1_000_000, 2).' Million USD';
    }
}