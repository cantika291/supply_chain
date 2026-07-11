<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\NegativeWord;
use App\Models\PositiveWord;
use App\Models\RiskScore;
use App\Models\RiskScoreHistory;
use Illuminate\Support\Facades\Log;

class RiskScoringService
{
    /**
     * Bobot masing-masing komponen sesuai spesifikasi PDF.
     * Total harus = 1.0 (100%)
     */
    private const WEIGHTS = [
        'weather'   => 0.30,
        'inflation' => 0.20,
        'currency'  => 0.10,
        'news'      => 0.40,
    ];

    /**
     * Klasifikasi level risiko berdasarkan total score.
     */
    private const RISK_LEVELS = [
        'Low Risk'    => [0, 33],
        'Medium Risk' => [34, 66],
        'High Risk'   => [67, 100],
    ];

    // Cache kamus kata supaya tidak query DB ratusan kali
    private ?array $positiveWords = null;
    private ?array $negativeWords = null;

    /**
     * Hitung & simpan risk score untuk SEMUA negara.
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function calculateAllRiskScores(): array
    {
        // Load kamus kata sekali saja di awal (bukan tiap negara)
        $this->positiveWords = PositiveWord::pluck('word')->toArray();
        $this->negativeWords = NegativeWord::pluck('word')->toArray();

        $countries = Country::with([
            'latestEconomicData',
            'weatherCache',
            'riskScore',
        ])->get();

        $calculatedCount = 0;

        foreach ($countries as $country) {
            try {
                $this->calculateCountryRisk($country);
                $calculatedCount++;
            } catch (\Exception $e) {
                Log::warning("Gagal hitung risk score untuk {$country->name}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => true,
            'total' => $calculatedCount,
            'message' => "Berhasil menghitung risk score untuk {$calculatedCount} negara.",
        ];
    }

    /**
     * Hitung risk score untuk 1 negara.
     */
    public function calculateCountryRisk(Country $country): RiskScore
    {
        // Pastikan kamus kata sudah di-load
        if ($this->positiveWords === null) {
            $this->positiveWords = PositiveWord::pluck('word')->toArray();
            $this->negativeWords = NegativeWord::pluck('word')->toArray();
        }

        // 1. Hitung skor tiap komponen (masing-masing 0-100)
        $weatherScore   = $this->calculateWeatherScore($country);
        $inflationScore = $this->calculateInflationScore($country);
        $currencyScore  = $this->calculateCurrencyScore($country);
        $newsScore      = $this->calculateNewsScore($country);

        // 2. Terapkan bobot (Weighted Risk Model sesuai PDF)
        $totalScore = round(
            ($weatherScore   * self::WEIGHTS['weather'])   +
            ($inflationScore * self::WEIGHTS['inflation'])  +
            ($currencyScore  * self::WEIGHTS['currency'])   +
            ($newsScore      * self::WEIGHTS['news']),
            2
        );

        // 3. Tentukan level risiko berdasarkan total score
        $riskLevel = $this->classifyRiskLevel($totalScore);

        // 4. Simpan/update skor terkini di tabel risk_scores
        $riskScore = RiskScore::updateOrCreate(
            ['country_id' => $country->id],
            [
                'weather_score'   => $weatherScore,
                'inflation_score' => $inflationScore,
                'currency_score'  => $currencyScore,
                'news_score'      => $newsScore,
                'total_score'     => $totalScore,
                'risk_level'      => $riskLevel,
                'calculated_at'   => now(),
            ]
        );

        // 5. Catat ke histori untuk grafik tren
        RiskScoreHistory::create([
            'risk_score_id' => $riskScore->id,
            'total_score'   => $totalScore,
            'risk_level'    => $riskLevel,
            'recorded_at'   => now()->toDateString(),
        ]);

        return $riskScore;
    }

