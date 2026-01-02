<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\VatController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AI\AnalyticsDashboardController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\OpenBankingController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\InvoiceBatchController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminErrorsController;
use App\Http\Controllers\Admin\AdminMaintenanceController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminNotificationsController;
use App\Http\Controllers\Admin\AdminEmailTemplatesController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\Admin\AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\AdminPeppolController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\AccountingFirmController;
use App\Http\Controllers\Firm\FirmDashboardController;
use App\Http\Controllers\MandateTaskController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\EReportingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\TaxPaymentController;
use App\Http\Controllers\SocialSecurityPaymentController;
use App\Http\Controllers\DocumentFolderController;
use App\Http\Controllers\DocumentTagController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// PWA Offline page
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Peppol Webhook (public - no auth required)
Route::post('/api/webhooks/peppol/{webhookSecret}', [\App\Http\Controllers\PeppolWebhookController::class, 'handle'])
    ->name('peppol.webhook');

// Invitation routes (public - no auth required to view)
Route::prefix('invitation')->name('invitation.')->group(function () {
    Route::get('/{token}', [InvitationController::class, 'show'])->name('accept');
    Route::post('/{token}', [InvitationController::class, 'accept'])->name('accept');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    // SECURITY: Rate limiting on login (5 attempts per 15 minutes)
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,60'); // 3 registrations per hour

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');

    // SECURITY: Rate limiting on password reset (3 attempts per hour)
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email')
        ->middleware('throttle:3,60');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update')
        ->middleware('throttle:5,60');

    // 2FA Challenge (during login)
    Route::get('/2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');

    // SECURITY: Rate limiting on 2FA verification (5 attempts per 15 minutes)
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
        ->name('2fa.verify')
        ->middleware('throttle:5,15');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 2FA Management
    Route::prefix('2fa')->name('2fa.')->group(function () {
        Route::get('/setup', [TwoFactorController::class, 'setup'])->name('setup');
        Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/recovery-codes', [TwoFactorController::class, 'showRecoveryCodes'])->name('recovery-codes');
        Route::post('/regenerate-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('regenerate-codes');
    });

    // Global Search
    Route::get('/search', [SearchController::class, 'global'])->name('search.global');

    // Tenant selection (before tenant middleware)
    Route::get('/select-company', [TenantController::class, 'select'])->name('tenant.select');
    Route::post('/switch-company', [TenantController::class, 'switch'])->name('tenant.switch');
    Route::get('/companies/create', [TenantController::class, 'create'])->name('companies.create');
    Route::post('/companies', [TenantController::class, 'store'])->name('companies.store');

    // Subscription routes (accessible before subscription check)
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/required', [SubscriptionController::class, 'required'])->name('required');
        Route::get('/suspended', [SubscriptionController::class, 'suspended'])->name('suspended');
        Route::get('/expired', [SubscriptionController::class, 'expired'])->name('expired');
        Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/select-plan', [SubscriptionController::class, 'selectPlan'])->name('select-plan');
        Route::post('/start-trial', [SubscriptionController::class, 'startTrial'])->name('start-trial');
        Route::get('/payment', [SubscriptionController::class, 'payment'])->name('payment');
        Route::post('/process-payment', [SubscriptionController::class, 'processPayment'])->name('process-payment');
        Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
        Route::get('/cancel-payment', [SubscriptionController::class, 'cancelPayment'])->name('cancel-payment');
        Route::get('/show', [SubscriptionController::class, 'show'])->name('show');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
    });

    // Protected routes with tenant middleware
    Route::middleware('tenant')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Onboarding
        Route::prefix('onboarding')->name('onboarding.')->controller(OnboardingController::class)->group(function () {
            Route::get('/status', 'status')->name('status');
            Route::post('/tour/start', 'startTour')->name('tour.start');
            Route::post('/tour/step/{stepIndex}', 'trackTourStep')->name('tour.step');
            Route::post('/tour/complete', 'completeTour')->name('tour.complete');
            Route::post('/survey', 'saveSurvey')->name('survey');
            Route::post('/skip', 'skip')->name('skip');
            Route::get('/progress', 'getProgress')->name('progress');
        });

        // Invoices
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
            Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
            Route::post('/{invoice}/validate', [InvoiceController::class, 'validateInvoice'])->name('validate');
            Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
            Route::post('/{invoice}/send-peppol', [InvoiceController::class, 'sendPeppol'])->name('send-peppol');
            Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('payment');
            Route::get('/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('duplicate');
            Route::get('/{invoice}/credit-note', [InvoiceController::class, 'creditNote'])->name('credit-note');
            Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
            Route::get('/{invoice}/ubl', [InvoiceController::class, 'downloadUbl'])->name('ubl');

            // Batch operations
            Route::prefix('batch')->name('batch.')->group(function () {
                Route::post('/mark-paid', [InvoiceBatchController::class, 'markAsPaid'])->name('mark-paid');
                Route::post('/mark-sent', [InvoiceBatchController::class, 'markAsSent'])->name('mark-sent');
                Route::post('/send-reminders', [InvoiceBatchController::class, 'sendReminders'])->name('send-reminders');
                Route::post('/export-pdf', [InvoiceBatchController::class, 'exportPdf'])->name('export-pdf');
                Route::post('/duplicate', [InvoiceBatchController::class, 'duplicate'])->name('duplicate');
                Route::delete('/destroy', [InvoiceBatchController::class, 'destroy'])->name('destroy');
            });
        });

        // Purchase invoices
        Route::prefix('purchases')->name('purchases.')->group(function () {
            Route::get('/', [InvoiceController::class, 'purchases'])->name('index');
            Route::get('/create', [InvoiceController::class, 'createPurchase'])->name('create');
            Route::post('/', [InvoiceController::class, 'storePurchase'])->name('store');
            Route::get('/import-ubl', [InvoiceController::class, 'showImportUbl'])->name('import-ubl');
            Route::post('/import-ubl', [InvoiceController::class, 'importUbl'])->name('import-ubl.store');
        });

        // Quotes (Devis)
        Route::prefix('quotes')->name('quotes.')->group(function () {
            Route::get('/', [QuoteController::class, 'index'])->name('index');
            Route::get('/create', [QuoteController::class, 'create'])->name('create');
            Route::post('/', [QuoteController::class, 'store'])->name('store');
            Route::get('/{quote}', [QuoteController::class, 'show'])->name('show');
            Route::get('/{quote}/edit', [QuoteController::class, 'edit'])->name('edit');
            Route::put('/{quote}', [QuoteController::class, 'update'])->name('update');
            Route::delete('/{quote}', [QuoteController::class, 'destroy'])->name('destroy');
            Route::post('/{quote}/send', [QuoteController::class, 'send'])->name('send');
            Route::post('/{quote}/accept', [QuoteController::class, 'accept'])->name('accept');
            Route::post('/{quote}/reject', [QuoteController::class, 'reject'])->name('reject');
            Route::post('/{quote}/convert', [QuoteController::class, 'convert'])->name('convert');
            Route::get('/{quote}/duplicate', [QuoteController::class, 'duplicate'])->name('duplicate');
            Route::get('/{quote}/pdf', [QuoteController::class, 'downloadPdf'])->name('pdf');
        });

        // Recurring Invoices (Factures récurrentes)
        Route::prefix('recurring-invoices')->name('recurring-invoices.')->group(function () {
            Route::get('/', [RecurringInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [RecurringInvoiceController::class, 'create'])->name('create');
            Route::post('/', [RecurringInvoiceController::class, 'store'])->name('store');
            Route::get('/{recurringInvoice}', [RecurringInvoiceController::class, 'show'])->name('show');
            Route::get('/{recurringInvoice}/edit', [RecurringInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{recurringInvoice}', [RecurringInvoiceController::class, 'update'])->name('update');
            Route::delete('/{recurringInvoice}', [RecurringInvoiceController::class, 'destroy'])->name('destroy');
            Route::post('/{recurringInvoice}/pause', [RecurringInvoiceController::class, 'pause'])->name('pause');
            Route::post('/{recurringInvoice}/resume', [RecurringInvoiceController::class, 'resume'])->name('resume');
            Route::post('/{recurringInvoice}/cancel', [RecurringInvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{recurringInvoice}/generate', [RecurringInvoiceController::class, 'generate'])->name('generate');
        });

        // Credit Notes (Notes de crédit)
        Route::prefix('credit-notes')->name('credit-notes.')->group(function () {
            Route::get('/', [CreditNoteController::class, 'index'])->name('index');
            Route::get('/create', [CreditNoteController::class, 'create'])->name('create');
            Route::post('/', [CreditNoteController::class, 'store'])->name('store');
            Route::get('/{creditNote}', [CreditNoteController::class, 'show'])->name('show');
            Route::get('/{creditNote}/edit', [CreditNoteController::class, 'edit'])->name('edit');
            Route::put('/{creditNote}', [CreditNoteController::class, 'update'])->name('update');
            Route::delete('/{creditNote}', [CreditNoteController::class, 'destroy'])->name('destroy');
            Route::post('/{creditNote}/validate', [CreditNoteController::class, 'markAsValidated'])->name('validate');
            Route::get('/{creditNote}/pdf', [CreditNoteController::class, 'downloadPdf'])->name('pdf');
            Route::get('/from-invoice/{invoice}', [CreditNoteController::class, 'createFromInvoice'])->name('from-invoice');
        });

        // Partners
        Route::prefix('partners')->name('partners.')->group(function () {
            Route::get('/', [PartnerController::class, 'index'])->name('index');
            Route::get('/create', [PartnerController::class, 'create'])->name('create');
            Route::post('/', [PartnerController::class, 'store'])->name('store');
            Route::post('/peppol/verify', [PartnerController::class, 'verifyPeppol'])->name('peppol.verify');
            Route::post('/peppol/search', [PartnerController::class, 'searchPeppol'])->name('peppol.search');
            Route::get('/{partner}', [PartnerController::class, 'show'])->name('show');
            Route::get('/{partner}/edit', [PartnerController::class, 'edit'])->name('edit');
            Route::put('/{partner}', [PartnerController::class, 'update'])->name('update');
            Route::delete('/{partner}', [PartnerController::class, 'destroy'])->name('destroy');
        });

        // Products & Services
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/search', [ProductController::class, 'search'])->name('search');
            Route::get('/export', [ProductController::class, 'export'])->name('export');
            Route::post('/import', [ProductController::class, 'import'])->name('import');
            Route::get('/custom-fields', [ProductController::class, 'getCustomFields'])->name('custom-fields');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('toggle-active');
            Route::get('/{product}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
        });

        // Bank
        Route::prefix('bank')->name('bank.')->group(function () {
            Route::get('/', [BankController::class, 'index'])->name('index');
            Route::get('/accounts', [BankController::class, 'accounts'])->name('accounts');
            Route::post('/accounts', [BankController::class, 'storeAccount'])->name('accounts.store');
            Route::get('/import', [BankController::class, 'showImport'])->name('import');
            Route::post('/import', [BankController::class, 'import'])->name('import.process');
            Route::get('/reconciliation', [\App\Http\Controllers\ReconciliationController::class, 'index'])->name('reconciliation');
            Route::post('/transactions/{transaction}/match', [BankController::class, 'matchTransaction'])->name('match');
        });

        // Accounting
        Route::prefix('accounting')->name('accounting.')->group(function () {
            Route::get('/', [AccountingController::class, 'index'])->name('index');
            Route::get('/chart', [AccountingController::class, 'chartOfAccounts'])->name('chart');
            Route::get('/journals', [AccountingController::class, 'journals'])->name('journals');

            // Exports comptables
            Route::get('/export', [\App\Http\Controllers\AccountingExportController::class, 'index'])->name('export');
            Route::post('/export/generate', [\App\Http\Controllers\AccountingExportController::class, 'generate'])->name('export.generate');
            Route::post('/journals', [AccountingController::class, 'storeJournal'])->name('journals.store');
            Route::get('/entries', [AccountingController::class, 'entries'])->name('entries');
            Route::get('/entries/create', [AccountingController::class, 'createEntry'])->name('entries.create');
            Route::post('/entries', [AccountingController::class, 'storeEntry'])->name('entries.store');
            Route::get('/entries/export', [AccountingController::class, 'exportEntries'])->name('entries.export');
            Route::get('/entries/{entry}', [AccountingController::class, 'showEntry'])->name('entries.show');
            Route::get('/entries/{entry}/edit', [AccountingController::class, 'editEntry'])->name('entries.edit');
            Route::put('/entries/{entry}', [AccountingController::class, 'updateEntry'])->name('entries.update');
            Route::delete('/entries/{entry}', [AccountingController::class, 'destroyEntry'])->name('entries.destroy');
            Route::post('/entries/{entry}/post', [AccountingController::class, 'postEntry'])->name('entries.post');
            Route::get('/entries/{entry}/duplicate', [AccountingController::class, 'duplicateEntry'])->name('entries.duplicate');
            Route::get('/entries/{entry}/reverse', [AccountingController::class, 'reverseEntry'])->name('entries.reverse');
            Route::get('/balance', [AccountingController::class, 'balance'])->name('balance');
            Route::get('/ledger', [AccountingController::class, 'ledger'])->name('ledger');
        });

        // VAT
        Route::prefix('vat')->name('vat.')->group(function () {
            Route::get('/', [VatController::class, 'index'])->name('index');
            Route::get('/create', [VatController::class, 'create'])->name('create');
            Route::post('/', [VatController::class, 'store'])->name('store');
            Route::get('/{declaration}', [VatController::class, 'show'])->name('show');
            Route::get('/{declaration}/edit', [VatController::class, 'edit'])->name('edit');
            Route::put('/{declaration}', [VatController::class, 'update'])->name('update');
            Route::post('/{declaration}/submit', [VatController::class, 'submit'])->name('submit');
            Route::get('/{declaration}/export-intervat', [VatController::class, 'exportIntervat'])->name('export-intervat');
            Route::get('/reports/client-listing', [VatController::class, 'clientListing'])->name('client-listing');
            Route::get('/reports/client-listing/export', [VatController::class, 'exportClientListing'])->name('export-client-listing');
            Route::get('/reports/intrastat', [VatController::class, 'intrastatDeclaration'])->name('intrastat');

            // Déclarations TVA automatiques
            Route::prefix('declarations')->name('declarations.')->controller(\App\Http\Controllers\VatDeclarationController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{declaration}', 'show')->name('show');
                Route::post('/generate', 'generate')->name('generate');
                Route::get('/{declaration}/download-xml', 'downloadXML')->name('download-xml');
                Route::get('/{declaration}/download-pdf', 'downloadPDF')->name('download-pdf');
                Route::post('/{declaration}/submit', 'submit')->name('submit');
                Route::delete('/{declaration}', 'destroy')->name('destroy');
            });
        });

        // Payroll (Paie)
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PayrollController::class, 'index'])->name('index');

            // Employees
            Route::get('/employees', [\App\Http\Controllers\PayrollController::class, 'employees'])->name('employees.index');
            Route::get('/employees/create', [\App\Http\Controllers\PayrollController::class, 'createEmployee'])->name('employees.create');
            Route::post('/employees', [\App\Http\Controllers\PayrollController::class, 'storeEmployee'])->name('employees.store');
            Route::get('/employees/{employee}', [\App\Http\Controllers\PayrollController::class, 'showEmployee'])->name('employees.show');
            Route::get('/employees/{employee}/edit', [\App\Http\Controllers\PayrollController::class, 'editEmployee'])->name('employees.edit');
            Route::put('/employees/{employee}', [\App\Http\Controllers\PayrollController::class, 'updateEmployee'])->name('employees.update');
            Route::delete('/employees/{employee}', [\App\Http\Controllers\PayrollController::class, 'destroyEmployee'])->name('employees.destroy');

            // Payslips
            Route::get('/payslips', [\App\Http\Controllers\PayrollController::class, 'payslips'])->name('payslips.index');
            Route::get('/payslips/{payslip}', [\App\Http\Controllers\PayrollController::class, 'showPayslip'])->name('payslips.show');
            Route::post('/payslips/{payslip}/validate', [\App\Http\Controllers\PayrollController::class, 'validatePayslip'])->name('payslips.validate');
            Route::post('/payslips/{payslip}/mark-paid', [\App\Http\Controllers\PayrollController::class, 'markAsPaid'])->name('payslips.mark-paid');
            Route::get('/payslips/{payslip}/pdf', [\App\Http\Controllers\PayrollController::class, 'downloadPayslipPDF'])->name('payslips.pdf');
        });

        // AI & Automation
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [AIController::class, 'index'])->name('index');

            // OCR Scanner
            Route::get('/scanner', [AIController::class, 'scanner'])->name('scanner');
            Route::post('/scan', [AIController::class, 'scan'])->name('scan');
            Route::post('/scan/batch', [AIController::class, 'batchScan'])->name('scan.batch');
            Route::get('/scan/{scan}', [AIController::class, 'showScan'])->name('scan.show');
            Route::post('/scan/{scan}/validate', [AIController::class, 'validateScan'])->name('scan.validate');

            // Treasury Forecast (moved to dedicated TreasuryController)
            // Route::get('/treasury', [AIController::class, 'treasury'])->name('treasury');
            // Route::get('/treasury/forecast', [AIController::class, 'treasuryForecast'])->name('treasury.forecast');
            // Route::get('/treasury/export', [AIController::class, 'exportTreasuryForecast'])->name('treasury.export');

            // Intelligent Categorization
            Route::get('/categorization', [AIController::class, 'categorization'])->name('categorization');
            Route::post('/categorize/{expense}', [AIController::class, 'categorizeExpense'])->name('categorize');
            Route::post('/categorize/batch', [AIController::class, 'batchCategorize'])->name('categorize.batch');
            Route::post('/categorize/learn', [AIController::class, 'learnCategorization'])->name('categorize.learn');

            // Anomaly Detection
            Route::get('/anomalies', [AIController::class, 'anomalies'])->name('anomalies');

            // AI Analytics Dashboard
            Route::get('/analytics', [AnalyticsDashboardController::class, 'index'])->name('analytics');
            Route::post('/analytics/refresh', [AnalyticsDashboardController::class, 'refresh'])->name('analytics.refresh');
            Route::get('/analytics/{component}', [AnalyticsDashboardController::class, 'component'])->name('analytics.component');
            Route::post('/analytics/export', [AnalyticsDashboardController::class, 'export'])->name('analytics.export');
        });

        // Compliance (Belgian Tax Compliance)
        Route::prefix('compliance')->name('compliance.')->group(function () {
            Route::get('/', [ComplianceController::class, 'index'])->name('index');
            Route::post('/refresh', [ComplianceController::class, 'refresh'])->name('refresh');
            Route::post('/simulate-regime', [ComplianceController::class, 'simulateRegimeChange'])->name('simulate-regime');
            Route::get('/fiscal-calendar', [ComplianceController::class, 'fiscalCalendar'])->name('fiscal-calendar');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
            Route::get('/expenses', [AnalyticsController::class, 'expenses'])->name('expenses');
            Route::get('/profitability', [AnalyticsController::class, 'profitability'])->name('profitability');
            Route::get('/chart-data', [AnalyticsController::class, 'chartData'])->name('chart-data');
            Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
        });

        // Open Banking (PSD2)
        Route::prefix('openbanking')->name('openbanking.')->group(function () {
            Route::get('/', [OpenBankingController::class, 'index'])->name('index');
            Route::get('/banks', [OpenBankingController::class, 'banks'])->name('banks');
            Route::get('/connect/{bankId}', [OpenBankingController::class, 'connect'])->name('connect');
            Route::get('/callback', [OpenBankingController::class, 'callback'])->name('callback');
            Route::post('/sync-all', [OpenBankingController::class, 'syncAll'])->name('sync-all');
            Route::post('/connections/{connection}/sync', [OpenBankingController::class, 'syncAccounts'])->name('sync-accounts');
            Route::post('/connections/{connection}/renew', [OpenBankingController::class, 'renew'])->name('renew');
            Route::delete('/connections/{connection}', [OpenBankingController::class, 'disconnect'])->name('disconnect');
            Route::get('/accounts/{account}', [OpenBankingController::class, 'accountDetail'])->name('account');
            Route::post('/accounts/{account}/sync', [OpenBankingController::class, 'syncTransactions'])->name('sync-transactions');
            Route::post('/connections/{connection}/payment', [OpenBankingController::class, 'initiatePayment'])->name('payment');
            Route::get('/health', [OpenBankingController::class, 'healthStatus'])->name('health');
        });

        // Approval Workflow
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [ApprovalController::class, 'index'])->name('index');
            Route::get('/pending', [ApprovalController::class, 'pending'])->name('pending');
            Route::get('/{request}', [ApprovalController::class, 'show'])->name('show');
            Route::post('/{request}/approve', [ApprovalController::class, 'approve'])->name('approve');
            Route::post('/{request}/reject', [ApprovalController::class, 'reject'])->name('reject');
            Route::post('/{request}/request-changes', [ApprovalController::class, 'requestChanges'])->name('request-changes');
            Route::post('/{request}/delegate', [ApprovalController::class, 'delegate'])->name('delegate');
            Route::post('/{request}/resubmit', [ApprovalController::class, 'resubmit'])->name('resubmit');
            Route::post('/{request}/cancel', [ApprovalController::class, 'cancel'])->name('cancel');

            // Workflow management
            Route::get('/workflows/list', [ApprovalController::class, 'workflows'])->name('workflows');
            Route::get('/workflows/create', [ApprovalController::class, 'createWorkflow'])->name('workflows.create');
            Route::post('/workflows', [ApprovalController::class, 'storeWorkflow'])->name('workflows.store');
            Route::get('/workflows/{workflow}/edit', [ApprovalController::class, 'editWorkflow'])->name('workflows.edit');
            Route::put('/workflows/{workflow}', [ApprovalController::class, 'updateWorkflow'])->name('workflows.update');
            Route::delete('/workflows/{workflow}', [ApprovalController::class, 'destroyWorkflow'])->name('workflows.destroy');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/create', [ReportController::class, 'create'])->name('create');

            // Rapports financiers directs
            Route::get('/balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
            Route::post('/analyze-financials', [ReportController::class, 'analyzeFinancials'])->name('analyze-financials');
            Route::post('/comparison', [ReportController::class, 'comparison'])->name('comparison');

            Route::post('/', [ReportController::class, 'store'])->name('store');
            Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
            Route::post('/preview', [ReportController::class, 'preview'])->name('preview');
            Route::get('/{report}', [ReportController::class, 'show'])->name('show');
            Route::put('/{report}', [ReportController::class, 'update'])->name('update');
            Route::delete('/{report}', [ReportController::class, 'destroy'])->name('destroy');
            Route::get('/{report}/execute', [ReportController::class, 'execute'])->name('execute');
            Route::get('/{report}/executions', [ReportController::class, 'executions'])->name('executions');
            Route::post('/{report}/favorite', [ReportController::class, 'toggleFavorite'])->name('favorite');
            Route::get('/{report}/duplicate', [ReportController::class, 'duplicate'])->name('duplicate');
            Route::get('/download/{execution}', [ReportController::class, 'download'])->name('download');
            Route::post('/cleanup', [ReportController::class, 'cleanup'])->name('cleanup');
        });

        // Treasury Forecast (Prévisions de Trésorerie)
        Route::prefix('treasury')->name('treasury.')->controller(\App\Http\Controllers\TreasuryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/forecast/data', 'getForecast')->name('forecast.data');
            Route::post('/what-if', 'whatIf')->name('what-if');
            Route::get('/export/pdf', 'exportPDF')->name('export.pdf');
            Route::get('/export/excel', 'exportExcel')->name('export.excel');
        });

        // Tax Payments (Impôts)
        Route::prefix('tax-payments')->name('tax-payments.')->group(function () {
            Route::get('/', [TaxPaymentController::class, 'index'])->name('index');
            Route::get('/create', [TaxPaymentController::class, 'create'])->name('create');
            Route::post('/', [TaxPaymentController::class, 'store'])->name('store');
            Route::get('/{taxPayment}', [TaxPaymentController::class, 'show'])->name('show');
            Route::get('/{taxPayment}/edit', [TaxPaymentController::class, 'edit'])->name('edit');
            Route::put('/{taxPayment}', [TaxPaymentController::class, 'update'])->name('update');
            Route::delete('/{taxPayment}', [TaxPaymentController::class, 'destroy'])->name('destroy');
            Route::post('/{taxPayment}/mark-paid', [TaxPaymentController::class, 'markAsPaid'])->name('mark-paid');
        });

        // Social Security Payments (ONSS)
        Route::prefix('social-security')->name('social-security.')->group(function () {
            Route::get('/', [SocialSecurityPaymentController::class, 'index'])->name('index');
            Route::get('/create', [SocialSecurityPaymentController::class, 'create'])->name('create');
            Route::post('/', [SocialSecurityPaymentController::class, 'store'])->name('store');
            Route::get('/{socialSecurityPayment}', [SocialSecurityPaymentController::class, 'show'])->name('show');
            Route::get('/{socialSecurityPayment}/edit', [SocialSecurityPaymentController::class, 'edit'])->name('edit');
            Route::put('/{socialSecurityPayment}', [SocialSecurityPaymentController::class, 'update'])->name('update');
            Route::delete('/{socialSecurityPayment}', [SocialSecurityPaymentController::class, 'destroy'])->name('destroy');
            Route::post('/{socialSecurityPayment}/mark-paid', [SocialSecurityPaymentController::class, 'markAsPaid'])->name('mark-paid');
            Route::post('/calculate-from-payroll', [SocialSecurityPaymentController::class, 'calculateFromPayroll'])->name('calculate-from-payroll');
        });

        // E-Reporting (Belgian 5-corner model - 2028 mandate)
        Route::prefix('ereporting')->name('ereporting.')->group(function () {
            Route::get('/', [EReportingController::class, 'index'])->name('index');
            Route::get('/pending', [EReportingController::class, 'pendingInvoices'])->name('pending');
            Route::get('/settings', [EReportingController::class, 'settings'])->name('settings');
            Route::put('/settings', [EReportingController::class, 'updateSettings'])->name('settings.update');
            Route::post('/submit/{invoice}', [EReportingController::class, 'submit'])->name('submit');
            Route::post('/batch-submit', [EReportingController::class, 'batchSubmit'])->name('batch-submit');
            Route::get('/submissions/{submission}', [EReportingController::class, 'show'])->name('show');
            Route::post('/submissions/{submission}/check', [EReportingController::class, 'checkStatus'])->name('check-status');
            Route::post('/submissions/{submission}/retry', [EReportingController::class, 'retry'])->name('retry');
            Route::get('/compliance-report', [EReportingController::class, 'complianceReport'])->name('compliance-report');
            Route::get('/statistics', [EReportingController::class, 'statistics'])->name('statistics');
        });

        // Documents Archive (Paper Cloud)
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/archived', [DocumentController::class, 'archived'])->name('archived');
            Route::get('/create', [DocumentController::class, 'create'])->name('create');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
            Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
            Route::get('/{document}/preview', [DocumentController::class, 'preview'])->name('preview');
            Route::post('/{document}/star', [DocumentController::class, 'toggleStar'])->name('star');
            Route::post('/{document}/archive', [DocumentController::class, 'archive'])->name('archive');
            Route::post('/{document}/unarchive', [DocumentController::class, 'unarchive'])->name('unarchive');
            Route::post('/{document}/move', [DocumentController::class, 'move'])->name('move');
            Route::post('/bulk', [DocumentController::class, 'bulk'])->name('bulk');
        });

        // Document Scanner OCR (Intelligent with Ollama AI)
        Route::prefix('scanner')->name('scanner.')->group(function () {
            Route::get('/', [ScannerController::class, 'index'])->name('index');
            Route::post('/scan', [ScannerController::class, 'scan'])->name('scan');
            Route::post('/create-invoice', [ScannerController::class, 'createInvoice'])->name('create-invoice');
            Route::post('/process-async', [ScannerController::class, 'processAsync'])->name('process-async');
        });

        // OCR Analytics Dashboard
        Route::prefix('ocr/analytics')->name('ocr.analytics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\OcrAnalyticsController::class, 'index'])->name('index');
            Route::get('/realtime', [\App\Http\Controllers\OcrAnalyticsController::class, 'realtimeStats'])->name('realtime');
            Route::get('/export', [\App\Http\Controllers\OcrAnalyticsController::class, 'export'])->name('export');
            Route::post('/retry/{scan}', [\App\Http\Controllers\OcrAnalyticsController::class, 'retry'])->name('retry');
        });

        // Email Invoice Import
        Route::prefix('email-invoices')->name('email-invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\EmailInvoiceController::class, 'index'])->name('index');
            Route::get('/{emailInvoice}', [\App\Http\Controllers\EmailInvoiceController::class, 'show'])->name('show');
            Route::post('/{emailInvoice}/process', [\App\Http\Controllers\EmailInvoiceController::class, 'process'])->name('process');
            Route::post('/{emailInvoice}/reject', [\App\Http\Controllers\EmailInvoiceController::class, 'reject'])->name('reject');
            Route::post('/{emailInvoice}/create-invoice', [\App\Http\Controllers\EmailInvoiceController::class, 'createInvoice'])->name('create-invoice');
            Route::delete('/{emailInvoice}', [\App\Http\Controllers\EmailInvoiceController::class, 'destroy'])->name('destroy');
        });

        // Document Folders
        Route::prefix('document-folders')->name('document-folders.')->group(function () {
            Route::get('/', [DocumentFolderController::class, 'index'])->name('index');
            Route::post('/', [DocumentFolderController::class, 'store'])->name('store');
            Route::put('/{folder}', [DocumentFolderController::class, 'update'])->name('update');
            Route::delete('/{folder}', [DocumentFolderController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [DocumentFolderController::class, 'reorder'])->name('reorder');
        });

        // Document Tags
        Route::prefix('document-tags')->name('document-tags.')->group(function () {
            Route::get('/', [DocumentTagController::class, 'index'])->name('index');
            Route::post('/', [DocumentTagController::class, 'store'])->name('store');
            Route::put('/{tag}', [DocumentTagController::class, 'update'])->name('update');
            Route::delete('/{tag}', [DocumentTagController::class, 'destroy'])->name('destroy');
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', fn() => redirect()->route('settings.company'))->name('index');
            Route::get('/company', [SettingsController::class, 'company'])->name('company');
            Route::put('/company', [SettingsController::class, 'updateCompany'])->name('company.update');
            Route::post('/company/logo', [SettingsController::class, 'uploadLogo'])->name('logo.upload');
            Route::delete('/company/logo', [SettingsController::class, 'deleteLogo'])->name('logo.delete');
            Route::get('/peppol', [SettingsController::class, 'peppol'])->name('peppol');
            Route::put('/peppol', [SettingsController::class, 'updatePeppol'])->name('peppol.update');
            Route::post('/peppol/test', [SettingsController::class, 'testPeppolConnection'])->name('peppol.test');
            Route::get('/invoices', [SettingsController::class, 'invoices'])->name('invoices');
            Route::put('/invoices', [SettingsController::class, 'updateInvoices'])->name('invoices.update');
            Route::get('/users', [SettingsController::class, 'users'])->name('users');
            Route::post('/users/invite', [SettingsController::class, 'inviteUser'])->name('users.invite');
            Route::put('/users/{user}/role', [SettingsController::class, 'updateUserRole'])->name('users.role');
            Route::delete('/users/{user}', [SettingsController::class, 'removeUser'])->name('users.remove');
            Route::post('/invitations/{invitation}/resend', [InvitationController::class, 'resend'])->name('invitations.resend');
            Route::delete('/invitations/{invitation}', [InvitationController::class, 'cancel'])->name('invitations.cancel');
            Route::get('/export', [SettingsController::class, 'export'])->name('export');

            // Product Types
            Route::prefix('product-types')->name('product-types.')->group(function () {
                Route::get('/', [ProductTypeController::class, 'index'])->name('index');
                Route::get('/create', [ProductTypeController::class, 'create'])->name('create');
                Route::post('/', [ProductTypeController::class, 'store'])->name('store');
                Route::get('/{productType}/edit', [ProductTypeController::class, 'edit'])->name('edit');
                Route::put('/{productType}', [ProductTypeController::class, 'update'])->name('update');
                Route::delete('/{productType}', [ProductTypeController::class, 'destroy'])->name('destroy');
                Route::post('/seed-defaults', [ProductTypeController::class, 'seedDefaults'])->name('seed-defaults');
                // Custom fields management
                Route::post('/{productType}/fields', [ProductTypeController::class, 'addField'])->name('fields.store');
                Route::put('/{productType}/fields/{field}', [ProductTypeController::class, 'updateField'])->name('fields.update');
                Route::delete('/{productType}/fields/{field}', [ProductTypeController::class, 'deleteField'])->name('fields.destroy');
                Route::post('/{productType}/fields/reorder', [ProductTypeController::class, 'reorderFields'])->name('fields.reorder');
            });

            // Product Categories
            Route::prefix('product-categories')->name('product-categories.')->group(function () {
                Route::get('/', [ProductCategoryController::class, 'index'])->name('index');
                Route::get('/create', [ProductCategoryController::class, 'create'])->name('create');
                Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
                Route::get('/{productCategory}/edit', [ProductCategoryController::class, 'edit'])->name('edit');
                Route::put('/{productCategory}', [ProductCategoryController::class, 'update'])->name('update');
                Route::delete('/{productCategory}', [ProductCategoryController::class, 'destroy'])->name('destroy');
                Route::post('/reorder', [ProductCategoryController::class, 'reorder'])->name('reorder');
                Route::get('/search', [ProductCategoryController::class, 'search'])->name('search');
            });
        });
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Superadmin only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Companies Management
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [AdminCompanyController::class, 'index'])->name('index');
        Route::get('/{company}', [AdminCompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [AdminCompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [AdminCompanyController::class, 'update'])->name('update');
        Route::post('/{company}/suspend', [AdminCompanyController::class, 'suspend'])->name('suspend');
        Route::post('/{company}/restore', [AdminCompanyController::class, 'restore'])->name('restore')->withTrashed();
        Route::post('/{company}/impersonate', [AdminCompanyController::class, 'impersonate'])->name('impersonate');
        Route::delete('/{company}', [AdminCompanyController::class, 'destroy'])->name('destroy');
    });

    // Stop impersonation (company)
    Route::post('/stop-impersonate-company', [AdminCompanyController::class, 'stopImpersonate'])->name('stop-impersonate-company');

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{user}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::post('/{user}/toggle-superadmin', [AdminUserController::class, 'toggleSuperadmin'])->name('toggle-superadmin');
        Route::post('/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('impersonate');
        Route::post('/{user}/restore', [AdminUserController::class, 'restore'])->name('restore')->withTrashed();
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

    // Stop impersonation (user)
    Route::post('/stop-impersonate-user', [AdminUserController::class, 'stopImpersonate'])->name('stop-impersonate-user');

    // Audit Logs
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AdminAuditLogController::class, 'index'])->name('index');
        Route::get('/export', [AdminAuditLogController::class, 'export'])->name('export');
        Route::get('/{auditLog}', [AdminAuditLogController::class, 'show'])->name('show');
        Route::post('/cleanup', [AdminAuditLogController::class, 'cleanup'])->name('cleanup');
    });

    // System Errors Management
    Route::prefix('errors')->name('errors.')->group(function () {
        Route::get('/', [AdminErrorsController::class, 'index'])->name('index');
        Route::get('/{error}', [AdminErrorsController::class, 'show'])->name('show');
        Route::post('/{error}/resolve', [AdminErrorsController::class, 'resolve'])->name('resolve');
        Route::post('/bulk-resolve', [AdminErrorsController::class, 'bulkResolve'])->name('bulk-resolve');
        Route::delete('/{error}', [AdminErrorsController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [AdminErrorsController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup', [AdminErrorsController::class, 'cleanup'])->name('cleanup');
    });

    // Maintenance Mode & System Tools
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [AdminMaintenanceController::class, 'index'])->name('index');
        Route::post('/enable', [AdminMaintenanceController::class, 'enable'])->name('enable');
        Route::post('/disable', [AdminMaintenanceController::class, 'disable'])->name('disable');
        Route::post('/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('clear-cache');
        Route::post('/optimize', [AdminMaintenanceController::class, 'optimize'])->name('optimize');
        Route::post('/clear-logs', [AdminMaintenanceController::class, 'clearLogs'])->name('clear-logs');
        Route::post('/migrate', [AdminMaintenanceController::class, 'migrate'])->name('migrate');
        Route::post('/restart-queue', [AdminMaintenanceController::class, 'restartQueue'])->name('restart-queue');
    });

    // Analytics & Statistics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AdminAnalyticsController::class, 'index'])->name('index');
        Route::post('/refresh', [AdminAnalyticsController::class, 'refresh'])->name('refresh');
        Route::get('/realtime', [AdminAnalyticsController::class, 'realtime'])->name('realtime');
        Route::get('/export', [AdminAnalyticsController::class, 'export'])->name('export');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [AdminNotificationsController::class, 'index'])->name('index');
        Route::get('/latest', [AdminNotificationsController::class, 'latest'])->name('latest');
        Route::get('/unread-count', [AdminNotificationsController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{notification}/read', [AdminNotificationsController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [AdminNotificationsController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [AdminNotificationsController::class, 'destroy'])->name('destroy');
        Route::delete('/delete-read', [AdminNotificationsController::class, 'deleteRead'])->name('delete-read');
        Route::get('/preferences', [AdminNotificationsController::class, 'preferences'])->name('preferences');
        Route::put('/preferences', [AdminNotificationsController::class, 'updatePreferences'])->name('update-preferences');
        Route::post('/send-test', [AdminNotificationsController::class, 'sendTest'])->name('send-test');
    });

    // Email Templates
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [AdminEmailTemplatesController::class, 'index'])->name('index');
        Route::get('/create', [AdminEmailTemplatesController::class, 'create'])->name('create');
        Route::post('/', [AdminEmailTemplatesController::class, 'store'])->name('store');
        Route::get('/{emailTemplate}', [AdminEmailTemplatesController::class, 'show'])->name('show');
        Route::get('/{emailTemplate}/edit', [AdminEmailTemplatesController::class, 'edit'])->name('edit');
        Route::put('/{emailTemplate}', [AdminEmailTemplatesController::class, 'update'])->name('update');
        Route::delete('/{emailTemplate}', [AdminEmailTemplatesController::class, 'destroy'])->name('destroy');
        Route::post('/{emailTemplate}/toggle-active', [AdminEmailTemplatesController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{emailTemplate}/duplicate', [AdminEmailTemplatesController::class, 'duplicate'])->name('duplicate');
        Route::get('/{emailTemplate}/preview', [AdminEmailTemplatesController::class, 'preview'])->name('preview');
        Route::post('/{emailTemplate}/send-test', [AdminEmailTemplatesController::class, 'sendTest'])->name('send-test');
        Route::post('/seed-defaults', [AdminEmailTemplatesController::class, 'seedDefaults'])->name('seed-defaults');
    });

    // Support Tickets
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/stats', [AdminSupportController::class, 'stats'])->name('stats');
        Route::get('/export', [AdminSupportController::class, 'export'])->name('export');
        Route::post('/bulk-action', [AdminSupportController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{ticket}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/{ticket}/update-status', [AdminSupportController::class, 'updateStatus'])->name('update-status');
        Route::post('/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('assign');
        Route::post('/{ticket}/update-priority', [AdminSupportController::class, 'updatePriority'])->name('update-priority');
        Route::post('/{ticket}/add-message', [AdminSupportController::class, 'addMessage'])->name('add-message');
    });

    // Backups
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [AdminBackupController::class, 'index'])->name('index');
        Route::post('/create', [AdminBackupController::class, 'create'])->name('create');
        Route::get('/settings', [AdminBackupController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminBackupController::class, 'updateSettings'])->name('update-settings');
        Route::get('/stats', [AdminBackupController::class, 'stats'])->name('stats');
        Route::get('/{backup}/download', [AdminBackupController::class, 'download'])->name('download');
        Route::delete('/{backup}', [AdminBackupController::class, 'destroy'])->name('destroy');
        Route::delete('/expired/delete', [AdminBackupController::class, 'deleteExpired'])->name('delete-expired');
    });

    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
        Route::put('/', [AdminSettingsController::class, 'update'])->name('update');
        Route::post('/clear-cache', [AdminSettingsController::class, 'clearCache'])->name('clear-cache');
        Route::post('/maintenance', [AdminSettingsController::class, 'maintenance'])->name('maintenance');
        Route::post('/run-migrations', [AdminSettingsController::class, 'runMigrations'])->name('run-migrations');
        Route::post('/retry-failed-jobs', [AdminSettingsController::class, 'retryFailedJobs'])->name('retry-failed-jobs');
        Route::get('/storage-link', [AdminSettingsController::class, 'storageLink'])->name('storage-link');
    });

    // Subscription Plans Management
    Route::prefix('subscription-plans')->name('subscription-plans.')->group(function () {
        Route::get('/', [AdminSubscriptionPlanController::class, 'index'])->name('index');
        Route::get('/create', [AdminSubscriptionPlanController::class, 'create'])->name('create');
        Route::post('/', [AdminSubscriptionPlanController::class, 'store'])->name('store');
        Route::get('/{subscriptionPlan}/edit', [AdminSubscriptionPlanController::class, 'edit'])->name('edit');
        Route::put('/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'update'])->name('update');
        Route::delete('/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'destroy'])->name('destroy');
        Route::post('/{subscriptionPlan}/toggle-active', [AdminSubscriptionPlanController::class, 'toggleActive'])->name('toggle-active');
    });

    // Subscriptions Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [AdminSubscriptionController::class, 'index'])->name('index');
        Route::get('/expiring-trials', [AdminSubscriptionController::class, 'expiringTrials'])->name('expiring-trials');
        Route::get('/unsubscribed', [AdminSubscriptionController::class, 'unsubscribed'])->name('unsubscribed');
        Route::get('/create/{company}', [AdminSubscriptionController::class, 'create'])->name('create');
        Route::post('/store/{company}', [AdminSubscriptionController::class, 'store'])->name('store');
        Route::get('/{subscription}', [AdminSubscriptionController::class, 'show'])->name('show');
        Route::get('/{subscription}/edit', [AdminSubscriptionController::class, 'edit'])->name('edit');
        Route::put('/{subscription}', [AdminSubscriptionController::class, 'update'])->name('update');
        Route::post('/{subscription}/suspend', [AdminSubscriptionController::class, 'suspend'])->name('suspend');
        Route::post('/{subscription}/reactivate', [AdminSubscriptionController::class, 'reactivate'])->name('reactivate');
        Route::post('/{subscription}/extend-trial', [AdminSubscriptionController::class, 'extendTrial'])->name('extend-trial');
        Route::post('/{subscription}/generate-invoice', [AdminSubscriptionController::class, 'generateInvoice'])->name('generate-invoice');
    });

    // Subscription Invoices
    Route::prefix('subscription-invoices')->name('subscription-invoices.')->group(function () {
        Route::get('/', [AdminSubscriptionController::class, 'invoices'])->name('index');
        Route::get('/{subscriptionInvoice}', [AdminSubscriptionController::class, 'showInvoice'])->name('show');
        Route::post('/{subscriptionInvoice}/mark-paid', [AdminSubscriptionController::class, 'markInvoicePaid'])->name('mark-paid');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index'])->name('index');
    });

    // Peppol Management
    Route::prefix('peppol')->name('peppol.')->group(function () {
        Route::get('/dashboard', [AdminPeppolController::class, 'dashboard'])->name('dashboard');
        Route::get('/settings', [AdminPeppolController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminPeppolController::class, 'updateSettings'])->name('settings.update');
        Route::post('/test', [AdminPeppolController::class, 'testConnection'])->name('test');
        Route::get('/quotas', [AdminPeppolController::class, 'quotas'])->name('quotas');
        Route::post('/quotas/{company}', [AdminPeppolController::class, 'updateQuota'])->name('quotas.update');
        Route::get('/optimize', [AdminPeppolController::class, 'optimize'])->name('optimize');
        Route::post('/optimize/apply', [AdminPeppolController::class, 'applyOptimalPlan'])->name('optimize.apply');
        Route::get('/usage', [AdminPeppolController::class, 'usage'])->name('usage');
        Route::post('/quotas/reset', [AdminPeppolController::class, 'resetQuotas'])->name('quotas.reset');
    });

    // Cache Management
    Route::prefix('cache')->name('cache.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CacheDashboardController::class, 'index'])->name('dashboard');
        Route::post('/clear', [\App\Http\Controllers\Admin\CacheDashboardController::class, 'clear'])->name('clear');
        Route::post('/clear-key', [\App\Http\Controllers\Admin\CacheDashboardController::class, 'clearKey'])->name('clear-key');
        Route::post('/warmup', [\App\Http\Controllers\Admin\CacheDashboardController::class, 'warmup'])->name('warmup');
        Route::post('/optimize', [\App\Http\Controllers\Admin\CacheDashboardController::class, 'optimize'])->name('optimize');
    });

    // System Health & Monitoring
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/health', [\App\Http\Controllers\Admin\AdminSystemController::class, 'health'])->name('health');
        Route::get('/phpinfo', [\App\Http\Controllers\Admin\AdminSystemController::class, 'phpinfo'])->name('phpinfo');
        Route::get('/logs', [\App\Http\Controllers\Admin\AdminSystemController::class, 'logs'])->name('logs');
        Route::post('/logs/clear', [\App\Http\Controllers\Admin\AdminSystemController::class, 'clearLogs'])->name('logs.clear');
    });

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminExportController::class, 'index'])->name('index');
        Route::get('/{type}', [\App\Http\Controllers\Admin\AdminExportController::class, 'export'])->name('download');
    });

    // Global Search API
    Route::get('/search', [\App\Http\Controllers\Admin\AdminSearchController::class, 'search'])->name('search');

    // API endpoints for searchable selects
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/companies', [\App\Http\Controllers\Admin\AdminSearchController::class, 'companies'])->name('companies');
        Route::get('/users', [\App\Http\Controllers\Admin\AdminSearchController::class, 'users'])->name('users');
        Route::get('/clients', [\App\Http\Controllers\Admin\AdminSearchController::class, 'clients'])->name('clients');
    });
});

