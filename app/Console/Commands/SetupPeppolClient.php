<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupPeppolClient extends Command
{
    protected $signature = 'peppol:setup-client
                            {--company= : Company ID or VAT number}
                            {--vat= : VAT number of the company}
                            {--name= : Company name}
                            {--email= : Contact email}
                            {--test : Enable test mode (recommended for first setup)}';

    protected $description = 'Configure a client company for Peppol invoicing (Belgium)';

    public function handle()
    {
        $this->info('🇧🇪 Peppol Belgium - Client Setup');
        $this->newLine();

        // Get or create company
        $company = $this->getOrCreateCompany();

        if (!$company) {
            $this->error('Failed to create or find company');
            return 1;
        }

        // Configure Peppol for company
        $this->configurePeppol($company);

        // Create test partner if needed
        if ($this->option('test') && $this->confirm('Create a test partner for demonstration?', true)) {
            $this->createTestPartner($company);
        }

        // Summary
        $this->showSummary($company);

        return 0;
    }

    protected function getOrCreateCompany(): ?Company
    {
        // Try to find existing company
        if ($companyId = $this->option('company')) {
            $company = Company::where('id', $companyId)
                ->orWhere('vat_number', $companyId)
                ->first();

            if ($company) {
                $this->info("✓ Found existing company: {$company->name}");
                return $company;
            }
        }

        // Create new company
        if ($this->confirm('Company not found. Create a new one?', true)) {
            return $this->createCompany();
        }

        return null;
    }

    protected function createCompany(): Company
    {
        $this->info('Creating new company...');

        $name = $this->option('name') ?? $this->ask('Company name');
        $vat = $this->option('vat') ?? $this->ask('VAT number (BE + 10 digits)', 'BE0123456789');
        $email = $this->option('email') ?? $this->ask('Contact email');

        // Clean VAT number
        $vatClean = preg_replace('/[^0-9]/', '', $vat);
        if (strlen($vatClean) !== 10) {
            $this->error('Invalid VAT number. Must be 10 digits after BE prefix.');
            exit(1);
        }

        $company = Company::create([
            'id' => Str::uuid(),
            'name' => $name,
            'vat_number' => $vatClean,
            'country_code' => 'BE',
            'email' => $email,
            'currency' => 'EUR',
            'language' => 'fr',
            'tax_system' => 'belgian_vat',
        ]);

        $this->info("✓ Company created: {$company->name} (VAT: BE{$company->vat_number})");

        // Create admin user
        if ($this->confirm('Create an admin user for this company?', true)) {
            $this->createAdminUser($company);
        }

        return $company;
    }

    protected function createAdminUser(Company $company): void
    {
        $email = $this->ask('User email', strtolower(Str::slug($company->name)) . '@example.com');
        $name = $this->ask('User name', 'Admin ' . $company->name);
        $password = Str::random(12);

        $user = User::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'email' => $email,
            'name' => $name,
            'first_name' => explode(' ', $name)[0] ?? 'Admin',
            'last_name' => explode(' ', $name, 2)[1] ?? $company->name,
            'password' => bcrypt($password),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->info("✓ User created: {$user->email}");
        $this->warn("  Password: {$password}");
        $this->warn("  ⚠️  Save this password! It will not be shown again.");
    }

    protected function configurePeppol(Company $company): void
    {
        $this->newLine();
        $this->info('Configuring Peppol settings...');

        $testMode = $this->option('test') ?? $this->confirm('Enable TEST mode? (recommended for first setup)', true);

        // Generate Peppol Participant ID
        $peppolId = '0208:BE' . $company->vat_number;

        $company->update([
            'peppol_enabled' => true,
            'peppol_test_mode' => $testMode,
            'peppol_participant_id' => $peppolId,
        ]);

        $this->info("✓ Peppol enabled");
        $this->info("  Participant ID: {$peppolId}");
        $this->info("  Mode: " . ($testMode ? '🧪 TEST (simulated)' : '🚀 PRODUCTION (real API)'));

        if ($testMode) {
            $this->warn('');
            $this->warn('  ⚠️  TEST MODE - Invoices will be simulated, not sent to real Peppol network');
            $this->warn('  ✓ Perfect for demonstrations and testing');
            $this->warn('  ✓ No API key required');
            $this->warn('  ✓ Unlimited usage');
        }
    }

    protected function createTestPartner(Company $company): void
    {
        $this->newLine();
        $this->info('Creating test partner...');

        $partner = Partner::create([
            'id' => Str::uuid(),
            'company_id' => $company->id,
            'name' => 'Test Customer SA',
            'vat_number' => '0987654321',
            'email' => 'test.customer@example.com',
            'type' => 'customer',
            'peppol_id' => '0208:BE0987654321',
            'peppol_enabled' => true,
        ]);

        $this->info("✓ Test partner created: {$partner->name}");
        $this->info("  Peppol ID: {$partner->peppol_id}");
    }

    protected function showSummary(Company $company): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✓ Setup Complete!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->table(
            ['Setting', 'Value'],
            [
                ['Company', $company->name],
                ['VAT', 'BE' . $company->vat_number],
                ['Peppol ID', $company->peppol_participant_id],
                ['Mode', $company->peppol_test_mode ? '🧪 TEST' : '🚀 PRODUCTION'],
                ['Status', $company->peppol_enabled ? '✓ Enabled' : '✗ Disabled'],
            ]
        );

        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->newLine();

        if ($company->peppol_test_mode) {
            $this->line('  1. Login to ComptaBE with company credentials');
            $this->line('  2. Create a test invoice');
            $this->line('  3. Click "Send via Peppol" - it will simulate the send');
            $this->line('  4. Check the generated UBL XML file');
            $this->line('  5. When ready for production:');
            $this->line('     - Get API key from Peppol provider (Recommand.eu or Peppol-Box.be)');
            $this->line('     - Update .env with API credentials');
            $this->line('     - Disable test mode');
        } else {
            $this->line('  1. Ensure .env has valid PEPPOL_API_KEY');
            $this->line('  2. Login to ComptaBE');
            $this->line('  3. Create invoice');
            $this->line('  4. Send via Peppol (real transmission)');
        }

        $this->newLine();
        $this->info('📖 Documentation: See GUIDE_PEPPOL_BELGIQUE_GRATUIT.md');
        $this->newLine();
    }
}
