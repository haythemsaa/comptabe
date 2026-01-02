<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->subscription->company;
        $plan = $this->subscription->plan;
        $isTrialing = $this->subscription->status === 'trialing';

        $message = (new MailMessage)
            ->subject($isTrialing ? "Bienvenue sur ComptaBE - Votre essai gratuit a commence" : "Votre abonnement ComptaBE est actif")
            ->greeting("Bienvenue sur ComptaBE !");

        if ($isTrialing) {
            $message->line("Votre periode d'essai gratuite de **{$plan->trial_days} jours** pour **{$company->name}** a bien demarre.")
                ->line("Pendant cette periode, vous avez acces a toutes les fonctionnalites du plan **{$plan->name}**.")
                ->line("Votre essai se termine le **{$this->subscription->trial_ends_at->format('d/m/Y')}**.");
        } else {
            $billingCycle = $this->subscription->billing_cycle === 'yearly' ? 'annuel' : 'mensuel';
            $message->line("Votre abonnement **{$plan->name}** ({$billingCycle}) pour **{$company->name}** est maintenant actif.")
                ->line("Montant : **{$this->subscription->amount} EUR** / " . ($this->subscription->billing_cycle === 'yearly' ? 'an' : 'mois'));
        }

        return $message
            ->line('Fonctionnalites incluses :')
            ->line("- Jusqu'a " . ($plan->max_invoices_per_month == -1 ? 'illimite' : $plan->max_invoices_per_month) . " factures/mois")
            ->line("- Jusqu'a " . ($plan->max_clients == -1 ? 'illimite' : $plan->max_clients) . " clients")
            ->line("- Jusqu'a " . ($plan->max_users == -1 ? 'illimite' : $plan->max_users) . " utilisateurs")
            ->action('Acceder a mon compte', route('dashboard'))
            ->line('Merci de votre confiance !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_created',
            'subscription_id' => $this->subscription->id,
            'company_id' => $this->subscription->company_id,
            'company_name' => $this->subscription->company->name,
            'plan_name' => $this->subscription->plan->name,
            'status' => $this->subscription->status,
            'message' => "Abonnement {$this->subscription->plan->name} cree pour {$this->subscription->company->name}",
        ];
    }
}
