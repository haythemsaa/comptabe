# Cahier des Charges Technique
## Module Expert-Comptable - ComptaBE

**Version:** 1.0
**Date:** 19 Décembre 2025
**Statut:** Proposition

---

## 1. Introduction

### 1.1 Contexte
ComptaBE est une application SaaS de comptabilité belge. Ce document spécifie l'ajout d'une filière "Expert-Comptable" permettant aux cabinets comptables de gérer plusieurs entreprises clientes depuis une interface centralisée.

### 1.2 Objectifs
- Permettre aux experts-comptables de gérer un portefeuille de clients
- Offrir une vue consolidée de tous les dossiers clients
- Faciliter la collaboration entre cabinet et clients
- Proposer des outils spécifiques aux professionnels du chiffre

### 1.3 Architecture Multi-Niveaux

```
┌─────────────────────────────────────────────────────────────────┐
│                        SUPERADMIN                                │
│         (Administration globale de la plateforme)                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────┐     ┌──────────────────────────────┐  │
│  │   VERSION ENTREPRISE │     │   VERSION EXPERT-COMPTABLE   │  │
│  │   (Particuliers/PME) │     │      (Cabinets comptables)   │  │
│  │                      │     │                              │  │
│  │  ┌────────────────┐  │     │  ┌────────────────────────┐  │  │
│  │  │  Entreprise A  │  │     │  │   Cabinet Comptable    │  │  │
│  │  │  (autonome)    │  │     │  │                        │  │  │
│  │  └────────────────┘  │     │  │  ┌─────┐ ┌─────┐      │  │  │
│  │                      │     │  │  │Cli.1│ │Cli.2│ ...  │  │  │
│  │  ┌────────────────┐  │     │  │  └─────┘ └─────┘      │  │  │
│  │  │  Entreprise B  │  │     │  └────────────────────────┘  │  │
│  │  │  (autonome)    │  │     │                              │  │
│  │  └────────────────┘  │     │  ┌────────────────────────┐  │  │
│  │                      │     │  │   Cabinet Comptable 2  │  │  │
│  └──────────────────────┘     │  └────────────────────────┘  │  │
│                               └──────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Types d'Utilisateurs et Rôles

### 2.1 Niveau Superadmin (Plateforme)

| Rôle | Description | Permissions |
|------|-------------|-------------|
| `superadmin` | Administrateur plateforme | Accès total, gestion abonnements, configuration globale |
| `support` | Support technique | Accès lecture aux dossiers, assistance utilisateurs |

### 2.2 Niveau Expert-Comptable (Cabinet)

| Rôle | Description | Permissions |
|------|-------------|-------------|
| `cabinet_owner` | Propriétaire du cabinet | Gestion complète du cabinet et tous les clients |
| `cabinet_admin` | Administrateur cabinet | Gestion des collaborateurs et clients |
| `cabinet_manager` | Chef de mission | Supervision d'un portefeuille de clients |
| `cabinet_accountant` | Collaborateur comptable | Travail sur les dossiers assignés |
| `cabinet_assistant` | Assistant | Saisie et tâches de base |

### 2.3 Niveau Entreprise (Client)

| Rôle | Description | Permissions |
|------|-------------|-------------|
| `company_owner` | Propriétaire entreprise | Gestion complète de son entreprise |
| `company_admin` | Administrateur | Configuration et utilisateurs |
| `company_accountant` | Comptable interne | Opérations comptables |
| `company_member` | Membre | Lecture seule |

---

## 3. Modèle de Données

### 3.1 Nouvelles Tables

#### 3.1.1 Table `accounting_firms` (Cabinets comptables)

```sql
CREATE TABLE accounting_firms (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    -- Informations de base
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    legal_form VARCHAR(50), -- SRL, SA, PP, etc.

    -- Identification professionnelle
    itaa_number VARCHAR(50), -- Numéro ITAA (Institut des conseillers fiscaux et experts-comptables)
    ire_number VARCHAR(50),  -- Numéro IRE (Institut des Réviseurs d'Entreprises)
    vat_number VARCHAR(20) NOT NULL,
    enterprise_number VARCHAR(20),

    -- Coordonnées
    street VARCHAR(255),
    house_number VARCHAR(20),
    box VARCHAR(10),
    postal_code VARCHAR(10),
    city VARCHAR(100),
    country_code CHAR(2) DEFAULT 'BE',
    email VARCHAR(255),
    phone VARCHAR(50),
    website VARCHAR(255),

    -- Branding
    logo_path VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#3B82F6',

    -- Configuration Peppol
    peppol_id VARCHAR(100),
    peppol_provider VARCHAR(50),
    peppol_api_key TEXT,
    peppol_api_secret TEXT,
    peppol_test_mode BOOLEAN DEFAULT true,

    -- Abonnement
    subscription_plan_id UUID REFERENCES subscription_plans(id),
    subscription_status VARCHAR(20) DEFAULT 'trial',
    trial_ends_at TIMESTAMP,
    max_clients INTEGER DEFAULT 10,
    max_users INTEGER DEFAULT 5,

    -- Paramètres
    settings JSONB DEFAULT '{}',
    features JSONB DEFAULT '{}',

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,

    -- Index
    INDEX idx_accounting_firms_itaa (itaa_number),
    INDEX idx_accounting_firms_vat (vat_number)
);
```

#### 3.1.2 Table `accounting_firm_users` (Collaborateurs cabinet)

```sql
CREATE TABLE accounting_firm_users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    accounting_firm_id UUID NOT NULL REFERENCES accounting_firms(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,

    -- Rôle dans le cabinet
    role VARCHAR(50) NOT NULL DEFAULT 'cabinet_accountant',
    -- cabinet_owner, cabinet_admin, cabinet_manager, cabinet_accountant, cabinet_assistant

    -- Informations professionnelles
    employee_number VARCHAR(50),
    job_title VARCHAR(100),
    department VARCHAR(100),

    -- Permissions spécifiques (override du rôle)
    permissions JSONB DEFAULT '{}',

    -- Accès aux clients
    can_access_all_clients BOOLEAN DEFAULT false,

    -- Configuration
    is_default BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,

    -- Audit
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(accounting_firm_id, user_id)
);
```

#### 3.1.3 Table `client_mandates` (Mandats clients)

```sql
CREATE TABLE client_mandates (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    accounting_firm_id UUID NOT NULL REFERENCES accounting_firms(id) ON DELETE CASCADE,
    company_id UUID NOT NULL REFERENCES companies(id) ON DELETE CASCADE,

    -- Type de mandat
    mandate_type VARCHAR(50) NOT NULL DEFAULT 'full',
    -- full: Mandat complet
    -- bookkeeping: Tenue comptable uniquement
    -- tax: Missions fiscales
    -- payroll: Gestion sociale
    -- advisory: Conseil uniquement
    -- audit: Révision

    -- Statut
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    -- pending: En attente d'acceptation
    -- active: Actif
    -- suspended: Suspendu
    -- terminated: Terminé

    -- Période du mandat
    start_date DATE NOT NULL,
    end_date DATE,

    -- Responsable dossier
    manager_user_id UUID REFERENCES users(id),

    -- Équipe assignée
    assigned_users JSONB DEFAULT '[]', -- [{user_id, role, permissions}]

    -- Services inclus
    services JSONB DEFAULT '{}',
    -- {
    --   bookkeeping: true,
    --   vat_declarations: true,
    --   annual_accounts: true,
    --   tax_returns: true,
    --   payroll: false,
    --   ...
    -- }

    -- Tarification
    billing_type VARCHAR(20) DEFAULT 'monthly',
    -- hourly, monthly, annual, package
    hourly_rate DECIMAL(10,2),
    monthly_fee DECIMAL(10,2),
    annual_fee DECIMAL(10,2),

    -- Accès client
    client_can_view BOOLEAN DEFAULT true,
    client_can_edit BOOLEAN DEFAULT false,
    client_can_validate BOOLEAN DEFAULT false,

    -- Notes
    internal_notes TEXT,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,

    UNIQUE(accounting_firm_id, company_id)
);
```

#### 3.1.4 Table `mandate_activities` (Activités sur dossiers)

```sql
CREATE TABLE mandate_activities (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_mandate_id UUID NOT NULL REFERENCES client_mandates(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id),

    -- Type d'activité
    activity_type VARCHAR(50) NOT NULL,
    -- login, invoice_created, vat_declared, document_uploaded, note_added, etc.

    -- Description
    description TEXT,

    -- Données supplémentaires
    metadata JSONB DEFAULT '{}',

    -- Temps passé (pour facturation)
    time_spent_minutes INTEGER,
    is_billable BOOLEAN DEFAULT false,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_mandate_activities_mandate (client_mandate_id),
    INDEX idx_mandate_activities_date (created_at)
);
```

#### 3.1.5 Table `mandate_documents` (Documents partagés)

```sql
CREATE TABLE mandate_documents (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_mandate_id UUID NOT NULL REFERENCES client_mandates(id) ON DELETE CASCADE,
    uploaded_by UUID NOT NULL REFERENCES users(id),

    -- Document
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    file_size INTEGER,

    -- Classification
    category VARCHAR(50),
    -- invoice, receipt, bank_statement, contract, annual_accounts, tax_return, other
    fiscal_year INTEGER,
    period VARCHAR(20), -- Q1, Q2, Q3, Q4, M01-M12

    -- Statut
    status VARCHAR(20) DEFAULT 'pending',
    -- pending, processing, processed, rejected

    -- OCR / AI
    ocr_text TEXT,
    ai_extracted_data JSONB,

    -- Visibilité
    visible_to_client BOOLEAN DEFAULT true,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP,
    processed_by UUID REFERENCES users(id)
);
```

#### 3.1.6 Table `mandate_tasks` (Tâches/missions)

```sql
CREATE TABLE mandate_tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_mandate_id UUID NOT NULL REFERENCES client_mandates(id) ON DELETE CASCADE,

    -- Tâche
    title VARCHAR(255) NOT NULL,
    description TEXT,

    -- Type
    task_type VARCHAR(50) NOT NULL,
    -- vat_declaration, annual_accounts, tax_return, bookkeeping, payroll, meeting, other

    -- Période concernée
    fiscal_year INTEGER,
    period VARCHAR(20),

    -- Échéances
    due_date DATE,
    reminder_date DATE,

    -- Assignation
    assigned_to UUID REFERENCES users(id),

    -- Statut
    status VARCHAR(20) DEFAULT 'pending',
    -- pending, in_progress, review, completed, cancelled
    priority VARCHAR(10) DEFAULT 'normal',
    -- low, normal, high, urgent

    -- Temps
    estimated_hours DECIMAL(5,2),
    actual_hours DECIMAL(5,2),

    -- Facturation
    is_billable BOOLEAN DEFAULT true,
    billed_at TIMESTAMP,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    completed_by UUID REFERENCES users(id)
);
```

#### 3.1.7 Table `mandate_communications` (Communications)

```sql
CREATE TABLE mandate_communications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_mandate_id UUID NOT NULL REFERENCES client_mandates(id) ON DELETE CASCADE,

    -- Expéditeur
    sender_id UUID NOT NULL REFERENCES users(id),
    sender_type VARCHAR(20) NOT NULL, -- cabinet, client

    -- Message
    subject VARCHAR(255),
    message TEXT NOT NULL,

    -- Pièces jointes
    attachments JSONB DEFAULT '[]',

    -- Statut
    is_read BOOLEAN DEFAULT false,
    read_at TIMESTAMP,

    -- Réponse à
    parent_id UUID REFERENCES mandate_communications(id),

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3.2 Modifications Tables Existantes

