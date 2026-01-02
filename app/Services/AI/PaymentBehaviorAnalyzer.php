<?php

namespace App\Services\AI;

use App\Models\Invoice;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentBehaviorAnalyzer
{
    /**
     * Analyze payment behavior for all customers
     */
    public function analyzeAllCustomers(string $companyId): array
    {
        $partners = Partner::where('company_id', $companyId)
            ->whereHas('invoices', function ($query) {
                $query->where('status', 'paid')
                      ->orWhere('status', 'sent');
            })
            ->get();

        $analyses = [];

        foreach ($partners as $partner) {
            $analysis = $this->analyzeCustomerPaymentBehavior($companyId, $partner->id);

            if ($analysis['risk_score'] > 0) {
                $analyses[] = array_merge([
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ], $analysis);
            }
        }

        // Sort by risk score descending
        usort($analyses, fn($a, $b) => $b['risk_score'] - $a['risk_score']);

        return $analyses;
    }

    /**
     * Analyze payment behavior for a specific customer
     */
    public function analyzeCustomerPaymentBehavior(string $companyId, string $partnerId): array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->whereNotNull('issue_date')
            ->orderBy('issue_date', 'desc')
            ->limit(50)
            ->get();

        if ($invoices->count() < 3) {
            return [
                'risk_score' => 0,
                'risk_level' => 'unknown',
                'avg_delay_days' => 0,
                'on_time_percentage' => 0,
                'total_invoices' => $invoices->count(),
                'patterns' => [],
                'recommendations' => ['Historique insuffisant pour analyse'],
            ];
        }

        // Calculate payment metrics
        $paidInvoices = $invoices->where('status', 'paid')->where('payment_date', '!=', null);

        $paymentDelays = [];
        foreach ($paidInvoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $paymentDate = Carbon::parse($invoice->payment_date);
            $delay = $dueDate->diffInDays($paymentDate, false);
            $paymentDelays[] = $delay;
        }

        $avgDelay = count($paymentDelays) > 0 ? array_sum($paymentDelays) / count($paymentDelays) : 0;
        $maxDelay = count($paymentDelays) > 0 ? max($paymentDelays) : 0;
        $onTimeCount = count(array_filter($paymentDelays, fn($d) => $d <= 0));
        $onTimePercentage = $paidInvoices->count() > 0
            ? ($onTimeCount / $paidInvoices->count()) * 100
            : 0;

        // Detect trends (recent vs historical)
        $recentInvoices = $paidInvoices->take(10);
        $recentDelays = [];
        foreach ($recentInvoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $paymentDate = Carbon::parse($invoice->payment_date);
            $recentDelays[] = $dueDate->diffInDays($paymentDate, false);
        }

        $recentAvgDelay = count($recentDelays) > 0 ? array_sum($recentDelays) / count($recentDelays) : 0;
        $trend = $recentAvgDelay > $avgDelay ? 'deteriorating' : ($recentAvgDelay < $avgDelay ? 'improving' : 'stable');

        // Calculate risk score (0-100)
        $riskScore = $this->calculateRiskScore($avgDelay, $onTimePercentage, $trend, $invoices);

        // Detect patterns
        $patterns = $this->detectPaymentPatterns($invoices, $paymentDelays);

        // Generate recommendations
        $recommendations = $this->generateRecommendations($riskScore, $avgDelay, $trend, $patterns);

        // Determine risk level
        $riskLevel = $this->getRiskLevel($riskScore);

        return [
            'risk_score' => round($riskScore, 2),
            'risk_level' => $riskLevel,
            'avg_delay_days' => round($avgDelay, 1),
            'recent_avg_delay_days' => round($recentAvgDelay, 1),
            'max_delay_days' => $maxDelay,
            'on_time_percentage' => round($onTimePercentage, 1),
            'payment_trend' => $trend,
            'total_invoices' => $invoices->count(),
            'paid_invoices' => $paidInvoices->count(),
            'unpaid_invoices' => $invoices->where('status', 'sent')->count(),
            'patterns' => $patterns,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Predict payment date for an invoice
     */
    public function predictPaymentDate(string $companyId, string $partnerId, Carbon $dueDate): array
    {
        $behavior = $this->analyzeCustomerPaymentBehavior($companyId, $partnerId);

        if ($behavior['total_invoices'] < 3) {
            return [
                'predicted_date' => $dueDate->copy(),
                'confidence' => 0,
                'delay_days' => 0,
                'reason' => 'Historique insuffisant',
            ];
        }

        // Use average delay to predict
        $predictedDelay = max(0, $behavior['avg_delay_days']);
        $predictedDate = $dueDate->copy()->addDays((int) $predictedDelay);

        // Adjust for day of week (avoid weekends)
        if ($predictedDate->isWeekend()) {
            $predictedDate = $predictedDate->next(Carbon::MONDAY);
        }

        // Calculate confidence based on consistency
        $confidence = min(100, $behavior['on_time_percentage'] + (100 - $behavior['risk_score'])) / 2;

        return [
            'predicted_date' => $predictedDate,
            'confidence' => round($confidence, 1),
            'delay_days' => (int) $predictedDelay,
            'reason' => "Basé sur historique de paiement (moyenne: " . round($behavior['avg_delay_days'], 1) . " jours de retard)",
        ];
    }

    /**
     * Calculate risk score (0-100)
     */
    protected function calculateRiskScore(float $avgDelay, float $onTimePercentage, string $trend, $invoices): float
    {
        $score = 0;

        // Factor 1: Average delay (40% weight)
        if ($avgDelay > 60) {
            $score += 40;
        } elseif ($avgDelay > 30) {
            $score += 30;
        } elseif ($avgDelay > 15) {
            $score += 20;
        } elseif ($avgDelay > 7) {
            $score += 10;
        }

        // Factor 2: On-time percentage (30% weight)
        $score += (100 - $onTimePercentage) * 0.3;

        // Factor 3: Trend (20% weight)
        if ($trend === 'deteriorating') {
            $score += 20;
        } elseif ($trend === 'stable' && $avgDelay > 0) {
            $score += 10;
        }

        // Factor 4: Currently overdue invoices (10% weight)
        $overdueCount = $invoices->filter(function ($invoice) {
            return $invoice->status === 'sent' &&
                   Carbon::parse($invoice->due_date)->isPast();
        })->count();

        if ($overdueCount > 0) {
            $score += min(10, $overdueCount * 2);
        }

        return min(100, $score);
    }

    /**
     * Detect payment patterns
     */
    protected function detectPaymentPatterns($invoices, array $paymentDelays): array
    {
        $patterns = [];

        // Pattern 1: Consistent lateness
        $lateCount = count(array_filter($paymentDelays, fn($d) => $d > 7));
        if ($lateCount > count($paymentDelays) * 0.7) {
            $patterns[] = [
                'type' => 'consistent_lateness',
                'description' => 'Paiements systématiquement en retard',
                'severity' => 'high',
            ];
        }

        // Pattern 2: Seasonality
        $paidInvoices = $invoices->where('status', 'paid')->where('payment_date', '!=', null');
        $monthlyDelays = [];
        foreach ($paidInvoices as $invoice) {
            $month = Carbon::parse($invoice->payment_date)->month;
            if (!isset($monthlyDelays[$month])) {
                $monthlyDelays[$month] = [];
            }
            $dueDate = Carbon::parse($invoice->due_date);
            $paymentDate = Carbon::parse($invoice->payment_date);
            $monthlyDelays[$month][] = $dueDate->diffInDays($paymentDate, false);
        }

        $avgDelaysByMonth = [];
        foreach ($monthlyDelays as $month => $delays) {
            $avgDelaysByMonth[$month] = array_sum($delays) / count($delays);
        }

        if (count($avgDelaysByMonth) >= 6) {
            $maxDelayMonth = array_keys($avgDelaysByMonth, max($avgDelaysByMonth))[0];
            $minDelayMonth = array_keys($avgDelaysByMonth, min($avgDelaysByMonth))[0];

            if (max($avgDelaysByMonth) - min($avgDelaysByMonth) > 14) {
                $patterns[] = [
                    'type' => 'seasonality',
                    'description' => "Retards plus importants en " . $this->getMonthName($maxDelayMonth),
                    'severity' => 'medium',
                ];
            }
        }

        // Pattern 3: Recent deterioration
        $recentInvoices = $invoices->take(5)->where('status', 'paid');
        $recentDelays = [];
        foreach ($recentInvoices as $invoice) {
            if ($invoice->payment_date) {
                $dueDate = Carbon::parse($invoice->due_date);
                $paymentDate = Carbon::parse($invoice->payment_date);
                $recentDelays[] = $dueDate->diffInDays($paymentDate, false);
            }
        }

        $recentAvg = count($recentDelays) > 0 ? array_sum($recentDelays) / count($recentDelays) : 0;
        $overallAvg = count($paymentDelays) > 0 ? array_sum($paymentDelays) / count($paymentDelays) : 0;

        if ($recentAvg > $overallAvg + 10) {
            $patterns[] = [
                'type' => 'recent_deterioration',
                'description' => 'Dégradation récente des délais de paiement',
                'severity' => 'high',
            ];
        }

        // Pattern 4: Invoice amount impact
        $largeInvoices = $invoices->where('total_amount', '>', 1000)->where('status', 'paid');
        $smallInvoices = $invoices->where('total_amount', '<=', 1000)->where('status', 'paid');

        $largeDelays = [];
        foreach ($largeInvoices as $invoice) {
            if ($invoice->payment_date) {
                $dueDate = Carbon::parse($invoice->due_date);
                $paymentDate = Carbon::parse($invoice->payment_date);
                $largeDelays[] = $dueDate->diffInDays($paymentDate, false);
            }
        }

        $smallDelays = [];
        foreach ($smallInvoices as $invoice) {
            if ($invoice->payment_date) {
                $dueDate = Carbon::parse($invoice->due_date);
                $paymentDate = Carbon::parse($invoice->payment_date);
                $smallDelays[] = $dueDate->diffInDays($paymentDate, false);
            }
        }

        $largeAvg = count($largeDelays) > 0 ? array_sum($largeDelays) / count($largeDelays) : 0;
        $smallAvg = count($smallDelays) > 0 ? array_sum($smallDelays) / count($smallDelays) : 0;

        if ($largeAvg > $smallAvg + 7 && count($largeDelays) >= 3) {
            $patterns[] = [
                'type' => 'amount_impact',
                'description' => 'Retards plus importants sur factures élevées',
                'severity' => 'medium',
            ];
        }

        return $patterns;
    }

    /**
     * Generate recommendations based on analysis
     */
    protected function generateRecommendations(float $riskScore, float $avgDelay, string $trend, array $patterns): array
    {
        $recommendations = [];

        if ($riskScore >= 70) {
            $recommendations[] = "⚠️ RISQUE ÉLEVÉ: Exiger un paiement à la livraison ou un acompte";
            $recommendations[] = "Contacter le client pour négocier des conditions de paiement";
            $recommendations[] = "Envisager un plan de paiement échelonné pour grandes factures";
        } elseif ($riskScore >= 40) {
            $recommendations[] = "Envoyer des rappels automatiques 7 jours avant échéance";
            $recommendations[] = "Appeler le client 3 jours après échéance";
        }

        if ($avgDelay > 30) {
            $recommendations[] = "Réduire les délais de paiement (ex: 30 jours → 15 jours)";
            $recommendations[] = "Proposer un escompte pour paiement anticipé (ex: -2% si paiement sous 7 jours)";
        }

        if ($trend === 'deteriorating') {
            $recommendations[] = "⚠️ Tendance négative détectée - Contacter le client rapidement";
            $recommendations[] = "Vérifier la santé financière du client";
        }

        foreach ($patterns as $pattern) {
            if ($pattern['type'] === 'amount_impact') {
                $recommendations[] = "Fractionner les grandes factures en plusieurs paiements";
            }

            if ($pattern['type'] === 'seasonality') {
                $recommendations[] = "Anticiper les retards saisonniers - Ajuster la trésorerie";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = "Client fiable - Maintenir les conditions actuelles";
        }

        return $recommendations;
    }

    /**
     * Get risk level label
     */
    protected function getRiskLevel(float $riskScore): string
    {
        if ($riskScore >= 70) {
            return 'high';
        } elseif ($riskScore >= 40) {
            return 'medium';
        } elseif ($riskScore >= 20) {
            return 'low';
        } else {
            return 'very_low';
        }
    }

    /**
     * Get month name in French
     */
    protected function getMonthName(int $month): string
    {
        $months = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];

        return $months[$month] ?? 'inconnu';
    }

    /**
     * Get payment behavior summary for dashboard
     */
    public function getDashboardSummary(string $companyId): array
    {
        $analyses = $this->analyzeAllCustomers($companyId);

        $highRiskCustomers = array_filter($analyses, fn($a) => $a['risk_score'] >= 70);
        $mediumRiskCustomers = array_filter($analyses, fn($a) => $a['risk_score'] >= 40 && $a['risk_score'] < 70);

        $avgDelayOverall = count($analyses) > 0
            ? array_sum(array_column($analyses, 'avg_delay_days')) / count($analyses)
            : 0;

        return [
            'total_customers_analyzed' => count($analyses),
            'high_risk_count' => count($highRiskCustomers),
            'medium_risk_count' => count($mediumRiskCustomers),
            'avg_delay_days' => round($avgDelayOverall, 1),
            'top_risks' => array_slice($analyses, 0, 10),
        ];
    }
}
