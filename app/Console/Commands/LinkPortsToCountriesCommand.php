<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Console\Command;

class LinkPortsToCountriesCommand extends Command
{
    protected $signature = 'ports:link-countries';
    protected $description = 'Hubungkan pelabuhan ke negara berdasarkan data yang ada';

    public function handle(): int
    {
        $this->info('Menghubungkan pelabuhan ke negara...');

        // Ambil semua port yang country_id masih null
        $ports = Port::whereNull('country_id')->get();

        if ($ports->isEmpty()) {
            $this->info('Semua pelabuhan sudah terhubung ke negara.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$ports->count()} pelabuhan yang belum terhubung...");

        $countries = Country::all();
        $linkedCount = 0;

        foreach ($ports as $port) {
            // Cari negara terdekat berdasarkan koordinat
            $nearestCountry = null;
            $minDistance    = PHP_FLOAT_MAX;

            foreach ($countries as $country) {
                if (! $country->latitude || ! $country->longitude) continue;

                $distance = $this->haversineDistance(
                    (float) $port->latitude,
                    (float) $port->longitude,
                    (float) $country->latitude,
                    (float) $country->longitude
                );

                if ($distance < $minDistance) {
                    $minDistance    = $distance;
                    $nearestCountry = $country;
                }
            }

            if ($nearestCountry && $minDistance < 1000) {
                $port->update(['country_id' => $nearestCountry->id]);
                $linkedCount++;
            }
        }

        $this->info("Berhasil menghubungkan {$linkedCount} pelabuhan ke negara.");
        return Command::SUCCESS;
    }

    /**
     * Hitung jarak antara 2 koordinat menggunakan formula Haversine (dalam km)
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}