#### 3.2.1 Table `companies` (ajouts)

```sql
ALTER TABLE companies ADD COLUMN company_type VARCHAR(20) DEFAULT 'standalone';
-- standalone: Entreprise autonome (version entreprise)
-- client: Client d'un cabinet
-- accounting_firm: Le cabinet lui-même (usage interne)

ALTER TABLE companies ADD COLUMN managed_by_firm_id UUID REFERENCES accounting_firms(id);
ALTER TABLE companies ADD COLUMN accepts_firm_management BOOLEAN DEFAULT false;
ALTER TABLE companies ADD COLUMN firm_access_level VARCHAR(20) DEFAULT 'full';
-- full: Accès complet
-- limited: Accès limité (lecture + certaines opérations)
-- readonly: Lecture seule
```

#### 3.2.2 Table `users` (ajouts)

```sql
ALTER TABLE users ADD COLUMN user_type VARCHAR(20) DEFAULT 'standard';
-- standard: Utilisateur normal
-- accountant: Expert-comptable professionnel
-- superadmin: Administrateur plateforme

ALTER TABLE users ADD COLUMN professional_title VARCHAR(100);
-- Expert-comptable certifié ITAA, Réviseur d'entreprises, etc.

ALTER TABLE users ADD COLUMN itaa_number VARCHAR(50);
ALTER TABLE users ADD COLUMN ire_number VARCHAR(50);
```

