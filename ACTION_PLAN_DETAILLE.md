# PLAN D'ACTION DÉTAILLÉ - COMPTABE

**Date**: 2025-12-31
**Destinataires**: Équipe technique
**Type**: Checklist opérationnelle

---

## 🔴 PHASE 0 - CRITIQUE (J0-J2) - 16h développement

### Objectif
Sécuriser application pour MVP - Corriger vulnérabilités bloquantes

### Sécurité (10h)

#### 1. Activer chiffrement sessions (30min)
```bash
# .env
SESSION_ENCRYPT=true
```
- [ ] Modifier `.env.example` ligne 28
- [ ] Modifier `.env` production
- [ ] Tester login/logout
- [ ] Vérifier persistence session
- [ ] **Fichier**: `.env`, `.env.example`

#### 2. Restreindre exemption CSRF (1h)
```php
// bootstrap/app.php ligne 30-32
$middleware->validateCsrfTokens(except: [
    'webhooks/mollie',
    'webhooks/stripe',
    'webhooks/peppol/callback',
]);
```
- [ ] Modifier `bootstrap/app.php`
- [ ] Lister webhooks légitimes uniquement
- [ ] Tester chaque webhook
- [ ] Documenter endpoints exemptés
- [ ] **Fichier**: `bootstrap/app.php`

#### 3. Rate limiting login/2FA (2h)
```php
// routes/web.php
Route::middleware('throttle:5,15')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
});
```
- [ ] Créer middleware throttle login
- [ ] Appliquer sur `/login`, `/2fa/verify`, `/password/email`
- [ ] Config: 5 tentatives / 15 minutes
- [ ] Tester blocage après 5 tentatives
- [ ] Message erreur clair utilisateur
- [ ] Logger tentatives bloquées
- [ ] **Fichier**: `routes/web.php`

#### 4. Validation magic bytes uploads (4h)
```php
// app/Http/Controllers/DocumentController.php ligne 154-165

public function store(Request $request)
{
    $request->validate([
        'file' => 'required|file|max:20480',
    ]);

    $file = $request->file('file');

    // Validation magic bytes
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file->getRealPath());
    finfo_close($finfo);

    $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
    ];

    if (!in_array($mimeType, $allowedMimes)) {
        throw new \Exception("Type de fichier non autorisé: {$mimeType}");
    }

    // Stockage sur disk 'private' au lieu de 'public'
    $path = $file->store("documents/{$year}/{$month}", 'private');

    // ...
}
```
- [ ] Implémenter validation finfo_file
- [ ] Bloquer types dangereux (.exe, .sh, .php, .js)
- [ ] Changer disk 'public' → 'private'
- [ ] Créer route `/documents/download/{id}` avec auth
- [ ] Tester upload PDF, image, Excel
- [ ] Tester rejet fichier .php renommé .pdf
- [ ] **Fichier**: `app/Http/Controllers/DocumentController.php`

#### 5. Logging uploads sécurisé (30min)
```php
// Ajouter dans DocumentController
AuditLog::log('document_uploaded', Document::class, $document->id, [
    'filename' => $originalName,
    'mime_type' => $mimeType,
    'size' => $file->getSize(),
    'validated_mime' => $finfo_mime,
]);
```
- [ ] Logger tous uploads (succès + échecs)
- [ ] Inclure mime détecté vs extension
- [ ] **Fichier**: `app/Http/Controllers/DocumentController.php`

#### 6. Route téléchargement sécurisée (2h)
```php
// routes/web.php
Route::get('/documents/download/{document}', [DocumentController::class, 'download'])
    ->middleware(['auth', 'tenant'])
    ->name('documents.download');

// DocumentController.php
public function download(Document $document)
{
    $this->authorize('view', $document);

    if ($document->company_id !== session('current_tenant_id')) {
        abort(403);
    }

    return Storage::disk('private')->download($document->file_path, $document->original_filename);
}
```
- [ ] Créer route download avec auth
- [ ] Vérifier company_id (multi-tenant)
- [ ] Tester téléchargement avec/sans auth
- [ ] Tester accès cross-tenant bloqué
- [ ] **Fichier**: `routes/web.php`, `app/Http/Controllers/DocumentController.php`

### Conformité (2h)

