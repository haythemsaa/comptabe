<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'invoice_id',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'document_type',
        'ocr_provider',
        'raw_text',
        'extracted_data',
        'confidence_score',
        'processing_time',
        'status',
        'auto_created',
        'validated_at',
        'validated_by',
        'error_message',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'confidence_score' => 'float',
        'auto_created' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isHighConfidence(): bool
    {
        return $this->confidence_score >= 0.85;
    }

    public function needsReview(): bool
    {
        return $this->confidence_score < 0.85 && $this->status !== 'validated';
    }
}
