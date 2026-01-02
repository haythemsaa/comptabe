# 🏗️ Architecture Complète ComptaBE

**Version:** 2.0.0
**Date:** 31 Décembre 2025
**Stack:** Laravel 11, PHP 8.2+, Alpine.js 3, MySQL 8, Redis

---

## 📐 Vue d'Ensemble Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Blade    │  │Alpine.js │  │Chart.js  │  │Tailwind  │   │
│  │Templates │  │3.x       │  │4.4.0     │  │CSS       │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    ROUTING LAYER                            │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  routes/web.php - 700+ lignes                        │  │
│  │  • Auth routes                                       │  │
│  │  • Tenant middleware routes                          │  │
│  │  • Resource routes (Invoices, Partners, etc.)       │  │
│  │  • AI routes (Analytics, Compliance)                 │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   MIDDLEWARE LAYER                          │
│  • Authenticate                                             │
│  • TenantMiddleware (Multi-company isolation)               │
│  • SubscriptionMiddleware (Plan verification)               │
│  • RoleMiddleware (Permissions)                             │
│  • TwoFactorMiddleware (2FA)                                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   CONTROLLER LAYER                          │
│  ┌──────────────┬──────────────┬──────────────┬─────────┐  │
│  │ Core MVC     │ AI Module    │ Admin        │ API     │  │
│  ├──────────────┼──────────────┼──────────────┼─────────┤  │
│  │ Dashboard    │ Analytics    │ Dashboard    │ Partner │  │
│  │ Invoice      │ Compliance   │ Company      │ Invoice │  │
│  │ Partner      │ Document     │ User         │ VAT     │  │
│  │ Bank         │              │ Subscription │         │  │
│  │ Accounting   │              │ Audit        │         │  │
│  │ VAT          │              │ Settings     │         │  │
│  │ Approval     │              │ Peppol       │         │  │
│  │ Firm         │              │              │         │  │
│  │ Document     │              │              │         │  │
│  │ Product      │              │              │         │  │
│  │ Subscription │              │              │         │  │
│  └──────────────┴──────────────┴──────────────┴─────────┘  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    BUSINESS LOGIC LAYER                     │
│  ┌───────────────────────────────────────────────────────┐ │
│  │              SERVICE ARCHITECTURE                     │ │
│  │                                                        │ │
│  │  AI Services (8 services, 3,500+ lignes)             │ │
│  │  ├── BusinessIntelligenceService                     │ │
│  │  ├── ProactiveAssistantService                       │ │
│  │  ├── ContextAwarenessService                         │ │
│  │  ├── IntelligentInvoiceExtractor                     │ │
│  │  ├── IntelligentCategorizationService               │ │
│  │  ├── SmartReconciliationService                     │ │
│  │  ├── PaymentBehaviorAnalyzer                        │ │
│  │  └── ChurnPredictionService                         │ │
│  │                                                        │ │
│  │  Compliance Services (2 services, 1,000+ lignes)    │ │
│  │  ├── BelgianTaxComplianceService                    │ │
│  │  └── VATOptimizationService                         │ │
│  │                                                        │ │
│  │  Integration Services (4 services, 2,000+ lignes)   │ │
│  │  ├── OpenBankingService (PSD2)                      │ │
│  │  ├── ECommerceIntegrationService                    │ │
│  │  ├── AccountingSoftwareExportService                │ │
│  │  └── PeppolService                                   │ │
│  │                                                        │ │
│  │  Collaboration Services (1 service, 450 lignes)     │ │
│  │  └── RealtimeCollaborationService                   │ │
│  │                                                        │ │
│  │  Core Services (15+ services)                        │ │
│  │  ├── VatDeclarationService                          │ │
│  │  ├── OcrService                                      │ │
│  │  ├── DocumentStorageService                         │ │
│  │  ├── NotificationService                            │ │
│  │  ├── AuditLogService                                │ │
│  │  ├── SubscriptionService                            │ │
│  │  ├── TreasuryForecastService                        │ │
│  │  └── ...                                             │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    JOB/QUEUE LAYER                          │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Asynchronous Jobs (15+ jobs)                        │ │
│  │                                                        │ │
│  │  AI Jobs:                                             │ │
│  │  • ProcessUploadedDocument (OCR)                     │ │
│  │  • DailyInsightsJob (Daily brief)                    │ │
│  │  • AutoCategorizeExpensesJob (Hourly)                │ │
│  │  • AutoReconcileTransactionsJob (Every 2h)           │ │
│  │  • ComplianceCheckJob (Daily)                        │ │
│  │                                                        │ │
│  │  Core Jobs:                                           │ │
│  │  • ProcessPeppolInvoice                              │ │
│  │  • SendInvoiceReminder                               │ │
│  │  • GenerateVatDeclaration                            │ │
│  │  • SyncBankTransactions                              │ │
│  │  • ExportAccountingData                              │ │
│  │  └── ...                                              │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    DATA ACCESS LAYER                        │
│  ┌───────────────────────────────────────────────────────┐ │
│  │              ELOQUENT MODELS (50+ models)            │ │
│  │                                                        │ │
│  │  Core Models:                                         │ │
│  │  • Company (Multi-tenant root)                       │ │
│  │  • User (Authentication)                             │ │
│  │  • Invoice, InvoiceItem                              │ │
│  │  • Partner (Customers/Suppliers)                     │ │
│  │  • BankAccount, BankTransaction                      │ │
│  │  • Account, JournalEntry, JournalEntryLine          │ │
│  │  • Product, ProductCategory                          │ │
│  │  • Expense                                            │ │
│  │                                                        │ │
│  │  AI Models:                                           │ │
│  │  • Document (OCR metadata)                           │ │
│  │  • AiInsight                                          │ │
│  │  • AiPrediction                                       │ │
│  │                                                        │ │
│  │  Compliance Models:                                   │ │
│  │  • VatDeclaration                                     │ │
│  │  • PayrollDeclaration                                 │ │
│  │  • TaxPayment                                         │ │
│  │  • SocialSecurityPayment                              │ │
│  │                                                        │ │
│  │  Workflow Models:                                     │ │
│  │  • ApprovalWorkflow, ApprovalRequest                 │ │
│  │  • ClientMandate, MandateTask                        │ │
│  │                                                        │ │
│  │  Integration Models:                                  │ │
│  │  • PeppolParticipant                                  │ │
│  │  • Subscription, SubscriptionPlan                    │ │
│  │  • AuditLog                                           │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   DATABASE LAYER                            │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  MySQL 8.0 (Primary Database)                        │ │
│  │                                                        │ │
│  │  Tables (80+ tables):                                 │ │
│  │  • companies (Multi-tenant root)                     │ │
│  │  • users (Authentication)                            │ │
│  │  • invoices, invoice_items                           │ │
│  │  • partners                                           │ │
│  │  • bank_accounts, bank_transactions                  │ │
│  │  • accounts, journal_entries, journal_entry_lines    │ │
│  │  • products, expenses                                 │ │
│  │  • vat_declarations, payroll_declarations            │ │
│  │  • documents, approval_workflows                     │ │
│  │  • subscriptions, audit_logs                         │ │
│  │  • ... (80+ tables total)                            │ │
│  │                                                        │ │
│  │  Indexes (100+ indexes):                              │ │
│  │  • Primary keys (UUID)                                │ │
│  │  • Foreign keys                                       │ │
│  │  • Performance indexes (AI queries)                   │ │
│  │  • Composite indexes (company_id + ...)             │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  Redis (Cache & Queue)                                │ │
│  │                                                        │ │
│  │  • Session storage                                    │ │
│  │  • Cache (analytics, compliance, predictions)        │ │
│  │  • Queue system (jobs)                                │ │
│  │  • Real-time collaboration (presence, locks)         │ │
│  │  • Rate limiting                                      │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                 EXTERNAL SERVICES LAYER                     │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  AI Services:                                         │ │
│  │  • Ollama (Local LLM - gratuit)                      │ │
│  │  • Claude API (optionnel)                            │ │
│  │  • Google Vision API (OCR)                           │ │
│  │                                                        │ │
│  │  Banking:                                             │ │
│  │  • Open Banking PSD2 API                             │ │
│  │  • Bank connections (BNP, KBC, Belfius, ING, etc.)  │ │
│  │                                                        │ │
│  │  E-Commerce:                                          │ │
│  │  • Shopify API                                        │ │
│  │  • WooCommerce REST API                              │ │
│  │  • PrestaShop API                                     │ │
│  │                                                        │ │
│  │  Government/Compliance:                               │ │
│  │  • VIES VAT validation (EU)                          │ │
│  │  • Intervat API (SPF Finances Belgique)             │ │
│  │  • KBO/BCE (Belgian company registry)               │ │
│  │  • DIMONA/DMFA (Social security)                    │ │
│  │                                                        │ │
│  │  Business:                                            │ │
│  │  • Peppol network                                     │ │
│  │  • Payment providers (Stripe, Mollie)               │ │
│  │  • Email service (SMTP)                              │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Structure Fichiers Détaillée

