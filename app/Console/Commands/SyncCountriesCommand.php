<?php

namespace App\Console\Commands;

use App\Services\CountryApiService;
use App\Services\ExchangeRateApiService;
use App\Services\GNewsApiService;
use App\Services\WeatherApiService;
use App\Services\WorldBankApiService;
use Illuminate\Console\Command;

class SyncCountriesCommand extends Command
{
    protected $signature = 'sync:countries';

    protected $description = 'Sinkronisasi data negara, ekonomi, dan cuaca ke database';

    public function handle(
        CountryApiService $countryApiService,
        WorldBankApiService $worldBankApiService,
        WeatherApiService $weatherApiService,
        ExchangeRateApiService $exchangeRateApiService,
        GNewsApiService $gNewsApiService
    ): int {
        $this->info('Memulai sinkronisasi data negara...');
        $countryResult = $countryApiService->syncAllCountries();
        $this->info($countryResult['message']);

        if (! $countryResult['success']) {
            return Command::FAILURE;
        }

        $this->info('Memulai sinkronisasi data ekonomi (World Bank)...');
        $economicResult = $worldBankApiService->syncAllEconomicData();
        $this->info($economicResult['message']);

        $this->info('Memulai sinkronisasi cuaca (Open-Meteo)... Ini mungkin butuh waktu ~30 detik.');
        $weatherResult = $weatherApiService->syncAllWeather();
        $this->info($weatherResult['message']);

        $this->info('Memulai sinkronisasi kurs mata uang (ExchangeRate-API)...');
        $rateResult = $exchangeRateApiService->syncAllRates();
        $this->info($rateResult['message']);

        $this->info('Memulai sinkronisasi berita (GNews)...');
        $newsResult = $gNewsApiService->syncAllNews();
        $this->info($newsResult['message']);

        return Command::SUCCESS;
    }
}