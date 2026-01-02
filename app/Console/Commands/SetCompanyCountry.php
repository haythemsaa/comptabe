<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

/**
 * Command pour configurer le pays d'une company (Belgique ou Tunisie)
 *
 * Usage: php artisan company:set-country {company-id} {country-code}
 */
class SetCompanyCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:set-country
                            {company? : ID ou nom de la company}
                            {country? : Code pays (BE pour Belgique, TN pour Tunisie)}
                            {--list : Lister toutes les companies}
                            {--all : Afficher toutes les infos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure le pays d\'une company (BE=Belgique, TN=Tunisie)';

    /**
     * Supported countries configuration.
     */
    protected array $countries = [
        'BE' => [
            'name' => 'Belgique',
            'flag' => '🇧🇪',
            'vat_prefix' => 'BE',
            'vat_digits' => 10,
            'fields' => ['vat_number', 'enterprise_number'],
            'plan_comptable' => 'PCMN (Plan Comptable Minimum Normalisé)',
            'tva_rates' => ['21%', '12%', '6%', '0%'],
            'social_security' => 'ONSS 13.07%',
        ],
        'TN' => [
            'name' => 'Tunisie',
            'flag' => '🇹🇳',
            'vat_prefix' => 'TN',
            'vat_digits' => 7,
            'fields' => ['matricule_fiscal', 'cnss_employer_number'],
            'plan_comptable' => 'Système Comptable des Entreprises (SCE)',
            'tva_rates' => ['19%', '13%', '7%', '0%'],
            'social_security' => 'CNSS (Caisse Nationale de Sécurité Sociale)',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listCompanies();
        }

        $companyIdentifier = $this->argument('company');
        $countryCode = $this->argument('country');

        // Mode interactif si pas d'arguments
        if (!$companyIdentifier) {
            return $this->interactiveMode();
        }

        // Trouver la company
        $company = $this->findCompany($companyIdentifier);

        if (!$company) {
            $this->error("❌ Company '{$companyIdentifier}' introuvable.");
            $this->info("💡 Utilisez --list pour voir toutes les companies disponibles.");
            return Command::FAILURE;
        }

        // Demander le pays si non fourni
        if (!$countryCode) {
            $countryCode = $this->choice(
                "Quel pays pour '{$company->name}' ?",
                ['BE' => '🇧🇪 Belgique', 'TN' => '🇹🇳 Tunisie'],
                $company->country_code ?? 'BE'
            );
        }

        $countryCode = strtoupper($countryCode);

        if (!isset($this->countries[$countryCode])) {
            $this->error("❌ Code pays invalide. Utilisez BE (Belgique) ou TN (Tunisie).");
            return Command::FAILURE;
        }

        return $this->setCountry($company, $countryCode);
    }

    /**
     * Mode interactif pour sélectionner company et pays.
     */
    protected function interactiveMode(): int
    {
        $this->info("🌍 Configuration Pays Company - Mode Interactif");
        $this->newLine();

        $companies = Company::orderBy('name')->get();

        if ($companies->isEmpty()) {
            $this->error("❌ Aucune company trouvée dans la base de données.");
            return Command::FAILURE;
        }

        $choices = $companies->mapWithKeys(function ($company) {
            $flag = $this->countries[$company->country_code]['flag'] ?? '🏴';
            return [$company->id => "{$flag} {$company->name} ({$company->country_code})"];
        })->toArray();

        $companyId = $this->choice('Sélectionnez une company', $choices);

        $company = $companies->firstWhere('id', $companyId);

        $countryCode = $this->choice(
            "Quel pays pour '{$company->name}' ?",
            ['BE' => '🇧🇪 Belgique', 'TN' => '🇹🇳 Tunisie'],
            $company->country_code ?? 'BE'
        );

        return $this->setCountry($company, $countryCode);
    }

    /**
     * Configurer le pays d'une company.
     */
    protected function setCountry(Company $company, string $countryCode): int
    {
        $country = $this->countries[$countryCode];

        $this->info("📋 Configuration pays pour: {$company->name}");
        $this->info("   Pays actuel: " . ($this->countries[$company->country_code]['flag'] ?? '🏴') . " {$company->country_code}");
        $this->info("   Nouveau pays: {$country['flag']} {$country['name']}");
        $this->newLine();

        if ($company->country_code === $countryCode) {
            $this->warn("⚠️  La company est déjà configurée pour {$country['name']}.");

            if (!$this->confirm('Voulez-vous reconfigurer quand même ?')) {
                return Command::SUCCESS;
            }
        }

        // Afficher les changements
        $this->info("📝 Modifications à apporter:");
        $this->info("   ✅ country_code: '{$company->country_code}' → '{$countryCode}'");
        $this->info("   ✅ Plan comptable: {$country['plan_comptable']}");
        $this->info("   ✅ TVA: " . implode(', ', $country['tva_rates']));
        $this->info("   ✅ Sécurité sociale: {$country['social_security']}");

        if ($countryCode === 'TN') {
            $this->warn("   ℹ️  Champs Tunisie disponibles:");
            $this->warn("      - matricule_fiscal (Matricule Fiscal)");
            $this->warn("      - cnss_employer_number (Numéro Employeur CNSS)");
        }

        $this->newLine();

        if (!$this->confirm("Confirmer le changement de pays pour '{$company->name}' ?")) {
            $this->info('Opération annulée.');
            return Command::SUCCESS;
        }

        // Mise à jour
        $updates = ['country_code' => $countryCode];

        // Si Tunisie, demander les infos spécifiques
        if ($countryCode === 'TN') {
            if ($this->confirm('Ajouter le Matricule Fiscal maintenant ?')) {
                $matricule = $this->ask('Matricule Fiscal (ex: 1234567A/M/000)');
                if ($matricule) {
                    $updates['matricule_fiscal'] = $matricule;
                }
            }

            if ($this->confirm('Ajouter le Numéro Employeur CNSS maintenant ?')) {
                $cnss = $this->ask('Numéro Employeur CNSS');
                if ($cnss) {
                    $updates['cnss_employer_number'] = $cnss;
                }
            }
        }

        $company->update($updates);

        $this->newLine();
        $this->info("✅ Pays configuré avec succès!");
        $this->newLine();

        $this->displayCompanyInfo($company->fresh());

        return Command::SUCCESS;
    }

    /**
     * Afficher les infos d'une company.
     */
    protected function displayCompanyInfo(Company $company): void
    {
        $country = $this->countries[$company->country_code];

        $this->info("📊 Informations Company:");
        $this->info("   Nom: {$company->name}");
        $this->info("   Pays: {$country['flag']} {$country['name']} ({$company->country_code})");

        if ($company->country_code === 'BE') {
            $this->info("   TVA: {$company->vat_number}");
            $this->info("   N° Entreprise: {$company->enterprise_number}");
        } elseif ($company->country_code === 'TN') {
            $this->info("   Matricule Fiscal: " . ($company->matricule_fiscal ?: 'Non défini'));
            $this->info("   CNSS Employeur: " . ($company->cnss_employer_number ?: 'Non défini'));
        }

        $this->newLine();
        $this->info("📚 Informations Comptables:");
        $this->info("   Plan comptable: {$country['plan_comptable']}");
        $this->info("   Taux TVA: " . implode(', ', $country['tva_rates']));
        $this->info("   Sécurité sociale: {$country['social_security']}");
    }

    /**
     * Lister toutes les companies.
     */
    protected function listCompanies(): int
    {
        $companies = Company::orderBy('name')->get();

        if ($companies->isEmpty()) {
            $this->warn("⚠️  Aucune company trouvée.");
            return Command::SUCCESS;
        }

        $this->info("📋 Liste des Companies ({$companies->count()}):");
        $this->newLine();

        $rows = $companies->map(function ($company) {
            $country = $this->countries[$company->country_code] ?? ['flag' => '🏴', 'name' => $company->country_code];

            return [
                'id' => $company->id,
                'name' => $company->name,
                'country' => "{$country['flag']} {$company->country_code}",
                'vat' => $company->vat_number ?: $company->matricule_fiscal ?: 'N/A',
                'type' => $company->company_type ?? 'standalone',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Nom', 'Pays', 'TVA/Matricule', 'Type'],
            $rows
        );

        $this->newLine();
        $this->info("💡 Usage: php artisan company:set-country {id-ou-nom} {BE|TN}");

        return Command::SUCCESS;
    }

    /**
     * Trouver une company par ID ou nom.
     */
    protected function findCompany(string $identifier): ?Company
    {
        // Essayer par ID (UUID)
        if (strlen($identifier) === 36 && str_contains($identifier, '-')) {
            return Company::find($identifier);
        }

        // Essayer par nom (recherche partielle)
        return Company::where('name', 'LIKE', "%{$identifier}%")->first();
    }
}
