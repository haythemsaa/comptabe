# 🇲🇦 Guide Pratique : Ajouter le Maroc

Ce guide vous montre **étape par étape** comment ajouter le support du Maroc dans ComptaBE.

---

## 📋 Prérequis

- [ ] Connaître les réglementations fiscales marocaines
- [ ] Avoir accès au CGNC (Code Général de Normalisation Comptable)
- [ ] Connaître les taux CNSS et barème IR marocains
- [ ] Environnement de développement Laravel configuré

---

## ✅ Étape 1 : Configuration du pays

### Fichier : `config/countries.php`

**Ajoutez** après la configuration de la Tunisie :

```php
'MA' => [
    'name' => 'Maroc',
    'currency' => 'MAD',
    'currency_symbol' => 'MAD',
    'decimal_places' => 2,

    'vat' => [
        'rates' => [20, 14, 10, 7, 0],
        'default_rate' => 20,
        'exemptions' => [
            // Produits de première nécessité
            'pain', 'lait', 'sucre',
            // Services médicaux
            'soins_medicaux',
        ],
    ],

    'payroll' => [
        'social_security' => [
            'employee_rate' => 4.48,
            'employer_rate' => 17.93,
            'organization' => 'CNSS',
            'ceiling' => 6000, // Plafond mensuel en MAD
            'components' => [
                'maladie' => ['employee' => 0, 'employer' => 0.67],
                'at' => ['employee' => 0, 'employer' => 0.67], // Accidents de travail
                'pf' => ['employee' => 4.48, 'employer' => 7.93], // Prestations familiales + court terme
                'amo' => ['employee' => 0, 'employer' => 2.26], // Assurance maladie obligatoire
                'formation' => ['employee' => 0, 'employer' => 1.6],
            ],
        ],
        'income_tax' => [
            'type' => 'progressive',
            'name' => 'IR', // Impôt sur le Revenu
            'brackets' => [
                ['min' => 0, 'max' => 30000, 'rate' => 0, 'deduction' => 0],
                ['min' => 30001, 'max' => 50000, 'rate' => 10, 'deduction' => 3000],
                ['min' => 50001, 'max' => 60000, 'rate' => 20, 'deduction' => 8000],
                ['min' => 60001, 'max' => 80000, 'rate' => 30, 'deduction' => 14000],
                ['min' => 80001, 'max' => 180000, 'rate' => 34, 'deduction' => 17200],
                ['min' => 180001, 'max' => null, 'rate' => 38, 'deduction' => 24400],
            ],
            'deductions' => [
                'frais_professionnels' => 0.20, // 20% avec plafond 2500 MAD/mois
                'frais_professionnels_max' => 2500,
            ],
        ],
    ],

    'accounting' => [
        'chart_type' => 'CGNC',
        'chart_name' => 'Code Général de Normalisation Comptable',
        'fiscal_year_start' => 1, // 1er janvier
        'fiscal_year_end' => 12,  // 31 décembre
    ],

    'legal_forms' => [
        'SARL' => 'Société à Responsabilité Limitée',
        'SARL AU' => 'SARL à Associé Unique',
        'SA' => 'Société Anonyme',
        'SNC' => 'Société en Nom Collectif',
        'SCS' => 'Société en Commandite Simple',
        'SCA' => 'Société en Commandite par Actions',
    ],

    'identifiers' => [
        'ice' => [
            'name' => 'ICE',
            'full_name' => 'Identifiant Commun de l\'Entreprise',
            'format' => '000000000000000', // 15 chiffres
            'required' => true,
        ],
        'rc' => [
            'name' => 'RC',
            'full_name' => 'Registre de Commerce',
            'required' => true,
        ],
        'patente' => [
            'name' => 'Patente',
            'full_name' => 'Numéro de Patente',
            'required' => true,
        ],
        'cnss' => [
            'name' => 'CNSS',
            'full_name' => 'Numéro d\'affiliation CNSS',
            'required' => true,
        ],
        'if' => [
            'name' => 'IF',
            'full_name' => 'Identifiant Fiscal',
            'required' => true,
        ],
    ],

    'declarations' => [
        'tva' => [
            'frequency' => 'monthly', // ou 'quarterly' selon régime
            'deadline_day' => 20, // 20 du mois suivant
        ],
        'ir_salaries' => [
            'frequency' => 'monthly',
            'deadline_day' => 10, // 10 du mois suivant
        ],
        'cnss' => [
            'frequency' => 'monthly',
            'deadline_day' => 10,
        ],
        'bilan' => [
            'frequency' => 'yearly',
            'deadline_months_after_year_end' => 3,
        ],
    ],
],
```

