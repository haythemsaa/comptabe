# Guide Utilisateur ComptaBE 📚

## Vue d'ensemble

ComptaBE est une **plateforme comptable SaaS belge tout-en-un** conçue pour simplifier la comptabilité des PME et des fiduciaires. Cette documentation couvre toutes les fonctionnalités disponibles.

---

## 🚀 Démarrage Rapide

### Installation et Configuration

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-repo/compta.git
   cd compta
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Variables d'environnement importantes**
   ```env
   # Base de données
   DB_DATABASE=compta
   DB_USERNAME=root
   DB_PASSWORD=

   # Claude AI (Assistant Chat)
   CLAUDE_API_KEY=sk-ant-...
   CLAUDE_MODEL=claude-3-5-sonnet-20241022

   # Peppol (Facturation électronique 2026)
   PEPPOL_PROVIDER=storecove
   STORECOVE_API_KEY=your_api_key
   ```

5. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

6. **Générer des données de démo**
   ```bash
   php artisan demo:setup --full
   ```

7. **Lancer l'application**
   ```bash
   php artisan serve
   npm run dev
   ```

Accédez à : `http://localhost:8000`

---

## 📊 Fonctionnalités Principales

### 1. Gestion des Factures et Devis

#### Créer une facture
- **Menu** : Ventes > Factures > Nouvelle facture
- **Raccourci** : Dashboard > "+ Nouvelle facture"
- **Via AI** : Demandez à l'assistant "Créer une facture pour [client]"

#### Fonctionnalités factures :
- ✅ Numérotation automatique personnalisable
- ✅ Modèles de factures réutilisables
- ✅ Factures récurrentes (abonnements)
- ✅ Relances automatiques par email
- ✅ Export PDF avec logo personnalisé
- ✅ Envoi via Peppol (facturation électronique)
- ✅ Multi-devises (EUR, USD, GBP)

#### Statuts de facture :
- **Draft** : Brouillon en cours d'édition
- **Sent** : Envoyée au client
- **Paid** : Payée (avec paiement enregistré)
- **Overdue** : En retard de paiement
- **Cancelled** : Annulée

---

### 2. Déclarations TVA en 1 Clic 🇧🇪

#### Génération automatique
```bash
php artisan vat:generate-missing --year=2025 --period-type=monthly
```

Ou via l'interface :
- **Menu** : Comptabilité > TVA > Nouvelle déclaration
- Sélectionnez la période (mois ou trimestre)
- Cliquez sur "Générer automatiquement"

#### Grilles TVA supportées (Belgique) :
- **Grilles 00-49** : Opérations sur le territoire belge
- **Grilles 54-72** : **Grilles européennes** (nouvelles réglementations 2025)
  - 54 : Livraisons intracommunautaires
  - 55 : TVA sur livraisons IC
  - 56 : Services IC (B2B)
  - 57 : TVA services IC
  - 59 : Acquisitions IC de biens
  - 63 : Services reçus d'un État membre
  - 71 : Import avec report de perception
  - 72 : TVA autoliquidée import

#### Export Intervat XML
L'export XML est conforme au format Intervat pour soumission directe au SPF Finances.

---

### 3. 🤖 Assistant AI Chat (Claude)

#### Activer l'assistant
1. Configurez votre clé API Claude dans `.env` :
   ```env
   CLAUDE_API_KEY=sk-ant-api03-...
   ```

2. L'assistant apparaît en bas à droite de toutes les pages (icône de chat)

#### Outils disponibles

**Pour les utilisateurs Tenant :**
- `read_invoices` : Lire les factures avec filtres
- `create_invoice` : Créer une nouvelle facture
- `create_quote` : Créer un devis
- `search_partners` : Rechercher des clients/fournisseurs
- `create_partner` : Ajouter un partenaire
- `record_payment` : Enregistrer un paiement
- `invite_user` : Inviter un collaborateur
- `send_invoice_email` : Envoyer une facture par email
- `convert_quote_to_invoice` : Convertir devis en facture
- `generate_vat_declaration` : Générer déclaration TVA
- `send_via_peppol` : Envoyer via Peppol
- `reconcile_bank_transaction` : Rapprocher une transaction bancaire
- `create_expense` : Créer une dépense
- `export_accounting_data` : Exporter données comptables

