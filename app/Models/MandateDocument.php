<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MandateDocument extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'client_mandate_id',
        'uploaded_by',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'fiscal_year',
        'period',
        'status',
        'ocr_text',
        'ai_extracted_data',
        'visible_to_client',
        'processed_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'ai_extracted_data' => 'array',
            'visible_to_client' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Category labels.
     */
    public const CATEGORY_LABELS = [
        'invoice' => 'Facture',
        'receipt' => 'Reçu/Ticket',
        'bank_statement' => 'Extrait bancaire',
        'contract' => 'Contrat',
        'annual_accounts' => 'Comptes annuels',
        'tax_return' => 'Déclaration fiscale',
        'payslip' => 'Fiche de paie',
        'other' => 'Autre',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'processed' => 'Traité',
        'rejected' => 'Rejeté',
    ];

    /**
     * Status colors.
     */
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'processing' => 'primary',
        'processed' => 'success',
        'rejected' => 'danger',
    ];

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category ?? 'Non classé';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return '-';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get file URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if document is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/');
    }

    /**
     * Check if document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    /**
     * Mark as processed.
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
    }

    /**
     * Client mandate.
     */
    public function clientMandate(): BelongsTo
    {
        return $this->belongsTo(ClientMandate::class);
    }

    /**
     * User who uploaded.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * User who processed.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for visible to client.
     */
    public function scopeVisibleToClient($query)
    {
        return $query->where('visible_to_client', true);
    }

    /**
     * Scope for a specific category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
