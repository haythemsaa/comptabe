<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Partner;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use App\Models\VatCode;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Report;
use App\Models\AccountingFirm;
use App\Models\ClientMandate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo data...');

        // ========================================
        // COMPANIES
        // ========================================
        $this->command->info('Creating companies...');

        $company1 = Company::create([
            'name' => 'TechBelgium SPRL',
            'legal_form' => 'SPRL',
            'vat_number' => 'BE0123456789',
            'enterprise_number' => '0123.456.789',
            'email' => 'contact@techbelgium.be',
            'phone' => '+32 2 123 45 67',
            'street' => 'Rue de la Loi',
            'house_number' => '42',
            'city' => 'Bruxelles',
            'postal_code' => '1000',
            'country_code' => 'BE',
            'peppol_id' => '0208:0123456789',
            'peppol_registered' => true,
            'peppol_registered_at' => now(),
            'default_iban' => 'BE68739012345678',
            'default_bic' => 'KREDBEBB',
            'fiscal_year_start_month' => 1,
            'vat_regime' => 'normal',
            'vat_periodicity' => 'quarterly',
            'settings' => [
                'invoice_prefix' => 'TECH',
                'invoice_next_number' => 2024001,
                'default_payment_terms' => 30,
                'default_vat_rate' => 21,
            ],
        ]);

        $company2 = Company::create([
            'name' => 'Consulting Pro SA',
            'legal_form' => 'SA',
            'vat_number' => 'BE0987654321',
            'enterprise_number' => '0987.654.321',
            'email' => 'info@consultingpro.be',
            'phone' => '+32 3 987 65 43',
            'street' => 'Avenue Louise',
            'house_number' => '200',
            'city' => 'Bruxelles',
            'postal_code' => '1050',
            'country_code' => 'BE',
            'fiscal_year_start_month' => 1,
            'vat_regime' => 'normal',
            'vat_periodicity' => 'quarterly',
            'settings' => [
                'invoice_prefix' => 'CP',
                'invoice_next_number' => 2024001,
            ],
        ]);

        // ========================================
        // USERS
        // ========================================
        $this->command->info('Creating users...');

        // Admin
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Demo',
            'email' => 'admin@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->companies()->attach($company1->id, ['role' => 'admin', 'is_default' => true]);
        $admin->companies()->attach($company2->id, ['role' => 'admin', 'is_default' => false]);

        // Comptable
        $comptable = User::create([
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'email' => 'comptable@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $comptable->companies()->attach($company1->id, ['role' => 'accountant', 'is_default' => true]);

        // Manager
        $manager = User::create([
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'email' => 'manager@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $manager->companies()->attach($company1->id, ['role' => 'user', 'is_default' => true]);

        // Viewer
        $viewer = User::create([
            'first_name' => 'Sophie',
            'last_name' => 'Leroy',
            'email' => 'viewer@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->companies()->attach($company1->id, ['role' => 'readonly', 'is_default' => true]);

        // ========================================
        // PLAN COMPTABLE BELGE (PCMN)
        // ========================================
        $this->command->info('Creating chart of accounts (PCMN)...');
        $this->createChartOfAccounts($company1);
        $this->createChartOfAccounts($company2);

        // ========================================
        // CODES TVA BELGES
        // ========================================
        $this->command->info('Creating Belgian VAT codes...');
        $this->createVatCodes($company1);
        $this->createVatCodes($company2);

        // ========================================
        // PARTNERS
        // ========================================
        $this->command->info('Creating partners...');

        // Clients
        $clients = [
            ['name' => 'Proximus SA', 'vat_number' => 'BE0202239951', 'city' => 'Bruxelles'],
            ['name' => 'Colruyt Group', 'vat_number' => 'BE0400378485', 'city' => 'Halle'],
            ['name' => 'Delhaize Le Lion', 'vat_number' => 'BE0402206045', 'city' => 'Zellik'],
            ['name' => 'Solvay SA', 'vat_number' => 'BE0403091220', 'city' => 'Bruxelles'],
            ['name' => 'UCB SA', 'vat_number' => 'BE0403053608', 'city' => 'Bruxelles'],
            ['name' => 'Barco NV', 'vat_number' => 'BE0473191041', 'city' => 'Kortrijk'],
            ['name' => 'Umicore SA', 'vat_number' => 'BE0401574852', 'city' => 'Bruxelles'],
            ['name' => 'KBC Groupe', 'vat_number' => 'BE0403227515', 'city' => 'Bruxelles'],
            ['name' => 'Ageas SA', 'vat_number' => 'BE0451406524', 'city' => 'Bruxelles'],
            ['name' => 'Elia System Operator', 'vat_number' => 'BE0476388378', 'city' => 'Bruxelles'],
        ];

        // Fournisseurs
        $suppliers = [
            ['name' => 'Microsoft Belgium', 'vat_number' => 'BE0440653281', 'city' => 'Zaventem'],
            ['name' => 'Amazon EU SARL', 'vat_number' => 'LU26375245', 'city' => 'Luxembourg'],
            ['name' => 'Office Depot Belgium', 'vat_number' => 'BE0406035847', 'city' => 'Vilvoorde'],
            ['name' => 'Telecom Provider', 'vat_number' => 'BE0202239952', 'city' => 'Bruxelles'],
            ['name' => 'Engie Electrabel', 'vat_number' => 'BE0403170701', 'city' => 'Bruxelles'],
            ['name' => 'Luminus', 'vat_number' => 'BE0471811661', 'city' => 'Bruxelles'],
            ['name' => 'Securitas Belgium', 'vat_number' => 'BE0406832527', 'city' => 'Groot-Bijgaarden'],
            ['name' => 'Sodexo Belgium', 'vat_number' => 'BE0403383418', 'city' => 'Bruxelles'],
        ];

        $customerPartners = [];
        $supplierPartners = [];

        foreach ($clients as $index => $partnerData) {
            $partner = Partner::create([
                'company_id' => $company1->id,
                'type' => 'customer',
                'reference' => 'C' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'name' => $partnerData['name'],
                'vat_number' => $partnerData['vat_number'],
                'is_company' => true,
                'email' => strtolower(preg_replace('/[^a-z0-9]/i', '', $partnerData['name'])) . '@example.com',
                'phone' => '+32 2 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'street' => 'Rue ' . fake()->streetName(),
                'house_number' => (string) rand(1, 200),
                'city' => $partnerData['city'],
                'postal_code' => (string) rand(1000, 9999),
                'country_code' => 'BE',
                'payment_terms_days' => rand(1, 3) * 15,
                'peppol_id' => '0208:' . substr($partnerData['vat_number'], 2),
                'peppol_capable' => true,
            ]);
            $customerPartners[] = $partner;
        }

        foreach ($suppliers as $index => $partnerData) {
            $partner = Partner::create([
                'company_id' => $company1->id,
                'type' => 'supplier',
                'reference' => 'F' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'name' => $partnerData['name'],
                'vat_number' => $partnerData['vat_number'],
                'is_company' => true,
                'email' => strtolower(preg_replace('/[^a-z0-9]/i', '', $partnerData['name'])) . '@supplier.com',
                'phone' => '+32 2 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'street' => 'Avenue ' . fake()->streetName(),
                'house_number' => (string) rand(1, 200),
                'city' => $partnerData['city'],
                'postal_code' => (string) rand(1000, 9999),
                'country_code' => str_starts_with($partnerData['vat_number'], 'LU') ? 'LU' : 'BE',
                'payment_terms_days' => 30,
            ]);
            $supplierPartners[] = $partner;
        }

        // ========================================
        // BANK ACCOUNTS
        // ========================================
        $this->command->info('Creating bank accounts...');

        $bankAccount1 = BankAccount::create([
            'company_id' => $company1->id,
            'name' => 'Compte courant KBC',
            'iban' => 'BE68739012345678',
            'bic' => 'KREDBEBB',
            'bank_name' => 'KBC',
            'is_default' => true,
            'is_active' => true,
        ]);

        $bankAccount2 = BankAccount::create([
            'company_id' => $company1->id,
            'name' => 'Compte épargne BNP',
            'iban' => 'BE39001234567890',
            'bic' => 'GEBABEBB',
            'bank_name' => 'BNP Paribas Fortis',
            'is_active' => true,
        ]);

        // ========================================
        // INVOICES
        // ========================================
        $this->command->info('Creating invoices...');

        // Factures de vente (12 derniers mois)
        $salesInvoices = [];
        for ($i = 0; $i < 50; $i++) {
            $date = Carbon::now()->subDays(rand(1, 365));
            $partner = $customerPartners[array_rand($customerPartners)];
            $status = $this->getRandomInvoiceStatus($date);
            $structuredComm = '+++' . rand(100, 999) . '/' . rand(1000, 9999) . '/' . rand(10000, 99999) . '+++';

            $invoice = Invoice::create([
                'company_id' => $company1->id,
                'partner_id' => $partner->id,
                'type' => 'out',
                'document_type' => 'invoice',
                'invoice_number' => 'TECH-' . $date->format('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $date,
                'due_date' => $date->copy()->addDays($partner->payment_terms_days ?? 30),
                'reference' => 'PO-' . rand(10000, 99999),
                'status' => $status,
                'currency' => 'EUR',
                'structured_communication' => $structuredComm,
                'notes' => fake()->optional(0.3)->sentence(),
            ]);

            // Lignes de facture
            $numLines = rand(1, 5);
            $services = [
                ['desc' => 'Développement web', 'price' => rand(500, 2000)],
                ['desc' => 'Consulting IT', 'price' => rand(800, 1500)],
                ['desc' => 'Maintenance serveurs', 'price' => rand(200, 800)],
                ['desc' => 'Support technique', 'price' => rand(100, 500)],
                ['desc' => 'Formation utilisateurs', 'price' => rand(300, 1000)],
                ['desc' => 'Audit sécurité', 'price' => rand(1000, 3000)],
                ['desc' => 'Migration cloud', 'price' => rand(2000, 5000)],
                ['desc' => 'Licence logiciel', 'price' => rand(100, 1000)],
            ];

            $totalExclVat = 0;
            $totalVat = 0;

            for ($j = 0; $j < $numLines; $j++) {
                $service = $services[array_rand($services)];
                $quantity = rand(1, 10);
                $unitPrice = $service['price'];
                $vatRate = [6, 12, 21][array_rand([6, 12, 21])];
                $lineTotal = $quantity * $unitPrice;
                $lineVat = round($lineTotal * $vatRate / 100, 2);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'line_number' => $j + 1,
                    'description' => $service['desc'],
                    'quantity' => $quantity,
                    'unit_code' => 'HUR', // Hour
                    'unit_price' => $unitPrice,
                    'vat_category' => 'S', // Standard rate
                    'vat_rate' => $vatRate,
                    'vat_amount' => $lineVat,
                    'line_amount' => $lineTotal,
                ]);

                $totalExclVat += $lineTotal;
                $totalVat += $lineVat;
            }

            $totalAmount = $totalExclVat + $totalVat;
            $amountPaid = $status === 'paid' ? $totalAmount : ($status === 'partial' ? round($totalAmount * 0.5, 2) : 0);

            $invoice->update([
                'total_excl_vat' => $totalExclVat,
                'total_vat' => $totalVat,
                'total_incl_vat' => $totalAmount,
                'amount_paid' => $amountPaid,
                'amount_due' => $totalAmount - $amountPaid,
            ]);

            $salesInvoices[] = $invoice;
        }

        // Factures d'achat
        $purchaseInvoices = [];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays(rand(1, 365));
            $partner = $supplierPartners[array_rand($supplierPartners)];
            $status = $this->getRandomInvoiceStatus($date);

            $invoice = Invoice::create([
                'company_id' => $company1->id,
                'partner_id' => $partner->id,
                'type' => 'in',
                'document_type' => 'invoice',
                'invoice_number' => 'ACH-' . $date->format('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => $date,
                'due_date' => $date->copy()->addDays(30),
                'reference' => fake()->optional()->numerify('INV-#####'),
                'status' => $status,
                'currency' => 'EUR',
            ]);

            $expenses = [
                ['desc' => 'Fournitures de bureau', 'price' => rand(50, 500)],
                ['desc' => 'Abonnement télécom', 'price' => rand(50, 200)],
                ['desc' => 'Électricité', 'price' => rand(100, 500)],
                ['desc' => 'Loyer bureaux', 'price' => rand(1000, 3000)],
                ['desc' => 'Assurance RC', 'price' => rand(200, 800)],
                ['desc' => 'Licences Microsoft 365', 'price' => rand(100, 500)],
                ['desc' => 'Hébergement cloud', 'price' => rand(200, 1000)],
            ];

            $expense = $expenses[array_rand($expenses)];
            $quantity = rand(1, 3);
            $unitPrice = $expense['price'];
            $vatRate = 21;
            $lineTotal = $quantity * $unitPrice;
            $lineVat = round($lineTotal * $vatRate / 100, 2);

            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'line_number' => 1,
                'description' => $expense['desc'],
                'quantity' => $quantity,
                'unit_code' => 'C62', // Unit
                'unit_price' => $unitPrice,
                'vat_category' => 'S',
                'vat_rate' => $vatRate,
                'vat_amount' => $lineVat,
                'line_amount' => $lineTotal,
            ]);

            $totalAmount = $lineTotal + $lineVat;
            $amountPaid = $status === 'paid' ? $totalAmount : 0;

            $invoice->update([
                'total_excl_vat' => $lineTotal,
                'total_vat' => $lineVat,
                'total_incl_vat' => $totalAmount,
                'amount_paid' => $amountPaid,
                'amount_due' => $totalAmount - $amountPaid,
            ]);

            $purchaseInvoices[] = $invoice;
        }

        // ========================================
        // BANK STATEMENTS & TRANSACTIONS
        // ========================================
        $this->command->info('Creating bank statements and transactions...');

        // Create a bank statement for the current month
        $bankStatement = BankStatement::create([
            'bank_account_id' => $bankAccount1->id,
            'statement_number' => '2024-12-001',
            'statement_date' => Carbon::now(),
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'opening_balance' => 35000.00,
            'closing_balance' => 45678.90,
            'total_debit' => 5000.00,
            'total_credit' => 15678.90,
            'source' => 'manual',
            'is_processed' => true,
            'processed_at' => now(),
        ]);

        // Transactions liées aux factures payées
        $sequenceNumber = 1;
        foreach (array_slice($salesInvoices, 0, 20) as $invoice) {
            if ($invoice->status === 'paid') {
                BankTransaction::create([
                    'bank_statement_id' => $bankStatement->id,
                    'bank_account_id' => $bankAccount1->id,
                    'sequence_number' => $sequenceNumber++,
                    'transaction_date' => $invoice->due_date->copy()->subDays(rand(0, 5)),
                    'value_date' => $invoice->due_date,
                    'amount' => $invoice->total_incl_vat,
                    'currency' => 'EUR',
                    'communication' => 'Paiement client ' . $invoice->partner->name,
                    'counterparty_name' => $invoice->partner->name,
                    'counterparty_account' => 'BE' . rand(10, 99) . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999),
                    'structured_communication' => $invoice->structured_communication,
                    'bank_reference' => $invoice->invoice_number,
                    'reconciliation_status' => 'matched',
                    'matched_invoice_id' => $invoice->id,
                ]);
            }
        }

        // Transactions non rapprochées
        for ($i = 0; $i < 15; $i++) {
            $isCredit = rand(0, 1) === 1;
            BankTransaction::create([
                'bank_statement_id' => $bankStatement->id,
                'bank_account_id' => $bankAccount1->id,
                'sequence_number' => $sequenceNumber++,
                'transaction_date' => Carbon::now()->subDays(rand(1, 60)),
                'value_date' => Carbon::now()->subDays(rand(1, 60)),
                'amount' => $isCredit ? rand(100, 5000) : -rand(50, 2000),
                'currency' => 'EUR',
                'communication' => $isCredit ? 'Virement entrant' : fake()->randomElement(['Frais bancaires', 'Paiement divers', 'Achat matériel']),
                'counterparty_name' => fake()->company(),
                'reconciliation_status' => 'pending',
            ]);
        }

        // ========================================
        // SAVED REPORTS
        // ========================================
        $this->command->info('Creating saved reports...');

        Report::create([
            'company_id' => $company1->id,
            'user_id' => $admin->id,
            'name' => 'Compte de résultat mensuel',
            'type' => 'profit_loss',
            'description' => 'Rapport P&L généré automatiquement chaque mois',
            'config' => [
                'date_from' => 'month_start',
                'date_to' => 'month_end',
                'format' => 'pdf',
            ],
            'schedule' => [
                'frequency' => 'monthly',
                'send_email' => true,
                'emails' => 'admin@demo.be',
            ],
            'is_favorite' => true,
            'is_public' => true,
        ]);

        Report::create([
            'company_id' => $company1->id,
            'user_id' => $comptable->id,
            'name' => 'Déclaration TVA trimestrielle',
            'type' => 'vat_summary',
            'description' => 'Préparation déclaration TVA',
            'config' => [
                'date_from' => 'quarter_start',
                'date_to' => 'quarter_end',
                'format' => 'pdf',
            ],
            'schedule' => [
                'frequency' => 'quarterly',
                'send_email' => true,
            ],
            'is_favorite' => true,
            'is_public' => false,
        ]);

        Report::create([
            'company_id' => $company1->id,
            'user_id' => $admin->id,
            'name' => 'Balance âgée clients',
            'type' => 'aged_receivables',
            'description' => 'Suivi des créances clients',
            'config' => [
                'date_from' => 'year_start',
                'date_to' => 'today',
                'format' => 'xlsx',
            ],
            'is_favorite' => false,
            'is_public' => true,
        ]);

        // ========================================
        // ACCOUNTING FIRM (EXPERT COMPTABLE)
        // ========================================
        $this->command->info('Creating accounting firm demo...');
        $this->createAccountingFirmDemo($company1, $admin);

        $this->command->info('Demo data created successfully!');
        $this->command->newLine();
        $this->command->info('=== CREDENTIALS ===');
        $this->command->table(
            ['Role', 'Email', 'Password', 'Type'],
            [
                ['Admin Entreprise', 'admin@demo.be', 'Demo2024!', 'Entreprise'],
                ['Comptable', 'comptable@demo.be', 'Demo2024!', 'Entreprise'],
                ['Manager', 'manager@demo.be', 'Demo2024!', 'Entreprise'],
                ['Viewer', 'viewer@demo.be', 'Demo2024!', 'Entreprise'],
                ['Expert Comptable', 'expert@demo.be', 'Demo2024!', 'Cabinet'],
                ['Collaborateur', 'collab@demo.be', 'Demo2024!', 'Cabinet'],
            ]
        );
        $this->command->newLine();
        $this->command->info('=== ACCOUNTING FIRM ACCESS ===');
        $this->command->info('URL: /firm');
        $this->command->info('Login with expert@demo.be to access the accounting firm portal');
    }

    protected function getRandomInvoiceStatus(Carbon $date): string
    {
        $daysOld = $date->diffInDays(now());

        if ($daysOld > 60) {
            return fake()->randomElement(['paid', 'paid', 'paid', 'validated']);
        } elseif ($daysOld > 30) {
            return fake()->randomElement(['paid', 'paid', 'validated', 'sent', 'partial']);
        } else {
            return fake()->randomElement(['draft', 'validated', 'sent', 'paid']);
        }
    }

    protected function createChartOfAccounts(Company $company): void
    {
        $accounts = [
            // Classe 1 - Fonds propres
            ['account_number' => '100', 'name' => 'Capital souscrit', 'type' => 'equity'],
            ['account_number' => '130', 'name' => 'Réserve légale', 'type' => 'equity'],
            ['account_number' => '133', 'name' => 'Réserves disponibles', 'type' => 'equity'],
            ['account_number' => '140', 'name' => 'Bénéfice reporté', 'type' => 'equity'],
            ['account_number' => '141', 'name' => 'Perte reportée', 'type' => 'equity'],

            // Classe 2 - Immobilisations
            ['account_number' => '200', 'name' => 'Frais d\'établissement', 'type' => 'asset'],
            ['account_number' => '210', 'name' => 'Immobilisations incorporelles', 'type' => 'asset'],
            ['account_number' => '220', 'name' => 'Terrains et constructions', 'type' => 'asset'],
            ['account_number' => '230', 'name' => 'Installations, machines', 'type' => 'asset'],
            ['account_number' => '240', 'name' => 'Mobilier et matériel roulant', 'type' => 'asset'],
            ['account_number' => '241', 'name' => 'Matériel informatique', 'type' => 'asset'],
            ['account_number' => '280', 'name' => 'Participations', 'type' => 'asset'],

            // Classe 3 - Stocks
            ['account_number' => '300', 'name' => 'Matières premières', 'type' => 'asset'],
            ['account_number' => '340', 'name' => 'Marchandises', 'type' => 'asset'],

            // Classe 4 - Créances et dettes (receivable = asset, payable = liability)
            ['account_number' => '400', 'name' => 'Clients', 'type' => 'asset'],
            ['account_number' => '401', 'name' => 'Clients douteux', 'type' => 'asset'],
            ['account_number' => '409', 'name' => 'Réductions de valeur clients', 'type' => 'asset'],
            ['account_number' => '411', 'name' => 'TVA à récupérer', 'type' => 'asset'],
            ['account_number' => '440', 'name' => 'Fournisseurs', 'type' => 'liability'],
            ['account_number' => '451', 'name' => 'TVA à payer', 'type' => 'liability'],
            ['account_number' => '452', 'name' => 'Précompte professionnel', 'type' => 'liability'],
            ['account_number' => '453', 'name' => 'ONSS', 'type' => 'liability'],
            ['account_number' => '455', 'name' => 'Rémunérations', 'type' => 'liability'],
            ['account_number' => '489', 'name' => 'Autres dettes diverses', 'type' => 'liability'],

            // Classe 5 - Trésorerie
            ['account_number' => '550', 'name' => 'Banques - Comptes courants', 'type' => 'asset'],
            ['account_number' => '551', 'name' => 'Banques - Comptes épargne', 'type' => 'asset'],
            ['account_number' => '570', 'name' => 'Caisse', 'type' => 'asset'],

            // Classe 6 - Charges
            ['account_number' => '600', 'name' => 'Achats de marchandises', 'type' => 'expense'],
            ['account_number' => '601', 'name' => 'Achats de matières premières', 'type' => 'expense'],
            ['account_number' => '604', 'name' => 'Achats de services', 'type' => 'expense'],
            ['account_number' => '610', 'name' => 'Loyers et charges locatives', 'type' => 'expense'],
            ['account_number' => '611', 'name' => 'Entretien et réparations', 'type' => 'expense'],
            ['account_number' => '612', 'name' => 'Fournitures', 'type' => 'expense'],
            ['account_number' => '613', 'name' => 'Services divers', 'type' => 'expense'],
            ['account_number' => '614', 'name' => 'Assurances', 'type' => 'expense'],
            ['account_number' => '615', 'name' => 'Transports', 'type' => 'expense'],
            ['account_number' => '616', 'name' => 'Télécom et Internet', 'type' => 'expense'],
            ['account_number' => '617', 'name' => 'Personnel intérimaire', 'type' => 'expense'],
            ['account_number' => '618', 'name' => 'Honoraires', 'type' => 'expense'],
            ['account_number' => '620', 'name' => 'Rémunérations', 'type' => 'expense'],
            ['account_number' => '621', 'name' => 'Cotisations patronales', 'type' => 'expense'],
            ['account_number' => '630', 'name' => 'Amortissements', 'type' => 'expense'],
            ['account_number' => '640', 'name' => 'Taxes d\'exploitation', 'type' => 'expense'],
            ['account_number' => '650', 'name' => 'Charges financières', 'type' => 'expense'],
            ['account_number' => '651', 'name' => 'Intérêts bancaires', 'type' => 'expense'],
            ['account_number' => '660', 'name' => 'Charges exceptionnelles', 'type' => 'expense'],
            ['account_number' => '670', 'name' => 'Impôts sur le résultat', 'type' => 'expense'],

            // Classe 7 - Produits (income = revenue)
            ['account_number' => '700', 'name' => 'Ventes de marchandises', 'type' => 'revenue'],
            ['account_number' => '701', 'name' => 'Ventes de produits finis', 'type' => 'revenue'],
            ['account_number' => '702', 'name' => 'Ventes de services', 'type' => 'revenue'],
            ['account_number' => '703', 'name' => 'Ristournes accordées', 'type' => 'revenue'],
            ['account_number' => '740', 'name' => 'Autres produits d\'exploitation', 'type' => 'revenue'],
            ['account_number' => '750', 'name' => 'Produits financiers', 'type' => 'revenue'],
            ['account_number' => '751', 'name' => 'Intérêts créditeurs', 'type' => 'revenue'],
            ['account_number' => '760', 'name' => 'Produits exceptionnels', 'type' => 'revenue'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create([
                'company_id' => $company->id,
                'account_number' => $account['account_number'],
                'name' => $account['name'],
                'type' => $account['type'],
                'is_active' => true,
            ]);
        }
    }

    protected function createVatCodes(Company $company): void
    {
        $vatCodes = [
            // Standard rates
            ['code' => '21', 'name' => 'TVA 21%', 'rate' => 21, 'category' => 'S', 'grid_base' => '03', 'grid_vat' => '54'],
            ['code' => '12', 'name' => 'TVA 12%', 'rate' => 12, 'category' => 'S', 'grid_base' => '02', 'grid_vat' => '54'],
            ['code' => '6', 'name' => 'TVA 6%', 'rate' => 6, 'category' => 'S', 'grid_base' => '01', 'grid_vat' => '54'],
            ['code' => '0', 'name' => 'TVA 0%', 'rate' => 0, 'category' => 'Z', 'grid_base' => '00', 'grid_vat' => null],
            // Exemptions
            ['code' => 'E', 'name' => 'Exonéré TVA', 'rate' => 0, 'category' => 'E', 'grid_base' => '00', 'grid_vat' => null],
            // Intra-community
            ['code' => 'IC', 'name' => 'Intracommunautaire', 'rate' => 0, 'category' => 'K', 'grid_base' => '46', 'grid_vat' => null],
            // Export
            ['code' => 'EX', 'name' => 'Export hors UE', 'rate' => 0, 'category' => 'G', 'grid_base' => '47', 'grid_vat' => null],
            // Reverse charge
            ['code' => 'RC', 'name' => 'Autoliquidation', 'rate' => 0, 'category' => 'AE', 'grid_base' => '00', 'grid_vat' => null],
            // Cocontractant
            ['code' => 'CC21', 'name' => 'Cocontractant 21%', 'rate' => 21, 'category' => 'AE', 'grid_base' => '87', 'grid_vat' => '56'],
        ];

        foreach ($vatCodes as $vat) {
            VatCode::create([
                'company_id' => $company->id,
                'code' => $vat['code'],
                'name' => $vat['name'],
                'rate' => $vat['rate'],
                'category' => $vat['category'],
                'grid_base' => $vat['grid_base'],
                'grid_vat' => $vat['grid_vat'],
                'is_active' => true,
            ]);
        }
    }

    protected function createAccountingFirmDemo(Company $existingCompany, User $existingAdmin): void
    {
        // Create the accounting firm
        $firm = AccountingFirm::create([
            'name' => 'Cabinet Expert-Conseil SPRL',
            'legal_form' => 'SPRL',
            'itaa_number' => 'ITAA-2024-12345',
            'ire_number' => 'IRE-B-98765',
            'vat_number' => 'BE0555666777',
            'enterprise_number' => '0555.666.777',
            'street' => 'Boulevard du Souverain',
            'house_number' => '100',
            'box' => '5',
            'postal_code' => '1170',
            'city' => 'Bruxelles',
            'country_code' => 'BE',
            'email' => 'contact@expert-conseil.be',
            'phone' => '+32 2 555 66 77',
            'website' => 'https://www.expert-conseil.be',
            'peppol_test_mode' => true,
            'subscription_status' => 'active',
            'max_clients' => 100,
            'max_users' => 20,
            'settings' => [
                'auto_assign_tasks' => true,
                'default_billing_type' => 'monthly',
                'email_notifications' => true,
            ],
            'features' => [
                'multi_client_dashboard',
                'task_management',
                'document_sharing',
                'time_tracking',
                'invoicing',
            ],
        ]);

        // Create expert comptable (owner)
        $expert = User::create([
            'first_name' => 'Philippe',
            'last_name' => 'Dumont',
            'email' => 'expert@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 'accountant',
            'default_firm_id' => $firm->id,
        ]);

        $firm->users()->attach($expert->id, [
            'role' => 'cabinet_owner',
            'is_active' => true,
            'can_access_all_clients' => true,
            'is_default' => true,
            'joined_at' => now()->subYears(5),
            'job_title' => 'Expert-Comptable Gérant',
        ]);

        // Create collaborator
        $collaborator = User::create([
            'first_name' => 'Isabelle',
            'last_name' => 'Lefebvre',
            'email' => 'collab@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 'collaborator',
            'default_firm_id' => $firm->id,
        ]);

        $firm->users()->attach($collaborator->id, [
            'role' => 'cabinet_accountant',
            'is_active' => true,
            'can_access_all_clients' => false,
            'is_default' => true,
            'joined_at' => now()->subYears(2),
            'job_title' => 'Comptable Senior',
        ]);

        // Create second collaborator
        $assistant = User::create([
            'first_name' => 'Thomas',
            'last_name' => 'Bernard',
            'email' => 'assistant@demo.be',
            'password' => 'Demo2024!',
            'email_verified_at' => now(),
            'is_active' => true,
            'user_type' => 'collaborator',
            'default_firm_id' => $firm->id,
        ]);

        $firm->users()->attach($assistant->id, [
            'role' => 'cabinet_assistant',
            'is_active' => true,
            'can_access_all_clients' => false,
            'is_default' => true,
            'joined_at' => now()->subMonths(6),
            'job_title' => 'Assistant Comptable',
        ]);

        // Link existing company as client of the firm
        $existingCompany->update([
            'managed_by_firm_id' => $firm->id,
            'accepts_firm_management' => true,
            'firm_access_level' => 'full',
        ]);

        // Create mandate for existing company
        ClientMandate::create([
            'accounting_firm_id' => $firm->id,
            'company_id' => $existingCompany->id,
            'mandate_type' => 'full_mandate',
            'status' => 'active',
            'start_date' => now()->subYears(2),
            'manager_user_id' => $expert->id,
            'services' => ['accounting', 'vat', 'annual_accounts', 'payroll', 'consulting'],
            'billing_type' => 'monthly',
            'monthly_fee' => 450.00,
            'client_can_view' => true,
            'client_can_edit' => true,
            'client_can_validate' => false,
            'internal_notes' => 'Client fidèle depuis 2022. Dossier bien tenu.',
        ]);

        // Create additional client companies for the firm
        $clientCompanies = [
            [
                'name' => 'Restaurant La Belle Époque',
                'vat' => 'BE0666777888',
                'type' => 'full_mandate',
                'services' => ['accounting', 'vat', 'annual_accounts'],
                'fee' => 350.00,
            ],
            [
                'name' => 'Garage Auto Plus SPRL',
                'vat' => 'BE0777888999',
                'type' => 'accounting_only',
                'services' => ['accounting', 'vat'],
                'fee' => 280.00,
            ],
            [
                'name' => 'Cabinet Médical Dr. Janssens',
                'vat' => 'BE0888999000',
                'type' => 'vat_only',
                'services' => ['vat'],
                'fee' => 150.00,
            ],
            [
                'name' => 'Boutique Mode & Style',
                'vat' => 'BE0999000111',
                'type' => 'full_mandate',
                'services' => ['accounting', 'vat', 'annual_accounts', 'payroll'],
                'fee' => 520.00,
            ],
            [
                'name' => 'IT Solutions Pro SA',
                'vat' => 'BE0111222333',
                'type' => 'consulting',
                'services' => ['consulting', 'tax_optimization'],
                'fee' => 200.00,
            ],
            [
                'name' => 'Boulangerie Artisanale Leroy',
                'vat' => 'BE0222333444',
                'type' => 'full_mandate',
                'services' => ['accounting', 'vat', 'annual_accounts'],
                'fee' => 300.00,
            ],
        ];

        foreach ($clientCompanies as $index => $clientData) {
            $company = Company::create([
                'name' => $clientData['name'],
                'vat_number' => $clientData['vat'],
                'enterprise_number' => str_replace('BE', '', $clientData['vat']),
                'street' => 'Rue du Client ' . ($index + 1),
                'house_number' => (string) rand(1, 200),
                'postal_code' => rand(1000, 9999),
                'city' => fake()->randomElement(['Bruxelles', 'Liège', 'Namur', 'Gand', 'Anvers', 'Charleroi']),
                'country_code' => 'BE',
                'email' => 'contact@' . Str::slug($clientData['name']) . '.be',
                'phone' => '+32 ' . rand(2, 9) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'company_type' => 'firm_client',
                'managed_by_firm_id' => $firm->id,
                'accepts_firm_management' => true,
                'firm_access_level' => 'full',
                'vat_regime' => 'normal',
                'vat_periodicity' => 'quarterly',
                'fiscal_year_start_month' => 1,
            ]);

            // Create chart of accounts and VAT codes for each client
            $this->createChartOfAccounts($company);
            $this->createVatCodes($company);

            // Create mandate
            ClientMandate::create([
                'accounting_firm_id' => $firm->id,
                'company_id' => $company->id,
                'mandate_type' => $clientData['type'],
                'status' => 'active',
                'start_date' => now()->subMonths(rand(3, 36)),
                'manager_user_id' => $index % 2 === 0 ? $expert->id : $collaborator->id,
                'services' => $clientData['services'],
                'billing_type' => 'monthly',
                'monthly_fee' => $clientData['fee'],
                'client_can_view' => true,
                'client_can_edit' => $clientData['type'] === 'full_mandate',
                'client_can_validate' => false,
            ]);

            // Create some sample partners and invoices for larger clients
            if (in_array($clientData['type'], ['full_mandate', 'accounting_only'])) {
                $this->createMinimalSampleData($company);
            }
        }
    }

    protected function createMinimalSampleData(Company $company): void
    {
        // Create a few partners
        for ($i = 0; $i < 3; $i++) {
            Partner::create([
                'company_id' => $company->id,
                'type' => $i < 2 ? 'customer' : 'supplier',
                'name' => fake()->company(),
                'vat_number' => 'BE0' . rand(100000000, 999999999),
                'email' => fake()->companyEmail(),
                'city' => fake()->city(),
                'country_code' => 'BE',
                'is_active' => true,
            ]);
        }

        // Create a few invoices
        $partners = Partner::where('company_id', $company->id)->get();
        $customer = $partners->where('type', 'customer')->first();
        $supplier = $partners->where('type', 'supplier')->first();

        if ($customer) {
            for ($i = 0; $i < 5; $i++) {
                $date = now()->subDays(rand(10, 180));
                $amount = rand(500, 5000);
                $vat = round($amount * 0.21, 2);

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'partner_id' => $customer->id,
                    'type' => 'out',
                    'invoice_number' => 'FAC-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $date,
                    'due_date' => $date->copy()->addDays(30),
                    'status' => fake()->randomElement(['paid', 'sent', 'validated']),
                    'currency' => 'EUR',
                    'total_excl_vat' => $amount,
                    'total_vat' => $vat,
                    'total_incl_vat' => $amount + $vat,
                    'amount_due' => 0,
                ]);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'line_number' => 1,
                    'description' => fake()->sentence(4),
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'vat_rate' => 21,
                    'line_amount' => $amount,
                    'vat_amount' => $vat,
                ]);
            }
        }

        if ($supplier) {
            for ($i = 0; $i < 3; $i++) {
                $date = now()->subDays(rand(10, 180));
                $amount = rand(200, 2000);
                $vat = round($amount * 0.21, 2);

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'partner_id' => $supplier->id,
                    'type' => 'in',
                    'invoice_number' => 'ACH-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $date,
                    'due_date' => $date->copy()->addDays(30),
                    'status' => fake()->randomElement(['paid', 'validated']),
                    'currency' => 'EUR',
                    'total_excl_vat' => $amount,
                    'total_vat' => $vat,
                    'total_incl_vat' => $amount + $vat,
                    'amount_due' => 0,
                ]);

                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'line_number' => 1,
                    'description' => fake()->sentence(4),
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'vat_rate' => 21,
                    'line_amount' => $amount,
                    'vat_amount' => $vat,
                ]);
            }
        }
    }
}