/*
|--------------------------------------------------------------------------
| Expert-Comptable / Accounting Firm Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('firm')->name('firm.')->group(function () {
    // Firm Dashboard (NEW - Multi-client overview)
    Route::get('/', [FirmDashboardController::class, 'index'])->name('dashboard');
    Route::get('/clients', [FirmDashboardController::class, 'clients'])->name('clients.list');

    // Firm Setup
    Route::get('/setup', [AccountingFirmController::class, 'setup'])->name('setup');
    Route::post('/setup', [AccountingFirmController::class, 'store'])->name('store');

    // Firm Settings
    Route::get('/settings', [AccountingFirmController::class, 'settings'])->name('settings');
    Route::put('/settings', [AccountingFirmController::class, 'updateSettings'])->name('settings.update');

    // Client Management
    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/create', [AccountingFirmController::class, 'createClient'])->name('create');
        Route::post('/', [AccountingFirmController::class, 'storeClient'])->name('store');
        Route::get('/{mandate}', [AccountingFirmController::class, 'showClient'])->name('show');
        Route::get('/{mandate}/edit', [AccountingFirmController::class, 'editClient'])->name('edit');
        Route::put('/{mandate}', [AccountingFirmController::class, 'updateClient'])->name('update');
    });

    // Team Management
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [AccountingFirmController::class, 'team'])->name('index');
        Route::post('/invite', [AccountingFirmController::class, 'inviteTeamMember'])->name('invite');
        Route::put('/{user}', [AccountingFirmController::class, 'updateTeamMember'])->name('update');
        Route::delete('/{user}', [AccountingFirmController::class, 'removeTeamMember'])->name('remove');
    });

    // Task Management
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [MandateTaskController::class, 'index'])->name('index');
        Route::get('/my-tasks', [MandateTaskController::class, 'myTasks'])->name('my-tasks');
        Route::get('/create', [MandateTaskController::class, 'create'])->name('create');
        Route::post('/', [MandateTaskController::class, 'store'])->name('store');
        Route::get('/{task}', [MandateTaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [MandateTaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [MandateTaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [MandateTaskController::class, 'destroy'])->name('destroy');
        Route::patch('/{task}/status', [MandateTaskController::class, 'updateStatus'])->name('status');
        Route::post('/{task}/log-time', [MandateTaskController::class, 'logTime'])->name('log-time');
    });
});

/*
|--------------------------------------------------------------------------
| Client Portal Routes
|--------------------------------------------------------------------------
|
| Dedicated portal for clients with limited access to view their documents,
| invoices, and interact with their accountant.
|
*/
Route::middleware(['auth', \App\Http\Middleware\ClientPortalAccess::class])
    ->prefix('portal/{company}')
    ->name('client-portal.')
    ->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\ClientPortalController::class, 'dashboard'])->name('dashboard');

        // Invoices
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ClientPortalController::class, 'invoices'])->name('index');
            Route::get('/{invoice}', [\App\Http\Controllers\ClientPortalController::class, 'showInvoice'])->name('show');
            Route::get('/{invoice}/download', [\App\Http\Controllers\ClientPortalController::class, 'downloadInvoice'])->name('download');
        });

        // Documents
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ClientPortalController::class, 'documents'])->name('index');
            Route::get('/create', [\App\Http\Controllers\ClientPortalController::class, 'createDocument'])->name('create');
            Route::post('/', [\App\Http\Controllers\ClientPortalController::class, 'storeDocument'])->name('store');
            Route::get('/{document}/download', [\App\Http\Controllers\ClientPortalController::class, 'downloadDocument'])->name('download');
        });

        // Comments
        Route::post('/comments', [\App\Http\Controllers\ClientPortalController::class, 'storeComment'])->name('comments.store');
    });

// Payment Webhooks (outside auth middleware - called by payment providers)
Route::post('/webhooks/mollie', [App\Http\Controllers\WebhookController::class, 'mollie'])->name('webhooks.mollie');
Route::post('/webhooks/stripe', [App\Http\Controllers\WebhookController::class, 'stripe'])->name('webhooks.stripe');

// TEMPORARY: Fix session company_id
Route::get('/fix-session', function() {
    $user = auth()->user();
    if (!$user) {
        return redirect('/login')->with('error', 'Veuillez vous connecter d\'abord.');
    }

    // Get user's default company or first company
    $company = $user->defaultCompany() ?? $user->companies()->wherePivot('is_default', true)->first() ?? $user->companies()->first();

    if ($company) {
        session(['current_tenant_id' => $company->id]);
        return redirect('/dashboard')->with('success', 'Session corrigée! Company: ' . $company->name . '. Vous pouvez maintenant utiliser Peppol.');
    }

    return redirect('/dashboard')->with('error', 'Aucune company trouvée pour votre compte. Veuillez contacter un administrateur.');
})->middleware('auth')->name('fix-session');