**Vérifiez** avec :

```bash
php artisan tinker
>>> config('countries.MA.name')
=> "Maroc"
>>> config('countries.MA.vat.rates')
=> [20, 14, 10, 7, 0]
```

---

## ✅ Étape 2 : Ajouter la devise MAD

### Fichier : `config/currencies.php`

La devise MAD existe déjà, mais **vérifiez** :

```php
'MAD' => [
    'name' => 'Dirham Marocain',
    'symbol' => 'MAD',
    'decimal_places' => 2,
    'locale' => 'fr-MA',
],
```

---

## ✅ Étape 3 : Migration pour champs spécifiques

### Créer la migration

```bash
php artisan make:migration add_morocco_fields_to_companies_table
```

### Fichier : `database/migrations/*_add_morocco_fields_to_companies_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Identifiant Commun de l'Entreprise (obligatoire au Maroc)
            $table->string('ice', 15)->nullable()->after('vat_number')
                ->comment('ICE - Identifiant Commun de l\'Entreprise (15 chiffres)');

            // Registre de Commerce
            $table->string('rc', 20)->nullable()->after('ice')
                ->comment('Numéro de Registre de Commerce');

            // Patente
            $table->string('patente', 20)->nullable()->after('rc')
                ->comment('Numéro de Patente');

            // CNSS Maroc
            $table->string('cnss_employer_number', 20)->nullable()->after('patente')
                ->comment('Numéro d\'affiliation CNSS Maroc');

            // Identifiant Fiscal
            $table->string('if', 20)->nullable()->after('cnss_employer_number')
                ->comment('IF - Identifiant Fiscal');

            // Index pour recherche
            $table->index('ice');
        });

        Schema::table('employees', function (Blueprint $table) {
            // CIN (Carte d'Identité Nationale) pour employés marocains
            $table->string('cin', 10)->nullable()->after('email')
                ->comment('Numéro de Carte d\'Identité Nationale');

            // CNSS employé
            $table->string('cnss_number', 20)->nullable()->after('cin')
                ->comment('Numéro d\'immatriculation CNSS');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['ice']);
            $table->dropColumn(['ice', 'rc', 'patente', 'cnss_employer_number', 'if']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['cin', 'cnss_number']);
        });
    }
};
```

### Exécuter la migration

```bash
php artisan migrate
```

**Résultat attendu** :

```
Migrating: *_add_morocco_fields_to_companies_table
Migrated: *_add_morocco_fields_to_companies_table (45.23ms)
```

---

## ✅ Étape 4 : Plan comptable CGNC

### Créer le seeder

```bash
php artisan make:seeder MoroccoChartOfAccountSeeder
```

### Fichier : `database/seeders/MoroccoChartOfAccountSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MoroccoChartOfAccountSeeder extends Seeder
{
    /**
     * Seed the Morocco CGNC chart of accounts.
     */
    public function run(Company $company = null): void
    {
        if (!$company) {
            throw new \Exception('Company parameter is required');
        }

        $this->command->info("📚 Chargement du CGNC Marocain pour {$company->name}...");

        $accounts = [
            // CLASSE 1 : COMPTES DE FINANCEMENT PERMANENT
            ['1111', 'Capital social ou personnel', 1, 'equity', 100000],
            ['1112', 'Actionnaires, capital souscrit, non appelé', 1, 'equity', 100000],
            ['1117', 'Capital souscrit, appelé, versé, non amorti', 1, 'equity', 100000],
            ['1140', 'Réserves légales', 1, 'equity', 110000],
            ['1151', 'Réserves statutaires ou contractuelles', 1, 'equity', 110000],
            ['1152', 'Réserves facultatives', 1, 'equity', 110000],
            ['1161', 'Report à nouveau (solde créditeur)', 1, 'equity', 110000],
            ['1169', 'Report à nouveau (solde débiteur)', 1, 'equity', 110000],
            ['1181', 'Résultat net en instance d\'affectation (solde créditeur)', 1, 'equity', 110000],
            ['1189', 'Résultat net en instance d\'affectation (solde débiteur)', 1, 'equity', 110000],
            ['1481', 'Emprunts auprès des établissements de crédit', 1, 'liability', 160000],
            ['1485', 'Emprunts auprès des sociétés de crédit', 1, 'liability', 160000],

            // CLASSE 2 : COMPTES D\'ACTIF IMMOBILISÉ
            ['2111', 'Frais de constitution', 2, 'asset', 200000],
            ['2230', 'Constructions', 2, 'asset', 210000],
            ['2321', 'Bâtiments', 2, 'asset', 210000],
            ['2331', 'Terrains nus', 2, 'asset', 210000],
            ['2340', 'Matériel de transport', 2, 'asset', 218000],
            ['2351', 'Mobilier de bureau', 2, 'asset', 218300],
            ['2352', 'Matériel de bureau', 2, 'asset', 218300],
            ['2355', 'Matériel informatique', 2, 'asset', 218300],
            ['2356', 'Agencements, installations et aménagements', 2, 'asset', 218300],

            // CLASSE 3 : COMPTES D\'ACTIF CIRCULANT (hors trésorerie)
            ['3111', 'Marchandises', 3, 'asset', 310000],
            ['3121', 'Matières premières', 3, 'asset', 312000],
            ['3122', 'Matières et fournitures consommables', 3, 'asset', 312000],
            ['3421', 'Clients', 3, 'asset', 342100],
            ['3424', 'Clients douteux ou litigieux', 3, 'asset', 342400],
            ['3425', 'Clients - effets à recevoir', 3, 'asset', 342500],
            ['3427', 'Clients - factures à établir', 3, 'asset', 342700],
            ['3428', 'Clients - produits à recevoir', 3, 'asset', 342800],

            // CLASSE 4 : COMPTES DE PASSIF CIRCULANT (hors trésorerie)
            ['4411', 'Fournisseurs', 4, 'liability', 441100],
            ['4415', 'Fournisseurs - effets à payer', 4, 'liability', 441500],
            ['4417', 'Fournisseurs - factures non parvenues', 4, 'liability', 441700],
            ['4432', 'Rémunérations dues au personnel', 4, 'liability', 443200],
            ['4441', 'CNSS', 4, 'liability', 444100],
            ['4443', 'Caisses de retraite', 4, 'liability', 444300],
            ['4445', 'Assurances accidents de travail', 4, 'liability', 444500],
            ['4447', 'Mutuelles', 4, 'liability', 444700],
            ['4452', 'État - TVA facturée', 4, 'liability', 445200],
            ['4455', 'État - TVA récupérable sur immobilisations', 4, 'asset', 445500],
            ['4456', 'État - TVA récupérable sur charges', 4, 'asset', 445600],
            ['4457', 'État - impôts et taxes assimilés', 4, 'liability', 445700],
            ['4458', 'État - organismes sociaux', 4, 'liability', 445800],
            ['4465', 'État - impôts sur les bénéfices', 4, 'liability', 446500],

            // CLASSE 5 : COMPTES DE TRÉSORERIE
            ['5141', 'Banques (soldes débiteurs)', 5, 'asset', 514100],
            ['5161', 'Caisses', 5, 'asset', 516100],

            // CLASSE 6 : COMPTES DE CHARGES
            ['6111', 'Achats de marchandises', 6, 'expense', 611100],
            ['6114', 'Variation de stock de marchandises', 6, 'expense', 611400],
            ['6121', 'Achats de matières premières', 6, 'expense', 612100],
            ['6124', 'Variation de stock de matières premières', 6, 'expense', 612400],
            ['6125', 'Achats non stockés de matières et fournitures', 6, 'expense', 612500],
            ['6126', 'Achats de travaux, études et prestations de service', 6, 'expense', 612600],
            ['6131', 'Locations et charges locatives', 6, 'expense', 613100],
            ['6132', 'Redevances de crédit-bail', 6, 'expense', 613200],
            ['6133', 'Entretien et réparations', 6, 'expense', 613300],
            ['6134', 'Primes d\'assurances', 6, 'expense', 613400],
            ['6136', 'Rémunérations d\'intermédiaires et honoraires', 6, 'expense', 613600],
            ['6141', 'Rémunérations du personnel', 6, 'expense', 614100],
            ['6144', 'Charges sociales', 6, 'expense', 614400],
            ['6145', 'Indemnités de congédiement', 6, 'expense', 614500],
            ['6146', 'Charges de personnel externe', 6, 'expense', 614600],
            ['6161', 'Impôts et taxes directs', 6, 'expense', 616100],
            ['6165', 'Impôts et taxes d\'État', 6, 'expense', 616500],
            ['6167', 'Impôts, taxes et droits assimilés', 6, 'expense', 616700],
            ['6311', 'Intérêts des emprunts et dettes', 6, 'expense', 631100],

            // CLASSE 7 : COMPTES DE PRODUITS
            ['7111', 'Ventes de marchandises', 7, 'revenue', 711100],
            ['7113', 'Rabais, remises et ristournes accordés', 7, 'revenue', 711300],
            ['7121', 'Ventes de biens produits', 7, 'revenue', 712100],
            ['7124', 'Variation de stocks de produits', 7, 'revenue', 712400],
            ['7125', 'Travaux', 7, 'revenue', 712500],
            ['7126', 'Études', 7, 'revenue', 712600],
            ['7127', 'Prestations de services', 7, 'revenue', 712700],
            ['7143', 'Rabais, remises et ristournes accordés (biens et services produits)', 7, 'revenue', 714300],
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
                'is_postable' => strlen($account[0]) >= 4,
            ]);
        }

        $count = count($accounts);
        $this->command->info("✅ {$count} comptes CGNC créés avec succès");
    }
}
```

---

## ✅ Étape 5 : Calculateur de paie marocain

### Créer le calculateur

```bash
php artisan make:class Services/Payroll/Calculators/MoroccoCalculator
```

### Fichier : `app/Services/Payroll/Calculators/MoroccoCalculator.php`

**Voir le fichier complet dans ARCHITECTURE_MULTI_PAYS.md**

Points clés :
- CNSS : 4.48% employé (plafonné à 6000 MAD)
- CNSS employeur : 17.93% (plafonné)
- Frais professionnels : 20% max 2500 MAD/mois
- IR progressif : 0% à 38%

---

## ✅ Étape 6 : Enregistrer le calculateur

### Fichier : `app/Services/Payroll/PayrollService.php`

```php
public function getCalculator(string $countryCode): PayrollCalculatorInterface
{
    return match($countryCode) {
        'BE' => new BelgiumCalculator(),
        'TN' => new TunisiaCalculator(),
        'MA' => new MoroccoCalculator(),  // ⭐ AJOUTER ICI
        'FR' => new FranceCalculator(),
        default => throw new \Exception("Calculateur non disponible pour: $countryCode"),
    };
}
```

---

## ✅ Étape 7 : Méthode helper Company

### Fichier : `app/Models/Company.php`

```php
public function isMorocco(): bool
{
    return $this->country_code === 'MA';
}

