<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Jobs\GenerateReportJob;
use Illuminate\Console\Command;

class GenerateScheduledReports extends Command
{
    protected $signature = 'reports:generate-scheduled';
    protected $description = 'Generate all scheduled reports that are due';

    public function handle(): int
    {
        $reports = Report::whereNotNull('schedule')
            ->with(['company', 'user'])
            ->get()
            ->filter(fn($report) => $report->shouldRun());

        if ($reports->isEmpty()) {
            $this->info('No scheduled reports to generate.');
            return Command::SUCCESS;
        }

        $this->info("Found {$reports->count()} report(s) to generate.");

        foreach ($reports as $report) {
            $this->line("Dispatching: {$report->name}");

            $user = $report->user ?? $report->company->users()->first();

            if (!$user) {
                $this->warn("  Skipping: No user found for report #{$report->id}");
                continue;
            }

            GenerateReportJob::dispatch($report, $user, [
                'format' => $report->config['format'] ?? 'pdf',
                'date_from' => $this->resolveDateRelative($report->config['date_from'] ?? 'month_start'),
                'date_to' => $this->resolveDateRelative($report->config['date_to'] ?? 'today'),
            ]);

            $this->info("  Dispatched successfully.");
        }

        return Command::SUCCESS;
    }

    protected function resolveDateRelative(string $relative): string
    {
        return match($relative) {
            'today' => now()->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'month_start' => now()->startOfMonth()->format('Y-m-d'),
            'month_end' => now()->endOfMonth()->format('Y-m-d'),
            'quarter_start' => now()->startOfQuarter()->format('Y-m-d'),
            'quarter_end' => now()->endOfQuarter()->format('Y-m-d'),
            'year_start' => now()->startOfYear()->format('Y-m-d'),
            'year_end' => now()->endOfYear()->format('Y-m-d'),
            'last_month_start' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
            'last_month_end' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            'last_quarter_start' => now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
            'last_quarter_end' => now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            'last_year_start' => now()->subYear()->startOfYear()->format('Y-m-d'),
            'last_year_end' => now()->subYear()->endOfYear()->format('Y-m-d'),
            default => $relative,
        };
    }
}
