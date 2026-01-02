<?php

namespace App\Jobs;

use App\Models\DocumentScan;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\AI\DocumentOCRService;
use App\Services\AI\IntelligentInvoiceExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InvoiceProcessedNotification;

/**
 * Process Uploaded Invoice Job
 *
 * Asynchronous job to process uploaded invoice documents:
 * 1. OCR extraction
 * 2. AI enhancement with Ollama
 * 3. Supplier matching
 * 4. Duplicate detection
 * 5. Auto-creation if high confidence
 */
class ProcessUploadedInvoice implements ShouldQueue
{
    use Queueable;

    public DocumentScan $scan;
    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(DocumentScan $scan)
    {
        $this->scan = $scan;
        $this->onQueue('documents'); // Dedicated queue for document processing
    }

    /**
     * Execute the job.
     */
    public function handle(
        DocumentOCRService $ocrService,
        IntelligentInvoiceExtractor $aiExtractor
    ): void
    {
        Log::info('Processing uploaded invoice', [
            'scan_id' => $this->scan->id,
            'filename' => $this->scan->original_filename,
        ]);

        try {
            // Update status
            $this->scan->update([
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            // Step 1: Perform OCR
            $ocrResult = $ocrService->processDocument(
                $this->scan->stored_path,
                $this->scan->document_type
            );

            // Step 2: Enhance with AI (Ollama)
            $enhanced = $aiExtractor->enhanceExtraction(
                $ocrResult['data'] ?? [],
                $ocrResult['text'] ?? ''
            );

            // Step 3: Match supplier
            $partner = $aiExtractor->matchSupplier($enhanced);

            if ($partner) {
                $enhanced['matched_partner_id'] = $partner->id;
                $enhanced['matched_partner_name'] = $partner->name;
                $enhanced['partner_match_confidence'] = 0.9;
            } else {
                $enhanced['partner_match_confidence'] = 0.0;
                $enhanced['needs_manual_partner_selection'] = true;
            }

            // Step 4: Detect duplicates
            $duplicateCheck = $aiExtractor->detectDuplicate($enhanced);
            $enhanced['duplicate_check'] = $duplicateCheck;

            // Step 5: Calculate overall confidence
            $overallConfidence = $this->calculateOverallConfidence(
                $enhanced,
                $partner !== null,
                !$duplicateCheck['duplicate']
            );

            // Update scan with results
            $this->scan->update([
                'raw_ocr_text' => $ocrResult['text'] ?? '',
                'extracted_data' => $enhanced,
                'overall_confidence' => $overallConfidence,
                'status' => 'completed',
                'processing_completed_at' => now(),
            ]);

            // Step 6: Auto-create invoice if confidence is high and not a duplicate
            if ($overallConfidence >= 0.85 && !$duplicateCheck['duplicate']) {
                $invoice = $this->autoCreateInvoice($this->scan, $enhanced, $partner);

                $this->scan->update([
                    'created_document_id' => $invoice->id,
                    'created_document_type' => Invoice::class,
                    'auto_created' => true,
                ]);

                Log::info('Invoice auto-created from document', [
                    'scan_id' => $this->scan->id,
                    'invoice_id' => $invoice->id,
                    'confidence' => $overallConfidence,
                ]);

                // Notify user
                $this->notifyUser($invoice, 'auto_created');

            } elseif ($overallConfidence >= 0.70) {
                // Medium confidence - require manual validation
                Log::info('Invoice ready for manual validation', [
                    'scan_id' => $this->scan->id,
                    'confidence' => $overallConfidence,
                ]);

                $this->notifyUser(null, 'requires_validation');

            } else {
                // Low confidence - flag for manual review
                Log::warning('Low confidence invoice extraction', [
                    'scan_id' => $this->scan->id,
                    'confidence' => $overallConfidence,
                ]);

                $this->notifyUser(null, 'manual_entry_recommended');
            }

        } catch (\Exception $e) {
            Log::error('Invoice processing failed', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->scan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_completed_at' => now(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Auto-create invoice from scan data
     */
    protected function autoCreateInvoice(
        DocumentScan $scan,
        array $data,
        ?Partner $partner
    ): Invoice
    {
        // Prepare invoice data
        $invoiceData = [
            'company_id' => $scan->company_id,
            'partner_id' => $partner?->id,
            'type' => $data['invoice_type'] ?? 'in', // Default to purchase invoice
            'invoice_number' => $data['invoice_number'] ?? 'AUTO-' . now()->timestamp,
            'invoice_date' => $data['invoice_date'] ?? now(),
            'due_date' => $data['due_date'] ?? now()->addDays($data['payment_terms'] ?? 30),
            'currency' => $data['currency'] ?? 'EUR',
            'total_excl_vat' => $data['total_excl_vat'] ?? 0,
            'total_vat' => $data['total_vat'] ?? 0,
            'total_incl_vat' => $data['total_incl_vat'] ?? 0,
            'status' => 'draft', // Always create as draft for review
            'notes' => "Créée automatiquement par OCR/IA (confiance: {$scan->overall_confidence})",
            'source' => 'ocr_auto',
            'document_scan_id' => $scan->id,
        ];

        // Create invoice
        $invoice = Invoice::create($invoiceData);

        // Add line items
        if (isset($data['line_items']) && is_array($data['line_items'])) {
            foreach ($data['line_items'] as $item) {
                $invoice->lines()->create([
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'vat_rate' => $item['vat_rate'] ?? 21,
                    'line_amount' => $item['amount'] ?? 0,
                    'account_id' => $this->resolveAccountId($item['suggested_account'] ?? null),
                ]);
            }
        }

        return $invoice;
    }

    /**
     * Resolve account ID from code suggestion
     */
    protected function resolveAccountId(?string $accountCode): ?int
    {
        if (!$accountCode) {
            return null;
        }

        $account = \App\Models\Account::where('code', $accountCode)
            ->where('company_id', $this->scan->company_id)
            ->first();

        return $account?->id;
    }

    /**
     * Calculate overall confidence score
     */
    protected function calculateOverallConfidence(
        array $data,
        bool $partnerMatched,
        bool $notDuplicate
    ): float
    {
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

        // Line items quality
        if (isset($data['line_items']) && count($data['line_items']) > 0) {
            $hasAccounts = 0;
            foreach ($data['line_items'] as $item) {
                if (!empty($item['suggested_account'])) {
                    $hasAccounts++;
                }
            }
            $scores[] = $hasAccounts / count($data['line_items']);
        } else {
            $scores[] = 0.3;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Notify user about processing result
     */
    protected function notifyUser(?Invoice $invoice, string $status): void
    {
        try {
            $user = $this->scan->createdBy; // Assuming relationship exists

            if ($user) {
                $user->notify(new InvoiceProcessedNotification(
                    $this->scan,
                    $invoice,
                    $status
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send notification', [
                'scan_id' => $this->scan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessUploadedInvoice job permanently failed', [
            'scan_id' => $this->scan->id,
            'exception' => $exception->getMessage(),
        ]);

        $this->scan->update([
            'status' => 'failed',
            'error_message' => 'Traitement échoué après ' . $this->tries . ' tentatives: ' . $exception->getMessage(),
            'processing_completed_at' => now(),
        ]);
    }
}
