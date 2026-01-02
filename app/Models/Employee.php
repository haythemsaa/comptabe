<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_number',
        'first_name',
        'last_name',
        'maiden_name',
        'birth_date',
        'birth_place',
        'birth_country',
        'gender',
        'nationality',
        'national_number',
        'cin', // Tunisia - Carte d'Identité Nationale
        'cnss_number', // Tunisia - CNSS number
        'email',
        'phone',
        'mobile',
        'street',
        'house_number',
        'box',
        'postal_code',
        'city',
        'country_code',
        'iban',
        'rib', // Tunisia - Relevé d'Identité Bancaire
        'bic',
        'status',
        'hire_date',
        'termination_date',
        'termination_reason',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'metadata',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'metadata' => 'array',
    ];

    protected $appends = [
        'full_name',
        'age',
        'seniority_years',
    ];

    /**
     * Relationships
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contracts()
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function activeContract()
    {
        return $this->hasOne(EmploymentContract::class)
            ->where('status', 'active')
            ->latest('start_date');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function latestPayslip()
    {
        return $this->hasOne(Payslip::class)
            ->latest('year')
            ->latest('month');
    }

    /**
     * Accessors
     */

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? now()->diffInYears($this->birth_date) : null;
    }

    public function getSeniorityYearsAttribute(): ?int
    {
        return $this->hire_date ? now()->diffInYears($this->hire_date) : null;
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    /**
     * Methods
     */

    /**
     * Check if employee is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Validate Belgian national number (numéro national).
     */
    public static function validateNationalNumber(string $nationalNumber): bool
    {
        // Remove spaces and dots
        $nn = preg_replace('/[^0-9]/', '', $nationalNumber);

        // Must be 11 digits
        if (strlen($nn) !== 11) {
            return false;
        }

        // Extract parts
        $birthDate = substr($nn, 0, 6); // YYMMDD
        $sequence = substr($nn, 6, 3);   // Sequence number
        $checkDigits = substr($nn, 9, 2); // Check digits

        // Calculate check digits
        $baseNumber = (int) substr($nn, 0, 9);

        // For people born after 2000, prepend 2
        $checkNumber1 = 97 - ($baseNumber % 97);
        $checkNumber2 = 97 - ((2000000000 + $baseNumber) % 97);

        return $checkDigits == $checkNumber1 || $checkDigits == $checkNumber2;
    }

    /**
     * Generate unique employee number.
     */
    public static function generateEmployeeNumber(Company $company): string
    {
        $year = now()->format('Y');
        $count = self::where('company_id', $company->id)->count() + 1;

        return sprintf('EMP-%s-%04d', $year, $count);
    }

    /**
     * Get current gross monthly salary.
     */
    public function getCurrentSalary(): ?float
    {
        $contract = $this->activeContract;

        return $contract ? $contract->gross_monthly_salary : null;
    }

    /**
     * Calculate annual gross salary.
     */
    public function getAnnualGrossSalary(): float
    {
        $monthlySalary = $this->getCurrentSalary() ?? 0;
        $contract = $this->activeContract;

        if (!$contract) {
            return 0;
        }

        // Base: 12 months
        $annual = $monthlySalary * 12;

        // Add 13th month if applicable
        if ($contract->{'13th_month'}) {
            $annual += $contract->{'13th_month'};
        }

        // Add year-end bonus if applicable
        if ($contract->year_end_bonus) {
            $annual += $contract->year_end_bonus;
        }

        return $annual;
    }

    /**
     * Get total cost for employer (including social security).
     */
    public function getTotalEmployerCost(): float
    {
        $grossSalary = $this->getCurrentSalary() ?? 0;

        // Employer social security ~25% (simplified)
        $employerSocialSecurity = $grossSalary * 0.25;

        return $grossSalary + $employerSocialSecurity;
    }
}
