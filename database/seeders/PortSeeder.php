<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            // Asia
            ['name' => 'Port of Shanghai', 'country' => 'CHN', 'lat' => 31.2304, 'lng' => 121.4737, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Singapore', 'country' => 'SGP', 'lat' => 1.2655, 'lng' => 103.8200, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Shenzhen', 'country' => 'CHN', 'lat' => 22.5431, 'lng' => 114.0579, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Busan', 'country' => 'KOR', 'lat' => 35.1796, 'lng' => 129.0756, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Hong Kong', 'country' => 'HKG', 'lat' => 22.3193, 'lng' => 114.1694, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Guangzhou', 'country' => 'CHN', 'lat' => 23.1291, 'lng' => 113.2644, 'type' => 'River Natural'],
            ['name' => 'Port of Qingdao', 'country' => 'CHN', 'lat' => 36.0671, 'lng' => 120.3826, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Tianjin', 'country' => 'CHN', 'lat' => 39.0042, 'lng' => 117.7148, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Tanjung Pelepas', 'country' => 'MYS', 'lat' => 1.3628, 'lng' => 103.5478, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Klang', 'country' => 'MYS', 'lat' => 3.0319, 'lng' => 101.3917, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Tanjung Priok', 'country' => 'IDN', 'lat' => -6.1045, 'lng' => 106.8737, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Surabaya', 'country' => 'IDN', 'lat' => -7.2458, 'lng' => 112.7378, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Belawan', 'country' => 'IDN', 'lat' => 3.7855, 'lng' => 98.6834, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Bangkok', 'country' => 'THA', 'lat' => 13.7563, 'lng' => 100.5018, 'type' => 'River Natural'],
            ['name' => 'Port of Laem Chabang', 'country' => 'THA', 'lat' => 13.0839, 'lng' => 100.8836, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Ho Chi Minh City', 'country' => 'VNM', 'lat' => 10.7626, 'lng' => 106.6602, 'type' => 'River Natural'],
            ['name' => 'Port of Manila', 'country' => 'PHL', 'lat' => 14.5820, 'lng' => 120.9673, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Mumbai', 'country' => 'IND', 'lat' => 18.9667, 'lng' => 72.8333, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Chennai', 'country' => 'IND', 'lat' => 13.0827, 'lng' => 80.2707, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Colombo', 'country' => 'LKA', 'lat' => 6.9271, 'lng' => 79.8612, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Karachi', 'country' => 'PAK', 'lat' => 24.8607, 'lng' => 67.0011, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Dubai (Jebel Ali)', 'country' => 'ARE', 'lat' => 24.9857, 'lng' => 55.0822, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Abu Dhabi', 'country' => 'ARE', 'lat' => 24.4539, 'lng' => 54.3773, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Bandar Abbas', 'country' => 'IRN', 'lat' => 27.1865, 'lng' => 56.2808, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Yokohama', 'country' => 'JPN', 'lat' => 35.4437, 'lng' => 139.6380, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Tokyo', 'country' => 'JPN', 'lat' => 35.6762, 'lng' => 139.6503, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Osaka', 'country' => 'JPN', 'lat' => 34.6937, 'lng' => 135.5023, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Kaohsiung', 'country' => 'TWN', 'lat' => 22.6273, 'lng' => 120.3014, 'type' => 'Coastal Natural'],

            // Europe
            ['name' => 'Port of Rotterdam', 'country' => 'NLD', 'lat' => 51.9225, 'lng' => 4.4792, 'type' => 'River Natural'],
            ['name' => 'Port of Antwerp', 'country' => 'BEL', 'lat' => 51.2194, 'lng' => 4.4025, 'type' => 'River Natural'],
            ['name' => 'Port of Hamburg', 'country' => 'DEU', 'lat' => 53.5753, 'lng' => 10.0153, 'type' => 'River Natural'],
            ['name' => 'Port of Bremerhaven', 'country' => 'DEU', 'lat' => 53.5396, 'lng' => 8.5809, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Felixstowe', 'country' => 'GBR', 'lat' => 51.9639, 'lng' => 1.3513, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Southampton', 'country' => 'GBR', 'lat' => 50.8979, 'lng' => -1.4049, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Valencia', 'country' => 'ESP', 'lat' => 39.4699, 'lng' => -0.3763, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Barcelona', 'country' => 'ESP', 'lat' => 41.3851, 'lng' => 2.1734, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Algeciras', 'country' => 'ESP', 'lat' => 36.1408, 'lng' => -5.4523, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Genoa', 'country' => 'ITA', 'lat' => 44.4056, 'lng' => 8.9463, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Marseille', 'country' => 'FRA', 'lat' => 43.2965, 'lng' => 5.3698, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Le Havre', 'country' => 'FRA', 'lat' => 49.4944, 'lng' => 0.1079, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Piraeus', 'country' => 'GRC', 'lat' => 37.9479, 'lng' => 23.6413, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Gioia Tauro', 'country' => 'ITA', 'lat' => 38.4306, 'lng' => 15.8978, 'type' => 'Coastal Natural'],

            // Americas
            ['name' => 'Port of Los Angeles', 'country' => 'USA', 'lat' => 33.7395, 'lng' => -118.2615, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Long Beach', 'country' => 'USA', 'lat' => 33.7542, 'lng' => -118.2164, 'type' => 'Coastal Natural'],
            ['name' => 'Port of New York', 'country' => 'USA', 'lat' => 40.6501, 'lng' => -74.0399, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Houston', 'country' => 'USA', 'lat' => 29.7604, 'lng' => -95.3698, 'type' => 'River Natural'],
            ['name' => 'Port of Savannah', 'country' => 'USA', 'lat' => 32.0835, 'lng' => -81.0998, 'type' => 'River Natural'],
            ['name' => 'Port of Seattle', 'country' => 'USA', 'lat' => 47.6062, 'lng' => -122.3321, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Santos', 'country' => 'BRA', 'lat' => -23.9618, 'lng' => -46.3322, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Itajai', 'country' => 'BRA', 'lat' => -26.9078, 'lng' => -48.6619, 'type' => 'River Natural'],
            ['name' => 'Port of Manzanillo', 'country' => 'MEX', 'lat' => 19.0559, 'lng' => -104.3190, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Veracruz', 'country' => 'MEX', 'lat' => 19.1738, 'lng' => -96.1342, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Colon', 'country' => 'PAN', 'lat' => 9.3547, 'lng' => -79.9013, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Buenos Aires', 'country' => 'ARG', 'lat' => -34.6037, 'lng' => -58.3816, 'type' => 'River Natural'],
            ['name' => 'Port of Cartagena', 'country' => 'COL', 'lat' => 10.3910, 'lng' => -75.4794, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Vancouver', 'country' => 'CAN', 'lat' => 49.2827, 'lng' => -123.1207, 'type' => 'Coastal Natural'],

            // Africa
            ['name' => 'Port of Durban', 'country' => 'ZAF', 'lat' => -29.8587, 'lng' => 31.0218, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Cape Town', 'country' => 'ZAF', 'lat' => -33.9249, 'lng' => 18.4241, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Alexandria', 'country' => 'EGY', 'lat' => 31.2001, 'lng' => 29.9187, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Dakar', 'country' => 'SEN', 'lat' => 14.6928, 'lng' => -17.4467, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Lagos (Apapa)', 'country' => 'NGA', 'lat' => 6.4531, 'lng' => 3.3958, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Mombasa', 'country' => 'KEN', 'lat' => -4.0435, 'lng' => 39.6682, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Djibouti', 'country' => 'DJI', 'lat' => 11.5806, 'lng' => 43.1450, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Casablanca', 'country' => 'MAR', 'lat' => 33.5731, 'lng' => -7.5898, 'type' => 'Coastal Natural'],

            // Oceania
            ['name' => 'Port of Melbourne', 'country' => 'AUS', 'lat' => -37.8136, 'lng' => 144.9631, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Sydney', 'country' => 'AUS', 'lat' => -33.8688, 'lng' => 151.2093, 'type' => 'Coastal Natural'],
            ['name' => 'Port of Brisbane', 'country' => 'AUS', 'lat' => -27.4698, 'lng' => 153.0251, 'type' => 'River Natural'],
            ['name' => 'Port of Auckland', 'country' => 'NZL', 'lat' => -36.8509, 'lng' => 174.7645, 'type' => 'Coastal Natural'],
        ];

        $countries = Country::all()->keyBy('cca3');

        foreach ($ports as $portData) {
            $country = $countries->get($portData['country']);

            Port::updateOrCreate(
                ['name' => $portData['name']],
                [
                    'country_id'  => $country?->id,
                    'latitude'    => $portData['lat'],
                    'longitude'   => $portData['lng'],
                    'harbor_type' => $portData['type'],
                ]
            );
        }

        $this->command->info('Berhasil import '.count($ports).' pelabuhan utama dunia.');
    }
}