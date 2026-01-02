<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankTransaction extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'bank_statement_id',
        'bank_account_id',
        'company_id',
        'sequence_number',
        'transaction_date',
        'value_date',
        'date',
        'amount',
        'currency',
        'counterparty_name',
        'counterparty_account',
        'counterparty_bic',
        'counterparty_iban',
        'communication',
        'structured_communication',
        'transaction_code',
        'bank_reference',
        'reconciliation_status',
        'matched_invoice_id',
        'matched_partner_id',
        'journal_entry_id',
        'is_reconciled',
        'reconciled_at',
        'reconciled_by',
        'invoice_id',
        'match_confidence',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'value_date' => 'date',
            'date' => 'date',
            'amount' => 'decimal:2',
            'match_confidence' => 'decimal:4',
            'sequence_number' => 'integer',
            'is_reconciled' => 'boolean',
            'reconciled_at' => 'datetime',
        ];
    }

    /**
     * Status colors.
     */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'matched' => 'success',
        'partial' => 'primary',
        'manual' => 'secondary',
        'ignored' => 'danger',
    ];

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->reconciliation_status] ?? 'secondary';
    }

    /**
     * Check if transaction is credit (incoming).
     */
    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if transaction is debit (outgoing).
     */
    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Bank statement.
     */
    public function statement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class, 'bank_statement_id');
    }

    /**
     * Bank account.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Matched invoice.
     */
    public function matchedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'matched_invoice_id');
    }

    /**
     * Invoice (alias for matchedInvoice).
     */
    public function invoice(): BelongsTo
    {
        return $this->matchedInvoice();
    }

    /**
     * Matched partner.
     */
    public function matchedPartner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'matched_partner_id');
    }

    /**
     * Partner (alias for matchedPartner).
     */
    public function partner(): BelongsTo
    {
        return $this->matchedPartner();
    }

    /**
     * Journal entry.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Company (for performance, instead of going through bank_account).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Reconciled invoice (new field from reconciliation migration).
     */
    public function reconciledInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * User who performed reconciliation.
     */
    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Payment created from this transaction (inverse relationship).
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'bank_transaction_id');
    }

    /**
     * Find matching invoices.
     */
    public function findMatchingInvoices()
    {
        $query = Invoice::unpaid();

        // Match by structured communication
        if ($this->structured_communication) {
            $query->where('structured_communication', $this->structured_communication);
        }

        // Match by amount
        $query->where('amount_due', abs($this->amount));

        return $query->get();
    }

    /**
     * Scope for pending reconciliation.
     */
    public function scopePending($query)
    {
        return $query->where('reconciliation_status', 'pending');
    }

    /**
     * Scope for reconciled transactions.
     */
    public function scopeReconciled($query)
    {
        return $query->whereIn('reconciliation_status', ['matched', 'manual']);
    }
}
