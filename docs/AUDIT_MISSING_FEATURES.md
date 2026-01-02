# ComptaBE Application - Audit des Fonctionnalités Manquantes et Incomplètes

**Date de l'audit:** 2025-12-20
**Version:** 1.0
**Auditeur:** Analyse automatisée du code

---

## Résumé Exécutif

Cette audit identifie les fonctionnalités manquantes, incomplètes ou partiellement implémentées dans l'application ComptaBE. L'application dispose d'une architecture solide avec de nombreuses fonctionnalités bien développées, mais certaines zones critiques nécessitent une attention particulière avant la mise en production.

**Statut Général:**
- ✅ Architecture multi-tenant fonctionnelle
- ✅ Gestion des factures (ventes/achats) complète
- ⚠️ Intégration Peppol partiellement implémentée
- ⚠️ E-Reporting (2028) implémenté mais non connecté à l'API réelle
- ⚠️ Open Banking (PSD2) implémenté mais non connecté aux API réelles
- ⚠️ Services IA fonctionnels mais sans API externes configurées
- ❌ Modèles manquants pour certaines fonctionnalités

---

## 1. Intégration Peppol (Envoi/Réception de Factures)

### 1.1 Fonctionnalités Implémentées ✅
- Génération UBL 2.1 XML conforme Peppol BIS Billing 3.0
- Parser UBL pour la réception de factures
- Validation des factures avant envoi Peppol
- Gestion des transmissions Peppol (modèle `PeppolTransmission`)
- Webhooks pour réception de factures
- Interface utilisateur complète
- Routes API définies

### 1.2 Fonctionnalités Incomplètes ⚠️

#### API Controller - Méthodes TODO
**Fichier:** `app/Http/Controllers/Api/PeppolApiController.php`

```php
// Ligne 20: TODO: Implement actual Peppol SMP lookup
public function lookup(string $participantId)

// Ligne 64: TODO: Implement actual Peppol sending
public function send(Invoice $invoice)
```

**Impact:** Critique
**Détails:**
- La méthode `lookup()` retourne actuellement un message statique indiquant que le lookup n'est pas implémenté
- La méthode `send()` met à jour le statut mais ne communique pas réellement avec l'Access Point Peppol
- Le service `PeppolService` simule l'envoi en mode test si aucune clé API n'est configurée

#### Service Peppol
**Fichier:** `app/Services/Peppol/PeppolService.php`

**Points d'attention:**
- URLs API placeholders: `https://api.peppol.be/v1` et `https://api.sandbox.peppol.be/v1`
- Simulation automatique en mode test sans clé API (lignes 73-95)
- Nécessite l'intégration avec un véritable Access Point Peppol (ex: Storecove, Ecosio, Pagero)

### 1.3 Actions Requises 🔧

1. **Intégration Access Point Peppol**
   - Choisir un fournisseur d'Access Point (Storecove, Ecosio, etc.)
   - Obtenir les credentials API
   - Implémenter les appels API réels dans `PeppolService`
   - Configurer les endpoints de production

2. **SMP Lookup**
   - Implémenter la recherche SMP pour vérifier si un participant est enregistré
   - Utiliser l'API du fournisseur ou implémenter une recherche DNS SMP

3. **Testing**
   - Tests d'intégration avec l'environnement de test Peppol
   - Validation des documents UBL générés
   - Tests de bout en bout envoi/réception

---

## 2. E-Reporting (Mandat Belge 2028)

### 2.1 Fonctionnalités Implémentées ✅
- Modèle 5-corner complet (implémentation du 5ème corner - gouvernement)
- Service `EReportingService` fonctionnel
- Contrôleur `EReportingController` complet
- Modèle `EReportingSubmission` avec états
- Génération de payload conforme aux exigences belges
- Vérification automatique si e-Reporting est requis
- Soumission automatique lors de l'envoi Peppol
- Interface utilisateur pour gestion des soumissions
- Rapports de conformité

