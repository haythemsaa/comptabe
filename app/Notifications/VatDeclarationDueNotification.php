<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VatDeclarationDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $period,
        public string $periodicity,
        public string $dueDate,
        public int $daysUntilDue,
        public ?float $estimatedVatAmount = null,
        public bool $isOverdue = false
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isOverdue) {
            $subject = "🚨 URGENT: Déclaration TVA en retard";
            $severityLabel = "RETARD";
        } elseif ($this->daysUntilDue <= 3) {
            $subject = "⚠️ URGENT: Déclaration TVA à soumettre";
            $severityLabel = "URGENT";
        } else {
            $subject = "📋 Rappel: Déclaration TVA à venir";
            $severityLabel = "RAPPEL";
        }

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("📋 **{$severityLabel} déclaration TVA**: La déclaration TVA pour la période **{$this->period}** ({$this->periodicity}) doit être soumise avant le **{$this->dueDate}**.");

        if ($this->isOverdue) {
            $message->line("🔴 **ATTENTION**: Cette déclaration est EN RETARD de **{$this->daysUntilDue} jour(s)**! Des pénalités peuvent s'appliquer.");
        } else {
            $message->line("⏰ **Échéance dans**: **{$this->daysUntilDue} jour(s)**");
        }

        if ($this->estimatedVatAmount !== null) {
            $amountLabel = $this->estimatedVatAmount >= 0 ? 'TVA à payer estimée' : 'Remboursement TVA estimé';
            $message->line("**{$amountLabel}**: " . number_format(abs($this->estimatedVatAmount), 2, ',', ' ') . " €");
        }

        if ($this->daysUntilDue <= 3 || $this->isOverdue) {
            $message->line("⚡ **Action immédiate requise!**");
        }

        $message->action('Préparer la déclaration', route('vat.declarations.index'))
            ->line('💡 **Fonctionnalités disponibles**:')
            ->line('• Génération automatique des grilles Intervat')
            ->line('• Vérification des données avant soumission')
            ->line('• Export XML pour le portail Intervat')
            ->line('• Archivage automatique des déclarations');

        if (!$this->isOverdue) {
            $message->line('✅ **Soumettez votre déclaration dès maintenant** pour éviter tout risque de pénalité.');
        }

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        if ($this->isOverdue) {
            $severity = 'critical';
        } elseif ($this->daysUntilDue <= 3) {
            $severity = 'warning';
        } else {
            $severity = 'info';
        }

        $statusText = $this->isOverdue
            ? "EN RETARD de {$this->daysUntilDue} jour(s)"
            : "Échéance dans {$this->daysUntilDue} jour(s)";

        return [
            'type' => 'vat_declaration_due',
            'severity' => $severity,
            'title' => 'Déclaration TVA ' . ($this->isOverdue ? 'en retard' : 'à venir'),
            'message' => "{$this->period} ({$this->periodicity}) - {$statusText}",
            'period' => $this->period,
            'periodicity' => $this->periodicity,
            'due_date' => $this->dueDate,
            'days_until_due' => $this->daysUntilDue,
            'is_overdue' => $this->isOverdue,
            'estimated_vat_amount' => $this->estimatedVatAmount,
            'action_url' => route('vat.declarations.index'),
            'action_text' => $this->isOverdue ? 'Soumettre maintenant' : 'Préparer',
            'icon' => 'file-text',
            'color' => $severity === 'critical' ? 'danger' : ($severity === 'warning' ? 'warning' : 'info'),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'vat_declaration_due';
    }
}
