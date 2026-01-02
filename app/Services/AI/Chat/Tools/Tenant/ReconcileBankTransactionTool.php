<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\BankTransaction;
use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class ReconcileBankTransactionTool extends AbstractTool
{
    public function getName(): string
    {
        return 'reconcile_bank_transaction';
    }

    public function getDescription(): string
    {
        return 'Reconciles (matches) a bank transaction with an invoice. Use this when a payment has been received and you want to link it to the corresponding invoice.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'transaction_id' => [
                    'type' => 'string',
                    'description' => 'ID of the bank transaction',
                ],
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice to match with',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Amount to reconcile (if partial payment)',
                ],
            ],
            'required' => ['transaction_id'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        return DB::transaction(function () use ($input, $context) {
            // Find transaction
            $transaction = BankTransaction::where('id', $input['transaction_id'])
                ->whereHas('bankAccount', function ($q) use ($context) {
                    $q->where('company_id', $context->company->id);
                })
                ->first();

            if (!$transaction) {
                return [
                    'error' => 'Transaction bancaire introuvable.',
                ];
            }

            // Check if already reconciled
            if ($transaction->status === 'reconciled') {
                return [
                    'warning' => 'Cette transaction est déjà rapprochée.',
                    'transaction' => [
                        'id' => $transaction->id,
                        'amount' => (float) $transaction->amount,
                        'invoice_id' => $transaction->invoice_id,
                    ],
                ];
            }

            // Find invoice
            $invoice = null;
            if (!empty($input['invoice_id'])) {
                $invoice = Invoice::where('id', $input['invoice_id'])
                    ->where('company_id', $context->company->id)
                    ->first();
            } elseif (!empty($input['invoice_number'])) {
                $invoice = Invoice::where('invoice_number', $input['invoice_number'])
                    ->where('company_id', $context->company->id)
                    ->first();
            }

            if (!$invoice) {
                return [
                    'error' => 'Facture introuvable. Spécifiez invoice_id ou invoice_number.',
                ];
            }

            // Determine amount to reconcile
            $reconcileAmount = $input['amount'] ?? abs($transaction->amount);

            // Validate amount
            if ($reconcileAmount > $invoice->amount_due) {
                return [
                    'error' => "Le montant ({$reconcileAmount}€) est supérieur au montant dû ({$invoice->amount_due}€).",
                ];
            }

            // Update transaction
            $transaction->invoice_id = $invoice->id;
            $transaction->status = 'reconciled';
            $transaction->reconciled_at = now();
            $transaction->reconciled_by = $context->user->id;
            $transaction->save();

            // Record payment on invoice
            $invoice->amount_paid = ($invoice->amount_paid ?? 0) + $reconcileAmount;
            $invoice->amount_due = $invoice->total_incl_vat - $invoice->amount_paid;

            // Update invoice status
            if ($invoice->amount_due <= 0.01) {
                $invoice->status = 'paid';
                $invoice->amount_due = 0;
            } elseif ($invoice->amount_paid > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            return [
                'success' => true,
                'message' => "Transaction rapprochée avec facture {$invoice->invoice_number}",
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => (float) $transaction->amount,
                    'date' => $transaction->date->format('d/m/Y'),
                    'description' => $transaction->description,
                    'status' => 'reconciled',
                ],
                'invoice' => [
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'total_incl_vat' => (float) $invoice->total_incl_vat,
                    'amount_paid' => (float) $invoice->amount_paid,
                    'amount_due' => (float) $invoice->amount_due,
                ],
            ];
        });
    }
}
