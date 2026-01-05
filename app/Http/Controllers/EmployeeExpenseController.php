<?php

namespace App\Http\Controllers;

use App\Models\EmployeeExpense;
use App\Models\ExpenseAttachment;
use App\Models\ExpenseCategory;
use App\Models\ExpensePolicy;
use App\Models\ExpenseReport;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeExpenseController extends Controller
{
    // ========== DASHBOARD ==========

    public function dashboard()
    {
        $companyId = auth()->user()->current_company_id;
        $userId = auth()->id();
        $currentMonth = now()->startOfMonth();

        // My expenses stats
        $myStats = [
            'draft' => EmployeeExpense::where('company_id', $companyId)
                ->forUser($userId)->draft()->count(),
            'pending' => EmployeeExpense::where('company_id', $companyId)
                ->forUser($userId)->pending()->count(),
            'approved' => EmployeeExpense::where('company_id', $companyId)
                ->forUser($userId)->approved()->count(),
            'month_total' => EmployeeExpense::where('company_id', $companyId)
                ->forUser($userId)
                ->where('expense_date', '>=', $currentMonth)
                ->sum('amount'),
        ];

        // My recent expenses
        $myExpenses = EmployeeExpense::where('company_id', $companyId)
            ->forUser($userId)
            ->with(['category', 'expenseReport'])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        // My reports
        $myReports = ExpenseReport::where('company_id', $companyId)
            ->forUser($userId)
            ->with('expenses')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Pending approval (for managers)
        $pendingApproval = ExpenseReport::where('company_id', $companyId)
            ->awaitingApproval()
            ->with(['user', 'expenses'])
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        // Expenses by category this month
        $byCategory = EmployeeExpense::where('company_id', $companyId)
            ->forUser($userId)
            ->where('expense_date', '>=', $currentMonth)
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // Categories for quick add
        $categories = ExpenseCategory::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('expenses.dashboard', compact(
            'myStats',
            'myExpenses',
            'myReports',
            'pendingApproval',
            'byCategory',
            'categories'
        ));
    }

    // ========== MY EXPENSES ==========

    public function index(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $query = EmployeeExpense::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->with(['category', 'expenseReport']);

        // Filters
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                    ->orWhere('merchant', 'like', "%{$request->search}%");
            });
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(20);

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->active()->ordered()->get();

        // Stats
        $stats = [
            'total' => EmployeeExpense::where('company_id', $companyId)
                ->forUser(auth()->id())->count(),
            'pending' => EmployeeExpense::where('company_id', $companyId)
                ->forUser(auth()->id())->pending()->count(),
            'this_month' => EmployeeExpense::where('company_id', $companyId)
                ->forUser(auth()->id())
                ->whereMonth('expense_date', now()->month)
                ->sum('amount'),
        ];

        return view('expenses.index', compact('expenses', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->active()->ordered()->get();

        $reports = ExpenseReport::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->whereIn('status', ['draft', 'rejected'])
            ->orderByDesc('created_at')
            ->get();

        $partners = Partner::where('company_id', $companyId)
            ->active()->orderBy('name')->get();

        $selectedCategory = $request->category
            ? $categories->firstWhere('id', $request->category)
            : null;

        return view('expenses.create', compact(
            'categories',
            'reports',
            'partners',
            'selectedCategory'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:expense_categories,id',
            'expense_report_id' => 'nullable|uuid|exists:expense_reports,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'merchant' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'required|in:personal_card,company_card,cash,bank_transfer,other',
            'is_billable' => 'boolean',
            'partner_id' => 'nullable|uuid|exists:partners,id',
            'is_mileage' => 'boolean',
            'distance_km' => 'nullable|numeric|min:0',
            'departure' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|in:car,motorcycle,bike,electric',
            'notes' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $companyId = auth()->user()->current_company_id;

        // Check policy
        $policy = ExpensePolicy::getApplicablePolicy($companyId, $validated['category_id'] ?? null);
        if ($policy) {
            $violations = $policy->checkExpense(new EmployeeExpense($validated));
            // Log violations but don't block (warn only)
        }

        DB::beginTransaction();
        try {
            $expense = EmployeeExpense::create([
                ...$validated,
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'status' => 'draft',
                'vat_rate' => $validated['vat_rate'] ?? 21,
                'currency' => $validated['currency'] ?? 'EUR',
                'is_billable' => $validated['is_billable'] ?? false,
                'is_mileage' => $validated['is_mileage'] ?? false,
            ]);

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $path = $file->store("expenses/{$companyId}/{$expense->id}", 'private');

                $expense->update([
                    'receipt_path' => $path,
                    'receipt_original_name' => $file->getClientOriginalName(),
                    'has_receipt' => true,
                ]);

                ExpenseAttachment::create([
                    'employee_expense_id' => $expense->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'type' => 'receipt',
                ]);
            }

            // Update report totals if assigned
            if ($expense->expense_report_id) {
                $expense->expenseReport->recalculateTotals();
            }

            DB::commit();

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Dépense créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    public function show(EmployeeExpense $expense)
    {
        $this->authorizeExpense($expense);

        $expense->load(['category', 'expenseReport', 'user', 'attachments', 'partner']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(EmployeeExpense $expense)
    {
        $this->authorizeExpense($expense);

        if (!$expense->canBeEdited()) {
            return redirect()->route('expenses.show', $expense)
                ->with('error', 'Cette dépense ne peut plus être modifiée.');
        }

        $companyId = auth()->user()->current_company_id;

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->active()->ordered()->get();

        $reports = ExpenseReport::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->whereIn('status', ['draft', 'rejected'])
            ->orderByDesc('created_at')
            ->get();

        $partners = Partner::where('company_id', $companyId)
            ->active()->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'reports', 'partners'));
    }

    public function update(Request $request, EmployeeExpense $expense)
    {
        $this->authorizeExpense($expense);

        if (!$expense->canBeEdited()) {
            return redirect()->route('expenses.show', $expense)
                ->with('error', 'Cette dépense ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'category_id' => 'nullable|uuid|exists:expense_categories,id',
            'expense_report_id' => 'nullable|uuid|exists:expense_reports,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'merchant' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|in:personal_card,company_card,cash,bank_transfer,other',
            'is_billable' => 'boolean',
            'partner_id' => 'nullable|uuid|exists:partners,id',
            'is_mileage' => 'boolean',
            'distance_km' => 'nullable|numeric|min:0',
            'departure' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'vehicle_type' => 'nullable|in:car,motorcycle,bike,electric',
            'notes' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $oldReportId = $expense->expense_report_id;

            $expense->update([
                ...$validated,
                'status' => 'draft', // Reset to draft on edit
                'rejection_reason' => null,
                'is_billable' => $validated['is_billable'] ?? false,
                'is_mileage' => $validated['is_mileage'] ?? false,
            ]);

            // Handle new receipt
            if ($request->hasFile('receipt')) {
                // Delete old receipt
                if ($expense->receipt_path) {
                    Storage::disk('private')->delete($expense->receipt_path);
                }

                $file = $request->file('receipt');
                $path = $file->store("expenses/{$expense->company_id}/{$expense->id}", 'private');

                $expense->update([
                    'receipt_path' => $path,
                    'receipt_original_name' => $file->getClientOriginalName(),
                    'has_receipt' => true,
                ]);
            }

            // Update report totals
            if ($oldReportId && $oldReportId !== $expense->expense_report_id) {
                ExpenseReport::find($oldReportId)?->recalculateTotals();
            }
            if ($expense->expense_report_id) {
                $expense->expenseReport->recalculateTotals();
            }

            DB::commit();

            return redirect()->route('expenses.show', $expense)
                ->with('success', 'Dépense mise à jour.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function destroy(EmployeeExpense $expense)
    {
        $this->authorizeExpense($expense);

        if (!$expense->canBeEdited()) {
            return redirect()->route('expenses.index')
                ->with('error', 'Cette dépense ne peut pas être supprimée.');
        }

        $reportId = $expense->expense_report_id;

        $expense->delete();

        // Update report totals
        if ($reportId) {
            ExpenseReport::find($reportId)?->recalculateTotals();
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Dépense supprimée.');
    }

    // ========== EXPENSE REPORTS ==========

    public function reports(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $query = ExpenseReport::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->with(['expenses', 'approvedBy']);

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'draft' => ExpenseReport::where('company_id', $companyId)
                ->forUser(auth()->id())->draft()->count(),
            'pending' => ExpenseReport::where('company_id', $companyId)
                ->forUser(auth()->id())->pending()->count(),
            'approved' => ExpenseReport::where('company_id', $companyId)
                ->forUser(auth()->id())->approved()->count(),
        ];

        return view('expenses.reports.index', compact('reports', 'stats'));
    }

    public function createReport()
    {
        $companyId = auth()->user()->current_company_id;

        // Get unassigned expenses
        $unassignedExpenses = EmployeeExpense::where('company_id', $companyId)
            ->forUser(auth()->id())
            ->unassigned()
            ->draft()
            ->with('category')
            ->orderByDesc('expense_date')
            ->get();

        return view('expenses.reports.create', compact('unassignedExpenses'));
    }

    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'expense_ids' => 'nullable|array',
            'expense_ids.*' => 'uuid|exists:employee_expenses,id',
        ]);

        $companyId = auth()->user()->current_company_id;

        DB::beginTransaction();
        try {
            $report = ExpenseReport::create([
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'reference' => ExpenseReport::generateReference($companyId),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'period_start' => $validated['period_start'] ?? null,
                'period_end' => $validated['period_end'] ?? null,
                'status' => 'draft',
            ]);

            // Assign selected expenses
            if (!empty($validated['expense_ids'])) {
                EmployeeExpense::whereIn('id', $validated['expense_ids'])
                    ->where('user_id', auth()->id())
                    ->update(['expense_report_id' => $report->id]);

                $report->recalculateTotals();
            }

            DB::commit();

            return redirect()->route('expenses.reports.show', $report)
                ->with('success', 'Rapport créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function showReport(ExpenseReport $report)
    {
        $this->authorizeReport($report);

        $report->load(['expenses.category', 'user', 'approvedBy', 'paidBy']);

        return view('expenses.reports.show', compact('report'));
    }

    public function submitReport(ExpenseReport $report)
    {
        $this->authorizeReport($report);

        if (!$report->canBeSubmitted()) {
            return back()->with('error', 'Ce rapport ne peut pas être soumis.');
        }

        $report->submit();

        return back()->with('success', 'Rapport soumis pour approbation.');
    }

    // ========== APPROVAL (Manager) ==========

    public function pendingApproval(Request $request)
    {
        $companyId = auth()->user()->current_company_id;

        $reports = ExpenseReport::where('company_id', $companyId)
            ->awaitingApproval()
            ->with(['user', 'expenses.category'])
            ->orderBy('created_at')
            ->paginate(20);

        $stats = [
            'pending_count' => $reports->total(),
            'pending_amount' => ExpenseReport::where('company_id', $companyId)
                ->awaitingApproval()->sum('total_amount'),
        ];

        return view('expenses.approval.index', compact('reports', 'stats'));
    }

    public function reviewReport(ExpenseReport $report)
    {
        $report->load(['user', 'expenses.category', 'expenses.attachments']);

        return view('expenses.approval.review', compact('report'));
    }

    public function approveReport(Request $request, ExpenseReport $report)
    {
        $validated = $request->validate([
            'approved_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $report->approve(
            auth()->user(),
            $validated['approved_amount'] ?? null
        );

        return redirect()->route('expenses.approval.index')
            ->with('success', 'Rapport approuvé.');
    }

    public function rejectReport(Request $request, ExpenseReport $report)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $report->reject($validated['rejection_reason'], auth()->user());

        return redirect()->route('expenses.approval.index')
            ->with('success', 'Rapport rejeté.');
    }

    // ========== CATEGORIES ==========

    public function categories()
    {
        $companyId = auth()->user()->current_company_id;

        $categories = ExpenseCategory::where('company_id', $companyId)
            ->withCount('expenses')
            ->ordered()
            ->get();

        return view('expenses.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'account_code' => 'nullable|string|max:20',
            'default_vat_rate' => 'nullable|numeric|min:0|max:100',
            'max_amount' => 'nullable|numeric|min:0',
            'requires_receipt' => 'boolean',
            'requires_approval' => 'boolean',
            'is_mileage' => 'boolean',
            'mileage_rate' => 'nullable|numeric|min:0',
        ]);

        $companyId = auth()->user()->current_company_id;

        ExpenseCategory::create([
            ...$validated,
            'company_id' => $companyId,
            'is_active' => true,
        ]);

        return back()->with('success', 'Catégorie créée.');
    }

    // ========== HELPERS ==========

    private function authorizeExpense(EmployeeExpense $expense): void
    {
        $companyId = auth()->user()->current_company_id;

        if ($expense->company_id !== $companyId) {
            abort(403);
        }

        // Only owner or manager can view
        if ($expense->user_id !== auth()->id()) {
            // TODO: Check if user is manager
        }
    }

    private function authorizeReport(ExpenseReport $report): void
    {
        $companyId = auth()->user()->current_company_id;

        if ($report->company_id !== $companyId) {
            abort(403);
        }

        if ($report->user_id !== auth()->id()) {
            // TODO: Check if user is manager
        }
    }
}
