<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'reminder_level',
        'reminder_date',
        'due_date',
        'status',
        'send_method',
        'amount_due',
        'late_fee',
        'interest_amount',
        'message',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'amount_due' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'interest_amount' => 'decimal:2',
        ];
    }

    /**
     * Invoice relationship.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get total amount with fees.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount_due + $this->late_fee + $this->interest_amount;
    }

    /**
     * Generate default message based on level.
     */
    public static function getDefaultMessage(int $level, Invoice $invoice): string
    {
        $messages = [
            1 => "Cher client,\n\nNous nous permettons de vous rappeler que la facture {$invoice->invoice_number} d'un montant de " . number_format($invoice->total_incl_vat, 2, ',', ' ') . " EUR reste impayee.\n\nNous vous prions de bien vouloir regulariser cette situation dans les meilleurs delais.\n\nCordialement",
            2 => "Cher client,\n\nMalgre notre premier rappel, nous constatons que la facture {$invoice->invoice_number} reste impayee.\n\nNous vous demandons de proceder au reglement dans les 7 jours.\n\nA defaut, des frais de retard pourront etre appliques.\n\nCordialement",
            3 => "Cher client,\n\nNos precedents rappels concernant la facture {$invoice->invoice_number} etant restes sans effet, nous vous mettons en demeure de proceder au paiement immediat.\n\nA defaut de reglement sous 48 heures, nous nous verrons contraints d'engager des procedures de recouvrement.\n\nCordialement",
        ];

        return $messages[$level] ?? $messages[3];
    }

    /**
     * Calculate interest amount based on Belgian legal rate.
     */
    public function calculateInterest(float $annualRate = 8.0): float
    {
        if (!$this->invoice || !$this->invoice->due_date) {
            return 0;
        }

        $daysOverdue = now()->diffInDays($this->invoice->due_date, false);

        if ($daysOverdue <= 0) {
            return 0;
        }

        $dailyRate = $annualRate / 365 / 100;
        return round($this->amount_due * $dailyRate * $daysOverdue, 2);
    }

    /**
     * Status label accessor.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'sent' => 'Envoye',
            'paid' => 'Paye',
            'cancelled' => 'Annule',
            default => $this->status,
        };
    }

    /**
     * Status color accessor.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'paid' => 'success',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Scope for pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent reminders.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Create reminder for overdue invoice.
     */
    public static function createForInvoice(Invoice $invoice, int $level = null): self
    {
        if (!$level) {
            $lastLevel = static::where('invoice_id', $invoice->id)->max('reminder_level');
            $level = ($lastLevel ?? 0) + 1;
        }

        $reminder = static::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'reminder_level' => $level,
            'reminder_date' => now(),
            'due_date' => now()->addDays($level === 1 ? 7 : ($level === 2 ? 5 : 2)),
            'status' => 'pending',
            'send_method' => $invoice->partner->peppol_capable ? 'peppol' : 'email',
            'amount_due' => $invoice->amount_remaining,
            'late_fee' => $level >= 2 ? 40.00 : 0, // Belgian standard fee
            'message' => static::getDefaultMessage($level, $invoice),
            'created_by' => auth()->id(),
        ]);

        $reminder->interest_amount = $reminder->calculateInterest();
        $reminder->save();

        return $reminder;
    }
}
