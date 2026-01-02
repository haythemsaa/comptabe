<?php

namespace App\Services\AI;

use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartReconciliationService
{
    /**
     * Réconciliation automatique avec scoring IA multi-critères
     *
     * @param BankTransaction $transaction
     * @return array
     */
    public function autoReconcile(BankTransaction $transaction): array
    {
        // Ne pas réconcilier si déjà fait
        if ($transaction->is_reconciled) {
            return [
                'matched' => false,
                'reason' => 'already_reconciled',
                'message' => 'Transaction déjà réconciliée',
            ];
        }

        // Trouver candidats
        $candidates = $this->findCandidates($transaction);

        if ($candidates->isEmpty()) {
            return [
                'matched' => false,
                'reason' => 'no_candidates',
                'message' => 'Aucune facture correspondante trouvée',
            ];
        }

        // Scorer les candidats
        $scored = $this->scoreMatches($candidates, $transaction);

        $best = $scored->first();

        // Auto-valider si confiance >= 95%
        if ($best['confidence'] >= 0.95) {
            $result = $this->executeReconciliation($transaction, $best['invoice']);

            return [
                'matched' => true,
                'auto_validated' => true,
                'confidence' => $best['confidence'],
                'invoice' => $best['invoice'],
                'payment' => $result['payment'],
                'message' => sprintf(
                    'Réconcilié automatiquement avec facture %s (confiance: %d%%)',
                    $best['invoice']->invoice_number,
                    round($best['confidence'] * 100)
                ),
            ];
        }

        // Sinon, suggérer à l'utilisateur
        return [
            'matched' => false,
            'requires_confirmation' => true,
            'suggestions' => $scored->take(3)->values(),
            'message' => sprintf(
                '%d correspondance(s) trouvée(s), validation manuelle requise',
                $scored->count()
            ),
        ];
    }

    /**
     * Trouver factures candidates pour matching
     *
     * @param BankTransaction $transaction
     * @return Collection
     */
    private function findCandidates(BankTransaction $transaction): Collection
    {
        $amount = abs($transaction->amount);

        // Recherche intelligente
        return Invoice::query()
            ->where('company_id', Company::current()->id)
            ->where('status', '!=', 'cancelled')
            ->where('amount_due', '>', 0) // Encore des montants impayés
            ->where(function ($q) use ($amount, $transaction) {
                // 1. Montant exact ou ±5%
                $q->whereBetween('amount_due', [
                    $amount * 0.95,
                    $amount * 1.05,
                ])
                // 2. Date proximité ±60 jours
                ->whereBetween('due_date', [
                    $transaction->date->copy()->subDays(60),
                    $transaction->date->copy()->addDays(60),
                ]);
            })
            ->with(['partner', 'payments'])
            ->get();
    }

    /**
     * Scorer les matches avec pondération
     *
     * @param Collection $candidates
     * @param BankTransaction $transaction
     * @return Collection
     */
    private function scoreMatches(Collection $candidates, BankTransaction $transaction): Collection
    {
        return $candidates->map(function ($invoice) use ($transaction) {
            $score = 0;
            $details = [];

            // 1. Montant exact (40 points)
            $amountDiff = abs($invoice->amount_due - abs($transaction->amount));
            if ($amountDiff < 0.01) {
                $score += 40;
                $details['amount'] = ['points' => 40, 'match' => 'exact'];
            } elseif ($amountDiff < ($invoice->amount_due * 0.01)) {
                // ±1%
                $score += 35;
                $details['amount'] = ['points' => 35, 'match' => 'très proche'];
            } elseif ($amountDiff < ($invoice->amount_due * 0.05)) {
                // ±5%
                $score += 25;
                $details['amount'] = ['points' => 25, 'match' => 'proche'];
            }

            // 2. Communication structurée belge (30 points)
            if ($invoice->structured_communication && $transaction->communication) {
                $match = $this->matchStructuredCommunication(
                    $transaction->communication,
                    $invoice->structured_communication
                );

                if ($match === 'exact') {
                    $score += 30;
                    $details['communication'] = ['points' => 30, 'match' => 'exact'];
                } elseif ($match === 'partial') {
                    $score += 15;
                    $details['communication'] = ['points' => 15, 'match' => 'partiel'];
                }
            }

            // 3. IBAN correspondance (15 points)
            if ($invoice->partner && $invoice->partner->iban && $transaction->counterparty_iban) {
                $invoiceIban = preg_replace('/\s+/', '', strtoupper($invoice->partner->iban));
                $transactionIban = preg_replace('/\s+/', '', strtoupper($transaction->counterparty_iban));

                if ($invoiceIban === $transactionIban) {
                    $score += 15;
                    $details['iban'] = ['points' => 15, 'match' => 'exact'];
                }
            }

            // 4. Date proximité (10 points max)
            $daysDiff = abs($transaction->date->diffInDays($invoice->due_date, false));
            $datePoints = max(0, 10 - ($daysDiff * 0.2));
            $score += $datePoints;
            $details['date'] = [
                'points' => round($datePoints, 1),
                'days_diff' => $daysDiff,
            ];

            // 5. Nom contrepartie fuzzy match (5 points)
            if ($invoice->partner && $transaction->counterparty_name) {
                $similarity = $this->calculateSimilarity(
                    $invoice->partner->name,
                    $transaction->counterparty_name
                );

                $namePoints = $similarity * 5;
                $score += $namePoints;
                $details['name'] = [
                    'points' => round($namePoints, 1),
                    'similarity' => round($similarity * 100) . '%',
                ];
            }

            // 6. Historique de paiement (bonus 5 points)
            if ($this->hasPaymentHistory($invoice->partner, $transaction)) {
                $score += 5;
                $details['history'] = ['points' => 5, 'match' => 'historique positif'];
            }

            return [
                'invoice' => $invoice,
                'score' => round($score, 2),
                'confidence' => round($score / 105, 4), // Max 105 points
                'details' => $details,
            ];
        })
        ->sortByDesc('score')
        ->values();
    }

    /**
     * Matching communication structurée belge
     * Format: +++XXX/XXXX/XXXXX+++
     *
     * @param string $transactionComm
     * @param string|null $invoiceComm
     * @return string
     */
    private function matchStructuredCommunication(
        string $transactionComm,
        ?string $invoiceComm
    ): string {
        if (!$invoiceComm) {
            return 'none';
        }

        // Nettoyer (enlever +++, espaces, /, tirets)
        $cleanTransaction = preg_replace('/[^0-9]/', '', $transactionComm);
        $cleanInvoice = preg_replace('/[^0-9]/', '', $invoiceComm);

        if ($cleanTransaction === $cleanInvoice) {
            return 'exact';
        }

        // Vérifier si communication structurée contenue dans transaction
        if (strlen($cleanInvoice) > 0 && strpos($cleanTransaction, $cleanInvoice) !== false) {
            return 'partial';
        }

        return 'none';
    }

    /**
     * Calculer similarité entre deux chaînes (Levenshtein normalisé)
     *
     * @param string $str1
     * @param string $str2
     * @return float
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === $str2) {
            return 1.0;
        }

        $maxLength = max(strlen($str1), strlen($str2));
        if ($maxLength === 0) {
            return 0.0;
        }

        $levenshtein = levenshtein($str1, $str2);
        return 1 - ($levenshtein / $maxLength);
    }

    /**
     * Vérifier historique de paiement du partenaire
     *
     * @param mixed $partner
     * @param BankTransaction $transaction
     * @return bool
     */
    private function hasPaymentHistory($partner, BankTransaction $transaction): bool
    {
        if (!$partner || !$transaction->counterparty_iban) {
            return false;
        }

        // Vérifier si ce partenaire a déjà payé depuis cet IBAN
        $previousPayments = Payment::query()
            ->whereHas('invoice', function ($q) use ($partner) {
                $q->where('partner_id', $partner->id);
            })
            ->whereHas('bankTransaction', function ($q) use ($transaction) {
                $q->where('counterparty_iban', $transaction->counterparty_iban);
            })
            ->exists();

        return $previousPayments;
    }

    /**
     * Exécuter la réconciliation
     *
     * @param BankTransaction $transaction
     * @param Invoice $invoice
     * @return array
     */
    public function executeReconciliation(
        BankTransaction $transaction,
        Invoice $invoice
    ): array {
        return DB::transaction(function () use ($transaction, $invoice) {
            // Créer paiement
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'amount' => abs($transaction->amount),
                'payment_date' => $transaction->date,
                'payment_method' => 'bank_transfer',
                'reference' => $transaction->communication ?? 'Virement bancaire',
                'bank_transaction_id' => $transaction->id,
                'notes' => sprintf(
                    'Réconciliation automatique - Transaction: %s',
                    $transaction->reference ?? $transaction->id
                ),
            ]);

            // Marquer transaction comme réconciliée
            $transaction->update([
                'is_reconciled' => true,
                'reconciled_at' => now(),
                'reconciled_by' => auth()->id(),
                'invoice_id' => $invoice->id,
            ]);

            // Mettre à jour le statut de la facture
            $invoice->updatePaymentStatus();

            // Log audit
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $transaction->amount,
                    'auto_matched' => true,
                ])
                ->log('auto_reconciliation');

            return [
                'payment' => $payment,
                'invoice' => $invoice->fresh(),
                'transaction' => $transaction->fresh(),
            ];
        });
    }

    /**
     * Réconciliation manuelle avec apprentissage
     *
     * @param BankTransaction $transaction
     * @param Invoice $invoice
     * @return array
     */
    public function manualReconcile(
        BankTransaction $transaction,
        Invoice $invoice
    ): array {
        // Exécuter réconciliation
        $result = $this->executeReconciliation($transaction, $invoice);

        // Apprendre du matching manuel pour améliorer l'IA
        $this->learnFromManualReconciliation($transaction, $invoice);

        return $result;
    }

    /**
     * Apprentissage automatique des patterns de réconciliation
     *
     * @param BankTransaction $transaction
     * @param Invoice $invoice
     * @return void
     */
    private function learnFromManualReconciliation(
        BankTransaction $transaction,
        Invoice $invoice
    ): void {
        // Cette fonction pourrait stocker des patterns pour améliorer
        // le scoring au fil du temps (ML basique)

        // Pour l'instant, on log simplement pour analyse future
        Log::info('Manual reconciliation learned', [
            'company_id' => Company::current()->id,
            'partner_id' => $invoice->partner_id,
            'amount_pattern' => abs($transaction->amount),
            'communication_pattern' => $transaction->communication,
            'iban_pattern' => $transaction->counterparty_iban,
            'days_diff' => $transaction->date->diffInDays($invoice->due_date),
        ]);
    }

    /**
     * Obtenir statistiques de réconciliation
     *
     * @param string|null $period
     * @return array
     */
    public function getReconciliationStats(?string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        $total = BankTransaction::query()
            ->where('company_id', Company::current()->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        $reconciled = BankTransaction::query()
            ->where('company_id', Company::current()->id)
            ->where('created_at', '>=', $startDate)
            ->where('is_reconciled', true)
            ->count();

        $autoReconciled = BankTransaction::query()
            ->where('company_id', Company::current()->id)
            ->where('created_at', '>=', $startDate)
            ->where('is_reconciled', true)
            ->whereNotNull('reconciled_at')
            ->whereHas('activityLogs', function ($q) {
                $q->where('description', 'auto_reconciliation');
            })
            ->count();

        $rate = $total > 0 ? ($reconciled / $total) * 100 : 0;
        $autoRate = $reconciled > 0 ? ($autoReconciled / $reconciled) * 100 : 0;

        return [
            'period' => $period,
            'total_transactions' => $total,
            'reconciled' => $reconciled,
            'auto_reconciled' => $autoReconciled,
            'pending' => $total - $reconciled,
            'reconciliation_rate' => round($rate, 1),
            'auto_reconciliation_rate' => round($autoRate, 1),
            'manual_reconciled' => $reconciled - $autoReconciled,
        ];
    }

    /**
     * Réconciliation en masse (batch)
     *
     * @param Collection $transactions
     * @return array
     */
    public function batchReconcile(Collection $transactions): array
    {
        $results = [
            'auto_matched' => 0,
            'suggestions' => 0,
            'no_match' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($transactions as $transaction) {
            try {
                $result = $this->autoReconcile($transaction);

                if ($result['matched'] && ($result['auto_validated'] ?? false)) {
                    $results['auto_matched']++;
                } elseif ($result['requires_confirmation'] ?? false) {
                    $results['suggestions']++;
                } else {
                    $results['no_match']++;
                }

                $results['details'][] = [
                    'transaction_id' => $transaction->id,
                    'result' => $result,
                ];
            } catch (\Exception $e) {
                $results['errors']++;
                $results['details'][] = [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ];

                Log::error('Batch reconciliation error', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
