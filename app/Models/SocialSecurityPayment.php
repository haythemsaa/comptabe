<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialSecurityPayment extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'contribution_type',
        'year',
        'quarter',
        'month',
        'period_label',
        'gross_salary_base',
        'employee_count',
        'employer_rate',
        'employee_rate',
        'employer_contribution',
        'employee_contribution',
        'total_contribution',
        'amount_paid',
        'penalties',
        'interests',
        'due_date',
        'payment_date',
        'declaration_date',
        'status',
        'onss_reference',
        'dmfa_number',
        'structured_communication',
        'payment_transaction_id',
        'journal_entry_id',
        'dmfa_file_path',
        'payment_proof_path',
        'certificate_path',
        'employee_breakdown',
        'notes',
        'calculation_details',
        'metadata',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'gross_salary_base' => 'decimal:2',
        'employer_rate' => 'decimal:2',
        'employee_rate' => 'decimal:2',
        'employer_contribution' => 'decimal:2',
        'employee_contribution' => 'decimal:2',
        'total_contribution' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'penalties' => 'decimal:2',
        'interests' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'declaration_date' => 'date',
        'validated_at' => 'datetime',
        'employee_breakdown' => 'array',
        'calculation_details' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Contribution type labels.
     */
    public const CONTRIBUTION_TYPE_LABELS = [
        'onss_employer' => 'Cotisations Patronales ONSS',
        'onss_employee' => 'Cotisations Ouvrières ONSS',
        'dmfa' => 'DMFA (Déclaration Multifonctionnelle)',
        'special_contribution' => 'Cotisation Spéciale de Sécurité Sociale',
        'pension_fund' => 'Fonds de Pension Complémentaire',
        'occupational_accident' => 'Assurance Accidents du Travail',
        'occupational_disease' => 'Assurance Maladies Professionnelles',
        'other' => 'Autre',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'draft' => 'Brouillon',
        'calculated' => 'Calculé',
        'declared' => 'Déclaré (DMFA)',
        'pending_payment' => 'En attente de paiement',
        'partially_paid' => 'Partiellement payé',
        'paid' => 'Payé',
        'overdue' => 'En retard',
        'contested' => 'Contesté',
    ];

    /**
     * Belgian ONSS employer rates (simplified - vary by sector).
     */
    public const DEFAULT_EMPLOYER_RATE = 25.00; // ~25%
    public const DEFAULT_EMPLOYEE_RATE = 13.07; // 13.07%

    /**
     * Relationships.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class, 'payment_transaction_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scopes.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForQuarter($query, int $year, int $quarter)
    {
        return $query->where('year', $year)->where('quarter', $quarter);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('contribution_type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['pending_payment', 'partially_paid'])
                    ->where('due_date', '<', now());
            });
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending_payment', 'partially_paid']);
    }

    /**
     * Accessors.
     */
    public function getContributionTypeLabelAttribute(): string
    {
        return self::CONTRIBUTION_TYPE_LABELS[$this->contribution_type] ?? $this->contribution_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_contribution - $this->amount_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now();
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Methods.
     */

    /**
     * Check if payment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Calculate contributions from payslips for a given period.
     */
    public static function calculateFromPayslips(Company $company, int $year, int $quarter): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $payslips = Payslip::where('company_id', $company->id)
            ->where('year', $year)
            ->whereBetween('month', [$startMonth, $endMonth])
            ->where('status', '!=', 'cancelled')
            ->get();

        $grossSalaryBase = $payslips->sum('gross_total');
        $employerContribution = $payslips->sum('employer_social_security');
        $employeeContribution = $payslips->sum('employee_social_security');
        $totalContribution = $employerContribution + $employeeContribution;

        $employeeBreakdown = $payslips->map(function ($payslip) {
            return [
                'employee_id' => $payslip->employee_id,
                'payslip_id' => $payslip->id,
                'gross_salary' => $payslip->gross_total,
                'employer_contribution' => $payslip->employer_social_security,
                'employee_contribution' => $payslip->employee_social_security,
            ];
        })->toArray();

        return [
            'gross_salary_base' => $grossSalaryBase,
            'employee_count' => $payslips->pluck('employee_id')->unique()->count(),
            'employer_contribution' => $employerContribution,
            'employee_contribution' => $employeeContribution,
            'total_contribution' => $totalContribution,
            'employee_breakdown' => $employeeBreakdown,
        ];
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(BankTransaction $transaction = null): bool
    {
        $this->amount_paid = $this->total_contribution;
        $this->payment_date = now();
        $this->status = 'paid';

        if ($transaction) {
            $this->payment_transaction_id = $transaction->id;
        }

        return $this->save();
    }

    /**
     * Record a partial payment.
     */
    public function recordPayment(float $amount, BankTransaction $transaction = null): bool
    {
        $this->amount_paid += $amount;
        $this->payment_date = now();

        if ($this->amount_paid >= $this->total_contribution) {
            $this->status = 'paid';
        } else {
            $this->status = 'partially_paid';
        }

        if ($transaction) {
            $this->payment_transaction_id = $transaction->id;
        }

        return $this->save();
    }

    /**
     * Calculate overdue days.
     */
    public function getOverdueDays(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Generate structured communication for ONSS payment.
     */
    public function generateStructuredCommunication(): string
    {
        // ONSS format: company number + quarter + year
        // Ex: 0123456789/Q1/2025
        $companyNumber = preg_replace('/\D/', '', $this->company->vat_number ?? '');
        return "{$companyNumber}/Q{$this->quarter}/{$this->year}";
    }

    /**
     * Update status based on due date and payment.
     */
    public function updateStatus(): void
    {
        if ($this->amount_paid >= $this->total_contribution) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date < now()) {
            $this->status = 'overdue';
        } elseif ($this->declaration_date || $this->dmfa_number) {
            $this->status = 'pending_payment';
        }

        $this->save();
    }

    /**
     * Get quarter label.
     */
    public function getQuarterLabelAttribute(): string
    {
        return "T{$this->quarter} {$this->year}";
    }
}
