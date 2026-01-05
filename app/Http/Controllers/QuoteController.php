<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Quote;
use App\Models\Partner;
use App\Models\VatCode;
use App\Models\ChartOfAccount;
use App\Services\InvoiceTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    /**
     * Display a listing of quotes.
     */
    public function index(Request $request)
    {
        $query = Quote::with(['partner', 'creator'])
            ->latest('quote_date');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('partner')) {
            $query->where('partner_id', $request->partner);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('partner', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('quote_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('quote_date', '<=', $request->date_to);
        }

        $quotes = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Quote::count(),
            'draft' => Quote::where('status', 'draft')->count(),
            'sent' => Quote::where('status', 'sent')->count(),
            'accepted' => Quote::where('status', 'accepted')->count(),
            'converted' => Quote::where('status', 'converted')->count(),
            'total_amount' => Quote::whereIn('status', ['sent', 'accepted'])->sum('total_incl_vat'),
        ];

        $partners = Partner::customers()->orderBy('name')->get(['id', 'name']);

        return view('quotes.index', compact('quotes', 'stats', 'partners'));
    }

    /**
     * Show the form for creating a new quote.
     */
    public function create()
    {
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        $nextNumber = Quote::generateNextNumber(session('current_tenant_id'));

        return view('quotes.create', compact(
            'partners',
            'vatCodes',
            'accounts',
            'nextNumber'
        ));
    }

    /**
     * Store a newly created quote.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.account_id' => ['nullable', 'uuid'],
        ]);

        $quote = DB::transaction(function () use ($validated, $request) {
            $quote = Quote::create([
                'partner_id' => $validated['partner_id'],
                'status' => 'draft',
                'quote_number' => Quote::generateNextNumber(session('current_tenant_id')),
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                $quote->lines()->create([
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

            $quote->calculateTotals();
            $quote->save();

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Devis créé avec succès!');
    }

    /**
     * Display the specified quote.
     */
    public function show(Quote $quote)
    {
        $quote->load(['partner', 'lines', 'creator', 'convertedInvoice']);

        return view('quotes.show', compact('quote'));
    }

    /**
     * Show the form for editing the specified quote.
     */
    public function edit(Quote $quote)
    {
        if (!$quote->isEditable()) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $quote->load('lines');
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();

        return view('quotes.edit', compact('quote', 'partners', 'vatCodes', 'accounts'));
    }

    /**
     * Update the specified quote.
     */
    public function update(Request $request, Quote $quote)
    {
        if (!$quote->isEditable()) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($quote, $validated) {
            $quote->update([
                'partner_id' => $validated['partner_id'],
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'discount_percent' => $validated['discount_percent'] ?? 0,
            ]);

            // Delete existing lines and recreate
            $quote->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                $quote->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            $quote->calculateTotals();
            $quote->save();
        });

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Devis mis à jour avec succès!');
    }

    /**
     * Mark quote as sent.
     */
    public function send(Quote $quote)
    {
        if ($quote->status !== 'draft') {
            return back()->with('error', 'Seuls les devis en brouillon peuvent être envoyés.');
        }

        $quote->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Devis marqué comme envoyé!');
    }

    /**
     * Mark quote as accepted.
     */
    public function accept(Quote $quote)
    {
        if (!in_array($quote->status, ['sent', 'draft'])) {
            return back()->with('error', 'Ce devis ne peut pas être accepté.');
        }

        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return back()->with('success', 'Devis accepté!');
    }

    /**
     * Mark quote as rejected.
     */
    public function reject(Quote $quote)
    {
        if (!in_array($quote->status, ['sent', 'draft', 'accepted'])) {
            return back()->with('error', 'Ce devis ne peut pas être refusé.');
        }

        $quote->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Devis refusé.');
    }

    /**
     * Convert quote to invoice.
     */
    public function convert(Quote $quote)
    {
        if (!$quote->canConvert()) {
            return back()->with('error', 'Ce devis ne peut pas être converti en facture.');
        }

        try {
            $invoice = $quote->convertToInvoice();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Devis converti en facture avec succès!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la conversion: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a quote.
     */
    public function duplicate(Quote $quote)
    {
        $newQuote = DB::transaction(function () use ($quote) {
            $newQuote = $quote->replicate([
                'quote_number',
                'status',
                'converted_invoice_id',
                'converted_at',
                'sent_at',
                'accepted_at',
                'rejected_at',
            ]);

            $newQuote->quote_number = Quote::generateNextNumber(session('current_tenant_id'));
            $newQuote->status = 'draft';
            $newQuote->quote_date = now();
            $newQuote->valid_until = now()->addDays(30);
            $newQuote->created_by = auth()->id();
            $newQuote->save();

            foreach ($quote->lines as $line) {
                $newLine = $line->replicate();
                $newLine->quote_id = $newQuote->id;
                $newLine->save();
            }

            return $newQuote;
        });

        return redirect()->route('quotes.edit', $newQuote)
            ->with('success', 'Devis dupliqué avec succès!');
    }

    /**
     * Download PDF.
     */
    public function downloadPdf(Quote $quote)
    {
        $quote->load(['partner', 'lines', 'company']);

        // Get company template colors
        $company = Company::current();
        $template = InvoiceTemplateService::getCompanyTemplate($company);
        $templateColors = $template['colors'];

        $pdf = Pdf::loadView('quotes.pdf', compact('quote', 'templateColors'));

        return $pdf->download("Devis_{$quote->quote_number}.pdf");
    }

    /**
     * Remove the specified quote.
     */
    public function destroy(Quote $quote)
    {
        if (!$quote->isEditable()) {
            return back()->with('error', 'Ce devis ne peut pas être supprimé.');
        }

        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Devis supprimé avec succès!');
    }
}
