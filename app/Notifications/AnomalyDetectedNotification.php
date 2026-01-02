<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnomalyDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $anomalyType,
        public string $description,
        public array $details = [],
        public string $severity = 'medium', // 'low', 'medium', 'high', 'critical'
        public ?string $entityType = null,
        public ?string $entityId = null,
        public ?array $suggestedActions = null
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Critical anomalies should also send SMS if configured
        $channels = ['mail', 'database'];

        // You could add SMS channel for critical anomalies
        // if ($this->severity === 'critical') {
        //     $channels[] = 'sms';
        // }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severityLabel = match($this->severity) {
            'critical' => '🔴 CRITIQUE',
            'high' => '🟠 HAUTE',
            'medium' => '🟡 MOYENNE',
            'low' => '🔵 FAIBLE',
            default => '⚪ INFO',
        };

        $anomalyTitle = match($this->anomalyType) {
            'duplicate_transaction' => 'Transaction en double détectée',
            'unusual_amount' => 'Montant inhabituel',
            'budget_exceeded' => 'Budget dépassé',
            'vat_discrepancy' => 'Incohérence TVA',
            'payment_delay' => 'Retard de paiement significatif',
            'negative_balance' => 'Solde négatif',
            'missing_document' => 'Document manquant',
            'data_inconsistency' => 'Incohérence de données',
            'fraud_risk' => 'Risque de fraude potentiel',
            default => 'Anomalie détectée',
        };

        $message = (new MailMessage)
            ->subject("🚨 Anomalie détectée - {$anomalyTitle}")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("**Notre système IA a détecté une anomalie nécessitant votre attention.**")
            ->line("")
            ->line("**Type d'anomalie :** {$anomalyTitle}")
            ->line("**Sévérité :** {$severityLabel}")
            ->line("**Description :** {$this->description}");

        // Add detailed information
        if (!empty($this->details)) {
            $message->line("")
                ->line("**Détails :**");

            foreach ($this->details as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                }
                $label = ucfirst(str_replace('_', ' ', $key));
                $message->line("- **{$label}** : {$value}");
            }
        }

        // Suggested actions
        if (!empty($this->suggestedActions)) {
            $message->line("")
                ->line("**Actions recommandées :**");

            foreach ($this->suggestedActions as $action) {
                $message->line("✓ {$action}");
            }
        }

        // Add action button based on entity type
        if ($this->entityType && $this->entityId) {
            $url = $this->getEntityUrl();
            if ($url) {
                $message->action('Voir le détail', $url);
            }
        }

        // Severity-specific messages
        if ($this->severity === 'critical') {
            $message->line("")
                ->line("⚠️ **ATTENTION** : Cette anomalie nécessite une action immédiate !")
                ->line("Veuillez vérifier et corriger dès que possible.");
        } elseif ($this->severity === 'high') {
            $message->line("")
                ->line("⚠️ Cette anomalie nécessite votre attention dans les plus brefs délais.");
        }

        $message->line("")
            ->line("Cette détection automatique vise à protéger l'intégrité de vos données comptables.");

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'anomaly_detected',
            'severity' => $this->severity,
            'anomaly_type' => $this->anomalyType,
            'title' => $this->getAnomalyTitle(),
            'message' => $this->description,
            'details' => $this->details,
            'suggested_actions' => $this->suggestedActions,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'action_url' => $this->getEntityUrl(),
            'action_text' => 'Voir le détail',
            'icon' => $this->getSeverityIcon(),
            'color' => $this->getSeverityColor(),
            'requires_action' => in_array($this->severity, ['critical', 'high']),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'anomaly_detected';
    }

    /**
     * Get the anomaly title.
     */
    protected function getAnomalyTitle(): string
    {
        return match($this->anomalyType) {
            'duplicate_transaction' => 'Transaction en double',
            'unusual_amount' => 'Montant inhabituel',
            'budget_exceeded' => 'Budget dépassé',
            'vat_discrepancy' => 'Incohérence TVA',
            'payment_delay' => 'Retard de paiement',
            'negative_balance' => 'Solde négatif',
            'missing_document' => 'Document manquant',
            'data_inconsistency' => 'Incohérence de données',
            'fraud_risk' => 'Risque de fraude',
            default => 'Anomalie détectée',
        };
    }

    /**
     * Get the URL for the related entity.
     */
    protected function getEntityUrl(): ?string
    {
        if (!$this->entityType || !$this->entityId) {
            return null;
        }

        return match($this->entityType) {
            'invoice' => route('invoices.show', $this->entityId),
            'expense' => route('expenses.show', $this->entityId),
            'bank_transaction' => route('bank-accounts.transactions', ['transaction' => $this->entityId]),
            'journal_entry' => route('journal-entries.show', $this->entityId),
            'partner' => route('partners.show', $this->entityId),
            default => route('dashboard'),
        };
    }

    /**
     * Get the icon based on severity.
     */
    protected function getSeverityIcon(): string
    {
        return match($this->severity) {
            'critical' => 'alert-octagon',
            'high' => 'alert-triangle',
            'medium' => 'alert-circle',
            'low' => 'info',
            default => 'bell',
        };
    }

    /**
     * Get the color based on severity.
     */
    protected function getSeverityColor(): string
    {
        return match($this->severity) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'warning',
            'low' => 'info',
            default => 'secondary',
        };
    }
}