---

## 4. Système de Permissions

### 4.1 Structure des Permissions

```php
// app/Enums/Permission.php

enum Permission: string
{
    // === PERMISSIONS SUPERADMIN ===
    case PLATFORM_MANAGE = 'platform.manage';
    case PLATFORM_USERS = 'platform.users';
    case PLATFORM_BILLING = 'platform.billing';
    case PLATFORM_SUPPORT = 'platform.support';

    // === PERMISSIONS CABINET ===
    // Gestion cabinet
    case FIRM_VIEW = 'firm.view';
    case FIRM_EDIT = 'firm.edit';
    case FIRM_BILLING = 'firm.billing';
    case FIRM_SETTINGS = 'firm.settings';

    // Gestion collaborateurs
    case FIRM_USERS_VIEW = 'firm.users.view';
    case FIRM_USERS_MANAGE = 'firm.users.manage';
    case FIRM_USERS_INVITE = 'firm.users.invite';

    // Gestion clients
    case CLIENTS_VIEW_ALL = 'clients.view.all';
    case CLIENTS_VIEW_ASSIGNED = 'clients.view.assigned';
    case CLIENTS_CREATE = 'clients.create';
    case CLIENTS_EDIT = 'clients.edit';
    case CLIENTS_DELETE = 'clients.delete';
    case CLIENTS_ASSIGN = 'clients.assign';

    // Opérations comptables (sur dossiers clients)
    case BOOKKEEPING_VIEW = 'bookkeeping.view';
    case BOOKKEEPING_EDIT = 'bookkeeping.edit';
    case BOOKKEEPING_VALIDATE = 'bookkeeping.validate';

    case VAT_VIEW = 'vat.view';
    case VAT_PREPARE = 'vat.prepare';
    case VAT_SUBMIT = 'vat.submit';

    case ACCOUNTS_VIEW = 'accounts.view';
    case ACCOUNTS_PREPARE = 'accounts.prepare';
    case ACCOUNTS_SUBMIT = 'accounts.submit';

    // Rapports
    case REPORTS_CLIENT = 'reports.client';
    case REPORTS_FIRM = 'reports.firm';
    case REPORTS_EXPORT = 'reports.export';

    // === PERMISSIONS ENTREPRISE ===
    case COMPANY_VIEW = 'company.view';
    case COMPANY_EDIT = 'company.edit';
    case COMPANY_USERS = 'company.users';
    case COMPANY_BILLING = 'company.billing';

    case INVOICES_VIEW = 'invoices.view';
    case INVOICES_CREATE = 'invoices.create';
    case INVOICES_EDIT = 'invoices.edit';
    case INVOICES_DELETE = 'invoices.delete';
    case INVOICES_SEND = 'invoices.send';

    case PARTNERS_VIEW = 'partners.view';
    case PARTNERS_MANAGE = 'partners.manage';

    case PRODUCTS_VIEW = 'products.view';
    case PRODUCTS_MANAGE = 'products.manage';

    case BANKING_VIEW = 'banking.view';
    case BANKING_RECONCILE = 'banking.reconcile';

    case JOURNAL_VIEW = 'journal.view';
    case JOURNAL_EDIT = 'journal.edit';
}
```

