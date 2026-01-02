<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxPayment extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'tax_type',
        'period_label',
        'year',
        'quarter',
        'month',
        'taxable_base',
        'tax_rate',
        'tax_amount',
        'advance_payments',
        'amount_due',
        'amount_paid',
        'penalties',
        'interests',
        'due_date',
        'payment_date',
        'declaration_date',
        'status',
        'reference_number',
        'structured_communication',
        'payment_transaction_id',
        'journal_entry_id',
        'declaration_file_path',
        'payment_proof_path',
        'notes',
        'calculation_details',
        'metadata',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'taxable_base' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'advance_payments' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'penalties' => 'decimal:2',
        'interests' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'declaration_date' => 'date',
        'validated_at' => 'datetime',
        'calculation_details' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Tax type labels (Belgian).
     */
    public const TAX_TYPE_LABELS = [
        'isoc' => 'Impôt des Sociétés (ISOC)',
        'ipp' => 'Impôt des Personnes Physiques (IPP)',
        'professional_tax' => 'Précompte Professionnel',
        'vat' => 'TVA',
        'withholding_tax' => 'Précompte Mobilier',
        'registration_tax' => 'Droits d\'Enregistrement',
        'property_tax' => 'Précompte Immobilier',
        'vehicle_tax' => 'Taxe de Circulation',
        'other' => 'Autre',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'calculated' => 'Calculé',
        'declared' => 'Déclaré',
        'pending_payment' => 'En attente de paiement',
        'partially_paid' => 'Partiellement payé',
        'paid' => 'Payé',
        'overdue' => 'En retard',
        'contested' => 'Contesté',
    ];

    /**
     * Relationships.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class, 'payment_transaction_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scopes.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('tax_type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['pending_payment', 'partially_paid'])
                    ->where('due_date', '<', now());
            });
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending_payment', 'partially_paid']);
    }

    /**
     * Accessors.
     */
    public function getTaxTypeLabelAttribute(): string
    {
        return self::TAX_TYPE_LABELS[$this->tax_type] ?? $this->tax_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->amount_due - $this->amount_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now();
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Methods.
     */

    /**
     * Mark as paid.
     */
    public function markAsPaid(BankTransaction $transaction = null): bool
    {
        $this->amount_paid = $this->amount_due;
        $this->payment_date = now();
        $this->status = 'paid';

        if ($transaction) {
            $this->payment_transaction_id = $transaction->id;
        }

        return $this->save();
    }

    /**
     * Record a partial payment.
     */
    public function recordPayment(float $amount, BankTransaction $transaction = null): bool
    {
        $this->amount_paid += $amount;
        $this->payment_date = now();

        if ($this->amount_paid >= $this->amount_due) {
            $this->status = 'paid';
        } else {
            $this->status = 'partially_paid';
        }

        if ($transaction) {
            $this->payment_transaction_id = $transaction->id;
        }

        return $this->save();
    }

    /**
     * Check if payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Calculate overdue days.
     */
    public function getOverdueDays(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Generate structured communication for payment.
     */
    public function generateStructuredCommunication(): string
    {
        // Format: +++XXX/XXXX/XXXXX+++
        $ref = str_pad(substr($this->id, 0, 12), 12, '0');
        $formatted = substr($ref, 0, 3) . '/' . substr($ref, 3, 4) . '/' . substr($ref, 7, 5);

        // Calculate check digit (modulo 97)
        $number = (int)str_replace('/', '', $formatted);
        $checkDigit = 97 - ($number % 97);
        $checkDigit = str_pad($checkDigit, 2, '0', STR_PAD_LEFT);

        return '+++' . $formatted . $checkDigit . '+++';
    }

    /**
     * Update status based on due date and payment.
     */
    public function updateStatus(): void
    {
        if ($this->amount_paid >= $this->amount_due) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date < now()) {
            $this->status = 'overdue';
        } elseif ($this->declaration_date) {
            $this->status = 'pending_payment';
        }

        $this->save();
    }
}
