<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceLog extends Model
{
    protected $fillable = [
        'recurring_invoice_id',
        'event',
        'description',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Créé',
            'activated' => 'Activé',
            'paused' => 'Mis en pause',
            'resumed' => 'Repris',
            'cancelled' => 'Annulé',
            'renewed' => 'Renouvelé',
            'modified' => 'Modifié',
            'invoice_generated' => 'Facture générée',
            'invoice_sent' => 'Facture envoyée',
            'email_failed' => 'Échec envoi email',
            'completed' => 'Terminé',
            default => $this->event,
        };
    }

    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'activated', 'resumed', 'invoice_sent' => 'green',
            'paused', 'modified' => 'yellow',
            'cancelled', 'email_failed' => 'red',
            'invoice_generated', 'completed' => 'blue',
            default => 'gray',
        };
    }
}
