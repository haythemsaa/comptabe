# ComptaBE - Tâches à Faire

**Date de création:** 2025-12-20
**Application:** ComptaBE - Application de Comptabilité Belge
**Statut:** En développement

---

## Résumé Exécutif

Ce document liste toutes les tâches identifiées lors de l'audit complet de l'application ComptaBE. Les tâches sont organisées par priorité et catégorie.

### Statistiques

| Catégorie | Critique | Haute | Moyenne | Basse | Total |
|-----------|----------|-------|---------|-------|-------|
| Bugs | 12 | 7 | 4 | 0 | 23 |
| Vues Manquantes | 20 | 9 | 3 | 0 | 32 |
| Performance | 8 | 4 | 3 | 0 | 15 |
| Sécurité | 4 | 4 | 5 | 4 | 17 |
| Fonctionnalités | 4 | 4 | 2 | 0 | 10 |
| **TOTAL** | **48** | **28** | **17** | **4** | **97** |

---

## 🔴 PRIORITÉ CRITIQUE (Faire en premier)

### 1. Bugs Critiques à Corriger

#### 1.1 Scopes Manquants dans les Modèles
**Fichier:** `app/Models/Partner.php`
```php
// Ajouter ces scopes
public function scopeCustomers($query)
{
    return $query->where('is_customer', true);
}

public function scopeSuppliers($query)
{
    return $query->where('is_supplier', true);
}
```

**Fichier:** `app/Models/VatCode.php`
```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

**Fichier:** `app/Models/Product.php`
```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopeOrdered($query)
{
    return $query->orderBy('sort_order')->orderBy('name');
}
```

#### 1.2 Attributs Manquants
**Fichier:** `app/Models/Invoice.php`
```php
// Ajouter accesseur pour vat_amount
public function getVatAmountAttribute(): float
{
    return $this->total_vat ?? ($this->total_incl_vat - $this->total_excl_vat);
}
```

**Fichier:** `app/Models/Quote.php` - Ligne 210
```php
// Changer de:
'due_date' => now()->addDays($this->partner->payment_terms ?? 30),
// À:
'due_date' => now()->addDays($this->partner->payment_terms_days ?? 30),
```

#### 1.3 Vérifications Null Manquantes
**Fichier:** `app/Http/Controllers/AccountingController.php`
```php
$currentYear = FiscalYear::current()->first();

if (!$currentYear) {
    return redirect()->route('settings.fiscal-years.create')
        ->with('error', 'Veuillez créer un exercice fiscal.');
}
```

**Fichier:** `app/Http/Controllers/VatController.php` - Lignes 292, 300, 369
```php
// Ajouter vérification partner avant accès
->filter(fn($i) => $i->partner && $i->partner->vat_number && ...)
```

---

### 2. Vues Critiques à Créer

#### 2.1 Module Firm (Cabinet Comptable)
```bash
# Créer les fichiers suivants:
resources/views/firm/clients/create.blade.php
resources/views/firm/clients/show.blade.php
resources/views/firm/clients/edit.blade.php
resources/views/firm/tasks/show.blade.php
resources/views/firm/tasks/edit.blade.php
```

#### 2.2 Système d'Approbation
```bash
resources/views/approvals/index.blade.php
resources/views/approvals/pending.blade.php
resources/views/approvals/show.blade.php
resources/views/approvals/workflows/index.blade.php
resources/views/approvals/workflows/create.blade.php
resources/views/approvals/workflows/edit.blade.php
```

#### 2.3 Authentification
```bash
resources/views/auth/forgot-password.blade.php
resources/views/auth/reset-password.blade.php
```

#### 2.4 Factures
```bash
resources/views/invoices/create.blade.php
resources/views/invoices/show.blade.php
resources/views/invoices/import-ubl.blade.php
```

#### 2.5 E-Reporting
```bash
resources/views/ereporting/show.blade.php
resources/views/ereporting/compliance-report.blade.php
resources/views/ereporting/pending-invoices.blade.php
```

---

### 3. Sécurité Critique

#### 3.1 Injection SQL dans CreditNote
**Fichier:** `app/Models/CreditNote.php` - Ligne 212
```php
// Changer de:
'amount_credited' => DB::raw("amount_credited + {$this->total_incl_vat}"),

