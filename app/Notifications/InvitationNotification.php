<?php

namespace App\Notifications;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Invitation $invitation
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
        $inviter = $this->invitation->inviter;
        $company = $this->invitation->company;
        $role = $this->invitation->role_label;

        return (new MailMessage)
            ->subject("Invitation a rejoindre {$company->name} sur ComptaBE")
            ->greeting("Bonjour,")
            ->line("{$inviter->full_name} vous invite a rejoindre l'entreprise **{$company->name}** sur ComptaBE.")
            ->line("Vous avez ete invite en tant que **{$role}**.")
            ->line("Cette invitation expire le {$this->invitation->expires_at->format('d/m/Y a H:i')}.")
            ->action('Accepter l\'invitation', $this->invitation->getAcceptUrl())
            ->line("Si vous n'avez pas encore de compte, vous pourrez en creer un lors de l'acceptation de l'invitation.")
            ->line("Si vous n'attendiez pas cette invitation, vous pouvez ignorer cet email.")
            ->salutation('Cordialement, L\'equipe ComptaBE');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'company_id' => $this->invitation->company_id,
            'company_name' => $this->invitation->company->name,
            'role' => $this->invitation->role,
        ];
    }
}
