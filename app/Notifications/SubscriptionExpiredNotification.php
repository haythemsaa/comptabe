<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public string $reason = 'expired' // 'expired', 'cancelled', 'payment_failed'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->subscription->company;
        $plan = $this->subscription->plan;

        $subject = match ($this->reason) {
            'cancelled' => "Votre abonnement ComptaBE a ete annule",
            'payment_failed' => "Votre abonnement ComptaBE a ete suspendu - Probleme de paiement",
            default => "Votre abonnement ComptaBE a expire",
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour,");

        match ($this->reason) {
            'cancelled' => $message
                ->line("Votre abonnement **{$plan->name}** pour **{$company->name}** a ete annule.")
                ->line("Vous n'aurez plus acces aux fonctionnalites premium a partir de maintenant.")
                ->line("Vos donnees restent conservees pendant 30 jours."),
            'payment_failed' => $message
                ->line("**Important** : Votre abonnement **{$plan->name}** pour **{$company->name}** a ete suspendu en raison d'un echec de paiement.")
                ->line("Pour retablir l'acces a votre compte, veuillez mettre a jour vos informations de paiement."),
            default => $message
                ->line("Votre abonnement **{$plan->name}** pour **{$company->name}** a expire.")
                ->line("Pour continuer a utiliser ComptaBE, veuillez renouveler votre abonnement."),
        };

        return $message
            ->line("")
            ->line("Nous serions ravis de vous revoir ! Renouvelez votre abonnement pour retrouver l'acces a :")
            ->line("- Creation de factures illimitee")
            ->line("- Gestion de clients")
            ->line("- Integration Peppol")
            ->line("- Et bien plus encore...")
            ->action('Renouveler mon abonnement', route('subscription.upgrade'))
            ->line("Des questions ? N'hesitez pas a nous contacter.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_' . $this->reason,
            'subscription_id' => $this->subscription->id,
            'company_id' => $this->subscription->company_id,
            'company_name' => $this->subscription->company->name,
            'plan_name' => $this->subscription->plan->name,
            'reason' => $this->reason,
            'message' => match ($this->reason) {
                'cancelled' => "Abonnement {$this->subscription->plan->name} annule pour {$this->subscription->company->name}",
                'payment_failed' => "Abonnement {$this->subscription->plan->name} suspendu - echec de paiement",
                default => "Abonnement {$this->subscription->plan->name} expire pour {$this->subscription->company->name}",
            },
        ];
    }
}