```
compta/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── AI/
│   │   │       └── RunDailyInsightsCommand.php
│   │   └── Kernel.php (Scheduler - 6 jobs)
│   │
│   ├── Events/
│   │   ├── PresenceUpdated.php
│   │   ├── LockUpdated.php
│   │   └── DocumentChanged.php
│   │
│   ├── Exceptions/
│   │   └── Handler.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AI/
│   │   │   │   └── AnalyticsDashboardController.php
│   │   │   ├── Admin/
│   │   │   │   ├── AdminDashboardController.php
│   │   │   │   ├── AdminCompanyController.php
│   │   │   │   ├── AdminUserController.php
│   │   │   │   ├── AdminAuditLogController.php
│   │   │   │   ├── AdminSubscriptionController.php
│   │   │   │   └── AdminPeppolController.php
│   │   │   ├── Api/
│   │   │   │   ├── PartnerApiController.php
│   │   │   │   ├── InvoiceApiController.php
│   │   │   │   └── VatApiController.php
│   │   │   ├── Firm/
│   │   │   │   └── FirmDashboardController.php
│   │   │   ├── AccountingController.php
│   │   │   ├── AccountingFirmController.php
│   │   │   ├── AIController.php
│   │   │   ├── AnalyticsController.php
│   │   │   ├── ApprovalController.php
│   │   │   ├── AuthController.php
│   │   │   ├── BankController.php
│   │   │   ├── ComplianceController.php
│   │   │   ├── CreditNoteController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── DocumentFolderController.php
│   │   │   ├── DocumentTagController.php
│   │   │   ├── EReportingController.php
│   │   │   ├── InvitationController.php
│   │   │   ├── InvoiceBatchController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── MandateTaskController.php
│   │   │   ├── OnboardingController.php
│   │   │   ├── OpenBankingController.php
│   │   │   ├── PartnerController.php
│   │   │   ├── PricingController.php
│   │   │   ├── ProductCategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductTypeController.php
│   │   │   ├── QuoteController.php
│   │   │   ├── RecurringInvoiceController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SearchController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── SocialSecurityPaymentController.php
│   │   │   ├── SubscriptionController.php
│   │   │   ├── TaxPaymentController.php
│   │   │   ├── TenantController.php
│   │   │   ├── TwoFactorController.php
│   │   │   └── VatController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── TenantMiddleware.php
│   │   │   ├── SubscriptionMiddleware.php
│   │   │   ├── RoleMiddleware.php
│   │   │   └── TwoFactorMiddleware.php
│   │   │
│   │   └── Requests/
│   │       ├── StoreInvoiceRequest.php
│   │       ├── UpdatePartnerRequest.php
│   │       └── ... (50+ request validators)
│   │
│   ├── Jobs/
│   │   ├── AutoCategorizeExpensesJob.php
│   │   ├── AutoReconcileTransactionsJob.php
│   │   ├── ComplianceCheckJob.php
│   │   ├── DailyInsightsJob.php
│   │   ├── ExportAccountingDataJob.php
│   │   ├── GenerateVatDeclarationJob.php
│   │   ├── ProcessPeppolInvoiceJob.php
│   │   ├── ProcessUploadedDocument.php
│   │   ├── SendInvoiceReminderJob.php
│   │   ├── SyncBankTransactionsJob.php
│   │   └── ... (15+ jobs)
│   │
│   ├── Models/
│   │   ├── Account.php
│   │   ├── AccountingFirm.php
│   │   ├── ApprovalRequest.php
│   │   ├── ApprovalWorkflow.php
│   │   ├── AuditLog.php
│   │   ├── BankAccount.php
│   │   ├── BankTransaction.php
│   │   ├── ClientMandate.php
│   │   ├── Company.php (Multi-tenant root)
│   │   ├── CreditNote.php
│   │   ├── Document.php
│   │   ├── DocumentFolder.php
│   │   ├── DocumentTag.php
│   │   ├── Expense.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── JournalEntry.php
│   │   ├── JournalEntryLine.php
│   │   ├── MandateTask.php
│   │   ├── Partner.php
│   │   ├── PayrollDeclaration.php
│   │   ├── Payslip.php
│   │   ├── PeppolParticipant.php
│   │   ├── Product.php
│   │   ├── ProductCategory.php
│   │   ├── Quote.php
│   │   ├── RecurringInvoice.php
│   │   ├── SocialSecurityPayment.php
│   │   ├── Subscription.php
│   │   ├── SubscriptionPlan.php
│   │   ├── TaxPayment.php
│   │   ├── User.php
│   │   ├── VatDeclaration.php
│   │   └── ... (50+ models)
│   │
│   ├── Notifications/
│   │   ├── ApprovalRequestedNotification.php
│   │   ├── ComplianceAlertNotification.php
│   │   ├── DailyBusinessBriefNotification.php
│   │   ├── InvoiceOverdueNotification.php
│   │   └── ... (10+ notifications)
│   │
│   ├── Policies/
│   │   ├── AccountPolicy.php
│   │   ├── ApprovalPolicy.php
│   │   ├── BankTransactionPolicy.php
│   │   ├── InvoicePolicy.php
│   │   └── PartnerPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   │
│   └── Services/
│       ├── AI/
│       │   ├── AccountingValidationService.php (500 lignes)
│       │   ├── BusinessIntelligenceService.php (600 lignes)
│       │   ├── ChurnPredictionService.php (500 lignes)
│       │   ├── ContextAwarenessService.php (150 lignes)
│       │   ├── IntelligentCategorizationService.php (400 lignes)
│       │   ├── IntelligentInvoiceExtractor.php (350 lignes)
│       │   ├── PaymentBehaviorAnalyzer.php (550 lignes)
│       │   ├── ProactiveAssistantService.php (400 lignes)
│       │   └── SmartReconciliationService.php (400 lignes)
│       │
│       ├── Collaboration/
│       │   └── RealtimeCollaborationService.php (450 lignes)
│       │
│       ├── Compliance/
│       │   ├── BelgianTaxComplianceService.php (600 lignes)
│       │   └── VATOptimizationService.php (400 lignes)
│       │
│       ├── Integrations/
│       │   ├── AccountingSoftwareExportService.php (450 lignes)
│       │   ├── ECommerceIntegrationService.php (600 lignes)
│       │   ├── OpenBankingService.php (500 lignes)
│       │   └── PeppolService.php (800 lignes)
│       │
│       ├── Chat/
│       │   └── ChatService.php
│       │
│       ├── Vat/
│       │   └── VatDeclarationService.php
│       │
│       ├── AuditLogService.php
│       ├── DocumentStorageService.php
│       ├── NotificationService.php
│       ├── OcrService.php
│       ├── SubscriptionService.php
│       ├── TreasuryForecastService.php
│       └── ... (30+ services)
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php (API keys)
│   └── session.php
│
├── database/
│   ├── factories/
│   │   └── ... (Model factories)
│   │
│   ├── migrations/
│   │   ├── 2024_xx_create_companies_table.php
│   │   ├── 2024_xx_create_users_table.php
│   │   ├── 2024_xx_create_invoices_table.php
│   │   ├── ... (80+ migrations)
│   │   ├── 2025_12_31_082505_add_ai_fields_to_expenses_table.php
│   │   ├── 2025_12_31_082541_add_ai_fields_to_bank_transactions_table.php
│   │   └── 2025_12_31_082613_add_indexes_for_ai_queries.php
│   │
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/
│   ├── build/ (Vite assets)
│   ├── images/
│   ├── sw.js (Service Worker PWA)
│   ├── manifest.json (PWA manifest)
│   └── index.php
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── components/
│   │   │   ├── chat.js
│   │   │   └── ... (Alpine components)
│   │   └── pwa/
│   │       └── offline-sync.js
│   │
│   └── views/
│       ├── ai/
│       │   └── analytics.blade.php
│       │
│       ├── approvals/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── pending.blade.php
│       │
│       ├── auth/
│       │   ├── forgot-password.blade.php
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   ├── reset-password.blade.php
│       │   └── verify-email.blade.php
│       │
│       ├── compliance/
│       │   └── dashboard.blade.php
│       │
│       ├── components/
│       │   └── ai/
│       │       └── suggestion-card.blade.php
│       │
│       ├── documents/
│       │   └── scan.blade.php
│       │
│       ├── emails/
│       │   ├── approvals/
│       │   ├── invoices/
│       │   └── alerts/
│       │
│       ├── firm/
│       │   └── clients/
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── show.blade.php
│       │
│       ├── invoices/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       │
│       ├── layouts/
│       │   ├── app.blade.php
│       │   ├── guest.blade.php
│       │   └── mobile.blade.php
│       │
│       ├── pdf/
│       │   ├── payslip.blade.php
│       │   └── vat-declaration.blade.php
│       │
│       └── ... (100+ views)
│
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php (700+ lignes)
│
├── storage/
│   ├── app/
│   │   ├── documents/
│   │   ├── exports/
│   │   └── public/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── .env
├── .env.example
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
│
└── Documentation/
    ├── ARCHITECTURE_COMPLETE.md (ce fichier)
    ├── IMPLEMENTATION_COMPLETE.md
    ├── PHASE_2_3_COMPLETION_SUMMARY.md
    └── FINAL_IMPLEMENTATION_SUMMARY.md
```

