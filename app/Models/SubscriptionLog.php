<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionLog extends Model
{
    protected $fillable = [
        'subscription_id',
        'event',
        'description',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
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
            'upgraded' => 'Mis à niveau',
            'downgraded' => 'Rétrogradé',
            'invoice_generated' => 'Facture générée',
            'payment_failed' => 'Paiement échoué',
            'payment_succeeded' => 'Paiement réussi',
            'trial_started' => 'Essai démarré',
            'trial_ended' => 'Essai terminé',
            default => $this->event,
        };
    }

    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'activated', 'resumed', 'payment_succeeded' => 'green',
            'paused', 'trial_started', 'trial_ended' => 'yellow',
            'cancelled', 'payment_failed' => 'red',
            'invoice_generated', 'renewed' => 'blue',
            default => 'gray',
        };
    }
}
