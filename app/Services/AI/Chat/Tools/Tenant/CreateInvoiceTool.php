<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class CreateInvoiceTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_invoice';
    }

    public function getDescription(): string
    {
        return 'Creates a new sales invoice with line items for a customer. Use this when the user asks to create, generate, or make an invoice.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'partner_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the customer/partner (use search_partners tool first if needed)',
                ],
                'partner_name' => [
                    'type' => 'string',
                    'description' => 'Customer name if partner_id is unknown (will search by name)',
                ],
                'invoice_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Invoice date (YYYY-MM-DD). Defaults to today if not specified.',
                ],
                'due_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Payment due date (YYYY-MM-DD). If not specified, calculated from invoice_date + 30 days.',
                ],
                'payment_terms_days' => [
                    'type' => 'integer',
                    'description' => 'Number of days for payment (default: 30). Only used if due_date is not provided.',
                ],
                'reference' => [
                    'type' => 'string',
                    'description' => 'Optional reference or description for the invoice',
                ],
                'lines' => [
                    'type' => 'array',
                    'description' => 'Array of invoice line items',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'description' => [
                                'type' => 'string',
                                'description' => 'Description of the product or service',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Quantity (default: 1)',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Price per unit excluding VAT',
                            ],
                            'vat_rate' => [
                                'type' => 'number',
                                'description' => 'VAT rate percentage (default: 21 for Belgium)',
                            ],
                            'discount_percent' => [
                                'type' => 'number',
                                'description' => 'Discount percentage (optional)',
                            ],
                        ],
                        'required' => ['description', 'unit_price'],
                    ],
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Public notes to appear on the invoice',
                ],
                'internal_notes' => [
                    'type' => 'string',
                    'description' => 'Internal notes (not visible to customer)',
                ],
            ],
            'required' => ['lines'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        // Require confirmation before creating invoice
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        return DB::transaction(function () use ($input, $context) {
            // Find partner
            $partner = $this->findPartner($input, $context);

            if (!$partner) {
                return [
                    'error' => 'Customer not found. Please provide a valid partner_id or partner_name.',
                    'suggestion' => 'Use the search_partners tool first to find the customer.',
                ];
            }

            // Prepare dates
            $invoiceDate = !empty($input['invoice_date'])
                ? $input['invoice_date']
                : now()->format('Y-m-d');

            $paymentTermsDays = $input['payment_terms_days'] ?? $partner->payment_terms ?? 30;

            $dueDate = !empty($input['due_date'])
                ? $input['due_date']
                : now()->addDays($paymentTermsDays)->format('Y-m-d');

            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $context->company->id,
                'partner_id' => $partner->id,
                'type' => 'sale',
                'document_type' => 'invoice',
                'status' => 'draft',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'reference' => $input['reference'] ?? null,
                'notes' => $input['notes'] ?? null,
                'internal_notes' => $input['internal_notes'] ?? null,
                'currency' => 'EUR',
                'created_by' => $context->user->id,
            ]);

            // Create invoice lines
            $lineNumber = 1;
            foreach ($input['lines'] as $lineData) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'line_number' => $lineNumber++,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'] ?? 1,
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'] ?? 21,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            // Reload invoice with lines and totals
            $invoice->refresh();
            $invoice->load('lines', 'partner');

            // Generate invoice number
            $invoice->generateInvoiceNumber();
            $invoice->save();

            return [
                'success' => true,
                'message' => "Facture {$invoice->invoice_number} créée avec succès pour {$partner->name}",
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $partner->name,
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
                    'Valider la facture pour la rendre officielle',
                    'Envoyer la facture au client par email',
                    'Envoyer via Peppol si le client est Peppol-capable',
                ],
            ];
        });
    }

    /**
     * Find partner by ID or name.
     */
    protected function findPartner(array $input, ToolContext $context): ?Partner
    {
        // Try to find by ID first
        if (!empty($input['partner_id'])) {
            return Partner::where('id', $input['partner_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        // Try to find by name
        if (!empty($input['partner_name'])) {
            return Partner::where('company_id', $context->company->id)
                ->where('name', 'like', '%' . $input['partner_name'] . '%')
                ->where('type', 'customer')
                ->first();
        }

        return null;
    }
}