### 4.2 Matrice des Rôles

```php
// app/Services/PermissionService.php

class PermissionService
{
    protected static array $rolePermissions = [
        // Superadmin
        'superadmin' => ['*'], // Toutes les permissions

        // Cabinet - Propriétaire
        'cabinet_owner' => [
            'firm.*',
            'clients.*',
            'bookkeeping.*',
            'vat.*',
            'accounts.*',
            'reports.*',
        ],

        // Cabinet - Admin
        'cabinet_admin' => [
            'firm.view', 'firm.edit', 'firm.settings',
            'firm.users.*',
            'clients.*',
            'bookkeeping.*',
            'vat.*',
            'accounts.*',
            'reports.*',
        ],

        // Cabinet - Manager
        'cabinet_manager' => [
            'firm.view',
            'firm.users.view',
            'clients.view.all', 'clients.edit', 'clients.assign',
            'bookkeeping.*',
            'vat.*',
            'accounts.*',
            'reports.*',
        ],

        // Cabinet - Comptable
        'cabinet_accountant' => [
            'firm.view',
            'clients.view.assigned',
            'bookkeeping.view', 'bookkeeping.edit',
            'vat.view', 'vat.prepare',
            'accounts.view', 'accounts.prepare',
            'reports.client',
        ],

        // Cabinet - Assistant
        'cabinet_assistant' => [
            'firm.view',
            'clients.view.assigned',
            'bookkeeping.view', 'bookkeeping.edit',
            'reports.client',
        ],

        // Entreprise - Propriétaire
        'company_owner' => [
            'company.*',
            'invoices.*',
            'partners.*',
            'products.*',
            'banking.*',
            'journal.*',
        ],

        // Entreprise - Admin
        'company_admin' => [
            'company.view', 'company.edit', 'company.users',
            'invoices.*',
            'partners.*',
            'products.*',
            'banking.*',
            'journal.view',
        ],

        // Entreprise - Comptable
        'company_accountant' => [
            'company.view',
            'invoices.*',
            'partners.view', 'partners.manage',
            'products.view',
            'banking.view', 'banking.reconcile',
            'journal.view', 'journal.edit',
        ],

        // Entreprise - Membre
        'company_member' => [
            'company.view',
            'invoices.view',
            'partners.view',
            'products.view',
        ],
    ];
}
```

