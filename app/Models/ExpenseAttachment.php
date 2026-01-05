<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExpenseAttachment extends Model
{
    use HasUuid;

    protected $fillable = [
        'employee_expense_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'type',
    ];

    public const TYPES = [
        'receipt' => ['label' => 'Reçu/Ticket', 'icon' => 'receipt'],
        'invoice' => ['label' => 'Facture', 'icon' => 'file-invoice'],
        'justification' => ['label' => 'Justificatif', 'icon' => 'file-text'],
        'other' => ['label' => 'Autre', 'icon' => 'file'],
    ];

    // Relations
    public function expense(): BelongsTo
    {
        return $this->belongsTo(EmployeeExpense::class, 'employee_expense_id');
    }

    // Helpers
    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getTypeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'file';
    }

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    // Delete file when model is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            Storage::delete($attachment->file_path);
        });
    }
}
