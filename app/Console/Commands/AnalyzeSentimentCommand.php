<?php

namespace App\Console\Commands;

use App\Services\SentimentAnalysisService;
use Illuminate\Console\Command;

class AnalyzeSentimentCommand extends Command
{
    protected $signature = 'sentiment:analyze';

    protected $description = 'Analisis sentimen semua berita di news_cache yang belum dianalisis';

    public function handle(SentimentAnalysisService $sentimentService): int
    {
        $this->info('Menganalisis sentimen berita...');

        $result = $sentimentService->analyzeAllNews();

        $this->info($result['message']);

        $summary = $sentimentService->getSentimentSummary();
        $this->newLine();
        $this->info('=== Ringkasan Sentimen Keseluruhan ===');
        $this->line("Positive : {$summary['positive']} berita ({$summary['positive_pct']}%)");
        $this->line("Neutral  : {$summary['neutral']} berita ({$summary['neutral_pct']}%)");
        $this->line("Negative : {$summary['negative']} berita ({$summary['negative_pct']}%)");
        $this->line("Total    : {$summary['total']} berita");

        return Command::SUCCESS;
    }
}