<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateApiService
{
    /**
     * Mengambil kurs SEMUA mata uang relatif terhadap USD dalam 1 request,
     * lalu menyimpan setiap kurs ke tabel exchange_rates.
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function syncAllRates(): array
    {
        $baseUrl = config('services.exchangerate.base_url');
        $apiKey = config('services.exchangerate.key');

        try {
            $response = Http::timeout(30)->get("{$baseUrl}/{$apiKey}/latest/USD");

            if (! $response->successful()) {
                Log::error('ExchangeRate API gagal diakses', ['status' => $response->status()]);

                return [
                    'success' => false,
                    'total' => 0,
                    'message' => 'Gagal menghubungi ExchangeRate API (HTTP '.$response->status().').',
                ];
            }

            $data = $response->json();

            if (($data['result'] ?? null) !== 'success') {
                $errorType = $data['error-type'] ?? 'unknown error';

                return [
                    'success' => false,
                    'total' => 0,
                    'message' => "ExchangeRate API mengembalikan error: {$errorType}.",
                ];
            }

            $rateDate = now()->toDateString();
            $conversionRates = $data['conversion_rates'] ?? [];
            $savedCount = 0;

            foreach ($conversionRates as $currencyCode => $rate) {
                ExchangeRate::updateOrCreate(
                    [
                        'currency_code' => $currencyCode,
                        'rate_date' => $rateDate,
                    ],
                    [
                        'rate_to_usd' => $rate,
                    ]
                );
                $savedCount++;
            }

            return [
                'success' => true,
                'total' => $savedCount,
                'message' => "Berhasil sinkronisasi kurs {$savedCount} mata uang.",
            ];
        } catch (\Exception $e) {
            Log::error('Exception saat sinkronisasi kurs', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'total' => 0,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ];
        }
    }
}