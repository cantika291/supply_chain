<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherApiService
{
    /**
     * Sinkronisasi cuaca untuk SEMUA negara yang punya koordinat valid.
     * 1 request per negara (Open-Meteo tidak punya endpoint batch),
     * dengan jeda kecil antar request agar sopan terhadap server gratis.
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function syncAllWeather(): array
    {
        $baseUrl = config('services.openmeteo.base_url');

        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $syncedCount = 0;
        $failedCount = 0;

        foreach ($countries as $country) {
            try {
                $response = Http::timeout(15)->get("{$baseUrl}/forecast", [
                    'latitude' => $country->latitude,
                    'longitude' => $country->longitude,
                    'current' => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                ]);

                if (! $response->successful()) {
                    $failedCount++;

                    continue;
                }

                $current = $response->json('current');

                if (! $current) {
                    $failedCount++;

                    continue;
                }

                $this->storeWeather($country, $current);
                $syncedCount++;
            } catch (\Exception $e) {
                Log::warning("Gagal ambil cuaca untuk {$country->name}", ['error' => $e->getMessage()]);
                $failedCount++;
            }

            // Jeda kecil antar request - sopan terhadap server gratis,
            // menghindari terkesan membanjiri (flooding) API.
            usleep(100000); // 0.1 detik
        }

        return [
            'success' => true,
            'total' => $syncedCount,
            'message' => "Berhasil sinkronisasi cuaca {$syncedCount} negara ({$failedCount} gagal/dilewati).",
        ];
    }

    /**
     * Menyimpan data cuaca 1 negara + menghitung klasifikasi storm risk
     * berdasarkan algoritma sederhana (kode WMO + kecepatan angin + curah hujan).
     */
    private function storeWeather(Country $country, array $current): void
    {
        $temperature = $current['temperature_2m'] ?? null;
        $rainfall = $current['precipitation'] ?? 0;
        $windSpeed = $current['wind_speed_10m'] ?? 0;
        $weatherCode = $current['weather_code'] ?? 0;

        $stormRisk = $this->classifyStormRisk($weatherCode, $windSpeed, $rainfall);

        WeatherCache::create([
            'country_id' => $country->id,
            'temperature' => $temperature,
            'rainfall' => $rainfall,
            'wind_speed' => $windSpeed,
            'storm_risk' => $stormRisk,
            'fetched_at' => now(),
        ]);
    }

    /**
     * Algoritma klasifikasi storm risk buatan sendiri, berdasarkan
     * kombinasi 3 indikator: kode cuaca WMO, kecepatan angin, curah hujan.
     *
     * Kode WMO 95-99 = thunderstorm (badai petir) - kategori tertinggi.
     * Referensi: https://open-meteo.com/en/docs (WMO Weather interpretation codes)
     */
    private function classifyStormRisk(int $weatherCode, float $windSpeed, float $rainfall): string
    {
        $isThunderstorm = $weatherCode >= 95 && $weatherCode <= 99;

        if ($isThunderstorm || $windSpeed > 60) {
            return 'high';
        }

        if ($rainfall > 10 || $windSpeed > 30) {
            return 'medium';
        }

        return 'low';
    }
}