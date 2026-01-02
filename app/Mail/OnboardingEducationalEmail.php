<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OnboardingEducationalEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailType; // 'day_2', 'day_5', 'day_7'

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        string $emailType
    ) {
        $this->emailType = $emailType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'day_2' => 'Vos premières factures avec ComptaBE 📄',
            'day_5' => 'Automatisez votre gestion avec ComptaBE 🚀',
            'day_7' => 'Devenez un pro de la compta avec ComptaBE 🎓',
        ];

        return new Envelope(
            subject: $subjects[$this->emailType] ?? 'Conseils ComptaBE',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $views = [
            'day_2' => 'emails.onboarding.day-2',
            'day_5' => 'emails.onboarding.day-5',
            'day_7' => 'emails.onboarding.day-7',
        ];

        return new Content(
            view: $views[$this->emailType] ?? 'emails.onboarding.day-2',
            with: [
                'userName' => $this->user->name,
                'companyName' => $this->user->currentCompany?->name ?? 'votre entreprise',
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
