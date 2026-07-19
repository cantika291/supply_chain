<?php

namespace App\Console\Commands;

use App\Services\GNewsApiService;
use App\Services\SentimentAnalysisService;
use Illuminate\Console\Command;

class SyncNewsCommand extends Command
{
    protected $signature = 'sync:news';
    protected $description = 'Sync berita dari GNews API dan analisis sentimen';

    public function handle(GNewsApiService $newsService, SentimentAnalysisService $sentimentService): int
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Sync berita dimulai...');
        $newsResult = $newsService->syncAllNews();
        $this->info($newsResult['message']);

        $this->info('Menganalisis sentimen berita baru...');
        $sentimentResult = $sentimentService->analyzeAllNews();
        $this->info($sentimentResult['message']);

        return Command::SUCCESS;
    }
}