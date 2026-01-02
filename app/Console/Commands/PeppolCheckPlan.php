<?php

namespace App\Console\Commands;

use App\Services\PeppolPlanOptimizer;
use Illuminate\Console\Command;

class PeppolCheckPlan extends Command
{
    protected $signature = 'peppol:check-plan';
    protected $description = 'Vérifier si le plan provider doit être optimisé';

    public function handle()
    {
        $optimizer = app(PeppolPlanOptimizer::class);
        $recommendation = $optimizer->getRecommendation();

        $this->info('=== Analyse du Plan Peppol ===');
        $this->info("Provider: {$recommendation['current']['provider']} / {$recommendation['current']['plan']}");
        $this->info("Volume: {$recommendation['current']['volume']} factures");
        $this->info("Coût: €{$recommendation['current']['cost']}/mois");
        $this->line('');

        if ($recommendation['should_upgrade'] || $recommendation['should_downgrade']) {
            $this->warn('⚠️ ' . $recommendation['reason']);
            $this->info("Optimal: {$recommendation['optimal']['provider_name']} / {$recommendation['optimal']['plan_name']}");
            $this->info("Économies: €" . number_format(abs($recommendation['savings']), 2) . "/mois");
            return Command::SUCCESS;
        }

        $this->info('✓ Plan optimal');
        return Command::SUCCESS;
    }
}
