# ComptaBE - Roadmap & Plan d'Action

**Date:** 2025-12-20
**Version:** 1.0
**Application:** ComptaBE - Logiciel de Comptabilité Belge SaaS

---

## Résumé Exécutif

Suite à l'audit complet de l'application ComptaBE, voici le plan d'action priorisé pour atteindre une version production-ready.

### Scores d'Audit

| Domaine | Score | Statut |
|---------|-------|--------|
| Tests & Routes | 100% | ✅ PASS |
| Architecture | 8/10 | ✅ GOOD |
| Design UI/UX | 7.2/10 | ⚠️ NEEDS WORK |
| Sécurité | MEDIUM-HIGH | ⚠️ NEEDS WORK |
| Fonctionnalités | 85% | ⚠️ INCOMPLETE |

### Estimation Totale
- **Bugs Critiques:** 2-3 jours
- **Sécurité Critique:** 3-5 jours
- **Fonctionnalités Manquantes:** 1-2 semaines
- **UI/UX Améliorations:** 2-3 semaines
- **Total avant Production:** 4-6 semaines

---

## Phase 1: Corrections Critiques (Semaine 1)

### 1.1 Bugs Critiques à Corriger Immédiatement

| # | Bug | Fichier | Impact |
|---|-----|---------|--------|
| 1 | Missing `Partner::customers()` scope | app/Models/Partner.php | 500 errors |
| 2 | Missing `Partner::suppliers()` scope | app/Models/Partner.php | 500 errors |
| 3 | Missing `VatCode::active()` scope | app/Models/VatCode.php | 500 errors |
| 4 | Missing `Product::active()` scope | app/Models/Product.php | 500 errors |
| 5 | Missing `Invoice::vat_amount` accessor | app/Models/Invoice.php | VAT calc errors |
| 6 | Null check `FiscalYear::current()` | AccountingController.php | 500 errors |
| 7 | Missing `payment_terms` in Partner | Quote.php:210 | 500 errors |

**Actions:**
```bash
# Ajouter les scopes manquants dans Partner.php
public function scopeCustomers($query) { return $query->where('is_customer', true); }
public function scopeSuppliers($query) { return $query->where('is_supplier', true); }

# Ajouter dans VatCode.php
public function scopeActive($query) { return $query->where('is_active', true); }

# Ajouter dans Product.php
public function scopeActive($query) { return $query->where('is_active', true); }
```

### 1.2 Sécurité Critique

| # | Issue | Priorité |
|---|-------|----------|
| 1 | Créer les Policies pour Invoice, Partner, etc. | CRITIQUE |
| 2 | Fixer SQL Injection dans CreditNote.php | CRITIQUE |
| 3 | Supprimer/Sécuriser PHPInfo | CRITIQUE |
| 4 | Ajouter RBAC aux routes | HAUTE |

**Actions:**
```bash
php artisan make:policy InvoicePolicy --model=Invoice
php artisan make:policy PartnerPolicy --model=Partner
php artisan make:policy BankAccountPolicy --model=BankAccount
```

### 1.3 Modèles Manquants

Créer les modèles et migrations:

```bash
php artisan make:model Expense -m
php artisan make:model ExpenseCategory -m
php artisan make:model RecurringTransaction -m
```

---

## Phase 2: Intégrations Externes (Semaines 2-3)

### 2.1 Peppol Access Point

**Statut actuel:** Simulé (mode test)
**Action:** Intégrer avec un vrai Access Point

**Options recommandées:**
1. **Storecove** - API simple, bonne documentation
2. **Ecosio** - Européen, support multilingue
3. **Pagero** - Entreprise, très complet

**Fichiers à modifier:**
- `app/Services/Peppol/PeppolService.php`
- `app/Http/Controllers/Api/PeppolApiController.php`

### 2.2 Open Banking (PSD2)

**Statut actuel:** Architecture complète, pas de credentials
**Action:** S'inscrire aux APIs bancaires

**Options:**
1. **Direct:** Inscription individuelle (KBC, BNP, ING, Belfius)
2. **Agrégateur:** Tink, Fintecture (une seule intégration)

### 2.3 OCR Service

