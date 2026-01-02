<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Quote extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'quote_number',
        'quote_date',
        'valid_until',
        'status',
        'total_excl_vat',
        'total_vat',
        'total_incl_vat',
        'currency',
        'discount_percent',
        'discount_amount',
        'reference',
        'notes',
        'terms',
        'converted_invoice_id',
        'converted_at',
        'created_by',
        'sent_at',
        'accepted_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'valid_until' => 'date',
            'total_excl_vat' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_incl_vat' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'converted_at' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Status colors for UI.
     */
    public const STATUS_COLORS = [
        'draft' => 'secondary',
        'sent' => 'primary',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired' => 'warning',
        'converted' => 'success',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'sent' => 'Envoyé',
        'accepted' => 'Accepté',
        'rejected' => 'Refusé',
        'expired' => 'Expiré',
        'converted' => 'Converti',
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
     * Check if quote is editable.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    /**
     * Check if quote can be converted.
     */
    public function canConvert(): bool
    {
        return in_array($this->status, ['sent', 'accepted'])
            && !$this->converted_invoice_id;
    }

    /**
     * Check if quote is expired.
     */
    public function isExpired(): bool
    {
        return $this->valid_until
            && $this->valid_until->isPast()
            && !in_array($this->status, ['converted', 'rejected']);
    }

    /**
     * Get days until expiration.
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->valid_until) return null;
        return now()->startOfDay()->diffInDays($this->valid_until, false);
    }

    /**
     * Alias for days_until_expiration (for backward compatibility).
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        return $this->days_until_expiration;
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Quote lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('line_number');
    }

    /**
     * Converted invoice.
     */
    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    /**
     * Creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate totals from lines.
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->lines->sum('line_total');

        // Apply global discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = round($subtotal * $this->discount_percent / 100, 2);
        }

        $this->total_excl_vat = $subtotal - $this->discount_amount;
        $this->total_vat = $this->lines->sum('vat_amount');

        // Adjust VAT if global discount applied
        if ($this->discount_amount > 0) {
            $discountRatio = $this->total_excl_vat / $subtotal;
            $this->total_vat = round($this->total_vat * $discountRatio, 2);
        }

        $this->total_incl_vat = $this->total_excl_vat + $this->total_vat;
    }

    /**
     * Convert quote to invoice.
     */
    public function convertToInvoice(): Invoice
    {
        if (!$this->canConvert()) {
            throw new \Exception('Ce devis ne peut pas être converti en facture.');
        }

        return DB::transaction(function () {
            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $this->company_id,
                'partner_id' => $this->partner_id,
                'type' => 'out',
                'document_type' => 'invoice',
                'status' => 'draft',
                'invoice_number' => Invoice::generateNextNumber($this->company_id, 'out'),
                'invoice_date' => now(),
                'due_date' => now()->addDays($this->partner->payment_terms_days ?? 30),
                'reference' => $this->reference,
                'structured_communication' => Invoice::generateStructuredCommunication(),
                'notes' => $this->notes,
                'currency' => $this->currency,
                'created_by' => auth()->id(),
            ]);

            // Copy lines
            foreach ($this->lines as $line) {
                $invoice->lines()->create([
                    'line_number' => $line->line_number,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit_price' => $line->unit_price,
                    'vat_rate' => $line->vat_rate,
                    'vat_category' => $line->vat_category,
                    'discount_percent' => $line->discount_percent,
                    'account_id' => $line->account_id,
                ]);
            }

            // Calculate invoice totals
            $invoice->calculateTotals();
            $invoice->save();

            // Mark quote as converted
            $this->update([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id,
                'converted_at' => now(),
            ]);

            return $invoice;
        });
    }

    /**
     * Generate next quote number.
     */
    public static function generateNextNumber(string $companyId): string
    {
        $year = now()->year;
        $prefix = 'DEV';

        $lastNumber = static::where('company_id', $companyId)
            ->where('quote_number', 'like', $prefix . $year . '-%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(quote_number, '-', -1) AS UNSIGNED) DESC")
            ->value('quote_number');

        if ($lastNumber) {
            $number = (int)explode('-', $lastNumber)[1] + 1;
        } else {
            $number = 1;
        }

        return $prefix . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for drafts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for sent quotes.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for accepted quotes.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for pending quotes (not yet converted or rejected).
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'accepted']);
    }

    /**
     * Scope for expired quotes.
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now())
            ->whereNotIn('status', ['converted', 'rejected', 'expired']);
    }
}
