<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get company by email or name
        $company = Company::where('email', 'info@consultingpro.be')->first()
            ?? Company::where('name', 'LIKE', '%Demo%')->first()
            ?? Company::where('name', 'LIKE', '%Consulting%')->first()
            ?? Company::first();

        if (!$company) {
            $this->command->error('No company found. Please create a company first.');
            return;
        }

        $this->command->info("Creating payroll demo data for company: {$company->name}");

        // Clean up existing demo data first
        $this->cleanupExistingDemoData($company);

        // Create demo employees
        $employees = $this->createDemoEmployees($company);

        // Create employment contracts
        $this->createEmploymentContracts($employees);

        // Generate payslips for last 6 months
        $this->generatePayslips($employees);

        $this->command->info('Payroll demo data created successfully!');
    }

    /**
     * Clean up existing demo data.
     */
    private function cleanupExistingDemoData(Company $company): void
    {
        $this->command->info('Cleaning up existing demo data...');

        // Delete existing demo employees (will cascade to contracts and payslips)
        $companyPrefix = strtoupper(substr($company->name, 0, 3));

        $count = Employee::where('company_id', $company->id)
            ->where(function ($query) use ($companyPrefix) {
                $query->where('email', 'LIKE', '%@example.be')
                    ->orWhere('employee_number', 'LIKE', $companyPrefix . '-EMP-%')
                    ->orWhere('employee_number', 'LIKE', 'EMP-%');
            })
            ->forceDelete();

        if ($count > 0) {
            $this->command->info("  - Deleted {$count} existing demo employees");
        }
    }

    /**
     * Create demo employees with realistic Belgian data.
     */
    private function createDemoEmployees(Company $company): array
    {
        $employeesData = [
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'birth_date' => '1985-07-30',
                'gender' => 'M',
                'national_number' => '85073003328',
                'email' => 'jean.dupont@example.be',
                'phone' => '+32 2 123 45 67',
                'mobile' => '+32 475 12 34 56',
                'street' => 'Rue de la Loi',
                'house_number' => '16',
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
                'iban' => 'BE68539007547034',
                'bic' => 'GKCCBEBB',
                'hire_date' => '2020-01-15',
                'status' => 'active',
                'base_salary' => 3500.00,
                'position' => 'Développeur Senior',
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'birth_date' => '1990-03-15',
                'gender' => 'F',
                'national_number' => '90031512345',
                'email' => 'marie.martin@example.be',
                'phone' => '+32 2 234 56 78',
                'mobile' => '+32 476 23 45 67',
                'street' => 'Avenue Louise',
                'house_number' => '54',
                'postal_code' => '1050',
                'city' => 'Ixelles',
                'country_code' => 'BE',
                'iban' => 'BE12345678901234',
                'bic' => 'GEBABEBB',
                'hire_date' => '2019-06-01',
                'status' => 'active',
                'base_salary' => 4200.00,
                'position' => 'Chef de Projet',
            ],
            [
                'first_name' => 'Luc',
                'last_name' => 'Dubois',
                'birth_date' => '1988-11-20',
                'gender' => 'M',
                'national_number' => '88112012367',
                'email' => 'luc.dubois@example.be',
                'phone' => '+32 2 345 67 89',
                'mobile' => '+32 477 34 56 78',
                'street' => 'Boulevard Anspach',
                'house_number' => '123',
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
                'iban' => 'BE98765432109876',
                'bic' => 'KREDBEBB',
                'hire_date' => '2021-03-10',
                'status' => 'active',
                'base_salary' => 3200.00,
                'position' => 'Comptable',
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Leroy',
                'birth_date' => '1992-05-08',
                'gender' => 'F',
                'national_number' => '92050812389',
                'email' => 'sophie.leroy@example.be',
                'mobile' => '+32 478 45 67 89',
                'street' => 'Rue Neuve',
                'house_number' => '78',
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
                'iban' => 'BE45678901234567',
                'bic' => 'BPOTBEB1',
                'hire_date' => '2022-09-01',
                'status' => 'active',
                'base_salary' => 2800.00,
                'position' => 'Assistante RH',
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Lambert',
                'birth_date' => '1983-12-12',
                'gender' => 'M',
                'national_number' => '83121212301',
                'email' => 'pierre.lambert@example.be',
                'phone' => '+32 2 456 78 90',
                'mobile' => '+32 479 56 78 90',
                'street' => 'Chaussée de Charleroi',
                'house_number' => '200',
                'postal_code' => '1060',
                'city' => 'Saint-Gilles',
                'country_code' => 'BE',
                'iban' => 'BE78901234567890',
                'bic' => 'AXABBE22',
                'hire_date' => '2018-04-15',
                'status' => 'active',
                'base_salary' => 5500.00,
                'position' => 'Directeur des Opérations',
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Deschamps',
                'birth_date' => '1995-08-25',
                'gender' => 'F',
                'national_number' => '95082512456',
                'email' => 'emma.deschamps@example.be',
                'mobile' => '+32 470 12 34 56',
                'street' => 'Avenue de Tervueren',
                'house_number' => '45',
                'postal_code' => '1040',
                'city' => 'Etterbeek',
                'country_code' => 'BE',
                'iban' => 'BE23456789012345',
                'bic' => 'NICABEBB',
                'hire_date' => '2023-01-10',
                'status' => 'active',
                'base_salary' => 2600.00,
                'position' => 'Stagiaire Marketing',
            ],
        ];

        $employees = [];

        $employeeCounter = 1;

        foreach ($employeesData as $data) {
            $position = $data['position'];
            $baseSalary = $data['base_salary'];
            unset($data['position'], $data['base_salary']);

            // Generate unique employee number with company prefix
            $companyPrefix = strtoupper(substr($company->name, 0, 3));
            $employeeNumber = sprintf('%s-EMP-%04d', $companyPrefix, $employeeCounter++);

            $employee = Employee::create([
                'company_id' => $company->id,
                'employee_number' => $employeeNumber,
                'nationality' => 'BE',
                'birth_place' => 'Bruxelles',
                'birth_country' => 'BE',
                ...$data,
            ]);

            $employee->position = $position;
            $employee->base_salary = $baseSalary;

            $employees[] = $employee;

            $this->command->info("Created employee: {$employee->full_name} ({$position})");
        }

        return $employees;
    }

    /**
     * Create employment contracts for employees.
     */
    private function createEmploymentContracts(array $employees): void
    {
        $contractCounter = 1;

        foreach ($employees as $employee) {
            // Determine contract type based on position
            $contractType = match ($employee->position) {
                'Stagiaire Marketing' => 'internship',
                'Directeur des Opérations' => 'permanent',
                default => 'permanent',
            };

            // Calculate benefits based on salary and position
            $hasCompanyCar = $employee->base_salary >= 4000;
            $mealVouchersValue = 8.00;
            $ecoVouchersAnnual = $employee->base_salary >= 3000 ? 250.00 : 0;

            // 13th month is standard in Belgium
            $thirteenthMonth = $employee->base_salary;

            // Year-end bonus for senior positions
            $yearEndBonus = $employee->base_salary >= 4500 ? $employee->base_salary * 0.5 : 0;

            // Map contract types to Belgian types
            $belgianContractType = match ($contractType) {
                'internship' => 'student',
                'permanent' => 'cdi',
                'temporary' => 'cdd',
                default => 'cdi',
            };

            // Generate unique contract number with timestamp to ensure uniqueness
            $contractNumber = sprintf('CT-%s-%04d', now()->format('Ymd-His'), $contractCounter++);

            EmploymentContract::create([
                'employee_id' => $employee->id,
                'contract_number' => $contractNumber,
                'contract_type' => $belgianContractType,
                'start_date' => $employee->hire_date,
                'end_date' => $contractType === 'internship' ? Carbon::parse($employee->hire_date)->addMonths(6) : null,
                'job_title' => $employee->position,
                'job_category' => 'CP 200', // Commission paritaire 200 (employés)
                'paritair_committee' => 200,
                'work_regime' => 'full_time',
                'weekly_hours' => 38,
                'gross_monthly_salary' => $employee->base_salary,
                'company_car' => $hasCompanyCar,
                'company_car_value' => $hasCompanyCar ? 35000.00 : null,
                'meal_vouchers' => true,
                'meal_voucher_value' => $mealVouchersValue,
                'eco_vouchers' => $ecoVouchersAnnual > 0,
                'eco_voucher_value' => $ecoVouchersAnnual,
                'hospitalization_insurance' => true,
                'group_insurance' => $employee->base_salary >= 3500,
                'mobile_phone' => in_array($employee->position, ['Chef de Projet', 'Directeur des Opérations', 'Développeur Senior']),
                'internet_allowance' => in_array($employee->position, ['Développeur Senior', 'Chef de Projet', 'Directeur des Opérations']),
                'internet_allowance_amount' => in_array($employee->position, ['Développeur Senior', 'Chef de Projet', 'Directeur des Opérations']) ? 30.00 : 0,
                '13th_month' => $thirteenthMonth,
                'year_end_bonus' => $yearEndBonus,
                'annual_leave_days' => 20,
                'extra_legal_days' => $employee->base_salary >= 4500 ? 5 : 0,
                'remote_work_allowed' => in_array($employee->position, ['Développeur Senior', 'Chef de Projet']),
                'remote_days_per_week' => in_array($employee->position, ['Développeur Senior', 'Chef de Projet']) ? 3 : 0,
                'status' => 'active',
                'signature_date' => Carbon::parse($employee->hire_date)->subDays(3),
            ]);

            $this->command->info("  - Created contract for {$employee->full_name}");
        }
    }

    /**
     * Get department based on position.
     */
    private function getDepartment(string $position): string
    {
        return match (true) {
            str_contains($position, 'Développeur') => 'IT',
            str_contains($position, 'Chef de Projet') => 'Management',
            str_contains($position, 'Comptable') => 'Finance',
            str_contains($position, 'RH') => 'Ressources Humaines',
            str_contains($position, 'Directeur') => 'Direction',
            str_contains($position, 'Marketing') => 'Marketing',
            default => 'Général',
        };
    }

    /**
     * Get notice period based on hire date (seniority).
     */
    private function getNoticePeriod(string $hireDate): int
    {
        $years = Carbon::parse($hireDate)->diffInYears(now());

        return match (true) {
            $years < 1 => 30,
            $years < 5 => 60,
            $years < 10 => 90,
            default => 120,
        };
    }

    /**
     * Generate payslips for the last 6 months.
     */
    private function generatePayslips(array $employees): void
    {
        $this->command->info('Generating payslips for last 6 months...');

        $payslipCounter = 1;

        // Generate for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            $this->command->info("  - Generating payslips for {$date->format('F Y')}");

            foreach ($employees as $employee) {
                // Get active contract
                $contract = $employee->contracts()
                    ->where('status', 'active')
                    ->latest('start_date')
                    ->first();

                if (!$contract) {
                    continue;
                }

                // Check if employee was hired by this month
                if (Carbon::parse($employee->hire_date)->greaterThan($date)) {
                    continue;
                }

                // Calculate payslip details
                $baseSalary = $contract->gross_monthly_salary;

                // Random overtime hours (0-10 hours)
                $overtimeHours = rand(0, 10);
                $overtimePay = $overtimeHours * ($baseSalary / 160) * 1.5; // 50% premium

                // Night/weekend hours (occasional)
                $nightHours = rand(0, 5);
                $nightPremium = $nightHours * ($baseSalary / 160) * 0.25;

                // Bonuses (random small bonus occasionally)
                $bonuses = rand(0, 100) > 80 ? rand(100, 500) : 0;

                // 13th month in December
                $thirteenthMonth = $month === 12 ? ($contract->{'13th_month'} ?? 0) : 0;

                // Calculate gross total
                $grossTotal = $baseSalary + $overtimePay + $nightPremium + $bonuses + $thirteenthMonth;

                // Social security (13.07% employee)
                $employeeSocialSecurity = $grossTotal * 0.1307;

                // Special social contribution (progressive, simplified here)
                $specialSocialContribution = $grossTotal > 2500 ? ($grossTotal - 2500) * 0.01 : 0;

                // Professional tax (progressive, simplified calculation)
                $taxableIncome = $grossTotal - $employeeSocialSecurity;
                $professionalTax = $this->calculateSimplifiedTax($taxableIncome);

                // Meal voucher deduction (employee pays 1.09€ per voucher)
                $workedDays = 22; // Average working days per month
                $mealVoucherDeduction = $contract->meal_vouchers ? $workedDays * 1.09 : 0;

                // Total deductions
                $totalDeductions = $employeeSocialSecurity + $specialSocialContribution + $professionalTax + $mealVoucherDeduction;

                // Net salary
                $netSalary = $grossTotal - $totalDeductions;

                // Employer costs
                $employerSocialSecurity = $grossTotal * 0.25; // ~25% employer contributions
                $totalEmployerCost = $grossTotal + $employerSocialSecurity;

                // Company car benefit
                $companyCarBenefit = $contract->company_car ? (($contract->company_car_value ?? 0) * 0.06 / 12) : 0;

                // Meal vouchers
                $mealVouchersCount = $contract->meal_vouchers ? $workedDays : 0;
                $mealVouchersValue = $contract->meal_voucher_value ?? 0;

                // Eco vouchers (in June typically)
                $ecoVouchersValue = ($month === 6 && $contract->eco_vouchers) ? ($contract->eco_voucher_value ?? 0) : 0;

                // Generate payslip number with unique counter
                $payslipNumber = sprintf('FP-%s-%02d-%04d', $year, $month, $payslipCounter++);

                // Status: paid for older months, validated for last month, draft for current month
                $status = match (true) {
                    $i > 1 => 'paid',
                    $i === 1 => 'validated',
                    default => 'draft',
                };

                // Payment date: set for all statuses (expected or actual payment date)
                $paymentDate = match ($status) {
                    'paid' => $date->copy()->endOfMonth(),
                    'validated' => $date->copy()->endOfMonth()->addDays(5), // Will be paid in 5 days
                    'draft' => $date->copy()->endOfMonth()->addDays(10), // Expected payment date
                    default => $date->copy()->endOfMonth(),
                };

                Payslip::create([
                    'employee_id' => $employee->id,
                    'company_id' => $employee->company_id,
                    'year' => $year,
                    'month' => $month,
                    'period' => sprintf('%04d-%02d', $year, $month),
                    'payment_date' => $paymentDate,
                    'payslip_number' => $payslipNumber,
                    'worked_hours' => 160 + $overtimeHours,
                    'overtime_hours' => $overtimeHours,
                    'night_hours' => $nightHours,
                    'worked_days' => $workedDays,
                    'base_salary' => $baseSalary,
                    'overtime_pay' => $overtimePay,
                    'night_premium' => $nightPremium,
                    'bonuses' => $bonuses,
                    '13th_month' => $thirteenthMonth,
                    'gross_total' => $grossTotal,
                    'employee_social_security' => $employeeSocialSecurity,
                    'employee_social_security_rate' => 13.07,
                    'special_social_contribution' => $specialSocialContribution,
                    'professional_tax' => $professionalTax,
                    'professional_tax_rate' => ($professionalTax / $taxableIncome) * 100,
                    'meal_voucher_deduction' => $mealVoucherDeduction,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'employer_social_security' => $employerSocialSecurity,
                    'employer_social_security_rate' => 25.00,
                    'total_employer_cost' => $totalEmployerCost,
                    'company_car_benefit' => $companyCarBenefit,
                    'meal_vouchers_count' => $mealVouchersCount,
                    'meal_vouchers_value' => $mealVouchersValue,
                    'eco_vouchers_value' => $ecoVouchersValue,
                    'status' => $status,
                    'validated_at' => $status !== 'draft' ? $date->endOfMonth() : null,
                    'validated_by' => $status !== 'draft' ? null : null, // Would need a user ID
                ]);
            }
        }

        $this->command->info('Payslips generated successfully!');
    }

    /**
     * Calculate simplified Belgian professional tax.
     * This is a very simplified version - real tax is much more complex.
     */
    private function calculateSimplifiedTax(float $monthlyTaxableIncome): float
    {
        $annualIncome = $monthlyTaxableIncome * 12;

        // Simplified Belgian tax brackets (2024)
        $tax = match (true) {
            $annualIncome <= 15200 => $annualIncome * 0.25,
            $annualIncome <= 26830 => 3800 + ($annualIncome - 15200) * 0.40,
            $annualIncome <= 46440 => 8452 + ($annualIncome - 26830) * 0.45,
            $annualIncome <= 61470 => 17277 + ($annualIncome - 46440) * 0.50,
            default => 24782 + ($annualIncome - 61470) * 0.50,
        };

        return $tax / 12; // Monthly tax
    }
}
