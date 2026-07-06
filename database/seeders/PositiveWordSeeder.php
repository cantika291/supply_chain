<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class PositiveWordSeeder extends Seeder
{
    /**
     * Kamus kata positif untuk Lexicon-Based Sentiment Analysis.
     * Kata-kata ini dipilih karena sering muncul di berita ekonomi,
     * logistik, dan geopolitik dengan konotasi positif.
     */
    public function run(): void
    {
        $words = [
            'growth', 'increase', 'profit', 'stable', 'improve',
            'recovery', 'expansion', 'surplus', 'boost', 'gain',
            'rise', 'strong', 'success', 'agreement', 'cooperation',
            'investment', 'opportunity', 'progress', 'efficient', 'resilient',
            'breakthrough', 'partnership', 'upgrade', 'thrive', 'rebound',
        ];

        foreach ($words as $word) {
            PositiveWord::firstOrCreate(['word' => strtolower($word)]);
        }
    }
}