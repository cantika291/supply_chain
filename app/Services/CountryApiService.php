<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryApiService
{
    /**
     * Mengambil SEMUA negara dari countries.dev (pengganti REST Countries API
     * yang sudah deprecated) dan menyimpannya ke tabel `countries`.
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function syncAllCountries(): array
    {
        $baseUrl = config('services.restcountries.base_url');

        try {
            $response = Http::timeout(30)->get("{$baseUrl}/countries");

            if (! $response->successful()) {
                Log::error('countries.dev API gagal diakses', [
                    'status' => $response->status(),
                ]);

                return [
                    'success' => false,
                    'total' => 0,
                    'message' => 'Gagal menghubungi countries.dev API (HTTP '.$response->status().').',
                ];
            }

            $countries = $response->json();
            $syncedCount = 0;
            $skippedCount = 0;

            foreach ($countries as $countryData) {
                if (! is_array($countryData) || empty($countryData['alpha3Code'])) {
                    $skippedCount++;

                    continue;
                }

                $this->storeCountry($countryData);
                $syncedCount++;
            }

            if ($skippedCount > 0) {
                Log::warning("Sinkronisasi negara: {$skippedCount} entri dilewati karena data tidak valid.");
            }

            return [
                'success' => true,
                'total' => $syncedCount,
                'message' => "Berhasil sinkronisasi {$syncedCount} negara.",
            ];
        } catch (\Exception $e) {
            Log::error('Exception saat sinkronisasi negara', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'total' => 0,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Menyimpan 1 negara ke database berdasarkan struktur JSON
     * dari countries.dev API.
     */
    private function storeCountry(array $countryData): void
    {
        $currencyCode = null;
        $currencyName = null;
        if (! empty($countryData['currencies'][0])) {
            $currencyCode = $countryData['currencies'][0]['code'] ?? null;
            $currencyName = $countryData['currencies'][0]['name'] ?? null;
        }

        $language = null;
        if (! empty($countryData['languages'][0])) {
            $language = $countryData['languages'][0]['name'] ?? null;
        }

        $latitude = $countryData['latlng'][0] ?? null;
        $longitude = $countryData['latlng'][1] ?? null;

        Country::updateOrCreate(
            ['cca3' => $countryData['alpha3Code']],
            [
                'name' => $countryData['name'] ?? 'Unknown',
                'official_name' => $countryData['name'] ?? null,
                'cca2' => $countryData['alpha2Code'] ?? null,
                'region' => $countryData['region'] ?? null,
                'subregion' => $countryData['subregion'] ?? null,
                'capital' => $countryData['capital'] ?? null,
                'currency_code' => $currencyCode,
                'currency_name' => $currencyName,
                'language' => $language,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'flag_url' => $countryData['flags']['png'] ?? null,
            ]
        );
    }
}