#### 7. Corriger bug reverse charge (30min)
```php
// app/Services/Compliance/BelgianTaxComplianceService.php ligne 57-58
// AVANT (BUGGY):
->where('vat_number', 'LIKE', 'BE%')
->where('vat_number', 'NOT LIKE', 'BE%') // ❌ CONTRADICTOIRE

// APRÈS (CORRECT):
->whereHas('partner', function ($query) {
    $query->whereNotNull('vat_number')
          ->where('vat_number', 'NOT LIKE', 'BE%'); // Seulement non-BE
})
->where('vat_amount', '>', 0)
```
- [ ] Supprimer première condition `LIKE 'BE%'`
- [ ] Garder uniquement `NOT LIKE 'BE%'`
- [ ] Tester détection reverse charge avec facture UE
- [ ] Vérifier alerte générée correctement
- [ ] **Fichier**: `app/Services/Compliance/BelgianTaxComplianceService.php` ligne 57-58

#### 8. Documenter politique archivage (1h30)
```markdown
# POLITIQUE D'ARCHIVAGE LÉGAL - ComptaBE

## Durées de conservation conformes législation belge

| Type Document | Durée | Base Légale |
|---------------|-------|-------------|
| Factures (achat/vente) | 10 ans | AR TVA art. 60 |
| Documents comptables | 7 ans | C. soc. art. 3:17 |
| Fiches de paie | Illimitée | Code social |
| Déclarations TVA | 7 ans | AR TVA |
| Comptes annuels | 10 ans | C. soc. art. 3:17 |
| Contrats | 10 ans après fin | Code civil |
| Registres sociaux | Permanente | C. soc. |

## Implémentation technique

- Soft delete avec `deleted_at`
- Purge automatique après expiration
- Export archive avant purge (PDF/A)
- RGPD: Anonymisation données perso après durée légale
```
- [ ] Créer fichier `docs/ARCHIVAGE_LEGAL.md`
- [ ] Documenter durées légales
- [ ] Ajouter section RGPD
- [ ] Référencer dans README
- [ ] **Fichier**: `docs/ARCHIVAGE_LEGAL.md` (nouveau)

### Performance (4h)

#### 9. Pagination factures (1h)
```php
// app/Http/Controllers/InvoiceController.php

// AVANT:
$invoices = Invoice::all(); // ❌ Charge toutes

// APRÈS:
$invoices = Invoice::with(['partner', 'items'])
    ->orderBy('issue_date', 'desc')
    ->paginate(50);
```
- [ ] Modifier `InvoiceController::index`
- [ ] Ajouter `paginate(50)`
- [ ] Eager loading `with(['partner', 'items'])`
- [ ] Modifier vue Blade pour pagination links
- [ ] Tester avec 1000+ factures
- [ ] **Fichier**: `app/Http/Controllers/InvoiceController.php`, `resources/views/invoices/index.blade.php`

#### 10. Pagination partners (30min)
```php
// app/Http/Controllers/PartnerController.php
$partners = Partner::orderBy('name')->paginate(50);
```
- [ ] Modifier `PartnerController::index`
- [ ] `paginate(50)`
- [ ] Modifier vue Blade
- [ ] **Fichier**: `app/Http/Controllers/PartnerController.php`

#### 11. Pagination transactions (30min)
```php
// app/Http/Controllers/BankTransactionController.php
$transactions = BankTransaction::with('reconciliation')
    ->orderBy('transaction_date', 'desc')
    ->paginate(100);
```
- [ ] Modifier `BankTransactionController::index`
- [ ] `paginate(100)`
- [ ] Eager loading
- [ ] **Fichier**: `app/Http/Controllers/BankTransactionController.php`

#### 12. Top 5 queries N+1 (2h)
```php
// Identifier avec Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

// Exemples à corriger:
// 1. InvoiceController::index
$invoices = Invoice::with(['partner', 'items', 'payments'])->paginate(50);

// 2. DashboardController
$recentActivity = AuditLog::with('user', 'auditable')->latest()->limit(10)->get();

// 3. ReportController
$expenses = Expense::with('category', 'account', 'vat_code')->get();
```
- [ ] Installer Laravel Debugbar
- [ ] Identifier top 5 pages avec N+1
- [ ] Ajouter `with()` relations
- [ ] Tester réduction queries (250 → <50)
- [ ] **Fichiers**: Multiples controllers

---

## 🟠 PHASE 1 - URGENT (J3-J14) - 80h développement

### Objectif
Production-ready pour 10-50 entreprises

### Sécurité (24h)