    /**
     * Komponen 1: Weather Score (bobot 30%)
     * Berdasarkan storm_risk yang sudah diklasifikasikan oleh WeatherApiService.
     */
    private function calculateWeatherScore(Country $country): float
    {
        $weatherCache = $country->weatherCache;

        if (! $weatherCache) {
            return 50.0; // Default medium kalau tidak ada data cuaca
        }

        return match ($weatherCache->storm_risk) {
            'high'   => 90.0,
            'medium' => 50.0,
            'low'    => 10.0,
            default  => 50.0,
        };
    }

    /**
     * Komponen 2: Inflation Score (bobot 20%)
     * Semakin tinggi inflasi, semakin tinggi risiko biaya produksi.
     */
    private function calculateInflationScore(Country $country): float
    {
        $economicData = $country->latestEconomicData;

        if (! $economicData || $economicData->inflation_rate === null) {
            return 50.0; // Default medium kalau tidak ada data
        }

        $inflationRate = (float) $economicData->inflation_rate;

        if ($inflationRate < 0) {
            return 20.0; // Deflasi — risiko rendah-medium
        }

        if ($inflationRate < 2) {
            return 10.0; // Inflasi ideal (<2%) - sangat rendah
        }

        if ($inflationRate < 5) {
            return 30.0; // Inflasi normal (2-5%) - rendah
        }

        if ($inflationRate < 10) {
            return 60.0; // Inflasi tinggi (5-10%) - medium-high
        }

        return 90.0; // Inflasi sangat tinggi (>10%) - sangat berisiko
    }

    /**
     * Komponen 3: Currency Score (bobot 10%)
     * Dihitung dari ketersediaan data kurs — kalau mata uang negara
     * tidak ada di database kurs kita, dianggap berisiko (tidak likuid/tidak
     * terlacak). Ini pendekatan sederhana sesuai level tugas akhir.
     */
    private function calculateCurrencyScore(Country $country): float
    {
        if (! $country->currency_code) {
            return 50.0;
        }

        $hasRate = \App\Models\ExchangeRate::where('currency_code', $country->currency_code)
            ->exists();

        if (! $hasRate) {
            return 70.0; // Mata uang tidak terlacak = berisiko lebih tinggi
        }

        // Mata uang terlacak = risiko dasar rendah
        // Bisa dikembangkan nanti dengan data volatilitas historis
        return 20.0;
    }

    /**
     * Komponen 4: News Sentiment Score (bobot 40%)
     * Lexicon-Based Sentiment Analysis — menghitung proporsi berita negatif
     * dari semua berita yang ada di database (berita umum, bukan per negara,
     * karena GNews tidak filter per negara secara akurat di free tier).
     *
     * Kalau tidak ada berita sama sekali, dianggap neutral (50).
     */
    private function calculateNewsScore(Country $country): float
    {
        // Ambil 20 berita terbaru dari database
        $recentNews = NewsCache::latest()->take(20)->get();

        if ($recentNews->isEmpty()) {
            return 50.0;
        }

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($recentNews as $news) {
            $text = strtolower($news->title . ' ' . ($news->description ?? ''));
            $words = preg_split('/\s+/', $text);

            foreach ($words as $word) {
                $word = preg_replace('/[^a-z]/', '', $word);

                if (in_array($word, $this->positiveWords)) {
                    $positiveCount++;
                }

                if (in_array($word, $this->negativeWords)) {
                    $negativeCount++;
                }
            }
        }

        $totalSentiment = $positiveCount + $negativeCount;

        if ($totalSentiment === 0) {
            return 50.0; // Tidak ada kata yang dikenali — neutral
        }

        // Skor berbasis proporsi kata negatif (0-100)
        $negativeRatio = $negativeCount / $totalSentiment;

        return round($negativeRatio * 100, 2);
    }

    /**
     * Klasifikasi level risiko berdasarkan total score.
     */
    private function classifyRiskLevel(float $score): string
    {
        foreach (self::RISK_LEVELS as $level => [$min, $max]) {
            if ($score >= $min && $score <= $max) {
                return $level;
            }
        }

        return 'High Risk'; // Fallback kalau score > 100 (tidak seharusnya terjadi)
    }
}