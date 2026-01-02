<?php

namespace App\Services\Payroll;

interface PayrollCalculatorInterface
{
    /**
     * Calculate net salary and all payroll components
     *
     * @param float $grossSalary
     * @param array $additionalData Additional employee/contract data
     * @return array Contains: gross_salary, employee_social_security, employer_social_security,
     *                        income_tax, net_salary, total_employer_cost
     */
    public function calculateNetSalary(float $grossSalary, array $additionalData = []): array;

    /**
     * Calculate progressive income tax
     *
     * @param float $taxableAmount
     * @return float
     */
    public function calculateIncomeTax(float $taxableAmount): float;

    /**
     * Calculate employee social security contributions
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployeeSocialSecurity(float $grossSalary): float;

    /**
     * Calculate employer social security contributions
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployerSocialSecurity(float $grossSalary): float;

    /**
     * Get country code for this calculator
     *
     * @return string
     */
    public function getCountryCode(): string;
}