#### 13. Renforcer TenantScope (4h)
```php
// app/Models/Scopes/TenantScope.php ligne 16

public function apply(Builder $builder, Model $model)
{
    if (!auth()->check()) {
        return; // Pas de filtre si pas connecté
    }

    $tenantId = session('current_tenant_id');

    if (!$tenantId) {
        throw new \Exception("Aucun tenant sélectionné");
    }

    // Vérifier que l'utilisateur a accès au tenant
    $user = auth()->user();
    if (!$user->hasAccessToCompany($tenantId)) {
        throw new \Exception("Accès refusé au tenant {$tenantId}");
    }

    $builder->where($model->getTable() . '.company_id', $tenantId);
}
```
- [ ] Ajouter vérification `auth()->check()`
- [ ] Vérifier `hasAccessToCompany()`
- [ ] Exception si tenant invalide
- [ ] Logging tentatives accès non autorisé
- [ ] Tester accès cross-tenant bloqué
- [ ] **Fichier**: `app/Models/Scopes/TenantScope.php`

#### 14. Middleware tenant validation (3h)
```php
// app/Http/Middleware/TenantMiddleware.php

public function handle(Request $request, Closure $next)
{
    if (!$request->user()) {
        return redirect()->route('login');
    }

    $tenantId = session('current_tenant_id');

    if (!$tenantId) {
        // Rediriger vers sélection entreprise
        return redirect()->route('companies.select');
    }

    if (!$request->user()->hasAccessToCompany($tenantId)) {
        session()->forget('current_tenant_id');
        abort(403, "Accès refusé à cette entreprise");
    }

    // Vérifier à chaque requête (pas juste au login)
    $company = Company::find($tenantId);
    if (!$company || !$company->is_active) {
        session()->forget('current_tenant_id');
        abort(403, "Entreprise inactive ou supprimée");
    }

    return $next($request);
}
```
- [ ] Validation tenant à chaque requête
- [ ] Vérifier `is_active`
- [ ] Redirection si tenant invalide
- [ ] Tester suspension entreprise
- [ ] **Fichier**: `app/Http/Middleware/TenantMiddleware.php`

#### 15. Chiffrer IBAN/BIC (4h)
```php
// app/Models/Partner.php

protected $casts = [
    'iban' => 'encrypted',
    'bic' => 'encrypted',
];

// Migration
Schema::table('partners', function (Blueprint $table) {
    // Colonnes déjà existantes, juste changer cast
});

// app/Models/Company.php
protected $casts = [
    'bank_account' => 'encrypted',
];

// app/Models/User.php (si numéro registre national)
protected $casts = [
    'national_number' => 'encrypted',
];
```
- [ ] Ajouter cast `encrypted` sur IBAN, BIC
- [ ] Tester création/lecture partner
- [ ] Vérifier données chiffrées en DB
- [ ] Migration existantes (pas besoin si cast suffit)
- [ ] **Fichiers**: `app/Models/Partner.php`, `app/Models/Company.php`

#### 16. Expiration tokens API 30j (2h)
```php
// config/sanctum.php
'expiration' => 30 * 24, // 30 jours en minutes

// app/Http/Controllers/Api/TokenController.php
public function create(Request $request)
{
    $token = $request->user()->createToken(
        $request->name,
        $request->abilities ?? ['*'],
        now()->addDays(30) // Expiration explicite
    );

    return response()->json([
        'token' => $token->plainTextToken,
        'expires_at' => now()->addDays(30)->toIso8601String(),
    ]);
}
```
- [ ] Configurer `expiration` dans `config/sanctum.php`
- [ ] Ajouter expiration explicite à création token
- [ ] Retourner `expires_at` en réponse API
- [ ] Tester token expiré rejeté
- [ ] Job nettoyage tokens expirés (daily)
- [ ] **Fichier**: `config/sanctum.php`, `app/Http/Controllers/Api/TokenController.php`

#### 17. Audit whereRaw (6h)
```bash
# Trouver tous les whereRaw
grep -r "whereRaw\|selectRaw\|orderByRaw" app/
```
- [ ] Lister 20 fichiers identifiés
- [ ] Vérifier chacun pour injection SQL
- [ ] Remplacer par query builder safe si possible
- [ ] Documenter raisons si whereRaw nécessaire
- [ ] **Fichiers**: Multiples (20 fichiers)

