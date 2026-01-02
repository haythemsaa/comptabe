<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'url',
        'secret',
        'events',
        'headers',
        'timeout',
        'max_retries',
        'is_active',
        'disabled_reason',
        'disabled_at',
        'delivery_count',
        'success_count',
        'failure_count',
        'last_success_at',
        'last_failure_at',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'disabled_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_failure_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->delivery_count === 0) {
            return 0;
        }

        return round(($this->success_count / $this->delivery_count) * 100, 1);
    }

    public function subscribesTo(string $event): bool
    {
        return in_array('*', $this->events) || in_array($event, $this->events);
    }
}
