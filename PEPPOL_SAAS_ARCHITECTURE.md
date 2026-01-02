# Architecture SaaS Centralisée Peppol - ComptaBE

## 📋 Vue d'ensemble

Suite à votre demande, l'architecture Peppol a été **complètement repensée** pour fonctionner comme un véritable SaaS avec API centralisée et gestion de quotas.

---

## 🎯 Changements d'Architecture

### ❌ Ancien Modèle (Décentralisé)
```
┌──────────────┐         ┌──────────────┐
│  Entreprise A│────────▶│ Recommand.eu │
│  (API key A) │         │              │
└──────────────┘         └──────────────┘

┌──────────────┐         ┌──────────────┐
│  Entreprise B│────────▶│  Digiteal    │
│  (API key B) │         │              │
└──────────────┘         └──────────────┘

Problèmes:
✗ Chaque entreprise doit acheter son abonnement
✗ Gestion des API keys compliquée pour les clients
✗ Pas de marge pour le SaaS
✗ Support client difficile
```

### ✅ Nouveau Modèle (Centralisé SaaS)
```
          ┌─────────────────────────────────┐
          │      SUPERADMIN (ComptaBE)      │
          │                                 │
          │  • UNE API key globale          │
          │  • Abonnement Recommand.eu Pro  │
          │  • €99/mois = 1000 docs         │
          │  • Gestion centralisée          │
          └────────────┬────────────────────┘
                       │
                       │ API Centralisée
                       │
          ┌────────────┴────────────┐
          │                         │
     ┌────▼───────┐         ┌──────▼────┐
     │ Tenant A   │         │ Tenant B  │
     │            │         │           │
     │ Plan: Free │         │ Plan: Pro │
     │ 20/mois    │         │ 100/mois  │
     │ Usage: 5   │         │ Usage: 45 │
     └────────────┘         └───────────┘

Avantages:
✓ Un seul abonnement global
✓ Quotas par entreprise
✓ Tracking d'usage
✓ Marge importante
✓ Facturation automatique
✓ Support centralisé
```

---

## 💰 Modèle Commercial

### Plans d'Abonnement Recommandés

| Plan | Factures/mois | Prix Client | Coût Recommand | Marge | ROI |
|------|--------------|-------------|----------------|-------|-----|
| **Free** | 20 | Gratuit | €0 | €0 | Lead magnet |
| **Starter** | 50 | €15/mois | ~€0 | €15 | 100% |
| **Pro** | 100 | €49/mois | ~€10 | €39 | 390% |
| **Business** | 500 | €149/mois | ~€50 | €99 | 198% |
| **Enterprise** | Illimité | Sur mesure | ~€200 | Sur mesure | 100%+ |

### Coûts Provider (Recommand.eu)

- **Free**: 25 docs gratuits, puis €0.30/doc
- **Starter** (€29/mois): 200 docs inclus, puis €0.20/doc
- **Pro** (€99/mois): 1000 docs inclus, puis €0.10/doc
- **Enterprise**: Sur mesure

**Recommandation**: Commencer avec plan **Pro (€99/mois)** = 1000 documents/mois

---

## 🗄️ Nouvelles Tables & Champs

### 1. Table `companies` - Nouveaux champs

```sql
-- Système de quotas
peppol_plan VARCHAR(255) DEFAULT 'free'
peppol_quota_monthly INT DEFAULT 20
peppol_usage_current_month INT DEFAULT 0
peppol_usage_last_reset TIMESTAMP NULL
peppol_overage_allowed BOOLEAN DEFAULT false
peppol_overage_cost DECIMAL(8,2) DEFAULT 0.50
```

**Exemples de plans**:
- `free`: 20 factures/mois
- `starter`: 50 factures/mois
- `pro`: 100 factures/mois
- `business`: 500 factures/mois
- `enterprise`: illimité

### 2. Table `system_settings` - Paramètres globaux

