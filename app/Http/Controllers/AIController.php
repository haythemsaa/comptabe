<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DocumentScan;
use App\Models\Invoice;
use App\Models\Expense;
use App\Services\AI\DocumentOCRService;
use App\Services\AI\IntelligentCategorizationService;
use App\Services\AI\TreasuryForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIController extends Controller
{
    public function __construct(
        protected DocumentOCRService $ocrService,
        protected IntelligentCategorizationService $categorizationService,
        protected TreasuryForecastService $treasuryService
    ) {}

    /**
     * Dashboard IA principal
     */
    public function index()
    {
        $companyId = Auth::user()->current_company_id;

        // Statistiques OCR
        $ocrStats = [
            'total_scans' => DocumentScan::where('company_id', $companyId)->count(),
            'pending' => DocumentScan::where('company_id', $companyId)->where('status', 'pending')->count(),
            'processed' => DocumentScan::where('company_id', $companyId)->where('status', 'completed')->count(),
            'auto_created' => DocumentScan::where('company_id', $companyId)->where('auto_created', true)->count(),
            'avg_confidence' => DocumentScan::where('company_id', $companyId)
                ->where('status', 'completed')
                ->avg('confidence_score') ?? 0,
        ];

        // Derniers scans
        $recentScans = DocumentScan::where('company_id', $companyId)
            ->with('invoice')
            ->latest()
            ->take(10)
            ->get();

        // Prévisions trésorerie résumées
        $treasuryForecast = $this->treasuryService->generateForecast(30);

        // Analyse catégorisation
        $categorizationStats = $this->getCategorizationStats($companyId);

        return view('ai.index', compact(
            'ocrStats',
            'recentScans',
            'treasuryForecast',
            'categorizationStats'
        ));
    }

    /**
     * Interface de scan de documents
     */
    public function scanner()
    {
        $companyId = Auth::user()->current_company_id;

        $pendingScans = DocumentScan::where('company_id', $companyId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $recentScans = DocumentScan::where('company_id', $companyId)
            ->whereIn('status', ['completed', 'failed', 'needs_review'])
            ->latest()
            ->take(20)
            ->get();

        return view('ai.scanner', compact('pendingScans', 'recentScans'));
    }

    /**
     * Upload et traitement d'un document
     */
    public function scan(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|in:invoice,receipt,credit_note,expense',
        ]);

        try {
            $scan = $this->ocrService->processDocument(
                $request->file('document'),
                $request->type
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'scan' => $scan,
                    'message' => $scan->auto_created
                        ? 'Document traité et facture créée automatiquement!'
                        : 'Document traité avec succès. Vérification recommandée.',
                ]);
            }

            return redirect()->route('ai.scanner')
                ->with('success', 'Document traité avec succès.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du traitement: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erreur lors du traitement du document.');
        }
    }

    /**
     * Upload multiple documents (batch)
     */
    public function batchScan(Request $request)
    {
        $request->validate([
            'documents' => 'required|array|min:1|max:20',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|in:invoice,receipt,credit_note,expense',
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($request->file('documents') as $file) {
            try {
                $scan = $this->ocrService->processDocument($file, $request->type);
                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'success' => true,
                    'scan_id' => $scan->id,
                    'confidence' => $scan->confidence_score,
                    'auto_created' => $scan->auto_created,
                ];
                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'filename' => $file->getClientOriginalName(),
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
                $errorCount++;
            }
        }

        return response()->json([
            'success' => $errorCount === 0,
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'success' => $successCount,
                'errors' => $errorCount,
            ],
        ]);
    }

    /**
     * Détail d'un scan
     */
    public function showScan(DocumentScan $scan)
    {
        $this->authorize('view', $scan);

        return view('ai.scan-detail', compact('scan'));
    }

    /**
     * Valider/corriger un scan
     */
    public function validateScan(Request $request, DocumentScan $scan)
    {
        $this->authorize('update', $scan);

        $validated = $request->validate([
            'extracted_data' => 'required|array',
            'create_invoice' => 'boolean',
        ]);

        $scan->update([
            'extracted_data' => $validated['extracted_data'],
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => Auth::id(),
        ]);

        // Créer la facture si demandé
        if ($request->boolean('create_invoice') && !$scan->invoice_id) {
            $invoice = $this->ocrService->createInvoiceFromScan($scan);
            $scan->update(['invoice_id' => $invoice->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan validé avec succès.',
            'scan' => $scan->fresh(),
        ]);
    }

    /**
     * Dashboard prévision trésorerie
     */
    public function treasury()
    {
        $forecast = $this->treasuryService->generateForecast(90);

        return view('ai.treasury', compact('forecast'));
    }

    /**
     * API prévision trésorerie
     */
    public function treasuryForecast(Request $request)
    {
        $days = $request->integer('days', 90);
        $days = min(365, max(7, $days));

        $forecast = $this->treasuryService->generateForecast($days);

        return response()->json($forecast);
    }

    /**
     * Dashboard catégorisation intelligente
     */
    public function categorization()
    {
        $companyId = Auth::user()->current_company_id;

        // Dernières dépenses non catégorisées
        $uncategorized = Expense::where('company_id', $companyId)
            ->whereNull('category')
            ->latest()
            ->take(50)
            ->get();

        // Suggestions pour chaque dépense
        $suggestions = [];
        foreach ($uncategorized as $expense) {
            $suggestions[$expense->id] = $this->categorizationService->categorize(
                $expense->description ?? $expense->label,
                $expense->amount,
                $expense->partner_id
            );
        }

        // Analyse des tendances
        $analysis = $this->categorizationService->analyzeSpendingPatterns();

        return view('ai.categorization', compact('uncategorized', 'suggestions', 'analysis'));
    }

    /**
     * Catégoriser automatiquement une dépense
     */
    public function categorizeExpense(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $suggestion = $this->categorizationService->categorize(
            $expense->description ?? $expense->label,
            $expense->amount,
            $expense->partner_id
        );

        if ($request->boolean('apply')) {
            $expense->update([
                'category' => $suggestion['category'],
                'account_code' => $suggestion['account_code'],
                'vat_code' => $suggestion['vat_code'],
            ]);
        }

        return response()->json([
            'success' => true,
            'suggestion' => $suggestion,
            'applied' => $request->boolean('apply'),
        ]);
    }

    /**
     * Catégorisation en masse
     */
    public function batchCategorize(Request $request)
    {
        $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:expenses,id',
        ]);

        $companyId = Auth::user()->current_company_id;
        $results = [];

        $expenses = Expense::whereIn('id', $request->expense_ids)
            ->where('company_id', $companyId)
            ->get();

        foreach ($expenses as $expense) {
            $suggestion = $this->categorizationService->categorize(
                $expense->description ?? $expense->label,
                $expense->amount,
                $expense->partner_id
            );

            if ($suggestion['confidence'] >= 0.7) {
                $expense->update([
                    'category' => $suggestion['category'],
                    'account_code' => $suggestion['account_code'],
                    'vat_code' => $suggestion['vat_code'],
                ]);

                $results[] = [
                    'expense_id' => $expense->id,
                    'applied' => true,
                    'category' => $suggestion['category'],
                    'confidence' => $suggestion['confidence'],
                ];
            } else {
                $results[] = [
                    'expense_id' => $expense->id,
                    'applied' => false,
                    'reason' => 'Confiance insuffisante',
                    'confidence' => $suggestion['confidence'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'applied' => collect($results)->where('applied', true)->count(),
            ],
        ]);
    }

    /**
     * Apprentissage: corriger une catégorisation
     */
    public function learnCategorization(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'category' => 'required|string',
            'account_code' => 'nullable|string',
            'vat_code' => 'nullable|string',
        ]);

        $this->categorizationService->learn($request->description, [
            'category' => $request->category,
            'account_code' => $request->account_code,
            'vat_code' => $request->vat_code,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Apprentissage enregistré.',
        ]);
    }

    /**
     * Analyse des anomalies
     */
    public function anomalies()
    {
        $company = Company::current();
        $analysis = $this->categorizationService->analyzeSpendingPatterns($company->id);

        return view('ai.anomalies', [
            'anomalies' => $analysis['anomalies'] ?? [],
            'predictions' => $analysis['predictions'] ?? [],
            'trends' => $analysis['monthly_trends'] ?? [],
        ]);
    }

    /**
     * Statistiques de catégorisation
     */
    protected function getCategorizationStats(int $companyId): array
    {
        $total = Expense::where('company_id', $companyId)->count();
        $categorized = Expense::where('company_id', $companyId)->whereNotNull('category')->count();

        return [
            'total' => $total,
            'categorized' => $categorized,
            'uncategorized' => $total - $categorized,
            'rate' => $total > 0 ? round(($categorized / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Export des prévisions
     */
    public function exportTreasuryForecast(Request $request)
    {
        $days = $request->integer('days', 90);
        $format = $request->get('format', 'csv');

        $forecast = $this->treasuryService->generateForecast($days);

        if ($format === 'json') {
            return response()->json($forecast);
        }

        // Export CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="treasury_forecast_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($forecast) {
            $file = fopen('php://output', 'w');

            // BOM pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($file, ['Date', 'Solde prévu', 'Entrées', 'Sorties', 'Confiance'], ';');

            foreach ($forecast['daily_forecast'] as $day) {
                fputcsv($file, [
                    $day['date'],
                    number_format($day['projected_balance'], 2, ',', ' '),
                    number_format($day['expected_inflows'], 2, ',', ' '),
                    number_format($day['expected_outflows'], 2, ',', ' '),
                    number_format($day['confidence'] * 100, 0) . '%',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
