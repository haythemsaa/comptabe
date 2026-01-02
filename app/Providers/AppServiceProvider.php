<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\ChartOfAccount;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalRequest;
use App\Observers\InvoiceObserver;
use App\Policies\AccountPolicy;
use App\Policies\ApprovalPolicy;
use App\Services\AI\Chat\ToolRegistry;
use Illuminate\Support\Facades\Gate;
use App\Services\AI\Chat\Tools\Tenant\ReadInvoicesTool;
use App\Services\AI\Chat\Tools\Tenant\CreateInvoiceTool;
use App\Services\AI\Chat\Tools\Tenant\CreateQuoteTool;
use App\Services\AI\Chat\Tools\Tenant\SearchPartnersTool;
use App\Services\AI\Chat\Tools\Tenant\RecordPaymentTool;
use App\Services\AI\Chat\Tools\Tenant\InviteUserTool;
use App\Services\AI\Chat\Tools\Tenant\SendInvoiceEmailTool;
use App\Services\AI\Chat\Tools\Tenant\ConvertQuoteToInvoiceTool;
use App\Services\AI\Chat\Tools\Tenant\CreatePartnerTool;
use App\Services\AI\Chat\Tools\Tenant\GenerateVATDeclarationTool;
use App\Services\AI\Chat\Tools\Tenant\SendViaPeppolTool;
use App\Services\AI\Chat\Tools\Tenant\UpdateInvoiceTool;
use App\Services\AI\Chat\Tools\Tenant\DeleteInvoiceTool;
use App\Services\AI\Chat\Tools\Tenant\ReconcileBankTransactionTool;
use App\Services\AI\Chat\Tools\Tenant\CreateExpenseTool;
use App\Services\AI\Chat\Tools\Tenant\ExportAccountingDataTool;
use App\Services\AI\Chat\Tools\Firm\GetAllClientsDataTool;
use App\Services\AI\Chat\Tools\Firm\BulkExportAccountingTool;
use App\Services\AI\Chat\Tools\Firm\GenerateMultiClientReportTool;
use App\Services\AI\Chat\Tools\Firm\AssignMandateTaskTool;
use App\Services\AI\Chat\Tools\Firm\GetClientHealthScoreTool;
use App\Services\AI\Chat\Tools\Payroll\CreateEmployeeTool;
use App\Services\AI\Chat\Tools\Payroll\GeneratePayslipTool;
use App\Services\AI\Chat\Tools\Tenant\CreateInvoiceTemplateTool;
use App\Services\AI\Chat\Tools\Tenant\CreateRecurringInvoiceTool;
use App\Services\AI\Chat\Tools\Tenant\ConfigureRemindersTool;
use App\Services\AI\Chat\Tools\Superadmin\CreateDemoAccountTool;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Number;
use App\View\Composers\CompanyConfigComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Strict mode for development
        Model::shouldBeStrict(!app()->isProduction());

        // Register model observers
        Invoice::observe(InvoiceObserver::class);

        // Share company country configuration with all views
        View::composer('*', CompanyConfigComposer::class);

        // Register policies
        $this->registerPolicies();

        // Register custom Blade directives
        $this->registerBladeDirectives();

        // Configure API rate limiting
        $this->configureRateLimiting();

        // Register AI chat tools
        $this->registerChatTools();

        // Set default currency formatting for Belgium
        Number::useLocale('fr_BE');
    }

    /**
     * Register authorization policies.
     */
    protected function registerPolicies(): void
    {
        // Register policies for models that don't follow standard naming convention
        Gate::policy(ChartOfAccount::class, AccountPolicy::class);
        Gate::policy(ApprovalWorkflow::class, ApprovalPolicy::class);
        Gate::policy(ApprovalRequest::class, ApprovalPolicy::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiter: 60 requests per minute for authenticated users
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiter for sensitive operations: 10 per minute
        RateLimiter::for('api-sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Public endpoints: 30 per minute by IP
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Webhook endpoints: 100 per minute (higher for integration needs)
        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        // Login attempts: 5 per minute
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });
    }

    /**
     * Register AI chat tools.
     */
    protected function registerChatTools(): void
    {
        $registry = app(ToolRegistry::class);

        // Register tenant tools - Basic operations
        $registry->register(new ReadInvoicesTool());
        $registry->register(new CreateInvoiceTool());
        $registry->register(new CreateQuoteTool());
        $registry->register(new SearchPartnersTool());
        $registry->register(new CreatePartnerTool());
        $registry->register(new RecordPaymentTool());
        $registry->register(new InviteUserTool());

        // Register tenant tools - Advanced operations
        $registry->register(new SendInvoiceEmailTool());
        $registry->register(new ConvertQuoteToInvoiceTool());
        $registry->register(new GenerateVATDeclarationTool());
        $registry->register(app(SendViaPeppolTool::class)); // Uses dependency injection

        // Register tenant tools - Management & Export
        $registry->register(new UpdateInvoiceTool());
        $registry->register(new DeleteInvoiceTool());
        $registry->register(new ReconcileBankTransactionTool());
        $registry->register(new CreateExpenseTool());
        $registry->register(new ExportAccountingDataTool());

        // Register tenant tools - Invoice Automation
        $registry->register(new CreateInvoiceTemplateTool());
        $registry->register(new CreateRecurringInvoiceTool());
        $registry->register(new ConfigureRemindersTool());

        // Register firm tools - Accounting Firm / Fiduciary operations
        $registry->register(new GetAllClientsDataTool());
        $registry->register(new BulkExportAccountingTool());
        $registry->register(new GenerateMultiClientReportTool());
        $registry->register(new AssignMandateTaskTool());
        $registry->register(new GetClientHealthScoreTool());

        // Register payroll tools - HR & Payroll management
        $registry->register(new CreateEmployeeTool());
        $registry->register(new GeneratePayslipTool());

        // Register superadmin tools
        $registry->register(new CreateDemoAccountTool());
    }

    /**
     * Register custom Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        // Currency formatting
        Blade::directive('currency', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', ' ') . ' €'; ?>";
        });

        // Belgian VAT number formatting
        Blade::directive('vatNumber', function ($expression) {
            return "<?php
                \$vat = preg_replace('/[^0-9]/', '', $expression);
                echo 'BE ' . substr(\$vat, 0, 4) . '.' . substr(\$vat, 4, 3) . '.' . substr(\$vat, 7);
            ?>";
        });

        // IBAN formatting
        Blade::directive('iban', function ($expression) {
            return "<?php
                \$iban = strtoupper(preg_replace('/\s+/', '', $expression));
                echo implode(' ', str_split(\$iban, 4));
            ?>";
        });

        // Date formatting (Belgian format)
        Blade::directive('dateFormat', function ($expression) {
            return "<?php echo ($expression)?->format('d/m/Y') ?? '-'; ?>";
        });

        // Datetime formatting
        Blade::directive('datetimeFormat', function ($expression) {
            return "<?php echo ($expression)?->format('d/m/Y H:i') ?? '-'; ?>";
        });

        // Role check directive
        Blade::if('role', function ($role) {
            $user = auth()->user();
            $tenantId = session('current_tenant_id');
            return $user && $user->getRoleInCompany($tenantId) === $role;
        });

        // Admin check directive
        Blade::if('admin', function () {
            return auth()->user()?->isAdminInCurrentTenant();
        });
    }
}
