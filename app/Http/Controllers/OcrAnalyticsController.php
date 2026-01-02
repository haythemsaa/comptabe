<?php

namespace App\Http\Controllers;

use App\Models\DocumentScan;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * OCR Analytics Controller
 *
 * Provides metrics and analytics for OCR/AI performance tracking
 */
class OcrAnalyticsController extends Controller
{
    /**
     * Display OCR analytics dashboard
     */
    public function index()
    {
        $companyId = Auth::user()->current_company_id;

        // Overall statistics
        $stats = $this->getOverallStats($companyId);

        // Performance metrics
        $performance = $this->getPerformanceMetrics($companyId);

        // Trends over time
        $trends = $this->getTrends($companyId);

        // Top errors and issues
        $issues = $this->getCommonIssues($companyId);

        // Recent scans
        $recentScans = DocumentScan::where('company_id', $companyId)
            ->with(['createdBy', 'createdInvoice'])
            ->latest()
            ->limit(20)
            ->get();

        return view('ocr.analytics', compact(
            'stats',
            'performance',
            'trends',
            'issues',
            'recentScans'
        ));
    }

    /**
     * Get overall statistics
     */
    protected function getOverallStats(int $companyId): array
    {
        $totalScans = DocumentScan::where('company_id', $companyId)->count();

        $completedScans = DocumentScan::where('company_id', $companyId)
            ->where('status', 'completed')
            ->count();

        $autoCreated = DocumentScan::where('company_id', $companyId)
            ->where('auto_created', true)
            ->count();

        $failedScans = DocumentScan::where('company_id', $companyId)
            ->where('status', 'failed')
            ->count();

        $avgConfidence = DocumentScan::where('company_id', $companyId)
            ->where('status', 'completed')
            ->avg('overall_confidence');

        $autoCreationRate = $totalScans > 0
            ? round(($autoCreated / $totalScans) * 100, 1)
            : 0;

        $successRate = $totalScans > 0
            ? round(($completedScans / $totalScans) * 100, 1)
            : 0;

        return [
            'total_scans' => $totalScans,
            'completed' => $completedScans,
            'auto_created' => $autoCreated,
            'failed' => $failedScans,
            'avg_confidence' => round($avgConfidence * 100, 1),
            'auto_creation_rate' => $autoCreationRate,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(int $companyId): array
    {
        // Processing time analysis
        $avgProcessingTime = DocumentScan::where('company_id', $companyId)
            ->whereNotNull('processing_started_at')
            ->whereNotNull('processing_completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, processing_started_at, processing_completed_at)) as avg_time')
            ->value('avg_time');

        // Confidence distribution
        $confidenceDistribution = DocumentScan::where('company_id', $companyId)
            ->where('status', 'completed')
            ->selectRaw('
                SUM(CASE WHEN overall_confidence >= 0.85 THEN 1 ELSE 0 END) as high,
                SUM(CASE WHEN overall_confidence >= 0.70 AND overall_confidence < 0.85 THEN 1 ELSE 0 END) as medium,
                SUM(CASE WHEN overall_confidence < 0.70 THEN 1 ELSE 0 END) as low
            ')
            ->first();

        // Document type breakdown
        $typeBreakdown = DocumentScan::where('company_id', $companyId)
            ->select('document_type', DB::raw('count(*) as count'))
            ->groupBy('document_type')
            ->get()
            ->pluck('count', 'document_type')
            ->toArray();

        return [
            'avg_processing_time' => round($avgProcessingTime ?? 0, 1),
            'confidence_distribution' => [
                'high' => $confidenceDistribution->high ?? 0,
                'medium' => $confidenceDistribution->medium ?? 0,
                'low' => $confidenceDistribution->low ?? 0,
            ],
            'type_breakdown' => $typeBreakdown,
        ];
    }

    /**
     * Get trends over last 30 days
     */
    protected function getTrends(int $companyId): array
    {
        $startDate = Carbon::now()->subDays(30);

        // Daily scan volume
        $dailyVolume = DocumentScan::where('company_id', $companyId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Daily average confidence
        $dailyConfidence = DocumentScan::where('company_id', $companyId)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, AVG(overall_confidence) * 100 as avg_conf')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('avg_conf', 'date')
            ->toArray();

        // Daily auto-creation rate
        $dailyAutoCreation = DocumentScan::where('company_id', $companyId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                (SUM(CASE WHEN auto_created = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as rate
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('rate', 'date')
            ->toArray();

        return [
            'daily_volume' => $dailyVolume,
            'daily_confidence' => $dailyConfidence,
            'daily_auto_creation' => $dailyAutoCreation,
        ];
    }

    /**
     * Get common issues and errors
     */
    protected function getCommonIssues(int $companyId): array
    {
        // Failed scans with error messages
        $failedScans = DocumentScan::where('company_id', $companyId)
            ->where('status', 'failed')
            ->whereNotNull('error_message')
            ->select('error_message', DB::raw('count(*) as count'))
            ->groupBy('error_message')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Low confidence patterns (extracted data analysis)
        $lowConfidenceScans = DocumentScan::where('company_id', $companyId)
            ->where('overall_confidence', '<', 0.70)
            ->where('status', 'completed')
            ->limit(100)
            ->get();

        $missingFields = [];
        foreach ($lowConfidenceScans as $scan) {
            $data = $scan->extracted_data ?? [];

            if (empty($data['invoice_number'] ?? null)) {
                $missingFields['invoice_number'] = ($missingFields['invoice_number'] ?? 0) + 1;
            }
            if (empty($data['invoice_date'] ?? null)) {
                $missingFields['invoice_date'] = ($missingFields['invoice_date'] ?? 0) + 1;
            }
            if (empty($data['vat_number'] ?? null)) {
                $missingFields['vat_number'] = ($missingFields['vat_number'] ?? 0) + 1;
            }
            if (empty($data['total_incl_vat'] ?? null)) {
                $missingFields['total_incl_vat'] = ($missingFields['total_incl_vat'] ?? 0) + 1;
            }
        }

        arsort($missingFields);

        return [
            'failed_scans' => $failedScans,
            'missing_fields' => $missingFields,
            'low_confidence_count' => $lowConfidenceScans->count(),
        ];
    }

    /**
     * Get detailed scan information (AJAX)
     */
    public function getScanDetails(DocumentScan $scan)
    {
        $this->authorize('view', $scan);

        return response()->json([
            'scan' => $scan->load(['createdBy', 'createdInvoice']),
            'extracted_data' => $scan->extracted_data,
            'processing_time' => $scan->processing_started_at && $scan->processing_completed_at
                ? $scan->processing_started_at->diffInSeconds($scan->processing_completed_at)
                : null,
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        $companyId = Auth::user()->current_company_id;

        $scans = DocumentScan::where('company_id', $companyId)
            ->when($request->start_date, function ($query, $startDate) {
                return $query->where('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                return $query->where('created_at', '<=', $endDate);
            })
            ->with(['createdBy', 'createdInvoice'])
            ->get();

        $csvData = [];
        $csvData[] = [
            'Date',
            'Filename',
            'Type',
            'Status',
            'Confidence',
            'Auto Created',
            'Processing Time (s)',
            'Invoice Number',
            'Total Amount',
            'Error Message',
        ];

        foreach ($scans as $scan) {
            $processingTime = $scan->processing_started_at && $scan->processing_completed_at
                ? $scan->processing_started_at->diffInSeconds($scan->processing_completed_at)
                : null;

            $csvData[] = [
                $scan->created_at->format('Y-m-d H:i:s'),
                $scan->original_filename,
                $scan->document_type,
                $scan->status,
                round(($scan->overall_confidence ?? 0) * 100, 1) . '%',
                $scan->auto_created ? 'Yes' : 'No',
                $processingTime,
                $scan->createdInvoice?->invoice_number ?? '',
                $scan->createdInvoice?->total_incl_vat ?? '',
                $scan->error_message ?? '',
            ];
        }

        $filename = 'ocr_analytics_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Retry failed scan
     */
    public function retry(DocumentScan $scan)
    {
        $this->authorize('update', $scan);

        if ($scan->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les scans échoués peuvent être réessayés',
            ], 400);
        }

        // Reset status and re-dispatch job
        $scan->update([
            'status' => 'queued',
            'error_message' => null,
            'processing_started_at' => null,
            'processing_completed_at' => null,
        ]);

        \App\Jobs\ProcessUploadedInvoice::dispatch($scan);

        return response()->json([
            'success' => true,
            'message' => 'Document remis en file d\'attente pour traitement',
        ]);
    }

    /**
     * Get real-time statistics (for dashboard refresh)
     */
    public function realtimeStats()
    {
        $companyId = Auth::user()->current_company_id;

        $processingCount = DocumentScan::where('company_id', $companyId)
            ->where('status', 'processing')
            ->count();

        $queuedCount = DocumentScan::where('company_id', $companyId)
            ->where('status', 'queued')
            ->count();

        $todayScans = DocumentScan::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->count();

        $todayAutoCreated = DocumentScan::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->where('auto_created', true)
            ->count();

        return response()->json([
            'processing' => $processingCount,
            'queued' => $queuedCount,
            'today_scans' => $todayScans,
            'today_auto_created' => $todayAutoCreated,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
