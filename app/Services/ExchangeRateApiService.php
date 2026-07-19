<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateApiService
{
    public function syncAllRates(): array
    {
        $baseUrl = config('services.exchangerate.base_url');
        $apiKey  = config('services.exchangerate.key');

        try {
            $response = Http::timeout(30)->get("{$baseUrl}/{$apiKey}/latest/USD");

            if (! $response->successful()) {
                Log::error('ExchangeRate API gagal', ['status' => $response->status()]);
                return [
                    'success' => false,
                    'total'   => 0,
                    'message' => 'Gagal menghubungi ExchangeRate API (HTTP '.$response->status().').',
                ];
            }

            $data = $response->json();

            if (($data['result'] ?? null) !== 'success') {
                $errorType = $data['error-type'] ?? 'unknown';
                return [
                    'success' => false,
                    'total'   => 0,
                    'message' => "ExchangeRate API error: {$errorType}.",
                ];
            }

            // Selalu gunakan tanggal hari ini (bukan tanggal dari API)
            // supaya data selalu ter-update saat refresh
            $rateDate = now()->toDateString();
            $conversionRates = $data['conversion_rates'] ?? [];
            $savedCount = 0;

            foreach ($conversionRates as $currencyCode => $rate) {
                ExchangeRate::updateOrCreate(
                    [
                        'currency_code' => $currencyCode,
                        'rate_date'     => $rateDate,
                    ],
                    [
                        'rate_to_usd' => $rate,
                    ]
                );
                $savedCount++;
            }

            // Simpan waktu sync terakhir
            cache(['last_currency_sync' => now()->format('d M Y H:i:s')], now()->addDay());

            return [
                'success' => true,
                'total'   => $savedCount,
                'message' => "Berhasil sync kurs {$savedCount} mata uang (data: {$rateDate}).",
            ];

        } catch (\Exception $e) {
            Log::error('Exception sync kurs', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'total'   => 0,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ];
        }
    }
}