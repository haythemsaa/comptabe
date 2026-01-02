# GUIDE DE CONFIGURATION - ComptaBE
## Superadmin & Multi-Pays (Belgique/Tunisie)

**Date**: 2025-12-31
**Version**: 1.0

---

## TABLE DES MATIÈRES

1. [Créer un Superadmin Expert-Comptable](#1-créer-un-superadmin-expert-comptable)
2. [Configurer le Pays d'une Company](#2-configurer-le-pays-dune-company)
3. [Différences Belgique vs Tunisie](#3-différences-belgique-vs-tunisie)
4. [Exemples Pratiques](#4-exemples-pratiques)
5. [FAQ](#5-faq)

---

## 1. CRÉER UN SUPERADMIN EXPERT-COMPTABLE

### Command: `user:make-superadmin`

**Syntaxe**:
```bash
php artisan user:make-superadmin {email} [--accountant] [--remove]
```

### Options

| Option | Description |
|--------|-------------|
| `email` | Email de l'utilisateur (obligatoire) |
| `--accountant` | Définir comme Expert-Comptable avec infos professionnelles |
| `--remove` | Retirer les droits superadmin |

---

### Exemple 1: Créer un Superadmin Simple

```bash
php artisan user:make-superadmin john.doe@example.com
```

**Résultat**:
```
👤 Utilisateur trouvé:
   Nom: John Doe
   Email: john.doe@example.com
   Type actuel: individual
   Superadmin actuel: ❌ NON

Confirmer la création de superadmin pour john.doe@example.com ? (yes/no) [no]:
> yes

✅ Superadmin créé avec succès!

📋 Récapitulatif:
   Email: john.doe@example.com
   Superadmin: ✅ OUI
   Type: individual

⚠️  ATTENTION: Les superadmins ont un accès TOTAL à toutes les companies!
   Ils peuvent contourner le TenantScope et voir toutes les données.
```

---

### Exemple 2: Créer un Expert-Comptable Superadmin

```bash
php artisan user:make-superadmin comptable@fiduciaire.be --accountant
```

**Questions interactives**:

1. **Titre professionnel** (choix multiple):
   ```
   Titre professionnel [expert_comptable]:
     [expert_comptable] Expert-Comptable
     [conseil_fiscal  ] Conseil Fiscal
     [reviseur        ] Réviseur d'Entreprises
     [comptable_agree ] Comptable Agréé
   ```

2. **Numéro ITAA** (Institut des Experts-Comptables Belgique):
   ```
   Ajouter le numéro ITAA (Institut des Experts-Comptables Belgique) ? (yes/no) [no]:
   > yes

   Numéro ITAA (ex: 12345):
   > 12345
   ```

3. **Numéro IRE** (Institut des Réviseurs d'Entreprises):
   ```
   Ajouter le numéro IRE (Institut des Réviseurs d'Entreprises) ? (yes/no) [no]:
   > yes

   Numéro IRE (ex: B-12345):
   > B-12345
   ```

**Résultat final**:
```
✅ Superadmin créé avec succès!

📋 Récapitulatif:
   Email: comptable@fiduciaire.be
   Superadmin: ✅ OUI
   Type: accountant
   Titre: Expert-Comptable
   ITAA: 12345
   IRE: B-12345

⚠️  ATTENTION: Les superadmins ont un accès TOTAL à toutes les companies!
   Ils peuvent contourner le TenantScope et voir toutes les données.
```

---

### Exemple 3: Retirer les Droits Superadmin

```bash
php artisan user:make-superadmin john.doe@example.com --remove
```

**Résultat**:
```
👤 Utilisateur trouvé:
   Nom: John Doe
   Email: john.doe@example.com
   Type actuel: individual
   Superadmin actuel: ✅ OUI

Êtes-vous sûr de vouloir retirer les droits superadmin à john.doe@example.com ? (yes/no) [no]:
> yes

✅ Droits superadmin retirés avec succès.
```

---

## 2. CONFIGURER LE PAYS D'UNE COMPANY

### Command: `company:set-country`

**Syntaxe**:
```bash
php artisan company:set-country [company] [country] [--list]
```

### Options

| Option | Description |
|--------|-------------|
| `company` | ID (UUID) ou nom de la company |
| `country` | Code pays: `BE` (Belgique) ou `TN` (Tunisie) |
| `--list` | Lister toutes les companies disponibles |

---

### Exemple 1: Lister Toutes les Companies

```bash
php artisan company:set-country --list
```

**Résultat**:
```
📋 Liste des Companies (3):

┌──────────────────────────────────┬───────────────────┬─────────┬──────────────┬────────────┐
│ ID                               │ Nom               │ Pays    │ TVA/Matricule│ Type       │
├──────────────────────────────────┼───────────────────┼─────────┼──────────────┼────────────┤
│ 9d8f5a3b-1c2e-4d7f-a8e9-1b2c3d4e │ Acme Belgium SPRL │ 🇧🇪 BE   │ BE0123456789 │ standalone │
│ 9d8f5a3b-2c3d-5e8f-b9f0-2c3d4e5f │ Fiduciaire Pro SA │ 🇧🇪 BE   │ BE0987654321 │ firm       │
│ 9d8f5a3b-3d4e-6f9g-c0g1-3d4e5f6g │ ComptaTN SARL     │ 🇹🇳 TN   │ 1234567A/M/0 │ standalone │
└──────────────────────────────────┴───────────────────┴─────────┴──────────────┴────────────┘

💡 Usage: php artisan company:set-country {id-ou-nom} {BE|TN}
```

---

### Exemple 2: Mode Interactif (Sans Arguments)

```bash
php artisan company:set-country
```

**Processus**:

1. **Sélection company**:
   ```
   🌍 Configuration Pays Company - Mode Interactif

   Sélectionnez une company:
     [9d8f5a3b-1c2e-4d7f-a8e9-1b2c3d4e] 🇧🇪 Acme Belgium SPRL (BE)
     [9d8f5a3b-2c3d-5e8f-b9f0-2c3d4e5f] 🇧🇪 Fiduciaire Pro SA (BE)
     [9d8f5a3b-3d4e-6f9g-c0g1-3d4e5f6g] 🇹🇳 ComptaTN SARL (TN)
   ```

2. **Sélection pays**:
   ```
   Quel pays pour 'Acme Belgium SPRL' ?:
     [BE] 🇧🇪 Belgique
     [TN] 🇹🇳 Tunisie
   ```

3. **Confirmation**:
   ```
   📋 Configuration pays pour: Acme Belgium SPRL
      Pays actuel: 🇧🇪 BE
      Nouveau pays: 🇹🇳 Tunisie

   📝 Modifications à apporter:
      ✅ country_code: 'BE' → 'TN'
      ✅ Plan comptable: Système Comptable des Entreprises (SCE)
      ✅ TVA: 19%, 13%, 7%, 0%
      ✅ Sécurité sociale: CNSS (Caisse Nationale de Sécurité Sociale)
      ℹ️  Champs Tunisie disponibles:
         - matricule_fiscal (Matricule Fiscal)
         - cnss_employer_number (Numéro Employeur CNSS)

   Confirmer le changement de pays pour 'Acme Belgium SPRL' ? (yes/no) [no]:
   > yes

   Ajouter le Matricule Fiscal maintenant ? (yes/no) [no]:
   > yes

   Matricule Fiscal (ex: 1234567A/M/000):
   > 1234567A/M/000

   Ajouter le Numéro Employeur CNSS maintenant ? (yes/no) [no]:
   > yes

   Numéro Employeur CNSS:
   > 12345678

   ✅ Pays configuré avec succès!

   📊 Informations Company:
      Nom: Acme Belgium SPRL
      Pays: 🇹🇳 Tunisie (TN)
      Matricule Fiscal: 1234567A/M/000
      CNSS Employeur: 12345678

   📚 Informations Comptables:
      Plan comptable: Système Comptable des Entreprises (SCE)
      Taux TVA: 19%, 13%, 7%, 0%
      Sécurité sociale: CNSS (Caisse Nationale de Sécurité Sociale)
   ```

---

### Exemple 3: Configuration Directe avec Arguments

```bash
php artisan company:set-country "Acme Belgium" TN
```

**Résultat identique** au mode interactif, mais plus rapide si vous connaissez l'ID/nom et le pays.

---

### Exemple 4: Utilisation par ID (UUID)

```bash
php artisan company:set-country 9d8f5a3b-1c2e-4d7f-a8e9-1b2c3d4e5f6g BE
```

Plus précis que le nom (évite les homonymes).

---

## 3. DIFFÉRENCES BELGIQUE VS TUNISIE

### Tableau Comparatif

| Aspect | 🇧🇪 Belgique (BE) | 🇹🇳 Tunisie (TN) |
|--------|------------------|-----------------|
| **Code pays** | `BE` | `TN` |
| **Identifiant fiscal** | Numéro de TVA (10 chiffres) | Matricule Fiscal (7 chiffres + lettres) |
| **Format TVA** | BE 0123.456.789 | TN 1234567A/M/000 |
| **N° Entreprise** | Numéro d'entreprise KBO (10 chiffres) | - |
| **Sécurité sociale** | ONSS 13.07% | CNSS (Caisse Nationale) |
| **Plan comptable** | PCMN (Plan Comptable Minimum Normalisé) | SCE (Système Comptable des Entreprises) |
| **Taux TVA** | 21%, 12%, 6%, 0% | 19%, 13%, 7%, 0% |
| **Champs DB** | `vat_number`, `enterprise_number` | `matricule_fiscal`, `cnss_employer_number` |

---

### Champs Database par Pays

#### Belgique (BE)

**Table `companies`**:
```php
'vat_number' => 'BE0123456789'
'enterprise_number' => '0123456789'
'country_code' => 'BE'
```

**Validations**:
- VAT: 10 chiffres précédés de "BE"
- Enterprise number: exactement 10 chiffres

---

#### Tunisie (TN)

**Table `companies`**:
```php
'matricule_fiscal' => '1234567A/M/000'
'cnss_employer_number' => '12345678'
'country_code' => 'TN'
```

**Validations**:
- Matricule: 7 chiffres + lettre catégorie (A/B/C) + /M/ ou /N/ + 3 chiffres
- CNSS: numéro employeur variable

---

## 4. EXEMPLES PRATIQUES

### Scénario 1: Cabinet d'Expertise-Comptable Belge

**Étape 1**: Créer le superadmin expert-comptable
```bash
php artisan user:make-superadmin comptable@fiduciaire.be --accountant
```

Remplir:
- Titre: `expert_comptable`
- ITAA: `12345`
- IRE: `B-12345` (si réviseur)

**Étape 2**: Vérifier les companies du cabinet
```bash
php artisan company:set-country --list
```

**Étape 3**: S'assurer que toutes sont en Belgique
```bash
php artisan company:set-country "Cabinet Compta Pro" BE
```

---

### Scénario 2: Entreprise Tunisienne

**Étape 1**: Lister les companies
```bash
php artisan company:set-country --list
```

**Étape 2**: Configurer pour la Tunisie
```bash
php artisan company:set-country "Société Tunisienne" TN
```

**Étape 3**: Renseigner les infos tunisiennes
- Matricule Fiscal: `1234567A/M/000`
- CNSS Employeur: `12345678`

---

### Scénario 3: Migration Belgique → Tunisie

**Commande**:
```bash
php artisan company:set-country 9d8f5a3b-... TN
```

**Conséquences**:
- ✅ `country_code` devient `TN`
- ✅ Champs tunisiens activés (`matricule_fiscal`, `cnss_employer_number`)
- ⚠️  Anciens champs belges (`vat_number`, `enterprise_number`) conservés mais non utilisés
- ⚠️  Plan comptable change: PCMN → SCE
- ⚠️  Taux TVA changent: 21% → 19%

**Important**: Vérifier la comptabilité après migration!

---

## 5. FAQ

### Q1: Un superadmin peut-il voir toutes les companies?

**Oui.** Le champ `is_superadmin` permet de contourner le `TenantScope`:

```php
// app/Models/Scopes/TenantScope.php (ligne 37)
if ($user->is_superadmin ?? false) {
    return; // Bypass scope
}
```

**Utilisation**: Administration, support client, audit.

---

### Q2: Peut-on avoir des companies belges ET tunisiennes?

**Oui!** Chaque company a son propre `country_code`:

```
Company 1: BE (Belgique)
Company 2: TN (Tunisie)
Company 3: BE (Belgique)
```

Le système s'adapte automatiquement aux champs requis par pays.

---

### Q3: Comment savoir si un utilisateur est superadmin?

**Via Tinker**:
```bash
php artisan tinker
>>> User::where('email', 'test@example.com')->first()->is_superadmin
=> true
```

**Via Database**:
```sql
SELECT first_name, last_name, email, is_superadmin, user_type
FROM users
WHERE is_superadmin = 1;
```

---

### Q4: Quels numéros professionnels pour un expert-comptable?

**Belgique**:
- **ITAA** (Institut des Experts-Comptables): Obligatoire pour exercer
- **IRE** (Institut des Réviseurs): Si réviseur d'entreprises (CAC)

**Tunisie**:
- **Ordre des Experts-Comptables de Tunisie** (OECT): Matricule professionnel

---

### Q5: Peut-on retirer le statut superadmin?

**Oui**, avec l'option `--remove`:

```bash
php artisan user:make-superadmin john@example.com --remove
```

⚠️  **Attention**: Si l'utilisateur perd superadmin, il ne voit plus que ses companies via `TenantScope`.

---

### Q6: Comment changer le pays sans perdre les données?

**Les données sont conservées!**

Lors du changement BE → TN:
- ✅ Factures, clients, écritures → **conservés**
- ✅ Anciens champs (`vat_number`) → **conservés mais non utilisés**
- ✅ Nouveaux champs (`matricule_fiscal`) → **disponibles**

**Recommandation**: Faire un backup avant migration critique.

---

### Q7: Comment vérifier le pays actuel d'une company?

**Via Tinker**:
```bash
php artisan tinker
>>> Company::find('9d8f5a3b-...')->country_code
=> "BE"
```

**Via Command**:
```bash
php artisan company:set-country --list
```

---

### Q8: Les taux TVA changent automatiquement?

**Non!** Les taux TVA configurés dans l'application ne changent PAS automatiquement.

**Action requise**:
1. Aller dans `Settings > TVA Codes`
2. Mettre à jour les taux manuellement:
   - BE: 21%, 12%, 6%, 0%
   - TN: 19%, 13%, 7%, 0%

---

## FICHIERS CONCERNÉS

### Commands
- `app/Console/Commands/MakeSuperadmin.php` (140 lignes)
- `app/Console/Commands/SetCompanyCountry.php` (285 lignes)

### Models
- `app/Models/User.php` - Champs: `is_superadmin`, `user_type`, `professional_title`, `itaa_number`, `ire_number`
- `app/Models/Company.php` - Champs: `country_code`, `matricule_fiscal`, `cnss_employer_number`

### Migrations
- `database/migrations/2025_12_30_090613_add_country_support_to_companies.php`

### Scopes
- `app/Models/Scopes/TenantScope.php` - Bypass pour superadmins (ligne 37)

---

## SÉCURITÉ

### ⚠️  Avertissements Superadmin

1. **Accès total**: Les superadmins contournent le multi-tenant
2. **Données sensibles**: Peuvent voir IBAN, BIC, salaires, etc. de toutes les companies
3. **Audit**: Toutes les actions sont loggées dans `audit_logs`
4. **Best practice**: Limiter le nombre de superadmins (max 2-3)

### ✅ Bonnes Pratiques

- ✅ Utiliser superadmin uniquement pour administration/support
- ✅ Créer des comptes normaux (non-superadmin) pour utilisation quotidienne
- ✅ Documenter qui a les droits superadmin
- ✅ Révoquer l'accès dès que non nécessaire

---

## SUPPORT

**Questions?** Consultez la documentation ou contactez:
- Support: support@comptabe.be
- Documentation: `/docs`

---

**Dernière mise à jour**: 2025-12-31
**Version**: 1.0
**Auteur**: Claude Code - Autonomous Implementation
