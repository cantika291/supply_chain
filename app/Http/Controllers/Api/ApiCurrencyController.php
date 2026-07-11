<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCurrencyController extends Controller
{
    /**
     * GET /api/currency
     * GET /api/v1/currency
     *
     * Mengembalikan kurs terbaru semua mata uang relatif terhadap USD.
     * Query params:
     *   - search: cari kode mata uang
     *   - per_page: default 50
     */
    public function index(Request $request): JsonResponse
    {
        $rates = ExchangeRate::selectRaw('currency_code, rate_to_usd, rate_date')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('exchange_rates')
                    ->groupBy('currency_code');
            })
            ->when($request->search,
                fn($q) => $q->where('currency_code', 'like', "%{$request->search}%"))
            ->orderBy('currency_code')
            ->paginate((int) $request->get('per_page', 50));

        return response()->json([
            'success'   => true,
            'base'      => 'USD',
            'note'      => 'Semua kurs relatif terhadap 1 USD. Diperbarui harian.',
            'meta' => [
                'total'        => $rates->total(),
                'current_page' => $rates->currentPage(),
                'last_page'    => $rates->lastPage(),
            ],
            'data' => $rates->map(fn($r) => [
                'currency_code' => $r->currency_code,
                'rate_to_usd'   => (float) $r->rate_to_usd,
                'rate_date'     => $r->rate_date,
                'meaning'       => "1 USD = {$r->rate_to_usd} {$r->currency_code}",
            ]),
        ]);
    }

    /**
     * GET /api/v1/currency/{code}
     * Detail + histori kurs untuk 1 mata uang.
     */
    public function show(string $code): JsonResponse
    {
        $code = strtoupper($code);

        $history = ExchangeRate::where('currency_code', $code)
            ->orderBy('rate_date', 'desc')
            ->take(30)
            ->get(['rate_date', 'rate_to_usd']);

        if ($history->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Kurs untuk '{$code}' tidak ditemukan.",
            ], 404);
        }

        $latest = $history->first();

        return response()->json([
            'success' => true,
            'data' => [
                'currency_code' => $code,
                'latest_rate'   => (float) $latest->rate_to_usd,
                'rate_date'     => $latest->rate_date,
                'meaning'       => "1 USD = {$latest->rate_to_usd} {$code}",
                'history'       => $history->map(fn($r) => [
                    'date' => $r->rate_date,
                    'rate' => (float) $r->rate_to_usd,
                ]),
            ],
        ]);
    }
}