<?php

namespace App\Models;

use App\Events\PaymentCreated;
use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'company_id',
        'bank_account_id',
        'bank_transaction_id',
        'journal_entry_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Payment methods.
     */
    const PAYMENT_METHODS = [
        'bank_transfer' => 'Virement bancaire',
        'cash' => 'Espèces',
        'check' => 'Chèque',
        'credit_card' => 'Carte bancaire',
        'direct_debit' => 'Prélèvement',
        'other' => 'Autre',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Payment $payment) {
            // Dispatcher l'event pour générer l'écriture comptable
            PaymentCreated::dispatch($payment);
        });
    }

    /**
     * Invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Bank account.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Bank transaction (if from reconciliation).
     */
    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    /**
     * Journal entry (accounting).
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }
}
