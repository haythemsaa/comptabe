# Spécification Détaillée - Application de Comptabilité Belge
## Conforme aux exigences 2026 (Peppol B2B obligatoire)

---

## 1. CONTEXTE RÉGLEMENTAIRE BELGIQUE 2026-2028

### 1.1 Calendrier des Obligations

| Date | Obligation | Impact |
|------|------------|--------|
| **1er janvier 2026** | Facturation électronique B2B obligatoire | Toutes les entreprises TVA belges |
| **1er janvier 2028** | e-Reporting temps quasi réel | Transmission automatique données TVA |
| **1er janvier 2028** | Fin régime forfaitaire | Dernières exceptions supprimées |

### 1.2 Qui est Concerné ?

**Entreprises OBLIGÉES :**
- Toutes les entreprises assujetties à la TVA en Belgique
- Indépendants, PME et grandes entreprises
- Régime franchise petites entreprises (< 25.000€ CA)
- Régime agricole particulier

**EXCEPTIONS :**
- Factures B2C (clients privés)
- Entreprises en faillite
- Activités exonérées TVA (Article 44 : médical, éducatif)
- Assujettis non-établis sans établissement stable en Belgique
- Régime forfaitaire (jusqu'au 01/01/2028)

### 1.3 Exigences Techniques

```
AVANT 2026 (Non conforme)          APRÈS 2026 (Obligatoire)
┌─────────────────────┐            ┌─────────────────────┐
│   Facture PDF       │     ❌     │   Facture UBL/XML   │
│   Email classique   │  ────────► │   Réseau Peppol     │
│   Facture papier    │            │   Norme EN 16931    │
└─────────────────────┘            └─────────────────────┘
```

**Format obligatoire :** Peppol BIS 3.0 (UBL 2.1 XML)
**Norme européenne :** EN 16931 / CEN/TS 16931
**Réseau :** Peppol (Pan-European Public Procurement OnLine)

### 1.4 Nouvelles Règles TVA 2026

- **Arrondi TVA** : Uniquement sur le montant total par taux de TVA
- **Arrondi ligne par ligne** : INTERDIT pour les e-factures
- **Déduction investissements numériques** : 20% (depuis 01/01/2025)
- **Déduction majorée logiciels** : 120% (2024-2027 pour PME/indépendants)

---

## 2. ARCHITECTURE SYSTÈME

### 2.1 Architecture Globale

```
┌─────────────────────────────────────────────────────────────────────┐
│                         APPLICATION COMPTA                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐              │
│  │   MODULE     │  │   MODULE     │  │   MODULE     │              │
│  │  FACTURATION │  │ COMPTABILITÉ │  │   BANQUE     │              │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘              │
│         │                 │                 │                       │
│         └────────────┬────┴────────────────┘                       │
│                      │                                              │
│              ┌───────▼───────┐                                     │
│              │   CORE API    │                                     │
│              │   (REST)      │                                     │
│              └───────┬───────┘                                     │
│                      │                                              │
├──────────────────────┼──────────────────────────────────────────────┤
│   INTÉGRATIONS       │                                              │
│                      │                                              │
│  ┌───────────────────▼───────────────────┐                         │
│  │         PEPPOL ACCESS POINT           │                         │
│  │  ┌─────────────┐  ┌─────────────┐    │                         │
│  │  │   AS4       │  │   SMP/SML   │    │                         │
│  │  │  Protocol   │  │  Discovery  │    │                         │
│  │  └─────────────┘  └─────────────┘    │                         │
│  └───────────────────────────────────────┘                         │
│                                                                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                │
│  │    CODA     │  │   ISABEL    │  │   BCE/KBO   │                │
│  │   Import    │  │   Connect   │  │    API      │                │
│  └─────────────┘  └─────────────┘  └─────────────┘                │
│                                                                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐                │
│  │  Intervat   │  │  SPF/FOD    │  │  e-Reporting│                │
│  │  (TVA)      │  │  Finances   │  │  (2028)     │                │
│  └─────────────┘  └─────────────┘  └─────────────┘                │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 2.2 Modèle Peppol "5 Coins" (e-Reporting 2028)

```
                         ┌─────────────────┐
                         │   AUTORITÉS     │
                         │   FISCALES      │
                         │  (SPF Finances) │
                         └────────▲────────┘
                                  │
                          5. e-Reporting
                          (temps réel)
                                  │
┌──────────┐    1.     ┌─────────┴────────┐    3.     ┌──────────┐
│          │  Facture  │                  │  Facture  │          │
│ VENDEUR  │──────────►│  PEPPOL NETWORK  │──────────►│ ACHETEUR │
│          │           │                  │           │          │
└────┬─────┘           └──────────────────┘           └────┬─────┘
     │                         ▲   ▲                       │
     │    2.                   │   │                  4.   │
     └────────────────────────►│   │◄──────────────────────┘
         Access Point C1           Access Point C2
         (Corner 2)                (Corner 3)
```

### 2.3 Stack Technologique Recommandé

```yaml
Frontend:
  - Framework: Vue.js 3 / React 18 / Angular 17
  - UI: Tailwind CSS / Vuetify / Material UI
  - PWA: Service Workers pour mode hors-ligne
  - Mobile: React Native / Flutter

Backend:
  - Runtime: Node.js 20 LTS / .NET 8 / PHP 8.3
  - Framework: NestJS / ASP.NET Core / Laravel 11
  - API: REST + GraphQL
  - Auth: OAuth 2.0 / OpenID Connect

Database:
  - Primary: PostgreSQL 16
  - Cache: Redis 7
  - Search: Elasticsearch 8
  - Files: MinIO / AWS S3

Infrastructure:
  - Cloud: AWS / Azure / GCP
  - Containers: Docker + Kubernetes
  - CI/CD: GitHub Actions / GitLab CI
  - Monitoring: Prometheus + Grafana

Peppol:
  - AS4 Gateway: Oxalis / Phase4
  - Format: UBL 2.1 XML
  - Validation: Schematron rules
```

---

## 3. MODULES FONCTIONNELS

### 3.1 Module Facturation

#### 3.1.1 Fonctionnalités

```
┌─────────────────────────────────────────────────────────────────┐
│                    MODULE FACTURATION                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  CRÉATION FACTURES                                             │
│  ├── Factures de vente                                         │
│  ├── Notes de crédit                                           │
│  ├── Factures d'acompte                                        │
│  ├── Factures récurrentes                                      │
│  └── Devis → Facture (conversion)                              │
│                                                                 │
│  FORMATS SUPPORTÉS                                             │
│  ├── UBL 2.1 (Peppol BIS 3.0) ✓ OBLIGATOIRE                   │
│  ├── PDF/A-3 avec XML embarqué                                 │
│  ├── Factur-X / ZUGFeRD                                        │
│  └── Export CSV/Excel                                          │
│                                                                 │
│  ENVOI                                                         │
│  ├── Peppol Network (B2B) ✓ OBLIGATOIRE 2026                  │
│  ├── Email (B2C uniquement)                                    │
│  └── Portail client                                            │
│                                                                 │
│  RÉCEPTION                                                     │
│  ├── Import Peppol automatique                                 │
│  ├── OCR factures (scan/photo)                                 │
│  ├── Import UBL manuel                                         │
│  └── Matching automatique commandes                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 3.1.2 Flux de Création Facture

```
┌─────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌─────────┐
│ Saisie  │───►│Validation│───►│Génération│───►│  Envoi   │───►│ Statut  │
│ données │    │  TVA     │    │   UBL    │    │ Peppol   │    │ livré   │
└─────────┘    └──────────┘    └──────────┘    └──────────┘    └─────────┘
     │              │               │               │               │
     ▼              ▼               ▼               ▼               ▼
┌─────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌─────────┐
│• Client │    │• Calcul  │    │• XML     │    │• Lookup  │    │• MDN    │
│• Lignes │    │  correct │    │  valide  │    │  SMP     │    │  reçu   │
│• TVA    │    │• Arrondi │    │• Schema  │    │• AS4     │    │• Accusé │
│• Dates  │    │  total   │    │  OK      │    │  envoi   │    │  récep. │
└─────────┘    └──────────┘    └──────────┘    └──────────┘    └─────────┘
```

#### 3.1.3 Structure UBL Facture

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">

  <!-- Identification -->
  <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
  <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
  <cbc:ID>INV-2026-00001</cbc:ID>
  <cbc:IssueDate>2026-01-15</cbc:IssueDate>
  <cbc:DueDate>2026-02-15</cbc:DueDate>
  <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
  <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>

  <!-- Vendeur -->
  <cac:AccountingSupplierParty>
    <cac:Party>
      <cbc:EndpointID schemeID="0208">0123456789</cbc:EndpointID>
      <cac:PartyIdentification>
        <cbc:ID schemeID="0208">BE0123456789</cbc:ID>
      </cac:PartyIdentification>
      <cac:PartyName>
        <cbc:Name>Ma Société SPRL</cbc:Name>
      </cac:PartyName>
      <cac:PostalAddress>
        <cbc:StreetName>Rue de la Loi 1</cbc:StreetName>
        <cbc:CityName>Bruxelles</cbc:CityName>
        <cbc:PostalZone>1000</cbc:PostalZone>
        <cac:Country>
          <cbc:IdentificationCode>BE</cbc:IdentificationCode>
        </cac:Country>
      </cac:PostalAddress>
      <cac:PartyTaxScheme>
        <cbc:CompanyID>BE0123456789</cbc:CompanyID>
        <cac:TaxScheme>
          <cbc:ID>VAT</cbc:ID>
        </cac:TaxScheme>
      </cac:PartyTaxScheme>
    </cac:Party>
  </cac:AccountingSupplierParty>

  <!-- Acheteur -->
  <cac:AccountingCustomerParty>
    <!-- Structure similaire -->
  </cac:AccountingCustomerParty>

  <!-- Lignes de facture -->
  <cac:InvoiceLine>
    <cbc:ID>1</cbc:ID>
    <cbc:InvoicedQuantity unitCode="C62">10</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount currencyID="EUR">1000.00</cbc:LineExtensionAmount>
    <cac:Item>
      <cbc:Description>Service de consultation</cbc:Description>
      <cbc:Name>Consultation</cbc:Name>
      <cac:ClassifiedTaxCategory>
        <cbc:ID>S</cbc:ID>
        <cbc:Percent>21</cbc:Percent>
        <cac:TaxScheme>
          <cbc:ID>VAT</cbc:ID>
        </cac:TaxScheme>
      </cac:ClassifiedTaxCategory>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount currencyID="EUR">100.00</cbc:PriceAmount>
    </cac:Price>
  </cac:InvoiceLine>

  <!-- Totaux TVA (arrondi au niveau total uniquement) -->
  <cac:TaxTotal>
    <cbc:TaxAmount currencyID="EUR">210.00</cbc:TaxAmount>
    <cac:TaxSubtotal>
      <cbc:TaxableAmount currencyID="EUR">1000.00</cbc:TaxableAmount>
      <cbc:TaxAmount currencyID="EUR">210.00</cbc:TaxAmount>
      <cac:TaxCategory>
        <cbc:ID>S</cbc:ID>
        <cbc:Percent>21</cbc:Percent>
        <cac:TaxScheme>
          <cbc:ID>VAT</cbc:ID>
        </cac:TaxScheme>
      </cac:TaxCategory>
    </cac:TaxSubtotal>
  </cac:TaxTotal>

  <!-- Montant total -->
  <cac:LegalMonetaryTotal>
    <cbc:LineExtensionAmount currencyID="EUR">1000.00</cbc:LineExtensionAmount>
    <cbc:TaxExclusiveAmount currencyID="EUR">1000.00</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount currencyID="EUR">1210.00</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount currencyID="EUR">1210.00</cbc:PayableAmount>
  </cac:LegalMonetaryTotal>

</Invoice>
```

### 3.2 Module Comptabilité

#### 3.2.1 Fonctionnalités

```
┌─────────────────────────────────────────────────────────────────┐
│                    MODULE COMPTABILITÉ                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  PLAN COMPTABLE                                                │
│  ├── PCMN Belge (Plan Comptable Minimum Normalisé)            │
│  ├── Plans personnalisés par secteur                          │
│  └── Comptes analytiques                                       │
│                                                                 │
│  ÉCRITURES                                                     │
│  ├── Saisie manuelle                                          │
│  ├── Import automatique (Peppol, CODA)                        │
│  ├── Écritures récurrentes                                    │
│  ├── Écritures de clôture                                     │
│  └── Contre-passations                                         │
│                                                                 │
│  JOURNAUX                                                      │
│  ├── Achats                                                    │
│  ├── Ventes                                                    │
│  ├── Financier (banque)                                       │
│  ├── Opérations diverses                                      │
│  └── Ouverture/Clôture                                        │
│                                                                 │
│  TVA BELGE                                                     │
│  ├── Calcul automatique grilles TVA                           │
│  ├── Déclaration périodique (mensuelle/trimestrielle)         │
│  ├── Listing clients                                          │
│  ├── Listing intracommunautaire                               │
│  └── Export Intervat XML                                       │
│                                                                 │
│  RAPPORTS                                                      │
│  ├── Balance                                                   │
│  ├── Grand livre                                               │
│  ├── Bilan                                                     │
│  ├── Compte de résultats                                      │
│  ├── Annexes BNB                                               │
│  └── Rapports personnalisés                                   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 3.2.2 Schéma de Données Comptables

```sql
-- Tables principales comptabilité

CREATE TABLE fiscal_years (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'open', -- open, closed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chart_of_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id),
    account_number VARCHAR(10) NOT NULL,
    account_name VARCHAR(255) NOT NULL,
    account_type VARCHAR(50) NOT NULL, -- asset, liability, equity, revenue, expense
    parent_account_id UUID REFERENCES chart_of_accounts(id),
    vat_code VARCHAR(10),
    is_active BOOLEAN DEFAULT true,
    UNIQUE(company_id, account_number)
);

CREATE TABLE journals (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id),
    code VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL, -- purchases, sales, bank, misc, opening
    default_account_id UUID REFERENCES chart_of_accounts(id),
    UNIQUE(company_id, code)
);

CREATE TABLE journal_entries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id),
    journal_id UUID REFERENCES journals(id),
    fiscal_year_id UUID REFERENCES fiscal_years(id),
    entry_number VARCHAR(50) NOT NULL,
    entry_date DATE NOT NULL,
    description TEXT,
    reference VARCHAR(100),
    source_document_type VARCHAR(50), -- invoice, bank_statement, manual
    source_document_id UUID,
    status VARCHAR(20) DEFAULT 'draft', -- draft, posted, reversed
    created_by UUID REFERENCES users(id),
    posted_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, journal_id, entry_number)
);

CREATE TABLE journal_entry_lines (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    journal_entry_id UUID REFERENCES journal_entries(id),
    line_number INTEGER NOT NULL,
    account_id UUID REFERENCES chart_of_accounts(id),
    partner_id UUID REFERENCES partners(id),
    description TEXT,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    vat_code VARCHAR(10),
    vat_amount DECIMAL(15,2),
    vat_base DECIMAL(15,2),
    analytic_account_id UUID,
    reconciliation_id UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_debit_credit CHECK (
        (debit > 0 AND credit = 0) OR (debit = 0 AND credit > 0) OR (debit = 0 AND credit = 0)
    )
);

-- Index pour performance
CREATE INDEX idx_entries_date ON journal_entries(entry_date);
CREATE INDEX idx_entries_company ON journal_entries(company_id);
CREATE INDEX idx_lines_account ON journal_entry_lines(account_id);
CREATE INDEX idx_lines_partner ON journal_entry_lines(partner_id);
```

### 3.3 Module Banque (CODA)

#### 3.3.1 Fonctionnalités

```
┌─────────────────────────────────────────────────────────────────┐
│                    MODULE BANQUE                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  IMPORT RELEVÉS                                                │
│  ├── CODA (format belge standard)                             │
│  ├── MT940 (SWIFT)                                             │
│  ├── CAMT.053 (ISO 20022)                                     │
│  └── CSV bancaire                                              │
│                                                                 │
│  RÉCONCILIATION                                                │
│  ├── Matching automatique factures                            │
│  ├── Règles de réconciliation personnalisées                  │
│  ├── Propositions intelligentes (ML)                          │
│  └── Réconciliation manuelle                                  │
│                                                                 │
│  PAIEMENTS                                                     │
│  ├── Génération fichiers SEPA                                 │
│  ├── Paiements fournisseurs batch                             │
│  ├── Virements internes                                       │
│  └── Domiciliations                                            │
│                                                                 │
│  TRÉSORERIE                                                    │
│  ├── Soldes temps réel                                        │
│  ├── Prévisions de trésorerie                                 │
│  └── Alertes seuils                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 3.3.2 Parser CODA

```javascript
// Exemple de parser CODA simplifié
class CODAParser {
  parse(codaContent) {
    const lines = codaContent.split('\n');
    const statements = [];
    let currentStatement = null;
    let currentTransaction = null;

    for (const line of lines) {
      const recordType = line.substring(0, 1);

      switch (recordType) {
        case '0': // Header
          currentStatement = this.parseHeader(line);
          break;
        case '1': // Old balance
          currentStatement.openingBalance = this.parseOldBalance(line);
          break;
        case '2': // Movement
          const movementType = line.substring(1, 2);
          if (movementType === '1') {
            currentTransaction = this.parseMovement21(line);
            currentStatement.transactions.push(currentTransaction);
          } else if (movementType === '2') {
            this.parseMovement22(line, currentTransaction);
          } else if (movementType === '3') {
            this.parseMovement23(line, currentTransaction);
          }
          break;
        case '3': // Information
          this.parseInformation(line, currentTransaction);
          break;
        case '8': // New balance
          currentStatement.closingBalance = this.parseNewBalance(line);
          break;
        case '9': // Trailer
          statements.push(currentStatement);
          break;
      }
    }

    return statements;
  }

  parseHeader(line) {
    return {
      creationDate: this.parseDate(line.substring(5, 11)),
      bankId: line.substring(11, 14).trim(),
      duplicate: line.substring(16, 17) === 'D',
      reference: line.substring(24, 34).trim(),
      transactions: []
    };
  }

  parseMovement21(line) {
    return {
      sequenceNumber: line.substring(2, 6),
      detailNumber: line.substring(6, 10),
      bankReference: line.substring(10, 31).trim(),
      amount: this.parseAmount(line.substring(32, 47)),
      valueDate: this.parseDate(line.substring(47, 53)),
      transactionCode: line.substring(53, 61),
      communication: '',
      structuredCommunication: null
    };
  }

  parseAmount(amountStr) {
    const sign = amountStr.substring(0, 1) === '1' ? -1 : 1;
    const value = parseInt(amountStr.substring(1), 10) / 1000;
    return sign * value;
  }

  parseDate(dateStr) {
    const day = dateStr.substring(0, 2);
    const month = dateStr.substring(2, 4);
    const year = '20' + dateStr.substring(4, 6);
    return new Date(`${year}-${month}-${day}`);
  }
}
```

### 3.4 Module Publication Automatique Comptable

#### 3.4.1 Concept "Click & Book"

```
┌─────────────────────────────────────────────────────────────────┐
│           PUBLICATION AUTOMATIQUE AU COMPTABLE                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ENTREPRENEUR                         COMPTABLE                 │
│  ┌─────────────┐                     ┌─────────────┐           │
│  │             │                     │             │           │
│  │  • Scan     │    SYNCHRONISATION  │  • Révision │           │
│  │  • Photo    │    TEMPS RÉEL       │  • Validation│          │
│  │  • Email    │ ◄───────────────────►│  • Écriture │          │
│  │  • Peppol   │                     │  • Conseil  │           │
│  │             │                     │             │           │
│  └─────────────┘                     └─────────────┘           │
│                                                                 │
│  FLUX AUTOMATISÉ:                                              │
│                                                                 │
│  1. Document reçu (Peppol/scan/email)                         │
│  2. OCR + extraction données                                   │
│  3. Catégorisation automatique (ML)                           │
│  4. Proposition d'écriture comptable                          │
│  5. Notification comptable                                     │
│  6. Validation/correction comptable                           │
│  7. Comptabilisation finale                                   │
│                                                                 │
│  NIVEAUX DE COLLABORATION:                                     │
│  ├── Full Service : comptable fait tout                       │
│  ├── Collaboratif : entrepreneur encode, comptable valide     │
│  └── Self-Service : entrepreneur autonome, comptable consulte │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### 3.4.2 API Synchronisation Comptable

```typescript
// Types pour la synchronisation comptable
interface DocumentTransmission {
  id: string;
  companyId: string;
  accountantId: string;
  documentType: 'invoice_in' | 'invoice_out' | 'bank_statement' | 'expense' | 'other';
  status: 'pending' | 'received' | 'processed' | 'rejected';

  // Document original
  originalFile: {
    url: string;
    mimeType: string;
    filename: string;
  };

  // Données extraites
  extractedData: {
    supplierName?: string;
    supplierVat?: string;
    invoiceNumber?: string;
    invoiceDate?: Date;
    dueDate?: Date;
    totalExclVat?: number;
    totalVat?: number;
    totalInclVat?: number;
    lines?: InvoiceLine[];
    confidence: number; // 0-100
  };

  // Proposition comptable
  proposedEntry?: {
    journalCode: string;
    entries: JournalEntryLine[];
    vatCode: string;
  };

  // Feedback comptable
  accountantFeedback?: {
    status: 'approved' | 'modified' | 'rejected';
    modifications?: any;
    comment?: string;
    processedAt: Date;
    processedBy: string;
  };

  timestamps: {
    created: Date;
    transmitted: Date;
    received?: Date;
    processed?: Date;
  };
}

// Service de synchronisation
class AccountantSyncService {

  async transmitDocument(doc: Document, companyId: string): Promise<DocumentTransmission> {
    // 1. Upload document
    const fileUrl = await this.storage.upload(doc.file);

    // 2. OCR et extraction
    const extractedData = await this.ocrService.extract(fileUrl);

    // 3. Catégorisation ML
    const category = await this.mlService.categorize(extractedData);

    // 4. Génération proposition comptable
    const proposedEntry = await this.generateAccountingProposal(extractedData, category);

    // 5. Création transmission
    const transmission = await this.db.documentTransmissions.create({
      companyId,
      accountantId: await this.getAccountantId(companyId),
      documentType: category,
      status: 'pending',
      originalFile: { url: fileUrl, mimeType: doc.mimeType, filename: doc.filename },
      extractedData,
      proposedEntry,
      timestamps: { created: new Date(), transmitted: new Date() }
    });

    // 6. Notification temps réel
    await this.notifyAccountant(transmission);

    return transmission;
  }

  async processAccountantDecision(
    transmissionId: string,
    decision: AccountantDecision
  ): Promise<void> {
    const transmission = await this.db.documentTransmissions.findById(transmissionId);

    if (decision.status === 'approved') {
      // Créer l'écriture comptable
      await this.createJournalEntry(transmission.proposedEntry);
    } else if (decision.status === 'modified') {
      // Créer l'écriture modifiée
      await this.createJournalEntry(decision.modifications);
    }

    // Mettre à jour la transmission
    await this.db.documentTransmissions.update(transmissionId, {
      status: 'processed',
      accountantFeedback: {
        status: decision.status,
        modifications: decision.modifications,
        comment: decision.comment,
        processedAt: new Date(),
        processedBy: decision.accountantId
      },
      'timestamps.processed': new Date()
    });

    // Notifier l'entrepreneur
    await this.notifyEntrepreneur(transmission, decision);
  }
}
```

### 3.5 Module e-Reporting (2028)

#### 3.5.1 Architecture e-Reporting

```
┌─────────────────────────────────────────────────────────────────┐
│                    e-REPORTING 2028                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  PRINCIPE: Transmission automatique des données TVA            │
│            aux autorités fiscales en temps quasi réel          │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                    FLUX DE DONNÉES                       │   │
│  │                                                          │   │
│  │   Facture émise                                         │   │
│  │        │                                                 │   │
│  │        ▼                                                 │   │
│  │   ┌──────────┐    ┌──────────┐    ┌──────────┐         │   │
│  │   │ Logiciel │───►│  Peppol  │───►│   SPF    │         │   │
│  │   │ Compta   │    │ Access   │    │ Finances │         │   │
│  │   │          │    │  Point   │    │          │         │   │
│  │   └──────────┘    └──────────┘    └──────────┘         │   │
│  │        │                               │                │   │
│  │        │      Données transmises:      │                │   │
│  │        │      • Numéro facture         │                │   │
│  │        │      • Date                   │                │   │
│  │        │      • TVA vendeur/acheteur   │                │   │
│  │        │      • Montants TVA           │                │   │
│  │        │      • Code nature opération  │                │   │
│  │        │                               │                │   │
│  │        ▼                               ▼                │   │
│  │   Remplacement automatique de:                         │   │
│  │   • Listing clients annuel                             │   │
│  │   • Déclaration intracommunautaire                     │   │
│  │   • Parties de la déclaration TVA                      │   │
│  │                                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  AVANTAGES:                                                    │
│  ├── Moins de déclarations manuelles                          │
│  ├── Détection fraude TVA améliorée                           │
│  ├── Pré-remplissage déclarations                             │
│  └── Contrôles fiscaux facilités                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. INTÉGRATION PEPPOL

### 4.1 Devenir Access Point Peppol

```
┌─────────────────────────────────────────────────────────────────┐
│               OPTIONS D'INTÉGRATION PEPPOL                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  OPTION 1: Utiliser un Access Point existant (RECOMMANDÉ)      │
│  ├── Coût: Abonnement mensuel + frais par document             │
│  ├── Avantages: Rapide, certifié, maintenance incluse          │
│  ├── Fournisseurs BE: Billit, CodaBox, Basware, Unifiedpost   │
│  └── Intégration: API REST                                     │
│                                                                 │
│  OPTION 2: Devenir Access Point                                │
│  ├── Coût: €15,000-50,000 setup + maintenance                 │
│  ├── Avantages: Contrôle total, pas de dépendance             │
│  ├── Exigences:                                                │
│  │   • Certification OpenPeppol                                │
│  │   • Infrastructure AS4 (Oxalis/Phase4)                     │
│  │   • SLA 99.9% uptime                                        │
│  │   • Audit annuel                                            │
│  └── Délai: 3-6 mois                                          │
│                                                                 │
│  OPTION 3: Hybrid (recommandé pour scale)                      │
│  ├── Démarrer avec AP tiers                                   │
│  ├── Évoluer vers propre AP si volume justifie               │
│  └── Garder AP tiers en backup                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Intégration via API Access Point

```typescript
// Service d'intégration Peppol via Access Point tiers
class PeppolService {
  private accessPointApi: AccessPointClient;

  constructor(config: PeppolConfig) {
    this.accessPointApi = new AccessPointClient({
      baseUrl: config.accessPointUrl,
      apiKey: config.apiKey,
      participantId: config.peppolId // Format: 0208:BE0123456789
    });
  }

  /**
   * Envoyer une facture via Peppol
   */
  async sendInvoice(invoice: Invoice): Promise<PeppolTransmission> {
    // 1. Générer UBL
    const ublXml = this.generateUBL(invoice);

    // 2. Valider UBL
    const validation = await this.validateUBL(ublXml);
    if (!validation.isValid) {
      throw new ValidationError(validation.errors);
    }

    // 3. Lookup destinataire dans SMP
    const recipientEndpoint = await this.lookupRecipient(
      invoice.customer.peppolId,
      'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice'
    );

    // 4. Envoyer via Access Point
    const transmission = await this.accessPointApi.send({
      document: ublXml,
      documentType: 'invoice',
      sender: this.config.peppolId,
      receiver: invoice.customer.peppolId,
      receiverEndpoint: recipientEndpoint
    });

    // 5. Stocker référence
    await this.storeTransmission(invoice.id, transmission);

    return transmission;
  }

  /**
   * Recevoir les factures entrantes
   */
  async receiveInvoices(): Promise<ReceivedInvoice[]> {
    // Polling ou webhook selon AP
    const documents = await this.accessPointApi.getInbox();

    const invoices: ReceivedInvoice[] = [];

    for (const doc of documents) {
      // Parser UBL
      const parsedInvoice = this.parseUBL(doc.content);

      // Créer facture fournisseur
      const invoice = await this.createSupplierInvoice(parsedInvoice);

      // Marquer comme traité
      await this.accessPointApi.acknowledge(doc.id);

      invoices.push(invoice);
    }

    return invoices;
  }

  /**
   * Générer UBL conforme Peppol BIS 3.0
   */
  private generateUBL(invoice: Invoice): string {
    const builder = new UBLBuilder();

    return builder
      .setCustomizationID('urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0')
      .setProfileID('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
      .setInvoiceNumber(invoice.number)
      .setIssueDate(invoice.date)
      .setDueDate(invoice.dueDate)
      .setCurrency('EUR')
      .setSupplier({
        endpointId: invoice.supplier.peppolId,
        name: invoice.supplier.name,
        vatNumber: invoice.supplier.vatNumber,
        address: invoice.supplier.address
      })
      .setCustomer({
        endpointId: invoice.customer.peppolId,
        name: invoice.customer.name,
        vatNumber: invoice.customer.vatNumber,
        address: invoice.customer.address
      })
      .setLines(invoice.lines.map(line => ({
        id: line.id,
        quantity: line.quantity,
        unitCode: line.unitCode,
        unitPrice: line.unitPrice,
        lineAmount: line.amount,
        description: line.description,
        vatCategory: line.vatCategory,
        vatRate: line.vatRate
      })))
      .setTaxTotal(this.calculateTaxTotal(invoice))
      .setLegalMonetaryTotal({
        lineExtensionAmount: invoice.totalExclVat,
        taxExclusiveAmount: invoice.totalExclVat,
        taxInclusiveAmount: invoice.totalInclVat,
        payableAmount: invoice.totalInclVat
      })
      .build();
  }

  /**
   * Calculer totaux TVA (arrondi au niveau total uniquement - règle 2026)
   */
  private calculateTaxTotal(invoice: Invoice): TaxTotal[] {
    const taxByRate = new Map<number, { base: number; amount: number }>();

    // Grouper par taux
    for (const line of invoice.lines) {
      const current = taxByRate.get(line.vatRate) || { base: 0, amount: 0 };
      current.base += line.amount;
      taxByRate.set(line.vatRate, current);
    }

    // Calculer TVA et arrondir au niveau du total par taux (règle 2026)
    const taxTotals: TaxTotal[] = [];
    for (const [rate, values] of taxByRate) {
      const taxAmount = Math.round(values.base * rate) / 100;
      taxTotals.push({
        taxableAmount: values.base,
        taxAmount: taxAmount, // Arrondi au centime
        taxCategory: this.getTaxCategory(rate),
        taxRate: rate
      });
    }

    return taxTotals;
  }
}
```

---

## 5. SÉCURITÉ ET CONFORMITÉ

### 5.1 Exigences Sécurité

```
┌─────────────────────────────────────────────────────────────────┐
│                    SÉCURITÉ APPLICATION                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  AUTHENTIFICATION                                              │
│  ├── Multi-factor authentication (MFA) obligatoire            │
│  ├── eID belge / itsme integration                            │
│  ├── SSO pour comptables (SAML/OIDC)                          │
│  └── Session timeout configurable                              │
│                                                                 │
│  AUTORISATION                                                  │
│  ├── RBAC (Role-Based Access Control)                         │
│  ├── Rôles: Admin, Comptable, Entrepreneur, Lecture seule    │
│  ├── Permissions granulaires par module                       │
│  └── Audit trail complet                                       │
│                                                                 │
│  CHIFFREMENT                                                   │
│  ├── TLS 1.3 en transit                                       │
│  ├── AES-256 au repos                                         │
│  ├── Chiffrement base de données                              │
│  └── Clés gérées via HSM/KMS                                  │
│                                                                 │
│  CONFORMITÉ                                                    │
│  ├── RGPD / GDPR                                              │
│  ├── Conservation données 7 ans (légal BE)                    │
│  ├── Droit à l'oubli (avec exceptions légales)               │
│  └── DPO désigné                                               │
│                                                                 │
│  INFRASTRUCTURE                                                │
│  ├── Hébergement EU (RGPD)                                    │
│  ├── Backup quotidien, rétention 30 jours                    │
│  ├── DR site (Recovery < 4h)                                  │
│  └── Pentest annuel                                           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Audit Trail

```typescript
// Système d'audit trail
interface AuditLog {
  id: string;
  timestamp: Date;
  userId: string;
  userEmail: string;
  userRole: string;
  companyId: string;
  action: AuditAction;
  resourceType: string;
  resourceId: string;
  changes?: {
    field: string;
    oldValue: any;
    newValue: any;
  }[];
  ipAddress: string;
  userAgent: string;
  sessionId: string;
}

type AuditAction =
  | 'create'
  | 'read'
  | 'update'
  | 'delete'
  | 'export'
  | 'print'
  | 'send'
  | 'approve'
  | 'reject'
  | 'login'
  | 'logout';

class AuditService {
  async log(event: Omit<AuditLog, 'id' | 'timestamp'>): Promise<void> {
    await this.db.auditLogs.create({
      id: generateUUID(),
      timestamp: new Date(),
      ...event
    });
  }

  // Middleware Express pour audit automatique
  auditMiddleware() {
    return async (req: Request, res: Response, next: NextFunction) => {
      const originalJson = res.json.bind(res);

      res.json = (data: any) => {
        if (res.statusCode < 400) {
          this.log({
            userId: req.user?.id,
            userEmail: req.user?.email,
            userRole: req.user?.role,
            companyId: req.params.companyId || req.body?.companyId,
            action: this.methodToAction(req.method),
            resourceType: this.pathToResourceType(req.path),
            resourceId: req.params.id,
            ipAddress: req.ip,
            userAgent: req.headers['user-agent'],
            sessionId: req.session?.id
          });
        }
        return originalJson(data);
      };

      next();
    };
  }
}
```

---

## 6. SCHÉMA BASE DE DONNÉES COMPLET

```sql
-- =====================================================
-- SCHÉMA BASE DE DONNÉES - APPLICATION COMPTABILITÉ
-- =====================================================

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- =====================================================
-- TABLES PRINCIPALES
-- =====================================================

-- Entreprises
CREATE TABLE companies (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    legal_form VARCHAR(50), -- SPRL, SA, SRL, etc.
    vat_number VARCHAR(20) UNIQUE NOT NULL, -- BE0123456789
    enterprise_number VARCHAR(20), -- 0123.456.789

    -- Adresse
    street VARCHAR(255),
    house_number VARCHAR(20),
    postal_code VARCHAR(10),
    city VARCHAR(100),
    country_code CHAR(2) DEFAULT 'BE',

    -- Peppol
    peppol_id VARCHAR(50), -- 0208:BE0123456789
    peppol_registered BOOLEAN DEFAULT false,

    -- Paramètres comptables
    fiscal_year_start_month INTEGER DEFAULT 1,
    vat_regime VARCHAR(50), -- normal, franchise, forfait
    vat_periodicity VARCHAR(20), -- monthly, quarterly

    -- Comptable assigné
    accountant_id UUID REFERENCES users(id),
    collaboration_level VARCHAR(50), -- full_service, collaborative, self_service

    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true
);

-- Utilisateurs
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255),

    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(50),

    role VARCHAR(50) NOT NULL, -- admin, accountant, entrepreneur, readonly

    -- Authentification
    mfa_enabled BOOLEAN DEFAULT false,
    mfa_secret VARCHAR(255),
    eid_linked BOOLEAN DEFAULT false,
    itsme_linked BOOLEAN DEFAULT false,

    -- Statut
    email_verified BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Association utilisateurs-entreprises
