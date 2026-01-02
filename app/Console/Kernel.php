<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily AI insights (every morning at 7:00 AM)
        $schedule->command('ai:daily-insights')
            ->dailyAt('07:00')
            ->onOneServer()
            ->withoutOverlapping()
            ->runInBackground();

        // Compliance checks (every day at 8:00 AM)
        $schedule->job(new \App\Jobs\ComplianceCheckJob())
            ->dailyAt('08:00')
            ->onOneServer()
            ->withoutOverlapping();

        // Auto-categorize expenses (every hour)
        $schedule->job(new \App\Jobs\AutoCategorizeExpensesJob())
            ->hourly()
            ->onOneServer()
            ->withoutOverlapping();

        // Auto-reconcile bank transactions (every 2 hours)
        $schedule->job(new \App\Jobs\AutoReconcileTransactionsJob())
            ->everyTwoHours()
            ->onOneServer()
            ->withoutOverlapping();

        // Process uploaded documents (every 15 minutes)
        $schedule->job(new \App\Jobs\ProcessUploadedDocument(null, null))
            ->everyFifteenMinutes()
            ->onOneServer()
            ->withoutOverlapping();

        // Send invoice reminders (daily at 10:00 AM)
        $schedule->command('invoices:send-reminders')
            ->dailyAt('10:00')
            ->onOneServer();

        // Clean up old logs and cache (weekly on Sunday at 2:00 AM)
        $schedule->command('cache:prune-stale-tags')
            ->weekly()
            ->sundays()
            ->at('02:00');

        // Automatic backups (configured via admin settings)
        if (env('AUTO_BACKUP_ENABLED', false)) {
            $frequency = env('AUTO_BACKUP_FREQUENCY', 'daily');
            $time = env('AUTO_BACKUP_TIME', '02:00');

            $backup = $schedule->command('backup:auto')
                ->onOneServer()
                ->withoutOverlapping();

            match($frequency) {
                'hourly' => $backup->hourly(),
                'daily' => $backup->dailyAt($time),
                'weekly' => $backup->weeklyOn(0, $time), // Sunday
                default => $backup->dailyAt($time),
            };
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
