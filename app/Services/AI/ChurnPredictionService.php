<?php

namespace App\Services\AI;

use App\Models\Partner;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChurnPredictionService
{
    /**
     * Predict churn risk for all customers
     */
    public function predictChurnForAllCustomers(string $companyId): array
    {
        $partners = Partner::where('company_id', $companyId)
            ->whereHas('invoices')
            ->get();

        $predictions = [];

        foreach ($partners as $partner) {
            $prediction = $this->predictCustomerChurn($companyId, $partner->id);

            if ($prediction['churn_risk_score'] > 30) {
                $predictions[] = array_merge([
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ], $prediction);
            }
        }

        // Sort by churn risk descending
        usort($predictions, fn($a, $b) => $b['churn_risk_score'] - $a['churn_risk_score']);

        return $predictions;
    }

    /**
     * Predict churn risk for a specific customer
     */
    public function predictCustomerChurn(string $companyId, string $partnerId): array
    {
        $signals = $this->detectChurnSignals($companyId, $partnerId);
        $churnScore = $this->calculateChurnScore($signals);
        $churnLevel = $this->getChurnLevel($churnScore);
        $recommendations = $this->generateRetentionRecommendations($signals, $churnScore);

        return [
            'churn_risk_score' => round($churnScore, 2),
            'churn_level' => $churnLevel,
            'signals' => $signals,
            'recommendations' => $recommendations,
            'confidence' => $this->calculateConfidence($signals),
        ];
    }

    /**
     * Detect churn signals for a customer
     */
    protected function detectChurnSignals(string $companyId, string $partnerId): array
    {
        $signals = [];

        // Signal 1: Declining order volume
        $volumeSignal = $this->detectVolumeDecline($companyId, $partnerId);
        if ($volumeSignal) {
            $signals[] = $volumeSignal;
        }

        // Signal 2: Decreasing order frequency
        $frequencySignal = $this->detectFrequencyDecline($companyId, $partnerId);
        if ($frequencySignal) {
            $signals[] = $frequencySignal;
        }

        // Signal 3: Reducing order value
        $valueSignal = $this->detectValueDecline($companyId, $partnerId);
        if ($valueSignal) {
            $signals[] = $valueSignal;
        }

        // Signal 4: Increasing payment delays
        $paymentSignal = $this->detectPaymentDelayIncrease($companyId, $partnerId);
        if ($paymentSignal) {
            $signals[] = $paymentSignal;
        }

        // Signal 5: Margin reduction
        $marginSignal = $this->detectMarginDecline($companyId, $partnerId);
        if ($marginSignal) {
            $signals[] = $marginSignal;
        }

        // Signal 6: No recent activity
        $inactivitySignal = $this->detectInactivity($companyId, $partnerId);
        if ($inactivitySignal) {
            $signals[] = $inactivitySignal;
        }

        // Signal 7: Communication decrease
        $communicationSignal = $this->detectCommunicationDecrease($companyId, $partnerId);
        if ($communicationSignal) {
            $signals[] = $communicationSignal;
        }

        return $signals;
    }

    /**
     * Detect volume decline
     */
    protected function detectVolumeDecline(string $companyId, string $partnerId): ?array
    {
        $last3MonthsVolume = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->whereBetween('issue_date', [now()->subMonths(3), now()])
            ->count();

        $previous3MonthsVolume = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->whereBetween('issue_date', [now()->subMonths(6), now()->subMonths(3)])
            ->count();

        if ($previous3MonthsVolume >= 3 && $last3MonthsVolume < $previous3MonthsVolume * 0.5) {
            $decline = round((($previous3MonthsVolume - $last3MonthsVolume) / $previous3MonthsVolume) * 100, 1);

            return [
                'type' => 'volume_decline',
                'severity' => 'high',
                'weight' => 25,
                'description' => "Baisse de {$decline}% du volume de commandes (dernier trimestre)",
                'data' => [
                    'last_3_months' => $last3MonthsVolume,
                    'previous_3_months' => $previous3MonthsVolume,
                    'decline_percentage' => $decline,
                ],
            ];
        }

        return null;
    }

    /**
     * Detect frequency decline
     */
    protected function detectFrequencyDecline(string $companyId, string $partnerId): ?array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->orderBy('issue_date', 'desc')
            ->limit(10)
            ->get();

        if ($invoices->count() < 4) {
            return null;
        }

        // Calculate average gap between invoices (recent vs historical)
        $recentInvoices = $invoices->take(3);
        $historicalInvoices = $invoices->skip(3)->take(7);

        $recentGaps = $this->calculateInvoiceGaps($recentInvoices);
        $historicalGaps = $this->calculateInvoiceGaps($historicalInvoices);

        $recentAvgGap = count($recentGaps) > 0 ? array_sum($recentGaps) / count($recentGaps) : 0;
        $historicalAvgGap = count($historicalGaps) > 0 ? array_sum($historicalGaps) / count($historicalGaps) : 0;

        if ($historicalAvgGap > 0 && $recentAvgGap > $historicalAvgGap * 2) {
            return [
                'type' => 'frequency_decline',
                'severity' => 'high',
                'weight' => 20,
                'description' => "Fréquence de commande réduite (écart moyen passé de {$historicalAvgGap} à {$recentAvgGap} jours)",
                'data' => [
                    'recent_avg_gap_days' => round($recentAvgGap, 1),
                    'historical_avg_gap_days' => round($historicalAvgGap, 1),
                ],
            ];
        }

        return null;
    }

    /**
     * Detect value decline
     */
    protected function detectValueDecline(string $companyId, string $partnerId): ?array
    {
        $last3MonthsValue = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->whereBetween('issue_date', [now()->subMonths(3), now()])
            ->sum('total_amount');

        $previous3MonthsValue = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->whereBetween('issue_date', [now()->subMonths(6), now()->subMonths(3)])
            ->sum('total_amount');

        if ($previous3MonthsValue > 100 && $last3MonthsValue < $previous3MonthsValue * 0.6) {
            $decline = round((($previous3MonthsValue - $last3MonthsValue) / $previous3MonthsValue) * 100, 1);

            return [
                'type' => 'value_decline',
                'severity' => 'high',
                'weight' => 25,
                'description' => "Baisse de {$decline}% du CA généré (dernier trimestre)",
                'data' => [
                    'last_3_months_value' => round($last3MonthsValue, 2),
                    'previous_3_months_value' => round($previous3MonthsValue, 2),
                    'decline_percentage' => $decline,
                ],
            ];
        }

        return null;
    }

    /**
     * Detect payment delay increase
     */
    protected function detectPaymentDelayIncrease(string $companyId, string $partnerId): ?array
    {
        $recentInvoices = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->where('status', 'paid')
            ->whereNotNull('payment_date')
            ->orderBy('issue_date', 'desc')
            ->limit(5)
            ->get();

        $historicalInvoices = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->where('status', 'paid')
            ->whereNotNull('payment_date')
            ->orderBy('issue_date', 'desc')
            ->skip(5)
            ->limit(10)
            ->get();

        if ($recentInvoices->count() < 3 || $historicalInvoices->count() < 5) {
            return null;
        }

        $recentDelays = $this->calculatePaymentDelays($recentInvoices);
        $historicalDelays = $this->calculatePaymentDelays($historicalInvoices);

        $recentAvgDelay = count($recentDelays) > 0 ? array_sum($recentDelays) / count($recentDelays) : 0;
        $historicalAvgDelay = count($historicalDelays) > 0 ? array_sum($historicalDelays) / count($historicalDelays) : 0;

        if ($recentAvgDelay > $historicalAvgDelay + 10) {
            return [
                'type' => 'payment_delay_increase',
                'severity' => 'medium',
                'weight' => 15,
                'description' => "Augmentation des délais de paiement (+{$recentAvgDelay} jours en moyenne récemment)",
                'data' => [
                    'recent_avg_delay' => round($recentAvgDelay, 1),
                    'historical_avg_delay' => round($historicalAvgDelay, 1),
                ],
            ];
        }

        return null;
    }

    /**
     * Detect margin decline
     */
    protected function detectMarginDecline(string $companyId, string $partnerId): ?array
    {
        // This would require cost data - simplified version
        // In real implementation, calculate profit margin if cost data is available

        return null; // Placeholder - implement when cost tracking is available
    }

    /**
     * Detect inactivity
     */
    protected function detectInactivity(string $companyId, string $partnerId): ?array
    {
        $lastInvoice = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->orderBy('issue_date', 'desc')
            ->first();

        if (!$lastInvoice) {
            return null;
        }

        $daysSinceLastInvoice = Carbon::parse($lastInvoice->issue_date)->diffInDays(now());

        // Get typical frequency
        $allInvoices = Invoice::where('company_id', $companyId)
            ->where('partner_id', $partnerId)
            ->orderBy('issue_date', 'desc')
            ->limit(10)
            ->get();

        $gaps = $this->calculateInvoiceGaps($allInvoices);
        $avgGap = count($gaps) > 0 ? array_sum($gaps) / count($gaps) : 30;

        if ($daysSinceLastInvoice > $avgGap * 2 && $daysSinceLastInvoice > 60) {
            return [
                'type' => 'inactivity',
                'severity' => 'high',
                'weight' => 30,
                'description' => "Aucune commande depuis {$daysSinceLastInvoice} jours (fréquence habituelle: {$avgGap} jours)",
                'data' => [
                    'days_since_last_invoice' => $daysSinceLastInvoice,
                    'typical_gap_days' => round($avgGap, 1),
                ],
            ];
        }

        return null;
    }

    /**
     * Detect communication decrease
     */
    protected function detectCommunicationDecrease(string $companyId, string $partnerId): ?array
    {
        // This would require communication tracking (emails, calls, meetings)
        // Placeholder for future implementation

        return null;
    }

    /**
     * Calculate invoice gaps in days
     */
    protected function calculateInvoiceGaps($invoices): array
    {
        $gaps = [];

        for ($i = 0; $i < $invoices->count() - 1; $i++) {
            $current = Carbon::parse($invoices[$i]->issue_date);
            $next = Carbon::parse($invoices[$i + 1]->issue_date);
            $gaps[] = $next->diffInDays($current);
        }

        return $gaps;
    }

    /**
     * Calculate payment delays in days
     */
    protected function calculatePaymentDelays($invoices): array
    {
        $delays = [];

        foreach ($invoices as $invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $paymentDate = Carbon::parse($invoice->payment_date);
            $delays[] = $dueDate->diffInDays($paymentDate, false);
        }

        return $delays;
    }

    /**
     * Calculate churn score based on signals
     */
    protected function calculateChurnScore(array $signals): float
    {
        if (empty($signals)) {
            return 0;
        }

        $totalWeight = array_sum(array_column($signals, 'weight'));
        $weightedScore = 0;

        foreach ($signals as $signal) {
            $severityMultiplier = match($signal['severity']) {
                'high' => 1.0,
                'medium' => 0.6,
                'low' => 0.3,
                default => 0.5,
            };

            $weightedScore += $signal['weight'] * $severityMultiplier;
        }

        return min(100, $weightedScore);
    }

    /**
     * Get churn level label
     */
    protected function getChurnLevel(float $churnScore): string
    {
        if ($churnScore >= 70) {
            return 'critical';
        } elseif ($churnScore >= 50) {
            return 'high';
        } elseif ($churnScore >= 30) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Calculate prediction confidence
     */
    protected function calculateConfidence(array $signals): float
    {
        // Confidence based on number and severity of signals
        $signalCount = count($signals);
        $highSeverityCount = count(array_filter($signals, fn($s) => $s['severity'] === 'high'));

        $confidence = min(100, ($signalCount * 15) + ($highSeverityCount * 10));

        return round($confidence, 1);
    }

    /**
     * Generate retention recommendations
     */
    protected function generateRetentionRecommendations(array $signals, float $churnScore): array
    {
        $recommendations = [];

        if ($churnScore >= 70) {
            $recommendations[] = "🚨 URGENT: Contacter immédiatement le client pour discussion";
            $recommendations[] = "Proposer une réunion pour comprendre les besoins actuels";
            $recommendations[] = "Envisager une offre spéciale ou remise de rétention";
        } elseif ($churnScore >= 50) {
            $recommendations[] = "⚠️ Planifier un appel de suivi dans la semaine";
            $recommendations[] = "Envoyer un questionnaire de satisfaction";
        }

        foreach ($signals as $signal) {
            switch ($signal['type']) {
                case 'volume_decline':
                case 'value_decline':
                    $recommendations[] = "Analyser la raison de la baisse de commandes - Concurrence? Qualité?";
                    $recommendations[] = "Proposer une promotion ou nouveau catalogue produits";
                    break;

                case 'frequency_decline':
                    $recommendations[] = "Rappeler régulièrement notre offre (newsletter, appels)";
                    break;

                case 'payment_delay_increase':
                    $recommendations[] = "Vérifier s'il y a des problèmes de trésorerie chez le client";
                    $recommendations[] = "Proposer un plan de paiement adapté";
                    break;

                case 'inactivity':
                    $recommendations[] = "Relancer le client avec une offre personnalisée";
                    $recommendations[] = "Vérifier s'il est passé à un concurrent";
                    break;
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = "Client stable - Maintenir le contact régulier";
        }

        return array_unique($recommendations);
    }

    /**
     * Get churn dashboard summary
     */
    public function getDashboardSummary(string $companyId): array
    {
        $predictions = $this->predictChurnForAllCustomers($companyId);

        $criticalCount = count(array_filter($predictions, fn($p) => $p['churn_level'] === 'critical'));
        $highCount = count(array_filter($predictions, fn($p) => $p['churn_level'] === 'high'));
        $mediumCount = count(array_filter($predictions, fn($p) => $p['churn_level'] === 'medium'));

        return [
            'total_at_risk' => count($predictions),
            'critical_risk_count' => $criticalCount,
            'high_risk_count' => $highCount,
            'medium_risk_count' => $mediumCount,
            'top_at_risk_customers' => array_slice($predictions, 0, 10),
        ];
    }
}
