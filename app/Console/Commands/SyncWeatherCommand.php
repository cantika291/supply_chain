<?php

namespace App\Console\Commands;

use App\Services\WeatherApiService;
use Illuminate\Console\Command;

class SyncWeatherCommand extends Command
{
    protected $signature = 'sync:weather';
    protected $description = 'Sync data cuaca dari Open-Meteo API';

    public function handle(WeatherApiService $service): int
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Sync cuaca dimulai...');
        $result = $service->syncAllWeather();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}