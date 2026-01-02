# Documentation Technique - Assistant AI ComptaBE

## Architecture

### Vue d'ensemble

L'assistant AI utilise une architecture en couches avec séparation stricte des responsabilités:

```
┌─────────────────────────────────────────┐
│         Frontend (Alpine.js)            │
│    Chat Widget + Message Components    │
└──────────────┬──────────────────────────┘
               │ HTTP/JSON
┌──────────────▼──────────────────────────┐
│         ChatController (API)            │
│    Routes: /api/chat/*                  │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│          ChatService                    │
│  - Orchestration conversations          │
│  - Gestion historique                   │
│  - Coordination outils                  │
└──────────┬─────────────┬────────────────┘
           │             │
      ┌────▼────┐   ┌────▼────────┐
      │ Claude  │   │    Tool     │
      │   AI    │   │  Registry   │
      │ Service │   │             │
      └─────────┘   └──────┬──────┘
                           │
                      ┌────▼──────┐
                      │   Tool    │
                      │ Executor  │
                      └─────┬─────┘
                            │
                ┌───────────┴───────────┐
                │                       │
           ┌────▼────┐            ┌────▼────┐
           │ Tenant  │            │Superadmin│
           │  Tools  │            │  Tools   │
           └─────────┘            └──────────┘
```

### Composants principaux

#### 1. ClaudeAIService

**Fichier:** `app/Services/AI/Chat/ClaudeAIService.php`

**Responsabilité:** Communication directe avec l'API Claude d'Anthropic.

**Méthodes clés:**

```php
public function sendMessage(array $messages, array $tools = []): array
```
- Envoie messages + outils disponibles à Claude
- Gère headers API (`x-api-key`, `anthropic-version`)
- Parse réponse (text ou tool_use)
- Retourne: `['role', 'content', 'tool_calls', 'usage']`

```php
public function formatToolDefinitions(array $tools): array
```
- Convertit outils internes en format Claude (JSON Schema)
- Input: Array d'objets AbstractTool
- Output: Array au format Anthropic Tool Definition

```php
public function calculateCost(int $inputTokens, int $outputTokens): float
```
- Calcule coût en $ avec tarifs config
- Input tokens: $3/million
- Output tokens: $15/million

**Exemple d'utilisation:**

```php
$claudeService = app(ClaudeAIService::class);

$messages = [
    ['role' => 'user', 'content' => 'Montre-moi mes factures impayées']
];

$tools = [
    app(ReadInvoicesTool::class)
];

$response = $claudeService->sendMessage($messages, $tools);
```

#### 2. ChatService

**Fichier:** `app/Services/AI/Chat/ChatService.php`

**Responsabilité:** Orchestration des conversations, gestion de l'historique.

**Méthodes clés:**

```php
public function startConversation(User $user, ?Company $company): ChatConversation
```
- Crée nouvelle conversation avec contexte
- Détermine `context_type` (tenant/superadmin)
- Initialise metadata

```php
public function sendMessage(ChatConversation $conversation, string $message): ChatMessage
```
- **Flow complet:**
  1. Charge historique (derniers 20 messages)
  2. Obtient outils autorisés via ToolRegistry
  3. Envoie à Claude via ClaudeAIService
  4. Si tool_use: exécute via ToolExecutor
  5. Renvoie résultats à Claude
  6. Stocke message final
  7. Retourne réponse

```php
public function getConversationHistory(ChatConversation $conv, int $limit = 20): array
```
- Charge messages pour contexte Claude
- Limite configurable (`config('ai.chat.context_window_messages')`)

**Exemple d'utilisation:**

```php
$chatService = app(ChatService::class);

$conversation = $chatService->startConversation(auth()->user(), $company);
$response = $chatService->sendMessage($conversation, 'Crée une facture pour ABC');
```

#### 3. ToolRegistry

**Fichier:** `app/Services/AI/Chat/ToolRegistry.php`

