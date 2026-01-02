<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Scheduled tasks
Schedule::command('peppol:check-inbox')->everyFiveMinutes();
Schedule::command('invoices:send-reminders')->dailyAt('09:00');
Schedule::command('bank:import-coda')->hourly();

// Subscription management
Schedule::command('subscriptions:send-notifications')->dailyAt('08:00');
Schedule::command('subscriptions:generate-invoices')->monthlyOn(1, '00:00');

// System health & notifications
Schedule::job(new \App\Jobs\CheckSystemHealthJob)->dailyAt('06:00')->name('system-health-check');

// Onboarding emails
Schedule::command('onboarding:send-emails')->dailyAt('10:00');
