<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasUuid;

    protected $fillable = [
        'subscription_id',
        'company_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'total',
        'currency',
        'status',
        'invoice_date',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_PENDING => 'En attente',
        self::STATUS_PAID => 'Payée',
        self::STATUS_FAILED => 'Échec',
        self::STATUS_REFUNDED => 'Remboursée',
    ];

    public const STATUS_COLORS = [
        self::STATUS_DRAFT => 'secondary',
        self::STATUS_PENDING => 'warning',
        self::STATUS_PAID => 'success',
        self::STATUS_FAILED => 'danger',
        self::STATUS_REFUNDED => 'info',
    ];

    public const PAYMENT_METHODS = [
        'bank_transfer' => 'Virement bancaire',
        'card' => 'Carte bancaire',
        'cash' => 'Espèces',
        'other' => 'Autre',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "SUB-{$year}-";

        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * The subscription this invoice belongs to
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * The company this invoice belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(string $method = null, string $reference = null): self
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $method ?? $this->payment_method,
            'payment_reference' => $reference ?? $this->payment_reference,
        ]);

        // Activate subscription if this was pending payment
        if ($this->subscription && $this->subscription->status === Subscription::STATUS_PAST_DUE) {
            $this->subscription->renew();
        }

        return $this;
    }

    /**
     * Create invoice for subscription
     */
    public static function createForSubscription(Subscription $subscription): self
    {
        $taxRate = 21; // TVA belge
        $amount = $subscription->amount;
        $tax = $amount * ($taxRate / 100);
        $total = $amount + $tax;

        return self::create([
            'subscription_id' => $subscription->id,
            'company_id' => $subscription->company_id,
            'amount' => $amount,
            'tax_amount' => $tax,
            'total' => $total,
            'currency' => 'EUR',
            'status' => self::STATUS_PENDING,
            'invoice_date' => now(),
            'due_date' => now()->addDays(15),
        ]);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_date', '<', now());
    }
}