**Pour les fiduciaires (Firm) :**
- `get_all_clients_data` : Vue d'ensemble tous clients
- `bulk_export_accounting` : Export groupé multi-clients
- `generate_multi_client_report` : Rapports comparatifs
- `assign_mandate_task` : Assigner tâches de mandat
- `get_client_health_score` : Score de santé client

**Pour les Superadmin :**
- `create_demo_account` : Créer compte de démonstration

#### Exemples d'utilisation :

```
Utilisateur : "Crée une facture pour Acme Corporation avec 10h de consulting à 85€/h"

Assistant : ✓ J'ai créé la facture DEMO-00015 pour Acme Corporation
            - 10 heures de Consultation comptable à 85,00 €
            - Total HT : 850,00 €
            - Total TVA (21%) : 178,50 €
            - Total TTC : 1 028,50 €
```

```
Utilisateur : "Combien de factures impayées ai-je ?"

Assistant : Vous avez 7 factures impayées :
            - 3 en retard (> 30 jours) : 4 523,50 €
            - 4 à échéance proche : 2 890,00 €
            Total dû : 7 413,50 €
```

#### Suivi des coûts
- Chaque conversation est trackée en DB
- Les tokens (input/output) sont comptabilisés
- Coût approximatif : ~3¢ par conversation moyenne
- Dashboard admin : visualisez les coûts mensuels

---

### 4. 💼 Portail Client (Client Portal)

#### Accès client sécurisé

**Niveaux de permission :**
- **view_only** : Consultation factures/documents uniquement
- **upload_documents** : + Upload de justificatifs
- **full_client** : + Commentaires, solde, rapports

#### Inviter un client au portail :
```bash
php artisan tinker
```
```php
use App\Models\ClientAccess;
use App\Models\User;
use App\Models\Company;

$user = User::where('email', 'client@example.com')->first();
$company = Company::first();

ClientAccess::create([
    'user_id' => $user->id,
    'company_id' => $company->id,
    'access_level' => 'full_client',
    'permissions' => [
        'view_invoices' => true,
        'download_invoices' => true,
        'upload_documents' => true,
        'comment' => true,
        'view_balance' => true,
    ],
]);
```

#### Fonctionnalités portail :
- 📊 Dashboard avec statistiques personnalisées
- 📄 Liste et détail des factures
- 📥 Téléchargement PDF des factures
- 📤 Upload de documents (drag & drop)
- 💬 Système de commentaires avec mentions (@utilisateur)
- 🔔 Notifications en temps réel

#### URL d'accès :
```
https://app.comptabe.be/portal/{company-id}
```

---

### 5. 🏦 Rapprochement Bancaire Intelligent

#### Importation fichiers CODA
```bash
php artisan bank:import-coda /path/to/file.cod --bank-account=uuid
```

#### Rapprochement automatique (Smart Reconciliation)

L'IA analyse :
- Montant de la transaction
- Nom du partenaire
- Numéro de référence/communication
- Date (± 30 jours de tolérance)

**Scores de correspondance :**
- **> 0.90** : Correspondance excellente (auto-rapprochement)
- **0.70-0.90** : Correspondance probable (suggestion)
- **< 0.70** : Nécessite vérification manuelle

#### Via l'interface :
- Menu : Comptabilité > Banque > Rapprochements
- Les suggestions apparaissent automatiquement
- Cliquez sur "Accepter" pour valider

---

### 6. 📨 Facturation Électronique Peppol (2026)

#### Configuration Peppol

**1. Choisir un fournisseur :**
- **Storecove** (recommandé) : API moderne, support PEPPOL complet
- **DIME.be** : Fournisseur belge
- **Unifiedpost** : Solution entreprise

**2. Configurer dans `.env` :**
```env
PEPPOL_PROVIDER=storecove
STORECOVE_API_KEY=your_key
STORECOVE_LEGAL_ENTITY_ID=your_le_id
```

**3. Enregistrer votre identifiant Peppol :**
- Format : `0208:BE0123456789` (BE + numéro TVA)
- Enregistrement via le fournisseur choisi

#### Envoyer une facture via Peppol :
1. Créez votre facture normalement
2. Vérifiez que le client a un identifiant Peppol
3. Cliquez sur "Envoyer via Peppol"
4. Le statut passe à "Envoyé" avec tracking

