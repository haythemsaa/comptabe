<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TunisiaDemoCompanySeeder extends Seeder
{
    /**
     * Seed a complete Tunisian demo company.
     */
    public function run(): void
    {
        $this->command->info('🇹🇳 Création d\'une entreprise tunisienne de démonstration...');

        // Vérifier si l'entreprise existe déjà (par vat_number car c'est unique)
        $existing = Company::where('vat_number', '1234567/A/M/000')
            ->orWhere('matricule_fiscal', '1234567/A/M/000')
            ->first();

        if ($existing) {
            $this->command->warn("⚠️  L'entreprise tunisienne existe déjà (ID: {$existing->id})");
            $this->command->info("Suppression de l'ancienne entreprise...");
            $existing->forceDelete(); // Force delete to bypass soft deletes
        }

        // 1. Créer la company tunisienne
        $company = Company::create([
            'name' => 'Société Tech Tunisie SARL',
            'legal_form' => 'SARL',
            'country_code' => 'TN',
            'vat_number' => '1234567/A/M/000', // Tunisia: same as matricule_fiscal

            // Tunisia-specific fields
            'matricule_fiscal' => '1234567/A/M/000',
            'cnss_employer_number' => '123456',

            // Contact
            'email' => 'contact@techtn.tn',
            'phone' => '+216 71 123 456',
            'website' => 'https://www.techtn.tn',

            // Address
            'street' => 'Avenue Habib Bourguiba',
            'house_number' => '123',
            'postal_code' => '1000',
            'city' => 'Tunis',

            // Settings
            'fiscal_year_start_month' => 1,
            'vat_regime' => 'normal',
            'vat_periodicity' => 'monthly',
            'company_type' => 'standalone',
            'accepts_firm_management' => false,

            // No Peppol for Tunisia
            'peppol_registered' => false,
            'peppol_test_mode' => false,
        ]);

        $this->command->info("✅ Entreprise créée: {$company->name} (ID: {$company->id})");

        // 2. Créer un utilisateur administrateur
        $user = User::create([
            'first_name' => 'Mohamed',
            'last_name' => 'Ben Salah',
            'email' => 'admin@techtn.tn',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->companies()->attach($company->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        $this->command->info("✅ Utilisateur créé: {$user->name} ({$user->email})");
        $this->command->info("   Mot de passe: password");

        // 3. Charger le plan comptable tunisien
        $this->command->info('📚 Chargement du Plan Comptable National (PCN) Tunisien...');

        $seeder = new TunisiaChartOfAccountSeeder();
        $seeder->run($company);

        $accountsCount = $company->chartOfAccounts()->count();
        $this->command->info("✅ {$accountsCount} comptes créés");

        // 4. Créer quelques partenaires tunisiens
        $this->command->info('👥 Création de partenaires tunisiens...');

        $partners = [
            [
                'name' => "STEG (Société Tunisienne de l'Électricité et du Gaz)",
                'type' => 'supplier',
                'vat_number' => '0000001/A/A/000', // Partners use vat_number field
                'email' => 'facturation@steg.com.tn',
                'phone' => '+216 71 341 411',
                'street' => 'Avenue Farhat Hached',
                'postal_code' => '1082',
                'city' => 'Tunis',
                'country_code' => 'TN',
            ],
            [
                'name' => 'Tunisie Telecom',
                'type' => 'supplier',
                'vat_number' => '0000002/B/N/000',
                'email' => 'entreprises@tunisietelecom.tn',
                'phone' => '+216 71 123 000',
                'street' => 'Rue du Lac Windermere',
                'postal_code' => '1053',
                'city' => 'Les Berges du Lac',
                'country_code' => 'TN',
            ],
            [
                'name' => 'Client PME Sfax',
                'type' => 'customer',
                'vat_number' => '9876543/C/M/000',
                'email' => 'contact@pmesfax.tn',
                'phone' => '+216 74 222 333',
                'street' => 'Avenue Hedi Chaker',
                'postal_code' => '3000',
                'city' => 'Sfax',
                'country_code' => 'TN',
            },
        ];

        foreach ($partners as $partnerData) {
            $partner = $company->partners()->create($partnerData);
            $this->command->info("   ✅ {$partner->name}");
        }

        // 5. Créer un employé pour tester la paie
        $this->command->info('👤 Création d\'un employé pour la paie...');

        $employee = $company->employees()->create([
            'first_name' => 'Ahmed',
            'last_name' => 'Trabelsi',
            'email' => 'ahmed.trabelsi@techtn.tn',
            'phone' => '+216 20 123 456',
            'hire_date' => now()->subMonths(6),
            'position' => 'Développeur Senior',
            'department' => 'IT',
            'employment_status' => 'active',
            'country_code' => 'TN',

            // Tunisia-specific
            'cin' => '12345678', // Carte d'Identité Nationale
            'cnss_number' => '987654321', // Numéro CNSS

            // Salary
            'gross_salary' => 2000.000, // 2000 TND
            'payment_frequency' => 'monthly',
            'bank_account' => '12345678901234567890', // RIB tunisien (20 chiffres)
        ]);

        $this->command->info("   ✅ {$employee->first_name} {$employee->last_name} (Salaire: 2000 TND)");

        // 6. Afficher le résumé
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('🇹🇳  ENTREPRISE TUNISIENNE CRÉÉE AVEC SUCCÈS');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->newLine();
        $this->command->table(
            ['Paramètre', 'Valeur'],
            [
                ['Entreprise', $company->name],
                ['Pays', '🇹🇳 Tunisie (TN)'],
                ['Matricule Fiscal', $company->matricule_fiscal],
                ['CNSS Employeur', $company->cnss_employer_number],
                ['Ville', $company->city],
                ['Plan Comptable', 'PCN Tunisien (' . $accountsCount . ' comptes)'],
                ['Partenaires', count($partners)],
                ['Employés', 1],
                ['Email Admin', $user->email],
                ['Mot de passe', 'password'],
            ]
        );

        $this->command->newLine();
        $this->command->info('📋 Informations Fiscales Tunisie:');
        $this->command->info('   • TVA: 19% (défaut), 13%, 7%, 0%');
        $this->command->info('   • CNSS Employé: 9.18%');
        $this->command->info('   • CNSS Employeur: 16.57%');
        $this->command->info('   • IRPP: Progressif (0% à 35%)');
        $this->command->info('   • Devise: TND (Dinar Tunisien) - 3 décimales');
        $this->command->newLine();

        $this->command->info('🎯 Pour tester:');
        $this->command->info('   1. Connectez-vous avec: admin@techtn.tn / password');
        $this->command->info('   2. La paie utilisera automatiquement TunisiaCalculator');
        $this->command->info('   3. Les comptes CNSS (431) et TVA (445) sont déjà créés');
        $this->command->info('   4. Exemple paie: 2000 TND brut → ~1660 TND net');
        $this->command->newLine();
    }
}
