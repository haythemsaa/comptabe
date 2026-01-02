<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VatDeclaration extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'period_type',
        'period_year',
        'period_number',
        'status',
        'grid_values',
        'total_operations',
        'total_vat_due',
        'total_vat_deductible',
        'balance',
        'validated_at',
        'validated_by',
        'submitted_at',
        'submission_reference',
        'intervat_response',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_number' => 'integer',
            'grid_values' => 'array',
            'total_operations' => 'decimal:2',
            'total_vat_due' => 'decimal:2',
            'total_vat_deductible' => 'decimal:2',
            'balance' => 'decimal:2',
            'validated_at' => 'datetime',
            'submitted_at' => 'datetime',
            'intervat_response' => 'array',
        ];
    }

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'validated' => 'Validé',
        'submitted' => 'Soumis',
        'accepted' => 'Accepté',
        'rejected' => 'Rejeté',
    ];

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get period name.
     */
    public function getPeriodNameAttribute(): string
    {
        if ($this->period_type === 'monthly') {
            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            return $months[$this->period_number] . ' ' . $this->period_year;
        }

        return "T{$this->period_number} {$this->period_year}";
    }

    /**
     * Get period start date.
     */
    public function getPeriodStartAttribute(): \Carbon\Carbon
    {
        if ($this->period_type === 'monthly') {
            return \Carbon\Carbon::create($this->period_year, $this->period_number, 1);
        }

        // Quarterly: T1=Jan, T2=Apr, T3=Jul, T4=Oct
        $month = (($this->period_number - 1) * 3) + 1;
        return \Carbon\Carbon::create($this->period_year, $month, 1);
    }

    /**
     * Get period end date.
     */
    public function getPeriodEndAttribute(): \Carbon\Carbon
    {
        if ($this->period_type === 'monthly') {
            return \Carbon\Carbon::create($this->period_year, $this->period_number, 1)->endOfMonth();
        }

        // Quarterly: end of T1=Mar, T2=Jun, T3=Sep, T4=Dec
        $month = $this->period_number * 3;
        return \Carbon\Carbon::create($this->period_year, $month, 1)->endOfMonth();
    }

    /**
     * Get output VAT (alias for total_vat_due).
     */
    public function getOutputVatAttribute(): float
    {
        return (float) ($this->total_vat_due ?? 0);
    }

    /**
     * Get input VAT (alias for total_vat_deductible).
     */
    public function getInputVatAttribute(): float
    {
        return (float) ($this->total_vat_deductible ?? 0);
    }

    /**
     * Check if balance is due (to pay).
     */
    public function isBalanceDue(): bool
    {
        return $this->balance > 0;
    }

    /**
     * Check if balance is recoverable.
     */
    public function isBalanceRecoverable(): bool
    {
        return $this->balance < 0;
    }

    /**
     * Validated by user.
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scope for draft declarations.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope by year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('period_year', $year);
    }
}
