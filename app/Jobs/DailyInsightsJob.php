<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Company;
use App\Services\AI\ProactiveAssistantService;
use App\Notifications\DailyBusinessBriefNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DailyInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct()
    {
    }

    /**
     * Execute the job - Send daily insights to all active users.
     */
    public function handle(ProactiveAssistantService $assistantService): void
    {
        Log::info('Starting daily insights job');

        // Get all active companies with subscriptions
        $companies = Company::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                  ->orWhere('status', 'trial');
        })->get();

        foreach ($companies as $company) {
            $this->processCompany($company, $assistantService);
        }

        Log::info('Daily insights job completed', [
            'companies_processed' => $companies->count(),
        ]);
    }

    /**
     * Process a single company.
     */
    protected function processCompany(Company $company, ProactiveAssistantService $assistantService): void
    {
        // Get owner and admins (they receive daily brief)
        $recipients = User::where('current_company_id', $company->id)
            ->whereHas('companyUsers', function ($query) use ($company) {
                $query->where('company_id', $company->id)
                      ->whereIn('role', ['owner', 'admin']);
            })
            ->where('email_verified_at', '!=', null)
            ->get();

        foreach ($recipients as $user) {
            try {
                // Generate daily brief
                $brief = $assistantService->generateDailyBrief($user);

                // Only send if there's something to report
                if ($this->shouldSendBrief($brief)) {
                    $user->notify(new DailyBusinessBriefNotification($brief));

                    Log::info('Daily brief sent', [
                        'user_id' => $user->id,
                        'company_id' => $company->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send daily brief', [
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Determine if brief should be sent.
     */
    protected function shouldSendBrief(array $brief): bool
    {
        // Send if there are priority actions or critical alerts
        return !empty($brief['priority_actions']) || !empty($brief['critical_alerts']);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Daily insights job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
