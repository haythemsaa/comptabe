<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use App\Models\Product;
use App\Models\VatCode;
use App\Models\ChartOfAccount;
use App\Services\Peppol\PeppolService;
use App\Services\EpcQrCodeService;
use App\Services\InvoiceTemplateService;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of sales invoices.
     */
    public function index(Request $request)
    {
        // PERFORMANCE: Eager loading all relations to avoid N+1 queries
        $query = Invoice::sales()
            ->with(['partner', 'creator', 'lines'])
            ->latest('invoice_date');

        // Filters
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
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
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('partner', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->paginate(20)->withQueryString();

        // Stats - optimized single query instead of 6 separate queries
        $statsQuery = Invoice::sales()
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN status = "draft" THEN 1 END) as draft,
                COUNT(CASE WHEN status = "sent" THEN 1 END) as sent,
                COUNT(CASE WHEN due_date < CURDATE() AND amount_due > 0 AND status NOT IN ("draft", "cancelled", "paid") THEN 1 END) as overdue,
                COALESCE(SUM(CASE WHEN status NOT IN ("draft", "cancelled") THEN total_incl_vat ELSE 0 END), 0) as total_amount,
                COALESCE(SUM(CASE WHEN amount_due > 0 AND status NOT IN ("draft", "cancelled", "paid") THEN amount_due ELSE 0 END), 0) as total_due
            ')
            ->first();

        $stats = [
            'total' => $statsQuery->total ?? 0,
            'draft' => $statsQuery->draft ?? 0,
            'sent' => $statsQuery->sent ?? 0,
            'overdue' => $statsQuery->overdue ?? 0,
            'total_amount' => $statsQuery->total_amount ?? 0,
            'total_due' => $statsQuery->total_due ?? 0,
        ];

        $partners = Partner::customers()->orderBy('name')->get(['id', 'name']);

        return view('invoices.index', compact('invoices', 'stats', 'partners'));
    }

    /**
     * Display a listing of purchase invoices.
     */
    public function purchases(Request $request)
    {
        $query = Invoice::purchases()
            ->with(['partner'])
            ->latest('invoice_date');

        // Same filtering logic as sales
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('peppol') && $request->peppol === 'pending') {
            $query->where('peppol_status', 'received')->where('is_booked', false);
        }

        $invoices = $query->paginate(20)->withQueryString();

        // Stats - optimized single query
        $statsQuery = Invoice::purchases()
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN is_booked = 0 THEN 1 END) as pending,
                COALESCE(SUM(CASE WHEN amount_due > 0 AND status NOT IN ("draft", "cancelled", "paid") THEN amount_due ELSE 0 END), 0) as total_due
            ')
            ->first();

        $stats = [
            'total' => $statsQuery->total ?? 0,
            'pending' => $statsQuery->pending ?? 0,
            'total_due' => $statsQuery->total_due ?? 0,
        ];

        return view('invoices.purchases', compact('invoices', 'stats'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();
        $products = Product::active()->ordered()->get();

        $nextNumber = Invoice::generateNextNumber(session('current_tenant_id'), 'out');
        $structuredCommunication = Invoice::generateStructuredCommunication();

        return view('invoices.create', compact(
            'partners',
            'vatCodes',
            'accounts',
            'products',
            'nextNumber',
            'structuredCommunication'
        ));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3', 'in:' . implode(',', array_keys(config('currencies.available')))],
            'structured_communication' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.account_id' => ['nullable', 'uuid'],
        ]);

        $invoice = DB::transaction(function () use ($validated, $request) {
            $invoice = Invoice::create([
                'partner_id' => $validated['partner_id'],
                'type' => 'out',
                'document_type' => 'invoice',
                'status' => 'draft',
                'invoice_number' => Invoice::generateNextNumber(session('current_tenant_id'), 'out'),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'reference' => $validated['reference'],
                'currency' => $validated['currency'],
                'exchange_rate' => 1, // TODO: Fetch real exchange rate if currency != company currency
                'structured_communication' => $validated['structured_communication'] ?? Invoice::generateStructuredCommunication(),
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                $invoice->lines()->create([
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

            $invoice->calculateTotals();
            $invoice->save();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Facture créée avec succès!');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['partner', 'lines', 'creator', 'peppolTransmissions']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $invoice->load('lines');
        $partners = Partner::customers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('revenue')->orderBy('account_number')->get();
        $products = Product::active()->ordered()->get();

        return view('invoices.edit', compact('invoice', 'partners', 'vatCodes', 'accounts', 'products'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cette facture ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $invoice->update([
                'partner_id' => $validated['partner_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
            ]);

            // Delete existing lines and recreate
            $invoice->lines()->delete();

            foreach ($validated['lines'] as $index => $lineData) {
                $invoice->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                ]);
            }

            $invoice->calculateTotals();
            $invoice->save();
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Facture mise à jour avec succès!');
    }

    /**
     * Validate the invoice (mark as validated/ready to send).
     */
    public function validateInvoice(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Seules les factures en brouillon peuvent être validées.');
        }

        $invoice->update(['status' => 'validated']);

        return back()->with('success', 'Facture validée avec succès!');
    }

    /**
     * Send invoice via Peppol.
     */
    public function sendPeppol(Invoice $invoice, PeppolService $peppolService)
    {
        if (!$invoice->canSendViaPeppol()) {
            return back()->with('error', 'Cette facture ne peut pas être envoyée via Peppol.');
        }

        try {
            // sendInvoice() returns PeppolTransmission object or throws exception
            $transmission = $peppolService->sendInvoice($invoice);

            // If we reach here, sending was successful (otherwise exception thrown)
            $invoice->update([
                'status' => 'sent',
                'peppol_status' => 'sent',
                'peppol_sent_at' => now(),
                'peppol_transmission_id' => $transmission->id,
            ]);

            // Check if test mode
            $isTestMode = $transmission->status === 'sent' &&
                          str_contains($transmission->response_payload ?? '', 'test_mode');

            $message = $isTestMode
                ? 'Facture envoyée via Peppol en MODE TEST (simulation) avec succès!'
                : 'Facture envoyée via Peppol avec succès!';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi via Peppol: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF.
     */
    public function downloadPdf(Invoice $invoice, EpcQrCodeService $qrCodeService)
    {
        $invoice->load(['partner', 'lines', 'company']);

        $company = $invoice->company;

        // Générer le QR code EPC SEPA si IBAN disponible
        $qrCode = null;
        if ($company->default_iban && $invoice->amount_due > 0) {
            try {
                $qrCode = $qrCodeService->generateForInvoice($invoice);
            } catch (\Exception $e) {
                // Si erreur, continuer sans QR code
                \Log::warning('Erreur génération QR code EPC', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Get company's template configuration
        $template = InvoiceTemplateService::getCompanyTemplate($company);
        $templateColors = $template['colors'];

        $pdf = Pdf::loadView($template['view'], compact('invoice', 'company', 'qrCode', 'templateColors'));

        return $pdf->download("Facture_{$invoice->invoice_number}.pdf");
    }

    /**
     * Download UBL XML.
     */
    public function downloadUbl(Invoice $invoice, PeppolService $peppolService)
    {
        $ubl = $peppolService->generateUBL($invoice);

        return response($ubl, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"{$invoice->invoice_number}.xml\"",
        ]);
    }

    /**
     * Redirect to create credit note from invoice.
     */
    public function creditNote(Invoice $invoice)
    {
        return redirect()->route('credit-notes.create', ['invoice_id' => $invoice->id]);
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        if (!$invoice->isEditable()) {
            return back()->with('error', 'Cette facture ne peut pas être supprimée.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès!');
    }

    /**
     * Show form for creating purchase invoice.
     */
    public function createPurchase()
    {
        $partners = Partner::suppliers()->orderBy('name')->get();
        $vatCodes = VatCode::active()->orderBy('rate', 'desc')->get();
        $accounts = ChartOfAccount::active()->postable()->ofType('expense')->orderBy('account_number')->get();

        return view('invoices.create-purchase', compact('partners', 'vatCodes', 'accounts'));
    }

    /**
     * Store a newly created purchase invoice.
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric'],
            'lines.*.account_id' => ['nullable', 'uuid'],
        ]);

        $invoice = DB::transaction(function () use ($validated, $request) {
            $invoice = Invoice::create([
                'partner_id' => $validated['partner_id'],
                'type' => 'in',
                'document_type' => 'invoice',
                'status' => $request->input('action') === 'draft' ? 'draft' : 'received',
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['lines'] as $index => $lineData) {
                $invoice->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'vat_category' => $lineData['vat_rate'] > 0 ? 'S' : 'Z',
                    'discount_percent' => 0,
                    'account_id' => $lineData['account_id'] ?? null,
                ]);
            }

            $invoice->calculateTotals();
            $invoice->save();

            return $invoice;
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Facture d\'achat enregistrée avec succès!');
    }

    /**
     * Show form for importing UBL file.
     */
    public function showImportUbl()
    {
        return view('invoices.import-ubl');
    }

    /**
     * Import UBL file as purchase invoice.
     */
    public function importUbl(Request $request, PeppolService $peppolService)
    {
        $request->validate([
            'ubl_file' => ['required', 'file', 'mimes:xml', 'max:5120'],
        ]);

        try {
            $ublContent = file_get_contents($request->file('ubl_file')->path());
            $company = Company::current();

            $invoice = $peppolService->importUblFile($company, $ublContent);

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Facture importée avec succès depuis le fichier UBL!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de l\'import: ' . $e->getMessage())
                ->withInput();
        }
    }
}