#### 18. FormRequests manquantes (5h)
```php
// app/Http/Requests/StorePartnerRequest.php
class StorePartnerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{10}$/|unique:partners,vat_number',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'iban' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/',
            'bic' => 'nullable|string|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
        ];
    }
}

// À créer:
// - StorePartnerRequest, UpdatePartnerRequest
// - StoreExpenseRequest, UpdateExpenseRequest
// - StoreProductRequest, UpdateProductRequest
// - StoreAccountRequest, UpdateAccountRequest
```
- [ ] Créer 8 FormRequests manquantes
- [ ] Validation regex (VAT, IBAN, BIC)
- [ ] Appliquer dans controllers
- [ ] Tester validation
- [ ] **Fichiers**: `app/Http/Requests/*` (nouveaux)

### Conformité (16h)

#### 19. Table retention_policies (4h)
```php
// database/migrations/xxxx_create_retention_policies_table.php
Schema::create('retention_policies', function (Blueprint $table) {
    $table->id();
    $table->string('document_type'); // invoice, expense, payslip, etc.
    $table->integer('retention_years');
    $table->string('legal_basis'); // AR TVA art. 60, C. soc. art. 3:17, etc.
    $table->boolean('permanent')->default(false); // Pour fiches de paie
    $table->boolean('anonymize_after')->default(true); // RGPD
    $table->timestamps();
});

// database/seeders/RetentionPolicySeeder.php
DB::table('retention_policies')->insert([
    ['document_type' => 'invoice', 'retention_years' => 10, 'legal_basis' => 'AR TVA art. 60'],
    ['document_type' => 'expense', 'retention_years' => 10, 'legal_basis' => 'AR TVA art. 60'],
    ['document_type' => 'vat_declaration', 'retention_years' => 7, 'legal_basis' => 'AR TVA'],
    ['document_type' => 'journal_entry', 'retention_years' => 7, 'legal_basis' => 'C. soc. art. 3:17'],
    ['document_type' => 'annual_accounts', 'retention_years' => 10, 'legal_basis' => 'C. soc. art. 3:17'],
    ['document_type' => 'payslip', 'retention_years' => 999, 'permanent' => true, 'legal_basis' => 'Code social'],
    ['document_type' => 'contract', 'retention_years' => 10, 'legal_basis' => 'Code civil'],
]);

// app/Models/RetentionPolicy.php
class RetentionPolicy extends Model
{
    public function getRetentionDate(Carbon $createdAt): Carbon
    {
        if ($this->permanent) {
            return Carbon::maxValue();
        }
        return $createdAt->copy()->addYears($this->retention_years);
    }
}
```
- [ ] Créer migration `retention_policies`
- [ ] Créer seeder avec durées légales
- [ ] Créer modèle `RetentionPolicy`
- [ ] Méthode `getRetentionDate()`
- [ ] **Fichiers**: Migration, Seeder, Model nouveaux

#### 20. Soft delete automatique (4h)
```php
// app/Console/Commands/PurgeExpiredDocuments.php
class PurgeExpiredDocuments extends Command
{
    public function handle()
    {
        $policies = RetentionPolicy::all();

        foreach ($policies as $policy) {
            if ($policy->permanent) {
                continue;
            }

            $modelClass = $this->getModelClass($policy->document_type);
            $expirationDate = now()->subYears($policy->retention_years);

            $expired = $modelClass::where('created_at', '<', $expirationDate)
                ->whereNull('deleted_at')
                ->get();

            foreach ($expired as $document) {
                // Export avant suppression
                $this->exportForArchive($document);

                // Soft delete
                $document->delete();

                $this->info("Deleted {$policy->document_type} ID {$document->id}");
            }
        }
    }
}

// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('documents:purge-expired')->monthly();
}
```
- [ ] Créer command `PurgeExpiredDocuments`
- [ ] Export PDF avant suppression
- [ ] Soft delete après expiration
- [ ] Scheduler mensuel
- [ ] Logging purges
- [ ] **Fichier**: `app/Console/Commands/PurgeExpiredDocuments.php`

