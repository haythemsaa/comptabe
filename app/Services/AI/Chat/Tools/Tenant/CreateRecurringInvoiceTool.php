<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Partner;
use App\Models\RecurringInvoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Carbon\Carbon;

class CreateRecurringInvoiceTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_recurring_invoice';
    }

    public function getDescription(): string
    {
        return 'Creates a recurring invoice that will automatically generate invoices at specified intervals (weekly, monthly, quarterly, or yearly). Perfect for subscriptions, rent, or regular services.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'partner_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the customer/partner',
                ],
                'partner_vat_number' => [
                    'type' => 'string',
                    'description' => 'Partner VAT number if ID is unknown',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Descriptive name (e.g., "Abonnement mensuel - Client X")',
                ],
                'frequency' => [
                    'type' => 'string',
                    'enum' => ['weekly', 'monthly', 'quarterly', 'yearly'],
                    'description' => 'How often to generate invoices',
                ],
                'frequency_interval' => [
                    'type' => 'integer',
                    'description' => 'Interval (e.g., 2 for every 2 months)',
                ],
                'start_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'When to start generating invoices (YYYY-MM-DD)',
                ],
                'end_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'When to stop (optional, null = indefinite)',
                ],
                'day_of_month' => [
                    'type' => 'integer',
                    'description' => 'Day of month (1-31) for monthly/quarterly/yearly',
                ],
                'max_invoices' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of invoices to generate (optional)',
                ],
                'line_items' => [
                    'type' => 'array',
                    'description' => 'Invoice line items (use {mois}, {annee} placeholders for dynamic text)',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'description' => ['type' => 'string'],
                            'quantity' => ['type' => 'number'],
                            'unit_price' => ['type' => 'number'],
                            'vat_rate' => ['type' => 'number', 'default' => 21],
                        ],
                        'required' => ['description', 'quantity', 'unit_price'],
                    ],
                ],
                'payment_terms_days' => [
                    'type' => 'integer',
                    'description' => 'Payment terms in days (default 30)',
                ],
                'auto_send' => [
                    'type' => 'boolean',
                    'description' => 'Automatically send invoice by email',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Notes to include on generated invoices',
                ],
            ],
            'required' => ['name', 'frequency', 'start_date', 'line_items'],
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

        // Find partner
        $partner = $this->findPartner($input, $context);

        if (!$partner) {
            return [
                'error' => 'Client non trouvé. Spécifiez partner_id ou partner_vat_number.',
            ];
        }

        // Parse dates
        $startDate = Carbon::parse($input['start_date']);
        $endDate = isset($input['end_date']) ? Carbon::parse($input['end_date']) : null;

        // Calculate totals from line items
        $subtotal = 0;
        $vatAmount = 0;

        foreach ($input['line_items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $lineVat = $lineTotal * ($item['vat_rate'] ?? 21) / 100;

            $subtotal += $lineTotal;
            $vatAmount += $lineVat;
        }

        $total = $subtotal + $vatAmount;

        // Calculate next invoice date
        $nextInvoiceDate = $startDate->copy();
        if (isset($input['day_of_month']) && in_array($input['frequency'], ['monthly', 'quarterly', 'yearly'])) {
            $nextInvoiceDate->day(min($input['day_of_month'], $nextInvoiceDate->daysInMonth));
        }

        // Create recurring invoice
        $recurring = RecurringInvoice::create([
            'company_id' => $context->company->id,
            'partner_id' => $partner->id,
            'name' => $input['name'],
            'frequency' => $input['frequency'],
            'frequency_interval' => $input['frequency_interval'] ?? 1,
            'day_of_month' => $input['day_of_month'] ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_invoice_date' => $nextInvoiceDate,
            'payment_terms_days' => $input['payment_terms_days'] ?? 30,
            'notes' => $input['notes'] ?? null,
            'total_excl_vat' => $subtotal,
            'total_vat' => $vatAmount,
            'total_incl_vat' => $total,
            'max_invoices' => $input['max_invoices'] ?? null,
            'auto_send' => $input['auto_send'] ?? false,
            'status' => 'active',
            'created_by' => $context->user->id,
        ]);

        // Create line items
        foreach ($input['line_items'] as $index => $item) {
            $recurring->lines()->create([
                'line_number' => $index + 1,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'] ?? 21,
                'vat_category' => 'S',
            ]);
        }

        $frequencyLabel = match ($recurring->frequency) {
            'weekly' => 'Hebdomadaire',
            'monthly' => 'Mensuel',
            'quarterly' => 'Trimestriel',
            'yearly' => 'Annuel',
        };

        return [
            'success' => true,
            'message' => "Facture récurrente créée : {$recurring->name}",
            'recurring_invoice' => [
                'id' => $recurring->id,
                'name' => $recurring->name,
                'partner' => $partner->name,
                'frequency' => $frequencyLabel,
                'start_date' => $recurring->start_date->format('d/m/Y'),
                'next_invoice_date' => $recurring->next_invoice_date->format('d/m/Y'),
                'total' => number_format($recurring->total_incl_vat, 2) . ' €',
                'status' => 'Actif',
            ],
            'schedule' => [
                'Première facture' => $recurring->next_invoice_date->format('d/m/Y'),
                'Fréquence' => $frequencyLabel,
                'Montant' => number_format($recurring->total_incl_vat, 2) . ' €',
                'Fin prévue' => $recurring->end_date ? $recurring->end_date->format('d/m/Y') : 'Indéfini',
            ],
            'next_steps' => [
                'La première facture sera générée automatiquement le ' . $recurring->next_invoice_date->format('d/m/Y'),
                $recurring->auto_send ? 'Les factures seront envoyées automatiquement par email' : 'Les factures seront créées en brouillon (à envoyer manuellement)',
                'Vous pouvez mettre en pause la récurrence à tout moment',
            ],
        ];
    }

    /**
     * Find partner by ID or VAT number
     */
    protected function findPartner(array $input, ToolContext $context): ?Partner
    {
        if (!empty($input['partner_id'])) {
            return Partner::where('id', $input['partner_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        if (!empty($input['partner_vat_number'])) {
            return Partner::where('vat_number', $input['partner_vat_number'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        return null;
    }
}
