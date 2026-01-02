<?php

namespace App\Http\Controllers;

use App\Models\SocialSecurityPayment;
use App\Models\FiscalYear;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SocialSecurityPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialSecurityPayment::with(['fiscalYear', 'creator', 'validator'])
            ->where('company_id', auth()->user()->currentCompany->id);

        // Filters
        if ($request->filled('contribution_type')) {
            $query->where('contribution_type', $request->contribution_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('quarter')) {
            $query->where('quarter', $request->quarter);
        }

        $payments = $query->latest('year')
            ->latest('quarter')
            ->paginate(20);

        $years = SocialSecurityPayment::where('company_id', auth()->user()->currentCompany->id)
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values();

        return view('social-security.index', compact('payments', 'years'));
    }

    public function create()
    {
        $fiscalYears = FiscalYear::where('company_id', auth()->user()->currentCompany->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('social-security.create', compact('fiscalYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contribution_type' => 'required|in:onss_employer,onss_employee,dmfa,special_contribution,pension_fund,occupational_accident,occupational_disease,other',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'required|integer|min:1|max:4',
            'month' => 'nullable|integer|min:1|max:12',
            'period_label' => 'nullable|string|max:255',
            'gross_salary_base' => 'required|numeric|min:0',
            'employee_count' => 'required|integer|min:0',
            'employer_rate' => 'nullable|numeric|min:0|max:100',
            'employee_rate' => 'nullable|numeric|min:0|max:100',
            'employer_contribution' => 'required|numeric|min:0',
            'employee_contribution' => 'required|numeric|min:0',
            'total_contribution' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'notes' => 'nullable|string',
        ]);

        $payment = new SocialSecurityPayment($validated);
        $payment->id = Str::uuid();
        $payment->company_id = auth()->user()->currentCompany->id;
        $payment->created_by = auth()->id();
        $payment->status = 'draft';

        // Auto-generate period label if not provided
        if (!$request->filled('period_label')) {
            $payment->period_label = "T{$validated['quarter']} {$validated['year']}";
        }

        $payment->save();

        return redirect()->route('social-security.index')
            ->with('success', 'Cotisation sociale créée avec succès.');
    }

    public function show(SocialSecurityPayment $socialSecurityPayment)
    {
        $this->authorize('view', $socialSecurityPayment);

        $socialSecurityPayment->load(['fiscalYear', 'paymentTransaction', 'journalEntry', 'creator', 'validator']);

        return view('social-security.show', compact('socialSecurityPayment'));
    }

    public function edit(SocialSecurityPayment $socialSecurityPayment)
    {
        $this->authorize('update', $socialSecurityPayment);

        $fiscalYears = FiscalYear::where('company_id', auth()->user()->currentCompany->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('social-security.edit', compact('socialSecurityPayment', 'fiscalYears'));
    }

    public function update(Request $request, SocialSecurityPayment $socialSecurityPayment)
    {
        $this->authorize('update', $socialSecurityPayment);

        $validated = $request->validate([
            'contribution_type' => 'required|in:onss_employer,onss_employee,dmfa,special_contribution,pension_fund,occupational_accident,occupational_disease,other',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'required|integer|min:1|max:4',
            'month' => 'nullable|integer|min:1|max:12',
            'period_label' => 'nullable|string|max:255',
            'gross_salary_base' => 'required|numeric|min:0',
            'employee_count' => 'required|integer|min:0',
            'employer_rate' => 'nullable|numeric|min:0|max:100',
            'employee_rate' => 'nullable|numeric|min:0|max:100',
            'employer_contribution' => 'required|numeric|min:0',
            'employee_contribution' => 'required|numeric|min:0',
            'total_contribution' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'declaration_date' => 'nullable|date',
            'amount_paid' => 'nullable|numeric|min:0',
            'penalties' => 'nullable|numeric|min:0',
            'interests' => 'nullable|numeric|min:0',
            'onss_reference' => 'nullable|string|max:255',
            'dmfa_number' => 'nullable|string|max:255',
            'structured_communication' => 'nullable|string|max:255',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'notes' => 'nullable|string',
        ]);

        $socialSecurityPayment->update($validated);

        return redirect()->route('social-security.show', $socialSecurityPayment)
            ->with('success', 'Cotisation sociale mise à jour avec succès.');
    }

    public function destroy(SocialSecurityPayment $socialSecurityPayment)
    {
        $this->authorize('delete', $socialSecurityPayment);

        $socialSecurityPayment->delete();

        return redirect()->route('social-security.index')
            ->with('success', 'Cotisation sociale supprimée avec succès.');
    }

    public function markAsPaid(Request $request, SocialSecurityPayment $socialSecurityPayment)
    {
        $this->authorize('update', $socialSecurityPayment);

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_transaction_id' => 'nullable|exists:bank_transactions,id',
        ]);

        $socialSecurityPayment->markAsPaid(
            $validated['amount_paid'],
            $validated['payment_date'],
            $validated['payment_transaction_id'] ?? null
        );

        return redirect()->route('social-security.show', $socialSecurityPayment)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function calculateFromPayroll(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'required|integer|min:1|max:4',
        ]);

        // Get all payslips for the period
        $startMonth = ($validated['quarter'] - 1) * 3 + 1;
        $endMonth = $startMonth + 2;

        $payslips = Payslip::where('company_id', auth()->user()->currentCompany->id)
            ->whereYear('payment_date', $validated['year'])
            ->whereMonth('payment_date', '>=', $startMonth)
            ->whereMonth('payment_date', '<=', $endMonth)
            ->where('status', 'paid')
            ->get();

        if ($payslips->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucune fiche de paie trouvée pour cette période.');
        }

        // Calculate from payslips
        $payment = SocialSecurityPayment::calculateFromPayslips($payslips, $validated['year'], $validated['quarter']);
        $payment->company_id = auth()->user()->currentCompany->id;
        $payment->created_by = auth()->id();
        $payment->save();

        return redirect()->route('social-security.show', $payment)
            ->with('success', 'Cotisations sociales calculées automatiquement depuis les fiches de paie.');
    }
}
