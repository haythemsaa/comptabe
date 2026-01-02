<?php

namespace App\Notifications;

use App\Models\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SubscriptionInvoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->invoice->company;

        return (new MailMessage)
            ->subject("Confirmation de paiement - Facture {$this->invoice->invoice_number}")
            ->greeting("Bonjour,")
            ->line("Nous avons bien recu votre paiement pour la facture **{$this->invoice->invoice_number}**.")
            ->line("**Details du paiement :**")
            ->line("- Facture : {$this->invoice->invoice_number}")
            ->line("- Entreprise : {$company->name}")
            ->line("- Montant paye : " . number_format($this->invoice->total, 2) . " EUR")
            ->line("- Date de paiement : " . $this->invoice->paid_at->format('d/m/Y'))
            ->line("- Methode : " . ucfirst($this->invoice->payment_method ?? 'N/A'))
            ->line("")
            ->line("Votre abonnement est actif jusqu'au **" . ($this->invoice->subscription?->current_period_end?->format('d/m/Y') ?? 'N/A') . "**.")
            ->action('Acceder a mon compte', route('dashboard'))
            ->line('Merci de votre confiance !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'company_id' => $this->invoice->company_id,
            'company_name' => $this->invoice->company->name,
            'amount' => $this->invoice->total,
            'paid_at' => $this->invoice->paid_at->toIso8601String(),
            'message' => "Paiement de " . number_format($this->invoice->total, 2) . " EUR recu pour la facture {$this->invoice->invoice_number}",
        ];
    }
}
