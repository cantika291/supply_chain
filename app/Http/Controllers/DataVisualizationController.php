<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataVisualizationController extends Controller
{
    public function index(): View
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3', 'flag_url']);
        return view('data-visualization.index', compact('countries'));
    }

    public function getData(Request $request): JsonResponse
    {
        $cca3 = strtoupper($request->query('cca3', 'IDN'));

        $country = Country::with(['economicData', 'riskScore.histories'])
            ->where('cca3', $cca3)
            ->first();

        if (! $country) {
            return response()->json(['error' => 'Negara tidak ditemukan.'], 404);
        }

        $economicHistory = $country->economicData()
            ->orderBy('year')
            ->get(['year', 'gdp', 'inflation_rate', 'population']);

        $currencyHistory = collect();
        if ($country->currency_code) {
            $currencyHistory = ExchangeRate::where('currency_code', $country->currency_code)
                ->orderBy('rate_date')
                ->get(['rate_date', 'rate_to_usd'])
                ->map(fn($r) => [
                    'date' => $r->rate_date,
                    'rate' => (float) $r->rate_to_usd,
                ]);
        }

        $riskHistory = collect();
        if ($country->riskScore) {
            $riskHistory = $country->riskScore->histories()
                ->orderBy('recorded_at')
                ->get(['recorded_at', 'total_score', 'risk_level'])
                ->map(fn($r) => [
                    'date'       => $r->recorded_at,
                    'score'      => (float) $r->total_score,
                    'risk_level' => $r->risk_level,
                ]);
        }

        return response()->json([
            'country' => [
                'name'          => $country->name,
                'cca3'          => $country->cca3,
                'flag_url'      => $country->flag_url,
                'currency_code' => $country->currency_code,
            ],
            'economic_history' => $economicHistory,
            'currency_history' => $currencyHistory,
            'risk_history'     => $riskHistory,
        ]);
    }
}