CREATE TABLE user_companies (
    user_id UUID REFERENCES users(id),
    company_id UUID REFERENCES companies(id),
    role VARCHAR(50) NOT NULL, -- owner, admin, user, readonly
    permissions JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, company_id)
);

-- Partenaires (clients/fournisseurs)
CREATE TABLE partners (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),

    type VARCHAR(20) NOT NULL, -- customer, supplier, both

    name VARCHAR(255) NOT NULL,
    vat_number VARCHAR(20),
    enterprise_number VARCHAR(20),

    -- Adresse
    street VARCHAR(255),
    house_number VARCHAR(20),
    postal_code VARCHAR(10),
    city VARCHAR(100),
    country_code CHAR(2) DEFAULT 'BE',

    -- Contact
    email VARCHAR(255),
    phone VARCHAR(50),
    contact_person VARCHAR(255),

    -- Peppol
    peppol_id VARCHAR(50),
    peppol_capable BOOLEAN DEFAULT false,

    -- Comptabilité
    default_account_receivable UUID REFERENCES chart_of_accounts(id),
    default_account_payable UUID REFERENCES chart_of_accounts(id),
    payment_terms_days INTEGER DEFAULT 30,

    -- Métadonnées
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true,

    UNIQUE(company_id, vat_number)
);

