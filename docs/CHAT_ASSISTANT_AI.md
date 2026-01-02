# Assistant Chat AI - Documentation Complète

## Vue d'ensemble

L'Assistant Chat AI de ComptaBE est alimenté par **Claude 3.5 Sonnet** et peut exécuter des actions réelles via 30+ outils intégrés. L'assistant peut créer factures, inviter users, réconcilier transactions, générer rapports et bien plus.

**Capacités**:
- 💬 Conversation naturelle en français
- 🔧 30+ outils d'action (Tool Use API)
- 🎯 Context-aware (voit la page actuelle)
- 🔒 Isolation tenant stricte
- 💰 Tracking coûts API
- ⚡ Temps réel via Alpine.js

---

## Architecture

### Stack Technique

- **LLM**: Claude 3.5 Sonnet (Anthropic)
- **Tool Use**: Claude Tool Use API
- **Frontend**: Alpine.js + Marked.js (Markdown)
- **Backend**: Laravel 11
- **Database**: MySQL (conversations, messages, tool_executions)
- **Cache**: Database/Redis

### Flow de Conversation

```
User → Widget → ChatController → ChatService
                                     ↓
                          Load history (20 messages)
                                     ↓
                          Get allowed tools (ToolRegistry)
                                     ↓
                          ClaudeAIService → Claude API
                                     ↓
                     Response contains tool_use?
                           ↙             ↘
                         YES              NO
                          ↓                ↓
                    ToolExecutor      Return text
                          ↓
                    Execute tools
                          ↓
                    Send results back to Claude
                          ↓
                    Final response → User
```

---

## Utilisation

### Interface Utilisateur

#### 1. Accès au Chat

Le widget chat est **toujours disponible** en bas à droite de toutes les pages (bouton flottant).

Cliquez sur le bouton pour ouvrir le panel.

#### 2. Exemples de Commandes

**Facturation**:
```
"Crée une facture pour Acme SA de 1250€ HT"
"Montre-moi toutes mes factures impayées"
"Envoie la facture FAC-2025-001 par email"
"Convertis le devis DEV-2025-015 en facture"
```

**Partenaires**:
```
"Trouve tous les clients en France"
"Ajoute un nouveau fournisseur: Dupont SPRL, TVA BE0123456789"
"Vérifie le numéro TVA BE0987654321"
```

**Paiements**:
```
"Enregistre un paiement de 500€ pour la facture FAC-2025-010"
"Réconcilie la transaction bancaire TX-2025-123 avec ma facture"
```

**TVA**:
```
"Génère ma déclaration TVA pour Q1 2025"
"Montre-moi le solde TVA pour ce trimestre"
```

**Gestion**:
```
"Invite jean.dupont@example.com comme comptable"
"Crée un modèle de facture récurrente mensuelle"
"Configure un rappel automatique à J+15"
```

**Rapports**:
```
"Exporte mes données comptables pour janvier-mars"
"Génère un rapport de trésorerie"
```

#### 3. Confirmation d'Actions

Certaines actions **dangereuses** (suppression, envoi Peppol) requièrent confirmation.

Un bouton "Confirmer" apparaît dans le chat avant exécution.

---

## API

### Endpoints

#### Envoyer un message

```http
POST /api/chat/send
Content-Type: application/json
Authorization: Bearer {token}

{
  "conversation_id": "optional-uuid",
  "message": "Crée une facture pour Acme SA"
}
```

**Réponse** (sans tool use):
```json
{
  "success": true,
  "conversation_id": "uuid",
  "response": "Je vais créer une facture pour Acme SA. Quel est le montant ?",
  "timestamp": "2025-12-26T10:30:00Z"
}
```

**Réponse** (avec tool use):
```json
{
  "success": true,
  "conversation_id": "uuid",
  "response": "J'ai créé la facture FAC-2025-042 pour Acme SA d'un montant de 1 250,00 € HT (1 512,50 € TTC).",
  "tool_calls": [
    {
      "name": "create_invoice",
      "status": "success",
      "execution_id": "uuid",
      "output": {
        "invoice_id": "uuid",
        "invoice_number": "FAC-2025-042",
        "total": 1512.50
      }
    }
  ],
  "timestamp": "2025-12-26T10:30:15Z",
  "cost": 0.002
}
```

#### Liste des conversations

```http
GET /api/chat/conversations
```

**Réponse**:
```json
{
  "conversations": [
    {
      "id": "uuid",
      "title": "Création facture Acme SA",
      "last_message_at": "2025-12-26T10:30:00Z",
      "message_count": 5
    }
  ]
}
```

#### Détails conversation

```http
GET /api/chat/conversations/{id}
```

