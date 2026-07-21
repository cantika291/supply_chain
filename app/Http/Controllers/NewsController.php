<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\NewsCache;
use App\Services\GNewsApiService;
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

        $summary     = $sentimentService->getSentimentSummary();
        $categoryStats = NewsCache::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $lastSync = cache('last_news_sync', null);

        return view('news.index', compact(
            'news', 'summary', 'categoryStats', 'countries', 'lastSync'
        ));
    }

    public function filter(Request $request): JsonResponse
    {
        $country  = $request->query('country', '');
        $category = $request->query('category', '');

        $query = NewsCache::with('sentiment')->latest('published_at');

        if ($country) {
            $countryName = Country::where('cca3', $country)->value('name');
            if ($countryName) {
                $query->where(function ($q) use ($countryName) {
                    $q->where('title', 'like', "%{$countryName}%")
                      ->orWhere('description', 'like', "%{$countryName}%");
                });
            }
        }

        if ($category) {
            $query->where('category', $category);
        }

        $news = $query->take(20)->get()->map(fn($article) => [
            'title'        => $article->title,
            'description'  => $article->description
                ? \Illuminate\Support\Str::limit($article->description, 150)
                : null,
            'source_name'  => $article->source_name,
            'source_url'   => $article->source_url,
            'category'     => $article->category,
            'published_at' => $article->published_at?->diffForHumans(),
            'sentiment'    => $article->sentiment ? [
                'label'          => $article->sentiment->sentiment,
                'positive_score' => $article->sentiment->positive_score,
                'negative_score' => $article->sentiment->negative_score,
            ] : null,
            'supply_chain_impact' => $this->analyzeSupplyChainImpact(
                $article->title,
                $article->description ?? ''
            ),
        ]);

        return response()->json([
            'count'   => $news->count(),
            'articles'=> $news,
        ]);
    }

    public function refresh(GNewsApiService $newsService, SentimentAnalysisService $sentimentService): JsonResponse
    {
        $result    = $newsService->syncAllNews();
        $sentimentService->analyzeAllNews();
        cache(['last_news_sync' => now()->format('d M Y H:i:s')], now()->addDay());
        return response()->json($result);
    }

    public function supplyChainImpact(string $title, string $description): array
    {
    return $this->analyzeSupplyChainImpact($title, $description);
    }

    /**
     * Analisis dampak berita terhadap supply chain
     */
    private function analyzeSupplyChainImpact(string $title, string $description): array
    {
        $text = strtolower($title . ' ' . $description);

        $impacts = [
            'logistics'   => ['delay', 'port', 'shipping', 'logistics', 'freight', 'cargo', 'container', 'supply chain'],
            'economic'    => ['inflation', 'gdp', 'economy', 'recession', 'trade', 'tariff', 'export', 'import'],
            'geopolitical'=> ['war', 'conflict', 'sanction', 'embargo', 'tension', 'crisis', 'unrest'],
            'weather'     => ['storm', 'flood', 'hurricane', 'typhoon', 'earthquake', 'disaster'],
        ];

        $detected = [];
        foreach ($impacts as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $detected[] = $type;
                    break;
                }
            }
        }

        $impactLevel = match(true) {
            count($detected) >= 3 => 'High',
            count($detected) >= 2 => 'Medium',
            count($detected) >= 1 => 'Low',
            default               => 'None',
        };

        return [
            'level'    => $impactLevel,
            'types'    => array_unique($detected),
            'color'    => match($impactLevel) {
                'High'   => 'danger',
                'Medium' => 'warning',
                'Low'    => 'info',
                default  => 'secondary',
            },
        ];
    }
}