#### 21. Intégrer KBO API (6h)
```php
// app/Services/Integrations/KboService.php
class KboService
{
    protected $baseUrl = 'https://kbopub.economie.fgov.be/kbopub/zoeknummerform.html';

    public function validateEnterpriseNumber(string $number): array
    {
        // Nettoyer format: 0123.456.789 → 0123456789
        $cleaned = preg_replace('/[^0-9]/', '', $number);

        if (strlen($cleaned) !== 10) {
            throw new \Exception("Numéro entreprise invalide: {$number}");
        }

        // API KBO publique (JSON)
        $response = Http::timeout(10)
            ->get("https://kbopub.economie.fgov.be/kbopub/api/enterprises/{$cleaned}");

        if (!$response->successful()) {
            throw new \Exception("Entreprise non trouvée au KBO: {$number}");
        }

        $data = $response->json();

        return [
            'kbo_number' => $cleaned,
            'name' => $data['name'] ?? null,
            'legal_form' => $data['legalForm'] ?? null,
            'address' => $data['address'] ?? null,
            'vat_number' => $data['vatNumber'] ?? null,
            'status' => $data['status'] ?? null, // AC (active), ST (stopped)
            'is_active' => $data['status'] === 'AC',
        ];
    }
}

// app/Http/Controllers/PartnerController.php
public function verifyKbo(Request $request)
{
    $kboService = new KboService();

    try {
        $data = $kboService->validateEnterpriseNumber($request->kbo_number);
        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 404);
    }
}
```
- [ ] Créer `KboService`
- [ ] Méthode `validateEnterpriseNumber()`
- [ ] Route API `/api/partners/verify-kbo`
- [ ] Tester avec numéro KBO valide
- [ ] Tester rejet entreprise radiée
- [ ] Cache résultats 24h (Redis)
- [ ] **Fichier**: `app/Services/Integrations/KboService.php` (nouveau)

#### 22. Compléter grilles TVA IC (2h)
```php
// app/Services/Vat/VatDeclarationService.php

// Ajouter grilles manquantes
const GRIDS = [
    // ... grilles existantes
    '44' => 'Services intracommunautaires',
    '45' => 'Livraisons intracommunautaires',
    '46' => 'Opérations avec cocontractant',
    '83' => 'Acquisitions intracommunautaires',
    '86' => 'Opérations intracommunautaires sortantes',
    '87' => 'Opérations intracommunautaires entrantes',
];

protected function calculateGrid44(string $companyId, Carbon $start, Carbon $end): float
{
    // Services fournis à clients UE (B2B)
    return Invoice::where('company_id', $companyId)
        ->whereBetween('issue_date', [$start, $end])
        ->whereHas('partner', function ($query) {
            $query->whereNotNull('vat_number')
                  ->where('vat_number', 'NOT LIKE', 'BE%');
        })
        ->where('invoice_type', 'service') // Distinguer services vs biens
        ->sum('subtotal_amount');
}

// Similaire pour grilles 45, 46, 83, 86, 87
```
- [ ] Ajouter constantes grilles IC
- [ ] Méthode `calculateGrid44()` services IC
- [ ] Méthode `calculateGrid45()` livraisons IC
- [ ] Méthode `calculateGrid46()` cocontractant
- [ ] Méthode `calculateGrid83()` acquisitions IC
- [ ] Tester calcul avec factures UE
- [ ] **Fichier**: `app/Services/Vat/VatDeclarationService.php`

### Performance (24h)

#### 23. Cache dashboard stats (4h)
```php
// app/Http/Controllers/DashboardController.php

public function index()
{
    $companyId = session('current_tenant_id');

    $stats = Cache::remember("dashboard_stats_{$companyId}", 3600, function () use ($companyId) {
        return [
            'total_invoices' => Invoice::where('company_id', $companyId)->count(),
            'pending_invoices' => Invoice::where('company_id', $companyId)
                ->where('status', 'pending')
                ->count(),
            'overdue_invoices' => Invoice::where('company_id', $companyId)
                ->where('status', 'overdue')
                ->count(),
            'total_revenue_month' => Invoice::where('company_id', $companyId)
                ->whereBetween('issue_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount'),
            'total_expenses_month' => Expense::where('company_id', $companyId)
                ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
        ];
    });

    return view('dashboard', compact('stats'));
}

// Invalider cache lors de création/modification
// app/Models/Invoice.php
protected static function booted()
{
    static::saved(function ($invoice) {
        Cache::forget("dashboard_stats_{$invoice->company_id}");
    });
}
```
- [ ] Wrapper `Cache::remember()` sur stats dashboard
- [ ] TTL 1h (3600s)
- [ ] Invalider cache sur create/update/delete
- [ ] Tester performance (3.5s → <1s)
- [ ] **Fichier**: `app/Http/Controllers/DashboardController.php`

