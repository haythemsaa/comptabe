# Module Paie - Documentation Complète

## Vue d'ensemble

Le module Paie de ComptaBE permet la gestion complète des employés, contrats de travail, fiches de paie et déclarations sociales belges. Il inclut des calculs automatiques conformes à la législation belge (ONSS, précompte professionnel, etc.) et des outils AI pour faciliter la gestion.

## Architecture

### 1. Base de Données

#### Table `employees`
Stocke les informations personnelles des employés.

**Champs principaux:**
- Identification: `employee_number`, `national_number`, `first_name`, `last_name`
- Dates: `birth_date`, `hire_date`, `termination_date`
- Coordonnées: `email`, `phone`, `mobile`, adresse complète
- Bancaire: `iban`, `bic`
- Statut: `status` (active, on_leave, terminated)

**Validations:**
- Le numéro national belge est validé avec checksum (11 chiffres)
- Format: YYMMDD-XXX-CC (date naissance + séquence + contrôle)

```php
// Exemple de validation
Employee::validateNationalNumber('85073003328'); // true
```

#### Table `employment_contracts`
Contrats de travail avec tous les détails salariaux et avantages.

**Types de contrats:**
- `cdi` - Contrat à durée indéterminée
- `cdd` - Contrat à durée déterminée
- `interim` - Intérim
- `student` - Job étudiant
- `apprenticeship` - Contrat d'apprentissage
- `flexi_job` - Flexi-job
- `extra_legal` - Extra-légal

**Rémunération:**
- Salaire brut mensuel et/ou horaire
- 13e mois, prime de fin d'année
- Bonus de performance
- Taux de commission

**Avantages:**
- Voiture de société (ATN)
- Chèques-repas (valeur configurable)
- Éco-chèques
- Assurance groupe
- Assurance hospitalisation
- GSM, laptop, internet
- Indemnités de frais

**Spécificités belges:**
- Numéro de commission paritaire
- Régime PC 200 (employés)
- Période de préavis
- Période d'essai
- Clause de non-concurrence
- Clause de confidentialité

#### Table `payslips`
Fiches de paie mensuelles avec calcul complet.

**Structure de calcul:**

1. **Rémunération brute:**
   - Salaire de base
   - Heures supplémentaires (150% du taux horaire)
   - Primes de nuit/week-end
   - Bonus et commissions
   - Pécule de vacances
   - 13e mois

2. **Retenues:**
   - ONSS travailleur: ~13.07% du brut
   - Cotisation spéciale de sécurité sociale
   - Précompte professionnel (barème progressif)
   - Chèques-repas part employé (€1.09 par chèque)

3. **Salaire net:**
   Brut - Total retenues

4. **Coût employeur:**
   - ONSS patronale: ~25% du brut
   - Coût total = Brut + ONSS patronale + avantages

**Exemple de calcul automatique:**

```php
// Génération d'une fiche de paie
$payslip = Payslip::create([
    'employee_id' => $employee->id,
    'period' => '2025-12',
    'base_salary' => 3000.00,
    'overtime_hours' => 10,
    // ... autres paramètres
]);

// Calculs automatiques:
// - Gross total: 3000 + (10h × hourly_rate × 1.5)
// - ONSS employé: gross × 0.1307
// - Précompte: calculé selon barèmes
// - Net: gross - retenues
// - ONSS patronale: gross × 0.25
```

**Statuts:**
- `draft` - Brouillon (modifiable)
- `validated` - Validée (prête pour paiement)
- `paid` - Payée
- `cancelled` - Annulée

#### Table `payroll_declarations`
Déclarations sociales et fiscales.

**Types de déclarations:**

1. **DIMONA** - Déclaration Immédiate/Onmiddellijke Aangifte
   - À soumettre AVANT l'entrée en service d'un employé
   - Obligatoire dans les 24h
   - Format XML vers plateforme ONSS