-- =====================================================
-- FACTURATION
-- =====================================================

-- Factures
CREATE TABLE invoices (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),
    partner_id UUID REFERENCES partners(id),

    type VARCHAR(20) NOT NULL, -- out (vente), in (achat)
    status VARCHAR(20) DEFAULT 'draft', -- draft, sent, received, paid, cancelled

    invoice_number VARCHAR(50) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,

    -- Montants
    total_excl_vat DECIMAL(15,2) NOT NULL,
    total_vat DECIMAL(15,2) NOT NULL,
    total_incl_vat DECIMAL(15,2) NOT NULL,
    currency CHAR(3) DEFAULT 'EUR',

    -- Peppol
    peppol_message_id VARCHAR(100),
    peppol_status VARCHAR(50), -- pending, sent, delivered, failed
    peppol_sent_at TIMESTAMP,
    peppol_delivered_at TIMESTAMP,

    -- Documents
    original_file_url TEXT,
    ubl_xml TEXT,
    pdf_url TEXT,

    -- Comptabilisation
    journal_entry_id UUID REFERENCES journal_entries(id),
    is_booked BOOLEAN DEFAULT false,
    booked_at TIMESTAMP,
    booked_by UUID REFERENCES users(id),

    -- Communication structurée
    structured_communication VARCHAR(20), -- +++123/4567/89012+++
    payment_reference VARCHAR(100),

    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(company_id, type, invoice_number)
);

