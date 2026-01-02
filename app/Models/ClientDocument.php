<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ClientDocument extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'uploaded_by',
        'type',
        'category',
        'filename',
        'original_filename',
        'mime_type',
        'file_size',
        'storage_path',
        'description',
        'document_date',
        'related_invoice_id',
        'is_processed',
    ];

    protected $casts = [
        'document_date' => 'date',
        'is_processed' => 'boolean',
    ];

    /**
     * Document types.
     */
    const TYPES = [
        'invoice' => 'Facture',
        'receipt' => 'Reçu',
        'bank_statement' => 'Relevé bancaire',
        'tax_document' => 'Document fiscal',
        'expense_report' => 'Note de frais',
        'contract' => 'Contrat',
        'other' => 'Autre',
    ];

    /**
     * User who uploaded the document.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Related invoice (if applicable).
     */
    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    /**
     * Comments on this document.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get document type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get download URL.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('client-portal.documents.download', $this->id);
    }

    /**
     * Delete file from storage when model is deleted.
     */
    protected static function booted(): void
    {
        static::deleted(function (ClientDocument $document) {
            if (Storage::disk('private')->exists($document->storage_path)) {
                Storage::disk('private')->delete($document->storage_path);
            }
        });
    }
}