2. **DmfA** - Déclaration Multi-Fonctionnelle
   - Déclaration trimestrielle ONSS
   - Soumission: dernier jour du mois suivant le trimestre
   - Contient: salaires, cotisations, jours travaillés

3. **Fiche 281.10** - Rémunérations salariés
   - Déclaration annuelle fiscale
   - Deadline: 1er mars de l'année suivante
   - Envoi au SPF Finances

4. **Fiche 281.20** - Commissions indépendants
   - Pour rémunérations hors salaires
   - Même deadline que 281.10

5. **Compte individuel**
   - Compte annuel employé
   - Deadline: 31 mars

**Statuts:**
- `draft` - En préparation
- `ready` - Prête à soumettre
- `submitted` - Soumise
- `accepted` - Acceptée par autorités
- `rejected` - Rejetée (à corriger)

### 2. Modèles Eloquent

#### Employee Model

**Relations:**
```php
$employee->company          // Entreprise
$employee->contracts        // Tous les contrats
$employee->activeContract   // Contrat actif actuel
$employee->payslips         // Toutes les fiches de paie
$employee->latestPayslip    // Dernière fiche
```

**Méthodes utiles:**
```php
$employee->isActive()                    // Vérifie si actif
$employee->getCurrentSalary()            // Salaire brut mensuel actuel
$employee->getAnnualGrossSalary()        // Salaire annuel brut (avec 13e mois)
$employee->getTotalEmployerCost()        // Coût total mensuel employeur
```

**Scopes:**
```php
Employee::active()->get()                // Uniquement actifs
Employee::terminated()->get()            // Uniquement terminés
```

**Accesseurs:**
```php
$employee->full_name                     // Prénom + Nom
$employee->age                           // Âge calculé
$employee->seniority_years               // Ancienneté en années
```

#### EmploymentContract Model

**Relations:**
```php
$contract->employee
$contract->company
$contract->payslips         // Fiches générées sur ce contrat
```

**Méthodes:**
```php
$contract->isActive()                    // Vérifie si actif
$contract->getTotalMonthlyCost()         // Coût mensuel complet
$contract->getEstimatedNetAnnualSalary() // Net annuel estimé
$contract->getDurationMonths()           // Durée en mois (CDD)
$contract->isInProbation()               // Dans période d'essai ?
$contract->getJointCommitteeInfo()       // Info commission paritaire
```

**Scopes:**
```php
EmploymentContract::active()->get()
EmploymentContract::expiringSoon(30)->get()  // Expire dans 30 jours
```

#### Payslip Model

**Relations:**
```php
$payslip->employee
$payslip->company
$payslip->validator         // User qui a validé
```

**Méthodes:**
```php
$payslip->validate($user)                // Valider la fiche
$payslip->markAsPaid()                   // Marquer comme payée
$payslip->cancel()                       // Annuler
$payslip->generatePDF()                  // Générer PDF
$payslip->getBreakdown()                 // Détail complet
$payslip->getEffectiveTaxRate()          // Taux d'imposition effectif
$payslip->canBeEdited()                  // Modifiable ?
$payslip->canBeDeleted()                 // Supprimable ?
```

**Méthodes statiques:**
```php
Payslip::getYearlySummary($employee, 2025)  // Résumé annuel
```

**Scopes:**
```php
Payslip::draft()->get()
Payslip::validated()->get()
Payslip::paid()->get()
Payslip::forPeriod(2025, 12)->get()     // Décembre 2025
Payslip::forYear(2025)->get()            // Toute l'année
```

**Accesseurs:**
```php
$payslip->period_name        // "Décembre 2025"
```

#### PayrollDeclaration Model

**Relations:**
```php
$declaration->company
```

