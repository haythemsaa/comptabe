<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportExecution extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'report_id',
        'user_id',
        'status',
        'format',
        'parameters',
        'file_path',
        'file_size',
        'execution_time_ms',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: exécutions réussies
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: exécutions échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Obtenir l'URL de téléchargement
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if (!$this->file_path || !Storage::disk('local')->exists($this->file_path)) {
            return null;
        }

        return route('reports.download', $this->id);
    }

    /**
     * Vérifier si le fichier existe encore
     */
    public function fileExists(): bool
    {
        return $this->file_path && Storage::disk('local')->exists($this->file_path);
    }

    /**
     * Supprimer le fichier associé
     */
    public function deleteFile(): bool
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            return Storage::disk('local')->delete($this->file_path);
        }
        return true;
    }

    /**
     * Formater la taille du fichier
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
