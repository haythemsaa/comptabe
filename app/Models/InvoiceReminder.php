<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReminder extends Model
{
    use HasFactory, HasUuid, HasTenant;

    protected $fillable = [
        'invoice_id',
        'company_id',
        'reminder_type',
        'reminder_number',
        'sent_date',
        'invoice_due_date',
        'days_overdue',
        'recipient_email',
        'email_subject',
        'email_body',
        'email_sent',
        'email_sent_at',
        'email_error',
        'invoice_amount',
        'amount_paid',
        'amount_due',
        'late_fee_applied',
        'interest_applied',
        'opened',
        'opened_at',
        'payment_received',
        'payment_received_at',
        'metadata',
    ];

    protected $casts = [
        'sent_date' => 'date',
        'invoice_due_date' => 'date',
        'days_overdue' => 'integer',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'invoice_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'late_fee_applied' => 'decimal:2',
        'interest_applied' => 'decimal:2',
        'opened' => 'boolean',
        'opened_at' => 'datetime',
        'payment_received' => 'boolean',
        'payment_received_at' => 'datetime',
        'reminder_number' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scopes
     */

    public function scopeSent($query)
    {
        return $query->where('email_sent', true);
    }

    public function scopePending($query)
    {
        return $query->where('email_sent', false);
    }

    public function scopeOpened($query)
    {
        return $query->where('opened', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Methods
     */

    /**
     * Mark email as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now(),
        ]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'email_sent' => false,
            'email_error' => $error,
        ]);
    }

    /**
     * Mark email as opened
     */
    public function markAsOpened(): void
    {
        if (!$this->opened) {
            $this->update([
                'opened' => true,
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Mark payment as received
     */
    public function markPaymentReceived(): void
    {
        $this->update([
            'payment_received' => true,
            'payment_received_at' => now(),
        ]);
    }

    /**
     * Get reminder type label in French
     */
    public function getReminderTypeLabelAttribute(): string
    {
        return match ($this->reminder_type) {
            'before_due' => 'Rappel avant échéance',
            'on_due_date' => 'Rappel échéance',
            'first_overdue' => '1ère relance',
            'second_overdue' => '2ème relance',
            'final_reminder' => 'Relance finale',
            'manual' => 'Relance manuelle',
            default => $this->reminder_type,
        };
    }

    /**
     * Get status color for badge
     */
    public function getStatusColorAttribute(): string
    {
        if (!$this->email_sent) {
            return 'warning';
        }

        if ($this->payment_received) {
            return 'success';
        }

        if ($this->opened) {
            return 'info';
        }

        return 'secondary';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->email_sent) {
            return 'En attente';
        }

        if ($this->payment_received) {
            return 'Payée';
        }

        if ($this->opened) {
            return 'Ouvert';
        }

        return 'Envoyé';
    }

    /**
     * Process email template with variables
     */
    public static function processTemplate(string $template, Invoice $invoice, array $additional = []): string
    {
        $variables = [
            '{invoice_number}' => $invoice->invoice_number,
            '{total}' => number_format($invoice->total_incl_vat, 2, ',', ' '),
            '{due_date}' => $invoice->due_date->format('d/m/Y'),
            '{invoice_date}' => $invoice->invoice_date->format('d/m/Y'),
            '{partner_name}' => $invoice->partner->name ?? '',
            '{company_name}' => $invoice->company->name ?? '',
            '{company_iban}' => $invoice->company->iban ?? '',
            '{structured_communication}' => $invoice->structured_communication ?? '',
            '{days_overdue}' => $invoice->due_date->diffInDays(now(), false),
            '{amount_due}' => number_format($invoice->amount_due, 2, ',', ' '),
        ];

        // Merge additional variables
        $variables = array_merge($variables, $additional);

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Create reminder for an invoice
     */
    public static function createForInvoice(
        Invoice $invoice,
        string $type,
        int $reminderNumber,
        string $subject,
        string $body,
        float $lateFee = 0,
        float $interest = 0
    ): self {
        return self::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'reminder_type' => $type,
            'reminder_number' => $reminderNumber,
            'sent_date' => now()->toDateString(),
            'invoice_due_date' => $invoice->due_date,
            'days_overdue' => max(0, $invoice->due_date->diffInDays(now(), false)),
            'recipient_email' => $invoice->partner->email ?? '',
            'email_subject' => $subject,
            'email_body' => $body,
            'email_sent' => false,
            'invoice_amount' => $invoice->total_incl_vat,
            'amount_paid' => $invoice->total_incl_vat - $invoice->amount_due,
            'amount_due' => $invoice->amount_due,
            'late_fee_applied' => $lateFee,
            'interest_applied' => $interest,
        ]);
    }
}