---

## 5. Interfaces Utilisateur

### 5.1 Dashboard Superadmin

```
┌─────────────────────────────────────────────────────────────────┐
│  ComptaBE Admin                                    [Admin User] │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐ │
│  │ Entreprises │ │  Cabinets   │ │ Utilisateurs│ │   MRR     │ │
│  │    1,247    │ │     89      │ │   3,456     │ │  €45,678  │ │
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘ │
│                                                                 │
│  ┌─────────────────────────────┬───────────────────────────────┐│
│  │   Inscriptions récentes     │    Revenus par plan          ││
│  │   [Graphique]               │    [Graphique camembert]     ││
│  └─────────────────────────────┴───────────────────────────────┘│
│                                                                 │
│  Navigation:                                                    │
│  - Dashboard                                                    │
│  - Entreprises (version standard)                               │
│  - Cabinets comptables                                          │
│  - Utilisateurs                                                 │
│  - Abonnements & Facturation                                    │
│  - Rapports plateforme                                          │
│  - Configuration globale                                        │
│  - Logs & Audit                                                 │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Dashboard Cabinet Expert-Comptable

```
┌─────────────────────────────────────────────────────────────────┐
│  Cabinet Dupont & Associés                    [Marie Dupont ▼] │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌───────────┐ │
│  │   Clients   │ │  Tâches en  │ │  Échéances  │ │ Documents │ │
│  │     45      │ │   cours: 23 │ │  cette sem. │ │ à traiter │ │
│  │ actifs      │ │             │ │     12      │ │    34     │ │
│  └─────────────┘ └─────────────┘ └─────────────┘ └───────────┘ │
│                                                                 │
│  Échéances urgentes:                                            │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ ⚠️ TVA Q4 2024 - Boulangerie Martin      | 20/01 | En cours ││
│  │ ⚠️ TVA Q4 2024 - Garage Central          | 20/01 | À faire  ││
│  │ 📋 Comptes annuels - SPRL Tech Solutions | 31/03 | Planifié ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  Navigation:                                                    │
│  - 📊 Tableau de bord                                           │
│  - 👥 Mes clients (liste dossiers)                              │
│  - 📋 Tâches & missions                                         │
│  - 📅 Calendrier échéances                                      │
│  - 📁 Documents centralisés                                     │
│  - 💬 Messagerie clients                                        │
│  - 📈 Rapports cabinet                                          │
│  - ⏱️ Temps & facturation                                       │
│  - ⚙️ Paramètres cabinet                                        │
│  - 👤 Collaborateurs                                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.3 Vue Liste Clients (Cabinet)

```
┌─────────────────────────────────────────────────────────────────┐
│  Mes Clients                          [+ Nouveau client] [🔍]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Filtres: [Tous ▼] [Actifs ▼] [Responsable ▼] [Services ▼]    │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ 🏢 Boulangerie Martin SPRL                                  ││
│  │    BE0123.456.789 | Liège | Tenue + TVA + Comptes          ││
│  │    👤 Jean Dupuis | ⚠️ 2 tâches en retard                   ││
│  │    [Ouvrir dossier] [Messagerie] [Documents]               ││
│  ├─────────────────────────────────────────────────────────────┤│
│  │ 🏢 Garage Central SA                                        ││
│  │    BE0987.654.321 | Namur | Mandat complet                 ││
│  │    👤 Marie Lambert | ✅ À jour                              ││
│  │    [Ouvrir dossier] [Messagerie] [Documents]               ││
│  ├─────────────────────────────────────────────────────────────┤│
│  │ 🏢 Tech Solutions SPRL                                      ││
│  │    BE0456.789.123 | Bruxelles | Conseil                    ││
│  │    👤 Non assigné | 📋 En attente                           ││
│  │    [Ouvrir dossier] [Assigner] [Documents]                 ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  Affichage: 1-20 sur 45 clients                    [< 1 2 3 >] │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.4 Vue Dossier Client (Cabinet)

```
┌─────────────────────────────────────────────────────────────────┐
│  ← Retour | Boulangerie Martin SPRL              [Actions ▼]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Informations client                                         ││
│  │ TVA: BE0123.456.789 | IBAN: BE68 5390 0754 7034            ││
│  │ Contact: Pierre Martin | pierre@boulangerie-martin.be      ││
│  │ Responsable: Jean Dupuis | Mandat: Complet depuis 01/2020  ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  [Comptabilité] [TVA] [Comptes annuels] [Documents] [Messages] │
│                                                                 │
│  ┌─ Comptabilité ───────────────────────────────────────────── ┐│
│  │                                                             ││
│  │  Situation au 31/12/2024:                                   ││
│  │  • Chiffre d'affaires: €456,789                             ││
│  │  • Charges: €398,456                                        ││
│  │  • Résultat provisoire: €58,333                             ││
│  │                                                             ││
│  │  Dernière écriture: 15/12/2024                              ││
│  │  Documents en attente: 12                                   ││
│  │                                                             ││
│  │  [Accéder à la comptabilité] [Importer documents]          ││
│  │                                                             ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  Tâches en cours:                                               │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ ☐ Déclaration TVA Q4 2024        | Échéance: 20/01/2025    ││
│  │ ☐ Clôture comptable 2024         | Échéance: 31/03/2025    ││
│  │ ☑ Rapprochement bancaire 12/2024 | Terminé: 10/01/2025     ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.5 Interface Client (Vue limitée)

