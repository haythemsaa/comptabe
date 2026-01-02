<?php

namespace App\Notifications;

use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SubscriptionInvoice $invoice,
        public bool $isReminder = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->invoice->company;
        $isOverdue = $this->invoice->due_date && $this->invoice->due_date->isPast();

        $subject = match (true) {
            $isOverdue => "Rappel : Facture {$this->invoice->invoice_number} en retard de paiement",
            $this->isReminder => "Rappel : Facture {$this->invoice->invoice_number} a payer",
            default => "Nouvelle facture {$this->invoice->invoice_number} - ComptaBE",
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour,");

        if ($isOverdue) {
            $daysOverdue = $this->invoice->due_date->diffInDays(now());
            $message->line("**Attention** : La facture **{$this->invoice->invoice_number}** pour **{$company->name}** est en retard de paiement depuis {$daysOverdue} jour(s).")
                ->line("Merci de proceder au reglement des que possible pour eviter toute interruption de service.");
        } elseif ($this->isReminder) {
            $message->line("Ceci est un rappel pour la facture **{$this->invoice->invoice_number}** concernant votre abonnement ComptaBE.");
        } else {
            $message->line("Votre facture **{$this->invoice->invoice_number}** est disponible.");
        }

        return $message
            ->line("**Details de la facture :**")
            ->line("- Numero : {$this->invoice->invoice_number}")
            ->line("- Entreprise : {$company->name}")
            ->line("- Periode : " . ($this->invoice->period_start ? $this->invoice->period_start->format('d/m/Y') . ' - ' . $this->invoice->period_end->format('d/m/Y') : 'N/A'))
            ->line("- Montant HT : " . number_format($this->invoice->subtotal, 2) . " EUR")
            ->line("- TVA ({$this->invoice->vat_rate}%) : " . number_format($this->invoice->vat_amount, 2) . " EUR")
            ->line("- **Total TTC : " . number_format($this->invoice->total, 2) . " EUR**")
            ->line("- Echeance : " . ($this->invoice->due_date ? $this->invoice->due_date->format('d/m/Y') : 'A reception'))
            ->line("")
            ->line("**Coordonnees bancaires :**")
            ->line("IBAN : BE00 0000 0000 0000")
            ->line("BIC : GEBABEBB")
            ->line("Communication : {$this->invoice->invoice_number}")
            ->action('Voir la facture', route('subscription.show'))
            ->line("Merci de votre confiance.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_due',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'company_id' => $this->invoice->company_id,
            'company_name' => $this->invoice->company->name,
            'amount' => $this->invoice->total,
            'due_date' => $this->invoice->due_date?->toIso8601String(),
            'is_overdue' => $this->invoice->due_date?->isPast() ?? false,
            'message' => "Facture {$this->invoice->invoice_number} de " . number_format($this->invoice->total, 2) . " EUR a payer",
        ];
    }
}
