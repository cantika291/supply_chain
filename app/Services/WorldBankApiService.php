<?php

namespace App\Services;

use App\Models\Country;
use App\Models\CountryEconomicData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldBankApiService
{
    /**
     * Kode indikator World Bank yang kita butuhkan, sesuai spesifikasi PDF:
     * GDP, Inflasi, Populasi, Ekspor, Impor.
     */
    private const INDICATORS = [
        'gdp' => 'NY.GDP.MKTP.CD',           // GDP (current US$)
        'inflation_rate' => 'FP.CPI.TOTL.ZG', // Inflation, consumer prices (annual %)
        'population' => 'SP.POP.TOTL',        // Population, total
        'exports_value' => 'NE.EXP.GNFS.CD',  // Exports of goods and services (current US$)
        'imports_value' => 'NE.IMP.GNFS.CD',  // Imports of goods and services (current US$)
    ];

    /**
     * Sinkronisasi 5 indikator ekonomi untuk SEMUA negara sekaligus.
     * Strategi: 1 request per indikator (bukan per negara) untuk efisiensi -
     * mrv=1 mengambil nilai TERBARU yang tersedia (Most Recent Value).
     *
     * @return array{success: bool, total: int, message: string}
     */
    public function syncAllEconomicData(): array
    {
        $baseUrl = config('services.worldbank.base_url');
        $collectedData = []; // [cca3 => ['year' => ..., 'gdp' => ..., ...]]

        foreach (self::INDICATORS as $fieldName => $indicatorCode) {
            try {
                $response = Http::timeout(30)->get("{$baseUrl}/country/all/indicator/{$indicatorCode}", [
                    'format' => 'json',
                    'mrv' => 1,        // Most Recent Value - ambil data terbaru yang tersedia
                    'per_page' => 400, // lebih dari cukup untuk 250+ negara & region
                ]);

                if (! $response->successful()) {
                    Log::warning("World Bank API gagal untuk indikator {$indicatorCode}", [
                        'status' => $response->status(),
                    ]);

                    continue;
                }

                $json = $response->json();

                // Struktur response World Bank: [metadata, data]. Kita butuh index [1].
                $records = $json[1] ?? [];

                foreach ($records as $record) {
                    if (! is_array($record) || empty($record['countryiso3code']) || $record['value'] === null) {
                        continue;
                    }

                    $iso3 = $record['countryiso3code'];
                    $collectedData[$iso3]['year'] = (int) $record['date'];
                    $collectedData[$iso3][$fieldName] = $record['value'];
                }
            } catch (\Exception $e) {
                Log::error("Exception saat mengambil indikator {$indicatorCode}", ['error' => $e->getMessage()]);
            }
        }

        return $this->storeEconomicData($collectedData);
    }

    /**
     * Menyimpan data yang sudah terkumpul ke tabel country_economic_data,
     * dicocokkan dengan tabel countries berdasarkan kode cca3.
     */
    private function storeEconomicData(array $collectedData): array
    {
        $savedCount = 0;
        $skippedCount = 0;

        foreach ($collectedData as $cca3 => $data) {
            $country = Country::where('cca3', $cca3)->first();

            // Lewati kode yang bukan negara sungguhan (World Bank juga
            // mengembalikan data untuk region/aggregate seperti "World", "EAS" dll,
            // yang tidak ada padanannya di tabel countries kita).
            if (! $country || empty($data['year'])) {
                $skippedCount++;

                continue;
            }

            CountryEconomicData::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'year' => $data['year'],
                ],
                [
                    'gdp' => $data['gdp'] ?? null,
                    'inflation_rate' => $data['inflation_rate'] ?? null,
                    'population' => $data['population'] ?? null,
                    'exports_value' => $data['exports_value'] ?? null,
                    'imports_value' => $data['imports_value'] ?? null,
                ]
            );

            $savedCount++;
        }

        return [
            'success' => true,
            'total' => $savedCount,
            'message' => "Berhasil sinkronisasi data ekonomi {$savedCount} negara ({$skippedCount} kode dilewati karena bukan negara valid).",
        ];
    }
}