#### Statuts Peppol :
- ✅ **sent** : Envoyé avec succès
- ⏳ **delivered** : Livré au destinataire
- ✓ **read** : Lu par le destinataire
- ❌ **rejected** : Rejeté (erreur format/données)

#### Quotas d'utilisation :
- Gratuit : 10 envois/mois
- Starter : 100 envois/mois
- Pro : 500 envois/mois
- Enterprise : Illimité

---

### 7. 📈 Prédictions de Trésorerie (ML)

#### Modèle Machine Learning

Utilise un algorithme de régression linéaire pour prédire :
- **Revenus futurs** (basés sur factures récurrentes + historique)
- **Dépenses prévues** (analyse des patterns mensuels)
- **Solde de trésorerie** (projection 1-12 mois)

#### Entraînement du modèle :
```bash
php artisan ml:train-cash-flow --company={uuid}
```

#### Générer des prédictions :
```bash
php artisan ml:predict-cash-flow --company={uuid} --months=6
```

#### Via l'interface :
- Menu : Tableau de bord > Prédictions
- Graphique interactif Chart.js
- Export PDF/Excel disponible

#### Précision :
- Basée sur minimum 6 mois de données historiques
- Précision moyenne : ~85% (selon régularité des opérations)
- Facteurs pris en compte :
  - Saisonnalité
  - Factures récurrentes
  - Tendances de croissance
  - Événements exceptionnels

---

### 8. 💰 Gestion de la Paie (Belgique)

#### Créer un employé :
```bash
php artisan tinker
```
```php
use App\Models\Employee;
use App\Models\Company;

Employee::create([
    'company_id' => Company::first()->id,
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'national_number' => '85.01.15-123.45',
    'email' => 'jean.dupont@example.com',
    'hire_date' => '2024-01-01',
    'employment_type' => 'permanent',
    'gross_salary' => 3500.00,
]);
```

#### Générer une fiche de paie :
- Menu : Paie > Employés > [Sélectionner] > Nouvelle fiche
- Période : Sélectionnez mois
- Cliquez "Générer"

#### Calculs automatiques (conformes Belgique) :
- **Cotisations sociales employé** : 13.07%
- **Cotisations patronales** : 25%
- **Précompte professionnel** : Barème progressif belge
- **Avantages en nature** : Voiture de société, téléphone, etc.

#### Déclarations sociales :
- **DIMONA** : Déclaration immédiate (embauche/sortie)
- **DmfA** : Déclaration trimestrielle multi-fonctionnelle
- Export XML conforme ONSS

---

### 9. 🔐 Gestion Multi-Tenant Sécurisée

#### Isolation des données

**Chaque entreprise (tenant) a :**
- Base de données partagée avec isolation stricte
- Colonne `company_id` sur toutes les tables
- Global scope Laravel automatique
- Middleware de vérification d'accès

#### Rôles utilisateurs :
- **Owner** : Propriétaire (tous les droits)
- **Admin** : Administrateur (gestion complète)
- **Accountant** : Comptable (lecture + saisie)
- **User** : Utilisateur standard (lecture uniquement)

#### Permissions granulaires :
```php
$user->can('create', Invoice::class);
$user->can('view', $invoice);
$user->can('update', $invoice);
```

#### Fiduciaires (Accounting Firms) :
- Gestion multi-clients depuis un seul compte
- Vue consolidée de tous les mandats
- Assignation de tâches aux collaborateurs
- Facturation temps passé

---

### 10. 🎨 Thème Sombre (Dark Mode)

#### Activation :
- Icône lune/soleil en haut à droite
- Préférence sauvegardée par utilisateur
- Appliquée à toute l'interface

#### Classes Tailwind utilisées :
```html
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
```

---

## 🛠️ Commandes Artisan Utiles

### Données de démo
```bash
# Setup complet avec toutes les fonctionnalités
php artisan demo:setup --full

# Setup pour une entreprise spécifique
php artisan demo:setup --company=uuid
```

### TVA
```bash
# Générer déclarations manquantes
php artisan vat:generate-missing --year=2025

# Générer pour une période spécifique
php artisan vat:generate --company=uuid --period=2025-Q1
```

### Banque
```bash
# Importer fichier CODA
php artisan bank:import-coda /path/to/file.cod --bank-account=uuid

# Lancer rapprochement automatique
php artisan bank:reconcile-auto --company=uuid
```

