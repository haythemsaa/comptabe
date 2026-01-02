<?php

namespace App\Notifications;

use App\Models\Report;
use App\Models\ReportExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Report $report,
        protected ReportExecution $execution
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rapport prêt: ' . $this->report->name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre rapport "' . $this->report->name . '" est prêt à être téléchargé.')
            ->line('Format: ' . strtoupper($this->execution->format))
            ->line('Taille: ' . $this->execution->formatted_file_size)
            ->action('Télécharger le rapport', route('reports.download', $this->execution))
            ->line('Ce lien sera valide pendant 7 jours.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_ready',
            'report_id' => $this->report->id,
            'report_name' => $this->report->name,
            'execution_id' => $this->execution->id,
            'format' => $this->execution->format,
            'download_url' => route('reports.download', $this->execution),
        ];
    }
}
