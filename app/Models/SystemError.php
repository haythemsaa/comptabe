<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemError extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'severity',
        'type',
        'message',
        'exception',
        'file',
        'line',
        'trace',
        'url',
        'method',
        'ip',
        'user_agent',
        'context',
        'request_data',
        'occurrences',
        'last_occurred_at',
        'resolved',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'context' => 'array',
        'request_data' => 'array',
        'resolved' => 'boolean',
        'last_occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark this error as resolved
     */
    public function resolve(?string $note = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_note' => $note,
        ]);
    }

    /**
     * Increment occurrence count
     */
    public function incrementOccurrence(): void
    {
        $this->increment('occurrences');
        $this->update(['last_occurred_at' => now()]);
    }

    /**
     * Get severity badge color
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'error' => 'orange',
            'warning' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Scope for unresolved errors
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope for critical errors
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}
