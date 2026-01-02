<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class RecordPaymentTool extends AbstractTool
{
    public function getName(): string
    {
        return 'record_payment';
    }

    public function getDescription(): string
    {
        return 'Records a payment for an invoice. Use this when a customer has paid an invoice or when you want to mark an invoice as paid.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown (e.g., F-2025-001)',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Payment amount. If not specified, will mark the full amount_due as paid.',
                ],
                'payment_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Date of payment (YYYY-MM-DD). Defaults to today.',
                ],
                'payment_method' => [
                    'type' => 'string',
                    'enum' => ['bank_transfer', 'cash', 'card', 'check', 'other'],
                    'description' => 'Payment method (default: bank_transfer)',
                ],
                'payment_reference' => [
                    'type' => 'string',
                    'description' => 'Payment reference or transaction ID',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Optional notes about the payment',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        // Require confirmation before recording payment
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        return DB::transaction(function () use ($input, $context) {
            // Find invoice
            $invoice = $this->findInvoice($input, $context);

            if (!$invoice) {
                return [
                    'error' => 'Invoice not found. Please provide a valid invoice_id or invoice_number.',
                    'suggestion' => 'Use the read_invoices tool to find the invoice first.',
                ];
            }

            // Check if invoice is already fully paid
            if ($invoice->status === 'paid') {
                return [
                    'error' => "La facture {$invoice->invoice_number} est déjà entièrement payée.",
                    'invoice' => [
                        'invoice_number' => $invoice->invoice_number,
                        'total' => (float) $invoice->total_incl_vat,
                        'paid' => (float) $invoice->amount_paid,
                        'due' => (float) $invoice->amount_due,
                    ],
                ];
            }

            // Determine payment amount
            $paymentAmount = $input['amount'] ?? $invoice->amount_due;

            // Validate amount
            if ($paymentAmount <= 0) {
                return [
                    'error' => 'Le montant du paiement doit être supérieur à 0.',
                ];
            }

            if ($paymentAmount > $invoice->amount_due) {
                return [
                    'error' => "Le montant du paiement ({$paymentAmount}€) est supérieur au montant dû ({$invoice->amount_due}€).",
                    'suggestion' => 'Vérifiez le montant ou enregistrez un paiement partiel.',
                ];
            }

            // Update invoice
            $invoice->amount_paid = ($invoice->amount_paid ?? 0) + $paymentAmount;
            $invoice->amount_due = $invoice->total_incl_vat - $invoice->amount_paid;

            // Update payment metadata
            if (!empty($input['payment_method'])) {
                $invoice->payment_method = $input['payment_method'];
            }

            if (!empty($input['payment_reference'])) {
                $invoice->payment_reference = $input['payment_reference'];
            }

            // Update status
            if ($invoice->amount_due <= 0.01) { // Allow for rounding
                $invoice->status = 'paid';
                $invoice->amount_due = 0; // Ensure it's exactly 0
            } elseif ($invoice->amount_paid > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            // Prepare response message
            $statusMessage = $invoice->status === 'paid'
                ? 'entièrement payée'
                : 'partiellement payée';

            return [
                'success' => true,
                'message' => "Paiement de {$paymentAmount}€ enregistré. Facture {$invoice->invoice_number} {$statusMessage}.",
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $invoice->partner->name ?? null,
                    'status' => $invoice->status,
                    'total_incl_vat' => (float) $invoice->total_incl_vat,
                    'amount_paid' => (float) $invoice->amount_paid,
                    'amount_due' => (float) $invoice->amount_due,
                    'currency' => 'EUR',
                ],
                'payment' => [
                    'amount' => (float) $paymentAmount,
                    'date' => $input['payment_date'] ?? now()->format('Y-m-d'),
                    'method' => $input['payment_method'] ?? 'bank_transfer',
                    'reference' => $input['payment_reference'] ?? null,
                ],
            ];
        });
    }

    /**
     * Find invoice by ID or number.
     */
    protected function findInvoice(array $input, ToolContext $context): ?Invoice
    {
        // Try to find by ID first
        if (!empty($input['invoice_id'])) {
            return Invoice::where('id', $input['invoice_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        // Try to find by invoice number
        if (!empty($input['invoice_number'])) {
            return Invoice::where('company_id', $context->company->id)
                ->where('invoice_number', $input['invoice_number'])
                ->first();
        }

        return null;
    }
}
