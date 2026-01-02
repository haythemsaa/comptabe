<?php

namespace App\Notifications;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ApprovalRequest $approvalRequest,
        public User $approver,
        public string $decision, // 'approved', 'rejected', 'changes_requested'
        public ?string $comment = null
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

        $status = match($this->decision) {
            'approved' => '✅ approuvée',
            'rejected' => '❌ rejetée',
            'changes_requested' => '📝 modifiée (changements demandés)',
            default => 'traitée',
        };

        $subject = match($this->decision) {
            'approved' => "✅ Demande approuvée - {$workflow->name}",
            'rejected' => "❌ Demande rejetée - {$workflow->name}",
            'changes_requested' => "📝 Changements demandés - {$workflow->name}",
            default => "Mise à jour demande - {$workflow->name}",
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre demande d'approbation pour **{$workflow->name}** a été **{$status}** par **{$this->approver->full_name}**.")
            ->line("")
            ->line("**Détails de la demande :**")
            ->line("- Workflow : {$workflow->name}")
            ->line("- Montant : " . number_format($request->amount, 2, ',', ' ') . " €")
            ->line("- Décision : " . ucfirst($this->decision))
            ->line("- Date : " . now()->format('d/m/Y à H:i'));

        if ($this->comment) {
            $message->line("")
                ->line("**Commentaire de l'approbateur :**")
                ->line($this->comment);
        }

        // Status-specific messages
        if ($this->decision === 'approved') {
            if ($request->status === 'approved') {
                $message->line("")
                    ->line("🎉 **Toutes les approbations sont complètes !** La demande est maintenant validée.");
            } else {
                $message->line("")
                    ->line("✅ Étape {$request->current_step}/{$workflow->rules->count()} validée. La demande passe à l'étape suivante.");
            }
        } elseif ($this->decision === 'rejected') {
            $message->line("")
                ->line("La demande a été définitivement rejetée. Aucune action supplémentaire n'est requise.");
        } elseif ($this->decision === 'changes_requested') {
            $message->line("")
                ->line("Veuillez apporter les modifications demandées et soumettre à nouveau la demande.");
        }

        $message->action('Voir la demande', route('approvals.show', $request->id));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $request = $this->approvalRequest;
        $workflow = $request->workflow;

        $severity = match($this->decision) {
            'approved' => 'success',
            'rejected' => 'danger',
            'changes_requested' => 'warning',
            default => 'info',
        };

        $icon = match($this->decision) {
            'approved' => 'check-circle',
            'rejected' => 'x-circle',
            'changes_requested' => 'edit',
            default => 'info',
        };

        return [
            'type' => 'approval_decision',
            'severity' => $severity,
            'title' => ucfirst($this->decision === 'approved' ? 'Demande approuvée' :
                               ($this->decision === 'rejected' ? 'Demande rejetée' : 'Changements demandés')),
            'message' => "{$this->approver->full_name} a {$this->decision} votre demande pour {$workflow->name}",
            'approval_request_id' => $request->id,
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->name,
            'approver_name' => $this->approver->full_name,
            'decision' => $this->decision,
            'comment' => $this->comment,
            'amount' => $request->amount,
            'current_step' => $request->current_step,
            'status' => $request->status,
            'action_url' => route('approvals.show', $request->id),
            'action_text' => 'Voir la demande',
            'icon' => $icon,
            'color' => $severity,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'approval_decision';
    }
}
