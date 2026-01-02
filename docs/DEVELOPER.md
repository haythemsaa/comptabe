# ComptaBE - Documentation Technique

## Architecture

ComptaBE est une application Laravel 11 multi-tenant utilisant :

- **Backend** : PHP 8.2+, Laravel 11
- **Frontend** : Blade, Tailwind CSS, Alpine.js
- **Base de données** : MySQL 8.0+ / PostgreSQL 15+
- **Cache** : Redis (recommandé) ou File
- **Queue** : Database ou Redis

## Structure du projet

```
app/
├── Console/Commands/       # Commandes Artisan personnalisées
├── Http/
│   ├── Controllers/       # Contrôleurs (API et Web)
│   ├── Middleware/        # Middlewares personnalisés
│   └── Requests/          # Form Requests pour validation
├── Models/                # Modèles Eloquent (56 modèles)
├── Notifications/         # Notifications (Email, etc.)
├── Observers/             # Observers pour les événements modèles
├── Policies/              # Politiques d'autorisation
├── Services/              # Services métier
│   ├── AI/               # Services d'intelligence artificielle
│   ├── OpenBanking/      # Services PSD2
│   └── Peppol/           # Services Peppol et e-Reporting
└── Traits/               # Traits réutilisables

database/
├── factories/            # Factories pour les tests
├── migrations/           # Migrations de base de données
└── seeders/             # Seeders pour données initiales

resources/
├── views/               # Vues Blade (154+ vues)
│   ├── components/      # Composants réutilisables
│   └── layouts/         # Layouts de base
└── lang/               # Fichiers de traduction (FR, NL, EN)

tests/
├── Feature/            # Tests d'intégration
└── Unit/              # Tests unitaires
```

## Services principaux

### PeppolService

Gère l'envoi et la réception de factures via Peppol.

```php
use App\Services\Peppol\PeppolService;

$service = new PeppolService();

// Envoyer une facture
$transmission = $service->sendInvoice($invoice);

// Générer UBL sans envoyer
$ublXml = $service->generateUBL($invoice);

// Vérifier le statut
$status = $service->checkStatus($transmission);
```

### UblParserService

Parse les documents UBL reçus.

```php
use App\Services\Peppol\UblParserService;

$parser = new UblParserService();

// Valider un document UBL
$result = $parser->validate($ublContent);

// Parser et créer une facture
$invoice = $parser->parseAndCreateInvoice($company, $ublContent);
```

### EReportingService

Gère la soumission au SPF Finances (modèle 5 coins).

```php
use App\Services\Peppol\EReportingService;

$service = new EReportingService($company);

// Soumettre une facture
$submission = $service->submitInvoice($invoice);

// Vérifier si requis
$required = $service->isEReportingRequired($invoice);

// Générer un rapport de conformité
$report = $service->generateComplianceReport($startDate, $endDate);
```

### PeppolDirectoryService

Interroge le répertoire Peppol.

```php
use App\Services\Peppol\PeppolDirectoryService;

$directory = new PeppolDirectoryService();

// Lookup par identifiant
$result = $directory->lookup('0123456789', '0208');

// Vérifier un numéro TVA belge
$result = $directory->verifyBelgianVat('BE0123456789');

// Recherche par nom
$results = $directory->searchByName('Company Name', 'BE');
```

### CacheService

Service de cache centralisé tenant-aware.

```php
use App\Services\CacheService;

$cache = new CacheService();

// Cache avec contexte tenant
$data = $cache->remember(
    CacheService::PREFIX_INVOICE,
    'stats',
    CacheService::TTL_MEDIUM,
    fn() => Invoice::count()
);

// Invalidation
$cache->invalidateInvoice();
```

## Multi-tenancy

Le système utilise un modèle multi-tenant basé sur la session.

### Middleware Tenant

```php
// app/Http/Middleware/TenantMiddleware.php
// Vérifie que l'utilisateur a accès à la company courante
```

### Scopes automatiques

```php
// Les modèles utilisant le trait BelongsToCompany
// sont automatiquement filtrés par company_id
Invoice::all(); // Retourne seulement les factures de la company courante
```

## Tests

### Exécuter les tests

```bash
# Tous les tests
php artisan test

# Tests unitaires
php artisan test --testsuite=Unit

# Tests d'intégration
php artisan test --testsuite=Feature

# Avec couverture
php artisan test --coverage
```

### Structure des tests

```php
// tests/Feature/InvitationTest.php
class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup avec factory
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
    }

    public function test_owner_can_send_invitation(): void
    {
        // ...
    }
}
```

## Commandes Artisan

### Commandes personnalisées

```bash
# Réchauffer le cache
php artisan cache:warm --all

# Synchroniser les taux de change
php artisan exchange-rates:sync

# Nettoyer les anciennes données
php artisan cleanup:old-data --days=365

# Générer les déclarations TVA
php artisan vat:generate --period=quarterly
```

## API

### Authentification

L'API utilise Laravel Sanctum pour l'authentification.

```bash
POST /api/v1/login
{
    "email": "user@example.com",
    "password": "password"
}
```

### Endpoints principaux

```
GET    /api/v1/invoices          # Liste des factures
POST   /api/v1/invoices          # Créer une facture
GET    /api/v1/invoices/{id}     # Détails d'une facture
PUT    /api/v1/invoices/{id}     # Modifier une facture
DELETE /api/v1/invoices/{id}     # Supprimer une facture

GET    /api/v1/partners          # Liste des partenaires
POST   /api/v1/partners          # Créer un partenaire
```

## Webhooks Peppol

### Configuration

Les webhooks Peppol sont reçus sur :
```
POST /api/peppol/webhook
```

### Payload types

- `document.received` : Document reçu
- `document.delivered` : Livraison confirmée
- `document.failed` : Échec de livraison

## Optimisation des performances

### Indexes de base de données

```bash
php artisan migrate # Inclut les indexes optimisés
```

### Cache

```bash
# En production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Query Logging (développement)

```php
// .env
APP_DEBUG=true
QUERY_LOGGING=true
```

## Sécurité

### Validation CSRF

Toutes les routes web sont protégées par CSRF.

### Autorisation

Les policies contrôlent l'accès aux ressources :

```php
// app/Policies/InvoicePolicy.php
public function update(User $user, Invoice $invoice): bool
{
    return $user->companies->contains($invoice->company_id);
}
```

### Audit Log

Toutes les actions sensibles sont loguées dans `audit_logs`.

## Déploiement

### Variables d'environnement requises

```env
APP_KEY=
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

PEPPOL_API_URL=
PEPPOL_API_KEY=
PEPPOL_TEST_MODE=true

EREPORTING_API_URL=
EREPORTING_API_KEY=
```

### Checklist de déploiement

1. `composer install --no-dev`
2. `php artisan migrate --force`
3. `php artisan config:cache`
4. `php artisan route:cache`
5. `php artisan view:cache`
6. `php artisan cache:warm --all`
7. Configurer les queues (supervisor)
8. Configurer les tâches cron

### Cron

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

*Documentation technique - ComptaBE v1.0.0*
