<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\VatCode;
use App\Models\ChartOfAccount;
use App\Services\InvoiceTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of credit notes.
     */
    public function index(Request $request)
    {
        $query = CreditNote::with(['partner', 'invoice', 'creator'])
            ->latest('credit_note_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('partner')) {
            $query->where('partner_id', $request->partner);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_number', 'like', "%{$search}%")
                    ->orWhereHas('partner', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $creditNotes = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => CreditNote::count(),
            'draft' => CreditNote::where('status', 'draft')->count(),
            'validated' => CreditNote::where('status', 'validated')->count(),
            'total_amount' => CreditNote::whereIn('status', ['validated', 'sent', 'applied'])->sum('total_incl_vat'),
        ];

        $partners = Partner::customers()->orderBy('name')->get(['id', 'name']);

        return view('credit-notes.index', compact('creditNotes', 'stats', 'partners'));
    }

    /**
     * Show the form for creating a new credit note.
     */
    public function create(Request $request)
    {
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        $nextNumber = CreditNote::generateNextNumber(session('current_tenant_id'));

        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with('lines')->find($request->invoice_id);
        }

        return view('credit-notes.create', compact('partners', 'vatCodes', 'accounts', 'nextNumber', 'invoice'));
    }

    /**
     * Store a newly created credit note.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'invoice_id' => ['nullable', 'uuid', 'exists:invoices,id'],
            'credit_note_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.account_id' => ['nullable', 'uuid'],
        ]);

        $creditNote = DB::transaction(function () use ($validated) {
            $creditNote = CreditNote::create([
                'partner_id' => $validated['partner_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'credit_note_number' => CreditNote::generateNextNumber(session('current_tenant_id')),
                'status' => 'draft',
                'credit_note_date' => $validated['credit_note_date'],
                'reference' => $validated['reference'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'structured_communication' => CreditNote::generateStructuredCommunication(),
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                $creditNote->lines()->create([
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

            $creditNote->calculateTotals();
            $creditNote->save();

            return $creditNote;
        });

        return redirect()->route('credit-notes.show', $creditNote)
            ->with('success', 'Note de credit creee avec succes!');
    }

    /**
     * Display the specified credit note.
     */
    public function show(CreditNote $creditNote)
    {
        $creditNote->load(['partner', 'invoice', 'lines', 'creator']);

        return view('credit-notes.show', compact('creditNote'));
    }

    /**
     * Show the form for editing.
     */
    public function edit(CreditNote $creditNote)
    {
        if (!$creditNote->isEditable()) {
            return redirect()->route('credit-notes.show', $creditNote)
                ->with('error', 'Cette note de credit ne peut plus etre modifiee.');
        }

        $creditNote->load('lines');
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        return view('credit-notes.edit', compact('creditNote', 'partners', 'vatCodes', 'accounts'));
    }

    /**
     * Update the specified credit note.
     */
    public function update(Request $request, CreditNote $creditNote)
    {
        if (!$creditNote->isEditable()) {
            return back()->with('error', 'Cette note de credit ne peut plus etre modifiee.');
        }

        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'credit_note_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($creditNote, $validated) {
            $creditNote->update([
                'partner_id' => $validated['partner_id'],
                'credit_note_date' => $validated['credit_note_date'],
                'reference' => $validated['reference'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
            ]);

            $creditNote->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                $creditNote->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            $creditNote->calculateTotals();
            $creditNote->save();
        });

        return redirect()->route('credit-notes.show', $creditNote)
            ->with('success', 'Note de credit mise a jour!');
    }

    /**
     * Validate the credit note.
     */
    public function markAsValidated(CreditNote $creditNote)
    {
        try {
            $creditNote->validate();
            return back()->with('success', 'Note de credit validee!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create credit note from invoice.
     */
    public function createFromInvoice(Invoice $invoice)
    {
        try {
            $creditNote = CreditNote::createFromInvoice($invoice);
            return redirect()->route('credit-notes.edit', $creditNote)
                ->with('success', 'Note de credit creee a partir de la facture.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF.
     */
    public function downloadPdf(CreditNote $creditNote)
    {
        $creditNote->load(['partner', 'lines', 'company', 'invoice']);

        // Get company template colors
        $company = Company::current();
        $template = InvoiceTemplateService::getCompanyTemplate($company);
        $templateColors = $template['colors'];

        $pdf = Pdf::loadView('credit-notes.pdf', compact('creditNote', 'templateColors'));

        return $pdf->download("NC_{$creditNote->credit_note_number}.pdf");
    }

    /**
     * Remove the specified credit note.
     */
    public function destroy(CreditNote $creditNote)
    {
        if (!$creditNote->isEditable()) {
            return back()->with('error', 'Cette note de credit ne peut pas etre supprimee.');
        }

        $creditNote->delete();

        return redirect()->route('credit-notes.index')
            ->with('success', 'Note de credit supprimee.');
    }
}
