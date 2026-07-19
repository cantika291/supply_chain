<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateApiService;
use Illuminate\Console\Command;

class SyncCurrencyCommand extends Command
{
    protected $signature = 'sync:currency';
    protected $description = 'Sync kurs mata uang dari ExchangeRate-API';

    public function handle(ExchangeRateApiService $service): int
    {
        $this->info('[' . now()->format('Y-m-d H:i:s') . '] Sync kurs dimulai...');
        $result = $service->syncAllRates();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}