<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Quote;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class ConvertQuoteToInvoiceTool extends AbstractTool
{
    public function getName(): string
    {
        return 'convert_quote_to_invoice';
    }

    public function getDescription(): string
    {
        return 'Converts an accepted quote (devis) into an invoice. Use this when the customer has accepted a quote and you want to create an invoice from it.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'quote_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the quote to convert',
                ],
                'quote_number' => [
                    'type' => 'string',
                    'description' => 'Quote number if quote_id is unknown (e.g., DEV-2025-001)',
                ],
                'invoice_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Invoice date (YYYY-MM-DD). Defaults to today.',
                ],
                'due_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Payment due date (YYYY-MM-DD). If not specified, calculated from payment terms.',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Additional notes for the invoice (will be added to quote notes)',
                ],
            ],
            'required' => [],
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
            // Find quote
            $quote = $this->findQuote($input, $context);

            if (!$quote) {
                return [
                    'error' => 'Quote not found. Please provide a valid quote_id or quote_number.',
                    'suggestion' => 'Search for the quote first.',
                ];
            }

            // Load quote lines and partner
            $quote->load('lines', 'partner');

            if (!$quote->partner) {
                return [
                    'error' => "Le devis {$quote->quote_number} n'a pas de client associé.",
                ];
            }

            // Check if quote is already converted
            if ($quote->status === 'converted' && $quote->converted_invoice_id) {
                $existingInvoice = Invoice::find($quote->converted_invoice_id);
                return [
                    'error' => "Le devis {$quote->quote_number} a déjà été converti en facture {$existingInvoice->invoice_number}.",
                    'invoice' => [
                        'id' => $existingInvoice->id,
                        'invoice_number' => $existingInvoice->invoice_number,
                    ],
                ];
            }

            // Check if quote is expired
            if ($quote->status === 'expired') {
                return [
                    'warning' => "Le devis {$quote->quote_number} est expiré (valide jusqu'au {$quote->valid_until->format('d/m/Y')}). La conversion est toujours possible.",
                ];
            }

            // Prepare invoice dates
            $invoiceDate = !empty($input['invoice_date'])
                ? $input['invoice_date']
                : now()->format('Y-m-d');

            $paymentTerms = $quote->partner->payment_terms ?? 30;
            $dueDate = !empty($input['due_date'])
                ? $input['due_date']
                : now()->addDays($paymentTerms)->format('Y-m-d');

            // Combine notes
            $notes = $quote->notes;
            if (!empty($input['notes'])) {
                $notes = $notes ? $notes . "\n\n" . $input['notes'] : $input['notes'];
            }

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $context->company->id,
                'partner_id' => $quote->partner_id,
                'type' => 'sale',
                'document_type' => 'invoice',
                'status' => 'draft',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'reference' => $quote->reference ?? "Devis {$quote->quote_number}",
                'notes' => $notes,
                'currency' => $quote->currency,
                'created_by' => $context->user->id,
            ]);

            // Copy quote lines to invoice lines
            $lineNumber = 1;
            foreach ($quote->lines as $quoteLine) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'line_number' => $lineNumber++,
                    'description' => $quoteLine->description,
                    'quantity' => $quoteLine->quantity,
                    'unit_price' => $quoteLine->unit_price,
                    'vat_rate' => $quoteLine->vat_rate,
                    'discount_percent' => $quoteLine->discount_percent ?? 0,
                ]);
            }

            // Reload invoice with lines and calculate totals
            $invoice->refresh();
            $invoice->load('lines');

            // Generate invoice number
            $invoice->generateInvoiceNumber();
            $invoice->save();

            // Update quote status
            $quote->status = 'converted';
            $quote->converted_invoice_id = $invoice->id;
            $quote->converted_at = now();
            $quote->save();

            return [
                'success' => true,
                'message' => "Devis {$quote->quote_number} converti en facture {$invoice->invoice_number}",
                'quote' => [
                    'id' => $quote->id,
                    'quote_number' => $quote->quote_number,
                    'status' => 'converted',
                    'converted_at' => $quote->converted_at->format('d/m/Y H:i'),
                ],
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $quote->partner->name,
                    'date' => $invoice->invoice_date->format('d/m/Y'),
                    'due_date' => $invoice->due_date->format('d/m/Y'),
                    'status' => $invoice->status,
                    'total_excl_vat' => (float) $invoice->total_excl_vat,
                    'total_vat' => (float) $invoice->total_vat,
                    'total_incl_vat' => (float) $invoice->total_incl_vat,
                    'currency' => 'EUR',
                    'lines_count' => $invoice->lines->count(),
                ],
                'next_steps' => [
                    "Valider la facture pour la rendre officielle",
                    "Envoyer la facture au client",
                ],
            ];
        });
    }

    /**
     * Find quote by ID or number.
     */
    protected function findQuote(array $input, ToolContext $context): ?Quote
    {
        // Try to find by ID first
        if (!empty($input['quote_id'])) {
            return Quote::where('id', $input['quote_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        // Try to find by quote number
        if (!empty($input['quote_number'])) {
            return Quote::where('company_id', $context->company->id)
                ->where('quote_number', $input['quote_number'])
                ->first();
        }

        return null;
    }
}
