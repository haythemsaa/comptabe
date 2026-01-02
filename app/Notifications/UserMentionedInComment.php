<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMentionedInComment extends Notification implements ShouldQueue
{
    use Queueable;

    public Comment $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

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
        $commentableType = class_basename($this->comment->commentable_type);
        $url = $this->getUrl();

        return (new MailMessage)
            ->subject('Vous avez été mentionné dans un commentaire')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->comment->user->name . ' vous a mentionné dans un commentaire sur ' . $commentableType . ':')
            ->line('"' . $this->comment->content . '"')
            ->action('Voir le commentaire', $url)
            ->line('Merci d\'utiliser notre application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'user_name' => $this->comment->user->name,
            'content' => $this->comment->content,
            'commentable_type' => class_basename($this->comment->commentable_type),
            'commentable_id' => $this->comment->commentable_id,
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Get URL to the commented resource.
     */
    protected function getUrl(): string
    {
        $commentable = $this->comment->commentable;

        return match (get_class($commentable)) {
            \App\Models\Invoice::class => route('invoices.show', $commentable->id),
            \App\Models\ClientDocument::class => route('client-portal.documents.show', $commentable->id),
            default => route('dashboard'),
        };
    }
}
