<?php

namespace App\Notifications;

use App\Models\ModuleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleRequestApprovedNotification extends Notification implements ShouldQueue
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
            ->subject("Module approuvé - {$module->name}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre demande pour le module **{$module->name}** a été approuvée !")
            ->line("")
            ->line("**Détails :**")
            ->line("- Module : {$module->name}")
            ->line("- Code : {$module->code}")
            ->line("- Approuvé par : {$reviewer->full_name}")
            ->line("- Date : " . $request->reviewed_at->format('d/m/Y à H:i'));

        // Check if it's a trial
        $pivot = $request->company->modules()->where('module_id', $module->id)->first();
        if ($pivot && $pivot->pivot->status === 'trial' && $pivot->pivot->trial_ends_at) {
            $trialDays = now()->diffInDays($pivot->pivot->trial_ends_at);
            $message->line("")
                ->line("**Période d'essai :**")
                ->line("Vous bénéficiez d'une période d'essai de {$trialDays} jours.")
                ->line("Fin de l'essai : " . $pivot->pivot->trial_ends_at->format('d/m/Y'));
        }

        if ($request->admin_message) {
            $message->line("")
                ->line("**Message de l'administrateur :**")
                ->line($request->admin_message);
        }

        $message->action('Accéder au module', route('modules.my-modules'))
            ->line('Le module est maintenant disponible dans votre espace.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->moduleRequest;
        $module = $request->module;

        $pivot = $request->company->modules()->where('module_id', $module->id)->first();
        $isTrial = $pivot && $pivot->pivot->status === 'trial';

        return [
            'type' => 'module_request_approved',
            'severity' => 'success',
            'title' => 'Module approuvé',
            'message' => "Le module {$module->name} est maintenant actif" . ($isTrial ? ' (essai)' : ''),
            'module_request_id' => $request->id,
            'module_id' => $module->id,
            'module_name' => $module->name,
            'module_code' => $module->code,
            'is_trial' => $isTrial,
            'trial_ends_at' => $isTrial && $pivot->pivot->trial_ends_at ? $pivot->pivot->trial_ends_at->toISOString() : null,
            'admin_message' => $request->admin_message,
            'action_url' => route('modules.my-modules'),
            'action_text' => 'Voir mes modules',
            'icon' => 'check-circle',
            'color' => 'success',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'module_request_approved';
    }
}
