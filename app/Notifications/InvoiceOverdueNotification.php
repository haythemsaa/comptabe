<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $overdueCount,
        public float $totalAmount,
        public int $avgDaysOverdue,
        public ?Invoice $oldestInvoice = null
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
        $message = (new MailMessage)
            ->subject("⚠️ {$this->overdueCount} facture(s) en retard de paiement")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Vous avez **{$this->overdueCount} facture(s)** en retard de paiement pour un total de **" . number_format($this->totalAmount, 2, ',', ' ') . " €**.")
            ->line("Retard moyen: **{$this->avgDaysOverdue} jours**");

        if ($this->oldestInvoice) {
            $daysOverdue = now()->diffInDays($this->oldestInvoice->due_date);
            $message->line("**Facture la plus ancienne**: {$this->oldestInvoice->invoice_number} ({$this->oldestInvoice->partner->name}) - **{$daysOverdue} jours de retard**");
        }

        $message->action('Voir les factures en retard', route('invoices.index', ['status' => 'overdue']))
            ->line('💡 **Suggestion**: Utilisez l\'envoi automatique de rappels pour améliorer le recouvrement.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice_overdue',
            'severity' => 'warning',
            'title' => "{$this->overdueCount} facture(s) en retard",
            'message' => "Total: " . number_format($this->totalAmount, 2, ',', ' ') . " € - Retard moyen: {$this->avgDaysOverdue} jours",
            'count' => $this->overdueCount,
            'total_amount' => $this->totalAmount,
            'avg_days_overdue' => $this->avgDaysOverdue,
            'oldest_invoice' => $this->oldestInvoice ? [
                'id' => $this->oldestInvoice->id,
                'number' => $this->oldestInvoice->invoice_number,
                'partner' => $this->oldestInvoice->partner->name,
                'days_overdue' => now()->diffInDays($this->oldestInvoice->due_date),
            ] : null,
            'action_url' => route('invoices.index', ['status' => 'overdue']),
            'action_text' => 'Voir les factures',
            'icon' => 'alert-circle',
            'color' => 'warning',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'invoice_overdue';
    }
}