**Responsabilité:** Enregistrement et gestion des permissions des outils.

**Méthodes clés:**

```php
public function getToolsForContext(User $user, ?Company $company): array
```
- Retourne outils autorisés selon rôle
- Vérifie `isSuperadmin()` vs tenant
- Filtre avec `config('ai.tools')`

```php
public function registerTool(string $name, AbstractTool $tool): void
```
- Enregistre outil dans le registre
- Utilisé au boot de l'application

```php
public function getTool(string $name): AbstractTool
```
- Récupère instance d'outil par nom
- Throw exception si non trouvé

**Exemple d'utilisation:**

```php
$registry = app(ToolRegistry::class);

// Enregistrer un outil
$registry->registerTool('create_invoice', new CreateInvoiceTool());

// Obtenir outils pour utilisateur
$tools = $registry->getToolsForContext(auth()->user(), $company);
```

#### 4. ToolExecutor

**Fichier:** `app/Services/AI/Chat/ToolExecutor.php`

**Responsabilité:** Exécution sécurisée des outils (couche critique de sécurité).

**Méthodes clés:**

```php
public function execute(AbstractTool $tool, array $input, Context $context): array
```
- **Vérifications de sécurité:**
  1. User authentifié
  2. Isolation tenant (`hasAccessToCompany`)
  3. Outil autorisé pour rôle
  4. Validation input vs JSON Schema
  5. Demande confirmation si nécessaire
  6. Audit logging
- Retourne résultat ou erreur

```php
public function validateInput(array $input, array $schema): void
```
- Valide paramètres contre JSON Schema
- Throw ValidationException si invalide

```php
public function requestConfirmation(AbstractTool $tool, array $input): array
```
- Retourne demande de confirmation
- Pour actions dangereuses (delete, bulk operations)

**Exemple d'utilisation:**

```php
$executor = app(ToolExecutor::class);

$tool = new CreateInvoiceTool();
$input = [
    'partner_id' => '123-456',
    'invoice_date' => '2024-12-30',
    'line_items' => [...]
];
$context = new Context($user, $company);

try {
    $result = $executor->execute($tool, $input, $context);
    // $result = ['success' => true, 'invoice_id' => '...', ...]
} catch (\Exception $e) {
    // Gestion erreur
}
```

## Création d'un nouvel outil

### Étape 1: Créer la classe de l'outil

Tous les outils héritent de `AbstractTool` et doivent implémenter 4 méthodes abstraites:

```php
<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Context;

class MonNouvelOutilTool extends AbstractTool
{
    /**
     * Nom unique de l'outil (snake_case)
     */
    public function getName(): string
    {
        return 'mon_nouvel_outil';
    }

    /**
     * Description pour Claude (ce que fait l'outil)
     */
    public function getDescription(): string
    {
        return 'Effectue une action spécifique pour l\'utilisateur';
    }

    /**
     * JSON Schema des paramètres requis
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'Description du paramètre 1',
                ],
                'param2' => [
                    'type' => 'integer',
                    'description' => 'Description du paramètre 2',
                    'minimum' => 1,
                ],
                'optional_param' => [
                    'type' => 'boolean',
                    'description' => 'Paramètre optionnel',
                ],
            ],
            'required' => ['param1', 'param2'],
        ];
    }

    /**
     * Logique métier de l'outil
     */
    public function execute(array $input, Context $context): array
    {
        // 1. Vérifier les permissions
        $this->checkPermission($context->user, 'create', \App\Models\MonModel::class);

        // 2. Isolation tenant automatique (via $context->company)
        $company = $context->company;

        // 3. Logique métier
        $result = \App\Models\MonModel::create([
            'company_id' => $company->id,
            'param1' => $input['param1'],
            'param2' => $input['param2'],
        ]);

        // 4. Retourner résultat structuré
        return [
            'success' => true,
            'message' => 'Action effectuée avec succès',
            'data' => [
                'id' => $result->id,
                'created_at' => $result->created_at->toIso8601String(),
            ],
        ];
    }

    /**
     * Optionnel: Indiquer si confirmation requise
     */
    public function requiresConfirmation(): bool
    {
        return false; // true pour actions dangereuses
    }
}
```

