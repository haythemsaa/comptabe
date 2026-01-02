# ComptaBE - Technical Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Project Structure](#project-structure)
3. [Models & Relationships](#models--relationships)
4. [Controllers & Actions](#controllers--actions)
5. [Services](#services)
6. [Database Schema](#database-schema)
7. [External Integrations](#external-integrations)
8. [Architecture Patterns](#architecture-patterns)

---

## Project Overview

**ComptaBE** is a comprehensive Belgian accounting management system built with Laravel, featuring multi-tenancy, Peppol e-invoicing, Open Banking integration, AI-powered document processing, and accounting firm management capabilities.

**Core Technologies:**
- Laravel 11.x (PHP Framework)
- MySQL/MariaDB (Database)
- Inertia.js with Vue.js (Frontend)
- Tailwind CSS (Styling)
- Laravel Sanctum (API Authentication)

**Key Features:**
- Multi-tenant architecture (Company-based)
- Belgian accounting standards (PCMN)
- Peppol network e-invoicing (UBL 2.1)
- Open Banking PSD2 integration (Belgian banks)
- AI-powered OCR for invoice scanning
- Accounting firm/client management
- VAT declaration (Intervat)
- CODA file parsing
- Subscription-based access control

---

## Project Structure

### Main Directories

```
C:\laragon\www\compta\
├── app/
│   ├── Console/          # Artisan commands
│   │   └── Commands/     # Custom CLI commands
│   ├── Helpers/          # Helper functions and utilities
│   ├── Http/
│   │   ├── Controllers/  # Application controllers
│   │   │   ├── Admin/    # Admin panel controllers
│   │   │   └── Api/      # API controllers (v1, partner, invoice, etc.)
│   │   └── Middleware/   # HTTP middleware
│   ├── Jobs/             # Queued jobs
│   ├── Models/           # Eloquent models
│   │   ├── Scopes/       # Global query scopes
│   │   └── Traits/       # Model traits (HasUuid, BelongsToTenant, etc.)
│   ├── Notifications/    # Notification classes
│   ├── Observers/        # Model observers
│   ├── Policies/         # Authorization policies
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic services
│   │   ├── AI/           # AI/ML services (OCR, categorization, forecasting)
│   │   ├── OpenBanking/  # PSD2 integration
│   │   ├── Peppol/       # Peppol e-invoicing services
│   │   ├── Reports/      # Report generation
│   │   ├── Webhook/      # Webhook handling
│   │   └── Workflow/     # Approval workflows
│   ├── Traits/           # Application-wide traits
│   └── View/             # View composers and components
├── bootstrap/            # Application bootstrapping
├── config/               # Configuration files
├── database/
│   ├── factories/        # Model factories for testing
│   ├── migrations/       # Database migrations
│   └── seeders/          # Database seeders
├── docs/                 # Documentation
├── public/               # Public assets
├── resources/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript/Vue components
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # API routes
│   ├── console.php       # Console routes
│   └── web.php           # Web routes
├── storage/              # Logs, cache, uploaded files
├── tests/                # Automated tests
└── vendor/               # Composer dependencies
```

### Purpose of Main Directories

- **app/Models**: Contains all Eloquent models representing database entities
- **app/Http/Controllers**: Handles HTTP requests and returns responses
- **app/Services**: Encapsulates complex business logic (integrations, processing)
- **database/migrations**: Database schema version control
- **resources/js**: Vue.js/Inertia.js frontend components
- **routes/**: Application route definitions (web, API)

---

## Models & Relationships

### Core Entities

#### 1. **User** (`App\Models\User`)
Represents system users (business owners, accountants, collaborators).

**Key Relationships:**
- `belongsToMany(Company)` - Users can access multiple companies
- `belongsToMany(AccountingFirm)` - Users can belong to accounting firms
- `hasMany(Invoice)` - Invoices created by user
- `hasMany(ClientMandate)` - Mandates managed by user (for accountants)
- `hasMany(MandateTask)` - Tasks assigned to user

**Key Fields:**
- `email`, `password`, `first_name`, `last_name`
- `is_superadmin` - System administrator flag
- `user_type` - individual, business, accountant, collaborator
- `professional_title` - Expert-comptable, Conseil fiscal, etc.
- `itaa_number`, `ire_number` - Professional registration numbers
- `mfa_enabled`, `mfa_secret` - Two-factor authentication

---

#### 2. **Company** (`App\Models\Company`)
Represents a business entity (tenant in multi-tenant architecture).

**Key Relationships:**
- `belongsToMany(User)` through `CompanyUser` pivot
- `hasMany(Partner)` - Customers and suppliers
- `hasMany(Invoice)` - Sales and purchase invoices
- `hasMany(ChartOfAccount)` - Company chart of accounts
- `hasMany(Journal)` - Accounting journals
- `hasMany(JournalEntry)` - Journal entries
- `hasMany(BankAccount)` - Bank accounts
- `hasMany(FiscalYear)` - Fiscal periods
- `hasMany(VatDeclaration)` - VAT declarations
- `hasMany(VatCode)` - VAT codes
- `hasMany(Product)` - Product/service catalog
- `hasMany(Quote)` - Quotations
- `hasMany(CreditNote)` - Credit notes
- `hasMany(RecurringInvoice)` - Recurring invoices
- `hasOne(Subscription)` - Active subscription
- `belongsTo(AccountingFirm, 'managed_by_firm_id')` - Managing firm
- `hasMany(ClientMandate)` - Mandates with firms

**Key Fields:**
- Company identity: `name`, `legal_form`, `vat_number`, `enterprise_number`
- Address: `street`, `house_number`, `postal_code`, `city`, `country_code`
- Banking: `default_iban`, `default_bic`
- Peppol: `peppol_id`, `peppol_provider`, `peppol_api_key`, `peppol_registered`
- Accounting: `fiscal_year_start_month`, `vat_regime`, `vat_periodicity`
- Firm management: `company_type`, `managed_by_firm_id`, `firm_access_level`

---

#### 3. **AccountingFirm** (`App\Models\AccountingFirm`)
Represents an accounting firm (cabinet comptable).

**Key Relationships:**
- `belongsToMany(User)` through `AccountingFirmUser`
- `hasMany(ClientMandate)` - Client relationships
- `belongsToMany(Company)` through `ClientMandate`
- `belongsTo(SubscriptionPlan)` - Firm subscription

**Key Fields:**
- `name`, `slug`, `legal_form`
- `itaa_number`, `ire_number` - Professional registration
- `vat_number`, `enterprise_number`
- `subscription_status` - trial, active, past_due, cancelled
- `max_clients`, `max_users` - Subscription limits

---

#### 4. **Partner** (`App\Models\Partner`)
Represents customers and suppliers.

**Key Relationships:**
- `belongsTo(Company)`
- `hasMany(Invoice)` - Invoices for this partner

**Key Fields:**
- `type` - customer, supplier, both
- `name`, `vat_number`, `enterprise_number`
- `is_company` - Business vs individual
- Address fields
- `peppol_id`, `peppol_capable` - Peppol identification
- `payment_terms_days`
- `default_account_receivable_id`, `default_account_payable_id`

**Scopes:**
- `scopeCustomers()` - Only customers
- `scopeSuppliers()` - Only suppliers
- `scopePeppolCapable()` - Partners with Peppol

---

#### 5. **Invoice** (`App\Models\Invoice`)
Represents sales and purchase invoices.

**Key Relationships:**
- `belongsTo(Company)`
- `belongsTo(Partner)`
- `belongsTo(User, 'created_by')`
- `hasMany(InvoiceLine)` - Invoice line items
- `belongsTo(JournalEntry)` - Accounting entry
- `hasMany(PeppolTransmission)` - Peppol transmissions
- `hasMany(BankTransaction, 'matched_invoice_id')` - Matched payments

**Key Fields:**
- `type` - 'out' (sales) or 'in' (purchase)
- `status` - draft, validated, sent, received, partial, paid, cancelled
- `invoice_number`, `invoice_date`, `due_date`
- Amounts: `total_excl_vat`, `total_vat`, `total_incl_vat`, `amount_paid`, `amount_due`
- Peppol: `peppol_message_id`, `peppol_status`, `peppol_sent_at`, `peppol_delivered_at`
- `ubl_xml` - UBL 2.1 XML content
- `structured_communication` - Belgian payment reference (+++123/4567/89012+++)
- `is_booked`, `booked_at` - Accounting status

**Scopes:**
- `scopeSales()`, `scopePurchases()`
- `scopeUnpaid()`, `scopeOverdue()`

---

#### 6. **Product** (`App\Models\Product`)
Product and service catalog.

**Key Relationships:**
- `belongsTo(Company)`
- `belongsTo(ProductType)` - Advanced product classification
- `belongsTo(ProductCategory)`
- `hasMany(ProductVariant)` - Product variants

**Key Fields:**
- `type` - product or service
- `code`, `sku`, `barcode`
- Pricing: `unit_price`, `cost_price`, `compare_price`, `min_price`
- `vat_rate`, `currency`, `unit`
- Inventory: `track_inventory`, `stock_quantity`, `low_stock_threshold`, `stock_status`
- Dimensions: `weight`, `length`, `width`, `height`
- Service: `duration_minutes`, `requires_scheduling`
- `custom_fields` - JSON field for custom attributes
- `tags` - JSON array of tags

---

#### 7. **JournalEntry** (`App\Models\JournalEntry`)
Accounting journal entries (double-entry bookkeeping).

**Key Relationships:**
- `belongsTo(Company)`
- `belongsTo(Journal)`
- `belongsTo(FiscalYear)`
- `hasMany(JournalEntryLine)` - Debit/credit lines
- `belongsTo(User, 'created_by')`
- `belongsTo(User, 'posted_by')`

**Key Fields:**
- `entry_number`, `entry_date`, `accounting_date`
- `source_type`, `source_id` - Link to source document (invoice, etc.)
- `status` - draft, posted, reversed
- `posted_at`, `posted_by`

**Methods:**
- `isBalanced()` - Checks if debits = credits
- `post()` - Posts the entry to accounting

---

#### 8. **BankAccount** (`App\Models\BankAccount`)
Bank accounts with Open Banking integration.

**Key Relationships:**
- `belongsTo(Company)`
- `belongsTo(ChartOfAccount, 'account_id')`
- `belongsTo(Journal)`
- `hasMany(BankStatement)`
- `hasMany(BankTransaction)`

**Key Fields:**
- `name`, `iban`, `bic`, `bank_name`
- `coda_enabled`, `coda_contract_number` - CODA file support
- `is_default`, `is_active`

---

### Accounting Firm Management Models

#### 9. **ClientMandate** (`App\Models\ClientMandate`)
Relationship between accounting firm and client company.

**Key Relationships:**
- `belongsTo(AccountingFirm)`
- `belongsTo(Company)`
- `belongsTo(User, 'manager_user_id')` - Accountant managing this client
- `hasMany(MandateTask)`
- `hasMany(MandateDocument)`
- `hasMany(MandateActivity)`

**Key Fields:**
- `mandate_type` - full_accounting, vat_declaration, annual_accounts, etc.
- `status` - pending, active, suspended, terminated
- `start_date`, `end_date`
- `services` - JSON array of services provided

---

#### 10. **MandateTask** (`App\Models\MandateTask`)
Tasks for client mandates.

**Key Fields:**
- `task_type` - vat_declaration, annual_closing, invoice_processing, etc.
- `title`, `description`
- `status` - pending, in_progress, review, completed, cancelled
- `priority` - low, normal, high, urgent
- `due_date`
- `assigned_to` - User ID

---

### Subscription & Billing Models

#### 11. **Subscription** (`App\Models\Subscription`)
Company subscription to a plan.

**Key Relationships:**
- `belongsTo(Company)`
- `belongsTo(SubscriptionPlan, 'plan_id')`

**Key Fields:**
- `status` - trial, active, past_due, cancelled
- `trial_ends_at`, `current_period_start`, `current_period_end`
- `cancel_at_period_end`

---

#### 12. **SubscriptionPlan** (`App\Models\SubscriptionPlan`)
Available subscription plans.

**Key Fields:**
- `name`, `description`, `price_monthly`, `price_yearly`
- Limits: `max_invoices_per_month`, `max_clients`, `max_products`, `max_users`
- Features: `feature_multi_currency`, `feature_peppol`, `feature_open_banking`, `feature_ai_ocr`, etc.

---

### Supporting Models

**ChartOfAccount**, **VatCode**, **Journal**, **FiscalYear**, **Quote**, **RecurringInvoice**, **CreditNote**, **BankTransaction**, **BankStatement**, **BankConnection**, **VatDeclaration**, **PeppolTransmission**, **DocumentScan**, **ApprovalWorkflow**, **ApprovalRequest**, **Webhook**, **Report**, **AuditLog**

---

## Controllers & Actions

### Main Controllers

#### 1. **DashboardController**
- `index()` - Main dashboard with KPIs, charts, recent activity

#### 2. **InvoiceController**
**Main Actions:**
- `index()` - List invoices (sales/purchases)
- `create()` - Show invoice creation form
- `store()` - Create new invoice
- `show($id)` - Display invoice details
- `edit($id)` - Edit invoice
- `update($id)` - Update invoice
- `destroy($id)` - Delete invoice
- `validate($id)` - Validate draft invoice
- `send($id)` - Send invoice via email/Peppol
- `downloadPdf($id)` - Generate and download PDF
- `book($id)` - Book invoice to accounting

#### 3. **PartnerController**
**Main Actions:**
- `index()` - List partners (customers/suppliers)
- `create()`, `store()` - Create partner
- `show($id)` - Partner details with invoices, stats
- `edit($id)`, `update($id)` - Edit partner
- `destroy($id)` - Delete partner
- `checkPeppol($id)` - Verify Peppol capability

#### 4. **AccountingController**
**Main Actions:**
- `chartOfAccounts()` - Manage chart of accounts
- `journals()` - Manage journals
- `journalEntries()` - List/create journal entries
- `fiscalYears()` - Manage fiscal years
- `generalLedger()` - General ledger report
- `trialBalance()` - Trial balance report
- `balanceSheet()` - Balance sheet
- `profitLoss()` - Profit & loss statement

#### 5. **BankController**
**Main Actions:**
- `index()` - List bank accounts
- `connect()` - Initiate Open Banking connection
- `syncTransactions($accountId)` - Synchronize transactions
- `matchTransactions()` - Match transactions to invoices
- `importCoda()` - Import CODA file
- `reconciliation()` - Bank reconciliation view

#### 6. **VatController**
**Main Actions:**
- `index()` - VAT dashboard
- `declarations()` - List VAT declarations
- `createDeclaration()` - Create new declaration
- `calculateDeclaration($period)` - Calculate VAT for period
- `submitToIntervat($id)` - Submit to Belgian tax authority
- `downloadXml($id)` - Download Intervat XML

#### 7. **ProductController**
**Main Actions:**
- `index()` - List products/services
- `create()`, `store()` - Create product
- `edit($id)`, `update($id)` - Edit product
- `destroy($id)` - Delete product
- `bulkImport()` - Import products from CSV/Excel
- `stockAdjustment()` - Adjust stock levels

#### 8. **QuoteController**
**Main Actions:**
- `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`
- `convertToInvoice($id)` - Convert quote to invoice
- `send($id)` - Send quote to customer
- `accept($id)`, `reject($id)` - Accept/reject quote

#### 9. **ReportController**
**Main Actions:**
- `index()` - Report library
- `generate($reportType)` - Generate report
- `schedule()` - Schedule automatic reports
- `export($id)` - Export report (PDF, Excel, CSV)

#### 10. **OpenBankingController**
**Main Actions:**
- `connect()` - Show available banks
- `authorize($bankId)` - Initiate OAuth flow
- `callback()` - Handle OAuth callback
- `disconnect($connectionId)` - Revoke bank connection

#### 11. **ApprovalController**
**Main Actions:**
- `index()` - List pending approvals
- `approve($requestId)` - Approve request
- `reject($requestId)` - Reject request
- `workflows()` - Manage approval workflows

#### 12. **AIController**
**Main Actions:**
- `scanDocument()` - Upload and scan document with OCR
- `reviewScan($scanId)` - Review OCR results
- `confirmScan($scanId)` - Confirm and create invoice
- `categorize()` - AI categorization suggestions

---

### Admin Controllers (Admin Panel)

#### **AdminDashboardController**
- System-wide statistics and monitoring

#### **AdminUserController**
- Manage all users across all companies

#### **AdminCompanyController**
- Manage companies, view details, impersonate

#### **AdminSubscriptionController**
- Manage subscriptions, plans, usage

#### **AdminAuditLogController**
- View audit logs and system activity

#### **AdminAnalyticsController**
- Advanced analytics and reporting

---

### API Controllers (RESTful API)

#### **Api\V1\InvoiceApiController**
RESTful API for invoices with token authentication.

**Endpoints:**
- `GET /api/v1/invoices` - List invoices
- `POST /api/v1/invoices` - Create invoice
- `GET /api/v1/invoices/{id}` - Get invoice
- `PUT /api/v1/invoices/{id}` - Update invoice
- `DELETE /api/v1/invoices/{id}` - Delete invoice

#### **Api\V1\PartnerApiController**
RESTful API for partners.

#### **Api\V1\WebhookController**
Handle incoming webhooks (Peppol, Open Banking, etc.).

---

## Services

### 1. **Peppol Services** (`app/Services/Peppol/`)

#### **PeppolService** (`PeppolService.php`)
Core Peppol integration service.

**Key Methods:**
- `sendInvoice(Invoice $invoice): PeppolTransmission` - Send invoice via Peppol network
- `generateUBL(Invoice $invoice): string` - Generate UBL 2.1 XML
- `handleWebhook(Company $company, array $payload)` - Process incoming Peppol webhooks
- `importUblFile(Company $company, string $ublContent): Invoice` - Import UBL invoice
- `checkStatus(PeppolTransmission $transmission): string` - Check transmission status

**Features:**
- UBL 2.1 (Universal Business Language) generation
- Belgian Peppol BIS Billing 3.0 compliance
- Test mode simulation for development
- 5-corner model support (automatic e-Reporting submission)
- Webhook handling for incoming invoices

#### **UblParserService** (`UblParserService.php`)
Parse incoming UBL XML invoices.

**Key Methods:**
- `validate(string $xml): array` - Validate UBL XML
- `parseAndCreateInvoice(Company $company, string $xml): Invoice` - Parse and create invoice from UBL

#### **EReportingService** (`EReportingService.php`)
Submit invoices to Belgian government (5th corner - e-Reporting mandate 2028).

**Key Methods:**
- `submitInvoice(Invoice $invoice)` - Submit to government platform
- `isEReportingRequired(Invoice $invoice): bool` - Check if submission required

#### **PeppolDirectoryService** (`PeppolDirectoryService.php`)
Query Peppol directory for participant verification.

---

### 2. **Open Banking Services** (`app/Services/OpenBanking/`)

#### **PSD2Service** (`PSD2Service.php`)
PSD2 Open Banking integration for Belgian banks.

**Supported Banks:**
- KBC/CBC
- BNP Paribas Fortis
- ING Belgium
- Belfius
- Argenta
- AXA Bank
- Crelan

**Key Methods:**
- `getSupportedBanks(): array` - List supported banks
- `getAuthorizationUrl(string $bankId, int $companyId): string` - Get OAuth URL
- `handleCallback(string $code, string $state): BankConnection` - Process OAuth callback
- `syncAccounts(BankConnection $connection): array` - Sync bank accounts
- `syncTransactions(BankAccount $account, ?Carbon $dateFrom, ?Carbon $dateTo): array` - Sync transactions
- `initiatePayment(BankConnection $connection, array $paymentData): array` - PISP payment initiation
- `refreshToken(BankConnection $connection)` - Refresh access token
- `revokeConnection(BankConnection $connection)` - Revoke connection

**Features:**
- OAuth 2.0 authentication flow
- Account information service (AIS)
- Transaction retrieval with automatic categorization
- Payment initiation service (PISP)
- Belgian structured communication extraction
- Automatic IBAN/BIC identification

---

### 3. **AI Services** (`app/Services/AI/`)

#### **DocumentOCRService** (`DocumentOCRService.php`)
AI-powered OCR for invoice/receipt scanning.

**Key Methods:**
- `processDocument(UploadedFile $file, string $type): DocumentScan` - Process uploaded document
- `extractInvoiceData(array $ocrResult): array` - Extract structured invoice data
- `enhanceWithAI(array $data, string $type): array` - Enhance with ML categorization
- `matchWithExistingData(array $data): array` - Match with existing partners/invoices
- `autoCreateInvoice(DocumentScan $scan): Invoice` - Auto-create invoice from high-confidence scan

**Supported OCR Providers:**
- Google Vision API
- Azure Computer Vision
- AWS Textract
- Local Tesseract (fallback)

**Extracted Data:**
- Invoice number, dates (invoice, due)
- Supplier information (name, address, VAT number)
- IBAN, BIC
- Belgian structured communication (+++xxx/xxxx/xxxxx+++)
- Amounts (excl. VAT, VAT, incl. VAT)
- Line items with quantities and prices
- VAT breakdown by rate

**Features:**
- Multi-language OCR (French, Dutch, English)
- Belgian VAT number validation (modulo 97)
- Fuzzy partner matching
- Confidence scoring for each field
- Auto-invoice creation for high-confidence scans (>85%)

#### **IntelligentCategorizationService** (`IntelligentCategorizationService.php`)
ML-based expense categorization.

**Key Methods:**
- `categorize(string $description): array` - Categorize expense by description
- Returns: `account_id`, `vat_code`, `category`

#### **TreasuryForecastService** (`TreasuryForecastService.php`)
Cash flow forecasting using historical data.

---

### 4. **Other Services**

#### **IntervatService** (`IntervatService.php`)
Belgian VAT declaration submission (Intervat).

**Key Methods:**
- `generateXml(VatDeclaration $declaration): string` - Generate Intervat XML
- `submitDeclaration(VatDeclaration $declaration)` - Submit to SPF Finances
- `validateDeclaration(VatDeclaration $declaration): array` - Validate before submission

#### **CodaParserService** (`CodaParserService.php`)
Parse Belgian CODA bank statement files.

**Key Methods:**
- `parse(string $codaContent): array` - Parse CODA file
- `import(BankAccount $account, string $codaContent): BankStatement` - Import to database

#### **ApprovalWorkflowService** (`Workflow/ApprovalWorkflowService.php`)
Multi-level approval workflows.

**Key Methods:**
- `createRequest(string $documentType, string $documentId)` - Create approval request
- `approve(ApprovalRequest $request, User $user)` - Approve step
- `reject(ApprovalRequest $request, User $user, string $reason)` - Reject request

#### **WebhookDispatcher** (`Webhook/WebhookDispatcher.php`)
Outgoing webhook dispatcher.

**Key Methods:**
- `dispatch(string $event, array $payload)` - Dispatch webhook to subscribed URLs
- Retry logic with exponential backoff

#### **ReportBuilderService** (`Reports/ReportBuilderService.php`)
Advanced report generation.

**Key Methods:**
- `buildReport(string $type, array $params): Report` - Generate report
- `exportToPdf(Report $report)` - Export to PDF
- `exportToExcel(Report $report)` - Export to Excel

#### **TenantService** (`TenantService.php`)
Multi-tenancy management.

**Key Methods:**
- `setCurrentTenant(string $companyId)` - Switch tenant context
- `getCurrentTenant(): ?Company` - Get current tenant

#### **TwoFactorAuthService** (`TwoFactorAuthService.php`)
Two-factor authentication (TOTP).

**Key Methods:**
- `generateSecret(): string` - Generate 2FA secret
- `verify(User $user, string $code): bool` - Verify TOTP code

#### **CacheService** (`CacheService.php`)
Application-wide caching utilities.

---

## Database Schema

### Core Tables

#### **users**
User accounts.
- `id` (UUID), `email`, `password`, `first_name`, `last_name`
- `is_superadmin`, `user_type`, `professional_title`
- `itaa_number`, `ire_number` (professional registration)
- `mfa_enabled`, `mfa_secret`
- Soft deletes

#### **companies**
Business entities (tenants).
- `id` (UUID), `name`, `legal_form`, `vat_number`, `enterprise_number`
- Address fields: `street`, `house_number`, `box`, `postal_code`, `city`, `country_code`
- Banking: `default_iban`, `default_bic`
- Peppol: `peppol_id`, `peppol_provider`, `peppol_api_key`, `peppol_registered`
- Accounting: `fiscal_year_start_month`, `vat_regime`, `vat_periodicity`
- Firm management: `company_type`, `managed_by_firm_id`, `firm_access_level`
- `settings` (JSON)
- Soft deletes

#### **company_user** (Pivot)
Many-to-many relationship: users <-> companies.
- `company_id`, `user_id`
- `role` (owner, admin, accountant, user)
- `permissions` (JSON)
- `is_default` - Default company for user

---

### Accounting Tables

#### **fiscal_years**
Fiscal periods.
- `id` (UUID), `company_id`, `name`, `start_date`, `end_date`
- `status` (open, closing, closed)

#### **chart_of_accounts**
Belgian PCMN chart of accounts.
- `id` (UUID), `company_id`, `account_number`, `name`
- `type` (asset, liability, equity, revenue, expense)
- `parent_id` (self-referencing for hierarchy)
- `is_active`, `is_system`, `allow_direct_posting`

#### **vat_codes**
VAT codes for Belgian tax.
- `id` (UUID), `company_id`, `code`, `name`, `rate`
- `category` (S, Z, E, AE, K, G, O) - UBL categories
- `grid_base`, `grid_vat` - Intervat grid numbers
- `account_vat_due_id`, `account_vat_deductible_id`

#### **journals**
Accounting journals.
- `id` (UUID), `company_id`, `code`, `name`
- `type` (purchases, sales, bank, cash, misc, opening, closing)
- `default_account_id`, `bank_account_id`

#### **journal_entries**
Journal entries (header).
- `id` (UUID), `company_id`, `journal_id`, `fiscal_year_id`
- `entry_number`, `entry_date`, `accounting_date`
- `source_type`, `source_id` - Polymorphic link to source document
- `status` (draft, posted, reversed)
- `created_by`, `posted_by`, `posted_at`
- Soft deletes

#### **journal_entry_lines**
Journal entry lines (debit/credit).
- `id` (UUID), `journal_entry_id`, `line_number`
- `account_id`, `partner_id`
- `debit`, `credit`
- `vat_code`, `vat_amount`, `vat_base`
- `due_date`, `reconciliation_id`

---

### Business Tables

#### **partners**
Customers and suppliers.
- `id` (UUID), `company_id`, `type` (customer, supplier, both)
- `name`, `vat_number`, `enterprise_number`, `is_company`
- Address fields
- `peppol_id`, `peppol_capable`, `peppol_verified_at`
- `payment_terms_days`
- `default_account_receivable_id`, `default_account_payable_id`
- Soft deletes

#### **invoices**
Sales and purchase invoices.
- `id` (UUID), `company_id`, `partner_id`
- `type` (out=sales, in=purchase)
- `status` (draft, validated, sent, received, partial, paid, cancelled)
- `invoice_number`, `invoice_date`, `due_date`, `delivery_date`
- Amounts: `total_excl_vat`, `total_vat`, `total_incl_vat`, `amount_paid`, `amount_due`
- Peppol: `peppol_message_id`, `peppol_status`, `peppol_sent_at`, `peppol_delivered_at`
- `ubl_xml` (LONGTEXT), `pdf_path`
- `journal_entry_id`, `is_booked`, `booked_at`
- `structured_communication` - Belgian payment reference
- `created_by`
- Soft deletes
- Unique: (`company_id`, `type`, `invoice_number`)

#### **invoice_lines**
Invoice line items.
- `id` (UUID), `invoice_id`, `line_number`
- `product_code`, `description`, `quantity`, `unit_code`, `unit_price`
- `discount_percent`, `discount_amount`
- `line_amount`, `vat_category`, `vat_rate`, `vat_amount`
- `account_id`, `analytic_account_id`
- Unique: (`invoice_id`, `line_number`)

#### **products**
Product and service catalog.
- `id` (UUID), `company_id`, `product_type_id`, `category_id`
- `code`, `sku`, `barcode`, `name`, `description`
- `type` (product, service)
- Pricing: `unit_price`, `cost_price`, `compare_price`, `min_price`
- `vat_rate`, `currency`, `unit`
- Inventory: `track_inventory`, `stock_quantity`, `low_stock_threshold`, `stock_status`
- Dimensions: `weight`, `length`, `width`, `height`
- Service: `duration_minutes`, `requires_scheduling`
- `custom_fields` (JSON), `tags` (JSON)
- Soft deletes

#### **quotes**
Quotations.
- Similar structure to invoices
- Additional: `valid_until`, `quote_status` (draft, sent, accepted, rejected, expired)

#### **recurring_invoices**
Recurring invoice templates.
- Similar to invoices
- `frequency` (daily, weekly, monthly, yearly)
- `start_date`, `end_date`, `next_invoice_date`
- `is_active`

#### **credit_notes**
Credit notes.
- `id` (UUID), `company_id`, `partner_id`, `original_invoice_id`
- Similar structure to invoices

---

### Banking Tables

#### **bank_accounts**
Bank accounts.
- `id` (UUID), `company_id`, `name`, `iban`, `bic`, `bank_name`
- `account_id` (FK to chart_of_accounts), `journal_id`
- `coda_enabled`, `coda_contract_number`
- `is_default`, `is_active`
- Soft deletes

#### **bank_connections**
Open Banking connections.
- `id` (UUID), `company_id`, `bank_id`, `bank_name`, `bic`
- `access_token` (encrypted), `refresh_token` (encrypted)
- `token_expires_at`, `consent_expires_at`
- `status` (active, expired, revoked)
- `last_sync_at`

#### **bank_transactions**
Imported bank transactions.
- `id` (UUID), `bank_account_id`, `company_id`
- `external_id`, `date`, `value_date`
- `amount`, `currency`, `description`
- `counterparty_name`, `counterparty_iban`, `counterparty_bic`
- `structured_communication`
- `matched_invoice_id` - Link to matched invoice
- `status` (imported, matched, reconciled)
- `raw_data` (JSON)

#### **bank_statements**
Bank statement summaries.
- `id` (UUID), `bank_account_id`
- `statement_number`, `statement_date`
- `opening_balance`, `closing_balance`
- `coda_file_path`

---

### Peppol Tables

#### **peppol_transmissions**
Log of Peppol transmissions.
- `id` (UUID), `company_id`, `invoice_id`
- `direction` (outbound, inbound)
- `sender_id`, `receiver_id`, `document_type`, `message_id`
- `status` (pending, sent, delivered, failed)
- `sent_at`, `delivered_at`, `mdn_received_at`
- `request_payload` (LONGTEXT), `response_payload` (LONGTEXT)
- `error_message`

#### **ereporting_submissions**
E-Reporting submissions (5th corner).
- `id` (UUID), `company_id`, `invoice_id`
- `submission_id`, `status`, `submitted_at`
- `response_data` (JSON)

---

### VAT Tables

#### **vat_declarations**
VAT declarations.
- `id` (UUID), `company_id`, `fiscal_year_id`
- `period_start`, `period_end`
- `declaration_type` (monthly, quarterly)
- `status` (draft, calculated, submitted, accepted)
- Grid amounts: `grid_00`, `grid_01`, ..., `grid_71`, `grid_72`
- `submitted_at`, `accepted_at`
- `intervat_xml` (LONGTEXT)

---

### Accounting Firm Tables

#### **accounting_firms**
Accounting firms (cabinets).
- `id` (UUID), `name`, `slug`, `legal_form`
- `itaa_number`, `ire_number`, `vat_number`
- Address fields
- `peppol_id`, `peppol_provider`
- `subscription_plan_id`, `subscription_status`
- `trial_ends_at`, `max_clients`, `max_users`
- `settings` (JSON), `features` (JSON)
- Soft deletes

#### **accounting_firm_users** (Pivot)
Firm collaborators.
- `accounting_firm_id`, `user_id`
- `role` (cabinet_owner, cabinet_admin, senior_accountant, junior_accountant, collaborator)
- `employee_number`, `job_title`, `department`
- `permissions` (JSON)
- `can_access_all_clients`
- `is_default`, `is_active`
- `joined_at`

#### **client_mandates**
Client relationships.
- `id` (UUID), `accounting_firm_id`, `company_id`
- `manager_user_id` - Assigned accountant
- `mandate_type` (full_accounting, vat_declaration, annual_accounts, payroll, tax_consultancy)
- `status` (pending, active, suspended, terminated)
- `start_date`, `end_date`
- `services` (JSON) - List of services
- `billing_rate`, `billing_frequency`

#### **mandate_tasks**
Tasks for client mandates.
- `id` (UUID), `client_mandate_id`, `assigned_to`
- `task_type` (vat_declaration, annual_closing, invoice_processing, etc.)
- `title`, `description`
- `status` (pending, in_progress, review, completed, cancelled)
- `priority` (low, normal, high, urgent)
- `due_date`, `completed_at`

#### **mandate_documents**
Documents attached to mandates.
- `id` (UUID), `client_mandate_id`
- `document_type`, `file_path`, `uploaded_by`

#### **mandate_activities**
Activity log for mandates.
- `id` (UUID), `client_mandate_id`, `user_id`
- `activity_type`, `description`, `metadata` (JSON)

#### **mandate_communications**
Communications with clients.
- `id` (UUID), `client_mandate_id`, `user_id`
- `direction` (inbound, outbound)
- `channel` (email, phone, meeting, portal)
- `subject`, `message`, `attachments` (JSON)

---

### Subscription Tables

#### **subscription_plans**
Available plans.
- `id` (UUID), `name`, `slug`, `description`
- Pricing: `price_monthly`, `price_yearly`, `setup_fee`
- Limits: `max_invoices_per_month`, `max_clients`, `max_products`, `max_users`, `max_storage_gb`
- Features (boolean): `feature_multi_currency`, `feature_peppol`, `feature_open_banking`, `feature_ai_ocr`, `feature_api_access`, `feature_white_label`, etc.
- `trial_days`, `is_active`

#### **subscriptions**
Company subscriptions.
- `id` (UUID), `company_id`, `plan_id`
- `status` (trial, active, past_due, cancelled)
- `trial_ends_at`, `current_period_start`, `current_period_end`
- `cancel_at_period_end`

#### **subscription_invoices**
Subscription billing invoices.
- `id` (UUID), `company_id`, `subscription_id`
- `invoice_number`, `invoice_date`, `due_date`
- `amount`, `status`, `paid_at`

#### **subscription_usages**
Usage tracking.
- `id` (UUID), `company_id`, `subscription_id`
- `period_start`, `period_end`
- Counters: `invoices_created`, `clients_count`, `products_count`, `users_count`, `storage_used_mb`

---

### System Tables

#### **audit_logs**
Audit trail.
- `id` (UUID), `user_id`, `company_id`
- `action`, `model_type`, `model_id`
- `old_values` (JSON), `new_values` (JSON)
- `ip_address`, `user_agent`
- `created_at`

#### **webhooks**
Outgoing webhook subscriptions.
- `id` (UUID), `company_id`, `url`, `secret`
- `events` (JSON) - Array of subscribed events
- `is_active`, `last_triggered_at`

#### **webhook_deliveries**
Webhook delivery log.
- `id` (UUID), `webhook_id`, `event`, `payload` (JSON)
- `status` (pending, success, failed)
- `attempts`, `response`, `delivered_at`

#### **reports**
Saved reports.
- `id` (UUID), `company_id`, `created_by`
- `report_type`, `name`, `parameters` (JSON)
- `schedule` (daily, weekly, monthly)
- `recipients` (JSON)

#### **report_executions**
Report execution history.
- `id` (UUID), `report_id`
- `executed_at`, `status`, `file_path`

#### **document_scans**
AI OCR document scans.
- `id` (UUID), `company_id`, `original_filename`, `stored_path`
- `document_type` (invoice, receipt, bank_statement)
- `status` (processing, completed, failed)
- `raw_ocr_text` (TEXT)
- `extracted_data` (JSON) - Structured data
- `confidence_scores` (JSON)
- `overall_confidence`
- `created_document_id`, `created_document_type` - Polymorphic link

#### **approval_workflows**
Approval workflow definitions.
- `id` (UUID), `company_id`, `name`
- `document_type` (invoice, expense, payment)
- `rules` (JSON) - Conditions and approvers
- `is_active`

#### **approval_requests**
Approval requests.
- `id` (UUID), `approval_workflow_id`, `document_type`, `document_id`
- `requested_by`, `status` (pending, approved, rejected)
- `current_step`

#### **approval_steps**
Individual approval steps.
- `id` (UUID), `approval_request_id`, `approver_id`
- `step_number`, `status`, `approved_at`, `rejected_at`
- `notes`

---

## External Integrations

### 1. Peppol Network Integration

**Purpose:** Electronic invoice exchange via Peppol network (Pan-European Public Procurement On-Line).

**Standard:** UBL 2.1 (Universal Business Language), Peppol BIS Billing 3.0

**Features:**
- Send sales invoices to Peppol-capable customers
- Receive purchase invoices from Peppol suppliers
- AS4 message protocol
- Belgian enterprise number scheme (0208)
- Structured communication support
- Test mode for development
- 5-corner model: Automatic submission to e-Reporting platform (Belgian government mandate 2028)

**Flow:**
1. Company creates/validates invoice
2. System generates UBL 2.1 XML (EN 16931 compliant)
3. Invoice sent to Peppol Access Point via API
4. Access Point handles AS4 transmission
5. System tracks delivery status
6. Automatic submission to government e-Reporting platform (5th corner)
7. Incoming invoices processed via webhook

**Configuration:**
- `peppol_provider` - Access Point provider
- `peppol_api_key`, `peppol_api_secret` - Authentication
- `peppol_test_mode` - Enable test environment

**Belgian Specifics:**
- Enterprise number scheme ID: `0208`
- Structured communication in payment terms
- BIS Billing 3.0 profile
- Multi-language support (FR/NL/EN)

---

### 2. Open Banking (PSD2) Integration

**Purpose:** Direct bank account access for transaction synchronization and payment initiation.

**Standard:** PSD2 (Payment Services Directive 2), OAuth 2.0, Berlin Group NextGenPSD2

**Supported Belgian Banks:**
- KBC / CBC
- BNP Paribas Fortis
- ING Belgium
- Belfius
- Argenta
- AXA Bank
- Crelan

**Features:**
- **AIS (Account Information Service):**
  - Read account lists
  - Fetch balances
  - Retrieve transactions (up to 90 days history)
  - Belgian structured communication extraction

- **PISP (Payment Initiation Service):**
  - Initiate SEPA credit transfers
  - Structured payment references
  - Payment status tracking

**Flow:**
1. User selects bank and initiates connection
2. OAuth 2.0 authorization flow (redirects to bank)
3. User authenticates and grants consent
4. System receives access token (90-day consent)
5. Automatic account and transaction synchronization
6. Token refresh before expiration

**Security:**
- Encrypted token storage
- 90-day consent renewal requirement (PSD2 regulation)
- Automatic token refresh
- CSRF protection via state parameter

**Data Extraction:**
- IBAN, BIC identification
- Counterparty information
- Belgian structured communication (+++xxx/xxxx/xxxxx+++)
- Bank identification from IBAN

---

### 3. AI/OCR Integration

**Purpose:** Automated invoice data extraction from scanned documents and photos.

**Providers:**
- **Google Vision API** (Primary)
- **Azure Computer Vision** (Alternative)
- **AWS Textract** (Alternative)
- **Tesseract OCR** (Local fallback)

**Features:**
- Multi-language OCR (French, Dutch, English)
- Intelligent field extraction:
  - Invoice number, dates
  - Supplier name and address
  - Belgian VAT number with validation
  - IBAN/BIC
  - Structured communication
  - Line items with quantities/prices
  - VAT breakdown by rate
- Fuzzy partner matching
- ML-based expense categorization
- Confidence scoring per field
- Auto-invoice creation for high-confidence scans (>85%)

**Extracted Fields with Confidence Scores:**
- Invoice number (0.9)
- Dates (0.85)
- VAT number (0.95 if valid)
- Amounts (0.85)
- Supplier info (0.7-0.99 if matched)

**Process:**
1. Upload document (PDF, JPG, PNG)
2. OCR extraction via selected provider
3. Structured data parsing with regex patterns
4. Belgian-specific validation (VAT modulo 97, structured communication)
5. Partner matching (VAT number or fuzzy name)
6. ML categorization for expense accounts
7. Confidence calculation
8. Auto-create invoice or present for review

---

### 4. Intervat (Belgian VAT) Integration

**Purpose:** Submit VAT declarations to Belgian tax authority (SPF Finances).

**Standard:** Intervat XML format

**Features:**
- Calculate VAT from journal entries
- Generate Intervat XML (all grids 00-72)
- Submit electronically to tax authority
- Track submission status
- Support for monthly/quarterly declarations

**VAT Grids:**
- Grids 00-49: Taxable transactions
- Grids 54-64: VAT amounts
- Grid 71: VAT to pay
- Grid 72: VAT to recover

**Flow:**
1. Select period (month or quarter)
2. System calculates from posted journal entries
3. Review grid amounts
4. Generate Intervat XML
5. Submit via eID or digital certificate
6. Track acceptance status

---

### 5. CODA File Import

**Purpose:** Import Belgian CODA bank statement files.

**Standard:** CODA format (Coded statement of account - Belgian standard)

**Features:**
- Parse CODA 1.0, 2.0, 2.1, 2.2, 2.3
- Extract transactions with all details
- Belgian structured communication extraction
- Automatic import to bank transactions
- Balance reconciliation

**Supported CODA Record Types:**
- Record 0: Header
- Record 1: Old balance
- Record 2: Transaction details
- Record 3: Additional info
- Record 8: New balance

---

### 6. Webhook Integration

**Purpose:** Real-time event notifications to external systems.

**Supported Events:**
- `invoice.created`, `invoice.updated`, `invoice.paid`
- `partner.created`, `partner.updated`
- `payment.received`
- `vat_declaration.submitted`
- `bank_transaction.imported`

**Features:**
- Event subscription management
- Webhook signature verification (HMAC-SHA256)
- Retry logic with exponential backoff
- Delivery tracking and logs

---

### 7. E-Reporting (5-Corner Model)

**Purpose:** Submit invoices to Belgian government platform (mandate effective 2028).

**Standard:** TBD (Belgian federal platform)

**Features:**
- Automatic submission after Peppol transmission
- B2B invoice reporting
- Government audit trail
- Compliance with 2028 e-invoicing mandate

**5-Corner Model:**
1. Supplier (Company)
2. Supplier's Access Point (Peppol AP)
3. Customer's Access Point (Peppol AP)
4. Customer (Partner)
5. **Government Platform (E-Reporting)** - Automatic submission by ComptaBE

---

## Architecture Patterns

### 1. Multi-Tenancy

**Implementation:** Shared database with tenant scoping.

**Pattern:**
- `Company` model as tenant
- `BelongsToTenant` trait on all tenant-scoped models
- `TenantScope` global scope automatically filters queries
- Session-based tenant selection: `session('current_tenant_id')`
- Middleware ensures tenant context

**Example:**
```php
// All queries automatically scoped to current tenant
Invoice::all(); // Only returns invoices for current company

// Manual tenant switching
Company::setCurrentTenant($companyId);
```

**Benefits:**
- Data isolation
- Shared codebase
- Cost-efficient infrastructure
- Easy tenant management

---

### 2. Service Layer Architecture

**Pattern:**
- Controllers handle HTTP concerns (request/response)
- Services contain business logic
- Models represent data and relationships
- Separation of concerns

**Example:**
```php
// Controller
public function sendInvoice($id) {
    $invoice = Invoice::findOrFail($id);
    $transmission = $this->peppolService->sendInvoice($invoice);
    return redirect()->back();
}

// Service
public function sendInvoice(Invoice $invoice): PeppolTransmission {
    $ublXml = $this->generateUBL($invoice);
    // ... complex Peppol logic
}
```

---

### 3. Repository Pattern (Implicit)

**Implementation:** Eloquent ORM with model scopes and relationships.

**Benefits:**
- Clean query interface
- Reusable scopes (`scopeOverdue()`, `scopePeppolCapable()`)
- Relationship eager loading
- Query builder abstraction

---

### 4. Event-Driven Architecture

**Pattern:**
- Model observers for lifecycle hooks
- Laravel events for cross-cutting concerns
- Webhook dispatcher for external notifications

**Example:**
```php
// Observer
class InvoiceObserver {
    public function created(Invoice $invoice) {
        // Trigger webhook
        WebhookDispatcher::dispatch('invoice.created', $invoice);
    }
}
```

---

### 5. Queue-Based Processing

**Pattern:**
- Background jobs for long-running tasks
- Queue workers process async
- Retry logic for failed jobs

**Use Cases:**
- OCR document processing
- Peppol transmission
- Bank transaction synchronization
- Report generation
- Email sending

---

### 6. API Versioning

**Pattern:**
- Versioned API routes (`/api/v1/invoices`)
- Backward compatibility
- Token-based authentication (Sanctum)

---

### 7. Soft Deletes

**Pattern:**
- Most models use `SoftDeletes` trait
- Data retention for audit
- Easy restoration
- `deleted_at` timestamp

---

### 8. UUID Primary Keys

**Pattern:**
- UUIDs instead of auto-increment IDs
- `HasUuid` trait
- Better for distributed systems
- No ID enumeration attacks

---

### 9. Policy-Based Authorization

**Pattern:**
- Laravel policies for authorization
- Resource-specific permissions
- Tenant-aware checks

**Example:**
```php
// Policy
public function view(User $user, Invoice $invoice) {
    return $user->hasAccessToCompany($invoice->company_id);
}

// Usage
$this->authorize('view', $invoice);
```

---

### 10. Configuration-Driven Features

**Pattern:**
- Feature flags in company settings
- Subscription plan-based access
- Dynamic feature toggling

**Example:**
```php
if ($company->hasFeature('peppol')) {
    // Show Peppol send button
}
```

---

## Technology Stack Summary

**Backend:**
- PHP 8.2+
- Laravel 11.x
- MySQL 8.0 / MariaDB 10.6+

**Frontend:**
- Inertia.js (SPA without API)
- Vue.js 3
- Tailwind CSS 3

**Authentication:**
- Laravel Sanctum (API tokens)
- Session-based (web)
- 2FA support (TOTP)

**Queue & Cache:**
- Redis (recommended)
- Database queue driver (fallback)

**File Storage:**
- Local storage (default)
- S3-compatible (configurable)

**External APIs:**
- Google Vision API (OCR)
- Belgian bank PSD2 APIs
- Peppol Access Point API
- Intervat API (SPF Finances)

**Development Tools:**
- Composer (PHP dependencies)
- NPM (frontend dependencies)
- Vite (asset bundling)
- PHPUnit (testing)

---

## Key Design Decisions

1. **Multi-Tenancy at Company Level:** Each company is a separate tenant, allowing users to access multiple companies.

2. **UUIDs for IDs:** Better security, no ID enumeration, distributed-system ready.

3. **Service Layer for Integrations:** Complex external integrations (Peppol, Open Banking, OCR) isolated in dedicated services.

4. **Belgian Accounting Standards:** PCMN chart of accounts, Intervat integration, Belgian-specific features (structured communication, VAT validation).

5. **Subscription-Based Access:** Flexible plans with feature flags and usage limits.

6. **Audit Trail:** Comprehensive logging of all actions for compliance.

7. **Soft Deletes:** Data retention and recovery for important entities.

8. **API-First for Integrations:** RESTful API for third-party integrations.

9. **Webhook Support:** Real-time notifications for external systems.

10. **AI Enhancement:** OCR and ML categorization for automation.

---

## Security Features

- Password hashing (bcrypt)
- CSRF protection
- SQL injection prevention (Eloquent ORM)
- XSS protection (Vue.js escaping)
- 2FA support
- Encrypted sensitive fields (tokens, API keys)
- Role-based access control
- Tenant data isolation
- Audit logging
- Webhook signature verification
- OAuth 2.0 for Open Banking
- API rate limiting

---

## Performance Optimizations

- Database indexes on foreign keys and frequently queried fields
- Eager loading for relationships (`with()`)
- Query result caching (CacheService)
- Queue-based async processing
- Optimized UBL XML generation
- Batch operations for imports

---

## Compliance & Standards

- **Belgian Accounting:** PCMN chart of accounts
- **Belgian VAT:** Intervat XML submission
- **E-Invoicing:** Peppol BIS Billing 3.0, UBL 2.1, EN 16931
- **Banking:** PSD2 compliance, OAuth 2.0
- **Data Protection:** GDPR-compliant data handling
- **E-Reporting:** Belgian 2028 mandate (5-corner model)

---

## Future Enhancements

Based on the codebase structure, potential future features:
- Multi-currency advanced support
- Advanced analytics and BI
- White-label capabilities for accounting firms
- Mobile app (API-ready)
- Blockchain-based audit trail
- Advanced AI forecasting
- Payroll integration
- Additional bank integrations
- International expansion (other countries)

---

## Getting Started for Developers

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+ / MariaDB 10.6+
- Node.js 18+
- Redis (optional, recommended)

### Installation
```bash
# Clone repository
git clone <repository-url>

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build assets
npm run build

# Start development server
php artisan serve
```

### Configuration
- Database: `.env` (DB_* variables)
- Peppol: `PEPPOL_PROVIDER`, `PEPPOL_API_KEY`
- Open Banking: `OPENBANKING_CLIENT_ID`, `OPENBANKING_CLIENT_SECRET`
- OCR: `GOOGLE_VISION_API_KEY` or alternative provider

---

## Conclusion

ComptaBE is a comprehensive, Belgian-focused accounting platform with:
- **58+ Models** managing complex business relationships
- **40+ Controllers** handling diverse user interactions
- **15+ Services** integrating external systems (Peppol, banks, AI)
- **50+ Database Tables** with proper indexing and relationships
- **Modern Architecture** using Laravel best practices
- **Belgian Compliance** with local accounting standards and regulations
- **Advanced Features** including AI OCR, Open Banking, Peppol e-invoicing, and accounting firm management

The system is designed for scalability, maintainability, and Belgian market requirements.

---

**Last Updated:** December 2025
**Version:** 1.0
**Framework:** Laravel 11.x
