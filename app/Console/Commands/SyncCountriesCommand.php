<?php

namespace App\Console\Commands;

use App\Services\CountryApiService;
use App\Services\WorldBankApiService;
use Illuminate\Console\Command;

class SyncCountriesCommand extends Command
{
    protected $signature = 'sync:countries';

    protected $description = 'Sinkronisasi data negara & data ekonomi ke database';

    public function handle(CountryApiService $countryApiService, WorldBankApiService $worldBankApiService): int
    {
        $this->info('Memulai sinkronisasi data negara...');
        $countryResult = $countryApiService->syncAllCountries();

        if ($countryResult['success']) {
            $this->info($countryResult['message']);
        } else {
            $this->error($countryResult['message']);

            return Command::FAILURE;
        }

        $this->info('Memulai sinkronisasi data ekonomi (World Bank)...');
        $economicResult = $worldBankApiService->syncAllEconomicData();

        if ($economicResult['success']) {
            $this->info($economicResult['message']);

            return Command::SUCCESS;
        }

        $this->error($economicResult['message']);

        return Command::FAILURE;
    }
}