### Étape 2: Enregistrer l'outil

Ajoutez l'outil dans le fichier de configuration `config/ai.php`:

```php
'tools' => [
    'tenant' => [
        'read_invoices',
        'create_invoice',
        // ... outils existants
        'mon_nouvel_outil', // ← Ajoutez ici
    ],
],
```

### Étape 3: Enregistrer dans le ToolRegistry

Modifiez le service provider `app/Providers/AppServiceProvider.php`:

```php
use App\Services\AI\Chat\Tools\Tenant\MonNouvelOutilTool;

public function boot()
{
    $registry = app(\App\Services\AI\Chat\ToolRegistry::class);

    // ... enregistrements existants

    $registry->registerTool('mon_nouvel_outil', new MonNouvelOutilTool());
}
```

### Étape 4: Créer une Policy (optionnel)

Si votre outil manipule un modèle spécifique, créez une Policy:

```bash
php artisan make:policy MonModelPolicy --model=MonModel
```

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MonModel;

class MonModelPolicy
{
    public function create(User $user): bool
    {
        // Vérifier que l'utilisateur a le droit de créer
        return $user->hasRole('admin') || $user->hasRole('owner');
    }

    public function update(User $user, MonModel $model): bool
    {
        // Vérifier l'isolation tenant
        return $user->hasAccessToCompany($model->company_id);
    }
}
```

Enregistrez la Policy dans `AuthServiceProvider`:

```php
protected $policies = [
    \App\Models\MonModel::class => \App\Policies\MonModelPolicy::class,
];
```

### Étape 5: Tester l'outil

Créez un test unitaire:

```php
<?php

namespace Tests\Feature\AI\Tools;

use Tests\TestCase;
use App\Services\AI\Chat\Tools\Tenant\MonNouvelOutilTool;
use App\Services\AI\Chat\Context;

class MonNouvelOutilToolTest extends TestCase
{
    public function test_execute_creates_resource()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company, ['role' => 'owner']);

        $tool = new MonNouvelOutilTool();
        $input = [
            'param1' => 'test',
            'param2' => 42,
        ];
        $context = new Context($user, $company);

        $result = $tool->execute($input, $context);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('mon_models', [
            'company_id' => $company->id,
            'param1' => 'test',
        ]);
    }

    public function test_validates_input_schema()
    {
        $tool = new MonNouvelOutilTool();

        $this->expectException(\InvalidArgumentException::class);

        // Paramètre requis manquant
        $tool->execute(['param1' => 'test'], new Context($user, $company));
    }
}
```

## Bonnes pratiques

### Sécurité

1. **Toujours vérifier l'isolation tenant:**
   ```php
   if (!$context->user->hasAccessToCompany($context->company->id)) {
       throw new \Exception('Unauthorized');
   }
   ```

2. **Utiliser les Policies Laravel:**
   ```php
   $this->checkPermission($context->user, 'create', Invoice::class);
   ```

3. **Valider les inputs:**
   - Définir un JSON Schema complet
   - Valider les formats (email, UUID, dates)
   - Vérifier les bornes (min/max)

4. **Ne jamais faire confiance aux IDs:**
   ```php
   // ❌ Mauvais: Récupère directement par ID
   $invoice = Invoice::find($input['invoice_id']);

   // ✅ Bon: Vérifie l'isolation tenant
   $invoice = $context->company->invoices()->findOrFail($input['invoice_id']);
   ```

5. **Actions dangereuses = Confirmation:**
   ```php
   public function requiresConfirmation(): bool
   {
       return true; // Pour delete, bulk operations, exports
   }
   ```

### Performance

1. **Eager loading:**
   ```php
   $invoices = $company->invoices()
       ->with(['partner', 'lines'])
       ->get();
   ```

2. **Pagination:**
   ```php
   $limit = min($input['limit'] ?? 10, 100); // Max 100
   $invoices = $company->invoices()->paginate($limit);
   ```

3. **Queue pour tâches longues:**
   ```php
   dispatch(new GenerateReportJob($company, $parameters));

   return [
       'success' => true,
       'message' => 'Rapport en cours de génération, vous recevrez un email.',
       'job_id' => $job->id,
   ];
   ```

### Clarté pour Claude

1. **Descriptions précises:**
   ```php
   public function getDescription(): string
   {
       return 'Crée une nouvelle facture avec lignes pour un client. ' .
              'Calcule automatiquement les totaux HT, TVA et TTC. ' .
              'Génère un numéro de facture séquentiel.';
   }
   ```

2. **Exemples dans les descriptions de paramètres:**
   ```php
   'invoice_date' => [
       'type' => 'string',
       'format' => 'date',
       'description' => 'Date de la facture au format YYYY-MM-DD (ex: 2024-12-30)',
   ],
   ```

3. **Messages de retour informatifs:**
   ```php
   return [
       'success' => true,
       'message' => "Facture {$invoice->invoice_number} créée avec succès pour {$partner->name}. Total: {$invoice->total_incl_vat}€",
       'invoice_id' => $invoice->id,
       'invoice_number' => $invoice->invoice_number,
   ];
   ```

## Debugging

### Activer les logs détaillés

Dans `ClaudeAIService`:

```php
\Log::info('Claude API Request', [
    'messages' => $messages,
    'tools' => array_map(fn($t) => $t->getName(), $tools),
]);

