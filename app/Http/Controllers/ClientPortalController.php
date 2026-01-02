<?php

namespace App\Http\Controllers;

use App\Models\ClientAccess;
use App\Models\ClientDocument;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    /**
     * Show client portal dashboard.
     */
    public function dashboard(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        // Get key metrics
        $stats = [
            'total_invoices' => Invoice::where('company_id', $company->id)
                ->where('type', 'out')
                ->count(),
            'unpaid_invoices' => Invoice::where('company_id', $company->id)
                ->where('type', 'out')
                ->where('status', 'sent')
                ->count(),
            'unpaid_amount' => Invoice::where('company_id', $company->id)
                ->where('type', 'out')
                ->where('status', 'sent')
                ->sum('total_incl_vat'),
            'current_month_revenue' => Invoice::where('company_id', $company->id)
                ->where('type', 'out')
                ->whereYear('invoice_date', now()->year)
                ->whereMonth('invoice_date', now()->month)
                ->sum('total_excl_vat'),
        ];

        // Recent invoices
        $recentInvoices = Invoice::where('company_id', $company->id)
            ->where('type', 'out')
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::where('company_id', $company->id)
            ->with('invoice')
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        // Recent documents
        $recentDocuments = ClientDocument::where('company_id', $company->id)
            ->with('uploadedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('client-portal.dashboard', compact(
            'company',
            'access',
            'stats',
            'recentInvoices',
            'recentPayments',
            'recentDocuments'
        ));
    }

    /**
     * List invoices.
     */
    public function invoices(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('view_invoices')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les factures');
        }

        $query = Invoice::where('company_id', $company->id)
            ->where('type', 'out');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')
            ->paginate(20);

        return view('client-portal.invoices.index', compact('company', 'access', 'invoices'));
    }

    /**
     * Show invoice details.
     */
    public function showInvoice(Request $request, Company $company, Invoice $invoice)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('view_invoices')) {
            abort(403);
        }

        if ($invoice->company_id !== $company->id) {
            abort(404);
        }

        $invoice->load(['partner', 'lines.product', 'payments']);

        // Get comments
        $comments = $invoice->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client-portal.invoices.show', compact('company', 'access', 'invoice', 'comments'));
    }

    /**
     * Download invoice PDF.
     */
    public function downloadInvoice(Request $request, Company $company, Invoice $invoice)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('download_invoices')) {
            abort(403);
        }

        if ($invoice->company_id !== $company->id) {
            abort(404);
        }

        // Generate PDF (assuming InvoiceController has this method)
        $pdf = app(\App\Http\Controllers\InvoiceController::class)->generatePDF($invoice);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $invoice->invoice_number . '.pdf');
    }

    /**
     * List documents.
     */
    public function documents(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        $query = ClientDocument::where('company_id', $company->id);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }

        $documents = $query->with('uploadedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client-portal.documents.index', compact('company', 'access', 'documents'));
    }

    /**
     * Show document upload form.
     */
    public function createDocument(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('upload_documents')) {
            abort(403, 'Vous n\'avez pas la permission d\'uploader des documents');
        }

        return view('client-portal.documents.create', compact('company', 'access'));
    }

    /**
     * Store uploaded document.
     */
    public function storeDocument(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('upload_documents')) {
            abort(403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'type' => 'required|in:' . implode(',', array_keys(ClientDocument::TYPES)),
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_date' => 'nullable|date',
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $storagePath = 'client-documents/' . $company->id . '/' . $filename;

        // Store file
        Storage::disk('private')->put($storagePath, file_get_contents($file));

        // Create document record
        $document = ClientDocument::create([
            'company_id' => $company->id,
            'uploaded_by' => auth()->id(),
            'type' => $validated['type'],
            'category' => $validated['category'] ?? null,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $storagePath,
            'description' => $validated['description'] ?? null,
            'document_date' => $validated['document_date'] ?? now(),
        ]);

        return redirect()
            ->route('client-portal.documents.index', $company)
            ->with('success', 'Document uploadé avec succès!');
    }

    /**
     * Download document.
     */
    public function downloadDocument(Request $request, Company $company, ClientDocument $document)
    {
        $access = $this->getClientAccess($request, $company);

        if ($document->company_id !== $company->id) {
            abort(404);
        }

        if (!Storage::disk('private')->exists($document->storage_path)) {
            abort(404, 'Fichier introuvable');
        }

        return Storage::disk('private')->download(
            $document->storage_path,
            $document->original_filename
        );
    }

    /**
     * Store comment.
     */
    public function storeComment(Request $request, Company $company)
    {
        $access = $this->getClientAccess($request, $company);

        if (!$access->hasPermission('comment')) {
            abort(403, 'Vous n\'avez pas la permission de commenter');
        }

        $validated = $request->validate([
            'commentable_type' => 'required|in:App\Models\Invoice,App\Models\ClientDocument',
            'commentable_id' => 'required|uuid',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|uuid|exists:comments,id',
        ]);

        // Extract mentions from content
        $mentions = Comment::extractMentions($validated['content']);

        $comment = Comment::create([
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'user_id' => auth()->id(),
            'company_id' => $company->id,
            'content' => $validated['content'],
            'mentions' => $mentions,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return back()->with('success', 'Commentaire ajouté!');
    }

    /**
     * Get client access or throw exception.
     */
    protected function getClientAccess(Request $request, Company $company): ?ClientAccess
    {
        $clientAccess = $request->get('client_access');

        // Store company in session for convenience
        session(['client_portal_company_id' => $company->id]);

        return $clientAccess;
    }
}
