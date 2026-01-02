<?php

namespace App\Http\Controllers;

use App\Services\AI\TreasuryForecastService;
use Illuminate\Http\Request;

class TreasuryController extends Controller
{
    protected TreasuryForecastService $forecastService;

    public function __construct(TreasuryForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * Display treasury forecast dashboard.
     */
    public function index(Request $request)
    {
        $days = $request->get('days', 90);
        $scenario = $request->get('scenario', 'realistic');

        // Generate forecast
        $forecast = $this->forecastService->generateForecast($days);

        return view('treasury.forecast', [
            'forecast' => $forecast,
            'days' => $days,
            'selectedScenario' => $scenario,
        ]);
    }

    /**
     * Get forecast data as JSON (for AJAX requests).
     */
    public function getForecast(Request $request)
    {
        $request->validate([
            'days' => 'nullable|integer|min:7|max:365',
            'scenario' => 'nullable|in:optimistic,realistic,pessimistic',
        ]);

        $days = $request->get('days', 90);
        $scenario = $request->get('scenario', 'realistic');

        try {
            $forecast = $this->forecastService->generateForecast($days);

            // Apply scenario adjustments
            if ($scenario !== 'realistic') {
                $forecast = $this->applyScenarioAdjustments($forecast, $scenario);
            }

            return response()->json([
                'success' => true,
                'data' => $forecast,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply scenario adjustments to forecast.
     */
    protected function applyScenarioAdjustments(array $forecast, string $scenario): array
    {
        $adjustments = match($scenario) {
            'optimistic' => [
                'inflow_multiplier' => 1.15,  // +15% inflows
                'outflow_multiplier' => 0.90,  // -10% outflows
                'probability_boost' => 0.10,   // +10% payment probability
                'label' => 'Scénario optimiste',
                'description' => 'Hypothèse: meilleure collecte des paiements et réduction des dépenses',
            ],
            'pessimistic' => [
                'inflow_multiplier' => 0.85,   // -15% inflows
                'outflow_multiplier' => 1.10,  // +10% outflows
                'probability_boost' => -0.15,  // -15% payment probability
                'label' => 'Scénario pessimiste',
                'description' => 'Hypothèse: retards de paiement et augmentation des dépenses',
            ],
            default => [
                'inflow_multiplier' => 1.0,
                'outflow_multiplier' => 1.0,
                'probability_boost' => 0.0,
                'label' => 'Scénario réaliste',
                'description' => 'Basé sur l\'historique et les tendances actuelles',
            ],
        };

        // Adjust daily forecast
        foreach ($forecast['daily_forecast'] as &$day) {
            $day['inflows'] *= $adjustments['inflow_multiplier'];
            $day['outflows'] *= $adjustments['outflow_multiplier'];
            $day['net_flow'] = $day['inflows'] - $day['outflows'];
            $day['balance'] = ($day['balance'] ?? $forecast['current_balance']) + $day['net_flow'];
        }

        $forecast['scenario'] = $adjustments;

        return $forecast;
    }

    /**
     * Run what-if simulation.
     */
    public function whatIf(Request $request)
    {
        $request->validate([
            'scenario_type' => 'required|in:revenue_increase,cost_reduction,delayed_payment,new_expense',
            'amount' => 'required|numeric',
            'start_date' => 'nullable|date',
            'frequency' => 'nullable|in:once,monthly,weekly',
        ]);

        try {
            $baseForecast = $this->forecastService->generateForecast(90);
            $modifiedForecast = $this->applyWhatIfScenario(
                $baseForecast,
                $request->scenario_type,
                $request->amount,
                $request->start_date,
                $request->frequency
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'base' => $baseForecast,
                    'modified' => $modifiedForecast,
                    'impact' => $this->calculateImpact($baseForecast, $modifiedForecast),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply what-if scenario to forecast.
     */
    protected function applyWhatIfScenario(array $forecast, string $type, float $amount, ?string $startDate, ?string $frequency): array
    {
        $modified = $forecast;
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : now();

        foreach ($modified['daily_forecast'] as $index => &$day) {
            $dayDate = \Carbon\Carbon::parse($day['date']);

            // Skip days before start date
            if ($dayDate->lt($startDate)) {
                continue;
            }

            $shouldApply = match($frequency) {
                'once' => $dayDate->eq($startDate),
                'monthly' => $dayDate->day === $startDate->day,
                'weekly' => $dayDate->dayOfWeek === $startDate->dayOfWeek,
                default => $dayDate->eq($startDate),
            };

            if ($shouldApply) {
                match($type) {
                    'revenue_increase' => $day['inflows'] += $amount,
                    'cost_reduction' => $day['outflows'] -= $amount,
                    'delayed_payment' => $day['inflows'] -= $amount,
                    'new_expense' => $day['outflows'] += $amount,
                };

                // Recalculate balance
                $day['net_flow'] = $day['inflows'] - $day['outflows'];
                if ($index > 0) {
                    $day['balance'] = $modified['daily_forecast'][$index - 1]['balance'] + $day['net_flow'];
                }
            }
        }

        return $modified;
    }

    /**
     * Calculate impact between two forecasts.
     */
    protected function calculateImpact(array $base, array $modified): array
    {
        $lastDayBase = end($base['daily_forecast']);
        $lastDayModified = end($modified['daily_forecast']);

        $balanceImpact = $lastDayModified['balance'] - $lastDayBase['balance'];
        $balanceImpactPct = $lastDayBase['balance'] != 0
            ? ($balanceImpact / abs($lastDayBase['balance'])) * 100
            : 0;

        // Find worst day
        $worstDayBase = collect($base['daily_forecast'])->sortBy('balance')->first();
        $worstDayModified = collect($modified['daily_forecast'])->sortBy('balance')->first();

        return [
            'balance_impact' => $balanceImpact,
            'balance_impact_pct' => round($balanceImpactPct, 2),
            'worst_day_base' => [
                'date' => $worstDayBase['date'],
                'balance' => $worstDayBase['balance'],
            ],
            'worst_day_modified' => [
                'date' => $worstDayModified['date'],
                'balance' => $worstDayModified['balance'],
            ],
            'worst_day_improvement' => $worstDayModified['balance'] - $worstDayBase['balance'],
        ];
    }

    /**
     * Export forecast to PDF.
     */
    public function exportPDF(Request $request)
    {
        $days = $request->get('days', 90);
        $forecast = $this->forecastService->generateForecast($days);

        $pdf = \PDF::loadView('treasury.forecast-pdf', compact('forecast'));

        return $pdf->download('prevision-tresorerie-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export forecast to Excel.
     */
    public function exportExcel(Request $request)
    {
        $days = $request->get('days', 90);
        $forecast = $this->forecastService->generateForecast($days);

        // Generate CSV for now (could use PhpSpreadsheet for full Excel)
        $csv = $this->generateForecastCSV($forecast);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="prevision-tresorerie-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Generate CSV from forecast data.
     */
    protected function generateForecastCSV(array $forecast): string
    {
        $output = fopen('php://temp', 'r+');

        // Header
        fputcsv($output, ['Date', 'Entrées', 'Sorties', 'Flux net', 'Solde', 'Solde minimum sécurisé']);

        // Data rows
        foreach ($forecast['daily_forecast'] as $day) {
            fputcsv($output, [
                $day['date'],
                number_format($day['inflows'], 2),
                number_format($day['outflows'], 2),
                number_format($day['net_flow'], 2),
                number_format($day['balance'], 2),
                number_format($day['min_safe_balance'] ?? 0, 2),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