\Log::info('Claude API Response', [
    'response' => $response,
    'tokens' => $usage,
]);
```

### Tester un outil manuellement

```bash
php artisan tinker
```

```php
$user = User::first();
$company = Company::first();
$tool = new \App\Services\AI\Chat\Tools\Tenant\ReadInvoicesTool();
$context = new \App\Services\AI\Chat\Context($user, $company);

$result = $tool->execute(['status' => 'unpaid'], $context);
dd($result);
```

### Vérifier la définition d'outil Claude

```bash
php artisan tinker
```

```php
$tool = new \App\Services\AI\Chat\Tools\Tenant\CreateInvoiceTool();
$service = app(\App\Services\AI\Chat\ClaudeAIService::class);

$definition = $service->formatToolDefinitions([$tool]);
dd($definition);
```

## Limites et considérations

### Limites API Claude

- **Rate limiting:** ~5 requests/seconde
- **Max tokens:** 200,000 tokens (input + output combinés)
- **Timeout:** 60 secondes par requête

### Fenêtre de contexte

- Par défaut: 20 derniers messages
- Configurable: `config('ai.chat.context_window_messages')`
- Si conversation très longue, messages anciens sont omis

### Coûts

- Sonnet: $3 input / $15 output par million tokens
- Conversation moyenne: ~$0.05
- Surveiller avec dashboard des coûts

## Roadmap

### Outils à implémenter

- [ ] Gestion des produits (catalogue)
- [ ] Gestion des projets
- [ ] Time tracking pour fiduciaires
- [ ] Génération de documents (contrats, devis PDF)
- [ ] Intégration bancaire avancée
- [ ] Déclarations fiscales complètes
- [ ] Gestion RH avancée (congés, absences)

### Améliorations système

- [ ] Streaming des réponses (Server-Sent Events)
- [ ] Multi-langue (détection automatique)
- [ ] Suggestions proactives (based on page context)
- [ ] Voice input/output
- [ ] Recherche full-text dans historique
- [ ] Export conversations en PDF
- [ ] Analytics avancées (usage patterns, success rate)

---

**Version:** 1.0
**Dernière mise à jour:** Décembre 2024
