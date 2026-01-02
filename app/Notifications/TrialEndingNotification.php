<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->subscription->company;
        $plan = $this->subscription->plan;
        $trialEndsAt = $this->subscription->trial_ends_at;

        return (new MailMessage)
            ->subject("Votre période d'essai se termine dans {$this->daysRemaining} jour(s)")
            ->greeting("Bonjour,")
            ->line("La période d'essai gratuite de **{$company->name}** sur ComptaBE se termine le **{$trialEndsAt->format('d/m/Y')}**.")
            ->line("Pour continuer à utiliser toutes les fonctionnalités, veuillez choisir un abonnement.")
            ->line("Votre plan actuel : **{$plan->name}**")
            ->line("Prix mensuel : **{$plan->price_monthly} €/mois**")
            ->line("Prix annuel : **{$plan->price_yearly} €/an** (économisez " . round((($plan->price_monthly * 12) - $plan->price_yearly) / ($plan->price_monthly * 12) * 100) . "%)")
            ->action('Choisir un abonnement', route('subscription.upgrade'))
            ->line("N'hésitez pas à nous contacter si vous avez des questions.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_ending',
            'subscription_id' => $this->subscription->id,
            'company_id' => $this->subscription->company_id,
            'company_name' => $this->subscription->company->name,
            'days_remaining' => $this->daysRemaining,
            'trial_ends_at' => $this->subscription->trial_ends_at->toIso8601String(),
            'message' => "Période d'essai de {$this->subscription->company->name} se termine dans {$this->daysRemaining} jour(s)",
        ];
    }
}
