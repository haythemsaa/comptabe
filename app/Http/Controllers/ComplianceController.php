<?php

namespace App\Http\Controllers;

use App\Services\Compliance\BelgianTaxComplianceService;
use App\Services\Compliance\VATOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    public function __construct(
        protected BelgianTaxComplianceService $complianceService,
        protected VATOptimizationService $optimizationService
    ) {}

    /**
     * Display the compliance dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        // Get cached results or generate new ones
        $alerts = cache()->remember(
            "compliance_alerts_{$companyId}",
            3600,
            fn() => $this->complianceService->checkVATCompliance($companyId)
        );

        $optimizations = cache()->remember(
            "compliance_optimizations_{$companyId}",
            3600,
            fn() => $this->optimizationService->analyzeOptimizations($companyId)
        );

        $fiscalCalendar = $this->complianceService->getFiscalCalendar($companyId);

        // Get upcoming deadlines (next 60 days)
        $upcomingDeadlines = array_filter($fiscalCalendar, function ($deadline) {
            return $deadline['deadline']->isFuture() &&
                   $deadline['deadline']->diffInDays(now()) <= 60;
        });

        // Sort alerts by severity
        $alertsBySeverity = [
            'high' => array_filter($alerts, fn($a) => $a['severity'] === 'high'),
            'medium' => array_filter($alerts, fn($a) => $a['severity'] === 'medium'),
            'low' => array_filter($alerts, fn($a) => $a['severity'] === 'low'),
        ];

        return view('compliance.dashboard', [
            'alerts' => $alerts,
            'alertsBySeverity' => $alertsBySeverity,
            'optimizations' => $optimizations,
            'fiscalCalendar' => $fiscalCalendar,
            'upcomingDeadlines' => $upcomingDeadlines,
        ]);
    }

    /**
     * Refresh compliance checks
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        // Clear cache
        cache()->forget("compliance_alerts_{$companyId}");
        cache()->forget("compliance_optimizations_{$companyId}");

        // Run checks
        $alerts = $this->complianceService->checkVATCompliance($companyId);
        $optimizations = $this->optimizationService->analyzeOptimizations($companyId);

        // Cache results
        cache()->put("compliance_alerts_{$companyId}", $alerts, 3600);
        cache()->put("compliance_optimizations_{$companyId}", $optimizations, 3600);

        return back()->with('success', 'Vérification de conformité mise à jour');
    }

    /**
     * Simulate VAT regime change
     */
    public function simulateRegimeChange(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;

        $newRegime = $request->input('regime', 'quarterly');

        $simulation = $this->optimizationService->simulateRegimeChange($companyId, $newRegime);

        return response()->json($simulation);
    }

    /**
     * Get fiscal calendar as JSON
     */
    public function fiscalCalendar(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->current_company_id;
        $year = $request->input('year', now()->year);

        $calendar = $this->complianceService->getFiscalCalendar($companyId, $year);

        return response()->json($calendar);
    }
}
