<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service de génération automatique d'écritures comptables
 *
 * Génère les écritures pour :
 * - Factures clients/fournisseurs
 * - Paiements reçus/effectués
 * - Réconciliations bancaires
 * - Opérations diverses
 */
class AccountingEntryService
{
    /**
     * Comptes par défaut (configurables par entreprise)
     */
    protected array $defaultAccounts = [
        // Clients/Fournisseurs
        'customer_receivable' => '400000',  // Clients
        'supplier_payable' => '440000',      // Fournisseurs

        // Produits/Charges
        'sales_revenue' => '700000',         // Ventes
        'purchase_expense' => '600000',      // Achats

        // TVA
        'vat_collected' => '451000',         // TVA collectée
        'vat_deductible' => '411000',        // TVA déductible

        // Banque
        'bank_account' => '550000',          // Banque

        // Divers
        'rounding_difference' => '658000',   // Écarts d'arrondi
    ];

    /**
     * Seuil pour validation automatique (en €)
     */
    const AUTO_POST_THRESHOLD = 1000.00;

    /**
     * Génère une écriture pour une facture validée
     *
     * @param Invoice $invoice
     * @param bool $autoPost
     * @return JournalEntry|null
     */
    public function generateFromInvoice(Invoice $invoice, bool $autoPost = true): ?JournalEntry
    {
        if ($invoice->journal_entry_id) {
            Log::info('Invoice already has accounting entry', ['invoice_id' => $invoice->id]);
            return $invoice->journalEntry;
        }

        DB::beginTransaction();

        try {
            $isSale = $invoice->type === 'sales';

            // Déterminer le journal
            $journal = $this->getJournalByType($invoice->company_id, $isSale ? 'sale' : 'purchase');

            // Créer l'écriture
            $entry = JournalEntry::create([
                'company_id' => $invoice->company_id,
                'journal_id' => $journal->id,
                'fiscal_year_id' => $this->getCurrentFiscalYear($invoice->company_id)->id,
                'entry_number' => JournalEntry::generateEntryNumber($journal->id),
                'entry_date' => $invoice->invoice_date ?? $invoice->issue_date,
                'accounting_date' => $invoice->invoice_date ?? $invoice->issue_date,
                'reference' => $invoice->invoice_number,
                'description' => sprintf(
                    'Facture %s - %s',
                    $invoice->invoice_number,
                    $invoice->partner->name ?? 'N/A'
                ),
                'source_type' => Invoice::class,
                'source_id' => $invoice->id,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $lineNumber = 1;

            if ($isSale) {
                // VENTE : Client au débit, Produits au crédit

                // Ligne client (débit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $this->getAccount($invoice->company_id, 'customer_receivable')->id,
                    'partner_id' => $invoice->partner_id,
                    'description' => 'Client - ' . $invoice->invoice_number,
                    'debit' => $invoice->total_amount,
                    'credit' => 0,
                    'due_date' => $invoice->due_date,
                ]);

                // Lignes produits (crédit)
                foreach ($invoice->lines as $line) {
                    $accountId = $line->account_id
                        ?? $this->getAccount($invoice->company_id, 'sales_revenue')->id;

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $accountId,
                        'description' => $line->description,
                        'debit' => 0,
                        'credit' => $line->subtotal,
                        'vat_code' => $line->vat_rate ? "BE-{$line->vat_rate}" : null,
                    ]);

                    // TVA collectée (crédit)
                    if ($line->vat_amount > 0) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_number' => $lineNumber++,
                            'account_id' => $this->getAccount($invoice->company_id, 'vat_collected')->id,
                            'description' => "TVA {$line->vat_rate}% - {$line->description}",
                            'debit' => 0,
                            'credit' => $line->vat_amount,
                            'vat_code' => "BE-{$line->vat_rate}",
                            'vat_base' => $line->subtotal,
                            'vat_amount' => $line->vat_amount,
                        ]);
                    }
                }

            } else {
                // ACHAT : Charges au débit, Fournisseur au crédit

                // Lignes charges (débit)
                foreach ($invoice->lines as $line) {
                    $accountId = $line->account_id
                        ?? $this->getAccount($invoice->company_id, 'purchase_expense')->id;

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'line_number' => $lineNumber++,
                        'account_id' => $accountId,
                        'description' => $line->description,
                        'debit' => $line->subtotal,
                        'credit' => 0,
                        'vat_code' => $line->vat_rate ? "BE-{$line->vat_rate}" : null,
                    ]);

                    // TVA déductible (débit)
                    if ($line->vat_amount > 0) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $entry->id,
                            'line_number' => $lineNumber++,
                            'account_id' => $this->getAccount($invoice->company_id, 'vat_deductible')->id,
                            'description' => "TVA {$line->vat_rate}% - {$line->description}",
                            'debit' => $line->vat_amount,
                            'credit' => 0,
                            'vat_code' => "BE-{$line->vat_rate}",
                            'vat_base' => $line->subtotal,
                            'vat_amount' => $line->vat_amount,
                        ]);
                    }
                }

                // Ligne fournisseur (crédit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => $lineNumber++,
                    'account_id' => $this->getAccount($invoice->company_id, 'supplier_payable')->id,
                    'partner_id' => $invoice->partner_id,
                    'description' => 'Fournisseur - ' . $invoice->invoice_number,
                    'debit' => 0,
                    'credit' => $invoice->total_amount,
                    'due_date' => $invoice->due_date,
                ]);
            }

            // Vérifier équilibrage
            if (!$entry->isBalanced()) {
                throw new \Exception('Écriture non équilibrée');
            }

            // Validation automatique selon seuil
            if ($autoPost && abs($invoice->total_amount) <= self::AUTO_POST_THRESHOLD) {
                $entry->post(auth()->user());
            }

            // Lier l'écriture à la facture
            $invoice->update(['journal_entry_id' => $entry->id]);

            DB::commit();

            Log::info('Accounting entry generated from invoice', [
                'invoice_id' => $invoice->id,
                'entry_id' => $entry->id,
                'auto_posted' => $entry->status === 'posted',
            ]);

            return $entry;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate accounting entry from invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Génère une écriture de paiement
     *
     * @param Payment $payment
     * @param bool $autoPost
     * @return JournalEntry|null
     */
    public function generateFromPayment(Payment $payment, bool $autoPost = true): ?JournalEntry
    {
        DB::beginTransaction();

        try {
            $invoice = $payment->invoice;
            $isSale = $invoice->type === 'sales';

            // Journal de banque
            $journal = $this->getJournalByType($payment->company_id, 'bank');

            // Créer l'écriture de paiement
            $entry = JournalEntry::create([
                'company_id' => $payment->company_id,
                'journal_id' => $journal->id,
                'fiscal_year_id' => $this->getCurrentFiscalYear($payment->company_id)->id,
                'entry_number' => JournalEntry::generateEntryNumber($journal->id),
                'entry_date' => $payment->payment_date,
                'accounting_date' => $payment->payment_date,
                'reference' => $payment->reference ?? $invoice->invoice_number,
                'description' => sprintf(
                    'Paiement facture %s - %s',
                    $invoice->invoice_number,
                    $invoice->partner->name ?? 'N/A'
                ),
                'source_type' => Payment::class,
                'source_id' => $payment->id,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            if ($isSale) {
                // Paiement client : Banque débit, Client crédit

                // Banque (débit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => 1,
                    'account_id' => $this->getBankAccount($payment->company_id, $payment->bank_account_id)->id,
                    'description' => 'Encaissement - ' . $invoice->invoice_number,
                    'debit' => $payment->amount,
                    'credit' => 0,
                ]);

                // Client (crédit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => 2,
                    'account_id' => $this->getAccount($payment->company_id, 'customer_receivable')->id,
                    'partner_id' => $invoice->partner_id,
                    'description' => 'Paiement - ' . $invoice->invoice_number,
                    'debit' => 0,
                    'credit' => $payment->amount,
                ]);

            } else {
                // Paiement fournisseur : Fournisseur débit, Banque crédit

                // Fournisseur (débit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => 1,
                    'account_id' => $this->getAccount($payment->company_id, 'supplier_payable')->id,
                    'partner_id' => $invoice->partner_id,
                    'description' => 'Paiement - ' . $invoice->invoice_number,
                    'debit' => $payment->amount,
                    'credit' => 0,
                ]);

                // Banque (crédit)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => 2,
                    'account_id' => $this->getBankAccount($payment->company_id, $payment->bank_account_id)->id,
                    'description' => 'Décaissement - ' . $invoice->invoice_number,
                    'debit' => 0,
                    'credit' => $payment->amount,
                ]);
            }

            // Gérer écart de paiement (escompte, arrondi)
            $difference = abs($invoice->amount_due) - $payment->amount;
            if (abs($difference) > 0.01 && abs($difference) < 10) {
                // Écart d'arrondi
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'line_number' => 3,
                    'account_id' => $this->getAccount($payment->company_id, 'rounding_difference')->id,
                    'description' => 'Écart d\'arrondi',
                    'debit' => $difference > 0 ? 0 : abs($difference),
                    'credit' => $difference > 0 ? $difference : 0,
                ]);
            }

            if (!$entry->isBalanced()) {
                throw new \Exception('Écriture de paiement non équilibrée');
            }

            // Auto-post
            if ($autoPost && $payment->amount <= self::AUTO_POST_THRESHOLD) {
                $entry->post(auth()->user());
            }

            $payment->update(['journal_entry_id' => $entry->id]);

            DB::commit();

            Log::info('Payment accounting entry generated', [
                'payment_id' => $payment->id,
                'entry_id' => $entry->id,
            ]);

            return $entry;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate payment entry', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Génère une écriture depuis une réconciliation bancaire
     *
     * @param BankTransaction $transaction
     * @return JournalEntry|null
     */
    public function generateFromReconciliation(BankTransaction $transaction): ?JournalEntry
    {
        // Si la réconciliation a créé un paiement, l'écriture est déjà générée
        if ($transaction->payment) {
            return $transaction->payment->journalEntry ?? null;
        }

        // Sinon, générer une écriture générique (opération diverse)
        // TODO: Implémenter selon besoins spécifiques

        return null;
    }

    /**
     * Obtient un journal par type
     */
    protected function getJournalByType(string $companyId, string $type): Journal
    {
        $journal = Journal::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$journal) {
            throw new \Exception("No active journal found for type: {$type}");
        }

        return $journal;
    }

    /**
     * Obtient un compte par code
     */
    protected function getAccount(string $companyId, string $accountKey): ChartOfAccount
    {
        $accountCode = $this->defaultAccounts[$accountKey] ?? null;

        if (!$accountCode) {
            throw new \Exception("Unknown account key: {$accountKey}");
        }

        // Chercher par code exact ou par début
        $account = ChartOfAccount::where('company_id', $companyId)
            ->where(function ($q) use ($accountCode) {
                $q->where('account_number', $accountCode)
                  ->orWhere('account_number', 'like', substr($accountCode, 0, 3) . '%');
            })
            ->where('is_active', true)
            ->orderBy('account_number')
            ->first();

        if (!$account) {
            throw new \Exception("Account not found: {$accountCode}");
        }

        return $account;
    }

    /**
     * Obtient le compte bancaire lié
     */
    protected function getBankAccount(string $companyId, ?string $bankAccountId): ChartOfAccount
    {
        if ($bankAccountId) {
            $bankAccount = \App\Models\BankAccount::find($bankAccountId);
            if ($bankAccount && $bankAccount->account_id) {
                return ChartOfAccount::findOrFail($bankAccount->account_id);
            }
        }

        // Compte par défaut
        return $this->getAccount($companyId, 'bank_account');
    }

    /**
     * Obtient l'exercice fiscal courant
     */
    protected function getCurrentFiscalYear(string $companyId): \App\Models\FiscalYear
    {
        $fiscalYear = \App\Models\FiscalYear::where('company_id', $companyId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_closed', false)
            ->first();

        if (!$fiscalYear) {
            throw new \Exception('No active fiscal year found');
        }

        return $fiscalYear;
    }

    /**
     * Valide et comptabilise une écriture
     */
    public function postEntry(JournalEntry $entry): bool
    {
        if (!$entry->isBalanced()) {
            throw new \Exception('Cannot post unbalanced entry');
        }

        if ($entry->status === 'posted') {
            return true;
        }

        return $entry->post(auth()->user());
    }

    /**
     * Génère les écritures manquantes pour une période
     */
    public function generateMissingEntries(string $companyId, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $results = [
            'invoices_processed' => 0,
            'payments_processed' => 0,
            'errors' => [],
        ];

        // Factures sans écriture
        $invoicesQuery = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->whereNull('journal_entry_id');

        if ($from) {
            $invoicesQuery->where('invoice_date', '>=', $from);
        }
        if ($to) {
            $invoicesQuery->where('invoice_date', '<=', $to);
        }

        foreach ($invoicesQuery->get() as $invoice) {
            try {
                $this->generateFromInvoice($invoice);
                $results['invoices_processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Paiements sans écriture
        $paymentsQuery = Payment::where('company_id', $companyId)
            ->whereNull('journal_entry_id');

        if ($from) {
            $paymentsQuery->where('payment_date', '>=', $from);
        }
        if ($to) {
            $paymentsQuery->where('payment_date', '<=', $to);
        }

        foreach ($paymentsQuery->get() as $payment) {
            try {
                $this->generateFromPayment($payment);
                $results['payments_processed']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'type' => 'payment',
                    'id' => $payment->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
