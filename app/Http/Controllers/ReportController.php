<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportExecution;
use App\Services\Reports\ReportBuilderService;
use App\Services\AI\FinancialAnalysisService;
use App\Jobs\GenerateReportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        protected ReportBuilderService $reportBuilder,
        protected FinancialAnalysisService $analysisService
    ) {}

    /**
     * Dashboard des rapports
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        // Rapports sauvegardés
        $savedReports = Report::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->withCount('executions')
            ->orderByDesc('is_favorite')
            ->orderByDesc('last_generated_at')
            ->get();

        // Dernières exécutions
        $recentExecutions = ReportExecution::whereHas('report', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->where('user_id', auth()->id())
            ->with('report:id,name,type')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Rapports planifiés
        $scheduledReports = $savedReports->filter(fn($r) => $r->schedule !== null);

        // Types de rapports groupés par catégorie
        $reportTypes = collect(ReportBuilderService::REPORT_TYPES)
            ->groupBy('category');

        return view('reports.index', compact(
            'savedReports',
            'recentExecutions',
            'scheduledReports',
            'reportTypes'
        ));
    }

    /**
     * Page de création/configuration de rapport
     */
    public function create(Request $request)
    {
        $type = $request->query('type', 'profit_loss');

        if (!isset(ReportBuilderService::REPORT_TYPES[$type])) {
            abort(404, 'Type de rapport invalide');
        }

        $reportInfo = ReportBuilderService::REPORT_TYPES[$type];

        // Paramètres par défaut selon le type
        $defaultConfig = $this->getDefaultConfig($type);

        return view('reports.create', compact('type', 'reportInfo', 'defaultConfig'));
    }

    /**
     * Génération instantanée de rapport
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:' . implode(',', ReportBuilderService::EXPORT_FORMATS),
            'options' => 'nullable|array',
        ]);

        $company = auth()->user()->currentCompany;
        $type = $request->type;
        $format = $request->format;

        // Créer une exécution
        $execution = ReportExecution::create([
            'report_id' => $request->report_id,
            'user_id' => auth()->id(),
            'status' => ReportExecution::STATUS_RUNNING,
            'format' => $format,
            'parameters' => [
                'type' => $type,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'options' => $request->options ?? [],
            ],
            'started_at' => now(),
        ]);

        try {
            $startTime = microtime(true);

            // Générer le rapport
            $this->reportBuilder
                ->setCompany($company)
                ->setDateRange(
                    Carbon::parse($request->date_from),
                    Carbon::parse($request->date_to)
                );

            $report = $this->reportBuilder->generate($type, $request->options ?? []);

            // Exporter dans le format demandé
            $filePath = $this->reportBuilder->export($report, $format);
            $relativePath = str_replace(storage_path('app/'), '', $filePath);

            $execution->update([
                'status' => ReportExecution::STATUS_COMPLETED,
                'file_path' => $relativePath,
                'file_size' => filesize($filePath),
                'execution_time_ms' => (microtime(true) - $startTime) * 1000,
                'completed_at' => now(),
            ]);

            // Mettre à jour le rapport parent si existant
            if ($execution->report_id) {
                $execution->report->update(['last_generated_at' => now()]);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'execution_id' => $execution->id,
                    'download_url' => route('reports.download', $execution),
                ]);
            }

            return redirect()->route('reports.download', $execution);

        } catch (\Exception $e) {
            $execution->update([
                'status' => ReportExecution::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Erreur lors de la génération: ' . $e->getMessage()]);
        }
    }

    /**
     * Aperçu du rapport (JSON)
     */
    public function preview(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'options' => 'nullable|array',
        ]);

        $company = auth()->user()->currentCompany;

        try {
            $this->reportBuilder
                ->setCompany($company)
                ->setDateRange(
                    Carbon::parse($request->date_from),
                    Carbon::parse($request->date_to)
                );

            $report = $this->reportBuilder->generate($request->type, $request->options ?? []);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher le bilan comptable (Balance Sheet) dans l'interface
     */
    public function balanceSheet(Request $request)
    {
        $company = auth()->user()->currentCompany;

        // Dates par défaut: depuis le début de l'année fiscale jusqu'à aujourd'hui
        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        try {
            $this->reportBuilder
                ->setCompany($company)
                ->setDateRange(
                    Carbon::parse($dateFrom),
                    Carbon::parse($dateTo)
                );

            $report = $this->reportBuilder->generate('balance_sheet');

            return view('reports.balance-sheet', compact('report', 'dateFrom', 'dateTo', 'company'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la génération du bilan: ' . $e->getMessage()]);
        }
    }

    /**
     * Afficher le compte de résultat (P&L) dans l'interface
     */
    public function profitLoss(Request $request)
    {
        $company = auth()->user()->currentCompany;

        // Dates par défaut: année en cours
        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        try {
            $this->reportBuilder
                ->setCompany($company)
                ->setDateRange(
                    Carbon::parse($dateFrom),
                    Carbon::parse($dateTo)
                );

            $report = $this->reportBuilder->generate('profit_loss');

            return view('reports.profit-loss', compact('report', 'dateFrom', 'dateTo', 'company'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la génération du compte de résultat: ' . $e->getMessage()]);
        }
    }

    /**
     * Analyser les rapports financiers avec l'IA (API endpoint).
     */
    public function analyzeFinancials(Request $request)
    {
        $company = auth()->user()->currentCompany;

        $dateFrom = $request->input('date_from', now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        try {
            $this->reportBuilder
                ->setCompany($company)
                ->setDateRange(
                    Carbon::parse($dateFrom),
                    Carbon::parse($dateTo)
                );

            // Generate both reports
            $balanceSheet = $this->reportBuilder->generate('balance_sheet');
            $profitLoss = $this->reportBuilder->generate('profit_loss');

            // Analyze with AI
            $analysis = $this->analysisService->analyzeFinancialReports(
                $balanceSheet['data'],
                $profitLoss['data'],
                $company
            );

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate multi-period comparison data (API endpoint).
     */
    public function comparison(Request $request)
    {
        $request->validate([
            'type' => 'required|in:profit_loss,balance_sheet,cash_flow',
            'preset' => 'required|string',
            'custom_start' => 'nullable|date',
            'custom_end' => 'nullable|date',
        ]);

        $company = auth()->user()->currentCompany;
        $type = $request->type;
        $preset = $request->preset;

        try {
            // Get periods based on preset
            $periods = $this->getPeriodsForPreset($preset, $request->custom_start, $request->custom_end);

            $data = [];

            foreach ($periods as $period) {
                $this->reportBuilder
                    ->setCompany($company)
                    ->setDateRange(
                        Carbon::parse($period['start']),
                        Carbon::parse($period['end'])
                    );

                $report = $this->reportBuilder->generate($type);

                $data[] = array_merge([
                    'label' => $period['label'],
                    'start' => $period['start'],
                    'end' => $period['end'],
                ], $report['data'] ?? []);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get periods array for a preset.
     */
    protected function getPeriodsForPreset(string $preset, ?string $customStart = null, ?string $customEnd = null): array
    {
        return match($preset) {
            'current_month' => [
                [
                    'label' => now()->translatedFormat('F Y'),
                    'start' => now()->startOfMonth()->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
            ],
            'current_quarter' => [
                [
                    'label' => 'T' . now()->quarter . ' ' . now()->year,
                    'start' => now()->startOfQuarter()->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
            ],
            'current_year' => [
                [
                    'label' => now()->year,
                    'start' => now()->startOfYear()->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
            ],
            'last_3_months' => $this->getLastNMonths(3),
            'last_6_months' => $this->getLastNMonths(6),
            'last_12_months' => $this->getLastNMonths(12),
            'last_4_quarters' => $this->getLastNQuarters(4),
            'year_comparison' => [
                [
                    'label' => now()->year,
                    'start' => now()->startOfYear()->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
                [
                    'label' => now()->subYear()->year,
                    'start' => now()->subYear()->startOfYear()->format('Y-m-d'),
                    'end' => now()->subYear()->endOfYear()->format('Y-m-d'),
                ],
            ],
            'custom' => [
                [
                    'label' => 'Période personnalisée',
                    'start' => $customStart ?: now()->startOfYear()->format('Y-m-d'),
                    'end' => $customEnd ?: now()->format('Y-m-d'),
                ],
            ],
            default => [
                [
                    'label' => now()->year,
                    'start' => now()->startOfYear()->format('Y-m-d'),
                    'end' => now()->format('Y-m-d'),
                ],
            ],
        };
    }

    /**
     * Sauvegarder un modèle de rapport
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string|max:500',
            'config' => 'required|array',
            'filters' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $report = Report::create([
            'company_id' => auth()->user()->current_company_id,
            'user_id' => auth()->id(),
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'config' => $request->config,
            'filters' => $request->filters,
            'schedule' => $request->schedule,
            'is_public' => $request->boolean('is_public'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'report' => $report,
            ], 201);
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Modèle de rapport sauvegardé');
    }

    /**
     * Afficher un rapport sauvegardé
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['executions' => function ($q) {
            $q->orderByDesc('created_at')->limit(20);
        }]);

        $reportInfo = ReportBuilderService::REPORT_TYPES[$report->type] ?? null;

        return view('reports.show', compact('report', 'reportInfo'));
    }

    /**
     * Mettre à jour un rapport
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'config' => 'sometimes|array',
            'filters' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_public' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        $report->update($request->only([
            'name', 'description', 'config', 'filters', 'schedule', 'is_public', 'is_favorite'
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        }

        return back()->with('success', 'Rapport mis à jour');
    }

    /**
     * Supprimer un rapport
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        // Supprimer les fichiers des exécutions
        foreach ($report->executions as $execution) {
            $execution->deleteFile();
        }

        $report->executions()->delete();
        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Rapport supprimé');
    }

    /**
     * Télécharger un rapport généré
     */
    public function download(ReportExecution $execution)
    {
        // Vérifier les permissions
        if ($execution->report && $execution->report->company_id !== auth()->user()->current_company_id) {
            abort(403);
        }

        if (!$execution->fileExists()) {
            abort(404, 'Le fichier n\'existe plus');
        }

        $path = storage_path('app/' . $execution->file_path);
        $filename = basename($execution->file_path);

        return response()->download($path, $filename);
    }

    /**
     * Exécuter un rapport sauvegardé
     */
    public function execute(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        $request->validate([
            'format' => 'required|in:' . implode(',', ReportBuilderService::EXPORT_FORMATS),
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        // Utiliser les dates de la config ou celles fournies
        $config = $report->config;
        $dateFrom = $request->date_from ?? $this->resolveDate($config['date_from'] ?? 'month_start');
        $dateTo = $request->date_to ?? $this->resolveDate($config['date_to'] ?? 'today');

        // Dispatch le job en background pour les gros rapports
        if ($request->boolean('async')) {
            GenerateReportJob::dispatch($report, auth()->user(), [
                'format' => $request->format,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Le rapport sera généré en arrière-plan',
            ]);
        }

        // Génération synchrone
        return $this->generate(new Request([
            'report_id' => $report->id,
            'type' => $report->type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'format' => $request->format,
            'options' => $config['options'] ?? [],
        ]));
    }

    /**
     * Toggle favori
     */
    public function toggleFavorite(Report $report)
    {
        $this->authorize('update', $report);

        $report->update(['is_favorite' => !$report->is_favorite]);

        return response()->json([
            'success' => true,
            'is_favorite' => $report->is_favorite,
        ]);
    }

    /**
     * Dupliquer un rapport
     */
    public function duplicate(Report $report)
    {
        $this->authorize('view', $report);

        $newReport = $report->replicate();
        $newReport->name = $report->name . ' (copie)';
        $newReport->user_id = auth()->id();
        $newReport->is_favorite = false;
        $newReport->last_generated_at = null;
        $newReport->save();

        return redirect()->route('reports.show', $newReport)
            ->with('success', 'Rapport dupliqué');
    }

    /**
     * Liste des exécutions pour un rapport
     */
    public function executions(Report $report)
    {
        $this->authorize('view', $report);

        $executions = $report->executions()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('reports.executions', compact('report', 'executions'));
    }

    /**
     * Nettoyer les anciennes exécutions
     */
    public function cleanup(Request $request)
    {
        $days = $request->integer('days', 30);

        $executions = ReportExecution::whereHas('report', function ($q) {
            $q->where('company_id', auth()->user()->current_company_id);
        })
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        $count = 0;
        foreach ($executions as $execution) {
            $execution->deleteFile();
            $execution->delete();
            $count++;
        }

        return response()->json([
            'success' => true,
            'deleted' => $count,
            'message' => "{$count} exécutions supprimées",
        ]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Configuration par défaut selon le type
     */
    protected function getDefaultConfig(string $type): array
    {
        $defaults = [
            'date_from' => 'year_start',
            'date_to' => 'today',
            'options' => [],
        ];

        return match($type) {
            'vat_summary' => array_merge($defaults, [
                'date_from' => 'quarter_start',
                'date_to' => 'quarter_end',
            ]),
            'vat_listing' => array_merge($defaults, [
                'date_from' => 'year_start',
                'date_to' => 'year_end',
                'options' => ['year' => now()->year - 1],
            ]),
            'aged_receivables', 'aged_payables' => array_merge($defaults, [
                'options' => ['as_of' => 'today'],
            ]),
            'trial_balance', 'balance_sheet' => array_merge($defaults, [
                'date_from' => null,
            ]),
            default => $defaults,
        };
    }

    /**
     * Résoudre une date relative
     */
    protected function resolveDate(string $relative): string
    {
        return match($relative) {
            'today' => now()->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'month_start' => now()->startOfMonth()->format('Y-m-d'),
            'month_end' => now()->endOfMonth()->format('Y-m-d'),
            'quarter_start' => now()->startOfQuarter()->format('Y-m-d'),
            'quarter_end' => now()->endOfQuarter()->format('Y-m-d'),
            'year_start' => now()->startOfYear()->format('Y-m-d'),
            'year_end' => now()->endOfYear()->format('Y-m-d'),
            'last_month_start' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'last_month_end' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'last_quarter_start' => now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
            'last_quarter_end' => now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            'last_year_start' => now()->subYear()->startOfYear()->format('Y-m-d'),
            'last_year_end' => now()->subYear()->endOfYear()->format('Y-m-d'),
            default => $relative,
        };
    }
}