### 2.2 Fonctionnalités Incomplètes ⚠️

#### URLs API Placeholders
**Fichier:** `app/Services/Peppol/EReportingService.php`

```php
// Lignes 50-52
$this->apiBaseUrl = $this->testMode
    ? 'https://api.sandbox.ereporting.belgium.be/v1'
    : 'https://api.ereporting.belgium.be/v1';
```

**Détails:**
- Les URLs sont des placeholders (le système e-Reporting belge n'est pas encore finalisé pour 2028)
- Simulation automatique en mode test (lignes 93-94, 331-348)
- Le payload généré est basé sur les spécifications attendues mais non confirmées

### 2.3 Actions Requises 🔧

1. **Attendre les spécifications officielles**
   - Le système e-Reporting belge n'est pas encore opérationnel (mandat 2028)
   - Suivre les annonces du SPF Finances
   - Mettre à jour les endpoints API dès leur publication

2. **Validation du format de données**
   - Vérifier que le payload JSON correspond aux spécifications finales
   - Adapter la structure si nécessaire

3. **Certification**
   - Obtenir la certification du SPF Finances une fois le système opérationnel
   - Tester en environnement sandbox

---

## 3. Open Banking / PSD2 (Connexion Bancaire)

### 3.1 Fonctionnalités Implémentées ✅
- Service `PSD2Service` complet
- Support de 8 banques belges principales (KBC, BNP Paribas Fortis, ING, Belfius, Argenta, AXA, CBC, Crelan)
- Flux OAuth2 pour autorisation
- Synchronisation des comptes bancaires
- Synchronisation des transactions
- Gestion du refresh token
- Initiation de paiements (PISP)
- Health check des connexions
- Extraction de la communication structurée belge
- Interface utilisateur complète

### 3.2 Fonctionnalités Incomplètes ⚠️

#### Configuration des Banques
**Fichier:** `app/Services/OpenBanking/PSD2Service.php`

**Détails:**
- Les URLs API sont configurées pour chaque banque (lignes 34-99)
- Mais les `client_id` et `client_secret` doivent être obtenus pour chaque banque
- Chaque banque nécessite une inscription séparée au programme PSD2/Open Banking

**Configuration manquante dans `config/services.php`:**
```php
'openbanking' => [
    'client_id' => env('OPENBANKING_CLIENT_ID'),
    'client_secret' => env('OPENBANKING_CLIENT_SECRET'),
    'redirect_uri' => env('OPENBANKING_REDIRECT_URI'),
]
```

### 3.3 Actions Requises 🔧

1. **Inscription auprès des banques**
   - S'inscrire au programme Open Banking de chaque banque
   - Obtenir les credentials OAuth2 (client_id, client_secret)
   - Configurer les redirect URIs

2. **Agrégateur alternatif**
   - Considérer l'utilisation d'un agrégateur (ex: Budget Insight, Fintecture, Tink)
   - Avantages: une seule intégration pour toutes les banques
   - Coût: frais de service mensuel

3. **Testing**
   - Tester avec les environnements sandbox de chaque banque
   - Valider les flux de transactions
   - Gérer les cas d'erreur (consentement expiré, etc.)

---

## 4. Services IA (OCR, Catégorisation, Prévisions)

### 4.1 Fonctionnalités Implémentées ✅

#### DocumentOCRService
- Architecture complète pour OCR de documents
- Support de multiples fournisseurs: Google Vision, Azure, AWS Textract, Tesseract
- Extraction intelligente de données de factures belges
- Matching avec partenaires existants
- Création automatique de factures
- Scores de confiance
- Interface utilisateur

#### IntelligentCategorizationService
- Catégorisation basée sur patterns et apprentissage
- 17 catégories prédéfinies adaptées à la comptabilité belge
- Apprentissage à partir des corrections utilisateur
- Analyse des tendances de dépenses
- Détection d'anomalies
- Prédictions

#### TreasuryForecastService
- Prévisions de trésorerie sur 90+ jours
- Analyse des factures clients/fournisseurs
- Détection automatique de transactions récurrentes
- Calcul de probabilités de paiement
- Scénarios optimiste/réaliste/pessimiste
- Génération d'alertes
- Recommandations actionnables

### 4.2 Fonctionnalités Incomplètes ⚠️

#### Configuration OCR
**Fichier:** `app/Services/AI/DocumentOCRService.php`

**Points critiques:**
```php
// Ligne 25: Configuration OCR provider
'ocr_provider' => config('services.ocr.provider', 'google_vision')

// Ligne 131: Google Vision API Key requise
'key' => config('services.google.vision_api_key')

// Ligne 155: Tesseract doit être installé sur le serveur
exec("tesseract {$fullPath} {$outputFile}...")
```

**Configuration manquante:**
- Clé API Google Vision
- Configuration Azure Computer Vision
- Configuration AWS Textract
- Installation Tesseract (fallback)

#### Modèles Manquants

Les services référencent des modèles qui n'existent pas dans le code:
- `ExpenseCategory` (référencé dans `IntelligentCategorizationService.php` ligne 8)
- `RecurringTransaction` (référencé dans `TreasuryForecastService.php` ligne 8)
- `Expense` (référencé dans plusieurs fichiers)

### 4.3 Actions Requises 🔧

1. **Configuration des services OCR**
   - Choisir un fournisseur principal (recommandé: Google Vision pour qualité)
   - Obtenir les clés API
   - Configurer Tesseract comme fallback
   - Tester avec différents types de documents

2. **Créer les modèles manquants**
   ```bash
   php artisan make:model ExpenseCategory -m
   php artisan make:model RecurringTransaction -m
   php artisan make:model Expense -m
   ```

3. **Créer les migrations manquantes**
   - Table `expense_categories`: id, company_id, name, code, account_code, etc.
   - Table `recurring_transactions`: id, company_id, description, amount, frequency, etc.
   - Table `expenses`: id, company_id, description, amount, category_id, etc.

---

## 5. Multi-Tenant (Gestion d'Entreprises)

### 5.1 Fonctionnalités Implémentées ✅
- Architecture multi-tenant complète
- Trait `BelongsToTenant` pour isolation des données
- Scope `TenantScope` automatique
- Sélection d'entreprise
- Basculement entre entreprises
- Création d'entreprise
- Middleware `tenant`
- Sessions isolées par entreprise

### 5.2 Fonctionnalités Complètes ✅
Aucune fonctionnalité manquante identifiée dans cette zone.

### 5.3 Recommandations 📋

1. **Tests de sécurité**
   - Vérifier l'isolation complète des données entre tenants
   - Tests de tentative d'accès cross-tenant
   - Audit des requêtes sans scope

2. **Performance**
   - Index sur `company_id` dans toutes les tables
   - Cache par tenant
   - Monitoring des requêtes lentes

---

## 6. Autres Fonctionnalités Incomplètes

### 6.1 Recherche de Partenaires (KBO/VIES)

**Fichier:** `app/Http/Controllers/Api/PartnerApiController.php`

```php
// Ligne 73: TODO: Lookup in external service (VIES, KBO)
public function lookupByVat(Request $request)
{
    // ... validation du numéro de TVA
    // TODO: Lookup in external service (VIES, KBO)

    return response()->json([
        'success' => false,
        'message' => 'Service non disponible',
    ], 503);
}
```

**Impact:** Moyen
**Action requise:**
- Intégrer l'API VIES pour validation TVA UE
- Intégrer l'API KBO/BCE pour données entreprises belges

### 6.2 Invitation d'Utilisateurs

**Fichier:** `app/Http/Controllers/AccountingFirmController.php`

```php
// Ligne 440: TODO: Send invitation email
public function inviteTeamMember(Request $request)
{
    // ... création de l'invitation
    // TODO: Send invitation email

    return back()->with('success', 'Invitation créée');
}
```

**Impact:** Moyen
**Action requise:**
- Créer un Mailable pour les invitations
- Envoyer l'email lors de la création
- Template email professionnel

### 6.3 Modèles de Données Manquants

Les modèles suivants sont référencés mais n'existent pas:

1. **RecurringTransaction**
   - Utilisé dans: `TreasuryForecastService`
   - Usage: Prévisions de trésorerie
   - Champs suggérés: company_id, description, amount, frequency, start_date, next_occurrence_date, is_active

2. **ExpenseCategory**
   - Utilisé dans: `IntelligentCategorizationService`
   - Usage: Catégorisation des dépenses
   - Champs suggérés: company_id, name, code, account_code, parent_id

3. **Expense**
   - Utilisé dans: Plusieurs services IA
   - Usage: Gestion des dépenses
   - Champs suggérés: company_id, partner_id, description, amount, category, account_code, vat_code

### 6.4 Relations Manquantes dans les Modèles

**Invoice Model** - Relations potentiellement manquantes:
- `documentScan()` - relation avec `DocumentScan` (pour factures créées par OCR)
- `ereportingSubmission()` - relation avec `EReportingSubmission`
- `recurringInvoice()` - relation avec `RecurringInvoice` (pour factures générées automatiquement)

---

## 7. Configuration Requise

### 7.1 Variables d'Environnement Manquantes

Ajouter au fichier `.env`:

```env
# Peppol Integration
PEPPOL_ACCESS_POINT_URL=https://api.storecove.com/v1
PEPPOL_API_KEY=your_api_key
PEPPOL_TEST_MODE=true

# E-Reporting (À configurer une fois disponible)
EREPORTING_API_URL=
EREPORTING_API_KEY=
EREPORTING_TEST_MODE=true

# Open Banking / PSD2
OPENBANKING_CLIENT_ID=
OPENBANKING_CLIENT_SECRET=
OPENBANKING_REDIRECT_URI=${APP_URL}/openbanking/callback

# Google Vision OCR
GOOGLE_VISION_API_KEY=

# Alternative: Azure Computer Vision
AZURE_CV_ENDPOINT=
AZURE_CV_KEY=

# Alternative: AWS Textract
AWS_TEXTRACT_REGION=
AWS_TEXTRACT_KEY=
AWS_TEXTRACT_SECRET=

# OCR Provider (google_vision, azure, aws_textract, local)
OCR_PROVIDER=google_vision

# VIES/KBO Integration
VIES_API_URL=https://ec.europa.eu/taxation_customs/vies/services/checkVatService
KBO_API_URL=https://kbopub.economie.fgov.be/kbopub/api
KBO_API_KEY=
```

### 7.2 Fichier de Configuration Services

Créer/mettre à jour `config/services.php`:

```php
return [
    // ... configurations existantes

    'peppol' => [
        'access_point_url' => env('PEPPOL_ACCESS_POINT_URL'),
        'api_key' => env('PEPPOL_API_KEY'),
        'test_mode' => env('PEPPOL_TEST_MODE', true),
    ],

    'ereporting' => [
        'api_url' => env('EREPORTING_API_URL'),
        'api_key' => env('EREPORTING_API_KEY'),
        'test_mode' => env('EREPORTING_TEST_MODE', true),
    ],

    'openbanking' => [
        'client_id' => env('OPENBANKING_CLIENT_ID'),
        'client_secret' => env('OPENBANKING_CLIENT_SECRET'),
        'redirect_uri' => env('OPENBANKING_REDIRECT_URI'),
    ],

    'google' => [
        'vision_api_key' => env('GOOGLE_VISION_API_KEY'),
    ],

    'azure' => [
        'cv_endpoint' => env('AZURE_CV_ENDPOINT'),
        'cv_key' => env('AZURE_CV_KEY'),
    ],

    'aws' => [
        'textract_region' => env('AWS_TEXTRACT_REGION'),
        'textract_key' => env('AWS_TEXTRACT_KEY'),
        'textract_secret' => env('AWS_TEXTRACT_SECRET'),
    ],

    'ocr' => [
        'provider' => env('OCR_PROVIDER', 'google_vision'),
    ],

    'vies' => [
        'api_url' => env('VIES_API_URL', 'https://ec.europa.eu/taxation_customs/vies/services/checkVatService'),
    ],

    'kbo' => [
        'api_url' => env('KBO_API_URL', 'https://kbopub.economie.fgov.be/kbopub/api'),
        'api_key' => env('KBO_API_KEY'),
    ],
];
```

---

## 8. Priorisation des Actions

### 8.1 Critique (P0) - Avant Production
1. ✅ Créer les modèles manquants (Expense, ExpenseCategory, RecurringTransaction)
2. ✅ Créer les migrations correspondantes
3. ⚠️ Implémenter l'envoi d'emails d'invitation
4. ⚠️ Configurer au moins un service OCR fonctionnel

### 8.2 Important (P1) - Premier Trimestre
1. 🔧 Intégrer avec un Access Point Peppol réel
2. 🔧 Configurer au moins 2-3 banques pour Open Banking
3. 🔧 Implémenter la recherche KBO/VIES
4. 🔧 Tests de sécurité multi-tenant

### 8.3 Souhaitable (P2) - Deuxième Trimestre
1. 📋 Finaliser l'intégration E-Reporting (dès que le système gouvernemental est disponible)
2. 📋 Étendre le support Open Banking à toutes les banques
3. 📋 Améliorer les services IA avec du Machine Learning réel
4. 📋 Tests de charge et optimisation

---

## 9. Migrations à Créer

### 9.1 Table `expenses`

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
    $table->date('expense_date');
    $table->string('description');
    $table->text('notes')->nullable();
    $table->decimal('amount', 15, 2);
    $table->string('currency', 3)->default('EUR');
    $table->foreignUuid('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
    $table->string('category')->nullable(); // text category
    $table->string('account_code')->nullable();
    $table->string('vat_code')->nullable();
    $table->decimal('vat_amount', 15, 2)->default(0);
    $table->string('payment_method')->nullable();
    $table->string('payment_reference')->nullable();
    $table->string('receipt_path')->nullable();
    $table->string('status')->default('pending'); // pending, approved, rejected, paid
    $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('company_id');
    $table->index('expense_date');
    $table->index('status');
});
```

### 9.2 Table `expense_categories`

```php
Schema::create('expense_categories', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('parent_id')->nullable()->constrained('expense_categories')->nullOnDelete();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->string('account_code')->nullable();
    $table->string('default_vat_code')->nullable();
    $table->string('color')->nullable();
    $table->string('icon')->nullable();
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('company_id');
    $table->index('parent_id');
});
```

### 9.3 Table `recurring_transactions`

```php
Schema::create('recurring_transactions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // income, expense
    $table->string('description');
    $table->text('notes')->nullable();
    $table->decimal('amount', 15, 2);
    $table->string('currency', 3)->default('EUR');
    $table->string('frequency'); // daily, weekly, monthly, quarterly, yearly
    $table->integer('interval')->default(1); // every X frequency
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->date('next_occurrence_date');
    $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
    $table->string('category')->nullable();
    $table->string('account_code')->nullable();
    $table->string('vat_code')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('auto_create')->default(false);
    $table->integer('occurrences_count')->default(0);
    $table->timestamp('last_executed_at')->nullable();
    $table->timestamps();

    $table->index('company_id');
    $table->index('next_occurrence_date');
    $table->index('is_active');
});
```

---

## 10. Tests à Effectuer

### 10.1 Tests Fonctionnels
- [ ] Cycle complet facture vente (création → validation → envoi Peppol → paiement)
- [ ] Cycle complet facture achat (réception UBL → validation → paiement)
- [ ] E-Reporting submission (mode test)
- [ ] Connexion bancaire (au moins une banque)
- [ ] Synchronisation transactions bancaires
- [ ] OCR de facture → création automatique
- [ ] Catégorisation intelligente
- [ ] Prévisions de trésorerie
- [ ] Workflow d'approbation
- [ ] Multi-tenant: isolation des données

### 10.2 Tests de Sécurité
- [ ] Tentative d'accès cross-tenant
- [ ] Validation des permissions utilisateur
- [ ] Protection CSRF
- [ ] XSS sur champs texte
- [ ] SQL injection
- [ ] File upload sécurisé (OCR)

### 10.3 Tests de Performance
- [ ] 1000+ factures dans une entreprise
- [ ] 100+ utilisateurs simultanés
- [ ] Recherche factures (pagination, filtres)
- [ ] Export PDF en masse
- [ ] Génération UBL en masse

---

## 11. Documentation à Créer

### 11.1 Documentation Technique
- [ ] Guide d'installation
- [ ] Guide de configuration des API externes
- [ ] Architecture multi-tenant
- [ ] Diagrammes de flux (Peppol, E-Reporting, PSD2)
- [ ] API REST documentation (OpenAPI/Swagger)

### 11.2 Documentation Utilisateur
- [ ] Guide de démarrage rapide
- [ ] Configuration Peppol
- [ ] Connexion bancaire
- [ ] Utilisation de l'OCR
- [ ] Workflows d'approbation
- [ ] Gestion multi-entreprise

---

## 12. Conclusion

### 12.1 Points Forts ✅
- Architecture solide et bien structurée
- Code propre et maintenable
- Multi-tenant bien implémenté
- Services bien découplés
- UI/UX complète avec routes définies
- Bonne couverture fonctionnelle

### 12.2 Points d'Attention ⚠️
- Intégrations externes simulées (Peppol, PSD2, OCR)
- Modèles de données manquants (Expense, RecurringTransaction, ExpenseCategory)
- Configuration requise pour les services externes
- Tests nécessaires avant production

### 12.3 Recommandation

L'application ComptaBE est **structurellement prête** mais nécessite:
1. Création des modèles manquants (1-2 jours)
2. Configuration des services externes (3-5 jours)
3. Tests d'intégration (5-7 jours)
4. Documentation (2-3 jours)

**Estimation totale avant production: 2-3 semaines**

---

## Annexe A - Checklist de Mise en Production

### Configuration
- [ ] Créer `.env` production avec toutes les variables
- [ ] Configurer au moins un service OCR
- [ ] Obtenir credentials Peppol Access Point
- [ ] S'inscrire aux APIs bancaires (ou agrégateur)
- [ ] Configurer emails (SMTP)

### Base de Données
- [ ] Créer modèles manquants
- [ ] Exécuter migrations
- [ ] Seeders pour données de base
- [ ] Backup strategy

### Sécurité
- [ ] SSL/TLS configuré
- [ ] Firewall configuré
- [ ] Rate limiting API
- [ ] 2FA obligatoire pour admins
- [ ] Audit logs activés

### Performance
- [ ] Redis configuré (cache, sessions, queues)
- [ ] Queue workers démarrés
- [ ] Monitoring (Sentry, New Relic, etc.)
- [ ] CDN pour assets statiques

### Tests
- [ ] Tests fonctionnels passés
- [ ] Tests de sécurité passés
- [ ] Tests de charge passés
- [ ] Backup/restore testé

### Documentation
- [ ] Guide installation
- [ ] Guide utilisateur
- [ ] API documentation
- [ ] Procédures support

---

**Fin du rapport d'audit**
