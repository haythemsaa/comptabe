<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\Partner;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class CreateQuoteTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_quote';
    }

    public function getDescription(): string
    {
        return 'Creates a new quote (devis/offre) for a customer. Use this when the user asks to create, generate, or make a quote, devis, or offer.';
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
                'quote_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Quote date (YYYY-MM-DD). Defaults to today if not specified.',
                ],
                'valid_until' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Quote validity end date (YYYY-MM-DD). If not specified, set to 30 days from quote_date.',
                ],
                'validity_days' => [
                    'type' => 'integer',
                    'description' => 'Number of days the quote is valid (default: 30). Only used if valid_until is not provided.',
                ],
                'reference' => [
                    'type' => 'string',
                    'description' => 'Optional reference or project name for the quote',
                ],
                'lines' => [
                    'type' => 'array',
                    'description' => 'Array of quote line items',
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
                    'description' => 'Public notes to appear on the quote',
                ],
                'terms' => [
                    'type' => 'string',
                    'description' => 'Terms and conditions for the quote',
                ],
            ],
            'required' => ['lines'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        // Require confirmation before creating quote
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
            $quoteDate = !empty($input['quote_date'])
                ? $input['quote_date']
                : now()->format('Y-m-d');

            $validityDays = $input['validity_days'] ?? 30;

            $validUntil = !empty($input['valid_until'])
                ? $input['valid_until']
                : now()->addDays($validityDays)->format('Y-m-d');

            // Create quote
            $quote = Quote::create([
                'company_id' => $context->company->id,
                'partner_id' => $partner->id,
                'status' => 'draft',
                'quote_date' => $quoteDate,
                'valid_until' => $validUntil,
                'reference' => $input['reference'] ?? null,
                'notes' => $input['notes'] ?? null,
                'terms' => $input['terms'] ?? null,
                'currency' => 'EUR',
                'created_by' => $context->user->id,
            ]);

            // Create quote lines
            $lineNumber = 1;
            foreach ($input['lines'] as $lineData) {
                QuoteLine::create([
                    'quote_id' => $quote->id,
                    'line_number' => $lineNumber++,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'] ?? 1,
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'] ?? 21,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            // Reload quote with lines and totals
            $quote->refresh();
            $quote->load('lines', 'partner');

            // Generate quote number
            $quote->generateQuoteNumber();
            $quote->save();

            return [
                'success' => true,
                'message' => "Devis {$quote->quote_number} créé avec succès pour {$partner->name}",
                'quote' => [
                    'id' => $quote->id,
                    'quote_number' => $quote->quote_number,
                    'partner' => $partner->name,
                    'date' => $quote->quote_date->format('d/m/Y'),
                    'valid_until' => $quote->valid_until->format('d/m/Y'),
                    'status' => $quote->status,
                    'total_excl_vat' => (float) $quote->total_excl_vat,
                    'total_vat' => (float) $quote->total_vat,
                    'total_incl_vat' => (float) $quote->total_incl_vat,
                    'currency' => 'EUR',
                    'lines_count' => $quote->lines->count(),
                ],
                'next_steps' => [
                    'Envoyer le devis au client par email',
                    'Le client peut accepter ou refuser le devis',
                    'Convertir en facture une fois accepté',
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
