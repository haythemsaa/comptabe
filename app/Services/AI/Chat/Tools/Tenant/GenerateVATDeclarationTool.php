<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Models\VatDeclaration;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class GenerateVATDeclarationTool extends AbstractTool
{
    public function getName(): string
    {
        return 'generate_vat_declaration';
    }

    public function getDescription(): string
    {
        return 'Generates a VAT declaration (déclaration TVA) for a specified period. Calculates input and output VAT, and determines the balance due or recoverable.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'period_start' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Period start date (YYYY-MM-DD)',
                ],
                'period_end' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Period end date (YYYY-MM-DD)',
                ],
                'period_type' => [
                    'type' => 'string',
                    'enum' => ['monthly', 'quarterly', 'annual'],
                    'description' => 'Period type (helps auto-calculate dates if not specified)',
                ],
                'year' => [
                    'type' => 'integer',
                    'description' => 'Year for the declaration (used with period_type)',
                ],
                'quarter' => [
                    'type' => 'integer',
                    'description' => 'Quarter number (1-4) for quarterly declarations',
                ],
                'month' => [
                    'type' => 'integer',
                    'description' => 'Month number (1-12) for monthly declarations',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false; // Read-only operation
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Determine period dates
        [$periodStart, $periodEnd] = $this->determinePeriod($input);

        if (!$periodStart || !$periodEnd) {
            return [
                'error' => 'Impossible de déterminer la période. Spécifiez period_start et period_end, ou utilisez period_type avec year/quarter/month.',
                'examples' => [
                    'Mensuel : period_type=monthly, year=2024, month=12',
                    'Trimestriel : period_type=quarterly, year=2024, quarter=4',
                    'Personnalisé : period_start=2024-01-01, period_end=2024-03-31',
                ],
            ];
        }

        // Calculate VAT
        $vatData = $this->calculateVAT($context->company->id, $periodStart, $periodEnd);

        // Check if declaration already exists
        $existingDeclaration = VatDeclaration::where('company_id', $context->company->id)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->first();

        if ($existingDeclaration) {
            return [
                'warning' => "Une déclaration TVA existe déjà pour cette période.",
                'existing_declaration' => [
                    'id' => $existingDeclaration->id,
                    'status' => $existingDeclaration->status,
                    'balance_due' => (float) $existingDeclaration->balance_due,
                    'created_at' => $existingDeclaration->created_at->format('d/m/Y'),
                ],
                'suggestion' => 'Voulez-vous créer une nouvelle déclaration de remplacement ?',
            ];
        }

        // Create VAT declaration
        $declaration = VatDeclaration::create([
            'company_id' => $context->company->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'draft',
            'output_vat' => $vatData['output_vat'],
            'input_vat' => $vatData['input_vat'],
            'balance_due' => $vatData['balance_due'],
            'created_by' => $context->user->id,
        ]);

        $balanceMessage = $vatData['balance_due'] > 0
            ? "TVA à payer : {$vatData['balance_due']}€"
            : "TVA à récupérer : " . abs($vatData['balance_due']) . "€";

        return [
            'success' => true,
            'message' => "Déclaration TVA générée pour la période du {$periodStart->format('d/m/Y')} au {$periodEnd->format('d/m/Y')}",
            'declaration' => [
                'id' => $declaration->id,
                'period_start' => $periodStart->format('d/m/Y'),
                'period_end' => $periodEnd->format('d/m/Y'),
                'status' => 'draft',
            ],
            'vat_summary' => [
                'output_vat' => (float) $vatData['output_vat'],
                'input_vat' => (float) $vatData['input_vat'],
                'balance_due' => (float) $vatData['balance_due'],
                'balance_message' => $balanceMessage,
            ],
            'details' => $vatData['details'],
            'next_steps' => [
                'Vérifier les montants calculés',
                'Valider la déclaration',
                'Soumettre via Intervat ou MyMinfin',
            ],
        ];
    }

    /**
     * Determine period start and end dates.
     */
    protected function determinePeriod(array $input): array
    {
        // If explicit dates provided
        if (!empty($input['period_start']) && !empty($input['period_end'])) {
            return [
                \Carbon\Carbon::parse($input['period_start']),
                \Carbon\Carbon::parse($input['period_end']),
            ];
        }

        $year = $input['year'] ?? now()->year;
        $periodType = $input['period_type'] ?? null;

        // Monthly
        if ($periodType === 'monthly' && !empty($input['month'])) {
            $month = $input['month'];
            $start = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            return [$start, $end];
        }

        // Quarterly
        if ($periodType === 'quarterly' && !empty($input['quarter'])) {
            $quarter = $input['quarter'];
            $startMonth = (($quarter - 1) * 3) + 1;
            $start = \Carbon\Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();
            return [$start, $end];
        }

        // Annual
        if ($periodType === 'annual') {
            $start = \Carbon\Carbon::createFromDate($year, 1, 1);
            $end = \Carbon\Carbon::createFromDate($year, 12, 31);
            return [$start, $end];
        }

        return [null, null];
    }

    /**
     * Calculate VAT for the period.
     */
    protected function calculateVAT(string $companyId, $periodStart, $periodEnd): array
    {
        // Output VAT (sales)
        $salesInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'sale')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->whereIn('status', ['validated', 'sent', 'partial', 'paid'])
            ->get();

        $outputVat = $salesInvoices->sum('total_vat');
        $salesTotal = $salesInvoices->sum('total_excl_vat');

        // Input VAT (purchases)
        $purchaseInvoices = Invoice::where('company_id', $companyId)
            ->where('type', 'purchase')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->get();

        $inputVat = $purchaseInvoices->sum('total_vat');
        $purchasesTotal = $purchaseInvoices->sum('total_excl_vat');

        // Balance (positive = to pay, negative = to recover)
        $balanceDue = $outputVat - $inputVat;

        return [
            'output_vat' => round($outputVat, 2),
            'input_vat' => round($inputVat, 2),
            'balance_due' => round($balanceDue, 2),
            'details' => [
                'sales_count' => $salesInvoices->count(),
                'sales_total_excl_vat' => round($salesTotal, 2),
                'purchases_count' => $purchaseInvoices->count(),
                'purchases_total_excl_vat' => round($purchasesTotal, 2),
            ],
        ];
    }
}
