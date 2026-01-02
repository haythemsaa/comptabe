<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Services\Compliance\BelgianTaxComplianceService;
use App\Services\Compliance\VATOptimizationService;
use App\Notifications\ComplianceAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ComplianceCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $companyId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        BelgianTaxComplianceService $complianceService,
        VATOptimizationService $optimizationService
    ): void {
        try {
            if ($this->companyId) {
                $this->checkCompanyCompliance($this->companyId, $complianceService, $optimizationService);
            } else {
                $this->checkAllCompanies($complianceService, $optimizationService);
            }
        } catch (\Exception $e) {
            Log::error('Compliance check failed', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Check compliance for all active companies
     */
    protected function checkAllCompanies(
        BelgianTaxComplianceService $complianceService,
        VATOptimizationService $optimizationService
    ): void {
        $companies = Company::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                  ->orWhere('status', 'trial');
        })->get();

        Log::info("Starting compliance check for {$companies->count()} companies");

        foreach ($companies as $company) {
            try {
                $this->checkCompanyCompliance($company->id, $complianceService, $optimizationService);
            } catch (\Exception $e) {
                Log::warning("Compliance check failed for company {$company->id}", [
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        Log::info('Compliance check completed for all companies');
    }

    /**
     * Check compliance for a specific company
     */
    protected function checkCompanyCompliance(
        string $companyId,
        BelgianTaxComplianceService $complianceService,
        VATOptimizationService $optimizationService
    ): void {
        $company = Company::find($companyId);

        if (!$company) {
            Log::warning("Company {$companyId} not found");
            return;
        }

        Log::info("Checking compliance for company: {$company->name}");

        // Run compliance checks
        $alerts = $complianceService->checkVATCompliance($companyId);

        // Run optimization analysis
        $optimizations = $optimizationService->analyzeOptimizations($companyId);

        // Store results in cache for dashboard
        cache()->put(
            "compliance_alerts_{$companyId}",
            $alerts,
            now()->addDay()
        );

        cache()->put(
            "compliance_optimizations_{$companyId}",
            $optimizations,
            now()->addDay()
        );

        // Send notifications for high severity alerts
        $this->sendNotifications($company, $alerts);

        Log::info("Compliance check completed for {$company->name}", [
            'alerts_count' => count($alerts),
            'optimizations_count' => count($optimizations),
        ]);
    }

    /**
     * Send notifications for high severity alerts
     */
    protected function sendNotifications(Company $company, array $alerts): void
    {
        $highSeverityAlerts = array_filter($alerts, function ($alert) {
            return $alert['severity'] === 'high';
        });

        if (empty($highSeverityAlerts)) {
            return;
        }

        // Get company owners and accountants
        $usersToNotify = User::where('current_company_id', $company->id)
            ->whereIn('role', ['owner', 'accountant'])
            ->get();

        foreach ($usersToNotify as $user) {
            try {
                $user->notify(new ComplianceAlertNotification($company, $highSeverityAlerts));
            } catch (\Exception $e) {
                Log::warning("Failed to send compliance notification to user {$user->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Sent compliance notifications to {$usersToNotify->count()} users for company {$company->name}");
    }
}