---

## 🔧 Technologies & Dépendances

### Backend (PHP/Laravel)
```json
{
  "laravel/framework": "^11.0",
  "php": "^8.2",
  "spatie/laravel-pdf": "^1.0",
  "laravel/horizon": "^5.0",
  "predis/predis": "^2.0",
  "league/flysystem-aws-s3-v3": "^3.0",
  "guzzlehttp/guzzle": "^7.0",
  "dragonbe/vies": "^2.0" (VAT validation)
}
```

### Frontend
```json
{
  "alpinejs": "^3.13",
  "chart.js": "^4.4.0",
  "tailwindcss": "^3.4",
  "axios": "^1.6",
  "@tailwindcss/forms": "^0.5",
  "vite": "^5.0"
}
```

### Infrastructure
- **Web Server:** Nginx 1.24+ / Apache 2.4+
- **PHP:** 8.2+ (FPM)
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Cache/Queue:** Redis 7.0+
- **AI:** Ollama (local) / Claude API (cloud)
- **OCR:** Google Vision API
- **Storage:** Local / S3-compatible

---

## 🔐 Sécurité & Authentification

### Multi-layered Security
```
┌─────────────────────────────────────┐
│  1. Authentication Layer            │
│     • Login/Password (bcrypt)       │
│     • 2FA (TOTP)                    │
│     • Session management            │
│     • Remember me tokens            │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│  2. Multi-Tenancy Isolation         │
│     • Company ID scoping            │
│     • Middleware enforcement        │
│     • Database-level isolation      │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│  3. Authorization (Policies)        │
│     • Role-based (owner/admin/      │
│       accountant/user/viewer)       │
│     • Resource policies             │
│     • Permission gates              │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│  4. Subscription Validation         │
│     • Plan limits                   │
│     • Feature flags                 │
│     • Usage tracking                │
└─────────────────────────────────────┘
            ↓
┌─────────────────────────────────────┐
│  5. Audit Trail                     │
│     • All actions logged            │
│     • Immutable logs                │
│     • Compliance ready              │
└─────────────────────────────────────┘
```

