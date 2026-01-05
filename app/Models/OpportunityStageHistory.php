<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityStageHistory extends Model
{
    use HasUuid;

    protected $table = 'opportunity_stage_history';

    protected $fillable = [
        'opportunity_id',
        'from_stage',
        'to_stage',
        'changed_by',
        'notes',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getFromStageLabel(): string
    {
        if (!$this->from_stage) {
            return 'Création';
        }
        return Opportunity::STAGES[$this->from_stage]['label'] ?? $this->from_stage;
    }

    public function getToStageLabel(): string
    {
        return Opportunity::STAGES[$this->to_stage]['label'] ?? $this->to_stage;
    }
}
