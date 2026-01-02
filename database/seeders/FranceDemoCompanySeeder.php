<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FranceDemoCompanySeeder extends Seeder
{
    /**
     * Seed a complete French demo company.
     */
    public function run(): void
    {
        $this->command->info('🇫🇷 Création d\'une entreprise française de démonstration...');

        // Vérifier si l'entreprise existe déjà
        $existing = Company::where('vat_number', 'FR12345678901')
            ->orWhere('siret', '12345678901234')
            ->first();

        if ($existing) {
            $this->command->warn("⚠️  L'entreprise française existe déjà (ID: {$existing->id})");
            $this->command->info("Suppression de l'ancienne entreprise...");
            $existing->forceDelete();
        }

        // 1. Créer la company française
        $company = Company::create([
            'name' => 'TechSolutions France SAS',
            'legal_form' => 'SAS',
            'country_code' => 'FR',
            'vat_number' => 'FR12345678901',

            // France-specific fields
            'siret' => '12345678901234',
            'siren' => '123456789',
            'ape_code' => '6201Z', // Programmation informatique
            'urssaf_number' => 'UR-123-456-789',
            'convention_collective' => 'IDCC 1486 - Bureaux d\'études techniques (Syntec)',

            // Contact
            'email' => 'contact@techsolutions.fr',
            'phone' => '+33 1 23 45 67 89',
            'website' => 'https://www.techsolutions.fr',

            // Address
            'street' => 'Avenue des Champs-Élysées',
            'house_number' => '75',
            'postal_code' => '75008',
            'city' => 'Paris',

            // Banking - IBAN optionnel (champ encrypté trop long pour demo)
            // 'default_iban' => 'FR1420041010050500013M02606',
            // 'default_bic' => 'BNPAFRPPXXX',

            // Settings
            'fiscal_year_start_month' => 1,
            'vat_regime' => 'normal',
            'vat_periodicity' => 'monthly',
            'company_type' => 'standalone',
            'accepts_firm_management' => false,

            // Peppol (available in France via Chorus Pro)
            'peppol_registered' => true,
            'peppol_id' => '0009:FR12345678901',
            'peppol_test_mode' => true,
        ]);

        $this->command->info("✅ Entreprise créée: {$company->name} (ID: {$company->id})");

        // 2. Créer un utilisateur administrateur (ou récupérer s'il existe)
        $user = User::where('email', 'admin@techsolutions.fr')->first();

        if (!$user) {
            $user = User::create([
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'admin@techsolutions.fr',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Attacher l'utilisateur à l'entreprise s'il ne l'est pas déjà
        if (!$user->companies()->where('company_id', $company->id)->exists()) {
            $user->companies()->attach($company->id, [
                'role' => 'owner',
                'is_default' => true,
            ]);
        }

        $this->command->info("✅ Utilisateur créé: {$user->name} ({$user->email})");
        $this->command->info("   Mot de passe: password");

        // 3. Charger le plan comptable français
        $this->command->info('📚 Chargement du Plan Comptable Général (PCG) Français...');

        $seeder = new FranceChartOfAccountSeeder();
        $seeder->run($company);

        $accountsCount = $company->chartOfAccounts()->count();
        $this->command->info("✅ {$accountsCount} comptes créés");

        // 4. Créer quelques partenaires français
        $this->command->info('👥 Création de partenaires français...');

        $partners = [
            [
                'name' => 'EDF (Électricité de France)',
                'type' => 'supplier',
                'vat_number' => 'FR03552081317',
                'email' => 'entreprises@edf.fr',
                'phone' => '+33 9 69 32 15 15',
                'street' => 'Avenue de Wagram',
                'house_number' => '22-30',
                'postal_code' => '75008',
                'city' => 'Paris',
                'country_code' => 'FR',
            ],
            [
                'name' => 'Orange Business Services',
                'type' => 'supplier',
                'vat_number' => 'FR42380129866',
                'email' => 'contact.pro@orange.fr',
                'phone' => '+33 9 69 36 39 00',
                'street' => 'Place d\'Alleray',
                'house_number' => '1',
                'postal_code' => '75015',
                'city' => 'Paris',
                'country_code' => 'FR',
            ],
            [
                'name' => 'Client Retail Lyon',
                'type' => 'customer',
                'vat_number' => 'FR98765432109',
                'email' => 'contact@retaillyon.fr',
                'phone' => '+33 4 72 00 11 22',
                'street' => 'Rue de la République',
                'house_number' => '15',
                'postal_code' => '69002',
                'city' => 'Lyon',
                'country_code' => 'FR',
            ],
            [
                'name' => 'Startup Bordeaux SARL',
                'type' => 'customer',
                'vat_number' => 'FR55123456789',
                'email' => 'hello@startupbdx.fr',
                'phone' => '+33 5 56 00 99 88',
                'street' => 'Quai des Chartrons',
                'house_number' => '42',
                'postal_code' => '33000',
                'city' => 'Bordeaux',
                'country_code' => 'FR',
            ],
        ];

        foreach ($partners as $partnerData) {
            $partner = $company->partners()->create($partnerData);
            $this->command->info("   ✅ {$partner->name}");
        }

        // Note: La création d'employés est désactivée car la table employees
        // a de nombreux champs requis (street, postal_code, city, etc.)
        // Vous pouvez créer des employés manuellement via l'interface
        $this->command->info('👤 Employés: À créer via l\'interface (champs requis complexes)');

        // 6. Afficher le résumé
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('🇫🇷  ENTREPRISE FRANÇAISE CRÉÉE AVEC SUCCÈS');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->newLine();
        $this->command->table(
            ['Paramètre', 'Valeur'],
            [
                ['Entreprise', $company->name],
                ['Pays', '🇫🇷 France (FR)'],
                ['SIRET', $company->siret],
                ['SIREN', $company->siren],
                ['Code APE', $company->ape_code],
                ['URSSAF', $company->urssaf_number],
                ['Convention', 'Syntec (IDCC 1486)'],
                ['Ville', $company->city],
                ['Plan Comptable', 'PCG Français (' . $accountsCount . ' comptes)'],
                ['Partenaires', count($partners)],
                ['Employés', '0 (à créer via interface)'],
                ['Email Admin', $user->email],
                ['Mot de passe', 'password'],
            ]
        );

        $this->command->newLine();
        $this->command->info('📋 Informations Fiscales France:');
        $this->command->info('   • TVA: 20% (défaut), 10%, 5.5%, 2.1%, 0%');
        $this->command->info('   • Cotisations Salariales: ~22% (CSG/CRDS, retraite, etc.)');
        $this->command->info('   • Cotisations Patronales: ~42% (maladie, chômage, retraite, etc.)');
        $this->command->info('   • Prélèvement à la source: Selon taux personnalisé');
        $this->command->info('   • PSS Mensuel 2024: 3 666 EUR');
        $this->command->info('   • SMIC 2024: 1 766,92 EUR brut');
        $this->command->info('   • Durée légale: 35h/semaine');
        $this->command->info('   • Devise: EUR (Euro) - 2 décimales');
        $this->command->newLine();

        $this->command->info('🎯 Pour tester:');
        $this->command->info('   1. Connectez-vous avec: admin@techsolutions.fr / password');
        $this->command->info('   2. Créez des employés via RH > Employés');
        $this->command->info('   3. La paie utilisera automatiquement FranceCalculator');
        $this->command->info('   4. Les comptes URSSAF (431), TVA (445) sont déjà créés');
        $this->command->info('   5. Exemple paie: 4500 EUR brut → ~3100 EUR net (approx.)');
        $this->command->info('   6. Chorus Pro (Peppol) activé en mode test');
        $this->command->newLine();
    }
}