```sql
-- Configuration globale Peppol (superadmin only)
peppol_global_provider = 'recommand'
peppol_global_api_key = 'sk_live_...'
peppol_global_api_secret = 'secret_...'
peppol_global_test_mode = false
peppol_enabled = true
```

### 3. Nouvelle Table `peppol_usage`

Table de tracking détaillé de chaque transmission:

```sql
CREATE TABLE peppol_usage (
    id BIGINT PRIMARY KEY,
    company_id CHAR(36),
    invoice_id CHAR(36) NULL,
    action ENUM('send', 'receive'),
    document_type ENUM('invoice', 'credit_note', 'debit_note'),
    transmission_id VARCHAR(255),
    participant_id VARCHAR(255), -- destinataire ou expéditeur
    status ENUM('success', 'failed', 'pending'),
    error_message TEXT NULL,
    cost DECIMAL(8,4) DEFAULT 0, -- coût de la transaction
    counted_in_quota BOOLEAN DEFAULT true,
    month INT,
    year INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

**Utilité**:
- Facturation précise par entreprise
- Statistiques d'usage
- Détection d'abus
- Reporting mensuel
- Gestion des erreurs

---

## 📊 Nouveau Modèle `PeppolUsage`

Créé pour gérer le tracking:

```php
// Logger un envoi
PeppolUsage::logSend(
    companyId: $company->id,
    invoiceId: $invoice->id,
    transmissionId: 'TX123',
    participantId: '0208:BE0123456789',
    documentType: 'invoice',
    cost: 0.10
);

// Logger une réception
PeppolUsage::logReceive(
    companyId: $company->id,
    invoiceId: $invoice->id,
    participantId: '0208:BE9876543210'
);

// Logger un échec
PeppolUsage::logFailed(
    companyId: $company->id,
    action: 'send',
    errorMessage: 'Quota exceeded'
);

// Obtenir l'usage du mois
$usage = PeppolUsage::getMonthlyUsage($company->id);
// => 45

// Obtenir le coût du mois
$cost = PeppolUsage::getMonthlyCost($company->id);
// => 4.50 €
```

---

## 🔄 Modifications à Apporter

### ✅ Déjà Fait

1. ✅ Migrations créées:
   - `add_peppol_quota_system_to_companies_table`
   - `add_global_peppol_settings_to_system_settings`
   - `create_peppol_usage_table`

2. ✅ Modèle `PeppolUsage` créé avec méthodes de tracking

3. ✅ Migrations exécutées avec succès

### 🔨 À Faire

#### 1. Mettre à jour `Company.php`

Ajouter dans `$fillable`:
```php
'peppol_plan',
'peppol_quota_monthly',
'peppol_usage_current_month',
'peppol_usage_last_reset',
'peppol_overage_allowed',
'peppol_overage_cost',
```

Ajouter relation:
```php
public function peppolUsage(): HasMany
{
    return $this->hasMany(PeppolUsage::class);
}
```

Ajouter méthodes:
```php
// Vérifier si quota disponible
public function hasPeppolQuota(): bool
{
    return $this->peppol_usage_current_month < $this->peppol_quota_monthly
        || $this->peppol_plan === 'enterprise';
}

// Incrémenter usage
public function incrementPeppolUsage(): void
{
    $this->increment('peppol_usage_current_month');
}

// Réinitialiser usage (cron mensuel)
public function resetPeppolUsage(): void
{
    $this->update([
        'peppol_usage_current_month' => 0,
        'peppol_usage_last_reset' => now(),
    ]);
}
```

#### 2. Modifier `PeppolService.php`

Changer pour utiliser les credentials GLOBAUX au lieu des credentials par entreprise:

```php
protected function getGlobalApiKey(): string
{
    return DB::table('system_settings')
        ->where('key', 'peppol_global_api_key')
        ->value('value');
}

