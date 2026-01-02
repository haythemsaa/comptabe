<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SocialSecurityPayment;
use App\Models\TaxPayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaxAndSocialSecurityDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating demo tax payments and social security payments...');

        // Get demo company
        $company = Company::where('name', 'LIKE', '%Demo%')->first();

        if (!$company) {
            $this->command->error('Demo company not found. Please run DatabaseSeeder first.');
            return;
        }

        $user = User::where('email', 'admin@demo.be')->first();

        if (!$user) {
            $user = $company->users()->first();
        }

        // Create Tax Payments
        $this->createTaxPayments($company, $user);

        // Create Social Security Payments
        $this->createSocialSecurityPayments($company, $user);

        $this->command->info('Demo data created successfully!');
    }

    /**
     * Create demo tax payments.
     */
    protected function createTaxPayments(Company $company, User $user): void
    {
        $this->command->info('Creating tax payments...');

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // ISOC (Corporate Tax) - Annual
        TaxPayment::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'tax_type' => 'isoc',
            'year' => $lastYear,
            'quarter' => null,
            'month' => null,
            'period_label' => "Exercice {$lastYear}",
            'taxable_base' => 250000.00,
            'tax_rate' => 25.00,
            'tax_amount' => 62500.00,
            'advance_payments' => 50000.00,
            'amount_due' => 12500.00,
            'amount_paid' => 12500.00,
            'due_date' => now()->subMonths(2),
            'payment_date' => now()->subMonths(2)->addDays(5),
            'status' => 'paid',
            'notes' => 'Impôt des sociétés exercice ' . $lastYear,
            'created_by' => $user->id,
        ]);

        // ISOC - Current year (pending)
        TaxPayment::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'tax_type' => 'isoc',
            'year' => $currentYear,
            'quarter' => null,
            'month' => null,
            'period_label' => "Exercice {$currentYear}",
            'taxable_base' => 280000.00,
            'tax_rate' => 25.00,
            'tax_amount' => 70000.00,
            'advance_payments' => 0.00,
            'amount_due' => 70000.00,
            'amount_paid' => 0.00,
            'due_date' => now()->addMonths(3),
            'status' => 'calculated',
            'notes' => 'Impôt des sociétés exercice en cours',
            'created_by' => $user->id,
        ]);

        // IPP (Personal Income Tax) - Quarterly advance payments
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $isPaid = $quarter <= 2;
            $isOverdue = $quarter == 3;

            TaxPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'tax_type' => 'ipp',
                'year' => $currentYear,
                'quarter' => $quarter,
                'month' => null,
                'period_label' => "T{$quarter} {$currentYear}",
                'taxable_base' => 45000.00,
                'tax_rate' => 45.00,
                'tax_amount' => 20250.00,
                'advance_payments' => 0.00,
                'amount_due' => 5062.50, // Quarterly advance
                'amount_paid' => $isPaid ? 5062.50 : 0.00,
                'due_date' => now()->startOfYear()->addMonths(($quarter - 1) * 3)->addMonth()->day(10),
                'payment_date' => $isPaid ? now()->startOfYear()->addMonths(($quarter - 1) * 3)->addMonth()->day(8) : null,
                'status' => $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'pending_payment'),
                'notes' => "Versement anticipé IPP T{$quarter}",
                'created_by' => $user->id,
            ]);
        }

        // Professional Withholding Tax (Précompte professionnel) - Monthly
        for ($month = 1; $month <= 12; $month++) {
            $isPaid = $month <= now()->month - 2;
            $isPending = $month == now()->month - 1;
            $isFuture = $month >= now()->month;

            if ($isFuture) continue; // Skip future months

            TaxPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'tax_type' => 'professional_tax',
                'year' => $currentYear,
                'quarter' => ceil($month / 3),
                'month' => $month,
                'period_label' => now()->month($month)->translatedFormat('F Y'),
                'taxable_base' => 25000.00,
                'tax_rate' => 30.00,
                'tax_amount' => 7500.00,
                'advance_payments' => 0.00,
                'amount_due' => 7500.00,
                'amount_paid' => $isPaid ? 7500.00 : 0.00,
                'due_date' => now()->month($month)->addMonth()->day(15),
                'payment_date' => $isPaid ? now()->month($month)->addMonth()->day(12) : null,
                'status' => $isPaid ? 'paid' : 'pending_payment',
                'notes' => 'Précompte professionnel mensuel',
                'created_by' => $user->id,
            ]);
        }

        // VAT (TVA) - Quarterly
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            if ($quarter > ceil(now()->month / 3)) continue; // Skip future quarters

            $isPaid = $quarter < ceil(now()->month / 3);

            TaxPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'tax_type' => 'vat',
                'year' => $currentYear,
                'quarter' => $quarter,
                'month' => null,
                'period_label' => "TVA T{$quarter} {$currentYear}",
                'taxable_base' => 120000.00,
                'tax_rate' => 21.00,
                'tax_amount' => 25200.00,
                'advance_payments' => 0.00,
                'amount_due' => 25200.00,
                'amount_paid' => $isPaid ? 25200.00 : 0.00,
                'due_date' => now()->startOfYear()->addMonths($quarter * 3)->day(20),
                'payment_date' => $isPaid ? now()->startOfYear()->addMonths($quarter * 3)->day(18) : null,
                'status' => $isPaid ? 'paid' : 'pending_payment',
                'reference_number' => 'BE' . str_pad($quarter, 4, '0', STR_PAD_LEFT) . $currentYear,
                'notes' => 'Déclaration TVA trimestrielle',
                'created_by' => $user->id,
            ]);
        }

        // Withholding Tax (Précompte mobilier) - Occasional
        TaxPayment::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'tax_type' => 'withholding_tax',
            'year' => $currentYear,
            'quarter' => 2,
            'month' => 6,
            'period_label' => 'Dividendes juin ' . $currentYear,
            'taxable_base' => 50000.00,
            'tax_rate' => 30.00,
            'tax_amount' => 15000.00,
            'advance_payments' => 0.00,
            'amount_due' => 15000.00,
            'amount_paid' => 0.00,
            'due_date' => now()->addMonth(),
            'status' => 'pending_payment',
            'notes' => 'Précompte mobilier sur distribution de dividendes',
            'created_by' => $user->id,
        ]);

        $this->command->info('✓ Tax payments created');
    }

    /**
     * Create demo social security payments.
     */
    protected function createSocialSecurityPayments(Company $company, User $user): void
    {
        $this->command->info('Creating social security payments...');

        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Last year - All paid
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            SocialSecurityPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'contribution_type' => 'onss_employer',
                'year' => $lastYear,
                'quarter' => $quarter,
                'month' => null,
                'period_label' => "T{$quarter} {$lastYear}",
                'gross_salary_base' => 75000.00,
                'employee_count' => 3,
                'employer_rate' => 25.00,
                'employee_rate' => 13.07,
                'employer_contribution' => 18750.00,
                'employee_contribution' => 9802.50,
                'total_contribution' => 28552.50,
                'amount_paid' => 28552.50,
                'due_date' => now()->year($lastYear)->startOfYear()->addMonths($quarter * 3)->day(15),
                'payment_date' => now()->year($lastYear)->startOfYear()->addMonths($quarter * 3)->day(12),
                'declaration_date' => now()->year($lastYear)->startOfYear()->addMonths($quarter * 3)->day(5),
                'status' => 'paid',
                'dmfa_number' => 'DMFA-' . $lastYear . '-Q' . $quarter . '-' . substr($company->vat_number, -6),
                'onss_reference' => 'ONSS' . $lastYear . str_pad($quarter, 2, '0', STR_PAD_LEFT),
                'notes' => "Cotisations ONSS T{$quarter} {$lastYear} - 3 employés",
                'created_by' => $user->id,
            ]);
        }

        // Current year
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $currentQuarter = ceil(now()->month / 3);

            if ($quarter > $currentQuarter) {
                continue; // Skip future quarters
            }

            $isPaid = $quarter < $currentQuarter;
            $isPending = $quarter == $currentQuarter;

            // ONSS Employer
            SocialSecurityPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'contribution_type' => 'onss_employer',
                'year' => $currentYear,
                'quarter' => $quarter,
                'month' => null,
                'period_label' => "ONSS Patronale T{$quarter} {$currentYear}",
                'gross_salary_base' => 82500.00,
                'employee_count' => 3,
                'employer_rate' => 25.00,
                'employee_rate' => 0.00,
                'employer_contribution' => 20625.00,
                'employee_contribution' => 0.00,
                'total_contribution' => 20625.00,
                'amount_paid' => $isPaid ? 20625.00 : 0.00,
                'due_date' => now()->startOfYear()->addMonths($quarter * 3)->day(15),
                'payment_date' => $isPaid ? now()->startOfYear()->addMonths($quarter * 3)->day(12) : null,
                'declaration_date' => $isPaid || $isPending ? now()->startOfYear()->addMonths($quarter * 3)->day(5) : null,
                'status' => $isPaid ? 'paid' : ($isPending ? 'pending_payment' : 'calculated'),
                'dmfa_number' => $isPaid || $isPending ? 'DMFA-' . $currentYear . '-Q' . $quarter . '-' . substr($company->vat_number, -6) : null,
                'onss_reference' => 'ONSS' . $currentYear . str_pad($quarter, 2, '0', STR_PAD_LEFT),
                'notes' => "Cotisations patronales ONSS T{$quarter} - 3 employés",
                'created_by' => $user->id,
            ]);

            // ONSS Employee
            SocialSecurityPayment::create([
                'id' => Str::uuid(),
                'company_id' => $company->id,
                'contribution_type' => 'onss_employee',
                'year' => $currentYear,
                'quarter' => $quarter,
                'month' => null,
                'period_label' => "ONSS Ouvrière T{$quarter} {$currentYear}",
                'gross_salary_base' => 82500.00,
                'employee_count' => 3,
                'employer_rate' => 0.00,
                'employee_rate' => 13.07,
                'employer_contribution' => 0.00,
                'employee_contribution' => 10782.75,
                'total_contribution' => 10782.75,
                'amount_paid' => $isPaid ? 10782.75 : 0.00,
                'due_date' => now()->startOfYear()->addMonths($quarter * 3)->day(15),
                'payment_date' => $isPaid ? now()->startOfYear()->addMonths($quarter * 3)->day(12) : null,
                'declaration_date' => $isPaid || $isPending ? now()->startOfYear()->addMonths($quarter * 3)->day(5) : null,
                'status' => $isPaid ? 'paid' : ($isPending ? 'pending_payment' : 'calculated'),
                'dmfa_number' => $isPaid || $isPending ? 'DMFA-' . $currentYear . '-Q' . $quarter . '-' . substr($company->vat_number, -6) : null,
                'notes' => "Cotisations ouvrières ONSS T{$quarter} - Retenues salariales",
                'created_by' => $user->id,
            ]);

            // Special Social Security Contribution (if Q4)
            if ($quarter == 4) {
                SocialSecurityPayment::create([
                    'id' => Str::uuid(),
                    'company_id' => $company->id,
                    'contribution_type' => 'special_contribution',
                    'year' => $currentYear,
                    'quarter' => $quarter,
                    'month' => null,
                    'period_label' => "Cotisation Spéciale SS {$currentYear}",
                    'gross_salary_base' => 82500.00,
                    'employee_count' => 3,
                    'employer_rate' => 0.00,
                    'employee_rate' => 1.00,
                    'employer_contribution' => 0.00,
                    'employee_contribution' => 825.00,
                    'total_contribution' => 825.00,
                    'amount_paid' => 0.00,
                    'due_date' => now()->startOfYear()->addMonths(12)->day(31),
                    'status' => 'calculated',
                    'notes' => 'Cotisation spéciale de sécurité sociale annuelle',
                    'created_by' => $user->id,
                ]);
            }
        }

        // Occupational Accident Insurance (Annual)
        SocialSecurityPayment::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'contribution_type' => 'occupational_accident',
            'year' => $currentYear,
            'quarter' => 4, // Annual contribution reported in Q4
            'month' => 12,
            'period_label' => "Assurance Accidents du Travail {$currentYear}",
            'gross_salary_base' => 330000.00, // Annual total
            'employee_count' => 3,
            'employer_rate' => 1.50,
            'employee_rate' => 0.00,
            'employer_contribution' => 4950.00,
            'employee_contribution' => 0.00,
            'total_contribution' => 4950.00,
            'amount_paid' => 0.00,
            'due_date' => now()->month(12)->day(31),
            'status' => 'calculated',
            'notes' => 'Prime annuelle assurance accidents du travail',
            'created_by' => $user->id,
        ]);

        $this->command->info('✓ Social security payments created');
    }
}
