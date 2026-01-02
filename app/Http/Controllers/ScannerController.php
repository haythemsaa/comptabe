<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessUploadedInvoice;
use App\Models\DocumentScan;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\AI\DocumentOCRService;
use App\Services\AI\IntelligentInvoiceExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Scanner Controller for Intelligent Document Upload
 *
 * Handles OCR scanning and auto-creation of invoices from uploaded documents
 */
class ScannerController extends Controller
{
    protected DocumentOCRService $ocrService;
    protected IntelligentInvoiceExtractor $aiExtractor;

    public function __construct(
        DocumentOCRService $ocrService,
        IntelligentInvoiceExtractor $aiExtractor
    ) {
        $this->ocrService = $ocrService;
        $this->aiExtractor = $aiExtractor;
    }

    /**
     * Display the scanner page
     */
    public function index()
    {
        return view('documents.scan');
    }

    /**
     * Scan uploaded document with OCR + AI
     *
     * POST /scanner/scan
     * Body: multipart/form-data with 'document' file and 'document_type' string
     *
     * Returns: JSON with extracted data, confidence scores, duplicate warnings
     */
    public function scan(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'document_type' => 'required|string|in:invoice,expense,receipt,quote',
        ]);

        try {
            $file = $request->file('document');
            $documentType = $request->input('document_type');
            $companyId = Auth::user()->current_company_id;

            // Store file temporarily
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('document_scans/temp', $filename, 'local');
            $fullPath = Storage::disk('local')->path($path);

            Log::info('Scanner: Processing document', [
                'filename' => $file->getClientOriginalName(),
                'type' => $documentType,
                'size' => $file->getSize(),
            ]);

            // Step 1: Perform OCR
            $ocrResult = $this->ocrService->processDocument($fullPath, $documentType);

            // Step 2: Enhance with AI (Ollama)
            $enhanced = $this->aiExtractor->enhanceExtraction(
                $ocrResult['data'] ?? [],
                $ocrResult['text'] ?? ''
            );

            // Step 3: Try to match supplier
            $matchedPartner = null;
            if (!empty($enhanced['supplier_name']) || !empty($enhanced['vat_number'])) {
                $matchedPartner = $this->aiExtractor->matchSupplier($enhanced);
            }

            // Step 4: Detect duplicates
            $duplicateCheck = $this->aiExtractor->detectDuplicate($enhanced);

            // Step 5: Calculate overall confidence
            $overallConfidence = $this->calculateOverallConfidence(
                $enhanced,
                $matchedPartner !== null,
                !$duplicateCheck['duplicate']
            );

            // Prepare response data for frontend
            $responseData = $this->formatResponseData($enhanced, $matchedPartner);
            $responseData['overall_confidence'] = $overallConfidence;
            $responseData['temp_file_path'] = $path;

            // Validation errors/warnings
            $validationErrors = $this->getValidationWarnings($enhanced);

            // Duplicate warning
            $duplicateWarning = null;
            if ($duplicateCheck['duplicate']) {
                $duplicateWarning = [
                    'message' => "Une facture similaire existe déjà (#{$duplicateCheck['existing_invoice_id']})",
                    'url' => route('invoices.show', $duplicateCheck['existing_invoice_id']),
                    'confidence' => $duplicateCheck['confidence'],
                ];
            }

            // AI suggestions
            $aiSuggestions = [];
            if (isset($enhanced['suggestions']) && is_array($enhanced['suggestions'])) {
                foreach ($enhanced['suggestions'] as $suggestion) {
                    if (str_contains($suggestion, 'catégorie')) {
                        $aiSuggestions['category'] = $suggestion;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Document scanné avec succès (confiance: " . round($overallConfidence * 100) . "%)",
                'data' => $responseData,
                'validation_errors' => $validationErrors,
                'duplicate_warning' => $duplicateWarning,
                'ai_suggestions' => $aiSuggestions,
                'extracted_data' => $enhanced, // Full data with confidence per field
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner: Scan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create invoice from scanned data
     *
     * POST /scanner/create-invoice
     * Body: multipart/form-data with extracted data + original document
     *
     * Returns: JSON with created invoice details
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'document' => 'required|file',
            'document_type' => 'required|string',
            'supplier_name' => 'required|string',
            'invoice_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            $companyId = Auth::user()->current_company_id;
            $file = $request->file('document');

            // Store document permanently
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs("document_scans/{$companyId}", $filename, 'local');

            // Create DocumentScan record
            $scan = DocumentScan::create([
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'document_type' => $request->input('document_type'),
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'status' => 'pending',
            ]);

            // Prepare extracted data from request
            $extractedData = [
                'invoice_number' => $request->input('invoice_number'),
                'invoice_date' => $request->input('invoice_date'),
                'due_date' => $request->input('due_date'),
                'supplier_name' => $request->input('supplier_name'),
                'supplier_vat' => $request->input('supplier_vat'),
                'total_excl_vat' => $request->input('subtotal'),
                'total_vat' => $request->input('vat_amount'),
                'total_incl_vat' => $request->input('total_amount'),
                'currency' => 'EUR',
                'description' => $request->input('description'),
            ];

            // Try to find or create partner
            $partner = $this->findOrCreatePartner($extractedData);

            // Create invoice directly (manual creation with validated data)
            $invoice = Invoice::create([
                'company_id' => $companyId,
                'partner_id' => $partner?->id,
                'type' => 'in', // Purchase invoice
                'invoice_number' => $extractedData['invoice_number'] ?? 'SCAN-' . now()->timestamp,
                'invoice_date' => $extractedData['invoice_date'],
                'due_date' => $extractedData['due_date'] ?? now()->addDays(30),
                'currency' => 'EUR',
                'total_excl_vat' => $extractedData['total_excl_vat'] ?? 0,
                'total_vat' => $extractedData['total_vat'] ?? 0,
                'total_incl_vat' => $extractedData['total_incl_vat'],
                'status' => 'draft',
                'notes' => "Créée via scan OCR/IA\n" . ($extractedData['description'] ?? ''),
                'source' => 'ocr_manual',
                'document_scan_id' => $scan->id,
            ]);

            // Add basic line item if amounts provided
            if (!empty($extractedData['total_excl_vat'])) {
                $invoice->lines()->create([
                    'description' => $extractedData['description'] ?? 'Facture scannée',
                    'quantity' => 1,
                    'unit_price' => $extractedData['total_excl_vat'],
                    'vat_rate' => $request->input('vat_rate', 21),
                    'line_amount' => $extractedData['total_excl_vat'],
                ]);
            }

            // Update scan record
            $scan->update([
                'status' => 'completed',
                'extracted_data' => $extractedData,
                'created_document_id' => $invoice->id,
                'created_document_type' => Invoice::class,
                'processing_completed_at' => now(),
            ]);

            Log::info('Scanner: Invoice created manually from scan', [
                'scan_id' => $scan->id,
                'invoice_id' => $invoice->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès !',
                'invoice' => [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'url' => route('invoices.show', $invoice),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner: Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process document asynchronously (queue job)
     *
     * Alternative endpoint for background processing
     */
    public function processAsync(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'document_type' => 'required|string|in:invoice,expense,receipt,quote',
        ]);

        try {
            $companyId = Auth::user()->current_company_id;
            $file = $request->file('document');

            // Store document
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs("document_scans/{$companyId}", $filename, 'local');

            // Create scan record
            $scan = DocumentScan::create([
                'company_id' => $companyId,
                'user_id' => Auth::id(),
                'document_type' => $request->input('document_type'),
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'status' => 'queued',
            ]);

            // Dispatch job
            ProcessUploadedInvoice::dispatch($scan);

            return response()->json([
                'success' => true,
                'message' => 'Document mis en file d\'attente pour traitement',
                'scan_id' => $scan->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Scanner: Async processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format response data for frontend
     */
    protected function formatResponseData(array $enhanced, ?Partner $partner): array
    {
        return [
            'supplier_name' => $enhanced['supplier_name'] ?? '',
            'supplier_vat' => $enhanced['vat_number'] ?? '',
            'invoice_number' => $enhanced['invoice_number'] ?? '',
            'invoice_date' => $enhanced['invoice_date'] ?? now()->format('Y-m-d'),
            'due_date' => $enhanced['due_date'] ?? now()->addDays(30)->format('Y-m-d'),
            'subtotal' => $enhanced['total_excl_vat'] ?? 0,
            'vat_amount' => $enhanced['total_vat'] ?? 0,
            'total_amount' => $enhanced['total_incl_vat'] ?? 0,
            'vat_rate' => $this->extractVatRate($enhanced),
            'description' => $this->extractDescription($enhanced),
            'matched_partner' => $partner ? [
                'id' => $partner->id,
                'name' => $partner->name,
                'vat' => $partner->vat_number,
            ] : null,
        ];
    }

    /**
     * Extract VAT rate from enhanced data
     */
    protected function extractVatRate(array $data): int
    {
        if (isset($data['line_items'][0]['vat_rate'])) {
            return (int) $data['line_items'][0]['vat_rate'];
        }

        // Default Belgian VAT rate
        return 21;
    }

    /**
     * Extract description from line items
     */
    protected function extractDescription(array $data): string
    {
        if (isset($data['line_items'][0]['description'])) {
            return $data['line_items'][0]['description'];
        }

        return '';
    }

    /**
     * Calculate overall confidence score
     */
    protected function calculateOverallConfidence(
        array $data,
        bool $partnerMatched,
        bool $notDuplicate
    ): float {
        $scores = [];

        // AI confidence
        $scores[] = $data['ai_confidence'] ?? 0.5;

        // Partner match bonus
        $scores[] = $partnerMatched ? 0.9 : 0.4;

        // Not duplicate bonus
        $scores[] = $notDuplicate ? 0.95 : 0.0;

        // Critical fields present
        $criticalFields = ['invoice_number', 'invoice_date', 'total_incl_vat'];
        $presentCount = 0;
        foreach ($criticalFields as $field) {
            if (!empty($data[$field])) {
                $presentCount++;
            }
        }
        $scores[] = $presentCount / count($criticalFields);

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Get validation warnings for extracted data
     */
    protected function getValidationWarnings(array $data): array
    {
        $warnings = [];

        if (empty($data['invoice_number'])) {
            $warnings[] = "Numéro de facture non détecté";
        }

        if (empty($data['vat_number'])) {
            $warnings[] = "Numéro de TVA fournisseur manquant";
        }

        if (empty($data['total_incl_vat']) || $data['total_incl_vat'] <= 0) {
            $warnings[] = "Montant total invalide ou manquant";
        }

        // Check VAT calculation
        if (isset($data['total_excl_vat'], $data['total_vat'], $data['total_incl_vat'])) {
            $calculatedTotal = $data['total_excl_vat'] + $data['total_vat'];
            $difference = abs($calculatedTotal - $data['total_incl_vat']);

            if ($difference > 0.02) { // 2 cents tolerance
                $warnings[] = "Incohérence dans les montants TVA (différence: " . number_format($difference, 2) . "€)";
            }
        }

        return $warnings;
    }

    /**
     * Find or create partner from extracted data
     */
    protected function findOrCreatePartner(array $data): ?Partner
    {
        $companyId = Auth::user()->current_company_id;

        // Try exact VAT match first
        if (!empty($data['supplier_vat'])) {
            $partner = Partner::where('company_id', $companyId)
                ->where('vat_number', $data['supplier_vat'])
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        // Try name match
        if (!empty($data['supplier_name'])) {
            $partner = Partner::where('company_id', $companyId)
                ->where('name', 'like', '%' . $data['supplier_name'] . '%')
                ->first();

            if ($partner) {
                return $partner;
            }

            // Create new partner if not found
            return Partner::create([
                'company_id' => $companyId,
                'name' => $data['supplier_name'],
                'vat_number' => $data['supplier_vat'] ?? null,
                'partner_type' => 'supplier',
                'notes' => 'Créé automatiquement via scan OCR',
            ]);
        }

        return null;
    }
}