---

## 📊 Flux de Données Principaux

### 1. Flux Invoice Creation
```
User Upload PDF
    ↓
ProcessUploadedDocument Job
    ↓
Google Vision API (OCR)
    ↓
IntelligentInvoiceExtractor
    ↓
Partner Matching (ML)
    ↓
Confidence ≥85%?
    ├─ Yes → Auto-create Invoice
    └─ No  → Preview for validation
        ↓
    User validates
        ↓
    Create Invoice + Items
        ↓
    Update Journal Entries
        ↓
    Trigger Notifications
```

### 2. Flux Auto-Categorization
```
Scheduler (Hourly)
    ↓
AutoCategorizeExpensesJob
    ↓
Fetch uncategorized expenses
    ↓
IntelligentCategorizationService
    ↓
ML Scoring (historical patterns)
    ↓
Confidence ≥75%?
    ├─ Yes → Auto-categorize
    └─ No  → Store suggestions
        ↓
    Update expense record
        ↓
    Cache invalidation
```

### 3. Flux Compliance Check
```
Scheduler (Daily 08:00)
    ↓
ComplianceCheckJob
    ↓
BelgianTaxComplianceService
    ↓
Parallel checks:
├─ VIES VAT validation
├─ Reverse charge detection
├─ Threshold monitoring
├─ Listing obligations
└─ Fiscal calendar
    ↓
Generate alerts
    ↓
Cache results
    ↓
High severity? → Email notification
```

