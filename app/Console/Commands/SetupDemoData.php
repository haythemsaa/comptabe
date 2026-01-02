<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Payment;
use App\Models\ClientAccess;
use App\Models\ClientDocument;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SetupDemoData extends Command
{
    protected $signature = 'demo:setup
                            {--company= : Create demo data for specific company ID}
                            {--full : Create full demo with all features}
                            {--fresh : Delete existing demo data before creating new}';

    protected $description = 'Set up comprehensive demo data to showcase all ComptaBE features';

    private Company $company;
    private User $owner;
    private User $accountant;
    private User $client;

    public function handle(): int
    {
        $this->info('🚀 Setting up ComptaBE demo data...');
        $this->newLine();

        // Step 1: Create or use existing company
        if ($companyId = $this->option('company')) {
            $this->company = Company::findOrFail($companyId);
            $this->info("✓ Using existing company: {$this->company->name}");
        } else {
            $this->createDemoCompany();
        }

        // Clean existing data if --fresh flag is set
        if ($this->option('fresh')) {
            $this->cleanExistingData();
        }

        // Step 2: Create users
        $this->createUsers();

        // Step 3: Create partners (customers & suppliers)
        $partners = $this->createPartners();

        // Step 4: Create products
        $products = $this->createProducts();

        // Step 5: Create invoices
        $invoices = $this->createInvoices($partners['customers'], $products);

        // Step 6: Create quotes
        $this->createQuotes($partners['customers'], $products);

        // Step 7: Create bank account & transactions
        $this->createBankData($invoices);

        // Step 8: Create client portal access
        $this->createClientPortalAccess($partners['customers']);

        if ($this->option('full')) {
            // Step 9: Create AI chat conversation
            $this->createChatConversation();

            // Step 10: Create documents
            $this->createDocuments();
        }

        $this->newLine();
        $this->info('✅ Demo data setup complete!');
        $this->newLine();
        $this->displayCredentials();

        return Command::SUCCESS;
    }

    private function createDemoCompany(): void
    {
        $this->info('Creating demo company...');

        $this->company = Company::firstOrCreate(
            ['vat_number' => 'BE0123456789'],
            [
                'name' => 'ComptaBE Demo SPRL',
                'legal_form' => 'SPRL',
                'street' => 'Avenue Louise',
                'house_number' => '123',
                'postal_code' => '1050',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
                'email' => 'demo@comptabe.be',
                'phone' => '+32 2 123 45 67',
                'website' => 'https://demo.comptabe.be',
                'default_iban' => 'BE68539007547034',
                'default_bic' => 'GEBABEBB',
                'vat_regime' => 'normal',
                'vat_periodicity' => 'monthly',
                'settings' => [
                    'is_demo' => true,
                    'currency' => 'EUR',
                    'default_vat_rate' => 21,
                    'invoice_prefix' => 'DEMO',
                    'quote_prefix' => 'DEVIS',
                    'peppol_enabled' => true,
                ],
            ]
        );

        $this->info("✓ Company created/found: {$this->company->name}");
    }

    private function createUsers(): void
    {
        $this->info('Creating users...');

        // Owner/Admin
        $this->owner = User::firstOrCreate(
            ['email' => 'owner@demo.comptabe.be'],
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
            ]
        );

        $this->owner->companies()->syncWithoutDetaching([
            $this->company->id => ['role' => 'owner']
        ]);

        // Accountant
        $this->accountant = User::firstOrCreate(
            ['email' => 'accountant@demo.comptabe.be'],
            [
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
            ]
        );

        $this->accountant->companies()->syncWithoutDetaching([
            $this->company->id => ['role' => 'accountant']
        ]);

        // Client user (for client portal demo)
        $this->client = User::firstOrCreate(
            ['email' => 'client@demo.comptabe.be'],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Dubois',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
            ]
        );

        $this->info('✓ Users created: owner, accountant, client');
    }

    private function createPartners(): array
    {
        $this->info('Creating partners (customers & suppliers)...');

        $customers = [];
        $suppliers = [];

        // Customers
        $customerData = [
            ['name' => 'Acme Corporation', 'vat' => 'BE0987654321', 'email' => 'contact@acme.be'],
            ['name' => 'TechStart SPRL', 'vat' => 'BE0456789123', 'email' => 'info@techstart.be'],
            ['name' => 'BelgianRetail SA', 'vat' => 'BE0789456123', 'email' => 'admin@belgianretail.be'],
        ];

        foreach ($customerData as $data) {
            $customers[] = Partner::create([
                'company_id' => $this->company->id,
                'type' => 'customer',
                'name' => $data['name'],
                'vat_number' => $data['vat'],
                'email' => $data['email'],
                'phone' => '+32 2 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'street' => 'Rue de Commerce',
                'house_number' => (string)rand(1, 200),
                'postal_code' => '1000',
                'city' => 'Bruxelles',
                'country_code' => 'BE',
            ]);
        }

        // Suppliers
        $supplierData = [
            ['name' => 'Office Supplies NV', 'vat' => 'BE0321654987'],
            ['name' => 'IT Services Belgium', 'vat' => 'BE0654987321'],
        ];

        foreach ($supplierData as $data) {
            $suppliers[] = Partner::create([
                'company_id' => $this->company->id,
                'type' => 'supplier',
                'name' => $data['name'],
                'vat_number' => $data['vat'],
                'street' => 'Chaussée de Charleroi',
                'house_number' => (string)rand(1, 300),
                'postal_code' => '1060',
                'city' => 'Saint-Gilles',
                'country_code' => 'BE',
            ]);
        }

        $this->info('✓ Partners created: ' . count($customers) . ' customers, ' . count($suppliers) . ' suppliers');

        return compact('customers', 'suppliers');
    }

    private function createProducts(): array
    {
        $this->info('Creating products...');

        $products = [];

        $productData = [
            ['name' => 'Consultation comptable', 'price' => 85.00, 'unit' => 'heure'],
            ['name' => 'Déclaration TVA', 'price' => 150.00, 'unit' => 'service'],
            ['name' => 'Gestion de paie', 'price' => 25.00, 'unit' => 'employé'],
            ['name' => 'Audit annuel', 'price' => 1200.00, 'unit' => 'service'],
            ['name' => 'Formation comptabilité', 'price' => 450.00, 'unit' => 'jour'],
        ];

        foreach ($productData as $data) {
            $products[] = Product::create([
                'company_id' => $this->company->id,
                'name' => $data['name'],
                'description' => 'Service professionnel de comptabilité',
                'unit_price' => $data['price'],
                'vat_rate' => 21,
                'unit' => $data['unit'],
                'is_active' => true,
            ]);
        }

        $this->info('✓ Products created: ' . count($products));

        return $products;
    }

    private function createInvoices(array $customers, array $products): array
    {
        $this->info('Creating invoices...');

        $invoices = [];
        $invoiceNumber = 1;

        foreach ($customers as $customer) {
            // Create 2-3 invoices per customer
            $count = rand(2, 3);

            for ($i = 0; $i < $count; $i++) {
                $invoiceDate = now()->subDays(rand(10, 90));
                $dueDate = $invoiceDate->copy()->addDays(30);

                $invoice = Invoice::create([
                    'company_id' => $this->company->id,
                    'partner_id' => $customer->id,
                    'invoice_number' => 'DEMO-' . str_pad($invoiceNumber++, 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'status' => collect(['draft', 'sent', 'paid'])->random(),
                    'notes' => 'Merci pour votre confiance',
                ]);

                // Add 1-3 lines per invoice
                $lineCount = rand(1, 3);
                $totalExclVat = 0;

                for ($j = 0; $j < $lineCount; $j++) {
                    $product = $products[array_rand($products)];
                    $quantity = rand(1, 10);
                    $lineAmount = $quantity * $product->unit_price;

                    InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'line_number' => $j + 1,
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $product->unit_price,
                        'vat_rate' => 21,
                        'line_amount' => $lineAmount,
                        'vat_amount' => $lineAmount * 0.21,
                    ]);

                    $totalExclVat += $lineAmount;
                }

                $totalVat = $totalExclVat * 0.21;

                $invoice->update([
                    'total_excl_vat' => $totalExclVat,
                    'total_vat' => $totalVat,
                    'total_incl_vat' => $totalExclVat + $totalVat,
                ]);

                // Create payment for "paid" invoices
                if ($invoice->status === 'paid' && Schema::hasTable('payments')) {
                    Payment::create([
                        'company_id' => $this->company->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $invoice->total_incl_vat,
                        'payment_date' => $invoiceDate->copy()->addDays(rand(5, 25)),
                        'payment_method' => 'bank_transfer',
                        'reference' => $invoice->invoice_number,
                    ]);
                }

                $invoices[] = $invoice;
            }
        }

        $this->info('✓ Invoices created: ' . count($invoices));

        return $invoices;
    }

    private function createQuotes(array $customers, array $products): void
    {
        $this->info('Creating quotes...');

        $quoteNumber = 1;
        $count = 0;

        // Create 1-2 quotes per customer
        foreach (array_slice($customers, 0, 2) as $customer) {
            $quote = Quote::create([
                'company_id' => $this->company->id,
                'partner_id' => $customer->id,
                'quote_number' => 'DEVIS-' . str_pad($quoteNumber++, 5, '0', STR_PAD_LEFT),
                'quote_date' => now()->subDays(rand(1, 30)),
                'valid_until' => now()->addDays(30),
                'status' => collect(['draft', 'sent', 'accepted'])->random(),
                'notes' => 'Devis valable 30 jours',
            ]);

            // Add lines
            $product = $products[array_rand($products)];
            $quantity = rand(5, 20);
            $lineTotal = $quantity * $product->unit_price;

            QuoteLine::create([
                'quote_id' => $quote->id,
                'line_number' => 1,
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->unit_price,
                'vat_rate' => 21,
                'line_total' => $lineTotal,
                'vat_amount' => $lineTotal * 0.21,
            ]);

            $totalExclVat = $lineTotal;
            $totalVat = $totalExclVat * 0.21;

            $quote->update([
                'total_excl_vat' => $totalExclVat,
                'total_vat' => $totalVat,
                'total_incl_vat' => $totalExclVat + $totalVat,
            ]);

            $count++;
        }

        $this->info('✓ Quotes created: ' . $count);
    }

    private function createBankData(array $invoices): void
    {
        if (!Schema::hasTable('bank_accounts')) {
            $this->warn('⊘ Skipping bank data (bank_accounts table not yet created)');
            return;
        }

        $this->info('Creating bank account & transactions...');

        $bankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'name' => 'Compte principal',
            'bank_name' => 'BNP Paribas Fortis',
            'iban' => 'BE68539007547034',
            'bic' => 'GEBABEBB',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->info('✓ Bank account created');

        // Skip transactions if bank_statements or bank_transactions tables don't exist
        if (!Schema::hasTable('bank_statements') || !Schema::hasTable('bank_transactions')) {
            $this->warn('⊘ Skipping bank transactions (tables not yet created)');
            return;
        }

        // Create a bank statement first (required for transactions)
        $bankStatement = BankStatement::create([
            'bank_account_id' => $bankAccount->id,
            'statement_number' => 'DEMO-' . now()->format('Ymd'),
            'statement_date' => now(),
            'period_start' => now()->subMonths(3),
            'period_end' => now(),
            'opening_balance' => 0,
            'closing_balance' => 0,
        ]);

        // Create transactions from paid invoices
        $count = 0;
        foreach ($invoices as $invoice) {
            if ($invoice->status === 'paid') {
                $paymentDate = $invoice->invoice_date->copy()->addDays(rand(5, 25));

                BankTransaction::create([
                    'bank_statement_id' => $bankStatement->id,
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => $paymentDate,
                    'value_date' => $paymentDate,
                    'amount' => $invoice->total_incl_vat,
                    'currency' => 'EUR',
                    'communication' => 'Paiement facture ' . $invoice->invoice_number,
                    'counterparty_name' => $invoice->partner->name,
                    'counterparty_iban' => 'BE' . rand(10, 99) . rand(100000000, 999999999),
                    'bank_reference' => $invoice->invoice_number,
                    'reconciliation_status' => 'matched',
                    'matched_invoice_id' => $invoice->id,
                    'matched_partner_id' => $invoice->partner_id,
                ]);

                $count++;
            }
        }

        $this->info('✓ Bank transactions created: ' . $count);
    }

    private function createClientPortalAccess(array $customers): void
    {
        if (!Schema::hasTable('client_access')) {
            $this->warn('⊘ Skipping client portal access (table not yet created)');
            return;
        }

        $this->info('Creating client portal access...');

        // Give first customer full portal access
        if (isset($customers[0])) {
            ClientAccess::create([
                'user_id' => $this->client->id,
                'company_id' => $this->company->id,
                'access_level' => 'full_client',
                'permissions' => [
                    'view_invoices' => true,
                    'download_invoices' => true,
                    'upload_documents' => true,
                    'comment' => true,
                    'view_balance' => true,
                ],
                'last_access_at' => now()->subDays(rand(1, 5)),
            ]);

            $this->info('✓ Client portal access created for: ' . $this->client->email);
        }
    }

    private function createChatConversation(): void
    {
        if (!Schema::hasTable('chat_conversations') || !Schema::hasTable('chat_messages')) {
            $this->warn('⊘ Skipping chat conversation (tables not yet created)');
            return;
        }

        $this->info('Creating AI chat conversation...');

        $conversation = ChatConversation::create([
            'user_id' => $this->owner->id,
            'company_id' => $this->company->id,
            'title' => 'Comment créer une nouvelle facture ?',
            'context_type' => 'tenant',
            'last_message_at' => now(),
        ]);

        // User message
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Comment créer une nouvelle facture pour un client ?',
            'created_at' => now()->subMinutes(5),
        ]);

        // Assistant response
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => "Pour créer une nouvelle facture, vous avez plusieurs options :\n\n1. **Via le menu** : Allez dans Ventes > Factures > Nouvelle facture\n2. **Via le tableau de bord** : Cliquez sur le bouton \"+ Nouvelle facture\"\n3. **Via l'assistant AI** : Je peux créer une facture pour vous ! Donnez-moi les informations suivantes :\n   - Nom du client\n   - Services ou produits\n   - Montants\n\nSouhaitez-vous que je crée une facture maintenant ?",
            'input_tokens' => 150,
            'output_tokens' => 320,
            'cost' => 0.005,
            'created_at' => now()->subMinutes(4),
        ]);

        $this->info('✓ Chat conversation created');
    }

    private function createDocuments(): void
    {
        if (!Schema::hasTable('client_documents')) {
            $this->warn('⊘ Skipping client documents (table not yet created)');
            return;
        }

        $this->info('Creating sample documents...');

        ClientDocument::create([
            'company_id' => $this->company->id,
            'uploaded_by' => $this->client->id,
            'type' => 'invoice',
            'category' => 'Dépenses',
            'filename' => Str::uuid() . '.pdf',
            'original_filename' => 'facture_achat_bureau.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 245678,
            'storage_path' => 'documents/' . $this->company->id . '/sample.pdf',
            'description' => 'Facture achat matériel de bureau',
            'document_date' => now()->subDays(15),
            'is_processed' => false,
        ]);

        $this->info('✓ Sample documents created');
    }

    private function cleanExistingData(): void
    {
        $this->warn('🧹 Cleaning existing demo data for company: ' . $this->company->name);

        // Delete in reverse order to respect foreign key constraints
        if (Schema::hasTable('chat_messages')) {
            ChatMessage::whereHas('conversation', function ($query) {
                $query->where('company_id', $this->company->id);
            })->delete();
        }

        if (Schema::hasTable('chat_conversations')) {
            ChatConversation::where('company_id', $this->company->id)->delete();
        }

        if (Schema::hasTable('client_documents')) {
            ClientDocument::where('company_id', $this->company->id)->delete();
        }

        if (Schema::hasTable('client_access')) {
            ClientAccess::where('company_id', $this->company->id)->delete();
        }

        if (Schema::hasTable('payments')) {
            Payment::where('company_id', $this->company->id)->delete();
        }

        if (Schema::hasTable('bank_transactions')) {
            BankTransaction::whereHas('bankAccount', function ($query) {
                $query->where('company_id', $this->company->id);
            })->delete();
        }

        if (Schema::hasTable('bank_statements')) {
            BankStatement::whereHas('bankAccount', function ($query) {
                $query->where('company_id', $this->company->id);
            })->delete();
        }

        if (Schema::hasTable('bank_accounts')) {
            BankAccount::where('company_id', $this->company->id)->withTrashed()->forceDelete();
        }

        if (Schema::hasTable('invoice_lines')) {
            InvoiceLine::whereHas('invoice', function ($query) {
                $query->where('company_id', $this->company->id)->withTrashed();
            })->forceDelete();
        }

        if (Schema::hasTable('invoices')) {
            Invoice::where('company_id', $this->company->id)->withTrashed()->forceDelete();
        }

        if (Schema::hasTable('quote_lines')) {
            QuoteLine::whereHas('quote', function ($query) {
                $query->where('company_id', $this->company->id)->withTrashed();
            })->forceDelete();
        }

        if (Schema::hasTable('quotes')) {
            Quote::where('company_id', $this->company->id)->withTrashed()->forceDelete();
        }

        if (Schema::hasTable('products')) {
            Product::where('company_id', $this->company->id)->delete();
        }

        if (Schema::hasTable('partners')) {
            Partner::where('company_id', $this->company->id)->delete();
        }

        $this->info('✓ Existing data cleaned');
        $this->newLine();
    }

    private function displayCredentials(): void
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📋 DEMO CREDENTIALS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->table(
            ['Role', 'Email', 'Password', 'Nom'],
            [
                ['Owner/Admin', 'owner@demo.comptabe.be', 'demo123', 'Jean Dupont'],
                ['Accountant', 'accountant@demo.comptabe.be', 'demo123', 'Marie Martin'],
                ['Client Portal', 'client@demo.comptabe.be', 'demo123', 'Sophie Dubois'],
                ['Superadmin', 'admin@demo.be', 'Demo2024!', 'Admin Demo'],
            ]
        );

        $this->newLine();
        $this->info('Company: ' . $this->company->name);
        $this->info('Company ID: ' . $this->company->id);
        $this->newLine();
        $this->info('🎯 Ready to demo ComptaBE features!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
