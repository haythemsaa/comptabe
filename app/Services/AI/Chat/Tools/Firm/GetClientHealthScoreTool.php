<?php

namespace App\Services\AI\Chat\Tools\Firm;

use App\Models\ClientMandate;
use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class GetClientHealthScoreTool extends AbstractTool
{
    public function getName(): string
    {
        return 'get_client_health_score';
    }

    public function getDescription(): string
    {
        return 'For accounting firms: calculates a comprehensive health score for a client company based on financial health, compliance, activity level, and payment behavior. Use this to assess client risk, identify issues early, or prepare review meetings.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'company_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the client company',
                ],
                'company_name' => [
                    'type' => 'string',
                    'description' => 'Client company name if company_id is unknown',
                ],
                'analysis_period_months' => [
                    'type' => 'number',
                    'description' => 'Number of months to analyze (default: 3)',
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
            ];
        }

        $firm = $context->user->currentFirm();

        if (!$firm) {
            return [
                'error' => 'Aucun cabinet comptable trouvé.',
            ];
        }

        // Find client mandate
        $mandate = $this->findClientMandate($input, $firm);

        if (!$mandate) {
            return [
                'error' => 'Client non trouvé ou non géré par votre cabinet.',
                'suggestion' => 'Vérifiez le nom du client ou son ID.',
            ];
        }

        $company = $mandate->company;
        $analysisMonths = $input['analysis_period_months'] ?? 3;

        // Calculate health score components
        $activityScore = $this->calculateActivityScore($company, $analysisMonths);
        $financialScore = $this->calculateFinancialScore($company, $analysisMonths);
        $complianceScore = $this->calculateComplianceScore($company);
        $paymentBehaviorScore = $this->calculatePaymentBehaviorScore($company, $analysisMonths);

        // Weighted overall score
        $overallScore = round(
            ($activityScore['score'] * 0.25) +
            ($financialScore['score'] * 0.30) +
            ($complianceScore['score'] * 0.25) +
            ($paymentBehaviorScore['score'] * 0.20)
        );

        // Determine health status
        $healthStatus = $this->getHealthStatus($overallScore);

        // Generate recommendations
        $recommendations = $this->generateRecommendations([
            'activity' => $activityScore,
            'financial' => $financialScore,
            'compliance' => $complianceScore,
            'payment' => $paymentBehaviorScore,
        ], $overallScore);

        // Risk assessment
        $riskLevel = $this->assessRiskLevel($overallScore, [
            'activity' => $activityScore,
            'financial' => $financialScore,
            'compliance' => $complianceScore,
            'payment' => $paymentBehaviorScore,
        ]);

        return [
            'success' => true,
            'client' => [
                'company_name' => $company->name,
                'company_id' => $company->id,
                'vat_number' => $company->vat_number,
            ],
            'mandate' => [
                'type' => $mandate->mandate_type,
                'status' => $mandate->status,
                'manager' => $mandate->manager?->name,
                'start_date' => $mandate->start_date?->format('d/m/Y'),
            ],
            'health_score' => [
                'overall_score' => $overallScore,
                'status' => $healthStatus['label'],
                'status_color' => $healthStatus['color'],
                'components' => [
                    'activity' => $activityScore,
                    'financial' => $financialScore,
                    'compliance' => $complianceScore,
                    'payment_behavior' => $paymentBehaviorScore,
                ],
            ],
            'risk_assessment' => $riskLevel,
            'recommendations' => $recommendations,
            'analysis_period' => "{$analysisMonths} derniers mois",
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Calculate activity score (regular invoicing, business growth).
     */
    protected function calculateActivityScore($company, int $months): array
    {
        $periodStart = now()->subMonths($months);

        $invoices = Invoice::where('company_id', $company->id)
            ->where('invoice_date', '>=', $periodStart)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        $invoiceCount = $invoices->count();
        $monthlyAverage = $invoiceCount / $months;

        // Score based on activity level
        $score = 100;
        $issues = [];

        if ($invoiceCount === 0) {
            $score = 0;
            $issues[] = 'Aucune activité détectée';
        } elseif ($monthlyAverage < 1) {
            $score = 40;
            $issues[] = 'Activité très faible (<1 facture/mois)';
        } elseif ($monthlyAverage < 3) {
            $score = 70;
            $issues[] = 'Activité modérée';
        }

        // Check for declining trend
        $recentMonth = Invoice::where('company_id', $company->id)
            ->where('invoice_date', '>=', now()->subMonth())
            ->count();

        $previousMonth = Invoice::where('company_id', $company->id)
            ->whereBetween('invoice_date', [now()->subMonths(2), now()->subMonth()])
            ->count();

        if ($previousMonth > 0 && $recentMonth < $previousMonth * 0.5) {
            $score -= 20;
            $issues[] = 'Déclin d\'activité récent';
        }

        return [
            'score' => max(0, $score),
            'label' => $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'Faible')),
            'metrics' => [
                'total_invoices' => $invoiceCount,
                'monthly_average' => round($monthlyAverage, 1),
                'recent_month' => $recentMonth,
            ],
            'issues' => $issues,
        ];
    }

    /**
     * Calculate financial score (revenue, profitability indicators).
     */
    protected function calculateFinancialScore($company, int $months): array
    {
        $periodStart = now()->subMonths($months);

        $sales = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->where('invoice_date', '>=', $periodStart)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        $purchases = Invoice::where('company_id', $company->id)
            ->where('type', 'purchase')
            ->where('invoice_date', '>=', $periodStart)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->get();

        $revenue = (float) $sales->sum('total_excl_vat');
        $expenses = (float) $purchases->sum('total_excl_vat');
        $margin = $revenue - $expenses;
        $marginPercent = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

        $score = 100;
        $issues = [];

        // Evaluate based on margin
        if ($margin < 0) {
            $score = 30;
            $issues[] = 'Marge négative (dépenses > revenus)';
        } elseif ($marginPercent < 10) {
            $score = 50;
            $issues[] = 'Marge faible (<10%)';
        } elseif ($marginPercent < 20) {
            $score = 70;
        }

        // Check revenue level
        if ($revenue < 5000) {
            $score -= 20;
            $issues[] = 'Chiffre d\'affaires très faible';
        }

        return [
            'score' => max(0, $score),
            'label' => $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'Faible')),
            'metrics' => [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'margin' => $margin,
                'margin_percent' => round($marginPercent, 1),
            ],
            'issues' => $issues,
        ];
    }

    /**
     * Calculate compliance score (VAT, documentation, legal).
     */
    protected function calculateComplianceScore($company): array
    {
        $score = 100;
        $issues = [];

        // Check VAT number
        if (empty($company->vat_number)) {
            $score -= 30;
            $issues[] = 'Numéro de TVA manquant';
        }

        // Check enterprise number
        if (empty($company->enterprise_number)) {
            $score -= 20;
            $issues[] = 'Numéro d\'entreprise manquant';
        }

        // Check address completeness
        if (empty($company->street) || empty($company->postal_code) || empty($company->city)) {
            $score -= 15;
            $issues[] = 'Adresse incomplète';
        }

        // Check IBAN
        if (empty($company->default_iban)) {
            $score -= 10;
            $issues[] = 'IBAN manquant';
        }

        // Check for overdue invoices (>90 days)
        $overdueCount = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now()->subDays(90))
            ->count();

        if ($overdueCount > 0) {
            $score -= 25;
            $issues[] = "{$overdueCount} facture(s) en retard >90j";
        }

        return [
            'score' => max(0, $score),
            'label' => $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'Faible')),
            'metrics' => [
                'has_vat_number' => !empty($company->vat_number),
                'has_enterprise_number' => !empty($company->enterprise_number),
                'address_complete' => !empty($company->street) && !empty($company->postal_code),
                'has_iban' => !empty($company->default_iban),
                'overdue_invoices' => $overdueCount,
            ],
            'issues' => $issues,
        ];
    }

    /**
     * Calculate payment behavior score (for receivables).
     */
    protected function calculatePaymentBehaviorScore($company, int $months): array
    {
        $periodStart = now()->subMonths($months);

        $allInvoices = Invoice::where('company_id', $company->id)
            ->where('type', 'sale')
            ->where('invoice_date', '>=', $periodStart)
            ->whereIn('status', ['sent', 'paid', 'partial'])
            ->get();

        if ($allInvoices->isEmpty()) {
            return [
                'score' => 100,
                'label' => 'N/A',
                'metrics' => [],
                'issues' => ['Pas de factures à analyser'],
            ];
        }

        $paidInvoices = $allInvoices->where('status', 'paid');
        $paymentRate = ($paidInvoices->count() / $allInvoices->count()) * 100;

        $overdueInvoices = $allInvoices->filter(function ($invoice) {
            return in_array($invoice->status, ['sent', 'partial']) && $invoice->due_date < now();
        });

        $score = 100;
        $issues = [];

        // Penalty for low payment rate
        if ($paymentRate < 50) {
            $score = 30;
            $issues[] = 'Taux de paiement très faible (<50%)';
        } elseif ($paymentRate < 70) {
            $score = 60;
            $issues[] = 'Taux de paiement moyen';
        } elseif ($paymentRate < 90) {
            $score = 80;
        }

        // Penalty for overdue
        $overdueCount = $overdueInvoices->count();
        if ($overdueCount > 5) {
            $score -= 30;
            $issues[] = "Nombreux impayés ({$overdueCount})";
        } elseif ($overdueCount > 2) {
            $score -= 15;
            $issues[] = "Quelques impayés ({$overdueCount})";
        }

        return [
            'score' => max(0, $score),
            'label' => $score >= 80 ? 'Excellent' : ($score >= 60 ? 'Bon' : ($score >= 40 ? 'Moyen' : 'Faible')),
            'metrics' => [
                'total_invoices' => $allInvoices->count(),
                'paid_invoices' => $paidInvoices->count(),
                'payment_rate' => round($paymentRate, 1),
                'overdue_count' => $overdueCount,
                'total_outstanding' => (float) $overdueInvoices->sum('amount_due'),
            ],
            'issues' => $issues,
        ];
    }

    /**
     * Get health status label and color.
     */
    protected function getHealthStatus(int $score): array
    {
        if ($score >= 80) {
            return ['label' => 'Excellent', 'color' => 'green'];
        } elseif ($score >= 60) {
            return ['label' => 'Bon', 'color' => 'blue'];
        } elseif ($score >= 40) {
            return ['label' => 'Moyen', 'color' => 'yellow'];
        } elseif ($score >= 20) {
            return ['label' => 'Faible', 'color' => 'orange'];
        }

        return ['label' => 'Critique', 'color' => 'red'];
    }

    /**
     * Assess overall risk level.
     */
    protected function assessRiskLevel(int $overallScore, array $components): array
    {
        $criticalIssues = [];

        // Check for critical red flags
        if ($components['financial']['score'] < 40) {
            $criticalIssues[] = 'Santé financière critique';
        }

        if ($components['compliance']['score'] < 50) {
            $criticalIssues[] = 'Problèmes de conformité majeurs';
        }

        if ($components['activity']['score'] === 0) {
            $criticalIssues[] = 'Aucune activité détectée';
        }

        if ($overallScore < 40 || count($criticalIssues) >= 2) {
            $level = 'Élevé';
            $color = 'red';
        } elseif ($overallScore < 60 || count($criticalIssues) === 1) {
            $level = 'Moyen';
            $color = 'orange';
        } else {
            $level = 'Faible';
            $color = 'green';
        }

        return [
            'level' => $level,
            'color' => $color,
            'critical_issues' => $criticalIssues,
        ];
    }

    /**
     * Generate actionable recommendations.
     */
    protected function generateRecommendations(array $components, int $overallScore): array
    {
        $recommendations = [];

        // Activity recommendations
        if ($components['activity']['score'] < 60) {
            $recommendations[] = [
                'category' => 'Activité',
                'priority' => 'high',
                'action' => 'Contacter le client pour comprendre la baisse d\'activité',
            ];
        }

        // Financial recommendations
        if ($components['financial']['score'] < 60) {
            if ($components['financial']['metrics']['margin_percent'] < 10) {
                $recommendations[] = [
                    'category' => 'Financier',
                    'priority' => 'high',
                    'action' => 'Analyser les coûts et optimiser la rentabilité',
                ];
            }
        }

        // Compliance recommendations
        foreach ($components['compliance']['issues'] as $issue) {
            $priority = str_contains($issue, 'TVA') ? 'high' : 'medium';

            $recommendations[] = [
                'category' => 'Conformité',
                'priority' => $priority,
                'action' => 'Résoudre : ' . $issue,
            ];
        }

        // Payment recommendations
        if ($components['payment']['score'] < 70) {
            $recommendations[] = [
                'category' => 'Recouvrement',
                'priority' => 'high',
                'action' => 'Suivre les impayés et relancer les clients',
            ];
        }

        // Overall health
        if ($overallScore < 50) {
            $recommendations[] = [
                'category' => 'Général',
                'priority' => 'urgent',
                'action' => 'Planifier une réunion de revue avec le client',
            ];
        }

        return $recommendations;
    }

    /**
     * Find client mandate by company ID or name.
     */
    protected function findClientMandate(array $input, $firm): ?ClientMandate
    {
        $query = ClientMandate::where('accounting_firm_id', $firm->id)
            ->where('status', 'active')
            ->with(['company', 'manager']);

        // Try by company_id first
        if (!empty($input['company_id'])) {
            return $query->where('company_id', $input['company_id'])->first();
        }

        // Try by company_name
        if (!empty($input['company_name'])) {
            return $query->whereHas('company', function ($q) use ($input) {
                $q->where('name', 'like', '%' . $input['company_name'] . '%');
            })->first();
        }

        return null;
    }
}
