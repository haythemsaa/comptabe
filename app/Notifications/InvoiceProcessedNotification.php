<?php

namespace App\Notifications;

use App\Models\DocumentScan;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Invoice Processed Notification
 *
 * Notifies users when their uploaded invoice document has been processed
 */
class InvoiceProcessedNotification extends Notification
{
    use Queueable;

    public DocumentScan $scan;
    public ?Invoice $invoice;
    public string $status;

    /**
     * Create a new notification instance.
     *
     * @param DocumentScan $scan The document scan that was processed
     * @param Invoice|null $invoice The created invoice (if auto-created)
     * @param string $status Status: 'auto_created', 'requires_validation', 'manual_entry_recommended'
     */
    public function __construct(DocumentScan $scan, ?Invoice $invoice, string $status)
    {
        $this->scan = $scan;
        $this->invoice = $invoice;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $confidence = round(($this->scan->overall_confidence ?? 0) * 100);

        return match ($this->status) {
            'auto_created' => $this->mailAutoCreated($confidence),
            'requires_validation' => $this->mailRequiresValidation($confidence),
            'manual_entry_recommended' => $this->mailManualEntry($confidence),
            default => $this->mailGeneric(),
        };
    }

    /**
     * Email for auto-created invoice
     */
    protected function mailAutoCreated(int $confidence): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Facture créée automatiquement - ' . $this->scan->original_filename)
            ->greeting('Bonne nouvelle !')
            ->line("Votre document **{$this->scan->original_filename}** a été traité avec succès.")
            ->line("**Confiance IA : {$confidence}%** - La facture a été créée automatiquement.")
            ->lineIf($this->invoice, "**Numéro de facture** : {$this->invoice?->invoice_number}")
            ->lineIf($this->invoice, "**Montant total** : " . number_format($this->invoice?->total_incl_vat ?? 0, 2) . " €")
            ->action('Voir la facture', route('invoices.show', $this->invoice))
            ->line('Vérifiez les données extraites et apportez des corrections si nécessaire.')
            ->success();
    }

    /**
     * Email for validation required
     */
    protected function mailRequiresValidation(int $confidence): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Validation requise - ' . $this->scan->original_filename)
            ->greeting('Action requise')
            ->line("Votre document **{$this->scan->original_filename}** a été scanné.")
            ->line("**Confiance IA : {$confidence}%** - Veuillez vérifier les données avant de créer la facture.")
            ->line('Certaines informations nécessitent votre validation avant la création automatique.')
            ->action('Valider les données', route('scanner.index') . '?scan=' . $this->scan->id)
            ->line('Conseil : Vérifiez particulièrement le fournisseur, les montants et les dates.')
            ->warning();
    }

    /**
     * Email for manual entry recommended
     */
    protected function mailManualEntry(int $confidence): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Saisie manuelle recommandée - ' . $this->scan->original_filename)
            ->greeting('Attention')
            ->line("Votre document **{$this->scan->original_filename}** a été scanné mais la qualité de l'extraction est faible.")
            ->line("**Confiance IA : {$confidence}%** - Nous recommandons une saisie manuelle.")
            ->line('Le document pourrait être de mauvaise qualité, illisible ou dans un format non standard.')
            ->action('Créer manuellement', route('invoices.create'))
            ->line("Vous pouvez également réessayer avec une meilleure qualité d'image.")
            ->line('**Conseils pour améliorer la détection :**')
            ->line('• Assurez-vous que le document est bien éclairé et net')
            ->line('• Évitez les plis et les ombres')
            ->line('• Préférez un PDF natif plutôt qu\'un scan')
            ->error();
    }

    /**
     * Generic email fallback
     */
    protected function mailGeneric(): MailMessage
    {
        return (new MailMessage)
            ->subject('Document traité - ' . $this->scan->original_filename)
            ->greeting('Notification')
            ->line("Votre document **{$this->scan->original_filename}** a été traité.")
            ->action('Voir les résultats', route('scanner.index'))
            ->line('Merci d\'utiliser notre système de scan intelligent !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'scan_id' => $this->scan->id,
            'invoice_id' => $this->invoice?->id,
            'status' => $this->status,
            'filename' => $this->scan->original_filename,
            'confidence' => $this->scan->overall_confidence,
            'message' => $this->getArrayMessage(),
            'icon' => $this->getIcon(),
            'action_url' => $this->getActionUrl(),
        ];
    }

    /**
     * Get message for database notification
     */
    protected function getArrayMessage(): string
    {
        $confidence = round(($this->scan->overall_confidence ?? 0) * 100);

        return match ($this->status) {
            'auto_created' => "Facture créée automatiquement ({$confidence}% confiance) : {$this->invoice?->invoice_number}",
            'requires_validation' => "Document scanné ({$confidence}% confiance) - Validation requise",
            'manual_entry_recommended' => "Qualité d'extraction faible ({$confidence}%) - Saisie manuelle recommandée",
            default => "Document {$this->scan->original_filename} traité",
        };
    }

    /**
     * Get icon for notification
     */
    protected function getIcon(): string
    {
        return match ($this->status) {
            'auto_created' => 'check-circle',
            'requires_validation' => 'exclamation-circle',
            'manual_entry_recommended' => 'x-circle',
            default => 'information-circle',
        };
    }

    /**
     * Get action URL
     */
    protected function getActionUrl(): string
    {
        if ($this->invoice) {
            return route('invoices.show', $this->invoice);
        }

        return route('scanner.index') . '?scan=' . $this->scan->id;
    }
}
