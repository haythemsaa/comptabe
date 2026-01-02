<?php

namespace App\Notifications;

use App\Models\ModuleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleRequestSubmittedNotification extends Notification implements ShouldQueue
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
        $company = $request->company;
        $module = $request->module;
        $requester = $request->requestedBy;

        $message = (new MailMessage)
            ->subject("Nouvelle demande de module - {$module->name}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("**{$company->name}** a demandé l'activation du module **{$module->name}**.")
            ->line("")
            ->line("**Détails de la demande :**")
            ->line("- Module : {$module->name}")
            ->line("- Code : {$module->code}")
            ->line("- Catégorie : " . ucfirst($module->category))
            ->line("- Entreprise : {$company->name}")
            ->line("- Demandé par : {$requester->full_name}")
            ->line("- Date : " . $request->created_at->format('d/m/Y à H:i'));

        if ($module->monthly_price > 0) {
            $message->line("- Prix : " . number_format($module->monthly_price, 2, ',', ' ') . " EUR/mois");
        } else {
            $message->line("- Prix : Gratuit");
        }

        if ($request->message) {
            $message->line("")
                ->line("**Message du demandeur :**")
                ->line($request->message);
        }

        $message->action('Gérer les demandes', route('admin.modules.requests'))
            ->line('Vous pouvez approuver ou refuser cette demande.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->moduleRequest;
        $company = $request->company;
        $module = $request->module;

        return [
            'type' => 'module_request_submitted',
            'severity' => 'info',
            'title' => 'Nouvelle demande de module',
            'message' => "{$company->name} demande le module {$module->name}",
            'module_request_id' => $request->id,
            'module_id' => $module->id,
            'module_name' => $module->name,
            'module_code' => $module->code,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'requester_name' => $request->requestedBy->full_name,
            'action_url' => route('admin.modules.requests'),
            'action_text' => 'Voir les demandes',
            'icon' => 'puzzle',
            'color' => 'primary',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'module_request_submitted';
    }
}
