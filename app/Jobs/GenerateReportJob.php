<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\ReportExecution;
use App\Models\User;
use App\Services\Reports\ReportBuilderService;
use App\Notifications\ReportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        protected Report $report,
        protected User $user,
        protected array $parameters = []
    ) {}

    public function handle(ReportBuilderService $reportBuilder): void
    {
        // Créer l'exécution
        $execution = ReportExecution::create([
            'report_id' => $this->report->id,
            'user_id' => $this->user->id,
            'status' => ReportExecution::STATUS_RUNNING,
            'format' => $this->parameters['format'] ?? 'pdf',
            'parameters' => $this->parameters,
            'started_at' => now(),
        ]);

        try {
            $startTime = microtime(true);

            // Configurer le builder
            $reportBuilder
                ->setCompany($this->report->company)
                ->setDateRange(
                    Carbon::parse($this->parameters['date_from']),
                    Carbon::parse($this->parameters['date_to'])
                );

            // Générer le rapport
            $options = $this->report->config['options'] ?? [];
            $reportData = $reportBuilder->generate($this->report->type, $options);

            // Exporter
            $format = $this->parameters['format'] ?? 'pdf';
            $filePath = $reportBuilder->export($reportData, $format);
            $relativePath = str_replace(storage_path('app/'), '', $filePath);

            // Mettre à jour l'exécution
            $execution->update([
                'status' => ReportExecution::STATUS_COMPLETED,
                'file_path' => $relativePath,
                'file_size' => filesize($filePath),
                'execution_time_ms' => (microtime(true) - $startTime) * 1000,
                'completed_at' => now(),
            ]);

            // Mettre à jour le rapport
            $this->report->update(['last_generated_at' => now()]);

            // Notifier l'utilisateur
            $this->user->notify(new ReportReadyNotification($this->report, $execution));

            Log::info('Report generated successfully', [
                'report_id' => $this->report->id,
                'execution_id' => $execution->id,
                'format' => $format,
            ]);

        } catch (\Exception $e) {
            $execution->update([
                'status' => ReportExecution::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            Log::error('Report generation failed', [
                'report_id' => $this->report->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateReportJob failed completely', [
            'report_id' => $this->report->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
