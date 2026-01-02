<?php

namespace App\Console\Commands;

use App\Mail\OnboardingEducationalEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOnboardingEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding:send-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send educational onboarding emails to users at specific intervals';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Sending onboarding emails...');

        // Day 2 emails (users registered 2 days ago)
        $day2Users = $this->getUsersRegisteredDaysAgo(2);
        foreach ($day2Users as $user) {
            if (!$this->hasReceivedEmail($user, 'day_2')) {
                Mail::to($user->email)->send(new OnboardingEducationalEmail($user, 'day_2'));
                $this->markEmailSent($user, 'day_2');
                $this->info("Sent day 2 email to {$user->email}");
            }
        }

        // Day 5 emails (users registered 5 days ago)
        $day5Users = $this->getUsersRegisteredDaysAgo(5);
        foreach ($day5Users as $user) {
            if (!$this->hasReceivedEmail($user, 'day_5')) {
                Mail::to($user->email)->send(new OnboardingEducationalEmail($user, 'day_5'));
                $this->markEmailSent($user, 'day_5');
                $this->info("Sent day 5 email to {$user->email}");
            }
        }

        // Day 7 emails (users registered 7 days ago)
        $day7Users = $this->getUsersRegisteredDaysAgo(7);
        foreach ($day7Users as $user) {
            if (!$this->hasReceivedEmail($user, 'day_7')) {
                Mail::to($user->email)->send(new OnboardingEducationalEmail($user, 'day_7'));
                $this->markEmailSent($user, 'day_7');
                $this->info("Sent day 7 email to {$user->email}");
            }
        }

        $this->info('Onboarding emails sent successfully!');

        return self::SUCCESS;
    }

    /**
     * Get users registered exactly N days ago.
     */
    private function getUsersRegisteredDaysAgo(int $days): \Illuminate\Database\Eloquent\Collection
    {
        $targetDate = Carbon::today()->subDays($days);

        return User::whereDate('created_at', $targetDate)
            ->whereNotNull('email_verified_at')
            ->where('is_superadmin', false)
            ->get();
    }

    /**
     * Check if user has already received this email.
     */
    private function hasReceivedEmail(User $user, string $emailType): bool
    {
        $progress = $user->onboardingProgress;

        if (!$progress) {
            return false;
        }

        $metadata = $progress->metadata ?? [];
        $sentEmails = $metadata['sent_emails'] ?? [];

        return in_array($emailType, $sentEmails);
    }

    /**
     * Mark email as sent for user.
     */
    private function markEmailSent(User $user, string $emailType): void
    {
        $progress = $user->onboardingProgress;

        if (!$progress) {
            return;
        }

        $metadata = $progress->metadata ?? [];
        $sentEmails = $metadata['sent_emails'] ?? [];
        $sentEmails[] = $emailType;

        $metadata['sent_emails'] = $sentEmails;
        $progress->update(['metadata' => $metadata]);
    }
}
