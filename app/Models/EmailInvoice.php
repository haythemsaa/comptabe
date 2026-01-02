<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailInvoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'message_id',
        'from_email',
        'from_name',
        'subject',
        'body_text',
        'body_html',
        'email_date',
        'attachments',
        'status',
        'processing_notes',
        'extracted_data',
        'confidence_score',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'email_date' => 'datetime',
        'processed_at' => 'datetime',
        'attachments' => 'array',
        'extracted_data' => 'array',
        'confidence_score' => 'decimal:2',
    ];

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Helpers
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }

    public function getFirstAttachment(): ?array
    {
        return $this->attachments[0] ?? null;
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsProcessed(Invoice $invoice, ?string $notes = null): void
    {
        $this->update([
            'status' => 'processed',
            'invoice_id' => $invoice->id,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'processing_notes' => $notes,
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'processing_notes' => $reason,
            'processed_at' => now(),
        ]);
    }

    public function markAsRejected(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'processing_notes' => $reason,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'processed' => 'badge-success',
            'failed' => 'badge-danger',
            'rejected' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'processed' => 'Traité',
            'failed' => 'Échec',
            'rejected' => 'Rejeté',
            default => 'Inconnu',
        };
    }
}
