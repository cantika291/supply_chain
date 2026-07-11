<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsCache;
use App\Models\NewsSentiment;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiNewsController extends Controller
{
    /**
     * GET /api/news
     * GET /api/v1/news
     *
     * Query params:
     *   - category: logistics | trade | shipping | economy | geopolitics
     *   - sentiment: Positive | Neutral | Negative
     *   - per_page: default 20
     */
    public function index(Request $request): JsonResponse
    {
        $news = NewsCache::with('sentiment')
            ->when($request->category,
                fn($q) => $q->where('category', $request->category))
            ->when($request->sentiment,
                fn($q) => $q->whereHas('sentiment',
                    fn($s) => $s->where('sentiment', ucfirst($request->sentiment))))
            ->latest('published_at')
            ->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'meta' => [
                'total'        => $news->total(),
                'current_page' => $news->currentPage(),
                'last_page'    => $news->lastPage(),
            ],
            'data' => $news->map(fn($n) => [
                'title'       => $n->title,
                'description' => $n->description,
                'source_name' => $n->source_name,
                'source_url'  => $n->source_url,
                'category'    => $n->category,
                'published_at'=> $n->published_at,
                'sentiment'   => $n->sentiment ? [
                    'label'          => $n->sentiment->sentiment,
                    'positive_score' => $n->sentiment->positive_score,
                    'negative_score' => $n->sentiment->negative_score,
                ] : null,
            ]),
        ]);
    }

    /**
     * GET /api/v1/news/sentiment
     * Ringkasan analisis sentimen keseluruhan.
     */
    public function sentiment(SentimentAnalysisService $service): JsonResponse
    {
        $summary = $service->getSentimentSummary();

        $byCategory = NewsCache::with('sentiment')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category,
                'total'    => $item->total,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'summary'     => $summary,
                'by_category' => $byCategory,
                'algorithm'   => 'Lexicon-Based Sentiment Analysis (PHP)',
                'description' => 'Menggunakan kamus kata positif/negatif untuk menilai sentimen berita supply chain',
            ],
        ]);
    }
}