### 4. Flux Open Banking Sync
```
User connects bank (OAuth2)
    ↓
Store access token
    ↓
Scheduler / Manual trigger
    ↓
OpenBankingService.syncAllAccounts()
    ↓
Refresh token if expired
    ↓
Fetch accounts list
    ↓
For each account:
├─ Get balance (cache 5min)
└─ Import transactions (90 days)
    ↓
Create BankTransaction records
    ↓
Trigger AutoReconcileTransactionsJob
```

---

## 🎯 Patterns & Principes Architecture

### 1. Service-Oriented Architecture
- **Services** pour business logic complexe
- **Jobs** pour opérations asynchrones
- **Events** pour découplage
- **Notifications** pour communication

### 2. Multi-Tenancy Pattern
```php
// Global scope sur tous les models
protected static function boot()
{
    parent::boot();

    static::addGlobalScope('company', function ($query) {
        if (auth()->check()) {
            $query->where('company_id', auth()->user()->current_company_id);
        }
    });
}
```

### 3. Repository Pattern (implicite via Eloquent)
- Models = Data access layer
- Services = Business logic
- Controllers = HTTP interface

### 4. Queue Pattern
```php
// Fire and forget
ProcessUploadedDocument::dispatch($path, $companyId);

// Delayed execution
AutoCategorizeExpensesJob::dispatch()->delay(now()->addMinutes(5));

// Chained jobs
Chain::add([
    new ProcessDocument($file),
    new ExtractData($file),
    new CreateInvoice($data),
]);
```