### Machine Learning
```bash
# Entraîner modèle de prédictions
php artisan ml:train-cash-flow --company=uuid

# Générer prédictions
php artisan ml:predict-cash-flow --company=uuid --months=6
```

### Peppol
```bash
# Envoyer facture via Peppol
php artisan peppol:send-invoice {invoice-id}

# Vérifier statut
php artisan peppol:check-status {invoice-id}
```

### E-reporting (MyMinfin)
```bash
# Soumettre déclaration e-reporting
php artisan ereporting:submit --company=uuid --year=2024
```

---

## 🔌 API REST (v1)

### Authentification

**Sanctum Token :**
```bash
POST /api/v1/login
{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "token": "1|abc123...",
  "user": {...}
}
```

Utiliser dans headers :
```
Authorization: Bearer 1|abc123...
```

### Endpoints principaux

#### Factures
```bash
GET    /api/v1/invoices
POST   /api/v1/invoices
GET    /api/v1/invoices/{id}
PUT    /api/v1/invoices/{id}
DELETE /api/v1/invoices/{id}
POST   /api/v1/invoices/{id}/send-email
POST   /api/v1/invoices/{id}/validate
```

#### Devis
```bash
GET    /api/v1/quotes
POST   /api/v1/quotes
POST   /api/v1/quotes/{id}/convert-to-invoice
```

#### Partenaires
```bash
GET    /api/v1/partners
POST   /api/v1/partners
GET    /api/v1/partners/{id}
```

#### Produits
```bash
GET    /api/v1/products
POST   /api/v1/products
```

#### Chat AI
```bash
GET    /api/chat/conversations
POST   /api/chat/send
POST   /api/chat/tools/{execution}/confirm
```

### Exemple d'utilisation (JavaScript) :
```javascript
const response = await fetch('/api/v1/invoices', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

const invoices = await response.json();
```

---

## 🚨 Dépannage

### Problème : Migration échoue

**Solution :**
```bash
php artisan migrate:fresh --seed
```

### Problème : Assistant AI ne répond pas

**Vérifications :**
1. Clé API Claude valide dans `.env`
2. Fichier `config/ai.php` présent
3. Tables chat migrées :
   ```bash
   php artisan migrate:status | grep chat
   ```

### Problème : Peppol "Provider not configured"

**Solution :**
```env
PEPPOL_PROVIDER=storecove
STORECOVE_API_KEY=your_key
```

Puis :
```bash
php artisan config:clear
php artisan cache:clear
```

### Problème : Permissions refusées

**Vérifier :**
```bash
# Propriétaire des fichiers
sudo chown -R www-data:www-data storage bootstrap/cache

# Permissions
sudo chmod -R 775 storage bootstrap/cache
```

---

## 📞 Support

### Documentation officielle
- **Site** : https://comptabe.be
- **Docs API** : https://docs.comptabe.be
- **GitHub** : https://github.com/comptabe/app

### Contact
- **Email** : support@comptabe.be
- **Téléphone** : +32 2 123 45 67
- **Chat** : Dans l'application (icône en bas à droite)

---

## 🎯 Roadmap 2025

### Q1 2025
- [x] Déclarations TVA grilles 54-72
- [x] Assistant AI Chat complet
- [x] Portail client avec commentaires
- [ ] App mobile (iOS/Android)

### Q2 2025
- [ ] Intégration e-commerce (Shopify, WooCommerce)
- [ ] OCR intelligent pour factures fournisseurs
- [ ] Workflow d'approbation multi-niveaux

### Q3 2025
- [ ] Comptabilité analytique avancée
- [ ] Budgets et prévisions IA
- [ ] Intégration CRM (Salesforce, HubSpot)

### Q4 2025
- [ ] Conformité GDPR automatique
- [ ] Blockchain pour audit trail
- [ ] API v2 avec GraphQL

---

## 📄 Licence

**Propriétaire** : ComptaBE SPRL
**Licence** : Propriétaire - Tous droits réservés
**Version** : 2.0.0
**Dernière mise à jour** : 28 décembre 2024

---

## 🏆 Credits

Développé avec ❤️ par l'équipe ComptaBE

**Technologies utilisées :**
- Laravel 11
- Alpine.js
- Tailwind CSS
- Claude AI (Anthropic)
- Chart.js
- MySQL
- Redis (cache)

---

**Bon comptabilité ! 🎉**
