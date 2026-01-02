<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Payslip;
use App\Models\Product;
use App\Models\Quote;
use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComprehensiveDemoSeeder extends Seeder
{
    protected Company $company;
    protected User $user;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating comprehensive demo data for admin@demo.be...');

        // Get demo company and user
        $this->company = Company::where('name', 'LIKE', '%Demo%')->first();
        $this->user = User::where('email', 'admin@demo.be')->first();

        if (!$this->company || !$this->user) {
            $this->command->error('Demo company or user not found!');
            return;
        }

        // Clean existing demo data (delete child records first)
        $this->command->info('Cleaning existing demo data...');
        Payslip::where('company_id', $this->company->id)->forceDelete();
        CreditNote::where('company_id', $this->company->id)->forceDelete();
        Quote::where('company_id', $this->company->id)->forceDelete();
        Invoice::where('company_id', $this->company->id)->forceDelete();
        RecurringInvoice::where('company_id', $this->company->id)->forceDelete();
        Employee::where('company_id', $this->company->id)->forceDelete();
        Product::where('company_id', $this->company->id)->forceDelete();
        Partner::where('company_id', $this->company->id)->forceDelete();

        // Create demo data for all modules
        $this->createCustomers();
        $this->createSuppliers();
        $this->createProducts();
        $this->createSalesInvoices();
        $this->createQuotes();
        $this->createCreditNotes();
        $this->createPurchaseInvoices();
        $this->createRecurringInvoices();
        $this->createEmployees();
        $this->createPayslips();

        $this->command->info('✅ Comprehensive demo data created successfully!');
        $this->command->info('📧 Login: admin@demo.be');
        $this->command->info('🏢 Company: ' . $this->company->name);
    }

    /**
     * Create demo customers.
     */
    protected function createCustomers(): void
    {
        $this->command->info('Creating customers...');

        $customers = [
            [
                'name' => 'Chocolaterie Neuhaus SA',
                'vat_number' => 'BE0403214321',
                'email' => 'facturation@neuhaus.be',
                'phone' => '+32 2 512 63 59',
                'address' => 'Rue au Beurre 4',
                'city' => 'Bruxelles',
                'postal_code' => '1000',
                'country' => 'BE',
            ],
            [
                'name' => 'Brasserie Duvel Moortgat NV',
                'vat_number' => 'BE0400630426',
                'email' => 'admin@duvel.be',
                'phone' => '+32 15 30 91 11',
                'address' => 'Breendonkdorp 58',
                'city' => 'Puurs-Sint-Amands',
                'postal_code' => '2870',
                'country' => 'BE',
            ],
            [
                'name' => 'Technologie Proximus SA',
                'vat_number' => 'BE0202239951',
                'email' => 'business@proximus.com',
                'phone' => '+32 2 202 41 11',
                'address' => 'Boulevard du Roi Albert II 27',
                'city' => 'Bruxelles',
                'postal_code' => '1030',
                'country' => 'BE',
            ],
            [
                'name' => 'Delhaize Group SA',
                'vat_number' => 'BE0402206045',
                'email' => 'contact@delhaizegroup.com',
                'phone' => '+32 2 412 21 11',
                'address' => 'Rue Osseghemstraat 53',
                'city' => 'Bruxelles',
                'postal_code' => '1080',
                'country' => 'BE',
            ],
            [
                'name' => 'BNP Paribas Fortis',
                'vat_number' => 'BE0403199702',
                'email' => 'corporate@bnpparibasfortis.com',
                'phone' => '+32 2 433 41 11',
                'address' => 'Rue Royale 20',
                'city' => 'Bruxelles',
                'postal_code' => '1000',
                'country' => 'BE',
            ],
        ];

        foreach ($customers as $customerData) {
            Partner::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'type' => 'customer',
                'is_company' => true,
                'name' => $customerData['name'],
                'vat_number' => $customerData['vat_number'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'],
                'street' => $customerData['address'],
                'city' => $customerData['city'],
                'postal_code' => $customerData['postal_code'],
                'country_code' => $customerData['country'],
                'payment_terms_days' => 30,
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ ' . count($customers) . ' customers created');
    }

    /**
     * Create demo suppliers.
     */
    protected function createSuppliers(): void
    {
        $this->command->info('Creating suppliers...');

        $suppliers = [
            [
                'name' => 'Microsoft Belgium BVBA',
                'vat_number' => 'BE0890645235',
                'email' => 'invoices@microsoft.be',
                'phone' => '+32 2 513 29 11',
            ],
            [
                'name' => 'Telenet Group NV',
                'vat_number' => 'BE0473416507',
                'email' => 'billing@telenet.be',
                'phone' => '+32 15 33 30 00',
            ],
            [
                'name' => 'Office Depot Belgium',
                'vat_number' => 'BE0437495679',
                'email' => 'facturation@officedepot.be',
                'phone' => '+32 2 706 19 00',
            ],
            [
                'name' => 'Colruyt Group NV',
                'vat_number' => 'BE0400378485',
                'email' => 'suppliers@colruytgroup.com',
                'phone' => '+32 2 363 55 45',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Partner::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'type' => 'supplier',
                'is_company' => true,
                'name' => $supplierData['name'],
                'vat_number' => $supplierData['vat_number'],
                'email' => $supplierData['email'],
                'phone' => $supplierData['phone'],
                'payment_terms_days' => 30,
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ ' . count($suppliers) . ' suppliers created');
    }

    /**
     * Create demo products and services.
     */
    protected function createProducts(): void
    {
        $this->command->info('Creating products and services...');

        $products = [
            [
                'name' => 'Consulting IT - Journée',
                'description' => 'Service de consulting informatique (tarif journalier)',
                'type' => 'service',
                'unit_price' => 850.00,
                'vat_rate' => 21.00,
            ],
            [
                'name' => 'Développement Web - Heure',
                'description' => 'Développement d\'applications web sur mesure',
                'type' => 'service',
                'unit_price' => 95.00,
                'vat_rate' => 21.00,
            ],
            [
                'name' => 'Formation Laravel - Journée',
                'description' => 'Formation professionnelle Laravel PHP',
                'type' => 'service',
                'unit_price' => 1200.00,
                'vat_rate' => 21.00,
            ],
            [
                'name' => 'Hébergement Cloud - Mensuel',
                'description' => 'Hébergement cloud professionnel',
                'type' => 'service',
                'unit_price' => 299.00,
                'vat_rate' => 21.00,
            ],
            [
                'name' => 'Licence Logiciel Pro',
                'description' => 'Licence annuelle logiciel professionnel',
                'type' => 'product',
                'unit_price' => 599.00,
                'vat_rate' => 21.00,
            ],
            [
                'name' => 'Support Technique - Mois',
                'description' => 'Support technique mensuel (8h/mois)',
                'type' => 'service',
                'unit_price' => 450.00,
                'vat_rate' => 21.00,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'name' => $productData['name'],
                'description' => $productData['description'],
                'type' => $productData['type'],
                'unit_price' => $productData['unit_price'],
                'vat_rate' => $productData['vat_rate'],
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ ' . count($products) . ' products/services created');
    }

    /**
     * Create demo sales invoices.
     */
    protected function createSalesInvoices(): void
    {
        $this->command->info('Creating sales invoices...');

        $customers = Partner::where('company_id', $this->company->id)
            ->where('type', 'customer')
            ->get();

        $products = Product::where('company_id', $this->company->id)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No customers or products found, skipping invoices');
            return;
        }

        $count = 0;
        $currentYear = now()->year;

        // Create invoices for last 6 months
        for ($month = 1; $month <= 6; $month++) {
            foreach ($customers->take(3) as $customer) {
                $invoiceDate = now()->subMonths(7 - $month)->day(15);
                $dueDate = $invoiceDate->copy()->addDays(30);

                $invoice = Invoice::create([
                    'id' => Str::uuid(),
                    'company_id' => $this->company->id,
                    'partner_id' => $customer->id,
                    'type' => 'out',
                    'document_type' => 'invoice',
                    'invoice_number' => 'INV-' . $currentYear . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'status' => $month <= 4 ? 'paid' : ($month == 5 ? 'sent' : 'draft'),
                    'currency' => 'EUR',
                    'created_by' => $this->user->id,
                ]);

                // Add 2-4 random line items
                $lineCount = rand(2, 4);
                for ($i = 0; $i < $lineCount; $i++) {
                    $product = $products->random();
                    $quantity = rand(1, 10);

                    $invoice->lines()->create([
                        'line_number' => $i + 1,
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->unit_price,
                        'vat_rate' => $product->vat_rate,
                    ]);
                }

                $invoice->calculateTotals();
                $invoice->save();

                $count++;
            }
        }

        $this->command->info('✓ ' . $count . ' sales invoices created');
    }

    /**
     * Create demo quotes.
     */
    protected function createQuotes(): void
    {
        $this->command->info('Creating quotes...');

        $customers = Partner::where('company_id', $this->company->id)
            ->where('type', 'customer')
            ->get();

        $products = Product::where('company_id', $this->company->id)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['draft', 'sent', 'accepted', 'rejected'];

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $quoteDate = now()->subDays(rand(1, 90));
            $validUntil = $quoteDate->copy()->addDays(30);

            $quote = Quote::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'partner_id' => $customer->id,
                'quote_number' => 'DEVIS-' . now()->year . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'quote_date' => $quoteDate,
                'valid_until' => $validUntil,
                'status' => $statuses[array_rand($statuses)],
                'currency' => 'EUR',
                'created_by' => $this->user->id,
            ]);

            // Add line items
            $lineCount = rand(2, 5);
            for ($j = 0; $j < $lineCount; $j++) {
                $product = $products->random();

                $quote->lines()->create([
                    'line_number' => $j + 1,
                    'description' => $product->name,
                    'quantity' => rand(1, 10),
                    'unit_price' => $product->unit_price,
                    'vat_rate' => $product->vat_rate,
                ]);
            }

            $quote->calculateTotals();
            $quote->save();
        }

        $this->command->info('✓ 10 quotes created');
    }

    /**
     * Create demo credit notes.
     */
    protected function createCreditNotes(): void
    {
        $this->command->info('Creating credit notes...');

        $invoices = Invoice::where('company_id', $this->company->id)
            ->where('type', 'out')
            ->where('status', 'paid')
            ->with('lines')
            ->take(2)
            ->get();

        if ($invoices->isEmpty()) {
            return;
        }

        foreach ($invoices as $invoice) {
            $creditNote = CreditNote::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'partner_id' => $invoice->partner_id,
                'invoice_id' => $invoice->id,
                'credit_note_number' => 'CN-' . now()->year . '-' . str_pad(CreditNote::count() + 1, 4, '0', STR_PAD_LEFT),
                'credit_note_date' => now()->subDays(rand(5, 15)),
                'status' => 'sent',
                'reason' => 'Retour marchandise défectueuse',
                'created_by' => $this->user->id,
            ]);

            // Copy first line from invoice (partial credit)
            $firstLine = $invoice->lines->first();
            if ($firstLine) {
                $creditNote->lines()->create([
                    'line_number' => 1,
                    'description' => $firstLine->description . ' (Retour)',
                    'quantity' => 1,
                    'unit_price' => $firstLine->unit_price,
                    'vat_rate' => $firstLine->vat_rate,
                ]);
            }

            $creditNote->calculateTotals();
            $creditNote->save();
        }

        $this->command->info('✓ 2 credit notes created');
    }

    /**
     * Create demo purchase invoices.
     */
    protected function createPurchaseInvoices(): void
    {
        $this->command->info('Creating purchase invoices...');

        $suppliers = Partner::where('company_id', $this->company->id)
            ->where('type', 'supplier')
            ->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        $count = 0;
        for ($month = 1; $month <= 6; $month++) {
            foreach ($suppliers->take(2) as $supplier) {
                $invoiceDate = now()->subMonths(7 - $month)->day(rand(1, 28));

                $invoice = Invoice::create([
                    'id' => Str::uuid(),
                    'company_id' => $this->company->id,
                    'partner_id' => $supplier->id,
                    'type' => 'in',
                    'document_type' => 'invoice',
                    'invoice_number' => 'FACH-' . $supplier->id . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $invoiceDate->copy()->addDays(30),
                    'status' => $month <= 4 ? 'paid' : 'received',
                    'currency' => 'EUR',
                    'created_by' => $this->user->id,
                ]);

                // Add expense lines
                $expenses = [
                    ['desc' => 'Fournitures de bureau', 'price' => 234.50],
                    ['desc' => 'Abonnement logiciel', 'price' => 299.00],
                    ['desc' => 'Services cloud', 'price' => 450.00],
                ];

                foreach ($expenses as $idx => $expense) {
                    $invoice->lines()->create([
                        'line_number' => $idx + 1,
                        'description' => $expense['desc'],
                        'quantity' => 1,
                        'unit_price' => $expense['price'],
                        'vat_rate' => 21.00,
                    ]);
                }

                $invoice->calculateTotals();
                $invoice->save();

                $count++;
            }
        }

        $this->command->info('✓ ' . $count . ' purchase invoices created');
    }

    /**
     * Create demo recurring invoices.
     */
    protected function createRecurringInvoices(): void
    {
        $this->command->info('Creating recurring invoices...');

        $customers = Partner::where('company_id', $this->company->id)
            ->where('type', 'customer')
            ->take(3)
            ->get();

        if ($customers->isEmpty()) {
            return;
        }

        $products = Product::where('company_id', $this->company->id)->get();

        foreach ($customers as $idx => $customer) {
            $product = $products->random();

            RecurringInvoice::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'partner_id' => $customer->id,
                'name' => 'Abonnement mensuel - ' . $customer->name,
                'description' => 'Facturation récurrente mensuelle',
                'frequency' => 'monthly',
                'interval' => 1,
                'day_of_month' => 1,
                'start_date' => now()->startOfMonth(),
                'next_invoice_date' => now()->addMonth()->startOfMonth(),
                'payment_terms_days' => 30,
                'auto_send_email' => true,
                'auto_validate' => false,
                'line_items' => [
                    [
                        'description' => $product->name . ' - {mois} {annee}',
                        'quantity' => 1,
                        'unit_price' => $product->unit_price,
                        'vat_rate' => 21.00,
                    ],
                ],
                'subtotal' => $product->unit_price,
                'vat_amount' => $product->unit_price * 0.21,
                'total' => $product->unit_price * 1.21,
                'status' => 'active',
                'invoices_generated_count' => $idx,
            ]);
        }

        $this->command->info('✓ 3 recurring invoices created');
    }

    /**
     * Create demo employees.
     */
    protected function createEmployees(): void
    {
        $this->command->info('Creating employees...');

        $employees = [
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@demo.be',
                'national_number' => '85.07.15-123.45',
                'job_title' => 'Développeur Senior',
                'gross_salary' => 4500.00,
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@demo.be',
                'national_number' => '90.03.22-987.65',
                'job_title' => 'Chef de Projet',
                'gross_salary' => 5200.00,
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Bernard',
                'email' => 'pierre.bernard@demo.be',
                'national_number' => '88.11.30-456.78',
                'job_title' => 'Commercial',
                'gross_salary' => 3800.00,
            ],
        ];

        foreach ($employees as $empData) {
            $hireDate = now()->subYears(rand(1, 5));

            // Create employee
            $employee = Employee::create([
                'id' => Str::uuid(),
                'company_id' => $this->company->id,
                'employee_number' => Employee::generateEmployeeNumber($this->company),
                'first_name' => $empData['first_name'],
                'last_name' => $empData['last_name'],
                'email' => $empData['email'],
                'national_number' => $empData['national_number'],
                'status' => 'active',
                'hire_date' => $hireDate,
                'birth_date' => now()->subYears(rand(25, 50)),
                'phone' => '+32 2 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'street' => 'Rue de la ' . ['Loi', 'Paix', 'Liberté'][array_rand(['Loi', 'Paix', 'Liberté'])],
                'house_number' => rand(1, 200),
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
            ]);

            // Create employment contract
            EmploymentContract::create([
                'id' => Str::uuid(),
                'employee_id' => $employee->id,
                'company_id' => $this->company->id,
                'contract_number' => 'CTR-2025-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT),
                'contract_type' => 'cdi',
                'status' => 'active',
                'start_date' => $hireDate,
                'job_title' => $empData['job_title'],
                'gross_monthly_salary' => $empData['gross_salary'],
            ]);
        }

        $this->command->info('✓ ' . count($employees) . ' employees created');
    }

    /**
     * Create demo payslips.
     */
    protected function createPayslips(): void
    {
        $this->command->info('Creating payslips...');

        $employees = Employee::where('company_id', $this->company->id)
            ->with('activeContract')
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        $count = 0;
        $payslipNumber = 1;
        // Create payslips for last 3 months
        for ($month = 1; $month <= 3; $month++) {
            foreach ($employees as $employee) {
                $grossSalary = $employee->getCurrentSalary();

                if (!$grossSalary) {
                    continue; // Skip if no active contract
                }

                $paymentDate = now()->subMonths(4 - $month)->endOfMonth();

                $employeeSS = $grossSalary * 0.1307; // 13.07%
                $professionalTax = ($grossSalary - $employeeSS) * 0.30; // ~30%
                $netSalary = $grossSalary - $employeeSS - $professionalTax;
                $employerSS = $grossSalary * 0.25; // 25%

                Payslip::create([
                    'id' => Str::uuid(),
                    'company_id' => $this->company->id,
                    'employee_id' => $employee->id,
                    'payslip_number' => 'PAYSLIP-' . $paymentDate->format('Y-m') . '-' . str_pad($payslipNumber, 3, '0', STR_PAD_LEFT),
                    'period' => $paymentDate->format('Y-m'),
                    'year' => $paymentDate->year,
                    'month' => $paymentDate->month,
                    'payment_date' => $paymentDate,
                    'gross_total' => $grossSalary,
                    'employee_social_security' => $employeeSS,
                    'employer_social_security' => $employerSS,
                    'professional_tax' => $professionalTax,
                    'net_salary' => $netSalary,
                    'status' => 'paid',
                ]);

                $count++;
                $payslipNumber++;
            }
        }

        $this->command->info('✓ ' . $count . ' payslips created');
    }
}
