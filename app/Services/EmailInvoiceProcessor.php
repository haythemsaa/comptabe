<?php

namespace App\Services;

use App\Models\EmailInvoice;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

/**
 * Service pour traiter les factures reçues par email
 */
class EmailInvoiceProcessor
{
    protected OcrService $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Traiter un email de facture
     */
    public function processEmailInvoice(EmailInvoice $emailInvoice, bool $autoCreate = true): array
    {
        try {
            $emailInvoice->markAsProcessing();

            // Vérifier qu'il y a des pièces jointes
            if (!$emailInvoice->hasAttachments()) {
                throw new \Exception('Aucune pièce jointe trouvée dans l\'email');
            }

            // Récupérer la première pièce jointe (facture)
            $attachment = $emailInvoice->getFirstAttachment();

            if (!$this->isValidInvoiceFile($attachment)) {
                throw new \Exception('Format de fichier non supporté: ' . ($attachment['mime_type'] ?? 'inconnu'));
            }

            // Créer un UploadedFile depuis la pièce jointe
            $file = $this->createUploadedFileFromAttachment($attachment);

            // Extraire les données avec OCR
            $extractedData = $this->ocrService->extractInvoiceData($file);

            // Enrichir avec les données de l'email
            $extractedData = $this->enrichDataFromEmail($extractedData, $emailInvoice);

            // Sauvegarder les données extraites
            $emailInvoice->update([
                'extracted_data' => $extractedData,
                'confidence_score' => $extractedData['ocr_confidence'] ?? 0,
            ]);

            // Créer automatiquement la facture si demandé et confiance suffisante
            if ($autoCreate && ($extractedData['ocr_confidence'] ?? 0) >= 0.6) {
                $invoice = $this->createInvoiceFromExtractedData($emailInvoice, $extractedData, $file);
                $emailInvoice->markAsProcessed($invoice, 'Facture créée automatiquement');

                return [
                    'success' => true,
                    'auto_created' => true,
                    'invoice' => $invoice,
                    'email_invoice' => $emailInvoice,
                ];
            }

            // Sinon, marquer comme en attente de validation manuelle
            $emailInvoice->update(['status' => 'pending']);

            return [
                'success' => true,
                'auto_created' => false,
                'requires_manual_validation' => true,
                'email_invoice' => $emailInvoice,
                'extracted_data' => $extractedData,
            ];

        } catch (\Exception $e) {
            $emailInvoice->markAsFailed($e->getMessage());

            \Log::error('Email invoice processing failed', [
                'email_invoice_id' => $emailInvoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'email_invoice' => $emailInvoice,
            ];
        }
    }

    /**
     * Créer une facture à partir des données extraites
     */
    public function createInvoiceFromExtractedData(EmailInvoice $emailInvoice, array $data, UploadedFile $file): Invoice
    {
        DB::beginTransaction();

        try {
            $company = $emailInvoice->company;

            // Créer ou récupérer le fournisseur
            $supplier = $this->getOrCreateSupplier($company, $data, $emailInvoice);

            // Créer la facture
            $invoice = new Invoice();
            $invoice->company_id = $company->id;
            $invoice->partner_id = $supplier->id;
            $invoice->type = 'in'; // Facture d'achat
            $invoice->invoice_number = $data['invoice_number'] ?? 'EMAIL-' . date('Ymd-His');
            $invoice->invoice_date = $data['invoice_date'] ?? now()->format('Y-m-d');
            $invoice->due_date = $data['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $invoice->currency = $data['currency'] ?? 'EUR';
            $invoice->status = 'received';
            $invoice->peppol_status = 'email_import';

            // Montants
            $invoice->subtotal = $data['subtotal'] ?? 0;
            $invoice->tax_amount = $data['vat_amount'] ?? 0;
            $invoice->total_amount = $data['total_amount'] ?? 0;
            $invoice->amount_due = $data['total_amount'] ?? 0;

            // Notes
            $invoice->notes = "Facture importée automatiquement par email\n";
            $invoice->notes .= "De: {$emailInvoice->from_email}\n";
            $invoice->notes .= "Sujet: {$emailInvoice->subject}\n";
            if (!empty($data['extracted_text'])) {
                $invoice->notes .= "\nTexte extrait:\n" . substr($data['extracted_text'], 0, 500);
            }

            $invoice->save();

            // Ajouter une ligne de facture
            $invoice->items()->create([
                'description' => $data['description'] ?? 'Prestation (voir document joint)',
                'quantity' => 1,
                'unit_price' => $data['subtotal'] ?? 0,
                'tax_rate' => $data['vat_rate'] ?? 21,
                'tax_amount' => $data['vat_amount'] ?? 0,
                'total' => $data['total_amount'] ?? 0,
            ]);

            // Attacher le document
            $path = $file->store('invoices/email-import', 'public');
            $invoice->attachments()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'type' => 'email_invoice',
            ]);

            DB::commit();

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Récupérer ou créer un fournisseur
     */
    protected function getOrCreateSupplier(Company $company, array $data, EmailInvoice $emailInvoice): Partner
    {
        $supplierName = $data['supplier_name'] ?? $emailInvoice->from_name ?? $emailInvoice->from_email;
        $supplierVat = $data['supplier_vat'] ?? null;
        $supplierEmail = $emailInvoice->from_email;

        // Chercher par numéro de TVA
        if ($supplierVat) {
            $supplier = Partner::where('company_id', $company->id)
                ->where('type', 'supplier')
                ->where('vat_number', $supplierVat)
                ->first();

            if ($supplier) {
                return $supplier;
            }
        }

        // Chercher par email
        $supplier = Partner::where('company_id', $company->id)
            ->where('type', 'supplier')
            ->where('email', $supplierEmail)
            ->first();

        if ($supplier) {
            // Mettre à jour le numéro de TVA si fourni
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
        $supplier->email = $supplierEmail;
        $supplier->vat_number = $supplierVat;
        $supplier->country = 'BE';
        $supplier->currency = 'EUR';
        $supplier->payment_terms = 30;
        $supplier->notes = "Créé automatiquement depuis email: {$emailInvoice->subject}";
        $supplier->save();

        return $supplier;
    }

    /**
     * Enrichir les données extraites avec les informations de l'email
     */
    protected function enrichDataFromEmail(array $data, EmailInvoice $emailInvoice): array
    {
        // Si le nom du fournisseur n'a pas été extrait, utiliser l'expéditeur
        if (empty($data['supplier_name'])) {
            $data['supplier_name'] = $emailInvoice->from_name ?? $emailInvoice->from_email;
        }

        // Utiliser la date de l'email si pas de date extraite
        if (empty($data['invoice_date'])) {
            $data['invoice_date'] = $emailInvoice->email_date->format('Y-m-d');
        }

        // Ajouter le sujet comme description si vide
        if (empty($data['description'])) {
            $data['description'] = $emailInvoice->subject;
        }

        return $data;
    }

    /**
     * Vérifier si le fichier est une facture valide
     */
    protected function isValidInvoiceFile(array $attachment): bool
    {
        $validMimes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png',
        ];

        return in_array($attachment['mime_type'] ?? '', $validMimes);
    }

    /**
     * Créer un UploadedFile depuis une pièce jointe
     */
    protected function createUploadedFileFromAttachment(array $attachment): UploadedFile
    {
        $path = $attachment['path'];
        $fullPath = storage_path('app/' . $path);

        return new UploadedFile(
            $fullPath,
            $attachment['filename'],
            $attachment['mime_type'],
            null,
            true // test mode
        );
    }
}
