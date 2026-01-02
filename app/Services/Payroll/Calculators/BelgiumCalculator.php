<?php

namespace App\Services\Payroll\Calculators;

use App\Services\Payroll\PayrollCalculatorInterface;

class BelgiumCalculator implements PayrollCalculatorInterface
{
    /**
     * Belgian ONSS rates (2024)
     */
    protected const ONSS_EMPLOYEE_RATE = 13.07; // %
    protected const ONSS_EMPLOYER_RATE = 25.00; // % (approximation)

    /**
     * Calculate net salary and all components
     *
     * @param float $grossSalary Monthly gross salary in EUR
     * @param array $additionalData
     * @return array
     */
    public function calculateNetSalary(float $grossSalary, array $additionalData = []): array
    {
        // Round to 2 decimal places (cents)
        $grossSalary = round($grossSalary, 2);

        // 1. Calculate ONSS (Office National de Sécurité Sociale)
        $employeeONSS = $this->calculateEmployeeSocialSecurity($grossSalary);
        $employerONSS = $this->calculateEmployerSocialSecurity($grossSalary);

        // 2. Taxable amount = Gross - Employee ONSS
        $taxableAmount = $grossSalary - $employeeONSS;

        // 3. Calculate précompte professionnel (simplified progressive tax)
        $professionalTax = $this->calculateIncomeTax($taxableAmount);

        // 4. Net salary = Taxable - Professional Tax
        $netSalary = round($taxableAmount - $professionalTax, 2);

        // 5. Total employer cost = Gross + Employer ONSS
        $totalEmployerCost = round($grossSalary + $employerONSS, 2);

        return [
            'gross_salary' => $grossSalary,
            'employee_social_security' => $employeeONSS,
            'employee_social_security_rate' => self::ONSS_EMPLOYEE_RATE,
            'employer_social_security' => $employerONSS,
            'employer_social_security_rate' => self::ONSS_EMPLOYER_RATE,
            'taxable_amount' => round($taxableAmount, 2),
            'income_tax' => $professionalTax,
            'income_tax_name' => 'Précompte professionnel',
            'net_salary' => $netSalary,
            'total_employer_cost' => $totalEmployerCost,
            'breakdown' => [
                'onss' => [
                    'employee' => $employeeONSS,
                    'employer' => $employerONSS,
                    'total' => round($employeeONSS + $employerONSS, 2),
                ],
                'professional_tax' => [
                    'taxable' => round($taxableAmount, 2),
                    'tax' => $professionalTax,
                ],
            ],
        ];
    }

    /**
     * Calculate Belgian professional tax (simplified)
     *
     * @param float $monthlyTaxableAmount
     * @return float
     */
    public function calculateIncomeTax(float $monthlyTaxableAmount): float
    {
        // This is a simplified calculation
        // In reality, Belgian tax calculation is much more complex and depends on:
        // - Family situation
        // - Number of dependents
        // - Other income
        // - Deductions
        // etc.

        // Simplified approximation: ~11% average for middle incomes
        $annualTaxable = $monthlyTaxableAmount * 12;

        // Very simplified progressive brackets
        $tax = 0;

        if ($annualTaxable <= 15200) {
            $tax = $annualTaxable * 0.25;
        } elseif ($annualTaxable <= 26830) {
            $tax = 15200 * 0.25 + ($annualTaxable - 15200) * 0.40;
        } elseif ($annualTaxable <= 46440) {
            $tax = 15200 * 0.25 + (26830 - 15200) * 0.40 + ($annualTaxable - 26830) * 0.45;
        } else {
            $tax = 15200 * 0.25 + (26830 - 15200) * 0.40 + (46440 - 26830) * 0.45 + ($annualTaxable - 46440) * 0.50;
        }

        // Convert to monthly and apply reduction (quotité exemptée)
        $monthlyTax = ($tax / 12) * 0.85; // Approximation with basic reduction

        return round($monthlyTax, 2);
    }

    /**
     * Calculate employee ONSS contribution
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployeeSocialSecurity(float $grossSalary): float
    {
        return round($grossSalary * (self::ONSS_EMPLOYEE_RATE / 100), 2);
    }

    /**
     * Calculate employer ONSS contribution
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployerSocialSecurity(float $grossSalary): float
    {
        return round($grossSalary * (self::ONSS_EMPLOYER_RATE / 100), 2);
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return 'BE';
    }

    /**
     * Get minimum wage for Belgium (2024)
     *
     * @return float Monthly minimum wage in EUR
     */
    public function getMinimumWage(): float
    {
        return 1954.99; // Salaire minimum mensuel moyen garanti 2024
    }

    /**
     * Get standard working hours per week for Belgium
     *
     * @return int Hours per week
     */
    public function getStandardWorkingHours(): int
    {
        return 38; // Standard in most sectors
    }
}
