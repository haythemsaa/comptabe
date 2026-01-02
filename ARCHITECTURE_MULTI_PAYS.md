# 🌍 Architecture Multi-Pays - ComptaBE

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture technique](#architecture-technique)
3. [Structure des fichiers](#structure-des-fichiers)
4. [Ajouter un nouveau pays](#ajouter-un-nouveau-pays)
5. [Exemples de pays](#exemples-de-pays)

---

## 🎯 Vue d'ensemble

ComptaBE est une application comptable **multi-pays** et **multi-devises** qui s'adapte automatiquement aux réglementations fiscales et comptables de chaque pays.

### Pays actuellement supportés
- 🇧🇪 **Belgique** (BE) - Complet
- 🇹🇳 **Tunisie** (TN) - Complet
- 🇫🇷 **France** (FR) - Partiel
- 🇳🇱 **Pays-Bas** (NL) - Partiel
- 🇱🇺 **Luxembourg** (LU) - Partiel
- 🇩🇪 **Allemagne** (DE) - Partiel

### Devises supportées
- EUR, TND, USD, GBP, CHF, MAD, CAD, DZD

---

## 🏗️ Architecture technique

### 1️⃣ Niveau Entreprise (Company)

Chaque entreprise a un **pays de base** qui détermine :
- 💱 Devise par défaut
- 📊 Plan comptable
- 🧾 Taux de TVA
- 👥 Système de sécurité sociale (ONSS/CNSS/etc.)
- 💰 Règles de paie
- 📑 Obligations fiscales

**Fichier clé** : `app/Models/Company.php`

```php
// Méthodes importantes
getCurrency()              // Devise de l'entreprise
getCurrencySymbol()        // Symbole (€, د.ت, etc.)
getDecimalPlaces()         // Nombre de décimales (2 ou 3)
getVatRates()             // Taux TVA du pays
getDefaultVatRate()        // Taux TVA par défaut
getSocialSecurityOrg()     // ONSS, CNSS, URSSAF, etc.
isTunisia()               // Vérification pays
isBelgium()               // Vérification pays
```

### 2️⃣ Niveau Document (Invoice, Quote, etc.)

Chaque document peut avoir **sa propre devise**, indépendante de l'entreprise.

**Exemple** : Une entreprise tunisienne (base TND) peut facturer en EUR, USD, ou toute autre devise.

**Champs dans la table `invoices`** :
- `currency` (CHAR(3)) : EUR, TND, USD, etc.
- `exchange_rate` (DECIMAL) : Taux de change au moment de la facturation

### 3️⃣ Configuration centralisée

#### A. Configuration des pays (`config/countries.php`)

```php
'TN' => [
    'name' => 'Tunisie',
    'currency' => 'TND',
    'currency_symbol' => 'د.ت',
    'decimal_places' => 3,
    'vat' => [
        'rates' => [19, 13, 7, 0],
        'default_rate' => 19,
    ],
    'payroll' => [
        'social_security' => [
            'employee_rate' => 9.18,
            'employer_rate' => 16.57,
            'organization' => 'CNSS',
        ],
        'income_tax' => [
            'type' => 'progressive',
            'brackets' => [
                ['min' => 0, 'max' => 5000, 'rate' => 0],
                ['min' => 5000, 'max' => 20000, 'rate' => 26],
                ['min' => 20000, 'max' => 30000, 'rate' => 28],
                ['min' => 30000, 'max' => 50000, 'rate' => 32],
                ['min' => 50000, 'max' => null, 'rate' => 35],
            ],
        ],
    ],
],
```

#### B. Configuration des devises (`config/currencies.php`)

```php
'TND' => [
    'name' => 'Dinar Tunisien',
    'symbol' => 'د.ت',
    'decimal_places' => 3,
    'locale' => 'fr-TN',
],
```

### 4️⃣ Plans comptables par pays

Chaque pays a son propre plan comptable standardisé.

**Seeders** :
- `BelgiumChartOfAccountSeeder.php` - PCMN belge
- `TunisiaChartOfAccountSeeder.php` - PCN tunisien
- `FranceChartOfAccountSeeder.php` - PCG français (à compléter)

**Structure** :
```
Class 1 : Capitaux permanents
Class 2 : Immobilisations
Class 3 : Stocks
Class 4 : Créances et dettes
Class 5 : Placements et trésorerie
Class 6 : Charges
Class 7 : Produits
```

### 5️⃣ Calculateurs de paie par pays

Chaque pays a sa propre logique de calcul de salaire.

**Calculateurs** :
- `app/Services/Payroll/Calculators/BelgiumCalculator.php`
- `app/Services/Payroll/Calculators/TunisiaCalculator.php`
- `app/Services/Payroll/Calculators/FranceCalculator.php` (à créer)

**Interface commune** : `PayrollCalculatorInterface`

```php
interface PayrollCalculatorInterface
{
    public function calculate(Employee $employee, Carbon $period): array;
    public function calculateSocialSecurity(float $grossSalary): array;
    public function calculateIncomeTax(float $taxableIncome): float;
}
```

### 6️⃣ View Composer pour partage des données

**Fichier** : `app/View/Composers/CompanyConfigComposer.php`

Partage automatiquement avec **toutes les vues** :
- `$currentCompany`
- `$companyCurrency`
- `$companyCurrencySymbol`
- `$companyDecimalPlaces`
- `$companyVatRates`
- `$companyDefaultVatRate`
- `$companySocialSecurityOrg`
- `$companyIsTunisia`
- `$companyIsBelgium`
- `$companyCountryCode`
- `$companyCountryName`

**Enregistré dans** : `app/Providers/AppServiceProvider.php`

```php
View::composer('*', CompanyConfigComposer::class);
```

---

## 📁 Structure des fichiers

```
compta/
├── app/
│   ├── Models/
│   │   └── Company.php                 # Méthodes getCountryConfig(), getCurrency(), etc.
│   ├── Services/
│   │   └── Payroll/
│   │       ├── Calculators/
│   │       │   ├── BelgiumCalculator.php
│   │       │   ├── TunisiaCalculator.php
│   │       │   └── PayrollCalculatorInterface.php
│   │       └── PayrollService.php      # Sélection auto du calculateur
│   ├── View/Composers/
│   │   └── CompanyConfigComposer.php   # Partage config avec vues
│   └── Providers/
│       └── AppServiceProvider.php      # Enregistrement ViewComposer
│
├── config/
│   ├── countries.php                   # ⭐ Configuration pays
│   └── currencies.php                  # ⭐ Configuration devises
│
├── database/
│   ├── migrations/
│   │   ├── *_create_companies_table.php
│   │   ├── *_add_tunisia_fields_to_companies_table.php
│   │   └── *_create_invoices_table.php
│   └── seeders/
│       ├── BelgiumChartOfAccountSeeder.php
│       ├── TunisiaChartOfAccountSeeder.php
│       ├── BelgiumDemoCompanySeeder.php
│       └── TunisiaDemoCompanySeeder.php
│
└── resources/views/
    ├── dashboard/index.blade.php       # Utilise $companyCurrency
    ├── invoices/
    │   ├── create.blade.php           # Sélecteur de devise
    │   ├── show.blade.php             # Affiche devise de la facture
    │   └── pdf.blade.php              # PDF avec devise dynamique
    └── payroll/
        └── payslips/show.blade.php    # Affiche ONSS/CNSS selon pays
```

---

## ➕ Ajouter un nouveau pays

### 🇲🇦 Exemple : Ajouter le Maroc

#### Étape 1 : Configuration du pays

**Fichier** : `config/countries.php`

```php
'MA' => [
    'name' => 'Maroc',
    'currency' => 'MAD',
    'currency_symbol' => 'MAD',
    'decimal_places' => 2,

    'vat' => [
        'rates' => [20, 14, 10, 7, 0],  // Taux TVA marocains
        'default_rate' => 20,
    ],

    'payroll' => [
        'social_security' => [
            'employee_rate' => 4.48,      // CNSS Maroc
            'employer_rate' => 17.93,
            'organization' => 'CNSS',
            'ceiling' => 6000,            // Plafond mensuel
        ],
        'income_tax' => [
            'type' => 'progressive',
            'brackets' => [
                ['min' => 0, 'max' => 30000, 'rate' => 0],
                ['min' => 30000, 'max' => 50000, 'rate' => 10],
                ['min' => 50000, 'max' => 60000, 'rate' => 20],
                ['min' => 60000, 'max' => 80000, 'rate' => 30],
                ['min' => 80000, 'max' => 180000, 'rate' => 34],
                ['min' => 180000, 'max' => null, 'rate' => 38],
            ],
        ],
    ],

    'accounting' => [
        'chart_type' => 'CGNC',         // Code Général de Normalisation Comptable
        'fiscal_year_start' => 1,       // 1er janvier
    ],
],
```

#### Étape 2 : Ajouter la devise (si pas déjà présente)

**Fichier** : `config/currencies.php`

```php
'MAD' => [
    'name' => 'Dirham Marocain',
    'symbol' => 'MAD',
    'decimal_places' => 2,
    'locale' => 'fr-MA',
],
```

#### Étape 3 : Migration pour champs spécifiques

**Créer** : `database/migrations/*_add_morocco_fields_to_companies_table.php`

```php
Schema::table('companies', function (Blueprint $table) {
    $table->string('ice', 15)->nullable()
        ->comment('Identifiant Commun de l\'Entreprise');

    $table->string('cnss_employer_number', 20)->nullable()
        ->comment('Numéro d\'affiliation CNSS Maroc');

    $table->string('patente', 20)->nullable()
        ->comment('Numéro de patente');
});
```

#### Étape 4 : Plan comptable marocain

**Créer** : `database/seeders/MoroccoChartOfAccountSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MoroccoChartOfAccountSeeder extends Seeder
{
    public function run(Company $company = null)
    {
        if (!$company) {
            throw new \Exception('Company is required');
        }

        $accounts = [
            // CLASSE 1 : COMPTES DE FINANCEMENT PERMANENT
            ['1111', 'Capital social', 1, 'equity', 100000],
            ['1140', 'Réserves légales', 1, 'equity', 110000],
            ['1148', 'Autres réserves', 1, 'equity', 110000],
            ['1161', 'Report à nouveau (solde créditeur)', 1, 'equity', 110000],
            ['1481', 'Emprunts auprès des établissements de crédit', 1, 'liability', 160000],

            // CLASSE 2 : COMPTES D'ACTIF IMMOBILISÉ
            ['2321', 'Bâtiments', 2, 'asset', 210000],
            ['2340', 'Matériel de transport', 2, 'asset', 218000],
            ['2351', 'Mobilier de bureau', 2, 'asset', 218300],
            ['2352', 'Matériel de bureau', 2, 'asset', 218300],
            ['2355', 'Matériel informatique', 2, 'asset', 218300],

            // CLASSE 3 : COMPTES D'ACTIF CIRCULANT
            ['3111', 'Marchandises en stock', 3, 'asset', 310000],
            ['3121', 'Matières premières', 3, 'asset', 312000],
            ['3421', 'Clients', 3, 'asset', 342100],
            ['3424', 'Clients douteux ou litigieux', 3, 'asset', 342400],
            ['3425', 'Clients - effets à recevoir', 3, 'asset', 342500],

            // CLASSE 4 : COMPTES DE PASSIF CIRCULANT
            ['4411', 'Fournisseurs', 4, 'liability', 441100],
            ['4415', 'Fournisseurs - effets à payer', 4, 'liability', 441500],
            ['4432', 'Rémunérations dues au personnel', 4, 'liability', 443200],
            ['4441', 'CNSS', 4, 'liability', 444100],
            ['4452', 'État - TVA facturée', 4, 'liability', 445200],
            ['4455', 'État - TVA récupérable', 4, 'asset', 445500],
            ['4457', 'État - impôts et taxes', 4, 'liability', 445700],
            ['4458', 'État - organismes sociaux', 4, 'liability', 445800],

            // CLASSE 5 : COMPTES DE TRÉSORERIE
            ['5141', 'Banques (soldes débiteurs)', 5, 'asset', 514100],
            ['5161', 'Caisses', 5, 'asset', 516100],

            // CLASSE 6 : COMPTES DE CHARGES
            ['6111', 'Achats de marchandises', 6, 'expense', 611100],
            ['6121', 'Achats de matières premières', 6, 'expense', 612100],
            ['6171', 'Variation de stock de marchandises', 6, 'expense', 617100],
            ['6174', 'Variation de stock de matières', 6, 'expense', 617400],
            ['6311', 'Locations et charges locatives', 6, 'expense', 631100],
            ['6340', 'Rémunérations du personnel', 6, 'expense', 634000],
            ['6344', 'Charges sociales', 6, 'expense', 634400],
            ['6363', 'Taxes sur salaires', 6, 'expense', 636300],
            ['6380', 'Honoraires', 6, 'expense', 638000],
            ['6393', 'Documentation générale', 6, 'expense', 639300],
            ['6513', 'Assurances', 6, 'expense', 651300],

            // CLASSE 7 : COMPTES DE PRODUITS
            ['7111', 'Ventes de marchandises', 7, 'revenue', 711100],
            ['7121', 'Ventes de produits finis', 7, 'revenue', 712100],
            ['7124', 'Ventes de produits accessoires', 7, 'revenue', 712400],
            ['7127', 'Ventes de produits résiduels', 7, 'revenue', 712700],
            ['7381', 'Intérêts et produits assimilés', 7, 'revenue', 738100],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create([
                'company_id' => $company->id,
                'account_number' => $account[0],
                'account_name' => $account[1],
                'account_class' => $account[2],
                'account_type' => $account[3],
                'pcmn_code' => $account[4],
                'is_active' => true,
                'is_postable' => strlen($account[0]) >= 4, // Comptes ≥ 4 chiffres postables
            ]);
        }
    }
}
```

#### Étape 5 : Calculateur de paie marocain

**Créer** : `app/Services/Payroll/Calculators/MoroccoCalculator.php`

```php
<?php

namespace App\Services\Payroll\Calculators;

use App\Models\Employee;
use Carbon\Carbon;

class MoroccoCalculator implements PayrollCalculatorInterface
{
    public function calculate(Employee $employee, Carbon $period): array
    {
        $grossSalary = $employee->gross_salary;

        // 1. Calcul CNSS
        $cnss = $this->calculateSocialSecurity($grossSalary);

        // 2. Base imposable = Brut - CNSS employé
        $taxableIncome = $grossSalary - $cnss['employee'];

        // 3. Déductions (frais professionnels 20%, max 2500 MAD/mois)
        $professionalExpenses = min($taxableIncome * 0.20, 2500);
        $taxableIncome -= $professionalExpenses;

        // 4. Calcul IR (Impôt sur le Revenu)
        $incomeTax = $this->calculateIncomeTax($taxableIncome);

        // 5. Net à payer
        $netSalary = $grossSalary - $cnss['employee'] - $incomeTax;

        return [
            'gross_salary' => $grossSalary,
            'employee_social_security' => $cnss['employee'],
            'employer_social_security' => $cnss['employer'],
            'employee_social_security_rate' => 4.48,
            'employer_social_security_rate' => 17.93,
            'income_tax' => $incomeTax,
            'taxable_income' => $taxableIncome,
            'professional_expenses' => $professionalExpenses,
            'net_salary' => $netSalary,
            'total_employer_cost' => $grossSalary + $cnss['employer'],
        ];
    }

    public function calculateSocialSecurity(float $grossSalary): array
    {
        $ceiling = 6000; // Plafond mensuel CNSS Maroc
        $baseSalary = min($grossSalary, $ceiling);

        return [
            'employee' => round($baseSalary * 0.0448, 2),  // 4.48%
            'employer' => round($baseSalary * 0.1793, 2),  // 17.93%
        ];
    }

    public function calculateIncomeTax(float $taxableIncome): float
    {
        $brackets = config('countries.MA.payroll.income_tax.brackets');
        $tax = 0;
        $previousMax = 0;

        foreach ($brackets as $bracket) {
            if ($taxableIncome <= $bracket['min']) {
                break;
            }

            $applicableIncome = min($taxableIncome, $bracket['max'] ?? $taxableIncome) - $bracket['min'];
            $tax += $applicableIncome * ($bracket['rate'] / 100);

            if ($bracket['max'] && $taxableIncome <= $bracket['max']) {
                break;
            }
        }

        return round($tax, 2);
    }
}
```

#### Étape 6 : Modifier le PayrollService

**Fichier** : `app/Services/Payroll/PayrollService.php`

```php
public function getCalculator(string $countryCode): PayrollCalculatorInterface
{
    return match($countryCode) {
        'BE' => new BelgiumCalculator(),
        'TN' => new TunisiaCalculator(),
        'MA' => new MoroccoCalculator(),  // ⭐ Ajouter ici
        'FR' => new FranceCalculator(),
        default => throw new \Exception("Pas de calculateur pour le pays: $countryCode"),
    };
}
```

#### Étape 7 : Ajouter dans l'interface admin

**Fichier** : `resources/views/admin/companies/edit.blade.php`

```html
<select name="country_code">
    <option value="BE">🇧🇪 Belgique</option>
    <option value="TN">🇹🇳 Tunisie</option>
    <option value="MA">🇲🇦 Maroc</option>  <!-- ⭐ Ajouter ici -->
    <option value="FR">🇫🇷 France</option>
    <option value="NL">🇳🇱 Pays-Bas</option>
</select>
```

#### Étape 8 : Ajouter méthode helper au modèle Company

**Fichier** : `app/Models/Company.php`

```php
public function isMorocco(): bool
{
    return $this->country_code === 'MA';
}
```

#### Étape 9 : Créer un seeder de démo

**Créer** : `database/seeders/MoroccoDemoCompanySeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MoroccoDemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name' => 'Société Marocaine SARL',
            'legal_form' => 'SARL',
            'country_code' => 'MA',
            'vat_number' => '12345678',
            'ice' => '001234567890001',
            'cnss_employer_number' => '1234567',
            'email' => 'contact@societe.ma',
            'phone' => '+212 5 22 12 34 56',
            'street' => 'Boulevard Mohammed V',
            'house_number' => '50',
            'postal_code' => '20000',
            'city' => 'Casablanca',
            'vat_regime' => 'normal',
        ]);

        $user = User::create([
            'first_name' => 'Ahmed',
            'last_name' => 'Benali',
            'email' => 'admin@societe.ma',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $user->companies()->attach($company->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        // Charger le plan comptable CGNC
        $seeder = new MoroccoChartOfAccountSeeder();
        $seeder->run($company);
    }
}
```

#### Étape 10 : Tests

Créer les tests unitaires :

```php
// tests/Feature/MoroccoPayrollTest.php
public function test_morocco_payroll_calculation()
{
    $employee = Employee::factory()->create([
        'gross_salary' => 5000, // 5000 MAD
        'country_code' => 'MA',
    ]);

    $calculator = new MoroccoCalculator();
    $result = $calculator->calculate($employee, now());

    // CNSS employé : 5000 * 4.48% = 224 MAD
    $this->assertEquals(224, $result['employee_social_security']);

    // CNSS employeur : 5000 * 17.93% = 896.5 MAD
    $this->assertEquals(896.5, $result['employer_social_security']);
}
```

---

## 📊 Exemples de pays à ajouter

### 🇩🇿 Algérie

**Spécificités** :
- Devise : DZD (Dinar Algérien)
- TVA : 19% (défaut), 9%, 0%
- Sécurité sociale : CNAS (9% employé, 26% employeur)
- Impôt : IRG progressif (0% à 35%)
- Plan comptable : SCF (Système Comptable Financier)

### 🇫🇷 France

**Spécificités** :
- Devise : EUR
- TVA : 20%, 10%, 5.5%, 2.1%, 0%
- Sécurité sociale : URSSAF (très complexe, ~22% employé, ~42% employeur)
- Impôt : IR progressif (0% à 45%)
- Plan comptable : PCG (Plan Comptable Général)
- DSN (Déclaration Sociale Nominative) mensuelle

### 🇸🇳 Sénégal

**Spécificités** :
- Devise : XOF (Franc CFA)
- TVA : 18% (défaut), 10%, 0%
- Sécurité sociale : CSS (5.6% employé, 8.4% employeur)
- Impôt : IRPP progressif
- Plan comptable : SYSCOHADA révisé

### 🇨🇮 Côte d'Ivoire

**Spécificités** :
- Devise : XOF (Franc CFA)
- TVA : 18%
- Sécurité sociale : CNPS
- Plan comptable : SYSCOHADA

---

## 🎯 Checklist complète pour ajouter un pays

- [ ] **Configuration**
  - [ ] Ajouter dans `config/countries.php`
  - [ ] Ajouter devise dans `config/currencies.php` (si nouvelle)
  - [ ] Définir taux TVA
  - [ ] Définir règles de paie
  - [ ] Définir barème fiscal

- [ ] **Base de données**
  - [ ] Créer migration pour champs spécifiques
  - [ ] Exécuter migration

- [ ] **Plan comptable**
  - [ ] Créer `{Pays}ChartOfAccountSeeder.php`
  - [ ] Référencer plan comptable officiel du pays
  - [ ] Créer comptes classes 1-7

- [ ] **Calculateur de paie**
  - [ ] Créer `{Pays}Calculator.php`
  - [ ] Implémenter `PayrollCalculatorInterface`
  - [ ] Calculer cotisations sociales
  - [ ] Calculer impôt sur le revenu
  - [ ] Ajouter au `PayrollService`

- [ ] **Interface utilisateur**
  - [ ] Ajouter pays dans dropdown admin
  - [ ] Ajouter méthode `is{Pays}()` au modèle Company
  - [ ] Créer vues spécifiques si nécessaire

- [ ] **Données de démonstration**
  - [ ] Créer `{Pays}DemoCompanySeeder.php`
  - [ ] Créer entreprise exemple
  - [ ] Créer partenaires exemples
  - [ ] Créer employé exemple

- [ ] **Tests**
  - [ ] Tests unitaires calculateur
  - [ ] Tests d'intégration
  - [ ] Validation conformité fiscale

- [ ] **Documentation**
  - [ ] Documenter particularités du pays
  - [ ] Ajouter exemples
  - [ ] Mettre à jour README

---

## 🔧 Outils et ressources

### Documentation fiscale par pays

- **Belgique** : SPF Finances, ONSS
- **Tunisie** : CNSS, Ministère des Finances
- **Maroc** : CNSS Maroc, DGI
- **France** : URSSAF, Impots.gouv.fr
- **Algérie** : CNAS, DGI

### APIs utiles

- **Taux de change** :
  - ExchangeRate-API.com
  - Fixer.io
  - Open Exchange Rates

- **Numéros TVA** :
  - VIES (UE) : https://ec.europa.eu/taxation_customs/vies/

- **Plans comptables** :
  - Disponibles sur sites officiels des ministères des finances

---

## 📞 Support

Pour ajouter un nouveau pays ou obtenir de l'aide :
1. Consulter cette documentation
2. Vérifier les exemples existants (Belgique, Tunisie)
3. Suivre la checklist étape par étape
4. Tester avec données de démonstration

---

**Date de dernière mise à jour** : 2025-12-31
**Version** : 2.0
**Auteur** : Claude AI + ComptaBE Team