public function getIdentifiers(): array
{
    if ($this->isMorocco()) {
        return [
            'ICE' => $this->ice,
            'RC' => $this->rc,
            'Patente' => $this->patente,
            'IF' => $this->if,
            'CNSS' => $this->cnss_employer_number,
        ];
    }

    // Autres pays...
    return [];
}
```

---

## ✅ Étape 8 : Interface admin

### Fichier : `resources/views/admin/companies/edit.blade.php`

```html
<select name="country_code" x-model="countryCode">
    <option value="BE">🇧🇪 Belgique</option>
    <option value="TN">🇹🇳 Tunisie</option>
    <option value="MA">🇲🇦 Maroc</option>  <!-- ⭐ AJOUTER -->
    <option value="FR">🇫🇷 France</option>
</select>

<!-- Champs Maroc -->
<div x-show="countryCode === 'MA'" x-cloak>
    <label>ICE *</label>
    <input name="ice" placeholder="000000000000000" maxlength="15">

    <label>Registre de Commerce</label>
    <input name="rc">

    <label>Patente</label>
    <input name="patente">

    <label>CNSS Employeur</label>
    <input name="cnss_employer_number">

    <label>Identifiant Fiscal</label>
    <input name="if">
</div>
```

---

## ✅ Étape 9 : Seeder de démonstration

### Créer le seeder

```bash
php artisan make:seeder MoroccoDemoCompanySeeder
```

**Voir fichier complet dans ARCHITECTURE_MULTI_PAYS.md**

### Exécuter le seeder

```bash
php artisan db:seed --class=MoroccoDemoCompanySeeder
```

**Résultat attendu** :

```
🇲🇦 Création d'une entreprise marocaine de démonstration...
✅ Entreprise créée: Société Marocaine SARL (ID: xxx)
✅ Utilisateur créé: Ahmed Benali (admin@societe.ma)
📚 Chargement du CGNC Marocain...
✅ 65 comptes créés
```

---

## ✅ Étape 10 : Tests

### Test unitaire du calculateur

### Fichier : `tests/Unit/MoroccoCalculatorTest.php`

```php
<?php