```
┌─────────────────────────────────────────────────────────────────┐
│  Boulangerie Martin                     [Pierre Martin ▼]      │
│  Géré par: Cabinet Dupont & Associés                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐               │
│  │  Factures   │ │  Documents  │ │  Messages   │               │
│  │   en cours  │ │  partagés   │ │    (2 new)  │               │
│  │     5       │ │     34      │ │             │               │
│  └─────────────┘ └─────────────┘ └─────────────┘               │
│                                                                 │
│  📨 Nouveau message de votre comptable:                         │
│  "Bonjour, merci de nous envoyer les factures manquantes..."   │
│  [Voir le message]                                              │
│                                                                 │
│  Navigation:                                                    │
│  - 🏠 Accueil                                                   │
│  - 🧾 Mes factures (création/consultation)                      │
│  - 📁 Documents partagés                                        │
│  - 💬 Messagerie comptable                                      │
│  - 📊 Mes rapports                                              │
│  - ⚙️ Paramètres                                                │
│                                                                 │
│  ℹ️ Certaines fonctionnalités sont gérées par votre cabinet.   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Flux Utilisateurs

### 6.1 Inscription Cabinet Expert-Comptable

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Landing    │────▶│  Formulaire  │────▶│ Vérification │
│   Page EC    │     │ inscription  │     │    ITAA      │
└──────────────┘     └──────────────┘     └──────────────┘
                                                  │
                                                  ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Dashboard   │◀────│   Choix du   │◀────│  Validation  │
│   Cabinet    │     │    plan      │     │   email      │
└──────────────┘     └──────────────┘     └──────────────┘
```

### 6.2 Ajout d'un Client par le Cabinet

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  + Nouveau   │────▶│  Client      │────▶│   Mandat     │
│    Client    │     │  existant?   │     │   config     │
└──────────────┘     └──────────────┘     └──────────────┘
                           │                     │
                    Non    │              ┌──────┴──────┐
                           ▼              ▼             ▼
                    ┌──────────────┐ ┌─────────┐ ┌─────────┐
                    │   Création   │ │ Inviter │ │ Assigner│
                    │  entreprise  │ │ client  │ │ équipe  │
                    └──────────────┘ └─────────┘ └─────────┘
                           │              │             │
                           └──────────────┴─────────────┘
                                          │
                                          ▼
                                   ┌──────────────┐
                                   │   Dossier    │
                                   │    actif     │
                                   └──────────────┘
```

### 6.3 Travail sur Dossier Client

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Sélection  │────▶│    Switch    │────▶│  Travail en  │
│    client    │     │   contexte   │     │  mode client │
└──────────────┘     └──────────────┘     └──────────────┘
                                                  │
        ┌─────────────────────────────────────────┤
        │                    │                    │
        ▼                    ▼                    ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Comptabilité│     │     TVA      │     │   Documents  │
│    client    │     │   client     │     │    client    │
└──────────────┘     └──────────────┘     └──────────────┘
        │                    │                    │
        └─────────────────────┬───────────────────┘
                              │
                              ▼
                       ┌──────────────┐
                       │  Logging     │
                       │  activité    │
                       └──────────────┘
```

---

## 7. API Endpoints

### 7.1 API Superadmin

```
# Gestion plateforme
GET    /api/admin/stats                    # Statistiques globales
GET    /api/admin/companies                # Liste entreprises
GET    /api/admin/accounting-firms         # Liste cabinets
GET    /api/admin/users                    # Liste utilisateurs
POST   /api/admin/impersonate/{user}       # Connexion en tant que

# Configuration
GET    /api/admin/settings                 # Paramètres plateforme
PUT    /api/admin/settings                 # Mise à jour paramètres
POST   /api/admin/cache/clear              # Vider cache
```

