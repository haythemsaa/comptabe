<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'folder_id',
        'uploaded_by',
        'name',
        'original_filename',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
        'extension',
        'type',
        'document_date',
        'reference',
        'description',
        'notes',
        'ocr_content',
        'ocr_processed',
        'ocr_processed_at',
        'thumbnail_path',
        'invoice_id',
        'partner_id',
        'bank_transaction_id',
        'fiscal_year',
        'is_archived',
        'is_starred',
        'archived_at',
        'shared_with_accountant',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'ocr_processed' => 'boolean',
            'ocr_processed_at' => 'datetime',
            'is_archived' => 'boolean',
            'is_starred' => 'boolean',
            'archived_at' => 'datetime',
            'shared_with_accountant' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    /**
     * Document type labels.
     */
    public const TYPES = [
        'invoice' => 'Facture',
        'receipt' => 'Ticket de caisse',
        'bank_statement' => 'Extrait bancaire',
        'contract' => 'Contrat',
        'tax_document' => 'Document fiscal',
        'payroll' => 'Fiche de paie',
        'correspondence' => 'Correspondance',
        'identity' => 'Document d\'identité',
        'other' => 'Autre',
    ];

    /**
     * Type icons.
     */
    public const TYPE_ICONS = [
        'invoice' => 'receipt',
        'receipt' => 'shopping-cart',
        'bank_statement' => 'building-library',
        'contract' => 'document-text',
        'tax_document' => 'calculator',
        'payroll' => 'banknotes',
        'correspondence' => 'envelope',
        'identity' => 'identification',
        'other' => 'document',
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Folder relationship.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class);
    }

    /**
     * Uploader relationship.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Invoice relationship.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Bank transaction relationship.
     */
    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    /**
     * Tags relationship.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag');
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'document';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    /**
     * Get the file URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }

    /**
     * Check if document is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if document is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if document can have a preview.
     */
    public function hasPreview(): bool
    {
        return $this->isImage() || $this->isPdf();
    }

    /**
     * Archive the document.
     */
    public function archive(): void
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }

    /**
     * Unarchive the document.
     */
    public function unarchive(): void
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);
    }

    /**
     * Toggle star status.
     */
    public function toggleStar(): void
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    /**
     * Scope for non-archived documents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for archived documents.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope for starred documents.
     */
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope for documents by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for documents in folder.
     */
    public function scopeInFolder($query, ?string $folderId)
    {
        if ($folderId) {
            return $query->where('folder_id', $folderId);
        }

        return $query->whereNull('folder_id');
    }

    /**
     * Scope for fiscal year.
     */
    public function scopeForFiscalYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhere('ocr_content', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for documents shared with accountant.
     */
    public function scopeSharedWithAccountant($query)
    {
        return $query->where('shared_with_accountant', true);
    }
}