#### 24. Indexes DB (4h)
```php
// database/migrations/xxxx_add_performance_indexes.php

Schema::table('invoices', function (Blueprint $table) {
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'issue_date']);
    $table->index(['partner_id']);
    $table->index(['status', 'due_date']); // Pour overdue queries
});

Schema::table('expenses', function (Blueprint $table) {
    $table->index(['company_id', 'expense_date']);
    $table->index(['account_id']);
    $table->index(['category_id']);
});

Schema::table('bank_transactions', function (Blueprint $table) {
    $table->index(['company_id', 'transaction_date']);
    $table->index(['reconciliation_status']);
});

Schema::table('journal_entries', function (Blueprint $table) {
    $table->index(['company_id', 'entry_date']);
    $table->index(['status']);
});

Schema::table('audit_logs', function (Blueprint $table) {
    $table->index(['company_id', 'created_at']);
    $table->index(['user_id']);
    $table->index(['auditable_type', 'auditable_id']);
});
```
- [ ] Créer migration indexes
- [ ] Indexes composites company_id + date
- [ ] Indexes foreign keys
- [ ] Exécuter migration
- [ ] `EXPLAIN` queries avant/après
- [ ] **Fichier**: Migration nouvelle

#### 25. Eager loading systematique (8h)
```php
// Auditer tous controllers et ajouter with()

// InvoiceController
$invoices = Invoice::with(['partner', 'items', 'payments', 'vat_code'])
    ->paginate(50);

// ExpenseController
$expenses = Expense::with(['category', 'account', 'vat_code', 'supplier'])
    ->paginate(50);

// DashboardController (recent activity)
$recentActivity = AuditLog::with('user', 'auditable')
    ->latest()
    ->limit(20)
    ->get();

// BankTransactionController
$transactions = BankTransaction::with(['reconciliation', 'account'])
    ->paginate(100);

// Etc. pour tous les controllers
```
- [ ] Lister tous controllers avec queries
- [ ] Ajouter `with()` relations utilisées en vue
- [ ] Tester avec Laravel Debugbar
- [ ] Vérifier réduction queries (250 → <50/page)
- [ ] **Fichiers**: 15+ controllers

#### 26. Code splitting Vite (4h)
```js
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpine', 'chart.js'],
                    'dashboard': ['./resources/js/dashboard.js'],
                    'invoices': ['./resources/js/invoices.js'],
                },
            },
        },
    },
});

// resources/js/app.js
import Alpine from 'alpinejs';

// Lazy load modules
document.addEventListener('alpine:init', () => {
    if (document.getElementById('dashboard')) {
        import('./dashboard.js');
    }

    if (document.getElementById('invoices-page')) {
        import('./invoices.js');
    }
});
```
- [ ] Configurer `manualChunks` Vite
- [ ] Séparer vendor, dashboard, invoices
- [ ] Lazy load modules conditionnels
- [ ] Tester bundle size (2.5MB → 1MB initial)
- [ ] **Fichier**: `vite.config.js`, `resources/js/app.js`

#### 27. Lazy loading images (2h)
```html
<!-- resources/views/components/image.blade.php -->
<img src="{{ $src }}"
     alt="{{ $alt }}"
     loading="lazy"
     class="{{ $class }}"
     width="{{ $width ?? 'auto' }}"
     height="{{ $height ?? 'auto' }}">

<!-- Usage -->
<x-image src="/storage/logos/{{ $company->logo }}" alt="{{ $company->name }}" loading="lazy" />
```
- [ ] Créer composant `x-image`
- [ ] Attribut `loading="lazy"` natif
- [ ] Remplacer `<img>` par `<x-image>` dans vues
- [ ] **Fichier**: `resources/views/components/image.blade.php` (nouveau)

#### 28. Compression Brotli (2h)
```nginx
# nginx.conf (si nginx)
http {
    brotli on;
    brotli_comp_level 6;
    brotli_types text/plain text/css application/json application/javascript text/xml application/xml;
}

# Apache .htaccess (si Apache)
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json
</IfModule>
```
- [ ] Activer Brotli/Gzip serveur web
- [ ] Tester compression assets
- [ ] Vérifier headers `Content-Encoding`
- [ ] **Fichier**: Config serveur web

### IA (8h)

