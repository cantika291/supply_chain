<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Console\Command;

class ImportPortsCommand extends Command
{
    protected $signature = 'import:ports';

    protected $description = 'Import data pelabuhan dari World Port Index CSV';

    public function handle(): int
    {
        $filePath = database_path('data/world-port-index.csv');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Membaca file CSV...');

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->error('Gagal membuka file CSV.');
            return Command::FAILURE;
        }

        // Header CSV
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        $this->info('Header CSV berhasil dibaca.');

        $importedCount = 0;
        $skippedCount = 0;

        // Cache semua negara berdasarkan nama
        $countries = Country::all()->keyBy(function ($country) {
            return strtoupper(trim($country->name));
        });

        // Alias nama negara
        $aliases = [
            'UNITED STATES' => 'UNITED STATES OF AMERICA',
            'RUSSIA' => 'RUSSIAN FEDERATION',
            'VIETNAM' => 'VIET NAM',
            'SOUTH KOREA' => 'KOREA, REPUBLIC OF',
            'NORTH KOREA' => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF",
            'IRAN' => 'IRAN (ISLAMIC REPUBLIC OF)',
            'SYRIA' => 'SYRIAN ARAB REPUBLIC',
            'TANZANIA' => 'TANZANIA, UNITED REPUBLIC OF',
            'BOLIVIA' => 'BOLIVIA (PLURINATIONAL STATE OF)',
            'VENEZUELA' => 'VENEZUELA (BOLIVARIAN REPUBLIC OF)',
        ];

        while (($row = fgetcsv($handle)) !== false) {

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), null);
            }

            $data = array_combine($headers, $row);

            $portName = trim($data['Main Port Name'] ?? '');
            $portCode = trim($data['World Port Index Number'] ?? '');
            $countryName = strtoupper(trim($data['Country Code'] ?? ''));
            $latitude = (float)($data['Latitude'] ?? 0);
            $longitude = (float)($data['Longitude'] ?? 0);
            $harborType = trim($data['Harbor Type'] ?? '');

            if (empty($portName) || $latitude == 0 || $longitude == 0) {
                $skippedCount++;
                continue;
            }

            // Gunakan alias jika ada
            $countryName = $aliases[$countryName] ?? $countryName;

            // Cari negara
            $country = $countries->get($countryName);

            Port::updateOrCreate(
                [
                    'port_code' => $portCode,
                ],
                [
                    'name' => $portName,
                    'country_id' => $country?->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'harbor_type' => $harborType ?: null,
                ]
            );

            $importedCount++;
        }

        fclose($handle);

        $this->info("Import selesai.");
        $this->info("Berhasil : {$importedCount}");
        $this->info("Dilewati : {$skippedCount}");

        return Command::SUCCESS;
    }
}