-- Lignes de facture
CREATE TABLE invoice_lines (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    invoice_id UUID REFERENCES invoices(id) ON DELETE CASCADE,
    line_number INTEGER NOT NULL,

    description TEXT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_code VARCHAR(10) DEFAULT 'C62', -- UN/ECE Recommendation 20
    unit_price DECIMAL(15,4) NOT NULL,

    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,

    line_amount DECIMAL(15,2) NOT NULL,

    vat_category CHAR(1) NOT NULL, -- S, Z, E, AE, K, G, O, L, M
    vat_rate DECIMAL(5,2) NOT NULL,
    vat_amount DECIMAL(15,2) NOT NULL,

    account_id UUID REFERENCES chart_of_accounts(id),
    analytic_account_id UUID,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(invoice_id, line_number)
);

-- Transmissions Peppol
CREATE TABLE peppol_transmissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),
    invoice_id UUID REFERENCES invoices(id),

    direction VARCHAR(10) NOT NULL, -- outbound, inbound

    sender_id VARCHAR(50) NOT NULL,
    receiver_id VARCHAR(50) NOT NULL,

    document_type VARCHAR(100) NOT NULL,
    message_id VARCHAR(100) UNIQUE,

    status VARCHAR(20) NOT NULL, -- pending, sent, delivered, failed
    error_message TEXT,

    -- Timestamps AS4
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    mdn_received_at TIMESTAMP,

    -- Données brutes
    request_payload TEXT,
    response_payload TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- BANQUE
