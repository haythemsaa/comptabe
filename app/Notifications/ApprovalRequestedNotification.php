<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ApprovalRequest $approvalRequest
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->approvalRequest;
        $workflow = $request->workflow;
        $requester = $request->requester;
        $currentRule = $workflow->rules()->where('step_order', $request->current_step)->first();

        $documentType = match($workflow->document_type) {
            'invoice' => 'une facture',
            'expense' => 'une dépense',
            'payment' => 'un paiement',
            'journal_entry' => 'une écriture comptable',
            default => 'un document',
        };

        $message = (new MailMessage)
            ->subject("🔔 Nouvelle demande d'approbation - {$workflow->name}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("**{$requester->full_name}** a soumis {$documentType} nécessitant votre approbation.")
            ->line("")
            ->line("**Détails de la demande :**")
            ->line("- Workflow : {$workflow->name}")
            ->line("- Montant : " . number_format($request->amount, 2, ',', ' ') . " €")
            ->line("- Étape : {$request->current_step}/{$workflow->rules->count()}")
            ->line("- Date de soumission : " . $request->created_at->format('d/m/Y à H:i'));

        if ($currentRule) {
            $message->line("- Approbations requises : {$currentRule->required_approvals}");
        }

        if ($request->notes) {
            $message->line("")
                ->line("**Note du demandeur :**")
                ->line($request->notes);
        }

        // Timeout warning if configured
        if ($workflow->timeout_hours) {
            $deadline = $request->created_at->addHours($workflow->timeout_hours);
            $message->line("")
                ->line("⏰ **Délai d'approbation** : {$deadline->format('d/m/Y à H:i')} ({$workflow->timeout_hours}h)");
        }

        $message->action('Voir la demande', route('approvals.show', $request->id))
            ->line('Vous pouvez approuver, rejeter ou demander des modifications.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $request = $this->approvalRequest;
        $workflow = $request->workflow;

        return [
            'type' => 'approval_requested',
            'severity' => 'info',
            'title' => "Nouvelle demande d'approbation",
            'message' => "{$request->requester->full_name} demande votre approbation pour {$workflow->name}",
            'approval_request_id' => $request->id,
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->name,
            'requester_name' => $request->requester->full_name,
            'amount' => $request->amount,
            'current_step' => $request->current_step,
            'total_steps' => $workflow->rules->count(),
            'document_type' => $workflow->document_type,
            'action_url' => route('approvals.show', $request->id),
            'action_text' => 'Voir la demande',
            'icon' => 'clipboard-check',
            'color' => 'primary',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'approval_requested';
    }
}
