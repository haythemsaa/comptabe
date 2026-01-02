<?php

namespace App\Http\Controllers;

use App\Models\RecurringInvoice;
use App\Models\Partner;
use App\Models\VatCode;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceController extends Controller
{
    /**
     * Display a listing of recurring invoices.
     */
    public function index(Request $request)
    {
        $query = RecurringInvoice::with(['partner', 'creator'])
            ->latest('created_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner')) {
            $query->where('partner_id', $request->partner);
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $recurringInvoices = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => RecurringInvoice::count(),
            'active' => RecurringInvoice::where('status', 'active')->count(),
            'paused' => RecurringInvoice::where('status', 'paused')->count(),
            'due_today' => RecurringInvoice::due()->count(),
            'monthly_revenue' => RecurringInvoice::active()
                ->where('frequency', 'monthly')
                ->sum('total_incl_vat'),
        ];

        $partners = Partner::customers()->orderBy('name')->get(['id', 'name']);

        return view('recurring-invoices.index', compact('recurringInvoices', 'stats', 'partners'));
    }

    /**
     * Show the form for creating a new recurring invoice.
     */
    public function create()
    {
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        return view('recurring-invoices.create', compact('partners', 'vatCodes', 'accounts'));
    }

    /**
     * Store a newly created recurring invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'name' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'in:weekly,monthly,quarterly,yearly'],
            'frequency_interval' => ['nullable', 'integer', 'min:1'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:28'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'max_invoices' => ['nullable', 'integer', 'min:1'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reference_prefix' => ['nullable', 'string', 'max:50'],
            'auto_send' => ['nullable', 'boolean'],
            'auto_send_peppol' => ['nullable', 'boolean'],
            'include_structured_communication' => ['nullable', 'boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.account_id' => ['nullable', 'uuid'],
        ]);

        $recurringInvoice = DB::transaction(function () use ($validated, $request) {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);

            $recurringInvoice = RecurringInvoice::create([
                'partner_id' => $validated['partner_id'],
                'name' => $validated['name'],
                'frequency' => $validated['frequency'],
                'frequency_interval' => $validated['frequency_interval'] ?? 1,
                'day_of_month' => $validated['day_of_month'],
                'day_of_week' => $validated['day_of_week'],
                'start_date' => $startDate,
                'end_date' => $validated['end_date'],
                'next_invoice_date' => $startDate,
                'max_invoices' => $validated['max_invoices'],
                'payment_terms_days' => $validated['payment_terms_days'] ?? 30,
                'notes' => $validated['notes'],
                'reference_prefix' => $validated['reference_prefix'],
                'status' => 'active',
                'auto_send' => $validated['auto_send'] ?? false,
                'auto_send_peppol' => $validated['auto_send_peppol'] ?? false,
                'include_structured_communication' => $validated['include_structured_communication'] ?? true,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                $recurringInvoice->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'account_id' => $lineData['account_id'] ?? null,
                ]);
            }

            $recurringInvoice->calculateTotals();
            $recurringInvoice->save();

            return $recurringInvoice;
        });

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Facture récurrente créée avec succès!');
    }

    /**
     * Display the specified recurring invoice.
     */
    public function show(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->load(['partner', 'lines', 'creator', 'generatedInvoices.invoice']);

        return view('recurring-invoices.show', compact('recurringInvoice'));
    }

    /**
     * Show the form for editing.
     */
    public function edit(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status === 'completed') {
            return redirect()->route('recurring-invoices.show', $recurringInvoice)
                ->with('error', 'Cette facture récurrente est terminée et ne peut plus être modifiée.');
        }

        $recurringInvoice->load('lines');
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        return view('recurring-invoices.edit', compact('recurringInvoice', 'partners', 'vatCodes', 'accounts'));
    }

    /**
     * Update the specified recurring invoice.
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status === 'completed') {
            return back()->with('error', 'Cette facture récurrente est terminée.');
        }

        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'name' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'in:weekly,monthly,quarterly,yearly'],
            'frequency_interval' => ['nullable', 'integer', 'min:1'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:28'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'end_date' => ['nullable', 'date'],
            'max_invoices' => ['nullable', 'integer', 'min:1'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'reference_prefix' => ['nullable', 'string', 'max:50'],
            'auto_send' => ['nullable', 'boolean'],
            'auto_send_peppol' => ['nullable', 'boolean'],
            'include_structured_communication' => ['nullable', 'boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($recurringInvoice, $validated) {
            $recurringInvoice->update([
                'partner_id' => $validated['partner_id'],
                'name' => $validated['name'],
                'frequency' => $validated['frequency'],
                'frequency_interval' => $validated['frequency_interval'] ?? 1,
                'day_of_month' => $validated['day_of_month'],
                'day_of_week' => $validated['day_of_week'],
                'end_date' => $validated['end_date'],
                'max_invoices' => $validated['max_invoices'],
                'payment_terms_days' => $validated['payment_terms_days'] ?? 30,
                'notes' => $validated['notes'],
                'reference_prefix' => $validated['reference_prefix'],
                'auto_send' => $validated['auto_send'] ?? false,
                'auto_send_peppol' => $validated['auto_send_peppol'] ?? false,
                'include_structured_communication' => $validated['include_structured_communication'] ?? true,
            ]);

            $recurringInvoice->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                $recurringInvoice->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            $recurringInvoice->calculateTotals();
            $recurringInvoice->save();
        });

        return redirect()->route('recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Facture récurrente mise à jour!');
    }

    /**
     * Pause the recurring invoice.
     */
    public function pause(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'active') {
            return back()->with('error', 'Seules les factures actives peuvent être mises en pause.');
        }

        $recurringInvoice->update(['status' => 'paused']);

        return back()->with('success', 'Facture récurrente mise en pause.');
    }

    /**
     * Resume the recurring invoice.
     */
    public function resume(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'paused') {
            return back()->with('error', 'Seules les factures en pause peuvent être réactivées.');
        }

        // Recalculate next invoice date if it's in the past
        $nextDate = $recurringInvoice->next_invoice_date;
        while ($nextDate && $nextDate->isPast()) {
            $recurringInvoice->next_invoice_date = $nextDate;
            $nextDate = $recurringInvoice->calculateNextDate();
        }

        $recurringInvoice->update([
            'status' => 'active',
            'next_invoice_date' => $nextDate ?? $recurringInvoice->next_invoice_date,
        ]);

        return back()->with('success', 'Facture récurrente réactivée.');
    }

    /**
     * Cancel the recurring invoice.
     */
    public function cancel(RecurringInvoice $recurringInvoice)
    {
        if (in_array($recurringInvoice->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cette facture récurrente ne peut pas être annulée.');
        }

        $recurringInvoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Facture récurrente annulée.');
    }

    /**
     * Manually generate an invoice.
     */
    public function generate(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'active') {
            return back()->with('error', 'Seules les factures actives peuvent générer des factures.');
        }

        try {
            $invoice = $recurringInvoice->generateInvoice();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture générée avec succès!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified recurring invoice.
     */
    public function destroy(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->invoices_generated > 0) {
            return back()->with('error', 'Cette facture récurrente a déjà généré des factures et ne peut pas être supprimée.');
        }

        $recurringInvoice->delete();

        return redirect()->route('recurring-invoices.index')
            ->with('success', 'Facture récurrente supprimée.');
    }
}
