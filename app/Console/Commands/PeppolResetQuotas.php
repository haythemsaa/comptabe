<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class PeppolResetQuotas extends Command
{
    protected $signature = 'peppol:reset-quotas';
    protected $description = 'Réinitialiser les quotas Peppol de toutes les entreprises (exécuter le 1er de chaque mois)';

    public function handle()
    {
        $this->info('Réinitialisation des quotas Peppol...');

        $count = 0;
        Company::chunk(100, function ($companies) use (&$count) {
            foreach ($companies as $company) {
                $company->resetPeppolUsage();
                $count++;
            }
        });

        $this->info("✓ Quotas réinitialisés pour {$count} entreprises");

        return Command::SUCCESS;
    }
}
