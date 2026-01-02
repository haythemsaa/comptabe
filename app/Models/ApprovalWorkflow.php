<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'document_type',
        'min_amount',
        'max_amount',
        'timeout_hours',
        'escalate_on_timeout',
        'escalate_on_rejection',
        'max_escalations',
        'notify_on_completion',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'escalate_on_timeout' => 'boolean',
        'escalate_on_rejection' => 'boolean',
        'notify_on_completion' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class)->orderBy('step_order');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'workflow_id');
    }

    public function getDocumentTypeLabel(): string
    {
        return match($this->document_type) {
            'invoice_purchase' => 'Factures d\'achat',
            'invoice_sale' => 'Factures de vente',
            'expense' => 'Dépenses',
            'payment' => 'Paiements',
            'journal_entry' => 'Écritures comptables',
            default => $this->document_type,
        };
    }

    public function getAmountRangeLabel(): string
    {
        if ($this->min_amount && $this->max_amount) {
            return number_format($this->min_amount, 0, ',', ' ') . ' € - ' .
                   number_format($this->max_amount, 0, ',', ' ') . ' €';
        }

        if ($this->min_amount) {
            return '> ' . number_format($this->min_amount, 0, ',', ' ') . ' €';
        }

        if ($this->max_amount) {
            return '< ' . number_format($this->max_amount, 0, ',', ' ') . ' €';
        }

        return 'Tous montants';
    }
}
