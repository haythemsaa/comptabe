<?php

namespace App\Notifications;

use App\Models\ModuleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ModuleRequest $moduleRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->moduleRequest;
        $module = $request->module;
        $reviewer = $request->reviewedBy;

        $message = (new MailMessage)
            ->subject("Demande de module refusée - {$module->name}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre demande pour le module **{$module->name}** a été refusée.")
            ->line("")
            ->line("**Détails :**")
            ->line("- Module : {$module->name}")
            ->line("- Refusé par : {$reviewer->full_name}")
            ->line("- Date : " . $request->reviewed_at->format('d/m/Y à H:i'));

        if ($request->admin_message) {
            $message->line("")
                ->line("**Raison du refus :**")
                ->line($request->admin_message);
        }

        $message->action('Explorer d\'autres modules', route('modules.marketplace'))
            ->line('Vous pouvez explorer d\'autres modules dans la marketplace.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->moduleRequest;
        $module = $request->module;

        return [
            'type' => 'module_request_rejected',
            'severity' => 'warning',
            'title' => 'Demande de module refusée',
            'message' => "Votre demande pour {$module->name} a été refusée",
            'module_request_id' => $request->id,
            'module_id' => $module->id,
            'module_name' => $module->name,
            'module_code' => $module->code,
            'admin_message' => $request->admin_message,
            'action_url' => route('modules.marketplace'),
            'action_text' => 'Voir la marketplace',
            'icon' => 'x-circle',
            'color' => 'danger',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'module_request_rejected';
    }
}
