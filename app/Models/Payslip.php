<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'company_id',
        'employment_contract_id',
        'period',
        'period_start',
        'period_end',
        'year',
        'month',
        'payment_date',
        'payslip_number',
        'worked_hours',
        'overtime_hours',
        'night_hours',
        'weekend_hours',
        'worked_days',
        'paid_leave_days',
        'sick_leave_days',
        'unpaid_leave_days',
        'base_salary',
        'gross_salary',
        'overtime_pay',
        'night_premium',
        'weekend_premium',
        'bonuses',
        'commissions',
        'holiday_pay',
        '13th_month',
        'other_taxable',
        'gross_total',
        'employee_social_security',
        'employee_social_security_rate',
        'special_social_contribution',
        'professional_tax',
        'professional_withholding_tax',
        'professional_tax_rate',
        'meal_voucher_deduction',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'employer_social_security',
        'employer_social_security_rate',
        'total_employer_cost',
        'company_car_benefit',
        'meal_vouchers_count',
        'meal_vouchers_value',
        'eco_vouchers_value',
        'status',
        'validated_at',
        'validated_by',
        'pdf_path',
        'pdf_generated_at',
        'detailed_items',
        'metadata',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'worked_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'night_hours' => 'decimal:2',
        'weekend_hours' => 'decimal:2',
        'worked_days' => 'integer',
        'paid_leave_days' => 'decimal:2',
        'sick_leave_days' => 'decimal:2',
        'unpaid_leave_days' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'night_premium' => 'decimal:2',
        'weekend_premium' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'commissions' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        '13th_month' => 'decimal:2',
        'other_taxable' => 'decimal:2',
        'gross_total' => 'decimal:2',
        'employee_social_security' => 'decimal:2',
        'employee_social_security_rate' => 'decimal:2',
        'special_social_contribution' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'professional_tax_rate' => 'decimal:2',
        'meal_voucher_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'employer_social_security' => 'decimal:2',
        'employer_social_security_rate' => 'decimal:2',
        'total_employer_cost' => 'decimal:2',
        'company_car_benefit' => 'decimal:2',
        'meal_vouchers_count' => 'integer',
        'meal_vouchers_value' => 'decimal:2',
        'eco_vouchers_value' => 'decimal:2',
        'validated_at' => 'date',
        'pdf_generated_at' => 'datetime',
        'detailed_items' => 'array',
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

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scopes
     */

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Accessors
     */

    public function getPeriodNameAttribute(): string
    {
        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return $monthNames[$this->month] . ' ' . $this->year;
    }

    /**
     * Methods
     */

    /**
     * Validate the payslip.
     */
    public function validate(User $user): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => $user->id,
        ]);

        return true;
    }

    /**
     * Mark payslip as paid.
     */
    public function markAsPaid(): bool
    {
        if ($this->status !== 'validated') {
            return false;
        }

        $this->update(['status' => 'paid']);

        return true;
    }

    /**
     * Cancel the payslip.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Generate PDF for the payslip.
     */
    public function generatePDF(): string
    {
        // Generate PDF using DomPDF
        $pdf = \PDF::loadView('pdf.payslip', [
            'payslip' => $this,
            'employee' => $this->employee,
            'company' => $this->company,
        ]);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        // Save PDF to storage
        $pdfPath = "payslips/{$this->year}/{$this->month}/{$this->payslip_number}.pdf";
        $pdfContent = $pdf->output();

        \Storage::disk('local')->put($pdfPath, $pdfContent);

        // Update model
        $this->update([
            'pdf_path' => $pdfPath,
            'pdf_generated_at' => now(),
        ]);

        return $pdfPath;
    }

    /**
     * Get breakdown for display.
     */
    public function getBreakdown(): array
    {
        return [
            'Rémunération brute' => [
                'Salaire de base' => $this->base_salary,
                'Heures supplémentaires' => $this->overtime_pay,
                'Prime de nuit' => $this->night_premium,
                'Prime week-end' => $this->weekend_premium,
                'Bonus' => $this->bonuses,
                'Commissions' => $this->commissions,
                'Pécule de vacances' => $this->holiday_pay,
                '13e mois' => $this->{'13th_month'},
                'Autres rémunérations' => $this->other_taxable,
                'Total brut' => $this->gross_total,
            ],
            'Retenues' => [
                'ONSS (13.07%)' => -$this->employee_social_security,
                'Cotisation spéciale' => -$this->special_social_contribution,
                'Précompte professionnel' => -$this->professional_tax,
                'Chèques-repas (part employé)' => -$this->meal_voucher_deduction,
                'Autres retenues' => -$this->other_deductions,
                'Total retenues' => -$this->total_deductions,
            ],
            'Salaire net' => $this->net_salary,
            'Avantages' => [
                'Voiture de société' => $this->company_car_benefit,
                'Chèques-repas' => $this->meal_vouchers_count . ' × ' . number_format($this->meal_vouchers_value, 2) . ' €',
                'Éco-chèques' => $this->eco_vouchers_value,
            ],
            'Coût employeur' => [
                'Salaire brut' => $this->gross_total,
                'ONSS patronale (25%)' => $this->employer_social_security,
                'Coût total' => $this->total_employer_cost,
            ],
        ];
    }

    /**
     * Get summary for period (year).
     */
    public static function getYearlySummary(Employee $employee, int $year): array
    {
        $payslips = self::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('status', '!=', 'cancelled')
            ->get();

        return [
            'total_gross' => $payslips->sum('gross_total'),
            'total_net' => $payslips->sum('net_salary'),
            'total_social_security' => $payslips->sum('employee_social_security'),
            'total_tax' => $payslips->sum('professional_tax'),
            'total_deductions' => $payslips->sum('total_deductions'),
            'total_employer_cost' => $payslips->sum('total_employer_cost'),
            'months_paid' => $payslips->count(),
            'average_net' => $payslips->avg('net_salary'),
        ];
    }

    /**
     * Calculate effective tax rate.
     */
    public function getEffectiveTaxRate(): float
    {
        if ($this->gross_total <= 0) {
            return 0;
        }

        $totalTax = $this->employee_social_security + $this->professional_tax;

        return round(($totalTax / $this->gross_total) * 100, 2);
    }

    /**
     * Check if payslip can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if payslip can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return in_array($this->status, ['draft', 'cancelled']);
    }
}
