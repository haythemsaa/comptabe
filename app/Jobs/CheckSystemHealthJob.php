<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckSystemHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('Starting daily system health check');

        $startTime = microtime(true);
        $totalCompanies = 0;
        $totalNotifications = 0;
        $errors = 0;

        // Get all active companies
        $companies = Company::where('status', 'active')
            ->where(function ($query) {
                // Only check companies that are not in demo/trial with expired dates
                $query->whereNull('trial_ends_at')
                    ->orWhere('trial_ends_at', '>=', now());
            })
            ->get();

        foreach ($companies as $company) {
            try {
                Log::info('Running health check for company', ['company_id' => $company->id]);

                // Run all notification checks for this company
                $results = $notificationService->runAllChecks($company);

                // Count notifications sent
                $notificationsSent = collect($results)->filter(fn($result) => $result === true)->count();
                $totalNotifications += $notificationsSent;

                Log::info('Health check completed for company', [
                    'company_id' => $company->id,
                    'notifications_sent' => $notificationsSent,
                    'results' => $results,
                ]);

                $totalCompanies++;

            } catch (\Exception $e) {
                $errors++;

                Log::error('Error in health check for company', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Don't fail the entire job, continue with other companies
                continue;
            }

            // Small delay to avoid overwhelming the system
            usleep(100000); // 100ms
        }

        $duration = round(microtime(true) - $startTime, 2);

        Log::info('Daily system health check completed', [
            'duration_seconds' => $duration,
            'total_companies' => $totalCompanies,
            'total_notifications' => $totalNotifications,
            'errors' => $errors,
        ]);

        // If there were significant errors, log a warning
        if ($errors > 0) {
            Log::warning('System health check completed with errors', [
                'errors' => $errors,
                'success_rate' => $totalCompanies > 0 ? round(($totalCompanies / ($totalCompanies + $errors)) * 100, 2) . '%' : 'N/A',
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Daily system health check job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally notify admins about the failure
        // \App\Models\User::superAdmins()->each(function ($admin) use ($exception) {
        //     $admin->notify(new \App\Notifications\SystemHealthCheckFailedNotification($exception));
        // });
    }
}
