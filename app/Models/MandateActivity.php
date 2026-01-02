<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MandateActivity extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'client_mandate_id',
        'user_id',
        'activity_type',
        'description',
        'metadata',
        'time_spent_minutes',
        'is_billable',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_billable' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Activity type labels.
     */
    public const TYPE_LABELS = [
        'login' => 'Connexion',
        'invoice_created' => 'Facture créée',
        'invoice_validated' => 'Facture validée',
        'vat_prepared' => 'TVA préparée',
        'vat_submitted' => 'TVA soumise',
        'document_uploaded' => 'Document téléversé',
        'document_processed' => 'Document traité',
        'task_created' => 'Tâche créée',
        'task_completed' => 'Tâche terminée',
        'note_added' => 'Note ajoutée',
        'message_sent' => 'Message envoyé',
        'time_entry' => 'Saisie temps',
        'other' => 'Autre',
    ];

    /**
     * Activity type icons.
     */
    public const TYPE_ICONS = [
        'login' => 'login',
        'invoice_created' => 'document-add',
        'invoice_validated' => 'check-circle',
        'vat_prepared' => 'calculator',
        'vat_submitted' => 'paper-airplane',
        'document_uploaded' => 'upload',
        'document_processed' => 'document-check',
        'task_created' => 'clipboard-list',
        'task_completed' => 'check',
        'note_added' => 'annotation',
        'message_sent' => 'mail',
        'time_entry' => 'clock',
        'other' => 'dots-horizontal',
    ];

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->activity_type] ?? 'dots-horizontal';
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpentAttribute(): ?string
    {
        if (!$this->time_spent_minutes) return null;

        $hours = floor($this->time_spent_minutes / 60);
        $minutes = $this->time_spent_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h" . ($minutes > 0 ? " {$minutes}min" : '');
        }

        return "{$minutes}min";
    }

    /**
     * Client mandate.
     */
    public function clientMandate(): BelongsTo
    {
        return $this->belongsTo(ClientMandate::class);
    }

    /**
     * User who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for billable activities.
     */
    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    /**
     * Scope for a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for activities in date range.
     */
    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
