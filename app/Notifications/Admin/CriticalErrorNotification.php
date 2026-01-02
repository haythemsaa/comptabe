<?php

namespace App\Notifications\Admin;

use App\Models\SystemError;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CriticalErrorNotification extends Notification
{
    use Queueable;

    protected SystemError $error;

    public function __construct(SystemError $error)
    {
        $this->error = $error;
    }

    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if ($notifiable->notification_preferences['email_notifications'] ?? false) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Erreur Critique',
            'message' => $this->error->message,
            'icon' => 'error',
            'severity' => 'critical',
            'error_id' => $this->error->id,
            'action_url' => route('admin.errors.show', $this->error),
            'action_text' => 'Voir l\'erreur',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Erreur Critique',
            'message' => $this->error->message,
            'icon' => 'error',
            'error_id' => $this->error->id,
        ]);
    }
}
