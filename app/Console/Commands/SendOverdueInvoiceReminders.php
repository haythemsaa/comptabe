<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Command pour envoyer des rappels automatiques pour les factures en retard
 *
 * Exécution recommandée: quotidienne via CRON (matin)
 */
class SendOverdueInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-overdue-reminders
                            {--company= : ID d\'une company spécifique}
                            {--dry-run : Afficher les factures en retard sans envoyer de notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des notifications pour les factures en retard de paiement';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📧 Démarrage envoi rappels factures impayées...');

        $dryRun = $this->option('dry-run');
        $companyId = $this->option('company');

        // Récupérer les factures en retard
        $overdueInvoices = Invoice::query()
            ->where('type', 'out') // Seulement factures de vente
            ->whereIn('status', ['sent', 'partial']) // Envoyées mais pas payées
            ->where('due_date', '<', now()) // En retard
            ->with(['partner', 'company'])
            ->when($companyId, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            })
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ Aucune facture en retard. Excellent!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  {$overdueInvoices->count()} facture(s) en retard trouvée(s)");

        // Grouper par company
        $groupedByCompany = $overdueInvoices->groupBy('company_id');

        $notificationsSent = 0;

        foreach ($groupedByCompany as $companyId => $invoices) {
            $company = $invoices->first()->company;

            if (!$company) {
                $this->error("Company {$companyId} not found. Skipping.");
                continue;
            }

            // Statistiques
            $totalAmount = $invoices->sum('amount_due');
            $count = $invoices->count();
            $avgDaysOverdue = round($invoices->avg(function ($invoice) {
                return now()->diffInDays($invoice->due_date);
            }));

            $oldestInvoice = $invoices->sortBy('due_date')->first();

            $this->info("📊 Company: {$company->name}");
            $this->info("   - {$count} facture(s) en retard");
            $this->info("   - Total: " . number_format($totalAmount, 2, ',', ' ') . " €");
            $this->info("   - Retard moyen: {$avgDaysOverdue} jours");

            if ($dryRun) {
                $this->warn('   [DRY-RUN] Notification non envoyée');
                $this->table(
                    ['Facture', 'Client', 'Échéance', 'Retard (jours)', 'Montant'],
                    $invoices->map(function ($invoice) {
                        return [
                            $invoice->invoice_number,
                            $invoice->partner->name,
                            $invoice->due_date->format('d/m/Y'),
                            now()->diffInDays($invoice->due_date),
                            number_format($invoice->amount_due, 2, ',', ' ') . ' €',
                        ];
                    })->toArray()
                );
                continue;
            }

            // Envoyer notification aux propriétaires et admins de la company
            $recipients = $company->users()
                ->wherePivotIn('role', ['owner', 'admin'])
                ->get();

            if ($recipients->isEmpty()) {
                $this->warn("   ⚠️  Aucun destinataire (owner/admin) pour {$company->name}");
                continue;
            }

            foreach ($recipients as $user) {
                try {
                    $user->notify(new InvoiceOverdueNotification(
                        overdueCount: $count,
                        totalAmount: $totalAmount,
                        avgDaysOverdue: $avgDaysOverdue,
                        oldestInvoice: $oldestInvoice
                    ));

                    $this->info("   ✅ Notification envoyée à {$user->email}");
                    $notificationsSent++;

                } catch (\Exception $e) {
                    $this->error("   ❌ Erreur envoi à {$user->email}: {$e->getMessage()}");
                    Log::error('Failed to send overdue invoice notification', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->warn('🔍 MODE DRY-RUN - Aucune notification envoyée');
        } else {
            $this->info("✅ {$notificationsSent} notification(s) envoyée(s)");

            // Log global
            Log::info('Overdue invoice reminders sent', [
                'invoices_count' => $overdueInvoices->count(),
                'companies_count' => $groupedByCompany->count(),
                'notifications_sent' => $notificationsSent,
            ]);
        }

        return Command::SUCCESS;
    }
}
