<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $currencies = ExchangeRate::select('currency_code')
            ->distinct()
            ->orderBy('currency_code')
            ->pluck('currency_code');

        $latestRates = ExchangeRate::selectRaw('currency_code, rate_to_usd, rate_date')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('exchange_rates')
                    ->groupBy('currency_code');
            })
            ->orderBy('currency_code')
            ->get();

        $featuredCodes = ['USD', 'EUR', 'GBP', 'JPY', 'CNY', 'IDR', 'AUD', 'SGD'];
        $featuredRates = $latestRates->whereIn('currency_code', $featuredCodes)
            ->keyBy('currency_code');

        $countries = Country::whereNotNull('currency_code')
            ->orderBy('name')
            ->get(['name', 'cca3', 'currency_code', 'currency_name', 'flag_url']);

        $lastSync = cache('last_currency_sync', null);

        return view('currency.index', compact(
            'currencies', 'latestRates', 'featuredRates', 'countries', 'lastSync'
        ));
    }

    public function history(Request $request): JsonResponse
    {
        $code = strtoupper($request->query('code', 'IDR'));

        $history = ExchangeRate::where('currency_code', $code)
            ->orderBy('rate_date', 'asc')
            ->get(['rate_date', 'rate_to_usd']);

        $latest = $history->last();

        return response()->json([
            'code'    => $code,
            'latest'  => $latest?->rate_to_usd,
            'date'    => $latest?->rate_date,
            'history' => $history->map(fn($r) => [
                'date' => $r->rate_date,
                'rate' => $r->rate_to_usd,
            ]),
        ]);
    }

    /**
     * AJAX endpoint untuk refresh kurs dari API langsung
     */
    public function refresh(ExchangeRateApiService $service): JsonResponse
    {
        $result = $service->syncAllRates();
        return response()->json($result);
    }
}