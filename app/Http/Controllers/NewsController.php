<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\NewsCache;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(SentimentAnalysisService $sentimentService): View
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca3', 'flag_url']);

        $news = NewsCache::with('sentiment')
            ->latest('published_at')
            ->paginate(10);

        $summary = $sentimentService->getSentimentSummary();

        $categoryStats = NewsCache::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        return view('news.index', compact('news', 'summary', 'categoryStats', 'countries'));
    }

    /**
     * AJAX endpoint — filter berita berdasarkan negara atau kategori.
     */
    public function filter(Request $request): JsonResponse
    {
        $country = $request->query('country', '');
        $category = $request->query('category', '');

        $query = NewsCache::with('sentiment')->latest('published_at');

        // Filter berdasarkan negara (cari nama negara di judul atau deskripsi)
        if ($country) {
            $countryName = Country::where('cca3', $country)->value('name');
            if ($countryName) {
                $query->where(function ($q) use ($countryName) {
                    $q->where('title', 'like', "%{$countryName}%")
                      ->orWhere('description', 'like', "%{$countryName}%");
                });
            }
        }

        // Filter berdasarkan kategori
        if ($category) {
            $query->where('category', $category);
        }

        $news = $query->take(20)->get()->map(function ($article) {
            return [
                'title'       => $article->title,
                'description' => $article->description
                    ? \Illuminate\Support\Str::limit($article->description, 120)
                    : null,
                'source_name' => $article->source_name,
                'source_url'  => $article->source_url,
                'category'    => $article->category,
                'published_at'=> $article->published_at?->diffForHumans(),
                'sentiment'   => $article->sentiment ? [
                    'label'          => $article->sentiment->sentiment,
                    'positive_score' => $article->sentiment->positive_score,
                    'negative_score' => $article->sentiment->negative_score,
                ] : null,
            ];
        });

        return response()->json([
            'count'   => $news->count(),
            'country' => $country,
            'articles'=> $news,
        ]);
    }
}