<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class CreateExpenseTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_expense';
    }

    public function getDescription(): string
    {
        return 'Creates an expense or purchase invoice for business costs like meals, travel, office supplies, etc. Use this when recording business expenses or supplier invoices.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'supplier_name' => [
                    'type' => 'string',
                    'description' => 'Name of the supplier or vendor',
                ],
                'supplier_id' => [
                    'type' => 'string',
                    'description' => 'UUID of existing supplier (if known)',
                ],
                'expense_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Date of the expense (YYYY-MM-DD). Defaults to today.',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Description of the expense',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Total amount excluding VAT',
                ],
                'vat_rate' => [
                    'type' => 'number',
                    'description' => 'VAT rate percentage (default: 21)',
                ],
                'category' => [
                    'type' => 'string',
                    'enum' => ['meals', 'travel', 'office_supplies', 'utilities', 'rent', 'services', 'other'],
                    'description' => 'Expense category for accounting',
                ],
                'payment_method' => [
                    'type' => 'string',
                    'enum' => ['cash', 'card', 'bank_transfer', 'other'],
                    'description' => 'How the expense was paid',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Additional notes about the expense',
                ],
            ],
            'required' => ['description', 'amount'],
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
            // Find or create supplier
            $supplier = null;

            if (!empty($input['supplier_id'])) {
                $supplier = Partner::where('id', $input['supplier_id'])
                    ->where('company_id', $context->company->id)
                    ->first();
            } elseif (!empty($input['supplier_name'])) {
                // Try to find existing supplier
                $supplier = Partner::where('company_id', $context->company->id)
                    ->where('name', 'like', '%' . $input['supplier_name'] . '%')
                    ->where('type', 'supplier')
                    ->first();

                // Create if not found
                if (!$supplier) {
                    $supplier = Partner::create([
                        'company_id' => $context->company->id,
                        'type' => 'supplier',
                        'name' => $input['supplier_name'],
                    ]);
                }
            }

            // Category mapping to descriptions
            $categoryDescriptions = [
                'meals' => 'Frais de restauration',
                'travel' => 'Frais de déplacement',
                'office_supplies' => 'Fournitures de bureau',
                'utilities' => 'Services publics (électricité, eau, etc.)',
                'rent' => 'Loyer',
                'services' => 'Services professionnels',
                'other' => 'Autres frais',
            ];

            $category = $input['category'] ?? 'other';
            $categoryLabel = $categoryDescriptions[$category] ?? 'Dépense';

            // Create purchase invoice (expense)
            $expense = Invoice::create([
                'company_id' => $context->company->id,
                'partner_id' => $supplier?->id,
                'type' => 'purchase',
                'document_type' => 'invoice',
                'status' => 'validated', // Expenses are usually validated immediately
                'invoice_date' => $input['expense_date'] ?? now()->format('Y-m-d'),
                'reference' => $categoryLabel . ' - ' . ($input['description'] ?? ''),
                'notes' => $input['notes'] ?? null,
                'payment_method' => $input['payment_method'] ?? null,
                'currency' => 'EUR',
                'created_by' => $context->user->id,
            ]);

            // Create invoice line
            InvoiceLine::create([
                'invoice_id' => $expense->id,
                'line_number' => 1,
                'description' => $input['description'],
                'quantity' => 1,
                'unit_price' => $input['amount'],
                'vat_rate' => $input['vat_rate'] ?? 21,
            ]);

            // Reload with calculations
            $expense->refresh();
            $expense->load('partner');

            // Generate invoice number
            $expense->generateInvoiceNumber();
            $expense->save();

            return [
                'success' => true,
                'message' => "Dépense enregistrée : {$categoryLabel}",
                'expense' => [
                    'id' => $expense->id,
                    'invoice_number' => $expense->invoice_number,
                    'supplier' => $supplier?->name ?? 'Fournisseur non spécifié',
                    'category' => $categoryLabel,
                    'description' => $input['description'],
                    'date' => $expense->invoice_date->format('d/m/Y'),
                    'amount_excl_vat' => (float) $expense->total_excl_vat,
                    'vat' => (float) $expense->total_vat,
                    'total_incl_vat' => (float) $expense->total_incl_vat,
                    'payment_method' => $input['payment_method'] ?? 'non spécifié',
                    'currency' => 'EUR',
                ],
                'tax_deductible' => $this->isTaxDeductible($category),
                'next_steps' => [
                    'Conservez le justificatif (ticket, facture)',
                    'La TVA est déductible si vous êtes assujetti',
                ],
            ];
        });
    }

    /**
     * Check if expense category is typically tax deductible.
     */
    protected function isTaxDeductible(string $category): bool
    {
        $deductibleCategories = ['office_supplies', 'utilities', 'rent', 'services', 'travel'];
        return in_array($category, $deductibleCategories);
    }
}