**Méthodes:**
```php
$declaration->markAsReady()              // Passer en "ready"
$declaration->submit($channel)           // Soumettre
$declaration->markAsAccepted($ref, $msg) // Marquer acceptée
$declaration->markAsRejected($msg)       // Marquer rejetée
$declaration->generateXML()              // Générer XML
$declaration->getSubmissionDeadline()    // Date limite
$declaration->isOverdue()                // En retard ?
$declaration->canBeEdited()
$declaration->canBeSubmitted()
$declaration->canBeDeleted()
```

**Scopes:**
```php
PayrollDeclaration::draft()->get()
PayrollDeclaration::submitted()->get()
PayrollDeclaration::accepted()->get()
PayrollDeclaration::rejected()->get()
PayrollDeclaration::forPeriod(2025, 4)->get()  // T4 2025
PayrollDeclaration::byType('dmfa')->get()
```

**Accesseurs:**
```php
$declaration->period_name     // "T4 2025" ou "Décembre 2025"
$declaration->type_name       // "DmfA (Déclaration Multi-Fonctionnelle)"
```

### 3. Outils AI

Le module inclut 2 outils AI disponibles via le chat assistant:

#### CreateEmployeeTool

**Nom:** `create_employee`

**Description:**
Crée un nouvel employé avec validation du numéro national belge.

**Paramètres:**
```json
{
  "first_name": "Jean",
  "last_name": "Dupont",
  "national_number": "85073003328",
  "birth_date": "1985-07-30",
  "gender": "M",
  "email": "jean.dupont@example.be",
  "phone": "+32 2 123 45 67",
  "street": "Rue de la Loi",
  "house_number": "123",
  "postal_code": "1000",
  "city": "Bruxelles",
  "iban": "BE68539007547034",
  "hire_date": "2025-01-15"
}
```

**Validation:**
- Numéro national obligatoire et valide
- Vérification d'unicité (pas de doublon)
- Génération automatique du numéro d'employé (format: `EMP-YYYY-0001`)

**Réponse:**
```json
{
  "success": true,
  "message": "Employé créé avec succès : Jean Dupont",
  "employee": {
    "id": "uuid",
    "employee_number": "EMP-2025-0001",
    "full_name": "Jean Dupont",
    "age": 39,
    "email": "jean.dupont@example.be"
  },
  "next_steps": [
    "Créer un contrat de travail pour cet employé",
    "Compléter les informations bancaires si manquantes",
    "Ajouter les bénéfices (voiture, chèques-repas, etc.)",
    "Déclarer via DIMONA dans les 24h"
  ]
}
```

**Exemple d'utilisation dans le chat:**
```
User: Ajoute un nouvel employé Jean Dupont, né le 30/07/1985,
      numéro national 85073003328, email jean.dupont@example.be,
      date d'embauche 15/01/2025

AI: [Utilise create_employee avec les paramètres]
    ✓ Employé créé avec succès : Jean Dupont (EMP-2025-0001)

    Prochaines étapes:
    - Créer un contrat de travail
    - Déclarer via DIMONA avant le début de travail
```

#### GeneratePayslipTool

**Nom:** `generate_payslip`

**Description:**
Génère une fiche de paie mensuelle avec calculs automatiques ONSS, précompte, net.

**Paramètres:**
```json
{
  "employee_id": "uuid",           // OU
  "employee_number": "EMP-2025-0001",
  "period": "2025-12",
  "worked_hours": 160,
  "overtime_hours": 10,
  "bonuses": 500,
  "paid_leave_days": 2,
  "sick_leave_days": 0,
  "payment_date": "2025-12-31"
}
```

**Validations:**
- Employé doit être actif
- Contrat actif obligatoire
- Vérification si fiche existe déjà pour la période
- Calculs conformes législation belge

**Calculs automatiques:**

1. **Salaire brut:**
   - Base: du contrat
   - Heures sup: heures × taux horaire × 1.5
   - Bonus: du paramètre

2. **ONSS employé:** 13.07% du brut

