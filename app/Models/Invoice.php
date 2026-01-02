<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'type',
        'document_type',
        'status',
        'invoice_number',
        'invoice_date',
        'due_date',
        'delivery_date',
        'reference',
        'order_reference',
        'total_excl_vat',
        'total_vat',
        'total_incl_vat',
        'amount_paid',
        'amount_due',
        'currency',
        'exchange_rate',
        'peppol_message_id',
        'peppol_status',
        'peppol_sent_at',
        'peppol_delivered_at',
        'peppol_error',
        'peppol_transmission_id',
        'peppol_received',
        'peppol_received_at',
        'original_file_path',
        'ubl_xml',
        'ubl_file_path',
        'pdf_path',
        'journal_entry_id',
        'is_booked',
        'booked_at',
        'booked_by',
        'structured_communication',
        'payment_reference',
        'payment_method',
        'notes',
        'internal_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'delivery_date' => 'date',
            'total_excl_vat' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_incl_vat' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'peppol_sent_at' => 'datetime',
            'peppol_delivered_at' => 'datetime',
            'peppol_received_at' => 'datetime',
            'peppol_received' => 'boolean',
            'is_booked' => 'boolean',
            'booked_at' => 'datetime',
        ];
    }

    /**
     * Status colors for UI.
     */
    public const STATUS_COLORS = [
        'draft' => 'secondary',
        'validated' => 'primary',
        'sent' => 'warning',
        'received' => 'primary',
        'partial' => 'warning',
        'paid' => 'success',
        'cancelled' => 'danger',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'validated' => 'Validé',
        'sent' => 'Envoyé',
        'received' => 'Reçu',
        'partial' => 'Partiellement payé',
        'paid' => 'Payé',
        'cancelled' => 'Annulé',
    ];

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Check if invoice is a sale.
     */
    public function isSale(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Check if invoice is a purchase.
     */
    public function isPurchase(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if invoice can be edited.
     */
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if invoice can be sent via Peppol.
     */
    public function canSendViaPeppol(): bool
    {
        return $this->type === 'out'
            && in_array($this->status, ['validated', 'sent'])
            && $this->partner?->peppol_capable
            && !$this->peppol_delivered_at;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->amount_due > 0;
    }

    /**
     * Get days until due (negative if overdue).
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) return null;
        return now()->startOfDay()->diffInDays($this->due_date, false);
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Invoice lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('line_number');
    }

    /**
     * Journal entry.
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Peppol transmissions.
     */
    public function peppolTransmissions(): HasMany
    {
        return $this->hasMany(PeppolTransmission::class);
    }

    /**
     * Bank transactions matched to this invoice.
     */
    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'matched_invoice_id');
    }

    /**
     * Comments on this invoice.
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\App\Models\Comment::class, 'commentable');
    }

    /**
     * Calculate totals from lines.
     */
    public function calculateTotals(): void
    {
        $this->total_excl_vat = $this->lines->sum('line_amount');
        $this->total_vat = $this->lines->sum('vat_amount');
        $this->total_incl_vat = $this->total_excl_vat + $this->total_vat;
        $this->amount_due = $this->total_incl_vat - $this->amount_paid;
    }

    /**
     * Get VAT summary grouped by rate.
     */
    public function vatSummary(): array
    {
        return $this->lines
            ->groupBy('vat_rate')
            ->map(fn ($lines) => $lines->sum('vat_amount'))
            ->sortKeys()
            ->toArray();
    }

    /**
     * Get balance due.
     */
    public function getBalanceAttribute(): float
    {
        return $this->total_incl_vat - $this->amount_paid;
    }

    /**
     * Generate structured communication.
     */
    public static function generateStructuredCommunication(): string
    {
        $random = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $modulo = (int)$random % 97;
        $checkDigits = $modulo === 0 ? '97' : str_pad($modulo, 2, '0', STR_PAD_LEFT);
        $full = $random . $checkDigits;

        return '+++' . substr($full, 0, 3) . '/' . substr($full, 3, 4) . '/' . substr($full, 7) . '+++';
    }

    /**
     * Generate next invoice number.
     */
    public static function generateNextNumber(string $companyId, string $type): string
    {
        $year = now()->year;
        $prefix = $type === 'out' ? 'VF' : 'AF';

        $lastNumber = static::where('company_id', $companyId)
            ->where('type', $type)
            ->where('invoice_number', 'like', $prefix . $year . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED) DESC")
            ->value('invoice_number');

        if ($lastNumber) {
            $number = (int)explode('-', $lastNumber)[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for sales invoices.
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for purchase invoices.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('amount_due', '>', 0)
            ->whereNotIn('status', ['draft', 'cancelled']);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->unpaid()
            ->where('due_date', '<', now());
    }
}
