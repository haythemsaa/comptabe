<?php

namespace App\Services\Reports;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportGeneratorService
{
    /**
     * Generate balance sheet (Bilan) for a given period.
     */
    public function generateBalanceSheet(Company $company, Carbon $startDate, Carbon $endDate): array
    {
        // Assets (Actif)
        $assets = [
            'fixed_assets' => $this->getBalanceByAccountRange($company, '20', '27', $startDate, $endDate),
            'current_assets' => [
                'inventory' => $this->getBalanceByAccountRange($company, '30', '37', $startDate, $endDate),
                'receivables' => $this->getBalanceByAccountRange($company, '40', '41', $startDate, $endDate),
                'investments' => $this->getBalanceByAccountRange($company, '50', '53', $startDate, $endDate),
                'cash' => $this->getBalanceByAccountRange($company, '54', '58', $startDate, $endDate),
            ],
        ];

        // Liabilities (Passif)
        $liabilities = [
            'equity' => $this->getBalanceByAccountRange($company, '10', '15', $startDate, $endDate),
            'provisions' => $this->getBalanceByAccountRange($company, '16', '16', $startDate, $endDate),
            'long_term_debt' => $this->getBalanceByAccountRange($company, '17', '17', $startDate, $endDate),
            'current_liabilities' => [
                'short_term_debt' => $this->getBalanceByAccountRange($company, '42', '48', $startDate, $endDate),
                'payables' => $this->getBalanceByAccountRange($company, '40', '40', $startDate, $endDate),
                'tax_liabilities' => $this->getBalanceByAccountRange($company, '45', '45', $startDate, $endDate),
            ],
        ];

        // Calculate totals
        $totalAssets = $assets['fixed_assets'] +
                       $assets['current_assets']['inventory'] +
                       $assets['current_assets']['receivables'] +
                       $assets['current_assets']['investments'] +
                       $assets['current_assets']['cash'];

        $totalLiabilities = $liabilities['equity'] +
                           $liabilities['provisions'] +
                           $liabilities['long_term_debt'] +
                           $liabilities['current_liabilities']['short_term_debt'] +
                           $liabilities['current_liabilities']['payables'] +
                           $liabilities['current_liabilities']['tax_liabilities'];

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'totals' => [
                'assets' => $totalAssets,
                'liabilities' => $totalLiabilities,
                'balanced' => abs($totalAssets - $totalLiabilities) < 0.01,
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Generate profit & loss statement (Compte de résultats).
     */
    public function generateProfitLoss(Company $company, Carbon $startDate, Carbon $endDate): array
    {
        // Revenue (Produits)
        $revenue = [
            'sales' => $this->getBalanceByAccountRange($company, '70', '74', $startDate, $endDate),
            'financial_income' => $this->getBalanceByAccountRange($company, '75', '75', $startDate, $endDate),
            'other_income' => $this->getBalanceByAccountRange($company, '76', '77', $startDate, $endDate),
        ];

        // Expenses (Charges)
        $expenses = [
            'purchases' => $this->getBalanceByAccountRange($company, '60', '60', $startDate, $endDate),
            'services' => $this->getBalanceByAccountRange($company, '61', '61', $startDate, $endDate),
            'salaries' => $this->getBalanceByAccountRange($company, '62', '62', $startDate, $endDate),
            'depreciation' => $this->getBalanceByAccountRange($company, '63', '63', $startDate, $endDate),
            'other_expenses' => $this->getBalanceByAccountRange($company, '64', '64', $startDate, $endDate),
            'financial_expenses' => $this->getBalanceByAccountRange($company, '65', '65', $startDate, $endDate),
            'exceptional_expenses' => $this->getBalanceByAccountRange($company, '66', '66', $startDate, $endDate),
        ];

        // Totals
        $totalRevenue = array_sum($revenue);
        $totalExpenses = array_sum($expenses);
        $netProfit = $totalRevenue - $totalExpenses;

        // Calculate margins
        $grossMargin = $revenue['sales'] - $expenses['purchases'];
        $ebitda = $netProfit + $expenses['depreciation'] + $expenses['financial_expenses'];
        $ebit = $netProfit + $expenses['financial_expenses'];

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'totals' => [
                'revenue' => $totalRevenue,
                'expenses' => $totalExpenses,
                'gross_margin' => $grossMargin,
                'ebitda' => $ebitda,
                'ebit' => $ebit,
                'net_profit' => $netProfit,
            ],
            'ratios' => [
                'gross_margin_pct' => $revenue['sales'] > 0 ? ($grossMargin / $revenue['sales']) * 100 : 0,
                'ebitda_margin_pct' => $totalRevenue > 0 ? ($ebitda / $totalRevenue) * 100 : 0,
                'net_margin_pct' => $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0,
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Generate comparative reports (multiple periods).
     */
    public function generateComparative(Company $company, string $reportType, array $periods): array
    {
        $results = [];

        foreach ($periods as $period) {
            $startDate = Carbon::parse($period['start']);
            $endDate = Carbon::parse($period['end']);

            $results[] = [
                'period' => $period,
                'data' => $reportType === 'balance_sheet'
                    ? $this->generateBalanceSheet($company, $startDate, $endDate)
                    : $this->generateProfitLoss($company, $startDate, $endDate),
            ];
        }

        // Calculate variations between periods
        if (count($results) >= 2) {
            $results = $this->calculateVariations($results, $reportType);
        }

        return [
            'type' => $reportType,
            'periods' => $results,
            'comparison_count' => count($results),
        ];
    }

    /**
     * Get balance for accounts in a specific range.
     */
    protected function getBalanceByAccountRange(Company $company, string $fromCode, string $toCode, Carbon $startDate, Carbon $endDate): float
    {
        return (float) DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.company_id', $company->id)
            ->where('accounts.code', '>=', $fromCode)
            ->where('accounts.code', '<=', $toCode . 'ZZZZZ')
            ->whereBetween('journal_entries.date', [$startDate, $endDate])
            ->whereNull('journal_entries.deleted_at')
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;
    }

    /**
     * Calculate variations between periods.
     */
    protected function calculateVariations(array $results, string $reportType): array
    {
        for ($i = 1; $i < count($results); $i++) {
            $current = $results[$i]['data'];
            $previous = $results[$i - 1]['data'];

            if ($reportType === 'profit_loss') {
                // Calculate variations for P&L
                $results[$i]['variations'] = [
                    'revenue' => [
                        'absolute' => $current['totals']['revenue'] - $previous['totals']['revenue'],
                        'percentage' => $previous['totals']['revenue'] > 0
                            ? (($current['totals']['revenue'] - $previous['totals']['revenue']) / $previous['totals']['revenue']) * 100
                            : 0,
                    ],
                    'expenses' => [
                        'absolute' => $current['totals']['expenses'] - $previous['totals']['expenses'],
                        'percentage' => $previous['totals']['expenses'] > 0
                            ? (($current['totals']['expenses'] - $previous['totals']['expenses']) / $previous['totals']['expenses']) * 100
                            : 0,
                    ],
                    'net_profit' => [
                        'absolute' => $current['totals']['net_profit'] - $previous['totals']['net_profit'],
                        'percentage' => $previous['totals']['net_profit'] != 0
                            ? (($current['totals']['net_profit'] - $previous['totals']['net_profit']) / abs($previous['totals']['net_profit'])) * 100
                            : 0,
                    ],
                ];
            } else {
                // Calculate variations for Balance Sheet
                $results[$i]['variations'] = [
                    'assets' => [
                        'absolute' => $current['totals']['assets'] - $previous['totals']['assets'],
                        'percentage' => $previous['totals']['assets'] > 0
                            ? (($current['totals']['assets'] - $previous['totals']['assets']) / $previous['totals']['assets']) * 100
                            : 0,
                    ],
                    'liabilities' => [
                        'absolute' => $current['totals']['liabilities'] - $previous['totals']['liabilities'],
                        'percentage' => $previous['totals']['liabilities'] > 0
                            ? (($current['totals']['liabilities'] - $previous['totals']['liabilities']) / $previous['totals']['liabilities']) * 100
                            : 0,
                    ],
                ];
            }
        }

        return $results;
    }

    /**
     * Generate trial balance (Balance de vérification).
     */
    public function generateTrialBalance(Company $company, Carbon $endDate): array
    {
        $accounts = DB::table('accounts')
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit'),
                DB::raw('COALESCE(SUM(journal_entry_lines.debit - journal_entry_lines.credit), 0) as balance'),
            ])
            ->leftJoin('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', function ($join) use ($endDate) {
                $join->on('journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                     ->where('journal_entries.date', '<=', $endDate)
                     ->whereNull('journal_entries.deleted_at');
            })
            ->where('accounts.company_id', $company->id)
            ->whereNull('accounts.deleted_at')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->having(DB::raw('COALESCE(SUM(journal_entry_lines.debit), 0) + COALESCE(SUM(journal_entry_lines.credit), 0)'), '>', 0)
            ->orderBy('accounts.code')
            ->get();

        $totalDebit = $accounts->sum('total_debit');
        $totalCredit = $accounts->sum('total_credit');

        return [
            'accounts' => $accounts,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
            'as_of_date' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Generate cash flow statement (Tableau de flux de trésorerie).
     */
    public function generateCashFlow(Company $company, Carbon $startDate, Carbon $endDate): array
    {
        // Operating activities (Activités d'exploitation)
        $operating = [
            'net_profit' => $this->getBalanceByAccountRange($company, '70', '67', $startDate, $endDate),
            'depreciation' => $this->getBalanceByAccountRange($company, '63', '63', $startDate, $endDate),
            'working_capital_change' => $this->calculateWorkingCapitalChange($company, $startDate, $endDate),
        ];

        // Investing activities (Activités d'investissement)
        $investing = [
            'asset_purchases' => $this->getBalanceByAccountRange($company, '20', '27', $startDate, $endDate),
            'asset_sales' => 0, // Would need specific tracking
        ];

        // Financing activities (Activités de financement)
        $financing = [
            'capital_increase' => $this->getBalanceByAccountRange($company, '10', '11', $startDate, $endDate),
            'loans_received' => $this->getBalanceByAccountRange($company, '17', '17', $startDate, $endDate),
            'dividends_paid' => 0, // Would need specific tracking
        ];

        $cashFromOperating = $operating['net_profit'] + $operating['depreciation'] + $operating['working_capital_change'];
        $cashFromInvesting = -$investing['asset_purchases'] + $investing['asset_sales'];
        $cashFromFinancing = $financing['capital_increase'] + $financing['loans_received'] - $financing['dividends_paid'];

        $netCashFlow = $cashFromOperating + $cashFromInvesting + $cashFromFinancing;

        return [
            'operating' => $operating,
            'investing' => $investing,
            'financing' => $financing,
            'summary' => [
                'cash_from_operating' => $cashFromOperating,
                'cash_from_investing' => $cashFromInvesting,
                'cash_from_financing' => $cashFromFinancing,
                'net_cash_flow' => $netCashFlow,
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Calculate working capital change.
     */
    protected function calculateWorkingCapitalChange(Company $company, Carbon $startDate, Carbon $endDate): float
    {
        $receivablesChange = $this->getBalanceByAccountRange($company, '40', '41', $startDate, $endDate);
        $inventoryChange = $this->getBalanceByAccountRange($company, '30', '37', $startDate, $endDate);
        $payablesChange = $this->getBalanceByAccountRange($company, '44', '44', $startDate, $endDate);

        return -($receivablesChange + $inventoryChange - $payablesChange);
    }
}
