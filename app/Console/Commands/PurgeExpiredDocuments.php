<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Command pour purger les documents expirés selon les politiques de rétention légale belges
 *
 * Exécution recommandée: mensuelle via CRON
 * Conforme à: AR TVA art. 60, C. soc. art. 3:17, RGPD
 */
class PurgeExpiredDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:purge-expired
                            {--dry-run : Afficher les documents à purger sans les supprimer}
                            {--force : Forcer la suppression sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge les documents expirés selon les politiques de rétention légale (TVA 10 ans, comptable 7 ans, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🗑️  Démarrage purge documents expirés...');

        // Récupération des politiques de rétention
        $policies = DB::table('retention_policies')->get()->keyBy('document_type');

        if ($policies->isEmpty()) {
            $this->error('❌ Aucune politique de rétention trouvée. Exécutez le seeder RetentionPolicySeeder.');
            return Command::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $totalPurged = 0;
        $documentsToArchive = [];

        // ========== PURGE FACTURES ==========
        $this->info('📄 Vérification factures...');
        $invoicePolicy = $policies->get('invoice');

        if ($invoicePolicy && !$invoicePolicy->permanent) {
            $expirationDate = now()->subYears($invoicePolicy->retention_years);

            $expiredInvoices = Invoice::where('created_at', '<', $expirationDate)
                ->whereNull('deleted_at')
                ->get();

            if ($expiredInvoices->isNotEmpty()) {
                $this->info("   Trouvé {$expiredInvoices->count()} facture(s) expirée(s) (> {$invoicePolicy->retention_years} ans)");

                foreach ($expiredInvoices as $invoice) {
                    $documentsToArchive[] = [
                        'type' => 'invoice',
                        'id' => $invoice->id,
                        'reference' => $invoice->invoice_number,
                        'date' => $invoice->created_at,
                        'company_id' => $invoice->company_id,
                    ];
                }

                if (!$dryRun) {
                    if (!$this->option('force') && !$this->confirm("Supprimer {$expiredInvoices->count()} facture(s) ?")) {
                        $this->info('   ⏭️  Ignoré par l\'utilisateur');
                    } else {
                        foreach ($expiredInvoices as $invoice) {
                            // SECURITY: Log avant suppression
                            AuditLog::log('document_purged', Invoice::class, $invoice->id, [
                                'invoice_number' => $invoice->invoice_number,
                                'retention_policy' => $invoicePolicy->legal_basis,
                                'expired_at' => $expirationDate,
                            ]);

                            // Soft delete
                            $invoice->delete();
                        }

                        $totalPurged += $expiredInvoices->count();
                        $this->info("   ✅ {$expiredInvoices->count()} facture(s) supprimée(s)");
                    }
                }
            } else {
                $this->info('   ℹ️  Aucune facture expirée');
            }
        }

        // ========== PURGE ÉCRITURES COMPTABLES ==========
        $this->info('📒 Vérification écritures comptables...');
        $journalPolicy = $policies->get('journal_entry');

        if ($journalPolicy && !$journalPolicy->permanent) {
            $expirationDate = now()->subYears($journalPolicy->retention_years);

            $expiredEntries = JournalEntry::where('entry_date', '<', $expirationDate)
                ->whereNull('deleted_at')
                ->get();

            if ($expiredEntries->isNotEmpty()) {
                $this->info("   Trouvé {$expiredEntries->count()} écriture(s) expirée(s) (> {$journalPolicy->retention_years} ans)");

                foreach ($expiredEntries as $entry) {
                    $documentsToArchive[] = [
                        'type' => 'journal_entry',
                        'id' => $entry->id,
                        'reference' => $entry->reference ?? 'N/A',
                        'date' => $entry->entry_date,
                        'company_id' => $entry->company_id,
                    ];
                }

                if (!$dryRun) {
                    if (!$this->option('force') && !$this->confirm("Supprimer {$expiredEntries->count()} écriture(s) ?")) {
                        $this->info('   ⏭️  Ignoré par l\'utilisateur');
                    } else {
                        foreach ($expiredEntries as $entry) {
                            AuditLog::log('document_purged', JournalEntry::class, $entry->id, [
                                'entry_reference' => $entry->reference,
                                'retention_policy' => $journalPolicy->legal_basis,
                                'expired_at' => $expirationDate,
                            ]);

                            $entry->delete();
                        }

                        $totalPurged += $expiredEntries->count();
                        $this->info("   ✅ {$expiredEntries->count()} écriture(s) supprimée(s)");
                    }
                }
            } else {
                $this->info('   ℹ️  Aucune écriture expirée');
            }
        }

        // ========== PURGE DOCUMENTS PHYSIQUES ==========
        $this->info('📁 Vérification documents physiques...');
        $documentPolicy = $policies->get('expense'); // Documents justificatifs = expenses

        if ($documentPolicy && !$documentPolicy->permanent) {
            $expirationDate = now()->subYears($documentPolicy->retention_years);

            $expiredDocuments = Document::where('created_at', '<', $expirationDate)
                ->whereNull('deleted_at')
                ->get();

            if ($expiredDocuments->isNotEmpty()) {
                $this->info("   Trouvé {$expiredDocuments->count()} document(s) expiré(s) (> {$documentPolicy->retention_years} ans)");

                if (!$dryRun) {
                    if (!$this->option('force') && !$this->confirm("Supprimer {$expiredDocuments->count()} document(s) ?")) {
                        $this->info('   ⏭️  Ignoré par l\'utilisateur');
                    } else {
                        foreach ($expiredDocuments as $document) {
                            // Log before deletion
                            AuditLog::log('document_purged', Document::class, $document->id, [
                                'file_name' => $document->original_filename,
                                'file_path' => $document->file_path,
                                'retention_policy' => $documentPolicy->legal_basis,
                                'expired_at' => $expirationDate,
                            ]);

                            // Delete physical file
                            if (Storage::disk($document->disk)->exists($document->file_path)) {
                                Storage::disk($document->disk)->delete($document->file_path);
                            }

                            // Soft delete database record
                            $document->delete();
                        }

                        $totalPurged += $expiredDocuments->count();
                        $this->info("   ✅ {$expiredDocuments->count()} document(s) supprimé(s)");
                    }
                }
            } else {
                $this->info('   ℹ️  Aucun document expiré');
            }
        }

        // ========== RAPPORT FINAL ==========
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 MODE DRY-RUN - Aucune suppression effectuée');
            $this->info("📊 {$totalPurged} document(s) seraient supprimés");
        } else {
            $this->info("✅ Purge terminée: {$totalPurged} document(s) supprimé(s)");

            // Log global
            Log::info('Documents purge completed', [
                'total_purged' => $totalPurged,
                'executed_at' => now(),
            ]);
        }

        if (!empty($documentsToArchive)) {
            $this->newLine();
            $this->warn('📦 Documents à archiver (export PDF/A recommandé):');
            $this->table(
                ['Type', 'ID', 'Référence', 'Date', 'Company'],
                array_map(fn($doc) => [
                    $doc['type'],
                    $doc['id'],
                    $doc['reference'],
                    $doc['date']->format('Y-m-d'),
                    $doc['company_id'],
                ], $documentsToArchive)
            );
        }

        return Command::SUCCESS;
    }
}
