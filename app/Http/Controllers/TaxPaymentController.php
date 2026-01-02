<?php

namespace App\Http\Controllers;

use App\Models\TaxPayment;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaxPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = TaxPayment::with(['fiscalYear', 'creator', 'validator'])
            ->where('company_id', auth()->user()->currentCompany->id);

        // Filters
        if ($request->filled('tax_type')) {
            $query->where('tax_type', $request->tax_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $taxPayments = $query->latest('year')
            ->latest('quarter')
            ->paginate(20);

        $years = TaxPayment::where('company_id', auth()->user()->currentCompany->id)
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values();

        return view('tax-payments.index', compact('taxPayments', 'years'));
    }

    public function create()
    {
        $fiscalYears = FiscalYear::where('company_id', auth()->user()->currentCompany->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tax-payments.create', compact('fiscalYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_type' => 'required|in:isoc,ipp,professional_tax,vat,withholding_tax,registration_tax,property_tax,vehicle_tax,other',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'month' => 'nullable|integer|min:1|max:12',
            'period_label' => 'nullable|string|max:255',
            'taxable_base' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'required|numeric|min:0',
            'advance_payments' => 'nullable|numeric|min:0',
            'amount_due' => 'required|numeric',
            'due_date' => 'nullable|date',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'notes' => 'nullable|string',
        ]);

        $taxPayment = new TaxPayment($validated);
        $taxPayment->id = Str::uuid();
        $taxPayment->company_id = auth()->user()->currentCompany->id;
        $taxPayment->created_by = auth()->id();
        $taxPayment->status = 'draft';

        // Auto-generate period label if not provided
        if (!$request->filled('period_label')) {
            $taxPayment->period_label = $this->generatePeriodLabel($validated);
        }

        $taxPayment->save();

        return redirect()->route('tax-payments.index')
            ->with('success', 'Paiement d\'impôt créé avec succès.');
    }

    public function show(TaxPayment $taxPayment)
    {
        $this->authorize('view', $taxPayment);

        $taxPayment->load(['fiscalYear', 'paymentTransaction', 'journalEntry', 'creator', 'validator']);

        return view('tax-payments.show', compact('taxPayment'));
    }

    public function edit(TaxPayment $taxPayment)
    {
        $this->authorize('update', $taxPayment);

        $fiscalYears = FiscalYear::where('company_id', auth()->user()->currentCompany->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('tax-payments.edit', compact('taxPayment', 'fiscalYears'));
    }

    public function update(Request $request, TaxPayment $taxPayment)
    {
        $this->authorize('update', $taxPayment);

        $validated = $request->validate([
            'tax_type' => 'required|in:isoc,ipp,professional_tax,vat,withholding_tax,registration_tax,property_tax,vehicle_tax,other',
            'year' => 'required|integer|min:2000|max:2100',
            'quarter' => 'nullable|integer|min:1|max:4',
            'month' => 'nullable|integer|min:1|max:12',
            'period_label' => 'nullable|string|max:255',
            'taxable_base' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'required|numeric|min:0',
            'advance_payments' => 'nullable|numeric|min:0',
            'amount_due' => 'required|numeric',
            'due_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'declaration_date' => 'nullable|date',
            'amount_paid' => 'nullable|numeric|min:0',
            'penalties' => 'nullable|numeric|min:0',
            'interests' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'structured_communication' => 'nullable|string|max:255',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'notes' => 'nullable|string',
        ]);

        $taxPayment->update($validated);

        return redirect()->route('tax-payments.show', $taxPayment)
            ->with('success', 'Paiement d\'impôt mis à jour avec succès.');
    }

    public function destroy(TaxPayment $taxPayment)
    {
        $this->authorize('delete', $taxPayment);

        $taxPayment->delete();

        return redirect()->route('tax-payments.index')
            ->with('success', 'Paiement d\'impôt supprimé avec succès.');
    }

    public function markAsPaid(Request $request, TaxPayment $taxPayment)
    {
        $this->authorize('update', $taxPayment);

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_transaction_id' => 'nullable|exists:bank_transactions,id',
        ]);

        $taxPayment->markAsPaid(
            $validated['amount_paid'],
            $validated['payment_date'],
            $validated['payment_transaction_id'] ?? null
        );

        return redirect()->route('tax-payments.show', $taxPayment)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    protected function generatePeriodLabel(array $data): string
    {
        $label = '';

        if (!empty($data['quarter'])) {
            $label = "T{$data['quarter']} {$data['year']}";
        } elseif (!empty($data['month'])) {
            $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            $label = "{$months[$data['month']]} {$data['year']}";
        } else {
            $label = "Année {$data['year']}";
        }

        return $label;
    }
}
