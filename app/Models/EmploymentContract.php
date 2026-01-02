<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentContract extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'company_id',
        'contract_number',
        'contract_type',
        'work_regime',
        'status',
        'start_date',
        'end_date',
        'trial_period_end',
        'termination_date',
        'termination_reason',
        'job_title',
        'job_category',
        'paritair_committee',
        'job_description',
        'work_location',
        'weekly_hours',
        'remote_work_allowed',
        'remote_days_per_week',
        'annual_leave_days',
        'extra_legal_days',
        'gross_monthly_salary',
        'gross_hourly_rate',
        'salary_scale',
        'salary_step',
        '13th_month',
        'year_end_bonus',
        'performance_bonus',
        'commission_rate',
        'company_car',
        'company_car_value',
        'fuel_card',
        'meal_vouchers',
        'meal_voucher_value',
        'eco_vouchers',
        'eco_voucher_value',
        'group_insurance',
        'hospitalization_insurance',
        'mobile_phone',
        'laptop',
        'internet_allowance',
        'internet_allowance_amount',
        'expense_allowance',
        'representation_allowance',
        'pc_200',
        'joint_committee_number',
        'pension_plan',
        'notice_period_days',
        'probation_period_days',
        'non_compete_clause',
        'confidentiality_clause',
        'signature_date',
        'signed_document_path',
        'contract_file_path',
        'additional_clauses',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trial_period_end' => 'date',
        'termination_date' => 'date',
        'signature_date' => 'date',
        'weekly_hours' => 'decimal:2',
        'annual_leave_days' => 'integer',
        'extra_legal_days' => 'integer',
        'remote_days_per_week' => 'integer',
        'gross_monthly_salary' => 'decimal:2',
        'gross_hourly_rate' => 'decimal:2',
        'internet_allowance_amount' => 'decimal:2',
        '13th_month' => 'decimal:2',
        'year_end_bonus' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'company_car' => 'boolean',
        'company_car_value' => 'decimal:2',
        'fuel_card' => 'boolean',
        'meal_vouchers' => 'boolean',
        'meal_voucher_value' => 'decimal:2',
        'eco_vouchers' => 'boolean',
        'eco_voucher_value' => 'decimal:2',
        'group_insurance' => 'boolean',
        'hospitalization_insurance' => 'boolean',
        'mobile_phone' => 'boolean',
        'laptop' => 'boolean',
        'internet_allowance' => 'boolean',
        'remote_work_allowed' => 'boolean',
        'expense_allowance' => 'decimal:2',
        'representation_allowance' => 'decimal:2',
        'pc_200' => 'boolean',
        'notice_period_days' => 'integer',
        'probation_period_days' => 'integer',
        'non_compete_clause' => 'boolean',
        'confidentiality_clause' => 'boolean',
        'additional_clauses' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Methods
     */

    /**
     * Check if contract is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->startOfDay();

        // Must have started
        if ($this->start_date->isAfter($today)) {
            return false;
        }

        // Check if ended
        if ($this->end_date && $this->end_date->isBefore($today)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate total monthly gross cost for employer.
     */
    public function getTotalMonthlyCost(): float
    {
        $baseSalary = $this->gross_monthly_salary;

        // Employer social security ~25%
        $employerSocialSecurity = $baseSalary * 0.25;

        // Benefits
        $benefitsCost = 0;

        if ($this->company_car) {
            $benefitsCost += $this->company_car_value ?? 0;
        }

        if ($this->meal_vouchers) {
            // Employer pays ~€5.91, employee pays €1.09 (for €8 voucher)
            $workingDays = 20; // Average per month
            $employerPart = ($this->meal_voucher_value ?? 8.00) - 1.09;
            $benefitsCost += $workingDays * $employerPart;
        }

        if ($this->group_insurance) {
            $benefitsCost += $baseSalary * 0.03; // ~3% estimate
        }

        if ($this->hospitalization_insurance) {
            $benefitsCost += 80; // ~€80/month estimate
        }

        $benefitsCost += $this->internet_allowance ?? 0;
        $benefitsCost += $this->expense_allowance ?? 0;

        return $baseSalary + $employerSocialSecurity + $benefitsCost;
    }

    /**
     * Calculate net annual salary (employee perspective).
     */
    public function getEstimatedNetAnnualSalary(): float
    {
        $monthlyGross = $this->gross_monthly_salary;

        // 12 months
        $annual = $monthlyGross * 12;

        // Add 13th month if applicable
        if ($this->{'13th_month'}) {
            $annual += $this->{'13th_month'};
        }

        // Add year-end bonus
        if ($this->year_end_bonus) {
            $annual += $this->year_end_bonus;
        }

        // Subtract employee social security (13.07%)
        $socialSecurity = $annual * 0.1307;

        // Subtract estimated professional tax (~30% average for middle income)
        // This is a simplification - real calculation needs tax tables
        $taxableIncome = $annual - $socialSecurity;
        $estimatedTax = $taxableIncome * 0.30;

        $netAnnual = $annual - $socialSecurity - $estimatedTax;

        return round($netAnnual, 2);
    }

    /**
     * Generate unique contract number.
     */
    public static function generateContractNumber(Company $company): string
    {
        $year = now()->format('Y');
        $count = self::where('company_id', $company->id)
            ->whereYear('start_date', $year)
            ->count() + 1;

        return sprintf('CTR-%s-%04d', $year, $count);
    }

    /**
     * Get contract duration in months.
     */
    public function getDurationMonths(): ?int
    {
        if (!$this->end_date) {
            return null; // CDI - unlimited
        }

        return $this->start_date->diffInMonths($this->end_date);
    }

    /**
     * Check if contract is in probation period.
     */
    public function isInProbation(): bool
    {
        if (!$this->probation_period_days || $this->status !== 'active') {
            return false;
        }

        $probationEnd = $this->start_date->copy()->addDays($this->probation_period_days);

        return now()->lessThanOrEqualTo($probationEnd);
    }

    /**
     * Get applicable joint committee (commission paritaire).
     */
    public function getJointCommitteeInfo(): ?array
    {
        if (!$this->joint_committee_number) {
            return null;
        }

        // Common Belgian joint committees
        $committees = [
            '200' => 'Employés (secteur général)',
            '111' => 'Construction métallique',
            '124' => 'Construction',
            '218' => 'Alimentation',
            '226' => 'Commerce international',
            '302' => 'Horeca',
            '330' => 'Services de santé',
        ];

        return [
            'number' => $this->joint_committee_number,
            'name' => $committees[$this->joint_committee_number] ?? 'Commission paritaire ' . $this->joint_committee_number,
        ];
    }
}
