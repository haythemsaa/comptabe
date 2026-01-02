<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Services\AI\SmartReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankReconciliationController extends Controller
{
    public function __construct(
        protected SmartReconciliationService $reconciliationService
    ) {}

    /**
     * Obtenir suggestions de réconciliation pour une transaction
     *
     * GET /api/reconciliation/suggestions/{transaction}
     */
    public function getSuggestions(BankTransaction $transaction): JsonResponse
    {
        try {
            $result = $this->reconciliationService->autoReconcile($transaction);

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('reconciledInvoice'),
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de correspondances',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réconcilier manuellement une transaction avec une facture
     *
     * POST /api/reconciliation/reconcile
     * Body: { transaction_id, invoice_id }
     */
    public function reconcile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:bank_transactions,id',
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $transaction = BankTransaction::findOrFail($request->transaction_id);
            $invoice = Invoice::findOrFail($request->invoice_id);

            // Vérifier que la transaction n'est pas déjà réconciliée
            if ($transaction->is_reconciled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette transaction est déjà réconciliée',
                ], 422);
            }

            // Vérifier tenant isolation
            if ($transaction->company_id !== auth()->user()->current_company_id
                || $invoice->company_id !== auth()->user()->current_company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            $result = $this->reconciliationService->manualReconcile($transaction, $invoice);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Transaction réconciliée avec la facture %s',
                    $invoice->invoice_number
                ),
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réconciliation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annuler une réconciliation
     *
     * DELETE /api/reconciliation/unreconcile/{transaction}
     */
    public function unreconcile(BankTransaction $transaction): JsonResponse
    {
        try {
            // Vérifier tenant isolation
            if ($transaction->company_id !== auth()->user()->current_company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            if (!$transaction->is_reconciled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette transaction n\'est pas réconciliée',
                ], 422);
            }

            DB::transaction(function () use ($transaction) {
                // Récupérer le paiement lié
                $payment = $transaction->payment;
                $invoice = $payment?->invoice;

                // Supprimer le paiement
                if ($payment) {
                    $payment->delete();
                }

                // Marquer transaction comme non réconciliée
                $transaction->update([
                    'is_reconciled' => false,
                    'reconciled_at' => null,
                    'reconciled_by' => null,
                    'invoice_id' => null,
                ]);

                // Mettre à jour le statut de la facture
                if ($invoice) {
                    $invoice->updatePaymentStatus();
                }

                // Log audit
                activity()
                    ->performedOn($transaction)
                    ->causedBy(auth()->user())
                    ->log('unreconciliation');
            });

            return response()->json([
                'success' => true,
                'message' => 'Réconciliation annulée avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réconciliation automatique en masse
     *
     * POST /api/reconciliation/batch
     * Body: { transaction_ids?: array, auto_only?: bool }
     */
    public function batchReconcile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_ids' => 'nullable|array',
            'transaction_ids.*' => 'exists:bank_transactions,id',
            'auto_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $companyId = auth()->user()->current_company_id;

            // Si IDs fournis, utiliser uniquement ceux-là
            if ($request->has('transaction_ids')) {
                $transactions = BankTransaction::whereIn('id', $request->transaction_ids)
                    ->where('company_id', $companyId)
                    ->where('is_reconciled', false)
                    ->get();
            } else {
                // Sinon, toutes les transactions non réconciliées
                $transactions = BankTransaction::where('company_id', $companyId)
                    ->where('is_reconciled', false)
                    ->orderBy('date', 'desc')
                    ->limit(100) // Limiter à 100 pour éviter timeout
                    ->get();
            }

            $result = $this->reconciliationService->batchReconcile($transactions);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    '%d transaction(s) réconciliée(s) automatiquement',
                    $result['auto_matched']
                ),
                'stats' => [
                    'processed' => $transactions->count(),
                    'auto_matched' => $result['auto_matched'],
                    'suggestions' => $result['suggestions'],
                    'no_match' => $result['no_match'],
                    'errors' => $result['errors'],
                ],
                'details' => $request->boolean('include_details') ? $result['details'] : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réconciliation en masse',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir statistiques de réconciliation
     *
     * GET /api/reconciliation/stats?period=month
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $period = $request->input('period', 'month');

            if (!in_array($period, ['week', 'month', 'quarter', 'year'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Période invalide (week, month, quarter, year)',
                ], 422);
            }

            $stats = $this->reconciliationService->getReconciliationStats($period);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir liste des transactions non réconciliées
     *
     * GET /api/reconciliation/pending
     */
    public function getPending(Request $request): JsonResponse
    {
        try {
            $companyId = auth()->user()->current_company_id;

            $query = BankTransaction::where('company_id', $companyId)
                ->where('is_reconciled', false)
                ->orderBy('date', 'desc');

            // Filtres optionnels
            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            if ($request->has('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }

            if ($request->has('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }

            $transactions = $query->paginate($request->input('per_page', 20));

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir détails d'une transaction avec suggestions
     *
     * GET /api/reconciliation/transaction/{transaction}
     */
    public function getTransactionDetails(BankTransaction $transaction): JsonResponse
    {
        try {
            // Vérifier tenant isolation
            if ($transaction->company_id !== auth()->user()->current_company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            $suggestions = null;

            // Si pas encore réconciliée, obtenir suggestions
            if (!$transaction->is_reconciled) {
                $result = $this->reconciliationService->autoReconcile($transaction);
                $suggestions = $result['suggestions'] ?? [];
            }

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load(['reconciledInvoice', 'payment']),
                'suggestions' => $suggestions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
