<?php

namespace App\Services;

use App\Models\NewsCache;
use App\Models\NewsSentiment;
use App\Models\NegativeWord;
use App\Models\PositiveWord;

class SentimentAnalysisService
{
    private array $positiveWords;
    private array $negativeWords;

    public function __construct()
    {
        $this->positiveWords = PositiveWord::pluck('word')->toArray();
        $this->negativeWords = NegativeWord::pluck('word')->toArray();
    }

    public function analyzeAllNews(): array
    {
        $unanalyzedNews = NewsCache::doesntHave('sentiment')->get();

        if ($unanalyzedNews->isEmpty()) {
            return [
                'success' => true,
                'total' => 0,
                'message' => 'Semua berita sudah dianalisis sebelumnya.',
            ];
        }

        $analyzedCount = 0;

        foreach ($unanalyzedNews as $news) {
            $this->analyzeNews($news);
            $analyzedCount++;
        }

        return [
            'success' => true,
            'total' => $analyzedCount,
            'message' => "Berhasil menganalisis sentimen {$analyzedCount} berita.",
        ];
    }

    public function analyzeNews(NewsCache $news): array
    {
        $text = strtolower($news->title . ' ' . ($news->description ?? ''));
        $words = preg_split('/[\s,\.!?;:()\-]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            $cleanWord = preg_replace('/[^a-z]/', '', $word);

            if (empty($cleanWord)) {
                continue;
            }

            if (in_array($cleanWord, $this->positiveWords)) {
                $positiveScore++;
            }

            if (in_array($cleanWord, $this->negativeWords)) {
                $negativeScore++;
            }
        }

        $totalWords = $positiveScore + $negativeScore;

        if ($totalWords === 0) {
            $sentiment = 'Neutral';
            $positivePct = 0.0;
            $negativePct = 0.0;
            $neutralPct = 100.0;
        } else {
            if ($positiveScore > $negativeScore) {
                $sentiment = 'Positive';
            } elseif ($negativeScore > $positiveScore) {
                $sentiment = 'Negative';
            } else {
                $sentiment = 'Neutral';
            }

            $positivePct = round(($positiveScore / $totalWords) * 100, 1);
            $negativePct = round(($negativeScore / $totalWords) * 100, 1);
            $neutralPct  = round(100 - $positivePct - $negativePct, 1);
        }

        NewsSentiment::updateOrCreate(
            ['news_cache_id' => $news->id],
            [
                'positive_score' => $positiveScore,
                'negative_score' => $negativeScore,
                'sentiment'      => $sentiment,
            ]
        );

        return [
            'positive'     => $positiveScore,
            'negative'     => $negativeScore,
            'sentiment'    => $sentiment,
            'positive_pct' => $positivePct,
            'negative_pct' => $negativePct,
            'neutral_pct'  => $neutralPct,
        ];
    }

    public function getSentimentSummary(): array
    {
        $sentiments = NewsSentiment::selectRaw('sentiment, COUNT(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment')
            ->toArray();

        $positive = $sentiments['Positive'] ?? 0;
        $neutral  = $sentiments['Neutral']  ?? 0;
        $negative = $sentiments['Negative'] ?? 0;
        $total    = $positive + $neutral + $negative;

        return [
            'positive'     => $positive,
            'neutral'      => $neutral,
            'negative'     => $negative,
            'total'        => $total,
            'positive_pct' => $total > 0 ? round(($positive / $total) * 100, 1) : 0,
            'neutral_pct'  => $total > 0 ? round(($neutral  / $total) * 100, 1) : 0,
            'negative_pct' => $total > 0 ? round(($negative / $total) * 100, 1) : 0,
        ];
    }
}