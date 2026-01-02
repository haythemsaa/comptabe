<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SystemAlertNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $severity;
    protected ?string $actionUrl;

    public function __construct(string $title, string $message, string $severity = 'warning', ?string $actionUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->severity = $severity;
        $this->actionUrl = $actionUrl;
    }

    public function via($notifiable): array
    {
        if (!($notifiable->notification_preferences['system_alerts'] ?? true)) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'icon' => 'alert',
            'severity' => $this->severity,
        ];

        if ($this->actionUrl) {
            $data['action_url'] = $this->actionUrl;
            $data['action_text'] = 'Voir les détails';
        }

        return $data;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'message' => $this->message,
            'icon' => 'alert',
            'severity' => $this->severity,
        ]);
    }
}