### 7.2 API Cabinet

```
# Gestion cabinet
GET    /api/firm                           # Infos cabinet
PUT    /api/firm                           # Mise à jour cabinet
GET    /api/firm/stats                     # Statistiques cabinet

# Collaborateurs
GET    /api/firm/users                     # Liste collaborateurs
POST   /api/firm/users                     # Ajouter collaborateur
PUT    /api/firm/users/{user}              # Modifier collaborateur
DELETE /api/firm/users/{user}              # Retirer collaborateur

# Clients/Mandats
GET    /api/firm/clients                   # Liste clients
POST   /api/firm/clients                   # Créer client
GET    /api/firm/clients/{client}          # Détails client
PUT    /api/firm/clients/{client}          # Modifier client
DELETE /api/firm/clients/{client}          # Supprimer mandat

# Mandat
GET    /api/firm/clients/{client}/mandate  # Détails mandat
PUT    /api/firm/clients/{client}/mandate  # Modifier mandat

# Tâches
GET    /api/firm/tasks                     # Toutes les tâches
GET    /api/firm/clients/{client}/tasks    # Tâches client
POST   /api/firm/clients/{client}/tasks    # Créer tâche
PUT    /api/firm/tasks/{task}              # Modifier tâche
DELETE /api/firm/tasks/{task}              # Supprimer tâche

# Documents
GET    /api/firm/clients/{client}/documents
POST   /api/firm/clients/{client}/documents
DELETE /api/firm/documents/{document}

# Communications
GET    /api/firm/clients/{client}/messages
POST   /api/firm/clients/{client}/messages

# Activités & temps
GET    /api/firm/activities                # Log activités
POST   /api/firm/time-entries              # Saisie temps
GET    /api/firm/time-entries              # Liste temps

# Rapports
GET    /api/firm/reports/clients           # Rapport clients
GET    /api/firm/reports/tasks             # Rapport tâches
GET    /api/firm/reports/time              # Rapport temps
GET    /api/firm/reports/revenue           # Rapport revenus
```

### 7.3 API Context Switching

```
# Changement de contexte
POST   /api/context/switch                 # Switch vers client
GET    /api/context/current                # Contexte actuel
POST   /api/context/return                 # Retour au cabinet

# Payload switch:
{
    "type": "client",           // client, firm, company
    "id": "uuid-du-client",
    "access_mode": "full"       // full, limited, readonly
}
```

---

## 8. Abonnements et Tarification

### 8.1 Plans Version Entreprise

| Plan | Prix/mois | Factures | Clients | Utilisateurs | Fonctionnalités |
|------|-----------|----------|---------|--------------|-----------------|
| **Starter** | €9 | 20/mois | 25 | 1 | Base |
| **Pro** | €29 | Illimité | 100 | 3 | + Peppol, Rapports |
| **Business** | €59 | Illimité | Illimité | 10 | + API, Multi-devise |

### 8.2 Plans Version Expert-Comptable

| Plan | Prix/mois | Clients | Collaborateurs | Fonctionnalités |
|------|-----------|---------|----------------|-----------------|
| **EC Starter** | €49 | 10 | 2 | Gestion dossiers, TVA |
| **EC Pro** | €99 | 30 | 5 | + Comptes annuels, Temps |
| **EC Business** | €199 | 75 | 15 | + API, White-label |
| **EC Enterprise** | Sur devis | Illimité | Illimité | + SLA, Support dédié |

### 8.3 Options Additionnelles

| Option | Prix |
|--------|------|
| Client supplémentaire | €3/client/mois |
| Collaborateur supplémentaire | €15/user/mois |
| Stockage supplémentaire (10 GB) | €5/mois |
| API calls (au-delà du quota) | €0.01/call |
| White-label complet | €50/mois |
| Formation (2h) | €150 one-time |

---

## 9. Sécurité

### 9.1 Isolation des Données

```php
// Middleware pour isolation cabinet/client
class FirmContextMiddleware
{
    public function handle($request, Closure $next)
    {
        // Vérifier le contexte actuel
        $context = session('firm_context');

        if ($context && $context['type'] === 'client') {
            // Appliquer le scope du client
            Company::addGlobalScope('client', function ($query) use ($context) {
                $query->where('id', $context['client_id']);
            });

            // Vérifier le mandat actif
            $mandate = ClientMandate::where('company_id', $context['client_id'])
                ->where('accounting_firm_id', auth()->user()->accounting_firm_id)
                ->where('status', 'active')
                ->firstOrFail();

            // Appliquer les restrictions du mandat
            View::share('mandatePermissions', $mandate->services);
        }

        return $next($request);
    }
}
```