**Statut actuel:** Code prêt, pas de clé API
**Action:** Configurer Google Vision ou Azure

```env
# .env
OCR_PROVIDER=google_vision
GOOGLE_VISION_API_KEY=your_key_here
```

---

## Phase 3: UI/UX Améliorations (Semaines 3-4)

### 3.1 Accessibilité (Priorité Haute)

- [ ] Ajouter "Skip to content" link
- [ ] Fixer contraste couleurs (secondary-500)
- [ ] Ajouter focus indicators
- [ ] Corriger heading hierarchy
- [ ] Ajouter aria-labels aux dropdowns
- [ ] Supporter `prefers-reduced-motion`

### 3.2 Performance UI

- [ ] Créer skeleton screens pour loading
- [ ] Déplacer queries sidebar vers ViewComposer
- [ ] Ajouter loading indicators
- [ ] Implémenter NProgress pour navigation

### 3.3 Responsive Design

- [ ] Fixer tables sur mobile (card view)
- [ ] Améliorer formulaires sur tablette
- [ ] Augmenter touch targets

### 3.4 Corrections Modal

- [ ] Fixer implémentation `<dialog>`
- [ ] Ajouter focus trap
- [ ] Supporter ESC key

---

## Phase 4: Fonctionnalités Complémentaires (Semaines 4-5)

### 4.1 Emails

- [ ] Implémenter envoi invitation (TODO dans AccountingFirmController)
- [ ] Templates emails professionnels
- [ ] Notifications factures en retard

### 4.2 Recherche KBO/VIES

- [ ] Intégrer API VIES (validation TVA EU)
- [ ] Intégrer API KBO (données entreprises BE)

### 4.3 Rate Limiting

```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

---

## Phase 5: Tests & Documentation (Semaine 6)

### 5.1 Tests Automatisés

```bash
php artisan make:test InvoiceControllerTest
php artisan make:test PartnerControllerTest
php artisan make:test AccountingControllerTest
```

### 5.2 Documentation

- [ ] Guide d'installation
- [ ] API documentation (OpenAPI/Swagger)
- [ ] Guide utilisateur
- [ ] Architecture technique

---

## Checklist Production

### Configuration
- [ ] Variables `.env` production configurées
- [ ] Clé API Peppol Access Point
- [ ] Credentials Open Banking
- [ ] Clé API OCR (Google Vision)
- [ ] SMTP configuré

### Base de Données
- [ ] Modèles manquants créés
- [ ] Migrations exécutées
- [ ] Backup strategy en place

### Sécurité
- [ ] SSL/TLS configuré
- [ ] Policies créées pour tous les modèles
- [ ] Rate limiting actif
- [ ] 2FA obligatoire pour admins
- [ ] PHPInfo supprimé/sécurisé

### Performance
- [ ] Redis configuré
- [ ] Queue workers démarrés
- [ ] CDN pour assets
- [ ] Monitoring (Sentry, etc.)

### Tests
- [ ] Tests unitaires passent
- [ ] Tests fonctionnels passent
- [ ] Tests sécurité effectués
- [ ] Tests charge effectués

---

## Rapports d'Audit Détaillés

Les rapports complets sont disponibles dans le dossier `docs/`:

1. **AUDIT_BUGS.md** - 23 bugs identifiés avec corrections
2. **AUDIT_DESIGN.md** - Audit UI/UX complet (score 7.2/10)
3. **AUDIT_MISSING_FEATURES.md** - Fonctionnalités incomplètes
4. **AUDIT_SECURITY.md** - Vulnérabilités et recommandations
5. **AUDIT_TESTS.md** - Tests routes et controllers (PASS)

---

## Contacts & Ressources

### APIs Externes
- **Storecove (Peppol):** https://www.storecove.com/
- **Tink (Open Banking):** https://tink.com/
- **Google Vision:** https://cloud.google.com/vision

### Documentation Laravel
- **Policies:** https://laravel.com/docs/authorization#creating-policies
- **Form Requests:** https://laravel.com/docs/validation#form-request-validation
- **Testing:** https://laravel.com/docs/testing

---

**Document généré automatiquement par l'audit ComptaBE**
**Dernière mise à jour:** 2025-12-20