-- =====================================================

-- Comptes bancaires
CREATE TABLE bank_accounts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),

    iban VARCHAR(34) NOT NULL,
    bic VARCHAR(11),
    bank_name VARCHAR(255),

    account_id UUID REFERENCES chart_of_accounts(id),
    journal_id UUID REFERENCES journals(id),

    -- CODA
    coda_enabled BOOLEAN DEFAULT false,
    coda_contract_number VARCHAR(50),

    is_default BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(company_id, iban)
);

-- Relevés bancaires
CREATE TABLE bank_statements (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    bank_account_id UUID REFERENCES bank_accounts(id),

    statement_number VARCHAR(50),
    statement_date DATE NOT NULL,

    opening_balance DECIMAL(15,2) NOT NULL,
    closing_balance DECIMAL(15,2) NOT NULL,

    total_debit DECIMAL(15,2),
    total_credit DECIMAL(15,2),

    source VARCHAR(20), -- coda, mt940, camt053, manual
    original_file_url TEXT,

    is_processed BOOLEAN DEFAULT false,
    processed_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions bancaires
CREATE TABLE bank_transactions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    bank_statement_id UUID REFERENCES bank_statements(id),
    bank_account_id UUID REFERENCES bank_accounts(id),

    transaction_date DATE NOT NULL,
    value_date DATE,

    amount DECIMAL(15,2) NOT NULL,
    currency CHAR(3) DEFAULT 'EUR',

    counterparty_name VARCHAR(255),
    counterparty_account VARCHAR(34),

    communication TEXT,
    structured_communication VARCHAR(20),

    transaction_code VARCHAR(20),
    bank_reference VARCHAR(100),

    -- Réconciliation
    reconciliation_status VARCHAR(20) DEFAULT 'pending', -- pending, matched, manual, ignored
    matched_invoice_id UUID REFERENCES invoices(id),
    journal_entry_id UUID REFERENCES journal_entries(id),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TVA
-- =====================================================

-- Codes TVA
CREATE TABLE vat_codes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),

    code VARCHAR(10) NOT NULL,
    description VARCHAR(255) NOT NULL,

    rate DECIMAL(5,2) NOT NULL,
    vat_category CHAR(1) NOT NULL, -- S, Z, E, etc.

    -- Grilles déclaration
    grid_base VARCHAR(10), -- 00, 01, 02, 03, etc.
    grid_vat VARCHAR(10), -- 54, 59, 64, etc.

    account_vat_due UUID REFERENCES chart_of_accounts(id),
    account_vat_deductible UUID REFERENCES chart_of_accounts(id),

    is_active BOOLEAN DEFAULT true,

    UNIQUE(company_id, code)
);