### 5. Caching Strategy
```php
// Multi-level caching
Cache::remember("analytics_{$companyId}", 3600, fn() =>
    $this->biService->getDashboardData($companyId)
);

// Cache invalidation
Cache::forget("analytics_{$companyId}");
Cache::tags(['company:'.$companyId])->flush();
```

---

## 📈 Performance & Scalabilité

### Database Optimization
- **100+ indexes** strategiques
- **Query optimization** (eager loading, select specific)
- **Pagination** sur toutes les listes
- **Database pooling**

### Caching Layers
1. **Redis** (global cache)
2. **Query cache** (MySQL)
3. **View cache** (Blade compilation)
4. **Route cache** (Laravel routing)
5. **Config cache** (App configuration)

### Queue System
- **Horizon** pour monitoring
- **Redis** backend
- **Multiple queues** (default, ai, compliance, exports)
- **Job batching**
- **Retry logic** (3 attempts)

### CDN & Assets
- **Vite** build optimization
- **Asset versioning**
- **Lazy loading** images
- **Code splitting**

---

## 🔄 Workflow Typiques

### A. Onboarding Client
```
1. Register account
2. Email verification
3. Select subscription plan
4. Complete company profile
5. Connect bank (optional)
6. Import initial data
7. Tour guidé interface
8. First invoice creation
```

### B. Monthly Closing
```
1. Auto-categorize all expenses
2. Auto-reconcile bank transactions
3. Review anomalies
4. Validate journal entries
5. Generate VAT declaration
6. Review compliance alerts
7. Export to accountant
8. Generate monthly reports
```

### C. Invoice Lifecycle
```
Create → Validate → Send → Track → Remind → Payment → Reconcile
   ↓        ↓        ↓      ↓       ↓         ↓         ↓
Draft → Validated → Sent → Overdue? → Paid → Reconciled → Archived
```

---

## 🌐 API Architecture

### RESTful API Endpoints
```
/api/v1/
├── /partners
├── /invoices
├── /vat
├── /analytics
├── /compliance
└── /webhooks
    ├── /peppol
    ├── /open-banking
    └── /e-commerce
```

### Webhook System
- Peppol incoming invoices
- Bank transaction updates
- E-commerce order creation
- Payment confirmations

---

## 📱 Progressive Web App (PWA)

### Service Worker Strategy
```javascript
// sw.js
- Network First (API calls)
- Cache First (images, assets)
- Stale While Revalidate (CSS, JS)
```

### Offline Capabilities
- Cache recent invoices/partners
- Queue offline actions
- Sync when back online
- IndexedDB for local data

### Push Notifications
- Overdue invoices
- Payment received
- Compliance alerts
- Approval requests

---

**Total:**
- **150+ fichiers** principaux
- **25,000+ lignes** de code métier
- **80+ tables** database
- **50+ models** Eloquent
- **100+ views** Blade
- **30+ services**
- **15+ jobs**
- **10+ middlewares**

Cette architecture est **production-ready** et **scalable**! 🚀