#### 29. Rate limiting chat (2h)
```php
// routes/web.php
Route::middleware('throttle:100,60')->group(function () {
    Route::post('/ai/chat', [ChatController::class, 'send']);
});

// app/Http/Controllers/ChatController.php
public function send(Request $request)
{
    $user = $request->user();

    // Vérifier quota journalier
    $todayUsage = ChatMessage::where('user_id', $user->id)
        ->whereDate('created_at', today())
        ->count();

    if ($todayUsage >= 200) { // 200 messages/jour
        return response()->json([
            'error' => 'Quota journalier dépassé (200 messages/jour)',
            'reset_at' => today()->addDay()->toIso8601String(),
        ], 429);
    }

    // ...
}
```
- [ ] Throttle 100 req/h sur `/ai/chat`
- [ ] Quota 200 messages/jour/user
- [ ] Message erreur clair
- [ ] Logger rate limit hits
- [ ] **Fichier**: `routes/web.php`, `app/Http/Controllers/ChatController.php`

#### 30. Configurer Google Vision (3h)
```bash
# .env
GOOGLE_CLOUD_PROJECT_ID=comptabe-production
GOOGLE_CLOUD_KEY_FILE=/path/to/service-account.json
```

```php
// app/Services/OcrService.php ligne 94

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

public function extractTextFromImage(string $imagePath): string
{
    try {
        // Google Vision (prioritaire)
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => config('services.google_vision.key_file'),
        ]);

        $image = file_get_contents($imagePath);
        $response = $imageAnnotator->documentTextDetection($image);

        $text = '';
        if ($response->getTextAnnotations()->count() > 0) {
            $text = $response->getTextAnnotations()[0]->getDescription();
        }

        $imageAnnotator->close();

        return $text;

    } catch (\Exception $e) {
        // Fallback tesseract si Google Vision échoue
        Log::warning("Google Vision failed, fallback to tesseract: " . $e->getMessage());
        return $this->extractWithTesseract($imagePath);
    }
}

protected function extractWithTesseract(string $imagePath): string
{
    // Tesseract local (déjà implémenté)
    $text = shell_exec("tesseract {$imagePath} stdout -l fra+nld+eng");
    return $text ?? '';
}
```
- [ ] Créer projet Google Cloud
- [ ] Activer Vision API
- [ ] Créer service account + JSON key
- [ ] Configurer `.env`
- [ ] Tester OCR avec Google Vision
- [ ] Vérifier fallback tesseract si échec
- [ ] **Fichier**: `app/Services/OcrService.php`, `.env`

#### 31. SSE streaming chat (3h)
```php
// routes/web.php
Route::get('/ai/chat/stream', [ChatController::class, 'stream'])
    ->middleware(['auth', 'throttle:100,60']);

// app/Http/Controllers/ChatController.php
public function stream(Request $request)
{
    return response()->stream(function () use ($request) {
        $prompt = $request->input('message');

        // Ollama streaming
        $process = proc_open(
            "ollama run llama2 \"{$prompt}\"",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        while (!feof($pipes[1])) {
            $chunk = fgets($pipes[1]);

            echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
            ob_flush();
            flush();
        }

        fclose($pipes[1]);
        proc_close($process);

    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no', // Nginx
    ]);
}
```

```js
// resources/js/components/chat.js
const eventSource = new EventSource(`/ai/chat/stream?message=${encodeURIComponent(message)}`);

eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    appendChunk(data.chunk);
};

eventSource.onerror = () => {
    eventSource.close();
};
```
- [ ] Route SSE `/ai/chat/stream`
- [ ] Streaming Ollama output
- [ ] EventSource client-side
- [ ] Affichage progressif réponse
- [ ] Tester latence améliorée
- [ ] **Fichiers**: `app/Http/Controllers/ChatController.php`, `resources/js/components/chat.js`

### UX (8h)

#### 32. Loading states universels (2h)
```html
<!-- resources/views/components/loading-spinner.blade.php -->
<div x-show="loading" class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>

<!-- Usage dans formulaires -->
<form @submit="loading = true">
    <button type="submit" :disabled="loading">
        <span x-show="!loading">Enregistrer</span>
        <x-loading-spinner x-show="loading" />
    </button>
</form>
```
- [ ] Créer composant `x-loading-spinner`
- [ ] Appliquer sur tous boutons submit
- [ ] Spinner sur chargement listes
- [ ] **Fichier**: `resources/views/components/loading-spinner.blade.php`

