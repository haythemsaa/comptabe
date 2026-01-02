<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EReportingSubmission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'submission_id',
        'type',
        'status',
        'government_reference',
        'request_payload',
        'response_payload',
        'error_message',
        'submitted_at',
        'accepted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the company that owns this submission.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the invoice for this submission.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Scope for pending submissions.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'submitted']);
    }

    /**
     * Scope for accepted submissions.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for rejected submissions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for failed submissions.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['rejected', 'error']);
    }

    /**
     * Scope for sales (outgoing invoices).
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sales');
    }

    /**
     * Scope for purchases (incoming invoices).
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchases');
    }

    /**
     * Check if submission is pending.
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'submitted']);
    }

    /**
     * Check if submission was accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if submission failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['rejected', 'error']);
    }

    /**
     * Get decoded request payload.
     */
    public function getRequestDataAttribute(): ?array
    {
        if (!$this->request_payload) return null;
        return json_decode($this->request_payload, true);
    }

    /**
     * Get decoded response payload.
     */
    public function getResponseDataAttribute(): ?array
    {
        if (!$this->response_payload) return null;
        return json_decode($this->response_payload, true);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'submitted' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            'error' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'submitted' => 'Soumis',
            'accepted' => 'Accepté',
            'rejected' => 'Rejeté',
            'error' => 'Erreur',
            default => 'Inconnu',
        };
    }
}
