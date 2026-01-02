<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\UblService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PeppolWebhookController extends Controller
{
    protected UblService $ublService;

    public function __construct(UblService $ublService)
    {
        $this->ublService = $ublService;
    }

    /**
     * Handle incoming Peppol invoice webhook
     */
    public function handle(Request $request, string $webhookSecret)
    {
        // Find company by webhook secret
        $company = Company::where('peppol_webhook_secret', $webhookSecret)->first();

        if (!$company) {
            Log::warning('Peppol webhook: Invalid webhook secret', [
                'secret' => $webhookSecret,
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid webhook secret'], 403);
        }

        try {
            // Set company context for tenant
            session(['current_tenant_id' => $company->id]);

            // Parse webhook payload based on provider
            $provider = config('peppol.provider', 'recommand');
            $invoiceData = match($provider) {
                'recommand' => $this->parseRecommandWebhook($request),
                'digiteal' => $this->parseDigitealWebhook($request),
                'b2brouter' => $this->parseB2BrouterWebhook($request),
                default => $this->parseGenericWebhook($request),
            };

            if (!$invoiceData) {
                return response()->json(['error' => 'Invalid webhook payload'], 400);
            }

            // Process the invoice
            $invoice = $this->processIncomingInvoice($company, $invoiceData);

            Log::info('Peppol webhook: Invoice processed successfully', [
                'company_id' => $company->id,
                'invoice_id' => $invoice->id,
                'transmission_id' => $invoiceData['transmission_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'message' => 'Invoice processed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Peppol webhook: Processing failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse Recommand.eu webhook payload
     */
    protected function parseRecommandWebhook(Request $request): ?array
    {
        $payload = $request->json()->all();

        if (!isset($payload['document']) || !isset($payload['document_type'])) {
            return null;
        }

        return [
            'transmission_id' => $payload['transmission_id'] ?? null,
            'sender_id' => $payload['sender_id'] ?? null,
            'document_type' => $payload['document_type'],
            'format' => $payload['format'] ?? 'ubl',
            'ubl_xml' => base64_decode($payload['document']),
            'metadata' => $payload['metadata'] ?? [],
            'received_at' => $payload['timestamp'] ?? now(),
        ];
    }

    /**
     * Parse Digiteal webhook payload
     */
    protected function parseDigitealWebhook(Request $request): ?array
    {
        $payload = $request->json()->all();

        if (!isset($payload['document_content'])) {
            return null;
        }

        return [
            'transmission_id' => $payload['message_id'] ?? null,
            'sender_id' => $payload['sender_identifier'] ?? null,
            'document_type' => 'invoice',
            'format' => $payload['document_format'] ?? 'UBL',
            'ubl_xml' => base64_decode($payload['document_content']),
            'metadata' => [],
            'received_at' => $payload['received_at'] ?? now(),
        ];
    }

    /**
     * Parse B2Brouter webhook payload
     */
    protected function parseB2BrouterWebhook(Request $request): ?array
    {
        $payload = $request->json()->all();

        if (!isset($payload['xml_content'])) {
            return null;
        }

        return [
            'transmission_id' => $payload['transmission_uuid'] ?? null,
            'sender_id' => $payload['from'] ?? null,
            'document_type' => $payload['document_type'] ?? 'invoice',
            'format' => 'ubl',
            'ubl_xml' => $payload['xml_content'],
            'metadata' => [],
            'received_at' => now(),
        ];
    }

    /**
     * Parse generic webhook payload
     */
    protected function parseGenericWebhook(Request $request): ?array
    {
        $payload = $request->json()->all();

        // Try to extract UBL from common fields
        $ublXml = $payload['ubl_xml']
            ?? $payload['xml']
            ?? $payload['document']
            ?? $payload['content']
            ?? null;

        if (!$ublXml) {
            return null;
        }

        // Decode if base64
        if (base64_decode($ublXml, true) !== false) {
            $ublXml = base64_decode($ublXml);
        }

        return [
            'transmission_id' => $payload['id'] ?? null,
            'sender_id' => $payload['sender'] ?? null,
            'document_type' => 'invoice',
            'format' => 'ubl',
            'ubl_xml' => $ublXml,
            'metadata' => $payload,
            'received_at' => now(),
        ];
    }

    /**
     * Process incoming invoice and create purchase invoice
     */
    protected function processIncomingInvoice(Company $company, array $data): Invoice
    {
        // Parse UBL XML
        $invoiceData = $this->ublService->parseInvoiceUbl($data['ubl_xml']);

        // Find or create supplier
        $supplier = $this->findOrCreateSupplier($company, $invoiceData['supplier']);

        // Store UBL file
        $ublPath = $this->storeUblFile($company, $data['ubl_xml'], $invoiceData['invoice_number']);

        // Create purchase invoice
        $invoice = Invoice::create([
            'company_id' => $company->id,
            'partner_id' => $supplier->id,
            'type' => 'purchase',
            'invoice_number' => $invoiceData['invoice_number'],
            'invoice_date' => $invoiceData['invoice_date'],
            'due_date' => $invoiceData['due_date'],
            'currency' => $invoiceData['currency'] ?? 'EUR',
            'subtotal' => $invoiceData['subtotal'],
            'vat_amount' => $invoiceData['vat_amount'],
            'total_amount' => $invoiceData['total_amount'],
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'notes' => $invoiceData['notes'] ?? null,
            'peppol_received' => true,
            'peppol_transmission_id' => $data['transmission_id'],
            'peppol_received_at' => $data['received_at'],
            'ubl_file_path' => $ublPath,
        ]);

        // Create invoice lines
        foreach ($invoiceData['lines'] as $lineData) {
            $invoice->lines()->create([
                'description' => $lineData['description'],
                'quantity' => $lineData['quantity'],
                'unit_price' => $lineData['unit_price'],
                'vat_rate' => $lineData['vat_rate'],
                'vat_amount' => $lineData['vat_amount'],
                'total' => $lineData['total'],
                'account_code' => $lineData['account_code'] ?? null,
            ]);
        }

        return $invoice;
    }

    /**
     * Find or create supplier from UBL data
     */
    protected function findOrCreateSupplier(Company $company, array $supplierData): Partner
    {
        // Try to find by VAT number first
        if (!empty($supplierData['vat_number'])) {
            $partner = Partner::where('company_id', $company->id)
                ->where('vat_number', $supplierData['vat_number'])
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        // Try to find by Peppol ID
        if (!empty($supplierData['peppol_id'])) {
            $partner = Partner::where('company_id', $company->id)
                ->where('peppol_id', $supplierData['peppol_id'])
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        // Create new supplier
        return Partner::create([
            'company_id' => $company->id,
            'type' => 'supplier',
            'name' => $supplierData['name'],
            'vat_number' => $supplierData['vat_number'] ?? null,
            'peppol_id' => $supplierData['peppol_id'] ?? null,
            'street' => $supplierData['street'] ?? null,
            'postal_code' => $supplierData['postal_code'] ?? null,
            'city' => $supplierData['city'] ?? null,
            'country_code' => $supplierData['country_code'] ?? 'BE',
            'email' => $supplierData['email'] ?? null,
            'phone' => $supplierData['phone'] ?? null,
        ]);
    }

    /**
     * Store UBL XML file
     */
    protected function storeUblFile(Company $company, string $ublXml, string $invoiceNumber): string
    {
        $filename = 'peppol_' . $invoiceNumber . '_' . time() . '.xml';
        $path = "companies/{$company->id}/peppol/incoming/{$filename}";

        Storage::put($path, $ublXml);

        return $path;
    }
}