protected function getGlobalApiSecret(): string
{
    return DB::table('system_settings')
        ->where('key', 'peppol_global_api_secret')
        ->value('value');
}

public function sendInvoice(Invoice $invoice): array
{
    $company = $invoice->company;

    // Vérifier quota
    if (!$company->hasPeppolQuota()) {
        if (!$company->peppol_overage_allowed) {
            return [
                'success' => false,
                'error' => 'Quota Peppol dépassé. Veuillez upgrader votre plan.',
            ];
        }
    }

    // Utiliser API key GLOBALE
    $apiKey = $this->getGlobalApiKey();
    $apiSecret = $this->getGlobalApiSecret();

    // ... envoyer via provider ...

    // Logger l'usage
    PeppolUsage::logSend(
        companyId: $company->id,
        invoiceId: $invoice->id,
        transmissionId: $result['transmission_id'],
        participantId: $invoice->partner->peppol_id,
        cost: $this->calculateCost()
    );

    // Incrémenter quota
    $company->incrementPeppolUsage();

    return ['success' => true, ...];
}

protected function calculateCost(): float
{
    // Logique de calcul basée sur le plan Recommand.eu
    // Pro: 1000 inclus, puis €0.10/doc
    return 0.10;
}
```

#### 3. Créer Interface Superadmin

Créer `AdminPeppolController.php`:

```php
class AdminPeppolController extends Controller
{
    public function settings()
    {
        $settings = [
            'provider' => $this->getSetting('peppol_global_provider'),
            'api_key' => $this->getSetting('peppol_global_api_key'),
            'test_mode' => $this->getSetting('peppol_global_test_mode'),
            'enabled' => $this->getSetting('peppol_enabled'),
        ];

        return view('admin.peppol.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:recommand,digiteal,b2brouter',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'test_mode' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting("peppol_global_$key", $value);
        }

        return back()->with('success', 'Configuration Peppol mise à jour');
    }

    public function dashboard()
    {
        $stats = [
            'total_usage_month' => PeppolUsage::currentMonth()->successful()->count(),
            'total_cost_month' => PeppolUsage::currentMonth()->sum('cost'),
            'companies_using' => Company::where('peppol_usage_current_month', '>', 0)->count(),
            'top_users' => Company::orderBy('peppol_usage_current_month', 'desc')->take(10)->get(),
        ];

        return view('admin.peppol.dashboard', compact('stats'));
    }

    public function quotas()
    {
        $companies = Company::with('peppolUsage')
            ->where('peppol_quota_monthly', '>', 0)
            ->paginate(50);

        return view('admin.peppol.quotas', compact('companies'));
    }
}
```

#### 4. Créer Vues Superadmin

**`resources/views/admin/peppol/settings.blade.php`**:
- Formulaire pour configurer l'API key globale
- Choix du provider
- Test de connexion

**`resources/views/admin/peppol/dashboard.blade.php`**:
- Graphiques d'usage global
- Top 10 entreprises utilisatrices
- Coût total du mois
- Revenus générés

**`resources/views/admin/peppol/quotas.blade.php`**:
- Liste de toutes les entreprises avec quotas
- Possibilité d'ajuster les quotas
- Voir l'usage en temps réel

#### 5. Créer Commande Artisan (Cron mensuel)

```php
php artisan make:command ResetPeppolQuotas

class ResetPeppolQuotas extends Command
{
    protected $signature = 'peppol:reset-quotas';

