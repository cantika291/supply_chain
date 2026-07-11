<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskScoreController extends Controller
{
    public function index(): View
    {
        $riskScores = RiskScore::with('country')
            ->orderBy('total_score', 'desc')
            ->get();

        $summary = [
            'low'    => $riskScores->where('risk_level', 'Low Risk')->count(),
            'medium' => $riskScores->where('risk_level', 'Medium Risk')->count(),
            'high'   => $riskScores->where('risk_level', 'High Risk')->count(),
            'total'  => $riskScores->count(),
        ];

        $topRisky = $riskScores->take(5);
        $safest   = $riskScores->sortBy('total_score')->take(5);

        return view('risk-scoring.index', compact('riskScores', 'summary', 'topRisky', 'safest'));
    }

    public function show(Request $request): JsonResponse
    {
        $cca3 = strtoupper($request->query('cca3', 'IDN'));

        $country = Country::with(['riskScore.histories'])->where('cca3', $cca3)->first();

        if (! $country || ! $country->riskScore) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $histories = $country->riskScore->histories()
            ->orderBy('recorded_at', 'asc')
            ->get(['recorded_at', 'total_score', 'risk_level']);

        return response()->json([
            'country'   => $country->name,
            'risk'      => $country->riskScore,
            'histories' => $histories,
        ]);
    }
}