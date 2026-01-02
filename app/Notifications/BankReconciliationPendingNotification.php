<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BankReconciliationPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $pendingTransactionsCount,
        public float $totalUnreconciledAmount,
        public int $daysSinceLastReconciliation,
        public string $bankAccountName,
        public ?int $bankAccountId = null
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
        $severityLabel = $this->daysSinceLastReconciliation > 30 ? 'URGENT' : 'RAPPEL';

        $message = (new MailMessage)
            ->subject("⚠️ {$severityLabel}: Rapprochement bancaire en attente")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("📊 **Rappel rapprochement bancaire**: Vous avez **{$this->pendingTransactionsCount} transaction(s)** non rapprochée(s) sur le compte **{$this->bankAccountName}**.")
            ->line("**Montant total non rapproché**: " . number_format(abs($this->totalUnreconciledAmount), 2, ',', ' ') . " €")
            ->line("**Dernier rapprochement**: il y a **{$this->daysSinceLastReconciliation} jours**");

        if ($this->daysSinceLastReconciliation > 30) {
            $message->line("🔴 **ATTENTION**: Un rapprochement mensuel est recommandé pour garantir la fiabilité de votre comptabilité!");
        }

        $actionUrl = $this->bankAccountId
            ? route('bank-reconciliation.index', ['account' => $this->bankAccountId])
            : route('bank-reconciliation.index');

        $message->action('Lancer le rapprochement', $actionUrl)
            ->line('💡 **Astuce**: Notre IA peut suggérer automatiquement les correspondances pour accélérer le processus.')
            ->line('✅ **Bénéfices d\'un rapprochement régulier**:')
            ->line('• Détection rapide des erreurs et fraudes')
            ->line('• Vision précise de votre trésorerie réelle')
            ->line('• Conformité comptable et audit facilité');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $severity = $this->daysSinceLastReconciliation > 30 ? 'warning' : 'info';

        return [
            'type' => 'bank_reconciliation_pending',
            'severity' => $severity,
            'title' => 'Rapprochement bancaire en attente',
            'message' => "{$this->pendingTransactionsCount} transaction(s) non rapprochée(s) - {$this->bankAccountName}",
            'pending_count' => $this->pendingTransactionsCount,
            'total_unreconciled' => $this->totalUnreconciledAmount,
            'days_since_last' => $this->daysSinceLastReconciliation,
            'bank_account' => $this->bankAccountName,
            'bank_account_id' => $this->bankAccountId,
            'action_url' => $this->bankAccountId
                ? route('bank-reconciliation.index', ['account' => $this->bankAccountId])
                : route('bank-reconciliation.index'),
            'action_text' => 'Rapprocher',
            'icon' => 'refresh',
            'color' => $severity === 'warning' ? 'warning' : 'info',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'bank_reconciliation_pending';
    }
}