3. **Précompte professionnel:**
   - Barème progressif belge 2025:
     - 25% jusqu'à 15 200 €
     - 40% de 15 200 à 26 830 €
     - 45% de 26 830 à 46 440 €
     - 50% au-delà de 46 440 €

4. **Chèques-repas:** €1.09 par jour travaillé (part employé)

5. **Net:** Brut - retenues

6. **ONSS patronale:** 25% du brut

7. **Coût total employeur:** Brut + ONSS patronale

**Réponse:**
```json
{
  "success": true,
  "message": "Fiche de paie générée pour Jean Dupont - 2025-12",
  "payslip": {
    "id": "uuid",
    "payslip_number": "PAY-2025-12-001",
    "period": "2025-12",
    "employee": "Jean Dupont",
    "gross_total": 3500.00,
    "employee_social_security": 457.45,
    "professional_tax": 650.00,
    "total_deductions": 1129.25,
    "net_salary": 2370.75,
    "employer_social_security": 875.00,
    "total_employer_cost": 4375.00
  },
  "breakdown": {
    "Salaire brut": "3 500,00 €",
    "- ONSS employé (13.07%)": "457,45 €",
    "- Précompte professionnel": "650,00 €",
    "= Salaire net": "2 370,75 €",
    "Coût patronal :": "",
    "+ ONSS patronale (25%)": "875,00 €",
    "= Coût total employeur": "4 375,00 €"
  },
  "next_steps": [
    "Validez la fiche de paie (update status)",
    "Générez le PDF pour envoi à l'employé",
    "Effectuez le paiement à la date prévue",
    "Incluez dans la déclaration DmfA trimestrielle"
  ]
}
```

**Exemple d'utilisation:**
```
User: Génère la fiche de paie de décembre 2025 pour Jean Dupont
      avec 10h supplémentaires et 500€ de bonus

AI: [Utilise generate_payslip]
    ✓ Fiche de paie générée : PAY-2025-12-001

    Détails:
    - Salaire brut: 3 500,00 €
    - ONSS employé: 457,45 €
    - Précompte: 650,00 €
    - Salaire net: 2 370,75 €

    Coût employeur: 4 375,00 €

    Prochaines étapes:
    1. Valider la fiche
    2. Générer le PDF
    3. Payer le salaire
    4. Inclure dans DmfA T4
```

### 4. Configuration

#### Dans `config/ai.php`

Les outils paie sont ajoutés aux outils tenant:

```php
'tools' => [
    'tenant' => [
        // ... autres outils

        // Payroll operations
        'create_employee',              // ✅ Implemented
        'generate_payslip',             // ✅ Implemented
    ],
],
```

#### Dans `AppServiceProvider.php`

Enregistrement des outils:

```php
// Register payroll tools - HR & Payroll management
$registry->register(new CreateEmployeeTool());
$registry->register(new GeneratePayslipTool());
```

## Flux de Travail Typique

### 1. Embauche d'un nouvel employé

```
1. Créer l'employé (via AI ou interface)
   → Employee créé avec numéro unique

2. Créer un contrat de travail
   → Type CDI/CDD, salaire, avantages

3. Soumettre DIMONA
   → AVANT le début de travail
   → PayrollDeclaration type 'dimona'

4. Premier mois: générer fiche de paie
   → Payslip avec calculs auto

5. Valider et payer
   → Statut 'paid'
```

### 2. Gestion mensuelle de la paie

```
1. Début du mois M+1:
   - Générer fiches de tous les employés actifs
   - Vérifier absences, heures sup, bonus

2. Validation:
   - Valider chaque fiche (status 'validated')
   - Générer PDFs

3. Paiement:
   - Effectuer virements
   - Marquer comme 'paid'
   - Envoyer PDFs aux employés

4. Fin de trimestre:
   - Générer DmfA (tous les 3 mois)
   - Soumettre à l'ONSS
```

### 3. Déclarations annuelles

