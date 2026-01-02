<?php

namespace App\Services\AI\Chat\Tools\Payroll;

use App\Models\Employee;
use App\Models\Payslip;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class GeneratePayslipTool extends AbstractTool
{
    public function getName(): string
    {
        return 'generate_payslip';
    }

    public function getDescription(): string
    {
        return 'Generates a monthly payslip (fiche de paie) for an employee with automatic calculation of social security, taxes, and net salary. Use this to create monthly salary documents.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'employee_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the employee',
                ],
                'employee_number' => [
                    'type' => 'string',
                    'description' => 'Employee number if ID is unknown',
                ],
                'period' => [
                    'type' => 'string',
                    'description' => 'Period in YYYY-MM format (e.g., 2025-12)',
                ],
                'worked_hours' => [
                    'type' => 'number',
                    'description' => 'Number of hours worked (default: from contract)',
                ],
                'overtime_hours' => [
                    'type' => 'number',
                    'description' => 'Overtime hours (default: 0)',
                ],
                'bonuses' => [
                    'type' => 'number',
                    'description' => 'Additional bonuses (default: 0)',
                ],
                'paid_leave_days' => [
                    'type' => 'number',
                    'description' => 'Paid leave days taken (default: 0)',
                ],
                'sick_leave_days' => [
                    'type' => 'number',
                    'description' => 'Sick leave days (default: 0)',
                ],
                'payment_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Payment date (default: last day of month)',
                ],
            ],
            'required' => ['period'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Find employee
        $employee = $this->findEmployee($input, $context);

        if (!$employee) {
            return [
                'error' => 'Employé non trouvé. Spécifiez employee_id ou employee_number.',
            ];
        }

        // Check if employee is active
        if (!$employee->isActive()) {
            return [
                'error' => "L'employé {$employee->full_name} n'est pas actif (statut: {$employee->status})",
            ];
        }

        // Get active contract
        $contract = $employee->activeContract;

        if (!$contract) {
            return [
                'error' => "Aucun contrat actif trouvé pour {$employee->full_name}",
                'suggestion' => 'Créez d\'abord un contrat de travail pour cet employé',
            ];
        }

        // Parse period
        [$year, $month] = explode('-', $input['period']);

        // Check if payslip already exists
        $existing = Payslip::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existing) {
            return [
                'warning' => "Une fiche de paie existe déjà pour {$input['period']}",
                'existing_payslip' => [
                    'id' => $existing->id,
                    'payslip_number' => $existing->payslip_number,
                    'status' => $existing->status,
                    'net_salary' => (float) $existing->net_salary,
                ],
                'suggestion' => 'Utilisez update_payslip pour modifier ou delete_payslip pour supprimer',
            ];
        }

        // Calculate payslip
        $calculation = $this->calculatePayslip($employee, $contract, $input, $year, $month);

        // Create payslip
        $payslip = Payslip::create([
            'employee_id' => $employee->id,
            'company_id' => $context->company->id,
            'period' => $input['period'],
            'year' => $year,
            'month' => $month,
            'payment_date' => $input['payment_date'] ?? now()->endOfMonth()->format('Y-m-d'),
            'payslip_number' => $this->generatePayslipNumber($context->company, $year, $month),
            ...$calculation,
            'status' => 'draft',
        ]);

        return [
            'success' => true,
            'message' => "Fiche de paie générée pour {$employee->full_name} - {$input['period']}",
            'payslip' => [
                'id' => $payslip->id,
                'payslip_number' => $payslip->payslip_number,
                'period' => $payslip->period,
                'employee' => $employee->full_name,
                'gross_total' => (float) $payslip->gross_total,
                'employee_social_security' => (float) $payslip->employee_social_security,
                'professional_tax' => (float) $payslip->professional_tax,
                'total_deductions' => (float) $payslip->total_deductions,
                'net_salary' => (float) $payslip->net_salary,
                'employer_social_security' => (float) $payslip->employer_social_security,
                'total_employer_cost' => (float) $payslip->total_employer_cost,
            ],
            'breakdown' => [
                'Salaire brut' => number_format($payslip->gross_total, 2) . ' €',
                '- ONSS employé (13.07%)' => number_format($payslip->employee_social_security, 2) . ' €',
                '- Précompte professionnel' => number_format($payslip->professional_tax, 2) . ' €',
                '= Salaire net' => number_format($payslip->net_salary, 2) . ' €',
                '',
                'Coût patronal :',
                '+ ONSS patronale (25%)' => number_format($payslip->employer_social_security, 2) . ' €',
                '= Coût total employeur' => number_format($payslip->total_employer_cost, 2) . ' €',
            ],
            'next_steps' => [
                'Validez la fiche de paie (update status)',
                'Générez le PDF pour envoi à l\'employé',
                'Effectuez le paiement à la date prévue',
                'Incluez dans la déclaration DmfA trimestrielle',
            ],
        ];
    }

    /**
     * Calculate payslip amounts.
     */
    protected function calculatePayslip($employee, $contract, array $input, int $year, int $month): array
    {
        // Base salary
        $baseSalary = $contract->gross_monthly_salary;

        // Overtime
        $overtimeHours = $input['overtime_hours'] ?? 0;
        $hourlyRate = $contract->gross_hourly_rate ?? ($baseSalary / 165); // ~165h/month
        $overtimePay = $overtimeHours * $hourlyRate * 1.5; // 50% premium

        // Bonuses
        $bonuses = $input['bonuses'] ?? 0;

        // Gross total
        $grossTotal = $baseSalary + $overtimePay + $bonuses;

        // Employee social security (ONSS) ~13.07%
        $employeeSocialSecurity = $grossTotal * 0.1307;

        // Taxable amount after social security
        $taxableAmount = $grossTotal - $employeeSocialSecurity;

        // Professional tax (simplified - would need tax tables in production)
        $professionalTax = $this->calculateProfessionalTax($taxableAmount);

        // Meal voucher employee part (€1.09 per voucher)
        $workedDays = $input['worked_days'] ?? 20;
        $mealVoucherDeduction = $contract->meal_vouchers ? ($workedDays * 1.09) : 0;

        // Total deductions
        $totalDeductions = $employeeSocialSecurity + $professionalTax + $mealVoucherDeduction;

        // Net salary
        $netSalary = $grossTotal - $totalDeductions;

        // Employer social security ~25%
        $employerSocialSecurity = $grossTotal * 0.25;

        // Total employer cost
        $totalEmployerCost = $grossTotal + $employerSocialSecurity;

        // Benefits
        $mealVouchersCount = $contract->meal_vouchers ? $workedDays : 0;
        $mealVouchersValue = $contract->meal_voucher_value ?? 8.00;

        return [
            'worked_hours' => $input['worked_hours'] ?? $contract->weekly_hours * 4.33,
            'overtime_hours' => $overtimeHours,
            'worked_days' => $workedDays,
            'paid_leave_days' => $input['paid_leave_days'] ?? 0,
            'sick_leave_days' => $input['sick_leave_days'] ?? 0,
            'base_salary' => $baseSalary,
            'overtime_pay' => $overtimePay,
            'bonuses' => $bonuses,
            'gross_total' => $grossTotal,
            'employee_social_security' => $employeeSocialSecurity,
            'employee_social_security_rate' => 13.07,
            'professional_tax' => $professionalTax,
            'professional_tax_rate' => ($taxableAmount > 0) ? ($professionalTax / $taxableAmount * 100) : 0,
            'meal_voucher_deduction' => $mealVoucherDeduction,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'employer_social_security' => $employerSocialSecurity,
            'employer_social_security_rate' => 25.00,
            'total_employer_cost' => $totalEmployerCost,
            'meal_vouchers_count' => $mealVouchersCount,
            'meal_vouchers_value' => $mealVouchersValue,
        ];
    }

    /**
     * Calculate professional tax (simplified).
     */
    protected function calculateProfessionalTax(float $taxableAmount): float
    {
        // Simplified Belgian tax brackets (2025)
        // Real calculation would need full tax tables with deductions

        if ($taxableAmount <= 0) {
            return 0;
        }

        // Progressive rates
        $tax = 0;

        // First bracket: 25% up to €15,200
        $bracket1 = min($taxableAmount, 15200);
        $tax += $bracket1 * 0.25;

        // Second bracket: 40% from €15,200 to €26,830
        if ($taxableAmount > 15200) {
            $bracket2 = min($taxableAmount - 15200, 11630);
            $tax += $bracket2 * 0.40;
        }

        // Third bracket: 45% from €26,830 to €46,440
        if ($taxableAmount > 26830) {
            $bracket3 = min($taxableAmount - 26830, 19610);
            $tax += $bracket3 * 0.45;
        }

        // Fourth bracket: 50% above €46,440
        if ($taxableAmount > 46440) {
            $bracket4 = $taxableAmount - 46440;
            $tax += $bracket4 * 0.50;
        }

        return round($tax, 2);
    }

    /**
     * Generate unique payslip number.
     */
    protected function generatePayslipNumber($company, int $year, int $month): string
    {
        $count = Payslip::where('company_id', $company->id)
            ->where('year', $year)
            ->where('month', $month)
            ->count() + 1;

        return sprintf('PAY-%04d-%02d-%03d', $year, $month, $count);
    }

    /**
     * Find employee by ID or number.
     */
    protected function findEmployee(array $input, ToolContext $context): ?Employee
    {
        if (!empty($input['employee_id'])) {
            return Employee::where('id', $input['employee_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        if (!empty($input['employee_number'])) {
            return Employee::where('employee_number', $input['employee_number'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        return null;
    }
}
