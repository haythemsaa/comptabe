<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowCashFlowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public float $currentBalance,
        public float $projectedBalance,
        public int $daysUntilNegative,
        public float $upcomingPayables,
        public float $upcomingReceivables
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severityLabel = $this->daysUntilNegative <= 7 ? 'CRITIQUE' : 'ATTENTION';

        $message = (new MailMessage)
            ->subject("🚨 {$severityLabel}: Trésorerie basse")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("⚠️ **Alerte trésorerie**: Votre solde bancaire risque d'être négatif dans **{$this->daysUntilNegative} jours**.")
            ->line("**Solde actuel**: " . number_format($this->currentBalance, 2, ',', ' ') . " €")
            ->line("**Solde projeté (J+30)**: " . number_format($this->projectedBalance, 2, ',', ' ') . " €");

        if ($this->daysUntilNegative <= 7) {
            $message->line("🔴 **URGENT**: Prenez des mesures immédiates!");
        }

        $message->line("**Encaissements prévus**: +" . number_format($this->upcomingReceivables, 2, ',', ' ') . " €")
            ->line("**Décaissements prévus**: -" . number_format($this->upcomingPayables, 2, ',', ' ') . " €")
            ->action('Voir prévisions trésorerie', route('dashboard'))
            ->line('💡 **Suggestions**:')
            ->line('• Relancez vos factures en retard pour accélérer les encaissements')
            ->line('• Négociez des délais de paiement avec vos fournisseurs')
            ->line('• Envisagez une ligne de crédit court terme');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $severity = $this->daysUntilNegative <= 7 ? 'critical' : 'warning';

        return [
            'type' => 'low_cash_flow',
            'severity' => $severity,
            'title' => 'Trésorerie basse',
            'message' => "Solde risque négatif dans {$this->daysUntilNegative} jours",
            'current_balance' => $this->currentBalance,
            'projected_balance' => $this->projectedBalance,
            'days_until_negative' => $this->daysUntilNegative,
            'upcoming_payables' => $this->upcomingPayables,
            'upcoming_receivables' => $this->upcomingReceivables,
            'action_url' => route('dashboard'),
            'action_text' => 'Voir trésorerie',
            'icon' => 'trending-down',
            'color' => $severity === 'critical' ? 'danger' : 'warning',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'low_cash_flow';
    }
}
