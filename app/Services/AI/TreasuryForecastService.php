<?php

namespace App\Services\AI;

use App\Models\Invoice;
use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\RecurringTransaction;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TreasuryForecastService
{
    protected int $companyId;
    protected int $forecastDays = 90;

    public function __construct()
    {
        $this->companyId = auth()->user()->current_company_id ?? 0;
    }

    /**
     * Generate comprehensive treasury forecast
     */
    public function generateForecast(int $days = 90): array
    {
        $this->forecastDays = $days;

        $currentBalance = $this->getCurrentBalance();
        $scheduledInflows = $this->getScheduledInflows();
        $scheduledOutflows = $this->getScheduledOutflows();
        $recurringItems = $this->getRecurringItems();
        $predictedInflows = $this->predictInflows();
        $predictedOutflows = $this->predictOutflows();

        $dailyForecast = $this->buildDailyForecast(
            $currentBalance,
            $scheduledInflows,
            $scheduledOutflows,
            $recurringItems,
            $predictedInflows,
            $predictedOutflows
        );

        $alerts = $this->generateAlerts($dailyForecast);
        $scenarios = $this->generateScenarios($dailyForecast);
        $recommendations = $this->generateRecommendations($dailyForecast, $alerts);

        return [
            'current_balance' => $currentBalance,
            'forecast_period' => [
                'start' => now()->toDateString(),
                'end' => now()->addDays($days)->toDateString(),
            ],
            'daily_forecast' => $dailyForecast,
            'summary' => $this->calculateSummary($dailyForecast),
            'alerts' => $alerts,
            'scenarios' => $scenarios,
            'recommendations' => $recommendations,
            'confidence_score' => $this->calculateConfidenceScore($dailyForecast),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get current bank balance
     */
    protected function getCurrentBalance(): float
    {
        return BankAccount::where('company_id', $this->companyId)
            ->where('is_active', true)
            ->sum('current_balance');
    }

    /**
     * Get scheduled inflows (unpaid sales invoices)
     */
    protected function getScheduledInflows(): Collection
    {
        return Invoice::where('company_id', $this->companyId)
            ->where('type', 'out')
            ->whereIn('status', ['validated', 'sent', 'partial'])
            ->where('due_date', '<=', now()->addDays($this->forecastDays))
            ->get()
            ->map(function ($invoice) {
                $probability = $this->calculatePaymentProbability($invoice);
                $expectedDate = $this->predictPaymentDate($invoice);

                return [
                    'id' => $invoice->id,
                    'type' => 'invoice_receivable',
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $invoice->partner->name ?? 'Unknown',
                    'due_date' => $invoice->due_date->toDateString(),
                    'expected_date' => $expectedDate->toDateString(),
                    'amount' => $invoice->amount_due,
                    'probability' => $probability,
                    'expected_amount' => $invoice->amount_due * $probability,
                    'days_overdue' => max(0, now()->diffInDays($invoice->due_date, false)),
                    'risk_level' => $this->assessReceivableRisk($invoice),
                ];
            });
    }

    /**
     * Get scheduled outflows (unpaid purchase invoices)
     */
    protected function getScheduledOutflows(): Collection
    {
        return Invoice::where('company_id', $this->companyId)
            ->where('type', 'in')
            ->whereIn('status', ['validated', 'partial'])
            ->where('due_date', '<=', now()->addDays($this->forecastDays))
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'type' => 'invoice_payable',
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $invoice->partner->name ?? 'Unknown',
                    'due_date' => $invoice->due_date->toDateString(),
                    'amount' => -abs($invoice->amount_due),
                    'probability' => 1.0, // We assume we'll pay our bills
                    'expected_amount' => -abs($invoice->amount_due),
                    'priority' => $this->calculatePaymentPriority($invoice),
                    'can_defer' => $invoice->due_date->isFuture(),
                ];
            });
    }

    /**
     * Get recurring transactions (salaries, rent, subscriptions)
     */
    protected function getRecurringItems(): Collection
    {
        $recurring = collect();
        $endDate = now()->addDays($this->forecastDays);

        // Get from database if available
        $dbRecurring = RecurringTransaction::where('company_id', $this->companyId)
            ->where('is_active', true)
            ->get();

        foreach ($dbRecurring as $item) {
            $dates = $this->calculateRecurrenceDates($item, now(), $endDate);

            foreach ($dates as $date) {
                $recurring->push([
                    'id' => $item->id,
                    'type' => 'recurring',
                    'description' => $item->description,
                    'date' => $date->toDateString(),
                    'amount' => $item->type === 'expense' ? -abs($item->amount) : $item->amount,
                    'probability' => 0.98,
                    'expected_amount' => ($item->type === 'expense' ? -abs($item->amount) : $item->amount) * 0.98,
                    'category' => $item->category,
                ]);
            }
        }

        // Add detected recurring patterns from historical data
        $detectedPatterns = $this->detectRecurringPatterns();
        foreach ($detectedPatterns as $pattern) {
            $dates = $this->calculatePatternDates($pattern, now(), $endDate);

            foreach ($dates as $date) {
                $recurring->push([
                    'id' => null,
                    'type' => 'detected_recurring',
                    'description' => $pattern['description'],
                    'date' => $date->toDateString(),
                    'amount' => $pattern['average_amount'],
                    'probability' => $pattern['confidence'],
                    'expected_amount' => $pattern['average_amount'] * $pattern['confidence'],
                    'category' => $pattern['category'] ?? 'other',
                    'detected' => true,
                ]);
            }
        }

        return $recurring;
    }

    /**
     * Predict additional inflows based on historical patterns
     */
    protected function predictInflows(): Collection
    {
        $predictions = collect();

        // Analyze historical sales patterns
        $historicalSales = Invoice::where('company_id', $this->companyId)
            ->where('type', 'out')
            ->where('invoice_date', '>=', now()->subYear())
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_incl_vat) as total')
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month');

        if ($historicalSales->isEmpty()) {
            return $predictions;
        }

        // Calculate average and trend
        $avgMonthlySales = $historicalSales->avg();
        $trend = $this->calculateTrend($historicalSales->values()->toArray());

        // Predict future sales
        for ($week = 1; $week <= ceil($this->forecastDays / 7); $week++) {
            $weekStart = now()->addWeeks($week)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $predictedAmount = ($avgMonthlySales / 4) * (1 + $trend);
            $confidence = max(0.3, 1 - ($week * 0.05)); // Decreasing confidence over time

            $predictions->push([
                'type' => 'predicted_sales',
                'description' => 'Ventes prévues',
                'date' => $weekStart->toDateString(),
                'week' => $week,
                'amount' => round($predictedAmount, 2),
                'probability' => $confidence,
                'expected_amount' => round($predictedAmount * $confidence, 2),
            ]);
        }

        return $predictions;
    }

    /**
     * Predict outflows based on historical patterns
     */
    protected function predictOutflows(): Collection
    {
        $predictions = collect();

        // Analyze historical purchase patterns
        $historicalPurchases = Invoice::where('company_id', $this->companyId)
            ->where('type', 'in')
            ->where('invoice_date', '>=', now()->subYear())
            ->selectRaw('MONTH(invoice_date) as month, SUM(total_incl_vat) as total')
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month');

        if ($historicalPurchases->isEmpty()) {
            return $predictions;
        }

        $avgMonthlyPurchases = $historicalPurchases->avg();
        $trend = $this->calculateTrend($historicalPurchases->values()->toArray());

        // Add VAT payment predictions (quarterly in Belgium)
        $nextVatDate = $this->getNextVatPaymentDate();
        if ($nextVatDate && $nextVatDate->diffInDays(now()) <= $this->forecastDays) {
            $estimatedVat = $this->estimateVatPayment();
            $predictions->push([
                'type' => 'predicted_vat',
                'description' => 'Paiement TVA estimé',
                'date' => $nextVatDate->toDateString(),
                'amount' => -abs($estimatedVat),
                'probability' => 0.95,
                'expected_amount' => -abs($estimatedVat) * 0.95,
            ]);
        }

        // Add predicted operating expenses
        for ($week = 1; $week <= ceil($this->forecastDays / 7); $week++) {
            $weekStart = now()->addWeeks($week)->startOfWeek();
            $predictedAmount = ($avgMonthlyPurchases / 4) * (1 + $trend);
            $confidence = max(0.4, 1 - ($week * 0.04));

            $predictions->push([
                'type' => 'predicted_expenses',
                'description' => 'Dépenses prévues',
                'date' => $weekStart->toDateString(),
                'week' => $week,
                'amount' => -abs(round($predictedAmount, 2)),
                'probability' => $confidence,
                'expected_amount' => -abs(round($predictedAmount * $confidence, 2)),
            ]);
        }

        return $predictions;
    }

    /**
     * Build daily forecast
     */
    protected function buildDailyForecast(
        float $currentBalance,
        Collection $inflows,
        Collection $outflows,
        Collection $recurring,
        Collection $predictedInflows,
        Collection $predictedOutflows
    ): array {
        $forecast = [];
        $runningBalance = $currentBalance;

        for ($day = 0; $day <= $this->forecastDays; $day++) {
            $date = now()->addDays($day);
            $dateStr = $date->toDateString();

            $dayInflows = $inflows->where('expected_date', $dateStr)->sum('expected_amount')
                + $recurring->where('date', $dateStr)->where('amount', '>', 0)->sum('expected_amount')
                + $predictedInflows->where('date', $dateStr)->sum('expected_amount');

            $dayOutflows = $outflows->where('due_date', $dateStr)->sum('expected_amount')
                + $recurring->where('date', $dateStr)->where('amount', '<', 0)->sum('expected_amount')
                + $predictedOutflows->where('date', $dateStr)->sum('expected_amount');

            $netChange = $dayInflows + $dayOutflows;
            $runningBalance += $netChange;

            $forecast[] = [
                'date' => $dateStr,
                'day_of_week' => $date->dayOfWeek,
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => $this->isBelgianHoliday($date),
                'inflows' => round($dayInflows, 2),
                'outflows' => round($dayOutflows, 2),
                'net_change' => round($netChange, 2),
                'balance' => round($runningBalance, 2),
                'inflow_details' => [
                    'invoices' => $inflows->where('expected_date', $dateStr)->values(),
                    'recurring' => $recurring->where('date', $dateStr)->where('amount', '>', 0)->values(),
                    'predicted' => $predictedInflows->where('date', $dateStr)->values(),
                ],
                'outflow_details' => [
                    'invoices' => $outflows->where('due_date', $dateStr)->values(),
                    'recurring' => $recurring->where('date', $dateStr)->where('amount', '<', 0)->values(),
                    'predicted' => $predictedOutflows->where('date', $dateStr)->values(),
                ],
            ];
        }

        return $forecast;
    }

    /**
     * Generate alerts based on forecast
     */
    protected function generateAlerts(array $forecast): array
    {
        $alerts = [];

        foreach ($forecast as $day) {
            // Low balance alert
            if ($day['balance'] < 0) {
                $alerts[] = [
                    'type' => 'critical',
                    'category' => 'negative_balance',
                    'date' => $day['date'],
                    'message' => "Solde négatif prévu: " . number_format($day['balance'], 2, ',', ' ') . " €",
                    'amount' => $day['balance'],
                    'action_required' => true,
                ];
            } elseif ($day['balance'] < 5000) {
                $alerts[] = [
                    'type' => 'warning',
                    'category' => 'low_balance',
                    'date' => $day['date'],
                    'message' => "Solde faible prévu: " . number_format($day['balance'], 2, ',', ' ') . " €",
                    'amount' => $day['balance'],
                    'action_required' => false,
                ];
            }

            // Large outflow alert
            if (abs($day['outflows']) > 10000) {
                $alerts[] = [
                    'type' => 'info',
                    'category' => 'large_outflow',
                    'date' => $day['date'],
                    'message' => "Sortie importante prévue: " . number_format(abs($day['outflows']), 2, ',', ' ') . " €",
                    'amount' => $day['outflows'],
                    'action_required' => false,
                ];
            }
        }

        // Sort by severity and date
        usort($alerts, function ($a, $b) {
            $severityOrder = ['critical' => 0, 'warning' => 1, 'info' => 2];
            $severityCompare = ($severityOrder[$a['type']] ?? 3) <=> ($severityOrder[$b['type']] ?? 3);
            return $severityCompare !== 0 ? $severityCompare : strcmp($a['date'], $b['date']);
        });

        return $alerts;
    }

    /**
     * Generate different scenarios (optimistic, realistic, pessimistic)
     */
    protected function generateScenarios(array $forecast): array
    {
        $lastDay = end($forecast);

        $realisticBalance = $lastDay['balance'];
        $optimisticBalance = $realisticBalance;
        $pessimisticBalance = $realisticBalance;

        // Calculate based on probability variations
        foreach ($forecast as $day) {
            // Optimistic: all receivables paid, some expenses deferred
            $optimisticBalance += $day['inflows'] * 0.2 + abs($day['outflows']) * 0.1;

            // Pessimistic: receivables delayed, all expenses realized
            $pessimisticBalance -= $day['inflows'] * 0.3 + abs($day['outflows']) * 0.15;
        }

        return [
            'optimistic' => [
                'label' => 'Scénario optimiste',
                'final_balance' => round($optimisticBalance, 2),
                'probability' => 0.2,
                'assumptions' => [
                    'Tous les clients paient à temps',
                    'Certaines dépenses peuvent être reportées',
                    'Nouvelles ventes au-dessus de la moyenne',
                ],
            ],
            'realistic' => [
                'label' => 'Scénario réaliste',
                'final_balance' => round($realisticBalance, 2),
                'probability' => 0.6,
                'assumptions' => [
                    'Paiements selon l\'historique',
                    'Dépenses conformes aux prévisions',
                    'Ventes dans la moyenne',
                ],
            ],
            'pessimistic' => [
                'label' => 'Scénario pessimiste',
                'final_balance' => round($pessimisticBalance, 2),
                'probability' => 0.2,
                'assumptions' => [
                    'Retards de paiement clients',
                    'Dépenses imprévues',
                    'Ventes en baisse',
                ],
            ],
        ];
    }

    /**
     * Generate actionable recommendations
     */
    protected function generateRecommendations(array $forecast, array $alerts): array
    {
        $recommendations = [];

        // Check for cash flow issues
        $criticalAlerts = array_filter($alerts, fn($a) => $a['type'] === 'critical');

        if (!empty($criticalAlerts)) {
            $firstCritical = reset($criticalAlerts);
            $daysUntilCritical = Carbon::parse($firstCritical['date'])->diffInDays(now());

            $recommendations[] = [
                'priority' => 'high',
                'category' => 'cash_flow',
                'title' => 'Action urgente sur la trésorerie',
                'description' => "Un solde négatif est prévu dans {$daysUntilCritical} jours.",
                'actions' => [
                    'Relancer les factures clients en retard',
                    'Négocier des délais de paiement avec les fournisseurs',
                    'Envisager un financement court terme',
                ],
            ];

            // Find overdue receivables to chase
            $overdueReceivables = Invoice::where('company_id', $this->companyId)
                ->where('type', 'out')
                ->whereIn('status', ['validated', 'sent'])
                ->where('due_date', '<', now())
                ->sum('amount_due');

            if ($overdueReceivables > 0) {
                $recommendations[] = [
                    'priority' => 'high',
                    'category' => 'receivables',
                    'title' => 'Relances clients à effectuer',
                    'description' => number_format($overdueReceivables, 2, ',', ' ') . " € de créances en retard",
                    'actions' => [
                        'Envoyer des rappels automatiques',
                        'Contacter les clients par téléphone',
                        'Proposer des plans de paiement',
                    ],
                ];
            }
        }

        // Check for optimization opportunities
        $avgBalance = collect($forecast)->avg('balance');
        if ($avgBalance > 50000) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'optimization',
                'title' => 'Optimisation de trésorerie',
                'description' => 'Votre solde moyen est élevé. Considérez des placements court terme.',
                'actions' => [
                    'Ouvrir un compte à terme',
                    'Investir dans des fonds monétaires',
                    'Négocier de meilleurs taux bancaires',
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate payment probability based on customer history
     */
    protected function calculatePaymentProbability(Invoice $invoice): float
    {
        // Get historical payment behavior
        $paidInvoices = Invoice::where('company_id', $this->companyId)
            ->where('partner_id', $invoice->partner_id)
            ->where('type', 'out')
            ->where('status', 'paid')
            ->get();

        if ($paidInvoices->isEmpty()) {
            return 0.7; // Default for new customers
        }

        // Calculate average days to payment
        $avgDays = $paidInvoices->map(function ($inv) {
            if ($inv->paid_at) {
                return $inv->paid_at->diffInDays($inv->due_date);
            }
            return 0;
        })->avg();

        $daysUntilDue = now()->diffInDays($invoice->due_date, false);
        $daysOverdue = max(0, -$daysUntilDue);

        // Probability decreases with days overdue
        $baseProbability = 0.95;
        $overdueDecay = 0.02; // 2% decrease per day overdue

        $probability = $baseProbability - ($daysOverdue * $overdueDecay);

        // Adjust based on customer history
        if ($avgDays > 15) {
            $probability *= 0.9; // Customer tends to pay late
        }

        return max(0.1, min(1.0, $probability));
    }

    /**
     * Predict when payment will actually be received
     */
    protected function predictPaymentDate(Invoice $invoice): Carbon
    {
        $paidInvoices = Invoice::where('company_id', $this->companyId)
            ->where('partner_id', $invoice->partner_id)
            ->where('type', 'out')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->get();

        if ($paidInvoices->isEmpty()) {
            return $invoice->due_date->copy()->addDays(7);
        }

        $avgDaysAfterDue = $paidInvoices->map(function ($inv) {
            return $inv->paid_at->diffInDays($inv->due_date);
        })->avg();

        return $invoice->due_date->copy()->addDays(max(0, round($avgDaysAfterDue)));
    }

    /**
     * Assess risk level of receivable
     */
    protected function assessReceivableRisk(Invoice $invoice): string
    {
        $daysOverdue = max(0, now()->diffInDays($invoice->due_date, false) * -1);

        if ($daysOverdue > 90) return 'critical';
        if ($daysOverdue > 60) return 'high';
        if ($daysOverdue > 30) return 'medium';
        if ($daysOverdue > 0) return 'low';

        return 'none';
    }

    /**
     * Calculate payment priority for suppliers
     */
    protected function calculatePaymentPriority(Invoice $invoice): int
    {
        $priority = 5; // Medium by default

        // Earlier due dates = higher priority
        $daysUntilDue = now()->diffInDays($invoice->due_date, false);
        if ($daysUntilDue < 0) $priority = 1;
        elseif ($daysUntilDue < 7) $priority = 2;
        elseif ($daysUntilDue < 14) $priority = 3;

        // Strategic suppliers get higher priority
        if ($invoice->partner && $invoice->partner->is_strategic) {
            $priority = max(1, $priority - 1);
        }

        return $priority;
    }

    /**
     * Detect recurring transaction patterns
     */
    protected function detectRecurringPatterns(): array
    {
        $transactions = BankTransaction::where('company_id', $this->companyId)
            ->where('transaction_date', '>=', now()->subYear())
            ->get();

        $patterns = [];
        $grouped = $transactions->groupBy(function ($t) {
            return $this->normalizeDescription($t->description);
        });

        foreach ($grouped as $description => $items) {
            if ($items->count() < 3) continue;

            // Check if amounts are consistent
            $amounts = $items->pluck('amount');
            $avgAmount = $amounts->avg();
            $stdDev = $this->standardDeviation($amounts->toArray());
            $coefficientOfVariation = $avgAmount != 0 ? abs($stdDev / $avgAmount) : 1;

            if ($coefficientOfVariation > 0.2) continue; // Too much variation

            // Check if timing is consistent
            $dates = $items->pluck('transaction_date')->sort()->values();
            $intervals = [];
            for ($i = 1; $i < $dates->count(); $i++) {
                $intervals[] = $dates[$i]->diffInDays($dates[$i - 1]);
            }

            if (empty($intervals)) continue;

            $avgInterval = array_sum($intervals) / count($intervals);
            $intervalStdDev = $this->standardDeviation($intervals);

            // Determine frequency
            $frequency = null;
            $confidence = 0;

            if (abs($avgInterval - 30) < 5 && $intervalStdDev < 10) {
                $frequency = 'monthly';
                $confidence = 0.8;
            } elseif (abs($avgInterval - 7) < 2 && $intervalStdDev < 3) {
                $frequency = 'weekly';
                $confidence = 0.85;
            } elseif (abs($avgInterval - 365) < 30 && $intervalStdDev < 20) {
                $frequency = 'yearly';
                $confidence = 0.7;
            }

            if ($frequency) {
                $patterns[] = [
                    'description' => $description,
                    'frequency' => $frequency,
                    'average_amount' => round($avgAmount, 2),
                    'average_interval_days' => round($avgInterval),
                    'last_occurrence' => $dates->last()->toDateString(),
                    'confidence' => $confidence,
                    'occurrences' => $items->count(),
                ];
            }
        }

        return $patterns;
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary(array $forecast): array
    {
        $balances = array_column($forecast, 'balance');
        $inflows = array_column($forecast, 'inflows');
        $outflows = array_column($forecast, 'outflows');

        return [
            'starting_balance' => $forecast[0]['balance'] ?? 0,
            'ending_balance' => end($balances),
            'min_balance' => min($balances),
            'max_balance' => max($balances),
            'avg_balance' => round(array_sum($balances) / count($balances), 2),
            'total_inflows' => round(array_sum($inflows), 2),
            'total_outflows' => round(array_sum($outflows), 2),
            'net_change' => round(array_sum($inflows) + array_sum($outflows), 2),
            'days_below_zero' => count(array_filter($balances, fn($b) => $b < 0)),
            'lowest_balance_date' => $forecast[array_search(min($balances), $balances)]['date'] ?? null,
        ];
    }

    /**
     * Calculate confidence score
     */
    protected function calculateConfidenceScore(array $forecast): float
    {
        // Confidence decreases over time
        $baseDays = min(30, $this->forecastDays);
        $extendedDays = max(0, $this->forecastDays - 30);

        $baseConfidence = 0.85;
        $decayRate = 0.01; // 1% per day after 30 days

        return max(0.5, $baseConfidence - ($extendedDays * $decayRate));
    }

    /**
     * Helper: Calculate trend
     */
    protected function calculateTrend(array $values): float
    {
        if (count($values) < 2) return 0;

        $n = count($values);
        $x = range(1, $n);
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $values));
        $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));

        $denominator = $n * $sumX2 - $sumX * $sumX;
        if ($denominator == 0) return 0;

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $avgY = $sumY / $n;

        return $avgY != 0 ? $slope / $avgY : 0;
    }

    /**
     * Helper: Standard deviation
     */
    protected function standardDeviation(array $values): float
    {
        if (count($values) < 2) return 0;

        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / count($values);

        return sqrt($variance);
    }

    /**
     * Helper: Normalize description
     */
    protected function normalizeDescription(string $description): string
    {
        // Remove numbers, dates, and extra whitespace
        $normalized = preg_replace('/\d{2}[\/\-]\d{2}[\/\-]\d{2,4}/', '', $description);
        $normalized = preg_replace('/\d+[,\.]\d{2}/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim(strtolower($normalized));
    }

    /**
     * Helper: Check Belgian holidays
     */
    protected function isBelgianHoliday(Carbon $date): bool
    {
        $holidays = [
            $date->year . '-01-01', // Nouvel an
            $date->year . '-05-01', // Fête du travail
            $date->year . '-07-21', // Fête nationale
            $date->year . '-08-15', // Assomption
            $date->year . '-11-01', // Toussaint
            $date->year . '-11-11', // Armistice
            $date->year . '-12-25', // Noël
        ];

        // Add Easter-based holidays
        $easter = Carbon::createFromTimestamp(easter_date($date->year));
        $holidays[] = $easter->toDateString();
        $holidays[] = $easter->copy()->addDay()->toDateString();
        $holidays[] = $easter->copy()->addDays(39)->toDateString(); // Ascension
        $holidays[] = $easter->copy()->addDays(50)->toDateString(); // Pentecôte

        return in_array($date->toDateString(), $holidays);
    }

    /**
     * Get next VAT payment date
     */
    protected function getNextVatPaymentDate(): ?Carbon
    {
        // Monthly declarations due on the 20th
        $current = now();
        $nextDate = Carbon::create($current->year, $current->month, 20);

        if ($nextDate->isPast()) {
            $nextDate->addMonth();
        }

        return $nextDate;
    }

    /**
     * Estimate VAT payment
     */
    protected function estimateVatPayment(): float
    {
        // Get last quarter's VAT balance
        $startDate = now()->subMonths(3)->startOfMonth();
        $endDate = now()->subMonth()->endOfMonth();

        $salesVat = Invoice::where('company_id', $this->companyId)
            ->where('type', 'out')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('vat_amount');

        $purchaseVat = Invoice::where('company_id', $this->companyId)
            ->where('type', 'in')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('vat_amount');

        return max(0, $salesVat - $purchaseVat);
    }

    /**
     * Calculate recurrence dates
     */
    protected function calculateRecurrenceDates($item, Carbon $start, Carbon $end): array
    {
        $dates = [];
        $current = Carbon::parse($item->next_occurrence_date ?? $item->start_date);

        while ($current <= $end) {
            if ($current >= $start) {
                $dates[] = $current->copy();
            }

            $current = match ($item->frequency) {
                'daily' => $current->addDay(),
                'weekly' => $current->addWeek(),
                'monthly' => $current->addMonth(),
                'quarterly' => $current->addMonths(3),
                'yearly' => $current->addYear(),
                default => $current->addMonth(),
            };
        }

        return $dates;
    }

    /**
     * Calculate pattern dates
     */
    protected function calculatePatternDates(array $pattern, Carbon $start, Carbon $end): array
    {
        $dates = [];
        $last = Carbon::parse($pattern['last_occurrence']);
        $interval = $pattern['average_interval_days'];

        $current = $last->copy()->addDays($interval);

        while ($current <= $end) {
            if ($current >= $start) {
                $dates[] = $current->copy();
            }
            $current->addDays($interval);
        }

        return $dates;
    }
}
