<?php

namespace App\Services\AI\Chat\Tools\Firm;

use App\Models\ClientMandate;
use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class GenerateMultiClientReportTool extends AbstractTool
{
    public function getName(): string
    {
        return 'generate_multi_client_report';
    }

    public function getDescription(): string
    {
        return 'For accounting firms: generates comparative reports across multiple clients. Shows rankings, trends, and benchmarks. Use this to identify top performers, clients needing attention, or to prepare portfolio reviews.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'report_type' => [
                    'type' => 'string',
                    'enum' => ['revenue_comparison', 'vat_overview', 'outstanding_analysis', 'growth_trends', 'compliance_status'],
                    'description' => 'Type of report to generate',
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['current_month', 'current_quarter', 'current_year', 'last_month', 'last_quarter', 'last_year'],
                    'description' => 'Period for analysis (default: current_quarter)',
                ],
                'client_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Specific client IDs to include. Leave empty for all active clients.',
                ],
                'top_n' => [
                    'type' => 'number',
                    'description' => 'For ranking reports, show top N clients (default: 10)',
                ],
            ],
            'required' => ['report_type'],
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
            ];
        }

        $firm = $context->user->currentFirm();

        if (!$firm) {
            return [
                'error' => 'Aucun cabinet comptable trouvé.',
            ];
        }

        $reportType = $input['report_type'];
        $period = $input['period'] ?? 'current_quarter';
        $topN = $input['top_n'] ?? 10;

        // Get date range
        $dateRange = $this->getDateRangeForPeriod($period);

        // Get client mandates
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->where('status', 'active')
            ->with('company');

        if (!empty($input['client_ids'])) {
            $query->whereIn('company_id', $input['client_ids']);
        }

        $mandates = $query->get();

        if ($mandates->isEmpty()) {
            return [
                'warning' => 'Aucun client actif trouvé.',
                'firm' => $firm->name,
            ];
        }

        // Generate report based on type
        $reportData = match ($reportType) {
            'revenue_comparison' => $this->generateRevenueComparison($mandates, $dateRange, $topN),
            'vat_overview' => $this->generateVATOverview($mandates, $dateRange),
            'outstanding_analysis' => $this->generateOutstandingAnalysis($mandates),
            'growth_trends' => $this->generateGrowthTrends($mandates, $dateRange),
            'compliance_status' => $this->generateComplianceStatus($mandates, $dateRange),
            default => ['error' => 'Type de rapport non reconnu'],
        };

        return [
            'success' => true,
            'firm' => [
                'name' => $firm->name,
                'id' => $firm->id,
            ],
            'report' => [
                'type' => $reportType,
                'period' => [
                    'label' => $this->getPeriodLabel($period),
                    'from' => $dateRange['from']->format('d/m/Y'),
                    'to' => $dateRange['to']->format('d/m/Y'),
                ],
                'generated_at' => now()->format('d/m/Y H:i'),
                'clients_analyzed' => $mandates->count(),
            ],
            'data' => $reportData,
        ];
    }

    /**
     * Generate revenue comparison report.
     */
    protected function generateRevenueComparison($mandates, array $dateRange, int $topN): array
    {
        $clientData = [];

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            $sales = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->get();

            $clientData[] = [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'revenue' => (float) $sales->sum('total_incl_vat'),
                'invoices_count' => $sales->count(),
                'average_invoice' => $sales->count() > 0 ? $sales->sum('total_incl_vat') / $sales->count() : 0,
            ];
        }

        // Sort by revenue descending
        $clientData = collect($clientData)->sortByDesc('revenue')->values()->take($topN)->toArray();

        // Calculate totals
        $totalRevenue = collect($clientData)->sum('revenue');
        $totalInvoices = collect($clientData)->sum('invoices_count');

        return [
            'title' => "Top {$topN} clients par chiffre d'affaires",
            'clients' => $clientData,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_invoices' => $totalInvoices,
                'average_revenue_per_client' => count($clientData) > 0 ? $totalRevenue / count($clientData) : 0,
            ],
            'insights' => $this->generateRevenueInsights($clientData, $totalRevenue),
        ];
    }

    /**
     * Generate VAT overview report.
     */
    protected function generateVATOverview($mandates, array $dateRange): array
    {
        $vatData = [];
        $totalVatCollected = 0;
        $totalVatPaid = 0;

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            $sales = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->get();

            $purchases = Invoice::where('company_id', $company->id)
                ->where('type', 'purchase')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->get();

            $vatCollected = (float) $sales->sum('total_vat');
            $vatPaid = (float) $purchases->sum('total_vat');
            $vatBalance = $vatCollected - $vatPaid;

            $totalVatCollected += $vatCollected;
            $totalVatPaid += $vatPaid;

            $vatData[] = [
                'company_name' => $company->name,
                'vat_collected' => $vatCollected,
                'vat_paid' => $vatPaid,
                'vat_balance' => $vatBalance,
                'status' => $vatBalance > 0 ? 'À payer' : ($vatBalance < 0 ? 'À récupérer' : 'Équilibré'),
            ];
        }

        // Sort by balance (descending - highest liability first)
        $vatData = collect($vatData)->sortByDesc('vat_balance')->values()->toArray();

        return [
            'title' => 'Vue d\'ensemble TVA multi-clients',
            'clients' => $vatData,
            'summary' => [
                'total_vat_collected' => $totalVatCollected,
                'total_vat_paid' => $totalVatPaid,
                'net_vat_balance' => $totalVatCollected - $totalVatPaid,
                'clients_to_pay' => collect($vatData)->where('vat_balance', '>', 0)->count(),
                'clients_to_recover' => collect($vatData)->where('vat_balance', '<', 0)->count(),
            ],
            'insights' => [
                'Le solde TVA global du portefeuille est de ' . number_format($totalVatCollected - $totalVatPaid, 2, ',', ' ') . ' €',
                collect($vatData)->where('vat_balance', '>', 0)->count() . ' clients ont de la TVA à payer',
                collect($vatData)->where('vat_balance', '<', 0)->count() . ' clients ont de la TVA à récupérer',
            ],
        ];
    }

    /**
     * Generate outstanding invoices analysis.
     */
    protected function generateOutstandingAnalysis($mandates): array
    {
        $outstandingData = [];
        $totalOutstanding = 0;

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            $outstanding = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereIn('status', ['sent', 'partial'])
                ->where('due_date', '<', now())
                ->get();

            if ($outstanding->isNotEmpty()) {
                $totalDue = (float) $outstanding->sum('amount_due');
                $totalOutstanding += $totalDue;

                // Calculate aging
                $aging = [
                    '0-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    '90+' => 0,
                ];

                foreach ($outstanding as $invoice) {
                    $daysOverdue = now()->diffInDays($invoice->due_date);

                    if ($daysOverdue <= 30) {
                        $aging['0-30'] += $invoice->amount_due;
                    } elseif ($daysOverdue <= 60) {
                        $aging['31-60'] += $invoice->amount_due;
                    } elseif ($daysOverdue <= 90) {
                        $aging['61-90'] += $invoice->amount_due;
                    } else {
                        $aging['90+'] += $invoice->amount_due;
                    }
                }

                $outstandingData[] = [
                    'company_name' => $company->name,
                    'invoices_count' => $outstanding->count(),
                    'total_outstanding' => $totalDue,
                    'oldest_invoice_days' => now()->diffInDays($outstanding->min('due_date')),
                    'aging' => $aging,
                    'risk_level' => $this->assessCreditRisk($totalDue, $outstanding->count(), $aging),
                ];
            }
        }

        // Sort by total outstanding (descending)
        $outstandingData = collect($outstandingData)->sortByDesc('total_outstanding')->values()->toArray();

        return [
            'title' => 'Analyse des impayés',
            'clients' => $outstandingData,
            'summary' => [
                'total_outstanding' => $totalOutstanding,
                'clients_with_outstanding' => count($outstandingData),
                'total_invoices' => collect($outstandingData)->sum('invoices_count'),
            ],
            'insights' => $this->generateOutstandingInsights($outstandingData, $totalOutstanding),
        ];
    }

    /**
     * Generate growth trends report.
     */
    protected function generateGrowthTrends($mandates, array $dateRange): array
    {
        // Compare current period with previous period
        $periodLength = $dateRange['from']->diffInDays($dateRange['to']);
        $previousPeriodStart = $dateRange['from']->copy()->subDays($periodLength + 1);
        $previousPeriodEnd = $dateRange['from']->copy()->subDay();

        $growthData = [];

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            // Current period
            $currentRevenue = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->sum('total_incl_vat');

            // Previous period
            $previousRevenue = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereBetween('invoice_date', [$previousPeriodStart, $previousPeriodEnd])
                ->whereIn('status', ['validated', 'sent', 'paid'])
                ->sum('total_incl_vat');

            $growth = $previousRevenue > 0
                ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
                : 0;

            $growthData[] = [
                'company_name' => $company->name,
                'current_period_revenue' => (float) $currentRevenue,
                'previous_period_revenue' => (float) $previousRevenue,
                'growth_amount' => (float) ($currentRevenue - $previousRevenue),
                'growth_percentage' => round($growth, 2),
                'trend' => $growth > 0 ? 'Croissance' : ($growth < 0 ? 'Déclin' : 'Stable'),
            ];
        }

        // Sort by growth percentage
        $growthData = collect($growthData)->sortByDesc('growth_percentage')->values()->toArray();

        return [
            'title' => 'Tendances de croissance',
            'clients' => $growthData,
            'summary' => [
                'clients_growing' => collect($growthData)->where('growth_percentage', '>', 0)->count(),
                'clients_declining' => collect($growthData)->where('growth_percentage', '<', 0)->count(),
                'clients_stable' => collect($growthData)->where('growth_percentage', '=', 0)->count(),
                'average_growth' => collect($growthData)->avg('growth_percentage'),
            ],
            'insights' => $this->generateGrowthInsights($growthData),
        ];
    }

    /**
     * Generate compliance status report.
     */
    protected function generateComplianceStatus($mandates, array $dateRange): array
    {
        $complianceData = [];

        foreach ($mandates as $mandate) {
            $company = $mandate->company;

            // Check various compliance indicators
            $hasInvoices = Invoice::where('company_id', $company->id)
                ->whereBetween('invoice_date', [$dateRange['from'], $dateRange['to']])
                ->exists();

            $hasPendingApprovals = false; // TODO: Check approval workflows

            $hasOverdueInvoices = Invoice::where('company_id', $company->id)
                ->where('type', 'sale')
                ->whereIn('status', ['sent', 'partial'])
                ->where('due_date', '<', now()->subDays(90))
                ->exists();

            $missingVAT = !$company->vat_number;

            $issues = [];
            if (!$hasInvoices) $issues[] = 'Aucune activité';
            if ($hasOverdueInvoices) $issues[] = 'Impayés > 90j';
            if ($missingVAT) $issues[] = 'N° TVA manquant';

            $score = 100;
            $score -= count($issues) * 20;
            $score = max(0, $score);

            $complianceData[] = [
                'company_name' => $company->name,
                'compliance_score' => $score,
                'status' => $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'À risque')),
                'issues' => $issues,
                'has_activity' => $hasInvoices,
            ];
        }

        // Sort by compliance score
        $complianceData = collect($complianceData)->sortByDesc('compliance_score')->values()->toArray();

        return [
            'title' => 'Statut de conformité',
            'clients' => $complianceData,
            'summary' => [
                'excellent' => collect($complianceData)->where('compliance_score', '>=', 80)->count(),
                'good' => collect($complianceData)->whereBetween('compliance_score', [60, 79])->count(),
                'medium' => collect($complianceData)->whereBetween('compliance_score', [40, 59])->count(),
                'at_risk' => collect($complianceData)->where('compliance_score', '<', 40)->count(),
                'average_score' => collect($complianceData)->avg('compliance_score'),
            ],
        ];
    }

    // Helper methods

    protected function getDateRangeForPeriod(string $period): array
    {
        return match ($period) {
            'current_month' => ['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()],
            'current_quarter' => ['from' => now()->startOfQuarter(), 'to' => now()->endOfQuarter()],
            'current_year' => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            'last_month' => ['from' => now()->subMonth()->startOfMonth(), 'to' => now()->subMonth()->endOfMonth()],
            'last_quarter' => ['from' => now()->subQuarter()->startOfQuarter(), 'to' => now()->subQuarter()->endOfQuarter()],
            'last_year' => ['from' => now()->subYear()->startOfYear(), 'to' => now()->subYear()->endOfYear()],
            default => ['from' => now()->startOfQuarter(), 'to' => now()->endOfQuarter()],
        };
    }

    protected function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'current_month' => 'Mois en cours',
            'current_quarter' => 'Trimestre en cours',
            'current_year' => 'Année en cours',
            'last_month' => 'Mois dernier',
            'last_quarter' => 'Trimestre dernier',
            'last_year' => 'Année dernière',
            default => 'Trimestre en cours',
        };
    }

    protected function generateRevenueInsights(array $clientData, float $totalRevenue): array
    {
        $insights = [];

        if (count($clientData) > 0) {
            $topClient = $clientData[0];
            $topClientPercentage = ($topClient['revenue'] / $totalRevenue) * 100;

            $insights[] = "Le client principal ({$topClient['company_name']}) représente " . round($topClientPercentage, 1) . "% du CA total";

            // Check concentration risk
            if ($topClientPercentage > 30) {
                $insights[] = "⚠️ Risque de concentration élevé - le top client dépasse 30% du CA";
            }
        }

        return $insights;
    }

    protected function assessCreditRisk(float $totalDue, int $invoiceCount, array $aging): string
    {
        if ($aging['90+'] > 0) {
            return 'Élevé';
        } elseif ($aging['61-90'] > $totalDue * 0.5) {
            return 'Moyen';
        }
        return 'Faible';
    }

    protected function generateOutstandingInsights(array $outstandingData, float $totalOutstanding): array
    {
        $insights = [];

        $insights[] = "Montant total des impayés : " . number_format($totalOutstanding, 2, ',', ' ') . " €";

        $highRiskClients = collect($outstandingData)->where('risk_level', 'Élevé')->count();
        if ($highRiskClients > 0) {
            $insights[] = "⚠️ {$highRiskClients} client(s) à risque élevé nécessitent une action immédiate";
        }

        return $insights;
    }

    protected function generateGrowthInsights(array $growthData): array
    {
        $insights = [];

        $topGrower = collect($growthData)->sortByDesc('growth_percentage')->first();
        if ($topGrower && $topGrower['growth_percentage'] > 0) {
            $insights[] = "Meilleure performance : {$topGrower['company_name']} avec +" . round($topGrower['growth_percentage'], 1) . "%";
        }

        $decliningClients = collect($growthData)->where('growth_percentage', '<', -10)->count();
        if ($decliningClients > 0) {
            $insights[] = "⚠️ {$decliningClients} client(s) en déclin significatif (>-10%)";
        }

        return $insights;
    }
}