**Réponse**:
```json
{
  "conversation": {
    "id": "uuid",
    "title": "...",
    "created_at": "..."
  },
  "messages": [
    {
      "id": "uuid",
      "role": "user",
      "content": "Crée une facture...",
      "created_at": "..."
    },
    {
      "id": "uuid",
      "role": "assistant",
      "content": "J'ai créé la facture...",
      "tool_calls": [...],
      "created_at": "..."
    }
  ]
}
```

#### Confirmer outil

```http
POST /api/chat/tools/{execution_id}/confirm
```

---

## Outils Disponibles

### Pour Tous les Tenants (30 outils)

#### Factures (9 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `read_invoices` | Liste factures avec filtres | "Montre factures impayées" |
| `create_invoice` | Crée nouvelle facture | "Facture 1250€ pour Acme" |
| `update_invoice` | Modifie facture existante | "Change le montant à 1500€" |
| `delete_invoice` | Supprime facture (draft) | "Supprime FAC-2025-010" |
| `send_invoice_email` | Envoie par email | "Envoie facture par email" |
| `send_via_peppol` | Envoie via Peppol | "Envoie via Peppol" |
| `create_quote` | Crée devis | "Crée devis 2000€" |
| `convert_quote_to_invoice` | Convertit devis | "Convertis devis en facture" |
| `create_invoice_template` | Modèle facture | "Crée modèle mensuel" |

#### Partenaires (3 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `search_partners` | Recherche clients/fournisseurs | "Trouve clients en France" |
| `create_partner` | Ajoute partenaire | "Ajoute Dupont SPRL" |

#### Paiements (2 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `record_payment` | Enregistre paiement | "Paiement 500€ pour FAC-001" |
| `reconcile_bank_transaction` | Réconcilie banque | "Réconcilie TX-123" |

#### TVA (1 outil)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `generate_vat_declaration` | Génère déclaration | "Génère TVA Q1 2025" |

#### Gestion (3 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `invite_user` | Invite utilisateur | "Invite jean@example.com" |
| `create_recurring_invoice` | Facture récurrente | "Crée facture mensuelle" |
| `configure_invoice_reminders` | Configure rappels | "Rappel J+15" |

#### Compta & Export (2 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `create_expense` | Enregistre dépense | "Dépense 150€ fournitures" |
| `export_accounting_data` | Exporte compta | "Exporte janvier-mars" |

#### Paie (2 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `create_employee` | Ajoute employé | "Ajoute Marie Durand" |
| `generate_payslip` | Génère fiche paie | "Fiche paie décembre" |

### Pour Fiduciaires (5 outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `get_all_clients_data` | Vue tous clients | "Montre tous mes clients" |
| `bulk_export_accounting` | Export multiple | "Exporte compta 10 clients" |
| `generate_multi_client_report` | Rapport comparatif | "Compare performance clients" |
| `assign_mandate_task` | Assigne tâche | "Assigne révision à Paul" |
| `get_client_health_score` | Santé financière | "Score santé client Acme" |

### Pour Superadmins (1+ outils)

| Outil | Description | Exemple |
|-------|-------------|---------|
| `create_demo_account` | Compte démo | "Crée démo pour prospect" |

---

## Configuration

### Variables d'environnement

Ajoutez dans `.env`:

```env
# Claude AI Configuration
CLAUDE_API_KEY=sk-ant-api03-xxxx...
CLAUDE_MODEL=claude-3-5-sonnet-20241022
CLAUDE_MAX_TOKENS=4096
CLAUDE_TEMPERATURE=0.7
```

