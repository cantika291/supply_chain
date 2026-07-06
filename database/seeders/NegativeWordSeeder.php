<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

class NegativeWordSeeder extends Seeder
{
    /**
     * Kamus kata negatif untuk Lexicon-Based Sentiment Analysis.
     * Kata-kata ini merepresentasikan risiko: geopolitik, ekonomi,
     * dan gangguan logistik.
     */
    public function run(): void
    {
        $words = [
            'war', 'crisis', 'inflation', 'delay', 'disaster',
            'decrease', 'decline', 'conflict', 'sanction', 'shortage',
            'recession', 'collapse', 'strike', 'disruption', 'default',
            'tension', 'instability', 'deficit', 'crash', 'embargo',
            'blockade', 'unrest', 'volatility', 'layoff', 'bankruptcy',
        ];

        foreach ($words as $word) {
            NegativeWord::firstOrCreate(['word' => strtolower($word)]);
        }
    }
}