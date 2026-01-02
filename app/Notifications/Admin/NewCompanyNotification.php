<?php

namespace App\Notifications\Admin;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewCompanyNotification extends Notification
{
    use Queueable;

    protected Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function via($notifiable): array
    {
        if (!($notifiable->notification_preferences['new_company'] ?? true)) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nouvelle Entreprise',
            'message' => "Une nouvelle entreprise a été créée: {$this->company->name}",
            'icon' => 'company',
            'severity' => 'info',
            'company_id' => $this->company->id,
            'action_url' => route('admin.companies.show', $this->company),
            'action_text' => 'Voir l\'entreprise',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Nouvelle Entreprise',
            'message' => "Nouvelle entreprise: {$this->company->name}",
            'icon' => 'company',
            'company_id' => $this->company->id,
        ]);
    }
}
