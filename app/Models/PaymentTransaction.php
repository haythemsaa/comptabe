<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'subscription_id',
        'subscription_invoice_id',
        'provider',
        'provider_payment_id',
        'provider_refund_id',
        'type',
        'status',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'payment_method',
        'payment_method_id',
        'description',
        'metadata',
        'error_message',
        'failure_reason',
        'paid_at',
        'failed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELED = 'canceled';

    /**
     * Type constants
     */
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_CHARGEBACK = 'chargeback';

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Subscription relationship
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Subscription invoice relationship
     */
    public function subscriptionInvoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class);
    }

    /**
     * Payment method relationship
     */
    public function paymentMethodRelation(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELED]);
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $reason = null, string $message = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'error_message' => $message,
        ]);
    }

    /**
     * Mark transaction as refunded
     */
    public function markAsRefunded(string $refundId = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'provider_refund_id' => $refundId ?? $this->provider_refund_id,
        ]);
    }

    /**
     * Scopes
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELED]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PAID => 'Payé',
            self::STATUS_FAILED => 'Échoué',
            self::STATUS_REFUNDED => 'Remboursé',
            self::STATUS_EXPIRED => 'Expiré',
            self::STATUS_CANCELED => 'Annulé',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color class
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELED => 'danger',
            self::STATUS_REFUNDED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Static method: Log a new payment
     */
    public static function logPayment(array $data): self
    {
        return self::create(array_merge([
            'type' => self::TYPE_PAYMENT,
            'status' => self::STATUS_PENDING,
            'currency' => 'EUR',
        ], $data));
    }

    /**
     * Static method: Log a refund
     */
    public static function logRefund(array $data): self
    {
        return self::create(array_merge([
            'type' => self::TYPE_REFUND,
            'status' => self::STATUS_REFUNDED,
            'currency' => 'EUR',
            'refunded_at' => now(),
        ], $data));
    }
}
