<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\BusinessIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsDashboardController extends Controller
{
    public function __construct(
        protected BusinessIntelligenceService $biService
    ) {}

    /**
     * Display the AI analytics dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        $data = $this->biService->getDashboardData($companyId);

        return view('ai.analytics', [
            'healthScore' => $data['health_score'],
            'insights' => $data['insights'],
            'anomalies' => $data['anomalies'],
            'predictions' => $data['predictions'],
            'kpis' => $data['kpis'],
            'trends' => $data['trends'],
        ]);
    }

    /**
     * Refresh dashboard data via AJAX.
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        $data = $this->biService->getDashboardData($companyId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get detailed component data.
     */
    public function component(Request $request, string $component)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        $data = match($component) {
            'health' => $this->biService->calculateHealthScore($companyId),
            'insights' => $this->biService->generateInsights($companyId),
            'anomalies' => $this->biService->detectAnomalies($companyId),
            'predictions' => $this->biService->generatePredictions($companyId),
            default => null,
        };

        if (!$data) {
            return response()->json(['error' => 'Component not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export dashboard data to PDF.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        $data = $this->biService->getDashboardData($companyId);

        // TODO: Generate PDF report
        // For now, return JSON
        return response()->json([
            'success' => true,
            'message' => 'Export PDF sera implémenté dans Phase 4.1',
            'data' => $data,
        ]);
    }
}
