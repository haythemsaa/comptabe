<?php

namespace App\Http\Controllers;

use App\Models\EmailInvoice;
use App\Models\Company;
use App\Services\EmailInvoiceProcessor;
use Illuminate\Http\Request;

class EmailInvoiceController extends Controller
{
    protected EmailInvoiceProcessor $processor;

    public function __construct(EmailInvoiceProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Afficher la liste des emails de factures
     */
    public function index(Request $request)
    {
        $company = Company::current();

        $query = EmailInvoice::where('company_id', $company->id)
            ->with(['invoice', 'processedBy'])
            ->orderBy('email_date', 'desc');

        // Filtres
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $emailInvoices = $query->paginate(20);

        // Statistiques
        $stats = [
            'pending' => EmailInvoice::where('company_id', $company->id)->pending()->count(),
            'processed' => EmailInvoice::where('company_id', $company->id)->processed()->count(),
            'failed' => EmailInvoice::where('company_id', $company->id)->failed()->count(),
            'rejected' => EmailInvoice::where('company_id', $company->id)->rejected()->count(),
        ];

        return view('email-invoices.index', compact('emailInvoices', 'stats', 'company'));
    }

    /**
     * Afficher les détails d'un email
     */
    public function show(EmailInvoice $emailInvoice)
    {
        $this->authorize('view', $emailInvoice);

        $emailInvoice->load(['invoice', 'processedBy', 'company']);

        return view('email-invoices.show', compact('emailInvoice'));
    }

    /**
     * Traiter un email manuellement
     */
    public function process(EmailInvoice $emailInvoice, Request $request)
    {
        $this->authorize('update', $emailInvoice);

        try {
            $autoCreate = $request->boolean('auto_create', false);

            $result = $this->processor->processEmailInvoice($emailInvoice, $autoCreate);

            if ($result['success']) {
                $message = $result['auto_created'] ?? false
                    ? 'Facture créée automatiquement avec succès'
                    : 'Email traité. Données extraites et prêtes pour validation.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'email_invoice' => $emailInvoice->fresh(),
                    'redirect' => $result['auto_created'] ?? false
                        ? route('purchases.show', $result['invoice']->id)
                        : null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erreur lors du traitement',
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Manual email processing failed', [
                'email_invoice_id' => $emailInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rejeter un email
     */
    public function reject(EmailInvoice $emailInvoice, Request $request)
    {
        $this->authorize('update', $emailInvoice);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $emailInvoice->markAsRejected($validated['reason']);

            return response()->json([
                'success' => true,
                'message' => 'Email rejeté avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer une facture manuellement depuis les données extraites
     */
    public function createInvoice(EmailInvoice $emailInvoice, Request $request)
    {
        $this->authorize('update', $emailInvoice);

        $validated = $request->validate([
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'supplier_vat' => ['nullable', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            // Créer un fichier temporaire depuis la première pièce jointe
            $attachment = $emailInvoice->getFirstAttachment();
            $tempPath = storage_path('app/' . $attachment['path']);

            $file = new \Illuminate\Http\UploadedFile(
                $tempPath,
                $attachment['filename'],
                $attachment['mime_type'],
                null,
                true
            );

            // Créer la facture
            $invoice = $this->processor->createInvoiceFromExtractedData(
                $emailInvoice,
                $validated,
                $file
            );

            $emailInvoice->markAsProcessed($invoice, 'Facture créée manuellement');

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès',
                'invoice' => [
                    'id' => $invoice->id,
                    'url' => route('purchases.show', $invoice->id),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Manual invoice creation from email failed', [
                'email_invoice_id' => $emailInvoice->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer un email
     */
    public function destroy(EmailInvoice $emailInvoice)
    {
        $this->authorize('delete', $emailInvoice);

        try {
            $emailInvoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Email supprimé avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }
}
