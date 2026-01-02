<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class ContextAwarenessService
{
    /**
     * Detect current context from route and request.
     */
    public function detectContext(Request $request): array
    {
        $routeName = Route::currentRouteName();
        $routeParams = Route::current()->parameters();

        return [
            'route' => $routeName,
            'params' => $routeParams,
            'query' => $request->query(),
            'page_type' => $this->getPageType($routeName),
            'entity_type' => $this->getEntityType($routeName),
            'action' => $this->getAction($routeName),
        ];
    }

    /**
     * Get page type from route name.
     */
    protected function getPageType(string $routeName): string
    {
        if (str_contains($routeName, '.index')) return 'list';
        if (str_contains($routeName, '.create')) return 'create';
        if (str_contains($routeName, '.edit')) return 'edit';
        if (str_contains($routeName, '.show')) return 'detail';
        if ($routeName === 'dashboard') return 'dashboard';

        return 'other';
    }

    /**
     * Get entity type from route name.
     */
    protected function getEntityType(string $routeName): ?string
    {
        $parts = explode('.', $routeName);
        return $parts[0] ?? null;
    }

    /**
     * Get action from route name.
     */
    protected function getAction(string $routeName): ?string
    {
        $parts = explode('.', $routeName);
        return $parts[1] ?? null;
    }

    /**
     * Get contextual data for page.
     */
    public function getContextualData(string $routeName, array $params, $user): array
    {
        return match(true) {
            str_contains($routeName, 'invoices') => $this->getInvoiceContext($user, $params),
            str_contains($routeName, 'expenses') => $this->getExpenseContext($user, $params),
            str_contains($routeName, 'bank') => $this->getBankContext($user, $params),
            str_contains($routeName, 'vat') => $this->getVATContext($user, $params),
            $routeName === 'dashboard' => $this->getDashboardContext($user),
            default => [],
        };
    }

    /**
     * Get invoice-related context.
     */
    protected function getInvoiceContext($user, array $params): array
    {
        $companyId = $user->current_company_id;

        return [
            'total_invoices' => \App\Models\Invoice::where('company_id', $companyId)->count(),
            'overdue_count' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
            'overdue_amount' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->sum('total_amount'),
            'draft_count' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'draft')
                ->count(),
        ];
    }

    /**
     * Get expense-related context.
     */
    protected function getExpenseContext($user, array $params): array
    {
        $companyId = $user->current_company_id;

        return [
            'total_expenses' => \App\Models\Expense::where('company_id', $companyId)->count(),
            'uncategorized_count' => \App\Models\Expense::where('company_id', $companyId)
                ->whereNull('category')
                ->count(),
            'pending_approval_count' => \App\Models\Expense::where('company_id', $companyId)
                ->where('status', 'pending_approval')
                ->count(),
            'without_document_count' => \App\Models\Expense::where('company_id', $companyId)
                ->whereDoesntHave('documents')
                ->where('total_amount', '>', 100)
                ->count(),
        ];
    }

    /**
     * Get bank-related context.
     */
    protected function getBankContext($user, array $params): array
    {
        $companyId = $user->current_company_id;

        return [
            'current_balance' => \App\Models\BankTransaction::where('company_id', $companyId)
                ->sum('amount'),
            'unreconciled_count' => \App\Models\BankTransaction::where('company_id', $companyId)
                ->whereNull('reconciled_at')
                ->count(),
        ];
    }

    /**
     * Get VAT-related context.
     */
    protected function getVATContext($user, array $params): array
    {
        $companyId = $user->current_company_id;
        $currentPeriod = now()->startOfMonth();

        return [
            'current_period' => $currentPeriod->format('Y-m'),
            'due_date' => $currentPeriod->copy()->addMonth()->day(20),
            'total_vat_collected' => \App\Models\Invoice::where('company_id', $companyId)
                ->whereYear('issue_date', $currentPeriod->year)
                ->whereMonth('issue_date', $currentPeriod->month)
                ->sum('vat_amount'),
            'total_vat_paid' => \App\Models\Expense::where('company_id', $companyId)
                ->whereYear('expense_date', $currentPeriod->year)
                ->whereMonth('expense_date', $currentPeriod->month)
                ->sum('vat_amount'),
        ];
    }

    /**
     * Get dashboard context.
     */
    protected function getDashboardContext($user): array
    {
        $companyId = $user->current_company_id;

        return [
            'pending_tasks_count' => 0, // TODO: implement task system
            'overdue_invoices_count' => \App\Models\Invoice::where('company_id', $companyId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
            'low_stock_count' => 0, // TODO: implement if inventory module exists
        ];
    }

    /**
     * Inject context into page data for AI chat.
     */
    public function injectContextForChat(array $context, array $contextData): string
    {
        $prompt = "Contexte de la page actuelle:\n";
        $prompt .= "- Page: " . $context['page_type'] . "\n";
        $prompt .= "- Entité: " . ($context['entity_type'] ?? 'N/A') . "\n";

        if (!empty($contextData)) {
            $prompt .= "\nDonnées contextuelles:\n";
            foreach ($contextData as $key => $value) {
                if (is_numeric($value)) {
                    $prompt .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
                }
            }
        }

        return $prompt;
    }
}
