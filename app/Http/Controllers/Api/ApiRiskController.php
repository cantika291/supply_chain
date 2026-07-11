<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskScore;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiRiskController extends Controller
{
    /**
     * GET /api/risk
     * GET /api/v1/risk
     *
     * Mengembalikan risk score semua negara.
     * Query params:
     *   - level: filter berdasarkan level (Low Risk, Medium Risk, High Risk)
     *   - sort: urutan (asc/desc berdasarkan total_score, default desc)
     */
    public function index(Request $request): JsonResponse
    {
        $query = RiskScore::with('country:id,name,cca3,flag_url,region')
            ->when($request->level, fn($q) => $q->where('risk_level', $request->level))
            ->orderBy('total_score', $request->get('sort', 'desc'));

        $scores = $query->paginate((int) $request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'formula' => 'Total = (Weather×30%) + (Inflation×20%) + (Currency×10%) + (News Sentiment×40%)',
            'levels'  => [
                'Low Risk'    => '0 – 33',
                'Medium Risk' => '34 – 66',
                'High Risk'   => '67 – 100',
            ],
            'meta' => [
                'total'        => $scores->total(),
                'current_page' => $scores->currentPage(),
                'last_page'    => $scores->lastPage(),
            ],
            'data' => $scores->map(fn($s) => [
                'country'         => $s->country?->name,
                'cca3'            => $s->country?->cca3,
                'region'          => $s->country?->region,
                'flag_url'        => $s->country?->flag_url,
                'weather_score'   => (float) $s->weather_score,
                'inflation_score' => (float) $s->inflation_score,
                'currency_score'  => (float) $s->currency_score,
                'news_score'      => (float) $s->news_score,
                'total_score'     => (float) $s->total_score,
                'risk_level'      => $s->risk_level,
                'calculated_at'   => $s->calculated_at,
            ]),
        ]);
    }

    /**
     * GET /api/v1/risk/{cca3}
     * Risk score detail untuk 1 negara.
     */
    public function show(string $cca3): JsonResponse
    {
        $country = Country::with(['riskScore.histories'])
            ->where('cca3', strtoupper($cca3))
            ->first();

        if (! $country || ! $country->riskScore) {
            return response()->json([
                'success' => false,
                'message' => "Risk score untuk '{$cca3}' tidak ditemukan.",
            ], 404);
        }

        $rs = $country->riskScore;

        return response()->json([
            'success' => true,
            'data' => [
                'country'    => $country->name,
                'cca3'       => $country->cca3,
                'scores' => [
                    'weather'   => ['score' => (float) $rs->weather_score,   'weight' => '30%'],
                    'inflation' => ['score' => (float) $rs->inflation_score, 'weight' => '20%'],
                    'currency'  => ['score' => (float) $rs->currency_score,  'weight' => '10%'],
                    'news'      => ['score' => (float) $rs->news_score,      'weight' => '40%'],
                    'total'     => (float) $rs->total_score,
                ],
                'risk_level'    => $rs->risk_level,
                'calculated_at' => $rs->calculated_at,
                'history'       => $rs->histories()
                    ->orderBy('recorded_at', 'desc')
                    ->take(30)
                    ->get(['recorded_at', 'total_score', 'risk_level']),
            ],
        ]);
    }
}