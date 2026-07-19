<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\NewsCache;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

        return view('news.index', compact('news', 'summary', 'categoryStats', 'countries'))
            ->with('lastSync', Cache::get('last_news_sync', 'Belum pernah sync'));
    }

    /**
     * AJAX endpoint — filter berita berdasarkan negara atau kategori.
     */
    public function filter(Request $request): JsonResponse
    {
        $country  = $request->query('country', '');
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
                'title'        => $article->title,
                'description'  => $article->description
                    ? \Illuminate\Support\Str::limit($article->description, 120)
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
            ];
        });

        return response()->json([
            'count'    => $news->count(),
            'country'  => $country,
            'articles' => $news,
        ]);
    }

    /**
     * Refresh berita dari GNews API, lalu langsung jalankan analisis sentimen
     * untuk berita-berita baru tersebut. Dipanggil dari tombol "Refresh Berita".
     */
    public function refreshNews(): JsonResponse
    {
        try {
            // Step 1: ambil berita terbaru
            $syncExitCode = Artisan::call('sync:news');
            $syncOutput   = trim(Artisan::output());

            if ($syncExitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil berita: ' . ($syncOutput ?: 'unknown error'),
                ], 500);
            }

            // Step 2: analisis sentimen untuk berita yang belum dianalisis
            $sentimentExitCode = Artisan::call('sentiment:analyze');
            $sentimentOutput   = trim(Artisan::output());

            if ($sentimentExitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Berita berhasil disinkron, tapi analisis sentimen gagal: '
                        . ($sentimentOutput ?: 'unknown error'),
                ], 500);
            }

            // Simpan waktu sync terakhir (dipakai di header halaman News)
            Cache::put('last_news_sync', now()->format('d M Y, H:i'), now()->addDays(7));

            return response()->json([
                'success' => true,
                'message' => 'Berita berhasil diperbarui & sentimen dianalisis.',
                'details' => [
                    'news'      => $syncOutput ?: 'Sync berita selesai.',
                    'sentiment' => $sentimentOutput ?: 'Analisis sentimen selesai.',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}