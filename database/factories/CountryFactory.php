<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    /**
     * Factory ini dipakai untuk generate data negara PALSU/dummy
     * saat testing otomatis (Tahap 14) - BUKAN untuk data produksi.
     * Data produksi asli akan datang dari REST Countries API di Tahap 5.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->country(),
            'official_name' => fake()->country(),
            'cca3' => fake()->unique()->lexify('???'),
            'cca2' => fake()->lexify('??'),
            'region' => fake()->randomElement(['Asia', 'Europe', 'Africa', 'Americas', 'Oceania']),
            'subregion' => fake()->word(),
            'capital' => fake()->city(),
            'currency_code' => fake()->currencyCode(),
            'currency_name' => fake()->word(),
            'language' => fake()->languageCode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'flag_url' => fake()->imageUrl(),
        ];
    }
}