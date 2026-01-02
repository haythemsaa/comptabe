<?php

namespace App\Services\Payroll\Calculators;

use App\Services\Payroll\PayrollCalculatorInterface;

class FranceCalculator implements PayrollCalculatorInterface
{
    /**
     * French URSSAF rates (2024)
     * These are simplified total rates - real calculation is much more complex
     */
    protected const URSSAF_EMPLOYEE_RATE = 22.00; // % (total approximatif)
    protected const URSSAF_EMPLOYER_RATE = 42.00; // % (total approximatif)

    /**
     * Plafond Sécurité Sociale mensuel 2024
     */
    protected const PSS_MONTHLY = 3666;

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

        // 1. Calculate URSSAF cotisations sociales
        $employeeURSSAF = $this->calculateEmployeeSocialSecurity($grossSalary);
        $employerURSSAF = $this->calculateEmployerSocialSecurity($grossSalary);

        // 2. Taxable amount = Gross - Employee URSSAF
        $taxableAmount = $grossSalary - $employeeURSSAF;

        // 3. Calculate prélèvement à la source (simplified)
        // Taux personnalisé si fourni, sinon calcul par défaut
        $tauxPrelevement = $additionalData['taux_prelevement'] ?? null;
        $incomeTax = $this->calculateIncomeTax($taxableAmount, $tauxPrelevement);

        // 4. Net salary = Taxable - Income Tax
        $netSalary = round($taxableAmount - $incomeTax, 2);

        // 5. Total employer cost = Gross + Employer URSSAF
        $totalEmployerCost = round($grossSalary + $employerURSSAF, 2);

        return [
            'gross_salary' => $grossSalary,
            'employee_social_security' => $employeeURSSAF,
            'employee_social_security_rate' => self::URSSAF_EMPLOYEE_RATE,
            'employer_social_security' => $employerURSSAF,
            'employer_social_security_rate' => self::URSSAF_EMPLOYER_RATE,
            'taxable_amount' => round($taxableAmount, 2),
            'income_tax' => $incomeTax,
            'income_tax_name' => 'Prélèvement à la source',
            'net_salary' => $netSalary,
            'total_employer_cost' => $totalEmployerCost,
            'taux_prelevement' => $tauxPrelevement,
            'breakdown' => [
                'urssaf' => [
                    'employee' => $employeeURSSAF,
                    'employer' => $employerURSSAF,
                    'total' => round($employeeURSSAF + $employerURSSAF, 2),
                ],
                'prelevement_source' => [
                    'taxable' => round($taxableAmount, 2),
                    'tax' => $incomeTax,
                    'taux' => $tauxPrelevement,
                ],
            ],
        ];
    }

    /**
     * Calculate French income tax (prélèvement à la source)
     *
     * @param float $monthlyTaxableAmount
     * @param float|null $tauxPersonnalise Taux personnalisé (%)
     * @return float
     */
    public function calculateIncomeTax(float $monthlyTaxableAmount, ?float $tauxPersonnalise = null): float
    {
        // Si taux personnalisé fourni (depuis la fiche de paie de l'employé)
        if ($tauxPersonnalise !== null) {
            return round($monthlyTaxableAmount * ($tauxPersonnalise / 100), 2);
        }

        // Sinon, calcul selon le barème progressif 2024 (1 part fiscale)
        // Avec abattement de 10%
        $annualTaxable = ($monthlyTaxableAmount * 12) * 0.90; // 10% abattement

        // Barème progressif de l'impôt sur le revenu 2024
        $tax = 0;

        if ($annualTaxable <= 11294) {
            $tax = 0;
        } elseif ($annualTaxable <= 28797) {
            $tax = ($annualTaxable - 11294) * 0.11;
        } elseif ($annualTaxable <= 82341) {
            $tax = (28797 - 11294) * 0.11 + ($annualTaxable - 28797) * 0.30;
        } elseif ($annualTaxable <= 177106) {
            $tax = (28797 - 11294) * 0.11 + (82341 - 28797) * 0.30 + ($annualTaxable - 82341) * 0.41;
        } else {
            $tax = (28797 - 11294) * 0.11 + (82341 - 28797) * 0.30
                   + (177106 - 82341) * 0.41 + ($annualTaxable - 177106) * 0.45;
        }

        // Convert to monthly
        $monthlyTax = $tax / 12;

        return round($monthlyTax, 2);
    }

    /**
     * Calculate employee URSSAF contribution (simplified)
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployeeSocialSecurity(float $grossSalary): float
    {
        // Calcul simplifié - en réalité, chaque composante a son propre plafond
        // Composantes principales:
        // - CSG/CRDS: 9.70% (sur 98.25% du salaire brut)
        // - Sécurité sociale (maladie): 0.40%
        // - Vieillesse plafonnée: 6.90% (jusqu'à PSS)
        // - Vieillesse déplafonnée: 0.40%
        // - Retraite complémentaire AGIRC-ARRCO: ~3.15%
        // - AGFF: 0.80-0.90%
        // Total approximatif: ~22%

        return round($grossSalary * (self::URSSAF_EMPLOYEE_RATE / 100), 2);
    }

    /**
     * Calculate employer URSSAF contribution (simplified)
     *
     * @param float $grossSalary
     * @return float
     */
    public function calculateEmployerSocialSecurity(float $grossSalary): float
    {
        // Calcul simplifié - en réalité beaucoup plus complexe
        // Composantes principales:
        // - Sécurité sociale (maladie): 7.00%
        // - Allocations familiales: 3.45%
        // - Assurance chômage: 4.05%
        // - Vieillesse: 8.55% + 1.90%
        // - Retraite complémentaire: 4.72%
        // - AGFF: 1.20-1.30%
        // - Contribution formation: 0.55%
        // - Contribution transport: ~1.60% (variable selon région)
        // Total approximatif: ~42%

        return round($grossSalary * (self::URSSAF_EMPLOYER_RATE / 100), 2);
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return 'FR';
    }

    /**
     * Get minimum wage for France (2024)
     *
     * @return float Monthly minimum wage in EUR (SMIC brut)
     */
    public function getMinimumWage(): float
    {
        return 1766.92; // SMIC mensuel brut 2024 (151.67h * 11.65€)
    }

    /**
     * Get standard working hours per week for France
     *
     * @return int Hours per week
     */
    public function getStandardWorkingHours(): int
    {
        return 35; // Durée légale du travail
    }
}
