<?php

namespace App\Services;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherApiService
{
    /**
     * Sync cuaca menggunakan Http::pool() — kirim banyak request
     * secara paralel, bukan satu per satu. Jauh lebih cepat.
     *
     * Open-Meteo tidak membatasi concurrent request untuk free tier,
     * jadi kita bisa kirim 25 request sekaligus (batch).
     */
    public function syncAllWeather(): array
    {
        $baseUrl = config('services.openmeteo.base_url');

        $countries = Country::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($countries->isEmpty()) {
            return [
                'success' => true,
                'total'   => 0,
                'message' => 'Tidak ada negara dengan koordinat valid.',
            ];
        }

        $syncedCount = 0;
        $failedCount = 0;

        // Bagi negara ke dalam batch 25 per kelompok
        // supaya tidak membanjiri server API sekaligus
        $batches = $countries->chunk(25);

        foreach ($batches as $batch) {
            try {
                // Http::pool() mengirim semua request dalam batch SECARA PARALEL
                $responses = Http::pool(function ($pool) use ($batch, $baseUrl) {
                    foreach ($batch as $country) {
                        $pool->as($country->cca3)
                            ->timeout(10)
                            ->get("{$baseUrl}/forecast", [
                                'latitude'  => $country->latitude,
                                'longitude' => $country->longitude,
                                'current'   => 'temperature_2m,precipitation,wind_speed_10m,weather_code',
                            ]);
                    }
                });

                // Proses setiap response dari batch
                foreach ($batch as $country) {
                    $response = $responses[$country->cca3] ?? null;

                   if (! $response instanceof \Illuminate\Http\Client\Response) {
                     $failedCount++;
                     continue;
                     }

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
                }

            } catch (\Exception $e) {
                Log::warning('Batch cuaca gagal', ['error' => $e->getMessage()]);
                $failedCount += $batch->count();
            }

            // Jeda sangat kecil antar batch (bukan antar negara)
            // 25 negara selesai → jeda 0.5 detik → batch berikutnya
            usleep(500000); // 0.5 detik per batch
        }

        // Simpan waktu sync terakhir
        cache(
            ['last_weather_sync' => now()->format('d M Y H:i:s')],
            now()->addDay()
        );

        return [
            'success' => true,
            'total'   => $syncedCount,
            'message' => "Berhasil sync cuaca {$syncedCount} negara ({$failedCount} gagal).",
        ];
    }

    private function storeWeather(Country $country, array $current): void
    {
        $temperature = $current['temperature_2m']   ?? null;
        $rainfall    = $current['precipitation']    ?? 0;
        $windSpeed   = $current['wind_speed_10m']   ?? 0;
        $weatherCode = $current['weather_code']     ?? 0;

        $stormRisk = $this->classifyStormRisk(
            (int) $weatherCode,
            (float) $windSpeed,
            (float) $rainfall
        );

        WeatherCache::create([
            'country_id' => $country->id,
            'temperature' => $temperature,
            'rainfall'    => $rainfall,
            'wind_speed'  => $windSpeed,
            'storm_risk'  => $stormRisk,
            'fetched_at'  => now(),
        ]);
    }

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