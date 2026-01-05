<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Opportunity extends Model
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'partner_id',
        'title',
        'description',
        'amount',
        'currency',
        'probability',
        'stage',
        'expected_close_date',
        'actual_close_date',
        'source',
        'assigned_to',
        'created_by',
        'lost_reason',
        'notes',
        'tags',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'tags' => 'array',
    ];

    // Stages avec labels et couleurs
    public const STAGES = [
        'lead' => ['label' => 'Prospect', 'color' => 'secondary', 'probability' => 10],
        'qualified' => ['label' => 'Qualifié', 'color' => 'info', 'probability' => 25],
        'proposal' => ['label' => 'Proposition', 'color' => 'primary', 'probability' => 50],
        'negotiation' => ['label' => 'Négociation', 'color' => 'warning', 'probability' => 75],
        'won' => ['label' => 'Gagné', 'color' => 'success', 'probability' => 100],
        'lost' => ['label' => 'Perdu', 'color' => 'danger', 'probability' => 0],
    ];

    public const SOURCES = [
        'website' => 'Site web',
        'referral' => 'Recommandation',
        'cold_call' => 'Appel à froid',
        'event' => 'Événement',
        'social_media' => 'Réseaux sociaux',
        'advertising' => 'Publicité',
        'partner' => 'Partenaire',
        'other' => 'Autre',
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(OpportunityStageHistory::class)->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopeStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    public function scopeWon($query)
    {
        return $query->where('stage', 'won');
    }

    public function scopeLost($query)
    {
        return $query->where('stage', 'lost');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeClosingThisMonth($query)
    {
        return $query->whereMonth('expected_close_date', now()->month)
                     ->whereYear('expected_close_date', now()->year)
                     ->open();
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_close_date', '<', now())
                     ->open();
    }

    // Helpers
    public function getStageLabel(): string
    {
        return self::STAGES[$this->stage]['label'] ?? $this->stage;
    }

    public function getStageColor(): string
    {
        return self::STAGES[$this->stage]['color'] ?? 'secondary';
    }

    public function getSourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source ?? '-';
    }

    public function getWeightedAmount(): float
    {
        return $this->amount * ($this->probability / 100);
    }

    public function isOpen(): bool
    {
        return !in_array($this->stage, ['won', 'lost']);
    }

    public function isWon(): bool
    {
        return $this->stage === 'won';
    }

    public function isLost(): bool
    {
        return $this->stage === 'lost';
    }

    public function isOverdue(): bool
    {
        return $this->expected_close_date &&
               $this->expected_close_date->isPast() &&
               $this->isOpen();
    }

    public function getDaysUntilClose(): ?int
    {
        if (!$this->expected_close_date) {
            return null;
        }
        return now()->diffInDays($this->expected_close_date, false);
    }

    // Actions
    public function changeStage(string $newStage, ?User $user = null, ?string $notes = null): void
    {
        $oldStage = $this->stage;

        if ($oldStage === $newStage) {
            return;
        }

        // Update stage and probability
        $this->stage = $newStage;
        $this->probability = self::STAGES[$newStage]['probability'] ?? $this->probability;

        // Set close date if won/lost
        if (in_array($newStage, ['won', 'lost'])) {
            $this->actual_close_date = now();
        } else {
            $this->actual_close_date = null;
        }

        $this->save();

        // Record history
        OpportunityStageHistory::create([
            'opportunity_id' => $this->id,
            'from_stage' => $oldStage,
            'to_stage' => $newStage,
            'changed_by' => $user?->id,
            'notes' => $notes,
        ]);
    }

    public function markAsWon(?User $user = null, ?string $notes = null): void
    {
        $this->changeStage('won', $user, $notes);
    }

    public function markAsLost(string $reason, ?User $user = null): void
    {
        $this->lost_reason = $reason;
        $this->save();
        $this->changeStage('lost', $user, $reason);
    }

    // Stats
    public static function getPipelineStats(string $companyId): array
    {
        $opportunities = self::where('company_id', $companyId)->open()->get();

        $byStage = [];
        foreach (self::STAGES as $stage => $config) {
            if (in_array($stage, ['won', 'lost'])) continue;

            $stageOpps = $opportunities->where('stage', $stage);
            $byStage[$stage] = [
                'label' => $config['label'],
                'color' => $config['color'],
                'count' => $stageOpps->count(),
                'amount' => $stageOpps->sum('amount'),
                'weighted' => $stageOpps->sum(fn($o) => $o->getWeightedAmount()),
            ];
        }

        return [
            'total_open' => $opportunities->count(),
            'total_amount' => $opportunities->sum('amount'),
            'weighted_amount' => $opportunities->sum(fn($o) => $o->getWeightedAmount()),
            'overdue_count' => $opportunities->filter(fn($o) => $o->isOverdue())->count(),
            'closing_this_month' => $opportunities->filter(fn($o) =>
                $o->expected_close_date &&
                $o->expected_close_date->isCurrentMonth()
            )->count(),
            'by_stage' => $byStage,
        ];
    }
}
