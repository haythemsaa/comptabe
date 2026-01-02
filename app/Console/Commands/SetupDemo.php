<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupDemo extends Command
{
    protected $signature = 'demo:setup {--fresh : Wipe and recreate database} {--force : Skip confirmation}';
    protected $description = 'Setup demo data for ComptaBE';

    public function handle(): int
    {
        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                                                            ║');
        $this->info('║               ComptaBE - Setup Demo                        ║');
        $this->info('║                                                            ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        if ($this->option('fresh')) {
            $this->warn('This will DESTROY all existing data!');
            if (!$this->option('force') && !$this->confirm('Are you sure you want to continue?')) {
                return Command::FAILURE;
            }

            $this->info('');
            $this->info('Step 1/3: Migrating database...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('  ✓ Database migrated');
        } else {
            $this->info('Step 1/3: Running migrations...');
            Artisan::call('migrate', ['--force' => true]);
            $this->info('  ✓ Migrations completed');
        }

        $this->info('');
        $this->info('Step 2/3: Creating demo data...');
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
        $this->info('  ✓ Demo data created');

        $this->info('');
        $this->info('Step 3/3: Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        $this->info('  ✓ Caches cleared');

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('');
        $this->info('  Demo setup completed successfully!');
        $this->info('');
        $this->info('  Business User Credentials:');
        $this->info('');
        $this->table(
            ['Role', 'Email', 'Password', 'Company'],
            [
                ['Admin', 'admin@demo.be', 'Demo2024!', 'TechBelgium SPRL'],
                ['Comptable', 'comptable@demo.be', 'Demo2024!', 'TechBelgium SPRL'],
                ['Manager', 'manager@demo.be', 'Demo2024!', 'TechBelgium SPRL'],
                ['Viewer', 'viewer@demo.be', 'Demo2024!', 'TechBelgium SPRL'],
            ]
        );
        $this->info('');
        $this->info('  Expert-Comptable Credentials (Accounting Firm Portal):');
        $this->info('');
        $this->table(
            ['Role', 'Email', 'Password', 'Cabinet'],
            [
                ['Expert-Comptable', 'expert@demo.be', 'Demo2024!', 'Cabinet Expert-Conseil SPRL'],
                ['Collaborateur', 'collab@demo.be', 'Demo2024!', 'Cabinet Expert-Conseil SPRL'],
                ['Assistant', 'assistant@demo.be', 'Demo2024!', 'Cabinet Expert-Conseil SPRL'],
            ]
        );
        $this->info('');
        $this->info('  Demo Companies:');
        $this->info('    • TechBelgium SPRL (BE0123456789) - Full demo data');
        $this->info('    • Consulting Pro SA (BE0987654321) - Empty company');
        $this->info('');
        $this->info('  Accounting Firm Clients (6 additional companies):');
        $this->info('    • IT Solutions Pro SA (BE0111222333)');
        $this->info('    • Boulangerie Artisanale Leroy (BE0222333444)');
        $this->info('    • Restaurant La Belle Époque (BE0666777888)');
        $this->info('    • Garage Auto Plus SPRL (BE0777888999)');
        $this->info('    • Cabinet Médical Dr. Janssens (BE0888999000)');
        $this->info('    • Boutique Mode & Style (BE0999000111)');
        $this->info('');
        $this->info('  Demo Data Includes:');
        $this->info('    • 50 sales invoices (12 months history)');
        $this->info('    • 30 purchase invoices');
        $this->info('    • 10 customers + 8 suppliers');
        $this->info('    • 2 bank accounts with transactions');
        $this->info('    • Chart of accounts (PCMN belge)');
        $this->info('    • Journal entries');
        $this->info('    • Approval workflows');
        $this->info('    • Webhooks configuration');
        $this->info('    • Saved report templates');
        $this->info('    • Accounting firm with client mandates');
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('');

        return Command::SUCCESS;
    }
}
