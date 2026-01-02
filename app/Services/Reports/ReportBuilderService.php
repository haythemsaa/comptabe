<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\BankTransaction;
use App\Models\Partner;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportBuilderService
{
    protected ?Company $company = null;
    protected ?Carbon $dateFrom = null;
    protected ?Carbon $dateTo = null;
    protected array $filters = [];
    protected string $currency = 'EUR';

    /**
     * Types de rapports disponibles
     */
    const REPORT_TYPES = [
        'profit_loss' => [
            'name' => 'Compte de résultat',
            'description' => 'Revenus, charges et résultat net',
            'category' => 'financial',
        ],
        'balance_sheet' => [
            'name' => 'Bilan',
            'description' => 'Actif, passif et capitaux propres',
            'category' => 'financial',
        ],
        'vat_summary' => [
            'name' => 'Déclaration TVA',
            'description' => 'Résumé TVA pour déclaration périodique',
            'category' => 'tax',
        ],
        'vat_listing' => [
            'name' => 'Listing TVA clients',
            'description' => 'Listing annuel des clients assujettis',
            'category' => 'tax',
        ],
        'cash_flow' => [
            'name' => 'Flux de trésorerie',
            'description' => 'Entrées et sorties de trésorerie',
            'category' => 'financial',
        ],
        'aged_receivables' => [
            'name' => 'Balance âgée clients',
            'description' => 'Créances clients par ancienneté',
            'category' => 'operational',
        ],
        'aged_payables' => [
            'name' => 'Balance âgée fournisseurs',
            'description' => 'Dettes fournisseurs par ancienneté',
            'category' => 'operational',
        ],
        'general_ledger' => [
            'name' => 'Grand livre',
            'description' => 'Détail des mouvements par compte',
            'category' => 'accounting',
        ],
        'trial_balance' => [
            'name' => 'Balance des comptes',
            'description' => 'Soldes débiteurs et créditeurs',
            'category' => 'accounting',
        ],
        'journal' => [
            'name' => 'Journal',
            'description' => 'Écritures comptables chronologiques',
            'category' => 'accounting',
        ],
        'sales_report' => [
            'name' => 'Rapport des ventes',
            'description' => 'Analyse des ventes par période',
            'category' => 'operational',
        ],
        'purchase_report' => [
            'name' => 'Rapport des achats',
            'description' => 'Analyse des achats par période',
            'category' => 'operational',
        ],
        'partner_statement' => [
            'name' => 'Relevé partenaire',
            'description' => 'Historique des transactions avec un partenaire',
            'category' => 'operational',
        ],
        'bank_reconciliation' => [
            'name' => 'Rapprochement bancaire',
            'description' => 'État du rapprochement bancaire',
            'category' => 'operational',
        ],
        'custom' => [
            'name' => 'Rapport personnalisé',
            'description' => 'Créez votre propre rapport',
            'category' => 'custom',
        ],
    ];

    /**
     * Formats d'export disponibles
     */
    const EXPORT_FORMATS = ['pdf', 'xlsx', 'csv', 'json'];

    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    public function setDateRange(?Carbon $from, ?Carbon $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo = $to;
        return $this;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Générer un rapport
     */
    public function generate(string $type, array $options = []): array
    {
        if (!isset(self::REPORT_TYPES[$type])) {
            throw new \InvalidArgumentException("Type de rapport invalide: {$type}");
        }

        $method = 'generate' . str_replace('_', '', ucwords($type, '_'));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException("Méthode de génération non implémentée: {$method}");
        }

        $data = $this->$method($options);

        return [
            'type' => $type,
            'name' => self::REPORT_TYPES[$type]['name'],
            'company' => $this->company?->name,
            'period' => [
                'from' => $this->dateFrom?->format('Y-m-d'),
                'to' => $this->dateTo?->format('Y-m-d'),
            ],
            'generated_at' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    /**
     * Exporter un rapport
     */
    public function export(array $report, string $format): mixed
    {
        return match($format) {
            'pdf' => $this->exportToPdf($report),
            'xlsx' => $this->exportToExcel($report),
            'csv' => $this->exportToCsv($report),
            'json' => $this->exportToJson($report),
            default => throw new \InvalidArgumentException("Format invalide: {$format}"),
        };
    }

    // ==========================================
    // RAPPORTS FINANCIERS
    // ==========================================

    /**
     * Compte de résultat (Profit & Loss)
     */
    protected function generateProfitLoss(array $options = []): array
    {
        // Revenus (classe 7 PCMN)
        $revenue = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.company_id', $this->company->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.accounting_date', [$this->dateFrom, $this->dateTo])
            ->where('chart_of_accounts.account_number', 'like', '7%')
            ->selectRaw('
                LEFT(chart_of_accounts.account_number, 2) as class_code,
                SUM(journal_entry_lines.credit - journal_entry_lines.debit) as amount
            ')
            ->groupBy('class_code')
            ->get();

        // Charges (classes 6 PCMN)
        $expenses = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.company_id', $this->company->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.accounting_date', [$this->dateFrom, $this->dateTo])
            ->where('chart_of_accounts.account_number', 'like', '6%')
            ->selectRaw('
                LEFT(chart_of_accounts.account_number, 2) as class_code,
                SUM(journal_entry_lines.debit - journal_entry_lines.credit) as amount
            ')
            ->groupBy('class_code')
            ->get();

        // Catégorisation selon PCMN belge
        $revenueCategories = [
            '70' => 'Chiffre d\'affaires',
            '71' => 'Variation des stocks et commandes en cours',
            '72' => 'Production immobilisée',
            '74' => 'Autres produits d\'exploitation',
            '75' => 'Produits financiers',
            '76' => 'Produits exceptionnels',
        ];

        $expenseCategories = [
            '60' => 'Approvisionnements et marchandises',
            '61' => 'Services et biens divers',
            '62' => 'Rémunérations et charges sociales',
            '63' => 'Amortissements et réductions de valeur',
            '64' => 'Autres charges d\'exploitation',
            '65' => 'Charges financières',
            '66' => 'Charges exceptionnelles',
            '67' => 'Impôts sur le résultat',
        ];

        $revenueData = [];
        $totalRevenue = 0;
        foreach ($revenueCategories as $code => $label) {
            $amount = $revenue->where('class_code', $code)->first()?->amount ?? 0;
            $revenueData[] = ['code' => $code, 'label' => $label, 'amount' => $amount];
            $totalRevenue += $amount;
        }

        $expenseData = [];
        $totalExpenses = 0;
        foreach ($expenseCategories as $code => $label) {
            $amount = $expenses->where('class_code', $code)->first()?->amount ?? 0;
            $expenseData[] = ['code' => $code, 'label' => $label, 'amount' => $amount];
            $totalExpenses += $amount;
        }

        $netResult = $totalRevenue - $totalExpenses;

        // Calcul du résultat d'exploitation
        $operatingRevenue = $revenue->whereIn('class_code', ['70', '71', '72', '74'])->sum('amount');
        $operatingExpenses = $expenses->whereIn('class_code', ['60', '61', '62', '63', '64'])->sum('amount');
        $operatingResult = $operatingRevenue - $operatingExpenses;

        // Résultat financier
        $financialResult = ($revenue->where('class_code', '75')->first()?->amount ?? 0) -
                          ($expenses->where('class_code', '65')->first()?->amount ?? 0);

        return [
            'revenue' => [
                'items' => $revenueData,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'items' => $expenseData,
                'total' => $totalExpenses,
            ],
            'operating_result' => $operatingResult,
            'financial_result' => $financialResult,
            'exceptional_result' => ($revenue->where('class_code', '76')->first()?->amount ?? 0) -
                                   ($expenses->where('class_code', '66')->first()?->amount ?? 0),
            'taxes' => $expenses->where('class_code', '67')->first()?->amount ?? 0,
            'net_result' => $netResult,
            'margin' => $totalRevenue > 0 ? round(($netResult / $totalRevenue) * 100, 2) : 0,
        ];
    }

    /**
     * Bilan (Balance Sheet)
     */
    protected function generateBalanceSheet(array $options = []): array
    {
        // Actif (classes 2, 3, 4, 5)
        $assets = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.company_id', $this->company->id)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.accounting_date', '<=', $this->dateTo)
            ->whereRaw("LEFT(chart_of_accounts.account_number, 1) IN ('2', '3', '4', '5')")
            ->selectRaw('
                LEFT(chart_of_accounts.account_number, 1) as class,
                LEFT(chart_of_accounts.account_number, 2) as subclass,
                SUM(journal_entry_lines.debit - journal_entry_lines.credit) as balance
            ')
            ->groupBy('class', 'subclass')
            ->get();

        // Passif (classes 1, 4 - note: classe 4 can be both asset and liability depending on balance)
        $liabilities = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journal_entries.company_id', $this->company->id)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.accounting_date', '<=', $this->dateTo)
            ->whereRaw("LEFT(chart_of_accounts.account_number, 1) IN ('1')")
            ->selectRaw('
                LEFT(chart_of_accounts.account_number, 1) as class,
                LEFT(chart_of_accounts.account_number, 2) as subclass,
                SUM(journal_entry_lines.credit - journal_entry_lines.debit) as balance
            ')
            ->groupBy('class', 'subclass')
            ->get();

        // Structure du bilan belge
        $assetStructure = [
            'fixed' => [
                'label' => 'Actifs immobilisés',
                'subcategories' => [
                    '20' => 'Frais d\'établissement',
                    '21' => 'Immobilisations incorporelles',
                    '22' => 'Terrains et constructions',
                    '23' => 'Installations, machines et outillage',
                    '24' => 'Mobilier et matériel roulant',
                    '25' => 'Immobilisations détenues en leasing',
                    '26' => 'Autres immobilisations corporelles',
                    '27' => 'Immobilisations en cours',
                    '28' => 'Immobilisations financières',
                ],
            ],
            'current' => [
                'label' => 'Actifs circulants',
                'subcategories' => [
                    '30' => 'Stocks - Approvisionnements',
                    '31' => 'Stocks - En-cours de fabrication',
                    '32' => 'Stocks - Produits finis',
                    '33' => 'Stocks - Marchandises',
                    '34' => 'Commandes en cours d\'exécution',
                    '40' => 'Créances commerciales',
                    '41' => 'Autres créances',
                    '50' => 'Actions propres',
                    '51' => 'Placements de trésorerie',
                    '52' => 'Valeurs disponibles',
                    '53' => 'Virements internes',
                ],
            ],
        ];

        $liabilityStructure = [
            'equity' => [
                'label' => 'Capitaux propres',
                'subcategories' => [
                    '10' => 'Capital',
                    '11' => 'Primes d\'émission',
                    '12' => 'Plus-values de réévaluation',
                    '13' => 'Réserves',
                    '14' => 'Résultat reporté',
                    '15' => 'Subsides en capital',
                ],
            ],
            'provisions' => [
                'label' => 'Provisions et impôts différés',
                'subcategories' => [
                    '16' => 'Provisions pour risques et charges',
                    '17' => 'Dettes à plus d\'un an',
                ],
            ],
            'debts' => [
                'label' => 'Dettes',
                'subcategories' => [
                    '42' => 'Dettes à plus d\'un an échéant dans l\'année',
                    '43' => 'Dettes financières',
                    '44' => 'Dettes commerciales',
                    '45' => 'Dettes fiscales et sociales',
                    '46' => 'Acomptes reçus',
                    '47' => 'Dettes diverses',
                    '48' => 'Comptes de régularisation',
                    '49' => 'Comptes d\'attente',
                ],
            ],
        ];

        $formatStructure = function ($structure, $data, $isLiability = false) {
            $result = [];
            $total = 0;

            foreach ($structure as $key => $category) {
                $categoryTotal = 0;
                $items = [];

                foreach ($category['subcategories'] as $code => $label) {
                    $balance = $data->where('subclass', $code)->first()?->balance ?? 0;
                    if ($balance != 0) {
                        $items[] = ['code' => $code, 'label' => $label, 'amount' => $balance];
                        $categoryTotal += $balance;
                    }
                }

                $result[$key] = [
                    'label' => $category['label'],
                    'items' => $items,
                    'total' => $categoryTotal,
                ];

                $total += $categoryTotal;
            }

            return ['categories' => $result, 'total' => $total];
        };

        $assetsFormatted = $formatStructure($assetStructure, $assets);
        $liabilitiesFormatted = $formatStructure($liabilityStructure, $liabilities, true);

        return [
            'assets' => $assetsFormatted,
            'liabilities' => $liabilitiesFormatted,
            'balanced' => abs($assetsFormatted['total'] - $liabilitiesFormatted['total']) < 0.01,
        ];
    }

    // ==========================================
    // RAPPORTS TVA
    // ==========================================

    /**
     * Déclaration TVA
     */
    protected function generateVatSummary(array $options = []): array
    {
        // Ventes
        $sales = Invoice::where('company_id', $this->company->id)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->selectRaw('
                SUM(total_excl_vat) as base,
                SUM(vat_amount) as vat
            ')
            ->first();

        // Achats
        $purchases = Invoice::where('company_id', $this->company->id)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('
                SUM(total_excl_vat) as base,
                SUM(vat_amount) as vat
            ')
            ->first();

        // Détail par taux de TVA
        $vatRates = Invoice::where('company_id', $this->company->id)
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->join('invoice_lines', 'invoices.id', '=', 'invoice_lines.invoice_id')
            ->selectRaw('
                invoices.type,
                invoice_lines.vat_rate,
                SUM(invoice_lines.total_excl_vat) as base,
                SUM(invoice_lines.vat_amount) as vat
            ')
            ->groupBy('invoices.type', 'invoice_lines.vat_rate')
            ->get();

        // Calcul de la TVA due/à récupérer
        $vatDue = $sales->vat ?? 0;
        $vatDeductible = $purchases->vat ?? 0;
        $balance = $vatDue - $vatDeductible;

        // Structure grille belge simplifiée
        $grid = [
            // Opérations à la sortie
            '00' => ['label' => 'Chiffre d\'affaires', 'amount' => $sales->base ?? 0],
            '01' => ['label' => 'Opérations soumises à 6%', 'amount' => $vatRates->where('type', 'sale')->where('vat_rate', 6)->first()?->base ?? 0],
            '02' => ['label' => 'Opérations soumises à 12%', 'amount' => $vatRates->where('type', 'sale')->where('vat_rate', 12)->first()?->base ?? 0],
            '03' => ['label' => 'Opérations soumises à 21%', 'amount' => $vatRates->where('type', 'sale')->where('vat_rate', 21)->first()?->base ?? 0],
            '47' => ['label' => 'Opérations exemptées', 'amount' => $vatRates->where('type', 'sale')->where('vat_rate', 0)->first()?->base ?? 0],

            // TVA due
            '54' => ['label' => 'TVA due sur opérations cases 01-03', 'amount' => $vatDue],

            // Opérations à l'entrée
            '81' => ['label' => 'Marchandises, matières premières', 'amount' => ($purchases->base ?? 0) * 0.6],
            '82' => ['label' => 'Services et biens divers', 'amount' => ($purchases->base ?? 0) * 0.4],

            // TVA déductible
            '59' => ['label' => 'TVA déductible', 'amount' => $vatDeductible],

            // Solde
            '71' => ['label' => 'TVA à payer', 'amount' => max(0, $balance)],
            '72' => ['label' => 'TVA à récupérer', 'amount' => max(0, -$balance)],
        ];

        return [
            'grid' => $grid,
            'summary' => [
                'sales_base' => $sales->base ?? 0,
                'sales_vat' => $vatDue,
                'purchases_base' => $purchases->base ?? 0,
                'purchases_vat' => $vatDeductible,
                'balance' => $balance,
                'to_pay' => max(0, $balance),
                'to_recover' => max(0, -$balance),
            ],
            'vat_by_rate' => [
                'sales' => $vatRates->where('type', 'sale')->values(),
                'purchases' => $vatRates->where('type', 'purchase')->values(),
            ],
        ];
    }

    /**
     * Listing TVA clients
     */
    protected function generateVatListing(array $options = []): array
    {
        $year = $options['year'] ?? $this->dateFrom?->year ?? now()->year;

        $clients = Invoice::where('company_id', $this->company->id)
            ->where('type', 'sale')
            ->whereYear('issue_date', $year)
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->join('partners', 'invoices.partner_id', '=', 'partners.id')
            ->whereNotNull('partners.vat_number')
            ->where('partners.vat_number', 'like', 'BE%')
            ->selectRaw('
                partners.vat_number,
                partners.name,
                SUM(invoices.total_excl_vat) as total_excl_vat,
                SUM(invoices.vat_amount) as total_vat
            ')
            ->groupBy('partners.vat_number', 'partners.name')
            ->having('total_excl_vat', '>=', 250)
            ->orderBy('total_excl_vat', 'desc')
            ->get();

        return [
            'year' => $year,
            'threshold' => 250,
            'clients' => $clients->map(function ($client) {
                return [
                    'vat_number' => $client->vat_number,
                    'name' => $client->name,
                    'turnover' => round($client->total_excl_vat, 2),
                    'vat' => round($client->total_vat, 2),
                ];
            }),
            'totals' => [
                'count' => $clients->count(),
                'turnover' => round($clients->sum('total_excl_vat'), 2),
                'vat' => round($clients->sum('total_vat'), 2),
            ],
        ];
    }

    // ==========================================
    // RAPPORTS OPERATIONNELS
    // ==========================================

    /**
     * Flux de trésorerie (Cash Flow)
     */
    protected function generateCashFlow(array $options = []): array
    {
        // Encaissements (paiements reçus)
        $inflows = BankTransaction::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('amount', '>', 0)
            ->selectRaw('
                CASE
                    WHEN matched_invoice_id IS NOT NULL THEN "invoice"
                    WHEN category LIKE "%revenue%" THEN "revenue"
                    ELSE "other"
                END as flow_type,
                SUM(amount) as total
            ')
            ->groupBy('flow_type')
            ->get();

        // Décaissements (paiements effectués)
        $outflows = BankTransaction::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->where('amount', '<', 0)
            ->selectRaw('
                CASE
                    WHEN matched_invoice_id IS NOT NULL THEN "invoice"
                    WHEN category LIKE "%salary%" THEN "salary"
                    WHEN category LIKE "%tax%" THEN "tax"
                    ELSE "other"
                END as flow_type,
                SUM(ABS(amount)) as total
            ')
            ->groupBy('flow_type')
            ->get();

        // Évolution mensuelle
        $monthlyFlow = BankTransaction::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('
                DATE_FORMAT(date, "%Y-%m") as month,
                SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as inflow,
                SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as outflow
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalInflows = $inflows->sum('total');
        $totalOutflows = $outflows->sum('total');

        return [
            'operating' => [
                'inflows' => [
                    'items' => $inflows,
                    'total' => $totalInflows,
                ],
                'outflows' => [
                    'items' => $outflows,
                    'total' => $totalOutflows,
                ],
                'net' => $totalInflows - $totalOutflows,
            ],
            'monthly' => $monthlyFlow->map(function ($row) {
                return [
                    'month' => $row->month,
                    'inflow' => round($row->inflow, 2),
                    'outflow' => round($row->outflow, 2),
                    'net' => round($row->inflow - $row->outflow, 2),
                ];
            }),
            'summary' => [
                'total_inflows' => $totalInflows,
                'total_outflows' => $totalOutflows,
                'net_cash_flow' => $totalInflows - $totalOutflows,
            ],
        ];
    }

    /**
     * Balance âgée clients
     */
    protected function generateAgedReceivables(array $options = []): array
    {
        return $this->generateAgingReport('sale', $options);
    }

    /**
     * Balance âgée fournisseurs
     */
    protected function generateAgedPayables(array $options = []): array
    {
        return $this->generateAgingReport('purchase', $options);
    }

    /**
     * Rapport d'ancienneté générique
     */
    protected function generateAgingReport(string $type, array $options = []): array
    {
        $asOf = Carbon::parse($options['as_of'] ?? now());

        $invoices = Invoice::where('company_id', $this->company->id)
            ->where('type', $type)
            ->whereIn('status', ['validated', 'sent', 'partial'])
            ->where('amount_due', '>', 0)
            ->with('partner:id,name,vat_number')
            ->get();

        $aging = [
            'current' => ['label' => '0-30 jours', 'amount' => 0, 'count' => 0],
            'days_30' => ['label' => '31-60 jours', 'amount' => 0, 'count' => 0],
            'days_60' => ['label' => '61-90 jours', 'amount' => 0, 'count' => 0],
            'days_90' => ['label' => '> 90 jours', 'amount' => 0, 'count' => 0],
        ];

        $byPartner = [];

        foreach ($invoices as $invoice) {
            $daysOverdue = $asOf->diffInDays(Carbon::parse($invoice->due_date), false);
            $daysOverdue = max(0, -$daysOverdue);

            $bucket = match(true) {
                $daysOverdue <= 30 => 'current',
                $daysOverdue <= 60 => 'days_30',
                $daysOverdue <= 90 => 'days_60',
                default => 'days_90',
            };

            $aging[$bucket]['amount'] += $invoice->amount_due;
            $aging[$bucket]['count']++;

            $partnerId = $invoice->partner_id;
            if (!isset($byPartner[$partnerId])) {
                $byPartner[$partnerId] = [
                    'partner' => $invoice->partner?->name ?? 'N/A',
                    'vat_number' => $invoice->partner?->vat_number,
                    'current' => 0,
                    'days_30' => 0,
                    'days_60' => 0,
                    'days_90' => 0,
                    'total' => 0,
                ];
            }

            $byPartner[$partnerId][$bucket] += $invoice->amount_due;
            $byPartner[$partnerId]['total'] += $invoice->amount_due;
        }

        usort($byPartner, fn($a, $b) => $b['total'] <=> $a['total']);

        return [
            'as_of' => $asOf->format('Y-m-d'),
            'summary' => $aging,
            'total' => array_sum(array_column($aging, 'amount')),
            'by_partner' => array_values($byPartner),
        ];
    }

    /**
     * Rapport des ventes
     */
    protected function generateSalesReport(array $options = []): array
    {
        $invoices = Invoice::where('company_id', $this->company->id)
            ->where('type', 'sale')
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->whereIn('status', ['validated', 'sent', 'paid'])
            ->with('partner:id,name')
            ->orderBy('issue_date', 'desc')
            ->get();

        // Par mois
        $byMonth = $invoices->groupBy(fn($i) => Carbon::parse($i->issue_date)->format('Y-m'))
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => round($group->sum('total_amount'), 2),
                ];
            });

        // Par client
        $byClient = $invoices->groupBy('partner_id')
            ->map(function ($group) {
                return [
                    'partner' => $group->first()->partner?->name ?? 'N/A',
                    'count' => $group->count(),
                    'total' => round($group->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(20);

        return [
            'invoices' => $invoices->map(fn($i) => [
                'number' => $i->number,
                'date' => $i->issue_date->format('Y-m-d'),
                'partner' => $i->partner?->name,
                'total' => $i->total_amount,
                'status' => $i->status,
            ]),
            'by_month' => $byMonth,
            'by_client' => $byClient,
            'totals' => [
                'count' => $invoices->count(),
                'total_excl_vat' => round($invoices->sum('total_excl_vat'), 2),
                'vat' => round($invoices->sum('total_vat'), 2),
                'total' => round($invoices->sum('total_incl_vat'), 2),
            ],
        ];
    }

    /**
     * Rapport des achats
     */
    protected function generatePurchaseReport(array $options = []): array
    {
        $invoices = Invoice::where('company_id', $this->company->id)
            ->where('type', 'purchase')
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->with('partner:id,name')
            ->orderBy('issue_date', 'desc')
            ->get();

        // Par mois
        $byMonth = $invoices->groupBy(fn($i) => Carbon::parse($i->issue_date)->format('Y-m'))
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => round($group->sum('total_amount'), 2),
                ];
            });

        // Par fournisseur
        $bySupplier = $invoices->groupBy('partner_id')
            ->map(function ($group) {
                return [
                    'partner' => $group->first()->partner?->name ?? 'N/A',
                    'count' => $group->count(),
                    'total' => round($group->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->take(20);

        return [
            'invoices' => $invoices->map(fn($i) => [
                'number' => $i->number,
                'date' => $i->issue_date->format('Y-m-d'),
                'partner' => $i->partner?->name,
                'total' => $i->total_amount,
                'status' => $i->status,
            ]),
            'by_month' => $byMonth,
            'by_supplier' => $bySupplier,
            'totals' => [
                'count' => $invoices->count(),
                'total_excl_vat' => round($invoices->sum('total_excl_vat'), 2),
                'vat' => round($invoices->sum('total_vat'), 2),
                'total' => round($invoices->sum('total_incl_vat'), 2),
            ],
        ];
    }

    // ==========================================
    // RAPPORTS COMPTABLES
    // ==========================================

    /**
     * Grand livre
     */
    protected function generateGeneralLedger(array $options = []): array
    {
        $accountCode = $options['account'] ?? null;

        $query = JournalEntry::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->with('account:id,code,name');

        if ($accountCode) {
            $query->whereHas('account', function ($q) use ($accountCode) {
                $q->where('code', 'like', $accountCode . '%');
            });
        }

        $entries = $query->orderBy('accounts.code')
            ->orderBy('date')
            ->get();

        $byAccount = $entries->groupBy('account_id')->map(function ($group) {
            $account = $group->first()->account;
            $runningBalance = 0;

            return [
                'account' => [
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'entries' => $group->map(function ($entry) use (&$runningBalance) {
                    $runningBalance += ($entry->debit - $entry->credit);
                    return [
                        'date' => $entry->date->format('Y-m-d'),
                        'reference' => $entry->reference,
                        'description' => $entry->description,
                        'debit' => $entry->debit,
                        'credit' => $entry->credit,
                        'balance' => $runningBalance,
                    ];
                }),
                'totals' => [
                    'debit' => round($group->sum('debit'), 2),
                    'credit' => round($group->sum('credit'), 2),
                    'balance' => round($group->sum('debit') - $group->sum('credit'), 2),
                ],
            ];
        });

        return [
            'accounts' => $byAccount->values(),
            'totals' => [
                'debit' => round($entries->sum('debit'), 2),
                'credit' => round($entries->sum('credit'), 2),
            ],
        ];
    }

    /**
     * Balance des comptes (Trial Balance)
     */
    protected function generateTrialBalance(array $options = []): array
    {
        $balances = JournalEntry::where('company_id', $this->company->id)
            ->where('date', '<=', $this->dateTo)
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->selectRaw('
                accounts.id,
                accounts.code,
                accounts.name,
                SUM(journal_entries.debit) as total_debit,
                SUM(journal_entries.credit) as total_credit
            ')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->orderBy('accounts.code')
            ->get();

        $accounts = $balances->map(function ($account) {
            $balance = $account->total_debit - $account->total_credit;
            return [
                'code' => $account->code,
                'name' => $account->name,
                'debit' => round($account->total_debit, 2),
                'credit' => round($account->total_credit, 2),
                'balance_debit' => $balance > 0 ? round($balance, 2) : 0,
                'balance_credit' => $balance < 0 ? round(abs($balance), 2) : 0,
            ];
        });

        return [
            'accounts' => $accounts,
            'totals' => [
                'debit' => round($accounts->sum('debit'), 2),
                'credit' => round($accounts->sum('credit'), 2),
                'balance_debit' => round($accounts->sum('balance_debit'), 2),
                'balance_credit' => round($accounts->sum('balance_credit'), 2),
            ],
        ];
    }

    /**
     * Journal des écritures
     */
    protected function generateJournal(array $options = []): array
    {
        $journalType = $options['journal_type'] ?? null;

        $query = JournalEntry::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->with('account:id,code,name');

        if ($journalType) {
            $query->where('journal_type', $journalType);
        }

        $entries = $query->orderBy('date')
            ->orderBy('reference')
            ->get();

        $grouped = $entries->groupBy('reference')->map(function ($group) {
            return [
                'reference' => $group->first()->reference,
                'date' => $group->first()->date->format('Y-m-d'),
                'description' => $group->first()->description,
                'lines' => $group->map(fn($e) => [
                    'account_code' => $e->account->code,
                    'account_name' => $e->account->name,
                    'debit' => $e->debit,
                    'credit' => $e->credit,
                ]),
                'total_debit' => round($group->sum('debit'), 2),
                'total_credit' => round($group->sum('credit'), 2),
            ];
        });

        return [
            'entries' => $grouped->values(),
            'totals' => [
                'entries_count' => $grouped->count(),
                'debit' => round($entries->sum('debit'), 2),
                'credit' => round($entries->sum('credit'), 2),
            ],
        ];
    }

    /**
     * Relevé partenaire
     */
    protected function generatePartnerStatement(array $options = []): array
    {
        $partnerId = $options['partner_id'] ?? null;

        if (!$partnerId) {
            throw new \InvalidArgumentException('partner_id requis');
        }

        $partner = Partner::where('company_id', $this->company->id)
            ->findOrFail($partnerId);

        $invoices = Invoice::where('company_id', $this->company->id)
            ->where('partner_id', $partnerId)
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->orderBy('issue_date')
            ->get();

        $runningBalance = 0;

        return [
            'partner' => [
                'name' => $partner->name,
                'vat_number' => $partner->vat_number,
                'address' => $partner->full_address,
            ],
            'transactions' => $invoices->map(function ($invoice) use (&$runningBalance) {
                $amount = $invoice->type === 'sale' ? $invoice->total_amount : -$invoice->total_amount;
                $runningBalance += $amount;

                return [
                    'date' => $invoice->issue_date->format('Y-m-d'),
                    'reference' => $invoice->number,
                    'description' => $invoice->type === 'sale' ? 'Facture' : 'Facture fournisseur',
                    'debit' => $invoice->type === 'sale' ? $invoice->total_amount : 0,
                    'credit' => $invoice->type === 'purchase' ? $invoice->total_amount : 0,
                    'balance' => $runningBalance,
                    'status' => $invoice->status,
                ];
            }),
            'summary' => [
                'total_invoiced' => round($invoices->where('type', 'sale')->sum('total_amount'), 2),
                'total_purchases' => round($invoices->where('type', 'purchase')->sum('total_amount'), 2),
                'balance' => $runningBalance,
            ],
        ];
    }

    /**
     * Rapprochement bancaire
     */
    protected function generateBankReconciliation(array $options = []): array
    {
        $accountId = $options['account_id'] ?? null;

        $query = BankTransaction::where('company_id', $this->company->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo]);

        if ($accountId) {
            $query->where('bank_account_id', $accountId);
        }

        $transactions = $query->orderBy('date')->get();

        $reconciled = $transactions->whereNotNull('matched_invoice_id');
        $unreconciled = $transactions->whereNull('matched_invoice_id');

        return [
            'reconciled' => [
                'count' => $reconciled->count(),
                'amount' => round($reconciled->sum('amount'), 2),
                'transactions' => $reconciled->map(fn($t) => [
                    'date' => $t->date->format('Y-m-d'),
                    'description' => $t->description,
                    'amount' => $t->amount,
                    'matched_to' => $t->matchedInvoice?->number,
                ]),
            ],
            'unreconciled' => [
                'count' => $unreconciled->count(),
                'amount' => round($unreconciled->sum('amount'), 2),
                'transactions' => $unreconciled->map(fn($t) => [
                    'date' => $t->date->format('Y-m-d'),
                    'description' => $t->description,
                    'amount' => $t->amount,
                    'communication' => $t->structured_communication,
                ]),
            ],
            'summary' => [
                'total_transactions' => $transactions->count(),
                'reconciliation_rate' => $transactions->count() > 0
                    ? round(($reconciled->count() / $transactions->count()) * 100, 1)
                    : 0,
            ],
        ];
    }

    /**
     * Rapport personnalisé
     */
    protected function generateCustom(array $options = []): array
    {
        // Le rapport personnalisé permet de combiner des données selon les options
        $sections = $options['sections'] ?? [];
        $result = [];

        foreach ($sections as $section) {
            $type = $section['type'] ?? null;
            $sectionOptions = $section['options'] ?? [];

            if ($type && isset(self::REPORT_TYPES[$type])) {
                $method = 'generate' . str_replace('_', '', ucwords($type, '_'));
                if (method_exists($this, $method)) {
                    $result[$type] = $this->$method($sectionOptions);
                }
            }
        }

        return $result;
    }

    // ==========================================
    // EXPORT
    // ==========================================

    /**
     * Export PDF
     */
    protected function exportToPdf(array $report): string
    {
        $view = 'reports.pdf.' . $report['type'];

        if (!view()->exists($view)) {
            $view = 'reports.pdf.generic';
        }

        $pdf = Pdf::loadView($view, [
            'report' => $report,
            'company' => $this->company,
        ]);

        $filename = sprintf(
            '%s_%s_%s.pdf',
            $report['type'],
            $this->company->id,
            now()->format('Ymd_His')
        );

        $path = storage_path("app/reports/{$filename}");
        $pdf->save($path);

        return $path;
    }

    /**
     * Export Excel
     */
    protected function exportToExcel(array $report): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-tête
        $sheet->setCellValue('A1', $report['name']);
        $sheet->setCellValue('A2', $report['company'] ?? '');
        $sheet->setCellValue('A3', sprintf('Période: %s - %s', $report['period']['from'], $report['period']['to']));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Données selon le type de rapport
        $this->fillExcelSheet($sheet, $report);

        $filename = sprintf(
            '%s_%s_%s.xlsx',
            $report['type'],
            $this->company->id,
            now()->format('Ymd_His')
        );

        $path = storage_path("app/reports/{$filename}");

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    /**
     * Remplir la feuille Excel selon le type de rapport
     */
    protected function fillExcelSheet($sheet, array $report): void
    {
        $data = $report['data'];
        $row = 5;

        // Logique simplifiée - à personnaliser selon chaque type
        if (isset($data['summary'])) {
            foreach ($data['summary'] as $key => $value) {
                if (!is_array($value)) {
                    $sheet->setCellValue("A{$row}", ucfirst(str_replace('_', ' ', $key)));
                    $sheet->setCellValue("B{$row}", is_numeric($value) ? number_format($value, 2) : $value);
                    $row++;
                }
            }
        }

        if (isset($data['items']) || isset($data['invoices']) || isset($data['accounts'])) {
            $items = $data['items'] ?? $data['invoices'] ?? $data['accounts'] ?? [];
            $row += 2;

            if (count($items) > 0) {
                $firstItem = is_array($items) ? reset($items) : $items->first();
                if (is_array($firstItem) || is_object($firstItem)) {
                    $headers = is_array($firstItem) ? array_keys($firstItem) : array_keys((array)$firstItem);
                    $col = 'A';
                    foreach ($headers as $header) {
                        $sheet->setCellValue("{$col}{$row}", ucfirst(str_replace('_', ' ', $header)));
                        $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                        $col++;
                    }
                    $row++;

                    foreach ($items as $item) {
                        $col = 'A';
                        $itemArray = is_array($item) ? $item : (array)$item;
                        foreach ($itemArray as $value) {
                            if (!is_array($value)) {
                                $sheet->setCellValue("{$col}{$row}", $value);
                            }
                            $col++;
                        }
                        $row++;
                    }
                }
            }
        }
    }

    /**
     * Export CSV
     */
    protected function exportToCsv(array $report): string
    {
        $filename = sprintf(
            '%s_%s_%s.csv',
            $report['type'],
            $this->company->id,
            now()->format('Ymd_His')
        );

        $path = storage_path("app/reports/{$filename}");

        $handle = fopen($path, 'w');

        // BOM pour UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // En-tête
        fputcsv($handle, [$report['name']], ';');
        fputcsv($handle, [$report['company'] ?? ''], ';');
        fputcsv($handle, ['Période', $report['period']['from'], $report['period']['to']], ';');
        fputcsv($handle, [], ';');

        // Données
        $this->writeCsvData($handle, $report['data']);

        fclose($handle);

        return $path;
    }

    /**
     * Écrire les données CSV récursivement
     */
    protected function writeCsvData($handle, $data, $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (isset($value[0]) && is_array($value[0])) {
                    // Tableau d'éléments
                    fputcsv($handle, array_keys($value[0]), ';');
                    foreach ($value as $row) {
                        fputcsv($handle, array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $row), ';');
                    }
                } else {
                    fputcsv($handle, [$prefix . $key], ';');
                    $this->writeCsvData($handle, $value, $prefix . '  ');
                }
            } else {
                fputcsv($handle, [$prefix . $key, $value], ';');
            }
        }
    }

    /**
     * Export JSON
     */
    protected function exportToJson(array $report): string
    {
        $filename = sprintf(
            '%s_%s_%s.json',
            $report['type'],
            $this->company->id,
            now()->format('Ymd_His')
        );

        $path = storage_path("app/reports/{$filename}");

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }
}
