# Guide d'utilisation - Assistant AI ComptaBE

## Vue d'ensemble

L'assistant AI de ComptaBE est un chatbot intelligent alimenté par Claude 3.5 Sonnet d'Anthropic. Il permet aux utilisateurs d'interagir en langage naturel pour effectuer diverses tâches comptables, créer des factures, générer des rapports, et bien plus encore.

## Configuration

### 1. Clé API Claude

Pour utiliser l'assistant AI, vous devez obtenir une clé API Claude:

1. Créez un compte sur [console.anthropic.com](https://console.anthropic.com)
2. Générez une clé API
3. Ajoutez-la dans votre fichier `.env`:

```bash
CLAUDE_API_KEY=sk-ant-api03-votre-clé-ici
CLAUDE_MODEL=claude-3-5-sonnet-20241022
CLAUDE_MAX_TOKENS=4096
CLAUDE_TEMPERATURE=0.7
```

### 2. Compilation des assets

Assurez-vous que les assets JavaScript sont compilés:

```bash
npm install
npm run build
```

## Utilisation

### Interface utilisateur

L'assistant AI apparaît sous forme d'un widget flottant en bas à droite de l'écran:

1. **Icône de chat** : Cliquez pour ouvrir/fermer le panneau de chat
2. **Badge de notifications** : Affiche le nombre de messages non lus
3. **Panneau de conversation** : Zone de chat avec historique des messages
4. **Zone de saisie** : Tapez votre message et appuyez sur "Envoyer"

### Exemples de commandes

#### Pour les utilisateurs Tenant

**Gestion des factures:**
- "Montre-moi les factures impayées"
- "Crée une facture pour le client Tech Solutions d'un montant de 1500€ HT avec 21% de TVA"
- "Envoie la facture INV-2024-001 par email"
- "Convertis le devis QUO-2024-005 en facture"

**Gestion des clients:**
- "Recherche tous les clients dont le nom contient 'Services'"
- "Crée un nouveau client: Nom 'ABC SPRL', email 'contact@abc.be', TVA 'BE0123456789'"

**Paiements:**
- "Enregistre un paiement de 850€ pour la facture INV-2024-001"

**Déclarations:**
- "Génère ma déclaration TVA pour le trimestre Q4 2024"

**Invitations:**
- "Invite jean.dupont@email.be comme comptable dans mon entreprise"

**Exports:**
- "Exporte mes données comptables pour l'exercice 2024"

#### Pour les Fiduciaires (Firms)

- "Montre-moi la santé financière de tous mes clients"
- "Génère un rapport comparatif pour mes 5 plus gros clients"
- "Exporte la comptabilité de tous mes clients pour décembre 2024"
- "Assigne une tâche de vérification TVA au client XYZ"

#### Pour les Superadmins

- "Crée un compte démo pour 'Entreprise Test' avec l'email 'demo@test.be'"

## Outils disponibles

### Outils Tenant (17 outils implémentés)

| Outil | Description |
|-------|-------------|
| `read_invoices` | Lire et filtrer les factures |
| `create_invoice` | Créer une nouvelle facture |
| `update_invoice` | Modifier une facture existante |
| `delete_invoice` | Supprimer une facture |
| `create_quote` | Créer un devis |
| `convert_quote_to_invoice` | Convertir un devis en facture |
| `send_invoice_email` | Envoyer une facture par email |
| `send_via_peppol` | Envoyer via le réseau Peppol |
| `search_partners` | Rechercher des clients/fournisseurs |
| `create_partner` | Créer un nouveau partenaire |
| `record_payment` | Enregistrer un paiement |
| `invite_user` | Inviter un utilisateur |
| `generate_vat_declaration` | Générer déclaration TVA |
| `reconcile_bank_transaction` | Réconcilier transaction bancaire |
| `create_expense` | Créer une dépense |
| `export_accounting_data` | Exporter données comptables |
| `create_invoice_template` | Créer modèle de facture |
| `create_recurring_invoice` | Créer facture récurrente |
| `configure_invoice_reminders` | Configurer rappels de facture |

### Outils Paie (2 outils)

| Outil | Description |
|-------|-------------|
| `create_employee` | Créer un employé |
| `generate_payslip` | Générer une fiche de paie |

### Outils Fiduciaire (5 outils)

| Outil | Description |
|-------|-------------|
| `get_all_clients_data` | Obtenir données tous clients |
| `bulk_export_accounting` | Export comptable en masse |
| `generate_multi_client_report` | Rapport multi-clients |
| `assign_mandate_task` | Assigner une tâche |
| `get_client_health_score` | Score de santé client |

### Outils Superadmin (1 outil)

| Outil | Description |
|-------|-------------|
| `create_demo_account` | Créer compte démo |

## Sécurité

### Isolation Tenant

- Chaque utilisateur ne peut accéder qu'aux données de son entreprise
- Les outils vérifient automatiquement les permissions via Laravel Policies
- Les superadmins ont accès à des outils supplémentaires

### Confirmation des actions dangereuses

Certaines actions nécessitent une confirmation explicite:
- Suppression de factures
- Modifications massives
- Export de données sensibles

Lorsqu'une confirmation est requise, un bouton "Confirmer" apparaît dans le chat.

### Audit logging

Toutes les actions effectuées via l'assistant AI sont enregistrées dans les logs d'audit avec:
- Utilisateur
- Entreprise
- Outil utilisé
- Paramètres d'entrée
- Résultat

## Suivi des coûts

### Tarification Claude API

- **Input:** $3 par million de tokens (~750,000 mots)
- **Output:** $15 par million de tokens

### Estimation des coûts

Une conversation typique (10 messages):
- ~5,000 tokens input + 2,000 tokens output
- Coût: ~$0.045 (4.5 centimes)

### Tableau de bord des coûts

Les administrateurs peuvent suivre:
- Coût total mensuel
- Coût par utilisateur
- Coût par entreprise
- Nombre de messages/jour
- Top outils utilisés

Les coûts sont calculés et stockés automatiquement dans la table `chat_messages`.

## Architecture technique

### Services Core

1. **ClaudeAIService** (`app/Services/AI/Chat/ClaudeAIService.php`)
   - Communication directe avec l'API Claude
   - Gestion des tokens et calcul des coûts

2. **ChatService** (`app/Services/AI/Chat/ChatService.php`)
   - Orchestration des conversations
   - Gestion de l'historique (fenêtre de contexte: 20 derniers messages)

3. **ToolRegistry** (`app/Services/AI/Chat/ToolRegistry.php`)
   - Enregistrement et gestion des permissions des outils
   - Filtre les outils selon le rôle (tenant/firm/superadmin)

4. **ToolExecutor** (`app/Services/AI/Chat/ToolExecutor.php`)
   - Exécution sécurisée des outils
   - Validation des paramètres
   - Audit logging

### Base de données

- `chat_conversations` : Conversations utilisateur
- `chat_messages` : Messages et réponses
- `chat_tool_executions` : Historique d'exécution des outils

### Frontend

- **Alpine.js** : Gestion de l'état et des interactions
- **Component:** `resources/js/components/chat.js`
- **Widget:** `resources/views/components/chat/chat-widget.blade.php`
- **Messages:** `resources/views/components/chat/message.blade.php`

## Test rapide

### Test manuel via l'interface

1. Connectez-vous à ComptaBE
2. Cliquez sur l'icône de chat en bas à droite
3. Tapez: "Bonjour, quelles sont mes factures impayées?"
4. Vérifiez que l'assistant répond correctement

### Vérification des logs

```bash
# Voir les requêtes API Claude
tail -f storage/logs/laravel.log | grep Claude

# Voir les conversations créées
php artisan tinker
>>> \App\Models\ChatConversation::with('messages')->latest()->first()
```

## Dépannage

### L'assistant ne répond pas

1. Vérifiez que `CLAUDE_API_KEY` est configurée dans `.env`
2. Vérifiez les logs: `tail -f storage/logs/laravel.log`
3. Vérifiez que les tables existent: `php artisan migrate:status`
4. Vérifiez que les assets sont compilés: `npm run build`

### Erreur "Rate limit exceeded"

L'API Claude a des limites de taux. Attendez quelques secondes et réessayez.

### Erreur "Tool not found"

Vérifiez que l'outil est bien enregistré dans `config/ai.php` sous la section `tools.tenant` ou `tools.superadmin`.

### Conversation ne se charge pas

1. Effacez le cache du navigateur
2. Vérifiez la console JavaScript: F12 → Console
3. Vérifiez que les routes API sont accessibles: `/api/chat/conversations`

## Améliorations futures

- [ ] Plus d'outils pour tous les modules
- [ ] Recherche full-text dans l'historique
- [ ] Actions suggérées proactives
- [ ] Support multi-langue (FR/NL/EN)
- [ ] Voice input (speech-to-text)
- [ ] Export PDF des conversations
- [ ] Raccourcis clavier (Cmd+/)
- [ ] Context awareness automatique (injecter page actuelle)
- [ ] Suggestions personnalisées basées sur l'usage
- [ ] Webhooks pour événements critiques

## Support

Pour toute question ou problème:
- Vérifiez d'abord ce guide
- Consultez les logs: `storage/logs/laravel.log`
- Contactez l'équipe technique

---

**Version:** 1.0
**Dernière mise à jour:** Décembre 2024
**Status:** Production Ready ✅