// À:
DB::statement(
    "UPDATE invoices SET amount_credited = amount_credited + ? WHERE id = ?",
    [$this->total_incl_vat, $this->invoice_id]
);
```

#### 3.2 Créer les Policies d'Autorisation
```bash
php artisan make:policy InvoicePolicy --model=Invoice
php artisan make:policy PartnerPolicy --model=Partner
php artisan make:policy BankTransactionPolicy --model=BankTransaction
php artisan make:policy AccountPolicy --model=ChartOfAccount
```

Exemple de policy:
```php
// app/Policies/InvoicePolicy.php
class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->current_company_id === $invoice->company_id;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->current_company_id === $invoice->company_id
            && $invoice->isEditable();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->current_company_id === $invoice->company_id
            && $invoice->isDeletable()
            && $user->hasPermission('delete-invoices');
    }
}
```

#### 3.3 Supprimer/Sécuriser PHPInfo
**Fichier:** `resources/views/admin/system/phpinfo.blade.php`
- Supprimer en production OU
- Ajouter restriction IP
- Exiger 2FA pour accès

---

### 4. Modèles Manquants à Créer

```bash
# Créer les modèles
php artisan make:model Expense -m
php artisan make:model ExpenseCategory -m
php artisan make:model RecurringTransaction -m
```

**Migration expenses:**
```php
Schema::create('expenses', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('partner_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
    $table->date('expense_date');
    $table->string('description');
    $table->decimal('amount', 15, 2);
    $table->string('category')->nullable();
    $table->string('account_code')->nullable();
    $table->string('status')->default('pending');
    $table->timestamps();
    $table->softDeletes();
    $table->index(['company_id', 'expense_date']);
});
```

**Migration expense_categories:**
```php
Schema::create('expense_categories', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('code')->unique();
    $table->string('account_code')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**Migration recurring_transactions:**
```php
Schema::create('recurring_transactions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
    $table->string('type'); // income, expense
    $table->string('description');
    $table->decimal('amount', 15, 2);
    $table->string('frequency'); // daily, weekly, monthly, quarterly, yearly
    $table->date('next_occurrence_date');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

---

## 🟠 PRIORITÉ HAUTE (Semaine 1-2)

### 5. Optimisation Performance

#### 5.1 Requêtes N+1 à Corriger

**InvoiceController::index() - 6 requêtes → 1 requête**
```php
// Remplacer lignes 60-67 par:
$stats = Invoice::sales()
    ->selectRaw("
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft,
        COUNT(CASE WHEN status = 'sent' THEN 1 END) as sent,
        COUNT(CASE WHEN due_date < NOW() AND amount_due > 0 THEN 1 END) as overdue,
        SUM(CASE WHEN status NOT IN ('draft', 'cancelled') THEN total_incl_vat ELSE 0 END) as total_amount,
        SUM(CASE WHEN amount_due > 0 THEN amount_due ELSE 0 END) as total_due
    ")
    ->first();
```

**AccountingController::balance() - 101 requêtes → 2 requêtes**
```php
// Pré-charger tous les soldes en une requête
$balances = JournalEntryLine::whereHas('journalEntry', fn($q) => $q->where('entry_date', '<=', $date))
    ->selectRaw('account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
    ->groupBy('account_id')
    ->get()
    ->keyBy('account_id');
```

**AnalyticsController::profitability() - 24 requêtes → 2 requêtes**
```php
$monthlyData = Invoice::where('company_id', $companyId)
    ->whereYear('issue_date', $year)
    ->selectRaw("MONTH(issue_date) as month, type, SUM(total_amount) as total")
    ->groupBy('month', 'type')
    ->get();
```

#### 5.2 Index Manquants à Ajouter
```bash
php artisan make:migration add_performance_indexes_phase2
```

```php
// Dans la migration:
Schema::table('invoices', function (Blueprint $table) {
    $table->index(['due_date', 'status', 'amount_due'], 'idx_invoices_overdue');
    $table->index(['type', 'invoice_date'], 'idx_invoices_type_date');
});

Schema::table('journal_entry_lines', function (Blueprint $table) {
    $table->index(['account_id', 'created_at'], 'idx_journal_lines_account_date');
});

Schema::table('bank_transactions', function (Blueprint $table) {
    $table->index(['value_date', 'amount'], 'idx_transactions_value_date');
});
```

#### 5.3 Mettre en File d'Attente les Opérations Lourdes
```bash
php artisan make:job GeneratePdfJob
php artisan make:job SendPeppolInvoiceJob
php artisan make:job ImportBankStatementJob
```

---

### 6. Vues Haute Priorité

```bash
# Analytics
resources/views/analytics/revenue.blade.php
resources/views/analytics/expenses.blade.php
resources/views/analytics/profitability.blade.php

# Création
resources/views/quotes/create.blade.php
resources/views/recurring-invoices/create.blade.php
resources/views/credit-notes/create.blade.php

# Bancaire
resources/views/bank/accounts.blade.php
resources/views/openbanking/account.blade.php
```

---

### 7. Sécurité Haute Priorité

#### 7.1 Rate Limiting API
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Routes API
});
```

#### 7.2 Valider les Colonnes Dynamiques SQL
```php
// app/Traits/OptimizedQueries.php
protected function validateColumn(string $column): string
{
    $allowed = ['amount', 'total', 'quantity', 'price', 'debit', 'credit'];

    if (!in_array($column, $allowed)) {
        throw new \InvalidArgumentException("Invalid column: {$column}");
    }

    return $column;
}
```

#### 7.3 Créer Form Request Classes
```bash
php artisan make:request StoreInvoiceRequest
php artisan make:request UpdateInvoiceRequest
php artisan make:request StorePartnerRequest
php artisan make:request StoreBankTransactionRequest
```

---

## 🟡 PRIORITÉ MOYENNE (Semaine 3-4)

### 8. Intégrations Externes

#### 8.1 Configuration Peppol
```env
# .env
PEPPOL_ACCESS_POINT_URL=https://api.storecove.com/v1
PEPPOL_API_KEY=your_api_key
PEPPOL_TEST_MODE=true
```

**Action:** Contacter Storecove, Ecosio ou Pagero pour obtenir les credentials.

#### 8.2 Configuration OCR
```env
GOOGLE_VISION_API_KEY=your_key
# OU
OCR_PROVIDER=tesseract
```

**Action:**
- Installer Tesseract sur le serveur: `apt-get install tesseract-ocr tesseract-ocr-fra tesseract-ocr-nld`
- OU obtenir clé API Google Vision

#### 8.3 Lookup KBO/VIES
**Fichier:** `app/Http/Controllers/Api/PartnerApiController.php`
```php
public function lookupByVat(Request $request)
{
    $vat = $request->input('vat_number');

    // VIES API call
    $client = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');

    $result = $client->checkVat([
        'countryCode' => substr($vat, 0, 2),
        'vatNumber' => substr($vat, 2),
    ]);

    return response()->json($result);
}
```

---

### 9. Amélioration Cache

#### 9.1 Étendre CacheService à tous les contrôleurs
```php
// InvoiceController
$stats = $cache->remember(
    CacheService::PREFIX_INVOICE,
    'stats_' . auth()->user()->current_company_id,
    CacheService::TTL_MEDIUM,
    fn() => $this->calculateStats()
);

// VatController
$vatData = $cache->remember(
    CacheService::PREFIX_VAT,
    "declaration_{$periodStart->format('Y-m')}",
    CacheService::TTL_LONG,
    fn() => $this->calculateVatData($periodStart, $periodEnd)
);
```

#### 9.2 Créer Commande Cache Warming
```bash
php artisan make:command WarmCache
```

```php
// app/Console/Commands/WarmCache.php
public function handle(CacheService $cache)
{
    Company::all()->each(function ($company) use ($cache) {
        $cache->forTenant($company->id);

        // Pré-charger les données fréquemment utilisées
        $this->warmDashboardMetrics($company);
        $this->warmVatRates($company);
    });
}
```

---

### 10. Envoi d'Emails d'Invitation

**Fichier:** `app/Mail/TeamInvitation.php`
```bash
php artisan make:mail TeamInvitation
```

```php
class TeamInvitation extends Mailable
{
    public function __construct(
        public Invitation $invitation,
        public AccountingFirm $firm
    ) {}

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
        );
    }
}
```

**Mettre à jour:** `app/Http/Controllers/AccountingFirmController.php`
```php
public function inviteTeamMember(Request $request)
{
    // ... création invitation ...

    Mail::to($validated['email'])->send(
        new TeamInvitation($invitation, $firm)
    );

    return back()->with('success', 'Invitation envoyée.');
}
```

---

## 🟢 PRIORITÉ BASSE (Mois 2+)

### 11. Fonctionnalités VosFactures à Implémenter

#### 11.1 Paiements en Ligne
- Intégrer Stripe ou Mollie
- Ajouter bouton "Payer en ligne" sur factures
- Webhooks pour mise à jour automatique statut

#### 11.2 Multi-langue Documents
- Ajouter champ `language` sur Invoice
- Créer templates PDF en FR/NL/EN/DE

#### 11.3 PWA (Progressive Web App)
- Créer `manifest.json`
- Ajouter service worker
- Cache offline des données critiques

#### 11.4 Portail Client
- Nouveau contrôleur `ClientPortalController`
- Vues spécifiques pour clients
- Accès limité à leurs factures

---

### 12. Documentation à Créer

```bash
docs/INSTALLATION.md          # Guide d'installation
docs/CONFIGURATION.md         # Configuration API externes
docs/API_DOCUMENTATION.md     # Documentation API REST
docs/USER_GUIDE.md           # Guide utilisateur
docs/ARCHITECTURE.md         # Architecture technique
```

---

## Rapports d'Audit Disponibles

Les rapports détaillés sont disponibles dans le dossier `docs/`:

| Rapport | Description |
|---------|-------------|
| `docs/AUDIT_BUGS.md` | 23 bugs identifiés avec corrections |
| `docs/AUDIT_MISSING_VIEWS.md` | 55 vues manquantes avec templates |
| `docs/AUDIT_PERFORMANCE.md` | Optimisations N+1, indexes, cache |
| `docs/AUDIT_MISSING_FEATURES.md` | Peppol, PSD2, OCR non connectés |
| `docs/AUDIT_SECURITY.md` | Vulnérabilités SQL, XSS, auth |

---

## Commandes Utiles

```bash
# Vérifier la syntaxe PHP
find app -name "*.php" -exec php -l {} \;

# Vérifier les routes
php artisan route:list --compact

# Vérifier les vues
php artisan view:clear && php artisan view:cache

# Migrations
php artisan migrate:status
php artisan migrate

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Démarrer serveur
php artisan serve --port=8002
```

---

## Checklist de Mise en Production

### Configuration
- [ ] Créer `.env` production avec toutes les variables
- [ ] Configurer au moins un service OCR
- [ ] Obtenir credentials Peppol Access Point
- [ ] S'inscrire aux APIs bancaires (ou agrégateur)
- [ ] Configurer emails (SMTP)

### Base de Données
- [ ] Créer modèles manquants (Expense, ExpenseCategory, RecurringTransaction)
- [ ] Exécuter toutes les migrations
- [ ] Créer seeders pour données de base
- [ ] Configurer stratégie de backup

### Sécurité
- [ ] SSL/TLS configuré
- [ ] Firewall configuré
- [ ] Rate limiting activé
- [ ] 2FA obligatoire pour admins
- [ ] Audit logs activés
- [ ] Supprimer PHPInfo

### Performance
- [ ] Redis configuré (cache, sessions, queues)
- [ ] Queue workers démarrés (Supervisor)
- [ ] Monitoring (Sentry, New Relic, etc.)
- [ ] CDN pour assets statiques

### Tests
- [ ] Tests fonctionnels passés
- [ ] Tests de sécurité passés
- [ ] Tests de charge passés
- [ ] Backup/restore testé

---

## Estimation de Temps

| Phase | Durée Estimée | Priorité |
|-------|---------------|----------|
| Bugs Critiques | 2-3 jours | P0 |
| Vues Critiques | 3-4 jours | P0 |
| Sécurité Critique | 2 jours | P0 |
| Modèles Manquants | 1 jour | P0 |
| Performance | 3-4 jours | P1 |
| Intégrations | 5-7 jours | P2 |
| Documentation | 2-3 jours | P2 |
| **TOTAL** | **18-24 jours** | - |

---

**Dernière mise à jour:** 2025-12-20
**Prochaine révision recommandée:** Après correction des bugs critiques

