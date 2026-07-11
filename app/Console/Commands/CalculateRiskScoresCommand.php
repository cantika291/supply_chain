<?php

namespace App\Console\Commands;

use App\Services\RiskScoringService;
use Illuminate\Console\Command;

class CalculateRiskScoresCommand extends Command
{
    protected $signature = 'risk:calculate';

    protected $description = 'Hitung risk score untuk semua negara berdasarkan data terkini';

    public function handle(RiskScoringService $riskScoringService): int
    {
        $this->info('Menghitung risk score untuk semua negara...');

        $result = $riskScoringService->calculateAllRiskScores();

        $this->info($result['message']);

        return Command::SUCCESS;
    }
}