### 9.2 Audit Trail

```php
// Toutes les actions sur dossiers clients sont loggées
class AuditService
{
    public function logActivity(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        MandateActivity::create([
            'client_mandate_id' => $this->getCurrentMandateId(),
            'user_id' => auth()->id(),
            'activity_type' => $action,
            'description' => $this->generateDescription($action, $model),
            'metadata' => [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
```

### 9.3 Chiffrement

- Toutes les données sensibles (numéros bancaires, credentials API) sont chiffrées
- Communications inter-cabinets/clients via canaux sécurisés
- Documents stockés avec chiffrement at-rest

---

## 10. Migrations Laravel

### 10.1 Migration Cabinets

```php
// database/migrations/2025_01_01_000001_create_accounting_firms_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_firms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_form', 50)->nullable();

            // Professional identification
            $table->string('itaa_number', 50)->nullable()->index();
            $table->string('ire_number', 50)->nullable();
            $table->string('vat_number', 20)->index();
            $table->string('enterprise_number', 20)->nullable();

            // Address
            $table->string('street')->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('box', 10)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city', 100)->nullable();
            $table->char('country_code', 2)->default('BE');

            // Contact
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();

            // Branding
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#3B82F6');

            // Peppol
            $table->string('peppol_id', 100)->nullable();
            $table->string('peppol_provider', 50)->nullable();
            $table->text('peppol_api_key')->nullable();
            $table->text('peppol_api_secret')->nullable();
            $table->boolean('peppol_test_mode')->default(true);

            // Subscription
            $table->foreignUuid('subscription_plan_id')->nullable()->constrained();
            $table->string('subscription_status', 20)->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_clients')->default(10);
            $table->integer('max_users')->default(5);

            // Settings
            $table->json('settings')->nullable();
            $table->json('features')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
```

### 10.2 Migration Mandats

```php
// database/migrations/2025_01_01_000003_create_client_mandates_table.php

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_mandates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accounting_firm_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();

            // Mandate type
            $table->string('mandate_type', 50)->default('full');
            $table->string('status', 20)->default('active');

            // Period
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Team
            $table->foreignUuid('manager_user_id')->nullable()->constrained('users');
            $table->json('assigned_users')->nullable();

            // Services
            $table->json('services')->nullable();

            // Billing
            $table->string('billing_type', 20)->default('monthly');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->decimal('annual_fee', 10, 2)->nullable();

            // Client access
            $table->boolean('client_can_view')->default(true);
            $table->boolean('client_can_edit')->default(false);
            $table->boolean('client_can_validate')->default(false);

            // Notes
            $table->text('internal_notes')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['accounting_firm_id', 'company_id']);
        });
    }
};
```

---

## 11. Planning de Développement

### Phase 1: Fondations (4-6 semaines)
- [ ] Création des migrations et modèles
- [ ] Système de permissions multi-niveaux
- [ ] Authentification et contexte switching
- [ ] Interface superadmin de base

### Phase 2: Module Cabinet (6-8 semaines)
- [ ] Inscription et onboarding cabinet
- [ ] Gestion des collaborateurs
- [ ] Dashboard cabinet
- [ ] Gestion des mandats clients

### Phase 3: Fonctionnalités Métier (8-10 semaines)
- [ ] Travail sur dossiers clients
- [ ] Système de tâches et échéances
- [ ] Gestion documentaire centralisée
- [ ] Messagerie cabinet-client

### Phase 4: Facturation et Rapports (4-6 semaines)
- [ ] Saisie des temps
- [ ] Facturation des prestations
- [ ] Rapports cabinet
- [ ] Rapports consolidés clients

### Phase 5: Polissage (2-4 semaines)
- [ ] Tests et corrections
- [ ] Documentation
- [ ] Formation et support
- [ ] Lancement beta

---

## 12. Annexes

### 12.1 Glossaire

| Terme | Définition |
|-------|------------|
| **Cabinet** | Entreprise d'expertise comptable (accounting firm) |
| **Mandat** | Contrat de mission entre cabinet et client |
| **Dossier** | Ensemble des données comptables d'un client |
| **ITAA** | Institut des conseillers fiscaux et experts-comptables |
| **IRE** | Institut des Réviseurs d'Entreprises |

### 12.2 Références

- Loi du 17 mars 2019 relative aux professions d'expert-comptable et de conseiller fiscal
- Norme ISQC 1 (Contrôle qualité)
- RGPD - Règlement général sur la protection des données
- Peppol BIS Billing 3.0

---

**Document rédigé par:** Claude AI
**Pour:** ComptaBE Development Team
**Dernière mise à jour:** 19/12/2025
