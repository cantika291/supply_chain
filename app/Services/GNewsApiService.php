<?php

namespace App\Services;

use App\Models\NewsCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GNewsApiService
{
    /**
     * Kategori berita sesuai spesifikasi PDF, dipetakan ke query pencarian
     * yang relevan. Setiap kategori = 1 request (hemat kuota 100/hari).
     */
    private const CATEGORIES = [
        'logistics' => 'supply chain logistics',
        'trade' => 'global trade tariff',
        'shipping' => 'shipping industry port',
        'economy' => 'global economy inflation',
        'geopolitics' => 'geopolitical conflict sanctions',
    ];

    /**
     * Sinkronisasi berita untuk semua kategori sekaligus.
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function syncAllNews(): array
    {
        $baseUrl = config('services.gnews.base_url');
        $apiKey = config('services.gnews.key');

        $totalSaved = 0;
        $errors = [];

        foreach (self::CATEGORIES as $category => $query) {
            try {
                $response = Http::timeout(20)->get("{$baseUrl}/search", [
                    'q' => $query,
                    'lang' => 'en',
                    'max' => 10,
                    'apikey' => $apiKey,
                ]);

                if (! $response->successful()) {
                    $errors[] = "{$category} (HTTP {$response->status()})";

                    Log::warning("GNews API gagal untuk kategori {$category}", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    continue;
                }

                $articles = $response->json('articles') ?? [];

                foreach ($articles as $article) {
                    $this->storeArticle($article, $category);
                    $totalSaved++;
                }
            } catch (\Exception $e) {
                $errors[] = $category;
                Log::error("Exception saat mengambil berita kategori {$category}", ['error' => $e->getMessage()]);
            }
        }

        $message = "Berhasil sinkronisasi {$totalSaved} berita dari ".count(self::CATEGORIES).' kategori.';

        if (! empty($errors)) {
            $message .= ' Gagal: '.implode(', ', $errors).'.';
        }

        return [
            'success' => true,
            'total' => $totalSaved,
            'message' => $message,
        ];
    }

    /**
     * Menyimpan 1 artikel berita. Menggunakan updateOrCreate berdasarkan
     * source_url (unique) supaya berita yang sama tidak tersimpan duplikat
     * kalau muncul di beberapa kategori sekaligus atau di-sync ulang.
     */
    private function storeArticle(array $article, string $category): void
    {
        if (empty($article['url']) || empty($article['title'])) {

        cache(['last_news_sync' => now()->toDateTimeString()], now()->addDay());
            return;
        }

        NewsCache::updateOrCreate(
            ['source_url' => $article['url']],
            [
                'title' => $article['title'],
                'description' => $article['description'] ?? null,
                'source_name' => $article['source']['name'] ?? 'Unknown',
                'category' => $category,
                'published_at' => $article['publishedAt'] ?? null,
            ]
        );
    }
}