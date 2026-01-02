<?php

namespace App\Services\AI\Chat\Tools\Firm;

use App\Models\AccountingFirm;
use App\Models\ClientMandate;
use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class GetAllClientsDataTool extends AbstractTool
{
    public function getName(): string
    {
        return 'get_all_clients_data';
    }

    public function getDescription(): string
    {
        return 'For accounting firms: retrieves overview data for all client companies managed by the firm. Shows key metrics like revenue, invoices, VAT, and compliance status. Use this when the user wants to see all their clients or get a portfolio overview.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status_filter' => [
                    'type' => 'string',
                    'enum' => ['all', 'active', 'pending', 'suspended'],
                    'description' => 'Filter by mandate status (default: active)',
                ],
                'sort_by' => [
                    'type' => 'string',
                    'enum' => ['name', 'revenue', 'invoices_count', 'last_activity'],
                    'description' => 'Sort clients by this field (default: name)',
                ],
                'include_metrics' => [
                    'type' => 'boolean',
                    'description' => 'Include detailed metrics for each client (default: true)',
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['current_month', 'current_quarter', 'current_year', 'last_month', 'last_quarter', 'last_year'],
                    'description' => 'Period for metrics calculation (default: current_month)',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate user is a firm member
        if (!$context->user->isCabinetMember()) {
            return [
                'error' => 'Cet outil est réservé aux membres de cabinets comptables.',
                'suggestion' => 'Vous devez être membre d\'un cabinet pour accéder à cette fonctionnalité.',
            ];
        }

        // Get user's accounting firm
        $firm = $context->user->currentFirm();

        if (!$firm) {
            return [
                'error' => 'Aucun cabinet comptable trouvé pour cet utilisateur.',
                'suggestion' => 'Veuillez d\'abord rejoindre ou créer un cabinet comptable.',
            ];
        }

        $statusFilter = $input['status_filter'] ?? 'active';
        $sortBy = $input['sort_by'] ?? 'name';
        $includeMetrics = $input['include_metrics'] ?? true;
        $period = $input['period'] ?? 'current_month';

        // Get date range for period
        $dateRange = $this->getDateRangeForPeriod($period);

        // Build query for client mandates
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->with(['company', 'manager']);

        // Apply status filter
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $mandates = $query->get();

        if ($mandates->isEmpty()) {
            return [
                'warning' => 'Aucun client trouvé pour ce cabinet.',
                'firm' => [
                    'name' => $firm->name,
                    'id' => $firm->id,
                ],
                'suggestion' => 'Commencez par ajouter des clients au cabinet.',
            ];
        }

        // Process each client
        $clients = $mandates->map(function ($mandate) use ($includeMetrics, $dateRange) {
            $company = $mandate->company;

            $clientData = [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'vat_number' => $company->vat_number,
                'mandate' => [
                    'id' => $mandate->id,
                    'type' => $mandate->mandate_type,
                    'status' => $mandate->status,
                    'start_date' => $mandate->start_date?->format('d/m/Y'),
                    'manager' => $mandate->manager?->name ?? 'Non assigné',
                    'services' => $mandate->services ?? [],
                ],
            ];

            // Add detailed metrics if requested
            if ($includeMetrics) {
                $clientData['metrics'] = $this->calculateClientMetrics($company, $dateRange);
            }

            return $clientData;
        });

        // Sort clients
        $clients = $this->sortClients($clients, $sortBy);

        // Calculate portfolio summary
        $summary = $this->calculatePortfolioSummary($clients, $includeMetrics);

        return [
            'success' => true,
            'firm' => [
                'name' => $firm->name,
                'id' => $firm->id,
            ],
            'period' => [
                'label' => $this->getPeriodLabel($period),
                'from' => $dateRange['from']->format('d/m/Y'),
                'to' => $dateRange['to']->format('d/m/Y'),
            ],
            'total_clients' => $clients->count(),
            'clients' => $clients->values()->toArray(),
            'summary' => $summary,
        ];
    }

    /**
     * Calculate metrics for a client company.
     */
    protected function calculateClientMetrics($company, array $dateRange): array
    {
        // Sales invoices
        $salesInvoices = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        // Purchase invoices
        $purchaseInvoices = Invoice::where('company_id', $company->id)
            ->where('type', 'purchase')
            ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        // Outstanding invoices
        $outstandingInvoices = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->get();

        return [
            'sales' => [
                'count' => $salesInvoices->count(),
                'total_excl_vat' => (float) $salesInvoices->sum('total_excl_vat'),
                'total_vat' => (float) $salesInvoices->sum('total_vat'),
                'total_incl_vat' => (float) $salesInvoices->sum('total_incl_vat'),
            ],
            'purchases' => [
                'count' => $purchaseInvoices->count(),
                'total_excl_vat' => (float) $purchaseInvoices->sum('total_excl_vat'),
                'total_vat' => (float) $purchaseInvoices->sum('total_vat'),
                'total_incl_vat' => (float) $purchaseInvoices->sum('total_incl_vat'),
            ],
            'outstanding' => [
                'count' => $outstandingInvoices->count(),
                'total_due' => (float) $outstandingInvoices->sum('amount_due'),
            ],
            'vat_balance' => [
                'collected' => (float) $salesInvoices->sum('total_vat'),
                'paid' => (float) $purchaseInvoices->sum('total_vat'),
                'balance' => (float) ($salesInvoices->sum('total_vat') - $purchaseInvoices->sum('total_vat')),
            ],
        ];
    }

    /**
     * Get date range for specified period.
     */
    protected function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'current_month' => [
                'from' => now()->startOfMonth(),
                'to' => now()->endOfMonth(),
            ],
            'current_quarter' => [
                'from' => now()->startOfQuarter(),
                'to' => now()->endOfQuarter(),
            ],
            'current_year' => [
                'from' => now()->startOfYear(),
                'to' => now()->endOfYear(),
            ],
            'last_month' => [
                'from' => now()->subMonth()->startOfMonth(),
                'to' => now()->subMonth()->endOfMonth(),
            ],
            'last_quarter' => [
                'from' => now()->subQuarter()->startOfQuarter(),
                'to' => now()->subQuarter()->endOfQuarter(),
            ],
            'last_year' => [
                'from' => now()->subYear()->startOfYear(),
                'to' => now()->subYear()->endOfYear(),
            ],
            default => [
                'from' => now()->startOfMonth(),
                'to' => now()->endOfMonth(),
            ],
        };
    }

    /**
     * Get human-readable period label.
     */
    protected function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'current_month' => 'Mois en cours',
            'current_quarter' => 'Trimestre en cours',
            'current_year' => 'Année en cours',
            'last_month' => 'Mois dernier',
            'last_quarter' => 'Trimestre dernier',
            'last_year' => 'Année dernière',
            default => 'Mois en cours',
        };
    }

    /**
     * Sort clients by specified field.
     */
    protected function sortClients($clients, string $sortBy)
    {
        return match ($sortBy) {
            'name' => $clients->sortBy('company_name'),
            'revenue' => $clients->sortByDesc('metrics.sales.total_incl_vat'),
            'invoices_count' => $clients->sortByDesc('metrics.sales.count'),
            'last_activity' => $clients->sortByDesc('mandate.start_date'),
            default => $clients->sortBy('company_name'),
        };
    }

    /**
     * Calculate portfolio-wide summary.
     */
    protected function calculatePortfolioSummary($clients, bool $includeMetrics): array
    {
        if (!$includeMetrics) {
            return [
                'message' => 'Métriques désactivées',
            ];
        }

        $totalSales = 0;
        $totalPurchases = 0;
        $totalVatCollected = 0;
        $totalVatPaid = 0;
        $totalOutstanding = 0;
        $totalInvoices = 0;

        foreach ($clients as $client) {
            if (isset($client['metrics'])) {
                $totalSales += $client['metrics']['sales']['total_incl_vat'] ?? 0;
                $totalPurchases += $client['metrics']['purchases']['total_incl_vat'] ?? 0;
                $totalVatCollected += $client['metrics']['vat_balance']['collected'] ?? 0;
                $totalVatPaid += $client['metrics']['vat_balance']['paid'] ?? 0;
                $totalOutstanding += $client['metrics']['outstanding']['total_due'] ?? 0;
                $totalInvoices += $client['metrics']['sales']['count'] ?? 0;
            }
        }

        return [
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'total_vat_collected' => $totalVatCollected,
            'total_vat_paid' => $totalVatPaid,
            'net_vat_balance' => $totalVatCollected - $totalVatPaid,
            'total_outstanding' => $totalOutstanding,
            'total_invoices' => $totalInvoices,
            'average_per_client' => $clients->count() > 0 ? $totalSales / $clients->count() : 0,
        ];
    }
}
