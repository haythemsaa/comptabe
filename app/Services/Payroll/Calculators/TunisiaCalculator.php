<?php

namespace App\Services\Payroll\Calculators;

use App\Services\Payroll\PayrollCalculatorInterface;

class TunisiaCalculator implements PayrollCalculatorInterface
{
    /**
     * Tunisian CNSS rates (2024)
     */
    protected const CNSS_EMPLOYEE_RATE = 9.18; // %
    protected const CNSS_EMPLOYER_RATE = 16.57; // %

    /**
     * Tunisian IRPP (Impôt sur le Revenu des Personnes Physiques) brackets (2024)
     * Annual amounts in TND
     */
    protected const IRPP_BRACKETS = [
        ['min' => 0, 'max' => 5000, 'rate' => 0],
        ['min' => 5000, 'max' => 20000, 'rate' => 26],
        ['min' => 20000, 'max' => 30000, 'rate' => 28],
        ['min' => 30000, 'max' => 50000, 'rate' => 32],
        ['min' => 50000, 'max' => null, 'rate' => 35],
    ];

    /**
     * Calculate net salary and all components
     *
     * @param float $grossSalary Monthly gross salary in TND
     * @param array $additionalData
     * @return array
     */
    public function calculateNetSalary(float $grossSalary, array $additionalData = []): array
    {
        // Round to 3 decimal places (millimes)
        $grossSalary = round($grossSalary, 3);

        // 1. Calculate CNSS (Caisse Nationale de Sécurité Sociale)
        $employeeCNSS = $this->calculateEmployeeSocialSecurity($grossSalary);
        $employerCNSS = $this->calculateEmployerSocialSecurity($grossSalary);

        // 2. Taxable amount = Gross - Employee CNSS
        $taxableAmount = $grossSalary - $employeeCNSS;

        // 3. Calculate IRPP (annual then monthly)
        $annualTaxableAmount = $taxableAmount * 12;
        $annualIRPP = $this->calculateIncomeTax($annualTaxableAmount);
        $monthlyIRPP = round($annualIRPP / 12, 3);

        // 4. Net salary = Gross - CNSS - IRPP
        $netSalary = round($grossSalary - $employeeCNSS - $monthlyIRPP, 3);

        // 5. Total employer cost = Gross + Employer CNSS
        $totalEmployerCost = round($grossSalary + $employerCNSS, 3);

        return [
            'gross_salary' => $grossSalary,
            'employee_social_security' => $employeeCNSS,
            'employee_social_security_rate' => self::CNSS_EMPLOYEE_RATE,
            'employer_social_security' => $employerCNSS,
            'employer_social_security_rate' => self::CNSS_EMPLOYER_RATE,
            'taxable_amount' => round($taxableAmount, 3),
            'income_tax' => $monthlyIRPP,
            'income_tax_name' => 'IRPP',
            'net_salary' => $netSalary,
            'total_employer_cost' => $totalEmployerCost,
            'breakdown' => [
                'cnss' => [
                    'employee' => $employeeCNSS,
                    'employer' => $employerCNSS,
                    'total' => round($employeeCNSS + $employerCNSS, 3),
                ],
                'irpp' => [
                    'annual_taxable' => round($annualTaxableAmount, 3),
                    'annual_tax' => round($annualIRPP, 3),
                    'monthly_tax' => $monthlyIRPP,
                ],
            ],
        ];
    }

    /**
     * Calculate IRPP (Tunisian progressive income tax)
     *
     * @param float $annualTaxableAmount Annual taxable amount in TND
     * @return float Annual tax amount
     */
    public function calculateIncomeTax(float $annualTaxableAmount): float
    {
        $tax = 0;

        // First bracket: 0 - 5,000 TND (0%)
        if ($annualTaxableAmount <= 5000) {
            return 0;
        }

        // Second bracket: 5,000 - 20,000 TND (26%)
        if ($annualTaxableAmount <= 20000) {
            $tax = ($annualTaxableAmount - 5000) * 0.26;
            return round($tax, 3);
        }

        // Third bracket: 20,000 - 30,000 TND (28%)
        $tax = (20000 - 5000) * 0.26; // 3,900 TND from previous bracket

        if ($annualTaxableAmount <= 30000) {
            $tax += ($annualTaxableAmount - 20000) * 0.28;
            return round($tax, 3);
        }

        // Fourth bracket: 30,000 - 50,000 TND (32%)
        $tax += (30000 - 20000) * 0.28; // 2,800 TND from previous bracket (total 6,700)

        if ($annualTaxableAmount <= 50000) {
            $tax += ($annualTaxableAmount - 30000) * 0.32;
            return round($tax, 3);
        }

        // Fifth bracket: > 50,000 TND (35%)
        $tax += (50000 - 30000) * 0.32; // 6,400 TND from previous bracket (total 13,100)
        $tax += ($annualTaxableAmount - 50000) * 0.35;

        return round($tax, 3);
    }

    /**
     * Calculate employee CNSS contribution
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployeeSocialSecurity(float $grossSalary): float
    {
        return round($grossSalary * (self::CNSS_EMPLOYEE_RATE / 100), 3);
    }

    /**
     * Calculate employer CNSS contribution
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployerSocialSecurity(float $grossSalary): float
    {
        return round($grossSalary * (self::CNSS_EMPLOYER_RATE / 100), 3);
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return 'TN';
    }

    /**
     * Calculate annual leave days for Tunisia
     *
     * @param int $monthsWorked Number of months worked in the year
     * @return int Number of leave days earned
     */
    public function calculateAnnualLeaveDays(int $monthsWorked): int
    {
        // In Tunisia: 1 day of paid leave per month worked
        // Maximum 12 days per year (if worked full year)
        return min($monthsWorked, 12);
    }

    /**
     * Get minimum wage for Tunisia (SMIG 2024)
     *
     * @return float Monthly minimum wage in TND
     */
    public function getMinimumWage(): float
    {
        return 460.00; // SMIG 2024 - 48 hours/week
    }

    /**
     * Get standard working hours per week for Tunisia
     *
     * @return int Hours per week
     */
    public function getStandardWorkingHours(): int
    {
        return 48; // Legal maximum in Tunisia
    }
}
