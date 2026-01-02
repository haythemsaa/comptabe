<?php

namespace App\Jobs;

use App\Models\DocumentScan;
use App\Models\User;
use App\Notifications\AnomalyDetectedNotification;
use App\Services\AI\DocumentOCRService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessUploadedDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes max for OCR processing

    public function __construct(
        public string $filePath,
        public string $documentType,
        public string $companyId,
        public string $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DocumentOCRService $ocrService): void
    {
        try {
            Log::info('Starting document OCR processing', [
                'file_path' => $this->filePath,
                'document_type' => $this->documentType,
                'company_id' => $this->companyId,
            ]);

            // Get file from storage
            $tempPath = $this->downloadFromStorage();

            // Create DocumentScan record
            $scan = DocumentScan::create([
                'company_id' => $this->companyId,
                'original_filename' => basename($this->filePath),
                'stored_path' => $this->filePath,
                'mime_type' => $this->getMimeType(),
                'file_size' => Storage::disk('private')->size($this->filePath),
                'document_type' => $this->documentType,
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            // Perform OCR extraction
            $ocrResult = $ocrService->processDocument(
                new \Illuminate\Http\UploadedFile($tempPath, basename($this->filePath)),
                $this->documentType
            );

            // Check for duplicate documents
            $this->checkForDuplicates($scan);

            // Notify user of completion
            $user = User::find($this->userId);
            if ($user && $scan->overall_confidence < 0.75) {
                // Low confidence - needs manual review
                $user->notify(new AnomalyDetectedNotification(
                    anomalyType: 'low_ocr_confidence',
                    description: "Le document {$scan->original_filename} a été traité avec une faible confiance ({$scan->overall_confidence}%). Une révision manuelle est recommandée.",
                    details: [
                        'document_id' => $scan->id,
                        'confidence' => $scan->overall_confidence,
                        'missing_fields' => $this->getMissingCriticalFields($scan),
                    ],
                    severity: 'medium',
                    entityType: 'document_scan',
                    entityId: $scan->id,
                    suggestedActions: [
                        'Vérifier les données extraites',
                        'Compléter les champs manquants manuellement',
                        'Confirmer les montants et dates',
                    ]
                ));
            }

            // Cleanup temp file
            @unlink($tempPath);

            Log::info('Document OCR processing completed', [
                'scan_id' => $scan->id,
                'confidence' => $scan->overall_confidence,
                'auto_created' => $scan->created_document_id ? 'yes' : 'no',
            ]);

        } catch (\Exception $e) {
            Log::error('Document OCR processing failed', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Notify user of failure
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new AnomalyDetectedNotification(
                    anomalyType: 'document_processing_failed',
                    description: "Échec du traitement du document {$this->filePath}",
                    details: [
                        'error' => $e->getMessage(),
                    ],
                    severity: 'high',
                    suggestedActions: [
                        'Vérifier la qualité du document scanné',
                        'Réessayer avec une meilleure résolution',
                        'Saisir manuellement si le problème persiste',
                    ]
                ));
            }

            throw $e;
        }
    }

    /**
     * Download file from storage to temporary location.
     */
    protected function downloadFromStorage(): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'doc_');
        $content = Storage::disk('private')->get($this->filePath);
        file_put_contents($tempPath, $content);
        return $tempPath;
    }

    /**
     * Get MIME type of the file.
     */
    protected function getMimeType(): string
    {
        $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);

        return match(strtolower($extension)) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'tiff', 'tif' => 'image/tiff',
            default => 'application/octet-stream',
        };
    }

    /**
     * Check for duplicate documents.
     */
    protected function checkForDuplicates(DocumentScan $scan): void
    {
        if (!$scan->extracted_data) {
            return;
        }

        $data = $scan->extracted_data;

        // Check by invoice number + supplier
        if (isset($data['invoice_number']['value']) && isset($data['matched_partner']['id'])) {
            $duplicate = DocumentScan::where('company_id', $this->companyId)
                ->where('id', '!=', $scan->id)
                ->where('document_type', 'invoice')
                ->whereJsonContains('extracted_data->invoice_number->value', $data['invoice_number']['value'])
                ->whereJsonContains('extracted_data->matched_partner->id', $data['matched_partner']['id'])
                ->first();

            if ($duplicate) {
                $this->notifyDuplicateFound($scan, $duplicate, 'invoice_number_and_supplier');
                $scan->update([
                    'duplicate_of_id' => $duplicate->id,
                    'is_duplicate' => true,
                ]);
            }
        }

        // Check by file hash (exact duplicate)
        $fileHash = md5(Storage::disk('private')->get($this->filePath));
        $hashDuplicate = DocumentScan::where('company_id', $this->companyId)
            ->where('id', '!=', $scan->id)
            ->where('file_hash', $fileHash)
            ->first();

        if ($hashDuplicate) {
            $this->notifyDuplicateFound($scan, $hashDuplicate, 'file_hash');
            $scan->update([
                'file_hash' => $fileHash,
                'duplicate_of_id' => $hashDuplicate->id,
                'is_duplicate' => true,
            ]);
        } else {
            $scan->update(['file_hash' => $fileHash]);
        }

        // Check by structured communication
        if (isset($data['structured_communication']['raw'])) {
            $commDuplicate = DocumentScan::where('company_id', $this->companyId)
                ->where('id', '!=', $scan->id)
                ->where('document_type', 'invoice')
                ->whereJsonContains('extracted_data->structured_communication->raw', $data['structured_communication']['raw'])
                ->where('created_at', '>', now()->subDays(90))
                ->first();

            if ($commDuplicate && !$scan->is_duplicate) {
                $this->notifyDuplicateFound($scan, $commDuplicate, 'structured_communication');
            }
        }
    }

    /**
     * Notify about duplicate found.
     */
    protected function notifyDuplicateFound(DocumentScan $scan, DocumentScan $duplicate, string $matchType): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $matchDescription = match($matchType) {
            'invoice_number_and_supplier' => 'même numéro de facture et fournisseur',
            'file_hash' => 'fichier identique (hash MD5)',
            'structured_communication' => 'même communication structurée',
            default => 'critères similaires',
        };

        $user->notify(new AnomalyDetectedNotification(
            anomalyType: 'duplicate_transaction',
            description: "Le document {$scan->original_filename} semble être un doublon d'un document existant",
            details: [
                'new_document' => $scan->original_filename,
                'existing_document' => $duplicate->original_filename,
                'match_type' => $matchDescription,
                'existing_date' => $duplicate->created_at->format('d/m/Y H:i'),
            ],
            severity: 'medium',
            entityType: 'document_scan',
            entityId: $scan->id,
            suggestedActions: [
                'Comparer les deux documents',
                'Supprimer le doublon si confirmé',
                'Vérifier qu\'il ne s\'agit pas d\'une erreur de numérisation',
            ]
        ));
    }

    /**
     * Get missing critical fields for low confidence scans.
     */
    protected function getMissingCriticalFields(DocumentScan $scan): array
    {
        $missing = [];
        $data = $scan->extracted_data ?? [];

        $criticalFields = [
            'invoice_number' => 'Numéro de facture',
            'invoice_date' => 'Date de facture',
            'vat_number' => 'Numéro TVA',
            'amounts' => 'Montants',
        ];

        foreach ($criticalFields as $field => $label) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessUploadedDocument job failed permanently', [
            'file_path' => $this->filePath,
            'error' => $exception->getMessage(),
        ]);

        // Clean up file if it still exists
        if (Storage::disk('private')->exists($this->filePath)) {
            Storage::disk('private')->delete($this->filePath);
        }
    }
}