namespace Tests\Unit;

use App\Services\Payroll\Calculators\MoroccoCalculator;
use App\Models\Employee;
use Carbon\Carbon;
use Tests\TestCase;

class MoroccoCalculatorTest extends TestCase
{
    public function test_calculates_cnss_correctly()
    {
        $calculator = new MoroccoCalculator();

        // Test 1 : Salaire sous plafond
        $result = $calculator->calculateSocialSecurity(5000);
        $this->assertEquals(224, $result['employee']); // 5000 * 4.48%
        $this->assertEquals(896.5, $result['employer']); // 5000 * 17.93%

        // Test 2 : Salaire au plafond
        $result = $calculator->calculateSocialSecurity(6000);
        $this->assertEquals(268.8, $result['employee']); // 6000 * 4.48%
        $this->assertEquals(1075.8, $result['employer']); // 6000 * 17.93%

        // Test 3 : Salaire au-dessus du plafond
        $result = $calculator->calculateSocialSecurity(10000);
        $this->assertEquals(268.8, $result['employee']); // Plafonné à 6000
        $this->assertEquals(1075.8, $result['employer']);
    }

    public function test_calculates_ir_correctly()
    {
        $calculator = new MoroccoCalculator();

        // Test 1 : Exonéré (< 30000/an = 2500/mois)
        $ir = $calculator->calculateIncomeTax(2000);
        $this->assertEquals(0, $ir);

        // Test 2 : Tranche 10%
        $ir = $calculator->calculateIncomeTax(4000); // 48000/an
        $this->assertEquals(150, $ir); // ((4000*12 - 30000) * 10%) / 12

        // Test 3 : Tranche 30%
        $ir = $calculator->calculateIncomeTax(7000); // 84000/an
        // Calcul : ((84000-60000)*30% + (60000-50000)*20% + (50000-30000)*10%) / 12
    }
}
```

### Exécuter les tests

```bash
php artisan test --filter=MoroccoCalculatorTest
```

---

## 📊 Vérification finale

### Checklist

- [ ] Configuration pays dans `countries.php`
- [ ] Migration exécutée avec succès
- [ ] Plan comptable CGNC chargé (65+ comptes)
- [ ] Calculateur de paie fonctionnel
- [ ] Interface admin avec champs Maroc
- [ ] Entreprise de démo créée
- [ ] Tests unitaires passent
- [ ] Documentation à jour

### Test manuel

1. **Créer entreprise marocaine**
   ```bash
   php artisan db:seed --class=MoroccoDemoCompanySeeder
   ```

2. **Se connecter**
   - Email : `admin@societe.ma`
   - Mot de passe : `password`

3. **Vérifier**
   - Dashboard affiche "MAD"
   - Menu "Cotisations CNSS"
   - Plan comptable CGNC visible
   - Créer facture : MAD dans dropdown devises

4. **Tester paie**
   ```bash
   php artisan tinker
   >>> $employee = Employee::where('email', 'employe@societe.ma')->first();
   >>> $payroll = app(PayrollService::class);
   >>> $result = $payroll->generatePayslip($employee, now());
   >>> print_r($result);
   ```

   **Résultat attendu** :
   ```
   [
       'gross_salary' => 5000.00,
       'employee_social_security' => 224.00,
       'employer_social_security' => 896.50,
       'income_tax' => 0.00,
       'net_salary' => 4776.00,
   ]
   ```

---

## 🎉 Félicitations !

Vous avez ajouté le Maroc à ComptaBE !

### Prochaines étapes

1. Ajouter validations spécifiques (format ICE, etc.)
2. Implémenter exports comptables CGNC
3. Ajouter déclarations fiscales marocaines
4. Créer templates de documents en arabe
5. Intégrer API CNSS Maroc si disponible

---

**Temps estimé** : 4-6 heures pour un développeur expérimenté
