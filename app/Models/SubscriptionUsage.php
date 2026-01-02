<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{
    use HasUuid;

    protected $table = 'subscription_usage';

    protected $fillable = [
        'company_id',
        'period',
        'invoices_created',
        'clients_count',
        'products_count',
        'users_count',
        'storage_used_mb',
    ];

    protected $casts = [
        'invoices_created' => 'integer',
        'clients_count' => 'integer',
        'products_count' => 'integer',
        'users_count' => 'integer',
        'storage_used_mb' => 'integer',
    ];

    /**
     * The company this usage belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get or create usage record for current month
     */
    public static function getCurrentUsage(Company $company): self
    {
        $period = now()->format('Y-m');

        return self::firstOrCreate(
            [
                'company_id' => $company->id,
                'period' => $period,
            ],
            [
                'invoices_created' => 0,
                'clients_count' => $company->partners()->customers()->count(),
                'products_count' => $company->products()->count(),
                'users_count' => $company->users()->count(),
                'storage_used_mb' => 0,
            ]
        );
    }

    /**
     * Increment invoice count
     */
    public function incrementInvoices(): self
    {
        $this->increment('invoices_created');
        return $this;
    }

    /**
     * Update counts
     */
    public function updateCounts(): self
    {
        $this->update([
            'clients_count' => $this->company->partners()->customers()->count(),
            'products_count' => $this->company->products()->count(),
            'users_count' => $this->company->users()->count(),
        ]);

        return $this;
    }

    /**
     * Update storage usage
     */
    public function updateStorageUsage(int $mb): self
    {
        $this->update(['storage_used_mb' => $mb]);
        return $this;
    }

    /**
     * Check if limit is reached
     */
    public function isLimitReached(string $type, int $limit): bool
    {
        if ($limit === -1) return false; // Unlimited

        return match ($type) {
            'invoices' => $this->invoices_created >= $limit,
            'clients' => $this->clients_count >= $limit,
            'products' => $this->products_count >= $limit,
            'users' => $this->users_count >= $limit,
            'storage' => $this->storage_used_mb >= $limit,
            default => false,
        };
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage(string $type, int $limit): int
    {
        if ($limit === -1 || $limit === 0) return 0;

        $current = match ($type) {
            'invoices' => $this->invoices_created,
            'clients' => $this->clients_count,
            'products' => $this->products_count,
            'users' => $this->users_count,
            'storage' => $this->storage_used_mb,
            default => 0,
        };

        return min(100, (int) round(($current / $limit) * 100));
    }
}
