<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Services\AI\SmartReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReconciliationController extends Controller
{
    protected SmartReconciliationService $reconciliationService;

    public function __construct(SmartReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Page de réconciliation bancaire
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $unreconciledTransactions = BankTransaction::query()
            ->whereHas('bankAccount', function ($query) {
                $query->where('company_id', auth()->user()->currentCompany->id);
            })
            ->where('is_reconciled', false)
            ->orderBy('date', 'desc')
            ->with(['bankAccount'])
            ->paginate(20);

        // Obtenir suggestions pour chaque transaction
        foreach ($unreconciledTransactions as $transaction) {
            $result = $this->reconciliationService->autoReconcile($transaction);
            $transaction->reconciliation_result = $result;
        }

        $stats = $this->reconciliationService->getReconciliationStats('month');

        return view('bank.reconciliation', [
            'transactions' => $unreconciledTransactions,
            'stats' => $stats,
        ]);
    }

    /**
     * Auto-réconcilier une transaction
     *
     * POST /api/bank/reconcile/auto/{transaction}
     *
     * @param BankTransaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoReconcile(BankTransaction $transaction)
    {
        // Vérifier ownership via bank account
        if ($transaction->bankAccount->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée',
            ], 404);
        }

        try {
            $result = $this->reconciliationService->autoReconcile($transaction);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réconciliation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réconcilier manuellement une transaction avec une facture
     *
     * POST /api/bank/reconcile/manual
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualReconcile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|uuid|exists:bank_transactions,id',
            'invoice_id' => 'required|uuid|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = BankTransaction::findOrFail($request->transaction_id);
        $invoice = Invoice::findOrFail($request->invoice_id);

        // Vérifier ownership
        if ($transaction->bankAccount->company_id !== auth()->user()->currentCompany->id ||
            $invoice->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Données non trouvées',
            ], 404);
        }

        // Vérifier que transaction pas déjà réconciliée
        if ($transaction->is_reconciled) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction déjà réconciliée',
            ], 422);
        }

        try {
            $result = $this->reconciliationService->manualReconcile($transaction, $invoice);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Transaction réconciliée avec facture %s',
                    $invoice->invoice_number
                ),
                'payment' => $result['payment'],
                'invoice' => $result['invoice'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réconciliation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réconciliation en masse (batch)
     *
     * POST /api/bank/reconcile/batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchReconcile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'uuid|exists:bank_transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $transactions = BankTransaction::query()
            ->whereHas('bankAccount', function ($query) {
                $query->where('company_id', auth()->user()->currentCompany->id);
            })
            ->whereIn('id', $request->transaction_ids)
            ->where('is_reconciled', false)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune transaction à réconcilier',
            ], 422);
        }

        try {
            $results = $this->reconciliationService->batchReconcile($transactions);

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    '%d transactions traitées: %d réconciliées automatiquement, %d suggestions, %d non matchées',
                    $transactions->count(),
                    $results['auto_matched'],
                    $results['suggestions'],
                    $results['no_match']
                ),
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réconciliation batch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les suggestions de matching pour une transaction
     *
     * GET /api/bank/reconcile/suggestions/{transaction}
     *
     * @param BankTransaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuggestions(BankTransaction $transaction)
    {
        // Vérifier ownership via bank account
        if ($transaction->bankAccount->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée',
            ], 404);
        }

        try {
            $result = $this->reconciliationService->autoReconcile($transaction);

            return response()->json([
                'success' => true,
                'suggestions' => $result['suggestions'] ?? [],
                'auto_validated' => $result['auto_validated'] ?? false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir statistiques de réconciliation
     *
     * GET /api/bank/reconcile/stats
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $period = $request->query('period', 'month');

        if (!in_array($period, ['week', 'month', 'quarter', 'year'])) {
            $period = 'month';
        }

        try {
            $stats = $this->reconciliationService->getReconciliationStats($period);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annuler une réconciliation
     *
     * POST /api/bank/reconcile/undo/{transaction}
     *
     * @param BankTransaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function undoReconciliation(BankTransaction $transaction)
    {
        // Vérifier ownership via bank account
        if ($transaction->bankAccount->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non trouvée',
            ], 404);
        }

        if (!$transaction->is_reconciled) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non réconciliée',
            ], 422);
        }

        try {
            \DB::transaction(function () use ($transaction) {
                // Supprimer le paiement associé
                if ($transaction->invoice_id) {
                    \App\Models\Payment::where('bank_transaction_id', $transaction->id)
                        ->delete();

                    // Mettre à jour statut facture
                    $invoice = Invoice::find($transaction->invoice_id);
                    if ($invoice) {
                        $invoice->updatePaymentStatus();
                    }
                }

                // Annuler réconciliation
                $transaction->update([
                    'is_reconciled' => false,
                    'reconciled_at' => null,
                    'reconciled_by' => null,
                    'invoice_id' => null,
                ]);

                // Log audit
                activity()
                    ->performedOn($transaction)
                    ->causedBy(auth()->user())
                    ->log('undo_reconciliation');
            });

            return response()->json([
                'success' => true,
                'message' => 'Réconciliation annulée',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage(),
            ], 500);
        }
    }
}