-- Déclarations TVA
CREATE TABLE vat_declarations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),

    period_type VARCHAR(20) NOT NULL, -- monthly, quarterly
    period_year INTEGER NOT NULL,
    period_number INTEGER NOT NULL, -- 1-12 or 1-4

    status VARCHAR(20) DEFAULT 'draft', -- draft, validated, submitted, accepted

    -- Montants par grille
    grid_values JSONB NOT NULL,

    total_due DECIMAL(15,2),
    total_deductible DECIMAL(15,2),
    balance DECIMAL(15,2),

    -- Soumission Intervat
    submission_date TIMESTAMP,
    submission_reference VARCHAR(100),
    intervat_response JSONB,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(company_id, period_year, period_number)
);

-- =====================================================
-- SYNCHRONISATION COMPTABLE
-- =====================================================

-- Transmissions documents comptable
CREATE TABLE document_transmissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),
    accountant_id UUID REFERENCES users(id),

    document_type VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',

    original_file_url TEXT,
    original_filename VARCHAR(255),
    original_mime_type VARCHAR(100),

    -- OCR
    extracted_data JSONB,
    extraction_confidence DECIMAL(5,2),

    -- Proposition comptable
    proposed_entry JSONB,

    -- Feedback comptable
    accountant_status VARCHAR(20), -- approved, modified, rejected
    accountant_modifications JSONB,
    accountant_comment TEXT,
    processed_at TIMESTAMP,
    processed_by UUID REFERENCES users(id),

    -- Journal entry créé
    journal_entry_id UUID REFERENCES journal_entries(id),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transmitted_at TIMESTAMP,
    received_at TIMESTAMP
);

