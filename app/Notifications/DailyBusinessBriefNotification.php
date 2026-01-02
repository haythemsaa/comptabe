<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyBusinessBriefNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $brief
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('📊 Votre brief quotidien - ' . now()->format('d/m/Y'))
            ->greeting('Bonjour ' . $this->brief['user_name'] . ',')
            ->line('Voici votre résumé quotidien généré par intelligence artificielle.');

        // Activity Summary
        if (!empty($this->brief['summary'])) {
            $summary = $this->brief['summary'];
            $message->line('')
                ->line('**📈 Activité d\'hier:**');

            if ($summary['invoices_created'] > 0) {
                $message->line("✓ {$summary['invoices_created']} facture(s) créée(s)");
            }

            if ($summary['invoices_paid'] > 0) {
                $message->line("✓ {$summary['invoices_paid']} facture(s) payée(s) - " . number_format($summary['revenue_received'], 2) . " €");
            }

            if ($summary['expenses_recorded'] > 0) {
                $message->line("✓ {$summary['expenses_recorded']} dépense(s) enregistrée(s) - " . number_format($summary['expenses_amount'], 2) . " €");
            }

            if ($summary['invoices_created'] == 0 && $summary['invoices_paid'] == 0 && $summary['expenses_recorded'] == 0) {
                $message->line('Aucune activité enregistrée hier.');
            }
        }

        // Critical Alerts
        if (!empty($this->brief['critical_alerts'])) {
            $message->line('')
                ->line('**🚨 Alertes critiques:**');

            foreach ($this->brief['critical_alerts'] as $alert) {
                $emoji = $alert['severity'] === 'critical' ? '🔴' : '🟠';
                $message->line("{$emoji} **{$alert['title']}** - {$alert['message']}");
            }
        }

        // Priority Actions
        if (!empty($this->brief['priority_actions'])) {
            $message->line('')
                ->line('**✅ 3 actions prioritaires du jour:**');

            foreach ($this->brief['priority_actions'] as $action) {
                $message->line("{$action['priority']}. {$action['title']}");
                if (isset($action['url'])) {
                    $message->line("   → [Accéder]({$action['url']})");
                }
            }
        }

        // AI Insights
        if (!empty($this->brief['ai_insights'])) {
            $message->line('')
                ->line('**💡 Insights IA:**');

            foreach ($this->brief['ai_insights'] as $insight) {
                $message->line("• **{$insight['title']}**")
                    ->line("  {$insight['description']}")
                    ->line("  _Recommandation: {$insight['recommendation']}_");
            }
        }

        $message->line('')
            ->action('Accéder au dashboard', route('dashboard'))
            ->line('')
            ->line('Bonne journée !')
            ->line('_Ce brief quotidien est généré automatiquement par votre assistant IA._');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'daily_brief',
            'date' => $this->brief['date'],
            'summary' => $this->brief['summary'],
            'priority_actions_count' => count($this->brief['priority_actions']),
            'critical_alerts_count' => count($this->brief['critical_alerts']),
        ];
    }
}