#### 33. Toast queue (3h)
```js
// resources/js/toast.js
class ToastQueue {
    constructor() {
        this.queue = [];
        this.current = null;
    }

    show(message, type = 'success', duration = 3000) {
        this.queue.push({ message, type, duration });
        if (!this.current) {
            this.processQueue();
        }
    }

    processQueue() {
        if (this.queue.length === 0) {
            this.current = null;
            return;
        }

        this.current = this.queue.shift();
        this.displayToast(this.current);

        setTimeout(() => {
            this.processQueue();
        }, this.current.duration);
    }

    displayToast({ message, type }) {
        // Alpine.js toast component
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }
}

window.toast = new ToastQueue();

// Usage
toast.show('Facture créée avec succès', 'success');
```
- [ ] Créer classe `ToastQueue`
- [ ] Gestion file d'attente
- [ ] Auto-dismiss configurable
- [ ] Types: success, error, warning, info
- [ ] **Fichier**: `resources/js/toast.js`

#### 34. Skeleton screens (3h)
```html
<!-- resources/views/components/skeleton-table.blade.php -->
<div class="animate-pulse">
    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
    <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
    <div class="h-4 bg-gray-200 rounded w-5/6 mb-4"></div>
</div>

<!-- Usage -->
<div x-show="loading">
    <x-skeleton-table />
</div>
<div x-show="!loading">
    <!-- Tableau réel -->
</div>
```
- [ ] Créer composants skeleton (table, card, list)
- [ ] Animation pulse CSS
- [ ] Appliquer sur dashboards
- [ ] **Fichier**: `resources/views/components/skeleton-*.blade.php`

---

## 🟡 PHASE 2 - IMPORTANT (J15-J30) - 80h

### Objectif
Scalabilité 50-500 entreprises, performance <1s

### Sécurité (16h)

#### 35. HMAC webhooks (4h)
#### 36. Logging Observers (4h)
#### 37. CSP headers (3h)
#### 38. FormRequests reste (5h)

### Conformité (12h)

#### 39. DMFA si RH (8h)
#### 40. Listing IC automatisé (4h)

### Performance (32h)

#### 41. Laravel Telescope production (4h)
#### 42. Redis Sentinel HA (8h)
#### 43. CDN CloudFlare (4h)
#### 44. Query optimization -50% (16h)

### IA (12h)

#### 45. Embeddings vectoriels (6h)
#### 46. ML scikit-learn (6h)

### Intégrations (8h)

#### 47. Open Banking test (4h)
#### 48. Retry logic APIs (2h)
#### 49. Monitoring uptime (2h)

---

## 🟢 PHASE 3 - EXCELLENCE (J31-J90) - 320h

### Sécurité (60h)
#### 50. Pentest externe (24h)
#### 51. PCI-DSS audit (16h)
#### 52. SIEM integration (12h)
#### 53. Rotation clés (8h)

### Conformité (40h)
#### 54. DIMONA si RH (24h)
#### 55. Signature eID Intervat (8h)
#### 56. E-invoicing 2028 (8h)

### Performance (80h)
#### 57. Auto-scaling infra (24h)
#### 58. Load testing (16h)
#### 59. APM DataDog (16h)
#### 60. GraphQL API (24h)

### IA (80h)
#### 61. Versioning modèles (16h)
#### 62. A/B testing (16h)
#### 63. Monitoring accuracy (16h)
#### 64. Multi-modal (32h)

### UX (60h)
#### 65. Guided tour (16h)
#### 66. Drag & drop (16h)
#### 67. Command palette (12h)
#### 68. PWA offline (16h)

---

## 📊 SUIVI PROGRESSION

### Checklist Phase 0 (16h)
- [ ] 1. SESSION_ENCRYPT=true (30min)
- [ ] 2. CSRF restreint (1h)
- [ ] 3. Rate limiting login (2h)
- [ ] 4. Magic bytes uploads (4h)
- [ ] 5. Logging uploads (30min)
- [ ] 6. Route download (2h)
- [ ] 7. Bug reverse charge (30min)
- [ ] 8. Doc archivage (1h30)
- [ ] 9. Pagination factures (1h)
- [ ] 10. Pagination partners (30min)
- [ ] 11. Pagination transactions (30min)
- [ ] 12. Top 5 N+1 (2h)

**Total**: 16h → **GO/NO-GO PRODUCTION**

### Checklist Phase 1 (80h)
- [ ] 13-18: Sécurité (24h)
- [ ] 19-22: Conformité (16h)
- [ ] 23-28: Performance (24h)
- [ ] 29-31: IA (8h)
- [ ] 32-34: UX (8h)

**Total**: 80h → **PRODUCTION 10-50 entreprises**

---

**Dernière mise à jour**: 2025-12-31
**Maintenu par**: Équipe technique ComptaBE
**Révision**: Hebdomadaire durant exécution