-- =====================================================
-- AUDIT
-- =====================================================

CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    user_id UUID REFERENCES users(id),
    user_email VARCHAR(255),
    user_role VARCHAR(50),
    company_id UUID REFERENCES companies(id),

    action VARCHAR(50) NOT NULL,
    resource_type VARCHAR(100) NOT NULL,
    resource_id UUID,

    changes JSONB,

    ip_address INET,
    user_agent TEXT,
    session_id VARCHAR(100)
);

-- Index pour performance audit
CREATE INDEX idx_audit_timestamp ON audit_logs(timestamp);
CREATE INDEX idx_audit_company ON audit_logs(company_id);
CREATE INDEX idx_audit_user ON audit_logs(user_id);
CREATE INDEX idx_audit_resource ON audit_logs(resource_type, resource_id);

-- =====================================================
-- INDEXES
-- =====================================================

CREATE INDEX idx_invoices_company ON invoices(company_id);
CREATE INDEX idx_invoices_partner ON invoices(partner_id);
CREATE INDEX idx_invoices_date ON invoices(invoice_date);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_peppol ON invoices(peppol_message_id);

CREATE INDEX idx_bank_transactions_date ON bank_transactions(transaction_date);
CREATE INDEX idx_bank_transactions_reconciliation ON bank_transactions(reconciliation_status);

CREATE INDEX idx_partners_company ON partners(company_id);
CREATE INDEX idx_partners_vat ON partners(vat_number);
CREATE INDEX idx_partners_peppol ON partners(peppol_id);
```

---

## 7. API REST

### 7.1 Endpoints Principaux

```yaml
# API Facturation
POST   /api/v1/invoices                    # Créer facture
GET    /api/v1/invoices                    # Lister factures
GET    /api/v1/invoices/:id                # Détail facture
PUT    /api/v1/invoices/:id                # Modifier facture
DELETE /api/v1/invoices/:id                # Supprimer facture (draft only)
POST   /api/v1/invoices/:id/send           # Envoyer via Peppol
POST   /api/v1/invoices/:id/book           # Comptabiliser
GET    /api/v1/invoices/:id/ubl            # Télécharger UBL
GET    /api/v1/invoices/:id/pdf            # Télécharger PDF