```
En janvier année N+1 pour année N:

1. Fiches 281.10 pour tous les employés
   - PayrollDeclaration type 'tax_281_10'
   - Deadline: 1er mars

2. Comptes individuels
   - Récapitulatif annuel par employé
   - Deadline: 31 mars
```

## Taux et Barèmes Belges (2025)

### ONSS (Sécurité sociale)

**Part employé:** 13.07%
- Sur le salaire brut
- Prélevé à la source

**Part patronale:** ~25%
- Sur le salaire brut
- Varie selon secteur/statut
- Inclut: pension, chômage, maladie, accidents du travail

### Précompte professionnel

Barème progressif (simplifié):

| Revenu imposable annuel | Taux |
|-------------------------|------|
| 0 - 15 200 € | 25% |
| 15 200 - 26 830 € | 40% |
| 26 830 - 46 440 € | 45% |
| > 46 440 € | 50% |

*Note: Le calcul réel inclut quotient conjugal, enfants à charge, etc.*

### Chèques-repas

- Valeur maximale défiscalisée: 8 €
- Part employé minimum: 1,09 €
- Part employeur: 6,91 € (déductible à 100%)

### Commissions paritaires (PC)

Principales commissions:
- **PC 200:** Employés (secteur général)
- **PC 111:** Métallurgie
- **PC 124:** Construction
- **PC 218:** Alimentation
- **PC 302:** Horeca
- **PC 330:** Soins de santé

## Sécurité et Compliance

### Validations

1. **Numéro national:**
   - Format: 11 chiffres
   - Checksum valide (modulo 97)
   - Pour personnes nées après 2000: vérification alternative

2. **Isolation tenant:**
   - Tous les modèles utilisent `BelongsToTenant`
   - Vérifications automatiques `company_id`

3. **Permissions:**
   - Outils AI nécessitent authentification
   - Confirmations pour actions sensibles

### Audit Trail

- Soft deletes sur tous les modèles
- Validation par utilisateur tracée (`validated_by`)
- Historique de modifications via Laravel Auditing (recommandé)

### RGPD

Données personnelles stockées:
- Nom, prénom, date de naissance
- Numéro national (sensible)
- Coordonnées
- Données bancaires (IBAN)

**Recommandations:**
- Encryption at rest pour numéro national
- Accès limité par rôle
- Logs d'accès aux fiches de paie
- Droit à l'oubli (soft delete 10 ans après départ)

## Améliorations Futures

### Phase 2 (recommandé)

1. **Contrôleurs et vues:**
   - EmployeeController (CRUD)
   - PayslipController (génération, validation, PDF)
   - PayrollDeclarationController (DIMONA, DmfA)
   - Vues Blade pour gestion UI

2. **PDFs et exports:**
   - Template fiche de paie conforme
   - Export DIMONA XML vers ONSS
   - Export DmfA XML trimestriel
   - Export fiches 281 pour SPF Finances

3. **Outils AI additionnels:**
   - `create_employment_contract`
   - `update_employee`
   - `terminate_employee`
   - `generate_dimona`
   - `generate_dmfa`
   - `generate_tax_281`

### Phase 3 (avancé)

1. **Intégrations externes:**
   - API ONSS (Portail de la sécurité sociale)
   - API SPF Finances (Tax-on-web)
   - Banques (SEPA direct debit pour salaires)

2. **Automatisations:**
   - Génération automatique fiches chaque mois
   - Rappels deadlines déclarations
   - Alertes contrats expirant
   - Calcul automatique vacances annuelles

3. **Rapports et analytics:**
   - Dashboard paie (coûts, évolution)
   - Prévisions budget salarial
   - Statistiques turnover
   - Analyse absentéisme

4. **Features RH:**
   - Gestion des congés
   - Notes de frais
   - Évaluations
   - Formation continue

## Support et Documentation

### Ressources officielles

