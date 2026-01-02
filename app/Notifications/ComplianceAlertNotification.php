<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplianceAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Company $company,
        public array $alerts
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $highSeverityCount = count($this->alerts);

        return (new MailMessage)
            ->subject("⚠️ Alertes Conformité Fiscale - {$this->company->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Nous avons détecté **{$highSeverityCount} alerte(s) de conformité fiscale** importante(s) pour {$this->company->name}.")
            ->line('Veuillez vérifier les points suivants :')
            ->lines($this->getAlertLines())
            ->action('Voir le Dashboard Conformité', url('/compliance'))
            ->line('Il est recommandé de traiter ces alertes dans les plus brefs délais pour éviter d\'éventuelles pénalités.')
            ->salutation('Cordialement, L\'équipe ComptaBE');
    }

    /**
     * Get alert lines for email
     */
    protected function getAlertLines(): array
    {
        $lines = [];

        foreach (array_slice($this->alerts, 0, 5) as $alert) {
            $lines[] = "**{$alert['title']}** : {$alert['message']}";
        }

        if (count($this->alerts) > 5) {
            $remaining = count($this->alerts) - 5;
            $lines[] = "... et {$remaining} autre(s) alerte(s)";
        }

        return $lines;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'alerts_count' => count($this->alerts),
            'alerts' => $this->alerts,
            'severity' => 'high',
        ];
    }
}