# API Peppol
GET    /api/v1/peppol/inbox                # Factures reçues
POST   /api/v1/peppol/inbox/:id/process    # Traiter facture reçue
GET    /api/v1/peppol/lookup/:peppolId     # Lookup participant
GET    /api/v1/peppol/transmissions        # Historique transmissions

# API Comptabilité
GET    /api/v1/accounts                    # Plan comptable
POST   /api/v1/entries                     # Créer écriture
GET    /api/v1/entries                     # Lister écritures
POST   /api/v1/entries/:id/post            # Valider écriture
GET    /api/v1/reports/balance             # Balance
GET    /api/v1/reports/ledger              # Grand livre
GET    /api/v1/reports/trial-balance       # Balance de vérification

# API Banque
POST   /api/v1/bank/import                 # Importer CODA/MT940
GET    /api/v1/bank/statements             # Lister relevés
GET    /api/v1/bank/transactions           # Lister transactions
POST   /api/v1/bank/reconcile              # Réconcilier

# API TVA
GET    /api/v1/vat/declarations            # Lister déclarations
POST   /api/v1/vat/declarations            # Créer déclaration
GET    /api/v1/vat/declarations/:id        # Détail
POST   /api/v1/vat/declarations/:id/submit # Soumettre Intervat
GET    /api/v1/vat/client-listing          # Listing clients

# API Synchronisation Comptable
POST   /api/v1/transmissions               # Transmettre document
GET    /api/v1/transmissions               # Lister transmissions
PUT    /api/v1/transmissions/:id/process   # Traitement comptable
```

---

## 8. INTERFACE UTILISATEUR

### 8.1 Wireframes Principaux

```
┌─────────────────────────────────────────────────────────────────────────┐
│  DASHBOARD                                                    [User ▼]  │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐        │
│  │   À RECEVOIR    │  │    À PAYER      │  │   TRÉSORERIE    │        │
│  │                 │  │                 │  │                 │        │
│  │   € 45,230.00   │  │   € 12,450.00   │  │   € 89,120.00   │        │
│  │   ↑ 12%         │  │   ↓ 5%          │  │   ↑ 3%          │        │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘        │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  ACTIONS REQUISES                                                │   │
│  │                                                                  │   │
│  │  ⚠️  3 factures à approuver (comptable)                         │   │
│  │  📥 5 factures Peppol reçues                                    │   │
│  │  🏦 Relevé bancaire à traiter                                   │   │
│  │  📋 Déclaration TVA Q4 à préparer                               │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌────────────────────────────┐  ┌────────────────────────────────┐   │
│  │  DERNIÈRES FACTURES        │  │  FLUX TRÉSORERIE              │   │
│  │                            │  │                                │   │
│  │  INV-2026-0045  €1,210.00 │  │  [═══════════════════════]    │   │
│  │  INV-2026-0044  €3,450.00 │  │                                │   │
│  │  INV-2026-0043    €850.00 │  │  Jan  Fév  Mar  Avr  Mai      │   │
│  │  ...                       │  │                                │   │
│  └────────────────────────────┘  └────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────────────────┐
│  NOUVELLE FACTURE                                          [Annuler]   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  Client *                              N° Facture                       │
│  ┌─────────────────────────────┐      ┌─────────────────────────┐     │
│  │ [Rechercher client...]    ▼│      │ INV-2026-0046           │     │
│  └─────────────────────────────┘      └─────────────────────────┘     │
│  ✓ Client Peppol actif                                                 │
│                                                                         │
│  Date facture *                        Date échéance                    │
│  ┌─────────────────┐                  ┌─────────────────┐             │
│  │ 15/01/2026   📅 │                  │ 15/02/2026   📅 │             │
│  └─────────────────┘                  └─────────────────┘             │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  LIGNES                                                    [+]  │   │
│  ├──────┬─────────────────────────┬───────┬─────────┬──────┬──────┤   │
│  │  #   │  Description            │  Qté  │  P.U.   │ TVA  │ Total│   │
│  ├──────┼─────────────────────────┼───────┼─────────┼──────┼──────┤   │
│  │  1   │  Consultation janvier   │  10   │  100.00 │ 21%  │1000.0│   │
│  │  2   │  Frais déplacement      │   1   │   50.00 │ 21%  │  50.0│   │
│  └──────┴─────────────────────────┴───────┴─────────┴──────┴──────┘   │
│                                                                         │
│                                        Sous-total HT:    € 1,050.00    │
│                                        TVA 21%:            € 220.50    │
│                                        ─────────────────────────────   │
│                                        TOTAL TTC:        € 1,270.50    │
│                                                                         │
│  Communication structurée: +++123/4567/89012+++                        │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │  [Brouillon]     [Prévisualiser PDF]     [Envoyer via Peppol]  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 9. ROADMAP IMPLÉMENTATION

### Phase 1: MVP (Mois 1-3)
- [ ] Setup infrastructure (DB, API, Auth)
- [ ] Module utilisateurs et entreprises
- [ ] Plan comptable PCMN
- [ ] Facturation basique (sans Peppol)
- [ ] Génération PDF

### Phase 2: Conformité Peppol (Mois 4-6)
- [ ] Intégration Access Point Peppol
- [ ] Génération UBL 2.1
- [ ] Réception factures Peppol
- [ ] Validation schémas

### Phase 3: Comptabilité Complète (Mois 7-9)
- [ ] Journaux et écritures
- [ ] Import CODA
- [ ] Réconciliation bancaire
- [ ] Déclarations TVA
- [ ] Export Intervat

### Phase 4: Collaboration (Mois 10-12)
- [ ] Portail comptable
- [ ] Synchronisation temps réel
- [ ] OCR documents
- [ ] Application mobile

### Phase 5: e-Reporting (2027)
- [ ] Préparation architecture 5 coins
- [ ] Tests transmission SPF
- [ ] Certification e-Reporting

---

## 10. RESSOURCES ET RÉFÉRENCES

### Documentation Officielle
- [e-facture.belgium.be](https://efacture.belgium.be)
- [Peppol.eu](https://peppol.eu)
- [SPF Finances](https://finances.belgium.be)

### Standards Techniques
- EN 16931 (Norme européenne facturation)
- UBL 2.1 (OASIS)
- Peppol BIS 3.0
- AS4 Profile

### Outils Open Source
- Oxalis (AS4 Gateway)
- Phase4 (AS4 Implementation)
- Apache CXF (Web Services)

---

*Document créé le 16/12/2024*
*Version 1.0*