    public function handle()
    {
        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $company->resetPeppolUsage();
            }
        });

        $this->info('Quotas Peppol réinitialisés pour toutes les entreprises');
    }
}
```

Ajouter dans `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Réinitialiser les quotas le 1er de chaque mois à 00:00
    $schedule->command('peppol:reset-quotas')
        ->monthlyOn(1, '00:00');
}
```

---

## 📈 Interface Tenant (Entreprise)

### Vue mise à jour dans `settings/peppol.blade.php`

Afficher:
```
┌─────────────────────────────────────┐
│   Configuration Peppol              │
├─────────────────────────────────────┤
│                                     │
│  Plan actuel: Pro                   │
│  Quota: 45 / 100 factures ce mois   │
│                                     │
│  ████████████░░░░░░░░  45%         │
│                                     │
│  Participant ID: 0208:BE0123456789  │
│  Mode test: Désactivé               │
│                                     │
│  [Upgrader le plan]  [Historique]   │
│                                     │
└─────────────────────────────────────┘
```

**IMPORTANT**: Les entreprises NE VOIENT PLUS:
- ❌ API Key
- ❌ API Secret
- ❌ Provider selection

Elles voient uniquement:
- ✅ Leur plan
- ✅ Leur quota et usage
- ✅ Leur Participant ID
- ✅ Possibilité d'upgrader

---

## 🎯 Workflow Complet

### Envoi d'une facture

```
1. Entreprise A clique "Envoyer via Peppol"
   └─> Controller vérifie quota (45/100) ✓

2. PeppolService utilise API key GLOBALE
   └─> Appel Recommand.eu avec credentials superadmin

3. Facture envoyée avec succès
   └─> PeppolUsage::logSend(...)
   └─> Company->incrementPeppolUsage() (45 → 46)

4. Si quota dépassé (100/100):
   └─> Erreur "Quota dépassé, upgrader le plan"
   └─> OU facturation overage si activé
```

### Réception d'une facture (Webhook)

```
1. Webhook reçoit facture de fournisseur
   └─> Parse UBL, trouve company via participant ID

2. Crée facture d'achat automatiquement
   └─> PeppolUsage::logReceive(...)
   └─> Company->incrementPeppolUsage()

3. Si quota dépassé:
   └─> Notification au superadmin
   └─> Suggestion d'upgrade automatique
```

---

## 💡 Recommandations

### Phase 1 - MVP (Semaine 1)
1. ✅ Créer migrations (fait)
2. ✅ Créer modèle PeppolUsage (fait)
3. 🔨 Mettre à jour Company model
4. 🔨 Modifier PeppolService pour API centralisée
5. 🔨 Créer interface superadmin basique

### Phase 2 - Production (Semaine 2)
6. Créer dashboard analytics superadmin
7. Créer système de facturation automatique
8. Implémenter upgrade de plan en self-service
9. Créer rapports d'usage pour clients

### Phase 3 - Optimisation (Semaine 3+)
10. Ajouter alertes quota (80%, 90%, 100%)
11. Implémenter retry automatique en cas d'échec
12. Créer API webhooks pour notifications
13. Ajouter métriques de performance

---

## 🔐 Sécurité

### API Key Globale
- Stockée dans `system_settings` (encrypted)
- Accessible uniquement par superadmin
- Logs d'accès dans `audit_logs`
- Rotation possible via interface admin

### Isolation Tenant
- Chaque entreprise ne voit que son usage
- Quotas stricts par entreprise
- Impossible de voir les données d'autres tenants
- Participant ID unique par entreprise

---

## 📞 Support

Si vous avez des questions sur cette nouvelle architecture, consultez:
- [PEPPOL_INTEGRATION.md](./PEPPOL_INTEGRATION.md) - Documentation technique Peppol
- [TEST_REPORT.md](./TEST_REPORT.md) - Rapport de tests

---

## ✅ Checklist Complète

- [x] Migrations créées
- [x] Modèle PeppolUsage créé
- [x] Migrations exécutées
- [ ] Company model mis à jour
- [ ] PeppolService modifié pour API centralisée
- [ ] AdminPeppolController créé
- [ ] Vues superadmin créées
- [ ] Vues tenant mises à jour
- [ ] Commande reset-quotas créée
- [ ] Cron job configuré
- [ ] Tests unitaires écrits
- [ ] Documentation API complétée

---

*Architecture créée le 2025-12-25 pour ComptaBE SaaS*
