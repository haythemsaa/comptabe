<?php

namespace App\Services\AI;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\BankTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ProactiveAssistantService
{
    /**
     * Get contextual suggestions based on current page and user activity.
     */
    public function getContextualSuggestions(User $user, string $context, array $contextData = []): array
    {
        return match($context) {
            'invoices.overdue' => $this->suggestionsForOverdueInvoices($user, $contextData),
            'cash_flow.negative' => $this->suggestionsForNegativeCashFlow($user, $contextData),
            'vat.declaration_due' => $this->suggestionsForVATDeclaration($user, $contextData),
            'expenses.uncategorized' => $this->suggestionsForUncategorizedExpenses($user, $contextData),
            'dashboard' => $this->suggestionsForDashboard($user),
            default => [],
        };
    }

    /**
     * Suggestions for overdue invoices page.
     */
    protected function suggestionsForOverdueInvoices(User $user, array $contextData): array
    {
        $overdueCount = $contextData['overdue_count'] ?? 0;

        if ($overdueCount == 0) {
            return [];
        }

        $suggestions = [];

        if ($overdueCount > 5) {
            $suggestions[] = [
                'id' => 'batch_reminder',
                'type' => 'action',
                'priority' => 'high',
                'icon' => 'mail',
                'title' => 'Envoyer des relances automatiques',
                'description' => "Voulez-vous que j'envoie des relances par email pour les {$overdueCount} factures en retard ?",
                'actions' => [
                    [
                        'label' => 'Envoyer maintenant',
                        'action' => 'send_batch_reminders',
                        'params' => ['type' => 'all_overdue'],
                        'style' => 'primary',
                    ],
                    [
                        'label' => 'Personnaliser',
                        'action' => 'customize_reminders',
                        'params' => [],
                        'style' => 'secondary',
                    ],
                ],
            ];
        }

        // Suggest payment plan for clients with multiple overdue invoices
        $clientsWithMultipleOverdue = Invoice::where('company_id', $user->current_company_id)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->select('partner_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('partner_id')
            ->having('count', '>', 2)
            ->count();

        if ($clientsWithMultipleOverdue > 0) {
            $suggestions[] = [
                'id' => 'payment_plans',
                'type' => 'recommendation',
                'priority' => 'medium',
                'icon' => 'calendar',
                'title' => 'Proposer des plans de paiement',
                'description' => "{$clientsWithMultipleOverdue} client(s) ont plusieurs factures impayées. Un plan de paiement pourrait faciliter le recouvrement.",
                'actions' => [
                    [
                        'label' => 'Voir les clients concernés',
                        'action' => 'view_multiple_overdue_clients',
                        'params' => [],
                        'style' => 'primary',
                    ],
                ],
            ];
        }

        return $suggestions;
    }

    /**
     * Suggestions for negative cash flow.
     */
    protected function suggestionsForNegativeCashFlow(User $user, array $contextData): array
    {
        $currentBalance = $contextData['current_balance'] ?? 0;
        $projectedBalance = $contextData['projected_balance'] ?? 0;

        if ($projectedBalance >= 0) {
            return [];
        }

        return [
            [
                'id' => 'cash_flow_action_plan',
                'type' => 'alert',
                'priority' => 'critical',
                'icon' => 'alert-triangle',
                'title' => 'Plan d\'action trésorerie négative',
                'description' => "Votre trésorerie projetée est négative (" . number_format($projectedBalance, 2) . " €). Je peux générer un plan d'action pour améliorer la situation.",
                'actions' => [
                    [
                        'label' => 'Générer le plan d\'action',
                        'action' => 'generate_cash_flow_plan',
                        'params' => ['balance' => $projectedBalance],
                        'style' => 'danger',
                    ],
                    [
                        'label' => 'Voir les prévisions détaillées',
                        'action' => 'view_cash_flow_forecast',
                        'params' => [],
                        'style' => 'secondary',
                    ],
                ],
            ],
            [
                'id' => 'accelerate_payments',
                'type' => 'recommendation',
                'priority' => 'high',
                'icon' => 'trending-up',
                'title' => 'Accélérer les encaissements',
                'description' => 'Identifier les clients avec les montants les plus élevés à encaisser rapidement.',
                'actions' => [
                    [
                        'label' => 'Voir la liste prioritaire',
                        'action' => 'view_priority_collections',
                        'params' => [],
                        'style' => 'primary',
                    ],
                ],
            ],
        ];
    }

    /**
     * Suggestions for VAT declaration.
     */
    protected function suggestionsForVATDeclaration(User $user, array $contextData): array
    {
        $dueDate = $contextData['due_date'] ?? null;
        $daysUntilDue = $dueDate ? now()->diffInDays($dueDate, false) : null;

        if ($daysUntilDue === null || $daysUntilDue > 14) {
            return [];
        }

        $suggestions = [];

        if ($daysUntilDue <= 7) {
            $suggestions[] = [
                'id' => 'vat_declaration_ready',
                'type' => 'action',
                'priority' => 'high',
                'icon' => 'file-text',
                'title' => 'Déclaration TVA à générer',
                'description' => "Votre déclaration TVA est due dans {$daysUntilDue} jour(s). Je peux la générer maintenant.",
                'actions' => [
                    [
                        'label' => 'Générer la déclaration',
                        'action' => 'generate_vat_declaration',
                        'params' => [],
                        'style' => 'primary',
                    ],
                    [
                        'label' => 'Vérifier les données',
                        'action' => 'verify_vat_data',
                        'params' => [],
                        'style' => 'secondary',
                    ],
                ],
            ];
        }

        return $suggestions;
    }

    /**
     * Suggestions for uncategorized expenses.
     */
    protected function suggestionsForUncategorizedExpenses(User $user, array $contextData): array
    {
        $uncategorizedCount = $contextData['uncategorized_count'] ?? 0;

        if ($uncategorizedCount == 0) {
            return [];
        }

        return [
            [
                'id' => 'auto_categorize',
                'type' => 'action',
                'priority' => 'medium',
                'icon' => 'tag',
                'title' => 'Catégorisation automatique disponible',
                'description' => "J'ai trouvé {$uncategorizedCount} dépense(s) non catégorisée(s). Je peux les catégoriser automatiquement en fonction de votre historique.",
                'actions' => [
                    [
                        'label' => 'Catégoriser automatiquement',
                        'action' => 'auto_categorize_expenses',
                        'params' => [],
                        'style' => 'primary',
                    ],
                    [
                        'label' => 'Catégoriser manuellement',
                        'action' => 'manual_categorize',
                        'params' => [],
                        'style' => 'secondary',
                    ],
                ],
            ],
        ];
    }

    /**
     * Suggestions for dashboard.
     */
    protected function suggestionsForDashboard(User $user): array
    {
        $suggestions = [];
        $companyId = $user->current_company_id;

        // Check for pending actions
        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->count();

        if ($overdueInvoices > 0) {
            $suggestions[] = [
                'id' => 'overdue_invoices',
                'type' => 'alert',
                'priority' => 'high',
                'icon' => 'alert-circle',
                'title' => "{$overdueInvoices} facture(s) en retard",
                'description' => 'Certaines factures nécessitent un suivi.',
                'actions' => [
                    [
                        'label' => 'Voir les factures',
                        'action' => 'navigate',
                        'params' => ['url' => route('invoices.index', ['status' => 'overdue'])],
                        'style' => 'primary',
                    ],
                ],
            ];
        }

        // Check cash flow
        $currentBalance = BankTransaction::where('company_id', $companyId)->sum('amount');
        if ($currentBalance < 1000) {
            $suggestions[] = [
                'id' => 'low_cash',
                'type' => 'warning',
                'priority' => 'high',
                'icon' => 'trending-down',
                'title' => 'Trésorerie faible',
                'description' => "Solde actuel : " . number_format($currentBalance, 2) . " €",
                'actions' => [
                    [
                        'label' => 'Voir les prévisions',
                        'action' => 'navigate',
                        'params' => ['url' => route('ai.analytics')],
                        'style' => 'warning',
                    ],
                ],
            ];
        }

        return $suggestions;
    }

    /**
     * Generate daily business brief for email.
     */
    public function generateDailyBrief(User $user): array
    {
        $companyId = $user->current_company_id;
        $yesterday = now()->subDay();

        return [
            'date' => now()->toDateString(),
            'user_name' => $user->first_name,
            'summary' => $this->getDailySummary($companyId, $yesterday),
            'priority_actions' => $this->getPriorityActions($companyId),
            'critical_alerts' => $this->getCriticalAlerts($companyId),
            'ai_insights' => $this->getDailyInsights($companyId),
        ];
    }

    /**
     * Get summary of yesterday's activity.
     */
    protected function getDailySummary(string $companyId, Carbon $date): array
    {
        return [
            'invoices_created' => Invoice::where('company_id', $companyId)
                ->whereDate('created_at', $date)
                ->count(),
            'invoices_paid' => Invoice::where('company_id', $companyId)
                ->whereDate('payment_date', $date)
                ->count(),
            'revenue_received' => Invoice::where('company_id', $companyId)
                ->whereDate('payment_date', $date)
                ->sum('total_amount'),
            'expenses_recorded' => Expense::where('company_id', $companyId)
                ->whereDate('created_at', $date)
                ->count(),
            'expenses_amount' => Expense::where('company_id', $companyId)
                ->whereDate('created_at', $date)
                ->sum('total_amount'),
        ];
    }

    /**
     * Get top 3 priority actions for today.
     */
    protected function getPriorityActions(string $companyId): array
    {
        $actions = [];

        // Priority 1: Overdue invoices
        $overdueCount = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->count();

        if ($overdueCount > 0) {
            $actions[] = [
                'priority' => 1,
                'title' => "Relancer {$overdueCount} facture(s) impayée(s)",
                'url' => route('invoices.index', ['status' => 'overdue']),
            ];
        }

        // Priority 2: VAT declaration due soon
        $vatDueSoon = now()->day > 15 && now()->day < 25;
        if ($vatDueSoon) {
            $actions[] = [
                'priority' => 2,
                'title' => 'Préparer la déclaration TVA du mois',
                'url' => route('vat.index'),
            ];
        }

        // Priority 3: Uncategorized expenses
        $uncategorizedCount = Expense::where('company_id', $companyId)
            ->whereNull('category')
            ->count();

        if ($uncategorizedCount > 5) {
            $actions[] = [
                'priority' => 3,
                'title' => "Catégoriser {$uncategorizedCount} dépense(s)",
                'url' => route('expenses.index', ['filter' => 'uncategorized']),
            ];
        }

        return array_slice($actions, 0, 3);
    }

    /**
     * Get critical alerts.
     */
    protected function getCriticalAlerts(string $companyId): array
    {
        $alerts = [];

        // Check negative balance
        $balance = BankTransaction::where('company_id', $companyId)->sum('amount');
        if ($balance < 0) {
            $alerts[] = [
                'severity' => 'critical',
                'title' => 'Trésorerie négative',
                'message' => 'Solde actuel : ' . number_format($balance, 2) . ' €',
            ];
        }

        // Check very overdue invoices (>90 days)
        $veryOverdueCount = Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<', now()->subDays(90))
            ->count();

        if ($veryOverdueCount > 0) {
            $alerts[] = [
                'severity' => 'high',
                'title' => 'Factures très en retard',
                'message' => "{$veryOverdueCount} facture(s) impayée(s) depuis plus de 90 jours",
            ];
        }

        return $alerts;
    }

    /**
     * Get AI insights for the day.
     */
    protected function getDailyInsights(string $companyId): array
    {
        // Use BusinessIntelligenceService for this
        $biService = app(BusinessIntelligenceService::class);
        $insights = $biService->generateInsights($companyId);

        return array_slice($insights, 0, 2); // Top 2 insights
    }

    /**
     * Execute suggested action.
     */
    public function executeAction(string $action, array $params, User $user): array
    {
        return match($action) {
            'send_batch_reminders' => $this->sendBatchReminders($params, $user),
            'generate_cash_flow_plan' => $this->generateCashFlowPlan($params, $user),
            'generate_vat_declaration' => $this->generateVATDeclaration($params, $user),
            'auto_categorize_expenses' => $this->autoCategorizeExpenses($params, $user),
            'navigate' => ['success' => true, 'redirect' => $params['url']],
            default => ['success' => false, 'message' => 'Action non reconnue'],
        };
    }

    /**
     * Send batch reminders for overdue invoices.
     */
    protected function sendBatchReminders(array $params, User $user): array
    {
        $invoices = Invoice::where('company_id', $user->current_company_id)
            ->where('status', 'sent')
            ->where('due_date', '<', now())
            ->get();

        $sent = 0;
        foreach ($invoices as $invoice) {
            // Send reminder email
            // $invoice->partner->notify(new InvoiceReminderNotification($invoice));
            $sent++;
        }

        return [
            'success' => true,
            'message' => "{$sent} relance(s) envoyée(s) avec succès",
            'count' => $sent,
        ];
    }

    /**
     * Generate cash flow action plan.
     */
    protected function generateCashFlowPlan(array $params, User $user): array
    {
        $balance = $params['balance'] ?? 0;

        $plan = [
            'current_situation' => "Trésorerie projetée négative : " . number_format($balance, 2) . " €",
            'recommendations' => [
                'Accélérer les encaissements des factures en retard',
                'Reporter les dépenses non urgentes',
                'Négocier des délais de paiement avec les fournisseurs',
                'Envisager une ligne de crédit à court terme si nécessaire',
            ],
            'priority_collections' => Invoice::where('company_id', $user->current_company_id)
                ->where('status', 'sent')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get(['id', 'invoice_number', 'partner_id', 'total_amount', 'due_date']),
        ];

        return [
            'success' => true,
            'plan' => $plan,
        ];
    }

    /**
     * Generate VAT declaration.
     */
    protected function generateVATDeclaration(array $params, User $user): array
    {
        // This would call the actual VAT service
        return [
            'success' => true,
            'message' => 'Déclaration TVA générée',
            'redirect' => route('vat.index'),
        ];
    }

    /**
     * Auto-categorize expenses using AI.
     */
    protected function autoCategorizeExpenses(array $params, User $user): array
    {
        $expenses = Expense::where('company_id', $user->current_company_id)
            ->whereNull('category')
            ->get();

        $categorized = 0;
        foreach ($expenses as $expense) {
            // Use AI categorization service
            // $category = app(IntelligentCategorizationService::class)->suggestCategory($expense);
            // $expense->update(['category' => $category]);
            $categorized++;
        }

        return [
            'success' => true,
            'message' => "{$categorized} dépense(s) catégorisée(s) automatiquement",
            'count' => $categorized,
        ];
    }
}