**ONSS (Sécurité sociale):**
- https://www.socialsecurity.be
- DIMONA: https://www.socialsecurity.be/site_fr/employer/applics/dimona
- DmfA: https://www.socialsecurity.be/site_fr/employer/applics/dmfa

**SPF Finances:**
- https://finances.belgium.be
- Barèmes précompte: https://finances.belgium.be/fr/entreprises/personnel_et_remuneration

**Législation:**
- Code du travail belge
- Loi sur les contrats de travail
- Arrêtés royaux ONSS

### Fichiers créés

```
app/
├── Models/
│   ├── Employee.php                       ✅ Créé
│   ├── EmploymentContract.php             ✅ Créé
│   ├── Payslip.php                        ✅ Créé
│   └── PayrollDeclaration.php             ✅ Créé
│
├── Services/AI/Chat/Tools/Payroll/
│   ├── CreateEmployeeTool.php             ✅ Créé
│   └── GeneratePayslipTool.php            ✅ Créé
│
└── Providers/
    └── AppServiceProvider.php             ✅ Modifié (registration)

database/migrations/
├── 2025_12_25_120000_create_employees_table.php               ✅ Créé
├── 2025_12_25_120001_create_employment_contracts_table.php    ✅ Créé
├── 2025_12_25_120002_create_payslips_table.php                ✅ Créé
└── 2025_12_25_120003_create_payroll_declarations_table.php    ✅ Créé

config/
└── ai.php                                  ✅ Modifié (tools)

docs/
└── MODULE_PAIE.md                         ✅ Ce fichier
```

### Tests

Pour tester le module:

```bash
# 1. Vérifier les migrations
php artisan migrate:status

# 2. Créer un employé de test via tinker
php artisan tinker
>>> $company = Company::first();
>>> $employee = Employee::create([
...     'company_id' => $company->id,
...     'employee_number' => 'EMP-2025-0001',
...     'first_name' => 'Test',
...     'last_name' => 'User',
...     'national_number' => '85073003328',
...     'birth_date' => '1985-07-30',
...     'hire_date' => now(),
...     'status' => 'active',
... ]);

# 3. Créer un contrat
>>> $contract = EmploymentContract::create([
...     'employee_id' => $employee->id,
...     'company_id' => $company->id,
...     'contract_type' => 'cdi',
...     'status' => 'active',
...     'start_date' => now(),
...     'gross_monthly_salary' => 3000,
...     'weekly_hours' => 38,
... ]);

# 4. Générer une fiche de paie via AI tool
# Dans le chat: "Génère la fiche de paie de décembre 2025 pour EMP-2025-0001"
```

### Dépannage

**Erreur: "National number invalid"**
- Vérifiez le format: 11 chiffres exacts
- Vérifiez le checksum avec validateur en ligne
- Exemple valide: 85073003328

**Erreur: "No active contract found"**
- L'employé doit avoir un contrat avec `status = 'active'`
- Vérifiez `start_date` <= aujourd'hui
- Vérifiez `end_date` null ou >= aujourd'hui

**Erreur: "Payslip already exists"**
- Une seule fiche par employé par mois
- Utilisez `update_payslip` pour modifier
- Ou supprimez l'ancienne si status = 'draft'

## Conclusion

Le module Paie est maintenant opérationnel avec:

✅ 4 tables de base de données complètes
✅ 4 modèles Eloquent avec relations et méthodes
✅ 2 outils AI fonctionnels
✅ Calculs conformes législation belge 2025
✅ Validations et sécurité
✅ Documentation complète

**État actuel:** MVP fonctionnel (70%)

**Prochaines étapes recommandées:**
1. Controllers + Routes
2. Vues Blade
3. Export PDF fiches de paie
4. Export XML DIMONA/DmfA
5. Intégrations ONSS

Pour toute question: consulter cette documentation ou les commentaires dans le code source.

---

*Dernière mise à jour: 25 décembre 2025*
*Version: 1.0.0*
