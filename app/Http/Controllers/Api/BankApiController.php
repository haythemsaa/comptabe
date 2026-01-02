<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankApiController extends Controller
{
    /**
     * List bank transactions.
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = BankTransaction::with('bankAccount:id,name,iban');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $transactions = $query->orderByDesc('date')
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get match suggestions for a transaction.
     */
    public function matchSuggestions(BankTransaction $transaction): JsonResponse
    {
        $suggestions = [];

        // Find invoices with matching amount
        $invoices = Invoice::where('amount_due', abs($transaction->amount))
            ->whereIn('status', ['sent', 'received', 'partial'])
            ->limit(5)
            ->get(['id', 'invoice_number', 'partner_id', 'total_incl_vat', 'amount_due']);

        foreach ($invoices as $invoice) {
            $suggestions[] = [
                'type' => 'invoice',
                'id' => $invoice->id,
                'reference' => $invoice->invoice_number,
                'amount' => $invoice->amount_due,
                'confidence' => 90,
            ];
        }

        // Find by structured communication
        if ($transaction->structured_communication) {
            $invoice = Invoice::where('structured_communication', $transaction->structured_communication)
                ->first(['id', 'invoice_number', 'total_incl_vat', 'amount_due']);

            if ($invoice) {
                array_unshift($suggestions, [
                    'type' => 'invoice',
                    'id' => $invoice->id,
                    'reference' => $invoice->invoice_number,
                    'amount' => $invoice->amount_due,
                    'confidence' => 100,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Match transaction with an invoice.
     */
    public function match(Request $request, BankTransaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        $transaction->update([
            'matched_invoice_id' => $invoice->id,
            'status' => 'matched',
            'matched_at' => now(),
        ]);

        // Update invoice payment
        $invoice->amount_paid += abs($transaction->amount);
        if ($invoice->amount_paid >= $invoice->total_incl_vat) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partial';
        }
        $invoice->amount_due = $invoice->total_incl_vat - $invoice->amount_paid;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaction matched successfully',
        ]);
    }

    /**
     * Ignore a transaction.
     */
    public function ignore(BankTransaction $transaction): JsonResponse
    {
        $transaction->update([
            'status' => 'ignored',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction ignored',
        ]);
    }
}
