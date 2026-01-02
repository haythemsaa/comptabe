<?php

namespace App\Mail;

use App\Models\InvitationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public InvitationToken $invitation
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $inviterName = $this->invitation->invitedBy?->name ?? 'ComptaBE';

        return new Envelope(
            subject: "Invitation à rejoindre ComptaBE - {$inviterName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invitation',
            with: [
                'invitation' => $this->invitation,
                'inviterName' => $this->invitation->invitedBy?->name ?? 'ComptaBE',
                'companyName' => $this->invitation->company?->name ?? 'ComptaBE',
                'acceptUrl' => $this->invitation->url,
                'expiresAt' => $this->invitation->expires_at->format('d/m/Y à H:i'),
                'role' => match($this->invitation->role) {
                    'admin' => 'Administrateur',
                    'accountant' => 'Comptable',
                    default => 'Utilisateur',
                },
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
