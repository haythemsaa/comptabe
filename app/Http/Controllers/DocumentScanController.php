<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentScanController extends Controller
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Afficher la page de scan de documents
     */
    public function index()
    {
        $company = Company::current();

        return view('documents.scan', compact('company'));
    }

    /**
     * Scanner un document uploadé
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:10240', // 10MB max
            ],
        ]);

        try {
            $file = $request->file('document');

            // Extraire les données avec OCR
            $data = $this->ocrService->extractInvoiceData($file);

            // Valider l'extraction
            $errors = $this->ocrService->validateExtraction($data);

            return response()->json([
                'success' => true,
                'data' => $data,
                'validation_errors' => $errors,
                'message' => count($errors) > 0
                    ? 'Document scanné avec quelques avertissements'
                    : 'Document scanné avec succès',
            ]);

        } catch (\Exception $e) {
            \Log::error('Document scan error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan du document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Créer une facture d'achat à partir des données scannées
     */
    public function createInvoice(Request $request)
    {
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
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        try {
            $company = Company::current();

            DB::beginTransaction();

            // Créer ou récupérer le fournisseur
            $supplier = $this->getOrCreateSupplier($company, $validated);

            // Créer la facture d'achat
            $invoice = new Invoice();
            $invoice->company_id = $company->id;
            $invoice->partner_id = $supplier->id;
            $invoice->type = 'in'; // Facture d'achat
            $invoice->invoice_number = $validated['invoice_number'] ?? 'SCAN-' . date('Ymd-His');
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'] ?? date('Y-m-d', strtotime($validated['invoice_date'] . ' +30 days'));
            $invoice->currency = 'EUR';
            $invoice->status = 'received'; // Statut "reçue" pour factures scannées
            $invoice->peppol_status = 'manual'; // Créée manuellement via scan

            // Montants
            $invoice->subtotal = $validated['subtotal'];
            $invoice->tax_amount = $validated['vat_amount'];
            $invoice->total_amount = $validated['total_amount'];
            $invoice->amount_due = $validated['total_amount'];

            // Métadonnées
            $invoice->notes = 'Facture créée automatiquement par scan OCR';
            if (!empty($validated['description'])) {
                $invoice->notes .= "\n\nDescription: " . $validated['description'];
            }

            $invoice->save();

            // Ajouter une ligne de facture simple
            $invoice->items()->create([
                'description' => $validated['description'] ?? 'Prestation/Achat (détails dans le document scanné)',
                'quantity' => 1,
                'unit_price' => $validated['subtotal'],
                'tax_rate' => $validated['vat_rate'],
                'tax_amount' => $validated['vat_amount'],
                'total' => $validated['total_amount'],
            ]);

            // Attacher le document scanné si présent
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $path = $file->store('invoices/scanned', 'public');

                $invoice->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'type' => 'scanned_invoice',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès',
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'url' => route('purchases.show', $invoice->id),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Invoice creation from scan error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la facture: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupérer ou créer un fournisseur
     */
    protected function getOrCreateSupplier(Company $company, array $data): Partner
    {
        $supplierName = $data['supplier_name'];
        $supplierVat = $data['supplier_vat'] ?? null;

        // Chercher d'abord par numéro de TVA si fourni
        if ($supplierVat) {
            $supplier = Partner::where('company_id', $company->id)
                ->where('type', 'supplier')
                ->where('vat_number', $supplierVat)
                ->first();

            if ($supplier) {
                return $supplier;
            }
        }

        // Sinon, chercher par nom (similarité)
        $supplier = Partner::where('company_id', $company->id)
            ->where('type', 'supplier')
            ->where('name', 'LIKE', '%' . $supplierName . '%')
            ->first();

        if ($supplier) {
            // Mettre à jour le numéro de TVA si fourni et manquant
            if ($supplierVat && empty($supplier->vat_number)) {
                $supplier->vat_number = $supplierVat;
                $supplier->save();
            }
            return $supplier;
        }

        // Créer un nouveau fournisseur
        $supplier = new Partner();
        $supplier->company_id = $company->id;
        $supplier->type = 'supplier';
        $supplier->name = $supplierName;
        $supplier->vat_number = $supplierVat;
        $supplier->country = 'BE'; // Par défaut Belgique
        $supplier->currency = 'EUR';
        $supplier->payment_terms = 30; // 30 jours par défaut
        $supplier->notes = 'Créé automatiquement par scan OCR';
        $supplier->save();

        return $supplier;
    }
}
