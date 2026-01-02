<?php

namespace App\Console\Commands;

use App\Models\EmailInvoice;
use App\Services\EmailInvoiceProcessor;
use Illuminate\Console\Command;

class ProcessEmailInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-emails
                            {--limit=10 : Nombre maximum d\'emails à traiter}
                            {--auto-create : Créer automatiquement les factures avec confiance suffisante}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traiter les factures reçues par email et créer automatiquement les factures';

    /**
     * Execute the console command.
     */
    public function handle(EmailInvoiceProcessor $processor)
    {
        $this->info('🚀 Traitement des factures reçues par email...');

        $limit = $this->option('limit');
        $autoCreate = $this->option('auto-create');

        // Récupérer les emails en attente
        $emailInvoices = EmailInvoice::pending()
            ->orderBy('email_date', 'asc')
            ->limit($limit)
            ->get();

        if ($emailInvoices->isEmpty()) {
            $this->info('✅ Aucun email en attente de traitement');
            return Command::SUCCESS;
        }

        $this->info("📧 {$emailInvoices->count()} email(s) à traiter");

        $processed = 0;
        $failed = 0;
        $autoCreated = 0;

        $bar = $this->output->createProgressBar($emailInvoices->count());
        $bar->start();

        foreach ($emailInvoices as $emailInvoice) {
            try {
                $result = $processor->processEmailInvoice($emailInvoice, $autoCreate);

                if ($result['success']) {
                    $processed++;
                    if ($result['auto_created'] ?? false) {
                        $autoCreated++;
                    }
                } else {
                    $failed++;
                }

            } catch (\Exception $e) {
                $failed++;
                $this->error("\n❌ Erreur: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Résumé
        $this->info('📊 Résumé du traitement:');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Traités', $processed],
                ['Créés automatiquement', $autoCreated],
                ['En attente validation', $processed - $autoCreated],
                ['Échecs', $failed],
            ]
        );

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