**Obtenir une clé API**:
1. Créer compte sur [console.anthropic.com](https://console.anthropic.com)
2. Générer API Key
3. Copier dans `.env`

### Coûts API

**Tarification Claude 3.5 Sonnet** (Décembre 2025):
- Input: **$3** / million tokens
- Output: **$15** / million tokens

**Estimation**:
- Conversation moyenne: ~2000 tokens (input + output)
- Coût par conversation: ~**$0.03** (3 centimes)
- 100 conversations/jour: **$3/jour** = **$90/mois**

**Optimisations**:
- Context window limité à 20 messages
- Cache conversations (évite recharges)
- Tracking coûts en DB

---

## Développement - Ajouter un Outil

### 1. Créer la Classe Outil

Fichier: `app/Services/AI/Chat/Tools/Tenant/MyNewTool.php`

```php
<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Services\AI\Chat\Tools\AbstractTool;

class MyNewTool extends AbstractTool
{
    public function getName(): string
    {
        return 'my_new_tool';
    }

    public function getDescription(): string
    {
        return 'Description claire de ce que fait l\'outil';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'Description du paramètre',
                ],
                'param2' => [
                    'type' => 'number',
                    'description' => 'Montant en euros',
                ],
            ],
            'required' => ['param1'],
        ];
    }

    public function execute(array $input, object $context): array
    {
        // Vérifier permission
        $this->checkPermission($context->user, 'create', \App\Models\MyModel::class);

        // Vérifier isolation tenant
        if ($context->company && $someModel->company_id !== $context->company->id) {
            throw new \Exception('Non autorisé');
        }

        // Exécuter action
        $result = // ... logique métier

        // Retourner résultat
        return [
            'success' => true,
            'message' => 'Action exécutée avec succès',
            'data' => $result,
        ];
    }

    // Optionnel: requiert confirmation
    public function requiresConfirmation(): bool
    {
        return true; // Pour actions dangereuses
    }
}
```

### 2. Enregistrer dans ToolRegistry

Fichier: `app/Services/AI/Chat/ToolRegistry.php`

```php
public function __construct()
{
    // ... autres outils

    $this->register('my_new_tool', new MyNewTool());
}
```

### 3. Ajouter aux Permissions

Fichier: `config/ai.php`

```php
'tools' => [
    'tenant' => [
        // ... autres
        'my_new_tool',
    ],
],
```

### 4. Tester

```php
// Dans ChatService
$result = $this->toolExecutor->execute(
    $tool,
    ['param1' => 'test'],
    $context
);
```

---

## Sécurité

### 1. Isolation Tenant

**Automatique via scope global**:
```php
// Dans modèles
protected static function booted()
{
    static::addGlobalScope(new TenantScope());
}
```

**Vérification explicite**:
```php
if ($model->company_id !== $context->company->id) {
    abort(403);
}
```

### 2. Permissions

Chaque outil vérifie les permissions via Laravel Policies:

```php
$this->checkPermission($user, 'create', Invoice::class);
```

### 3. Validation Input

JSON Schema validation automatique avant exécution:

```php
$this->toolExecutor->validateInput($input, $tool->getInputSchema());
```

### 4. Audit Logging

Chaque exécution d'outil est loggée:

```php
activity()
    ->performedOn($model)
    ->causedBy($user)
    ->withProperties(['tool' => 'create_invoice', 'input' => $input])
    ->log('chat_tool_executed');
```

### 5. Rate Limiting

API chat rate-limitée:
```php
Route::middleware('throttle:api')->post('/chat/send');
```

### 6. Confirmation Actions Dangereuses

Outils dangereux (delete, send_peppol) requièrent confirmation utilisateur avant exécution.

---

## Base de Données

### Tables

#### `chat_conversations`

| Colonne | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| user_id | UUID | User propriétaire |
| company_id | UUID | Company context (null pour superadmin) |
| title | STRING | Auto-généré du 1er message |
| context_type | ENUM | 'tenant' ou 'superadmin' |
| metadata | JSON | Contexte page, filtres |
| is_archived | BOOLEAN | Archivée ? |
| last_message_at | TIMESTAMP | Dernier message |

#### `chat_messages`

| Colonne | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| conversation_id | UUID | Foreign key |
| role | ENUM | 'user', 'assistant', 'system' |
| content | TEXT | Message texte |
| tool_calls | JSON | Outils demandés |
| tool_results | JSON | Résultats outils |
| input_tokens | INT | Tokens input (coût) |
| output_tokens | INT | Tokens output (coût) |
| cost | DECIMAL | Coût API ($) |

#### `chat_tool_executions`

| Colonne | Type | Description |
|---------|------|-------------|
| id | UUID | Primary key |
| message_id | UUID | Foreign key |
| tool_name | STRING | Nom outil |
| tool_input | JSON | Paramètres |
| tool_output | JSON | Résultat |
| status | ENUM | 'pending', 'success', 'error' |
| error_message | TEXT | Message erreur |
| requires_confirmation | BOOLEAN | Confirmation requise ? |
| confirmed | BOOLEAN | Confirmé ? |
| executed_at | TIMESTAMP | Date exécution |

---

## Performances

### Optimisations

1. **Context Window Limité**: Seulement 20 derniers messages envoyés à Claude
2. **Cache Conversations**: Évite recharges DB
3. **Eager Loading**: Pas de N+1
   ```php
   $conversations = ChatConversation::with('messages')->get();
   ```
4. **Indexes DB**: Sur `conversation_id`, `user_id`, `status`
5. **Queue Jobs**: Pour outils longs (exports, rapports)
   ```php
   dispatch(new GenerateReportJob($params))->onQueue('chat-tools');
   ```

### Monitoring Coûts

Dashboard admin affiche:
- Coût total mensuel
- Coût par user
- Coût par outil
- Messages/jour
- Top users

**Query exemple**:
```php
$monthCost = ChatMessage::whereMonth('created_at', now()->month)
    ->sum('cost');
```

---

## Dépannage

### Erreur: "Claude API rate limit exceeded"

**Cause**: Trop de requêtes à l'API Claude

**Solution**:
- Augmenter rate limit Claude (plan payant)
- Ajouter queue pour messages
- Implémenter retry with exponential backoff

### Erreur: "Tool execution failed: Permission denied"

**Cause**: User n'a pas permission pour l'action

**Vérification**:
```php
// Dans Policy
public function create(User $user): bool
{
    return $user->hasPermission('create_invoices');
}
```

### Outil ne s'exécute pas

**Debug**:
```php
Log::info('Tool execution', [
    'tool' => $toolName,
    'input' => $input,
    'context' => $context,
]);
```

**Vérifier**:
1. Outil enregistré dans `ToolRegistry`
2. Outil dans `config/ai.php → tools.tenant`
3. Input valide selon schema
4. Permissions user OK

### Conversation ne charge pas

**Vérifier ownership**:
```php
if ($conversation->user_id !== auth()->id()) {
    abort(403);
}
```

---

## Roadmap

### V1 (Actuel) ✅
- 30+ outils tenant + firm + superadmin
- Interface chat complète
- Tool Use API Claude
- Tracking coûts
- Confirmation actions
- Audit logging

### V2 (Prochain trimestre)
- **Plus d'outils**:
  - Génération rapports personnalisés
  - Analyse prédictive trésorerie
  - Suggestions optimisation fiscale
  - Import/export documents
- **Context amélioré**:
  - Auto-inject contexte page (invoice#123, etc.)
  - Compréhension screenshots
- **Multimodal**:
  - Upload images factures
  - Analyse PDF
  - Voice input (speech-to-text)

### V3 (Futur)
- **Fine-tuning**: Claude fine-tuné sur données comptables belges
- **Proactive AI**: Suggestions automatiques
- **Multi-agents**: Plusieurs agents spécialisés (compta, paie, juridique)
- **Webhooks**: Notifications événements externes
- **Export conversations**: PDF, email

---

## Exemples Avancés

### 1. Création Facture Complexe

**User**: "Crée une facture pour Acme SA : 10 licences logiciel à 150€, support 500€, total HT, TVA 21%, échéance 30 jours"

**Assistant**:
```
🔧 create_invoice
Parameters:
- partner_id: (recherche "Acme SA")
- line_items: [
    {desc: "Licences logiciel", qty: 10, price: 150},
    {desc: "Support", qty: 1, price: 500}
  ]
- vat_rate: 21
- payment_terms_days: 30

✅ Facture FAC-2025-098 créée avec succès
Total HT: 2 000,00 €
TVA 21%: 420,00 €
Total TTC: 2 420,00 €
Échéance: 25 janvier 2025
```

### 2. Workflow Multi-Outils

**User**: "Crée une facture pour Dupont de 1500€, envoie-la par email et configure un rappel J+15"

**Assistant exécute séquentiellement**:
1. `create_invoice` → FAC-2025-099
2. `send_invoice_email` → Email envoyé
3. `configure_invoice_reminders` → Rappel J+15 activé

**Résultat**:
```
✅ J'ai effectué les 3 actions:
1. Facture FAC-2025-099 créée (1 500,00 € HT)
2. Email envoyé à contact@dupont.be
3. Rappel automatique configuré pour le 10 janvier 2025
```

### 3. Analyse & Rapports

**User**: "Montre-moi mes factures impayées de plus de 30 jours et calcule le total"

**Assistant**:
```
🔧 read_invoices
Filters: status=unpaid, overdue_days>30

Résultat: 8 factures trouvées
Total impayé: 12 450,00 €

Détails:
- FAC-2024-156 (Acme SA): 2 500 € - 45 jours de retard
- FAC-2024-189 (Dupont): 1 800 € - 38 jours
- ...

Voulez-vous que j'envoie des rappels automatiques ?
```

---

## Support & Contact

**Documentation**: `/docs/CHAT_ASSISTANT_AI.md`
**Config**: `config/ai.php`
**Code**: `app/Services/AI/Chat/`
**Issues**: GitHub Issues

**Contact**: support@comptabe.be

---

## Références

- [Claude API Documentation](https://docs.anthropic.com/claude/docs)
- [Tool Use Guide](https://docs.anthropic.com/claude/docs/tool-use)
- [Best Practices](https://docs.anthropic.com/claude/docs/tool-use-best-practices)
- [Pricing](https://www.anthropic.com/pricing)

---

**Dernière mise à jour**: 26 décembre 2025
**Version**: 1.0.0
**LLM**: Claude 3.5 Sonnet (claude-3-5-sonnet-20241022)
