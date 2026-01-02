<?php

namespace App\Services\AI;

use App\Models\Invoice;
use App\Models\Partner;
use App\Models\VatCode;
use App\Models\ChartOfAccount;
use App\Models\DocumentScan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentOCRService
{
    protected array $config;
    protected IntelligentCategorizationService $categorizationService;

    public function __construct(IntelligentCategorizationService $categorizationService)
    {
        $this->categorizationService = $categorizationService;
        $this->config = [
            'ocr_provider' => config('services.ocr.provider', 'google_vision'),
            'confidence_threshold' => 0.75,
            'supported_formats' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'tiff'],
        ];
    }

    /**
     * Process uploaded document with OCR and AI extraction
     */
    public function processDocument(UploadedFile $file, string $type = 'invoice'): DocumentScan
    {
        // Store the original file
        $path = $file->store('documents/scans', 'private');

        // Create scan record
        $scan = DocumentScan::create([
            'company_id' => auth()->user()->current_company_id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => $type,
            'status' => 'processing',
            'processing_started_at' => now(),
        ]);

        try {
            // Perform OCR
            $ocrResult = $this->performOCR($path);
            $scan->update(['raw_ocr_text' => $ocrResult['text']]);

            // Extract structured data based on document type
            $extractedData = match ($type) {
                'invoice' => $this->extractInvoiceData($ocrResult),
                'receipt' => $this->extractReceiptData($ocrResult),
                'bank_statement' => $this->extractBankStatementData($ocrResult),
                default => $this->extractGenericData($ocrResult),
            };

            // Enhance with AI
            $enhancedData = $this->enhanceWithAI($extractedData, $type);

            // Match with existing data
            $matchedData = $this->matchWithExistingData($enhancedData);

            // Calculate confidence scores
            $confidenceScores = $this->calculateConfidenceScores($matchedData);

            $scan->update([
                'extracted_data' => $matchedData,
                'confidence_scores' => $confidenceScores,
                'overall_confidence' => $this->calculateOverallConfidence($confidenceScores),
                'status' => 'completed',
                'processing_completed_at' => now(),
            ]);

            // Auto-create invoice if confidence is high enough
            if ($scan->overall_confidence >= 0.85 && $type === 'invoice') {
                $invoice = $this->autoCreateInvoice($scan);
                $scan->update([
                    'created_document_id' => $invoice->id,
                    'created_document_type' => Invoice::class,
                ]);
            }

        } catch (\Exception $e) {
            $scan->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processing_completed_at' => now(),
            ]);
        }

        return $scan->fresh();
    }

    /**
     * Perform OCR on the document
     */
    protected function performOCR(string $path): array
    {
        $fileContent = Storage::disk('private')->get($path);
        $base64 = base64_encode($fileContent);

        // Use configured OCR provider
        return match ($this->config['ocr_provider']) {
            'google_vision' => $this->googleVisionOCR($base64),
            'azure' => $this->azureComputerVisionOCR($base64),
            'aws_textract' => $this->awsTextractOCR($base64),
            default => $this->localTesseractOCR($path),
        };
    }

    /**
     * Google Vision OCR
     */
    protected function googleVisionOCR(string $base64): array
    {
        $response = Http::post('https://vision.googleapis.com/v1/images:annotate', [
            'requests' => [[
                'image' => ['content' => $base64],
                'features' => [
                    ['type' => 'DOCUMENT_TEXT_DETECTION'],
                    ['type' => 'LOGO_DETECTION'],
                ],
            ]],
            'key' => config('services.google.vision_api_key'),
        ]);

        $result = $response->json();
        $textAnnotations = $result['responses'][0]['textAnnotations'] ?? [];
        $fullText = $textAnnotations[0]['description'] ?? '';

        return [
            'text' => $fullText,
            'blocks' => $this->parseTextBlocks($result['responses'][0]['fullTextAnnotation'] ?? []),
            'logos' => $result['responses'][0]['logoAnnotations'] ?? [],
            'confidence' => $this->extractAverageConfidence($result),
        ];
    }

    /**
     * Local Tesseract OCR fallback
     */
    protected function localTesseractOCR(string $path): array
    {
        $fullPath = Storage::disk('private')->path($path);

        // Use Tesseract via shell
        $outputFile = tempnam(sys_get_temp_dir(), 'ocr_');
        exec("tesseract {$fullPath} {$outputFile} -l fra+nld+eng --oem 3 --psm 3 2>&1", $output, $returnCode);

        $text = file_exists("{$outputFile}.txt") ? file_get_contents("{$outputFile}.txt") : '';
        @unlink("{$outputFile}.txt");

        return [
            'text' => $text,
            'blocks' => $this->parseRawText($text),
            'logos' => [],
            'confidence' => 0.7,
        ];
    }

    /**
     * Extract invoice data from OCR result
     */
    protected function extractInvoiceData(array $ocrResult): array
    {
        $text = $ocrResult['text'];

        return [
            'invoice_number' => $this->extractInvoiceNumber($text),
            'invoice_date' => $this->extractDate($text, 'invoice'),
            'due_date' => $this->extractDate($text, 'due'),
            'supplier' => $this->extractSupplierInfo($text),
            'vat_number' => $this->extractVatNumber($text),
            'iban' => $this->extractIBAN($text),
            'structured_communication' => $this->extractStructuredCommunication($text),
            'amounts' => $this->extractAmounts($text),
            'line_items' => $this->extractLineItems($text),
            'currency' => $this->extractCurrency($text),
        ];
    }

    /**
     * Extract invoice number using multiple patterns
     */
    protected function extractInvoiceNumber(string $text): ?array
    {
        $patterns = [
            '/(?:facture|invoice|factuurnummer|fact\.?\s*n[°o]?\.?)\s*[:\s]?\s*([A-Z0-9\-\/]+)/i',
            '/(?:n[°o]\.?\s*(?:de\s+)?facture)\s*[:\s]?\s*([A-Z0-9\-\/]+)/i',
            '/(?:document|ref|référence)\s*[:\s]?\s*([A-Z0-9\-\/]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return [
                    'value' => trim($matches[1]),
                    'confidence' => 0.9,
                    'pattern_used' => $pattern,
                ];
            }
        }

        return null;
    }

    /**
     * Extract dates from text
     */
    protected function extractDate(string $text, string $type = 'invoice'): ?array
    {
        $keywords = match ($type) {
            'invoice' => ['date\s*(?:de\s+)?facture', 'factuurdatum', 'invoice\s*date', 'date', 'du'],
            'due' => ['échéance', 'date\s*(?:d[\'e]\s*)?échéance', 'vervaldatum', 'due\s*date', 'à\s*payer\s*avant'],
            default => ['date'],
        };

        $datePatterns = [
            '/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', // DD/MM/YYYY or DD-MM-YYYY
            '/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/',   // YYYY-MM-DD
            '/(\d{1,2})\s+(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\s+(\d{4})/i',
        ];

        foreach ($keywords as $keyword) {
            $pattern = "/{$keyword}\s*[:\s]?\s*(.{10,30})/i";
            if (preg_match($pattern, $text, $contextMatch)) {
                $context = $contextMatch[1];

                foreach ($datePatterns as $datePattern) {
                    if (preg_match($datePattern, $context, $dateMatch)) {
                        $date = $this->parseDate($dateMatch);
                        if ($date) {
                            return [
                                'value' => $date->format('Y-m-d'),
                                'confidence' => 0.85,
                                'original' => $dateMatch[0],
                            ];
                        }
                    }
                }
            }
        }

        // Fallback: find any date in the document
        foreach ($datePatterns as $datePattern) {
            if (preg_match_all($datePattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $date = $this->parseDate($match);
                    if ($date && $date->isPast() && $date->isAfter(now()->subYears(2))) {
                        return [
                            'value' => $date->format('Y-m-d'),
                            'confidence' => 0.5,
                            'original' => $match[0],
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Parse date from regex match
     */
    protected function parseDate(array $match): ?Carbon
    {
        try {
            // Handle month names
            $months = [
                'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4,
                'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8,
                'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
            ];

            if (isset($months[strtolower($match[2] ?? '')])) {
                return Carbon::create($match[3], $months[strtolower($match[2])], $match[1]);
            }

            // Handle numeric dates
            if (strlen($match[1]) === 4) {
                // YYYY-MM-DD format
                return Carbon::create($match[1], $match[2], $match[3]);
            } else {
                // DD-MM-YYYY format
                $year = strlen($match[3]) === 2 ? '20' . $match[3] : $match[3];
                return Carbon::create($year, $match[2], $match[1]);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract Belgian VAT number
     */
    protected function extractVatNumber(string $text): ?array
    {
        $patterns = [
            '/BE\s?0?\s?(\d{3})[\s\.]?(\d{3})[\s\.]?(\d{3})/i',
            '/(?:tva|btw|vat|n[°o]?\s*(?:de\s+)?(?:tva|btw))\s*[:\s]?\s*(?:BE\s?)?0?(\d{3})[\s\.]?(\d{3})[\s\.]?(\d{3})/i',
            '/(?:numéro\s*d\'?entreprise|ondernemingsnummer)\s*[:\s]?\s*0?(\d{3})[\s\.]?(\d{3})[\s\.]?(\d{3})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $vatNumber = 'BE0' . $matches[1] . $matches[2] . $matches[3];

                // Validate Belgian VAT number (modulo 97)
                $number = intval($matches[1] . $matches[2] . $matches[3]);
                $isValid = (97 - ($number % 97)) === intval(substr($matches[3], -2)) ||
                           $this->validateBelgianVAT($vatNumber);

                return [
                    'value' => $vatNumber,
                    'formatted' => 'BE 0' . $matches[1] . '.' . $matches[2] . '.' . $matches[3],
                    'confidence' => $isValid ? 0.95 : 0.7,
                    'is_valid' => $isValid,
                ];
            }
        }

        // Try other EU VAT numbers
        $euPattern = '/([A-Z]{2})\s?([A-Z0-9]{8,12})/i';
        if (preg_match($euPattern, $text, $matches)) {
            return [
                'value' => strtoupper($matches[1] . $matches[2]),
                'confidence' => 0.6,
                'is_valid' => null,
            ];
        }

        return null;
    }

    /**
     * Validate Belgian VAT number
     */
    protected function validateBelgianVAT(string $vatNumber): bool
    {
        $vatNumber = preg_replace('/[^0-9]/', '', $vatNumber);
        if (strlen($vatNumber) !== 10) {
            return false;
        }

        $base = intval(substr($vatNumber, 0, 8));
        $check = intval(substr($vatNumber, 8, 2));

        return (97 - ($base % 97)) === $check;
    }

    /**
     * Extract IBAN
     */
    protected function extractIBAN(string $text): ?array
    {
        $pattern = '/([A-Z]{2}\d{2})\s?(\d{4})\s?(\d{4})\s?(\d{4})/i';

        if (preg_match($pattern, $text, $matches)) {
            $iban = strtoupper($matches[1] . $matches[2] . $matches[3] . $matches[4]);

            return [
                'value' => $iban,
                'formatted' => $matches[1] . ' ' . $matches[2] . ' ' . $matches[3] . ' ' . $matches[4],
                'confidence' => 0.9,
                'bank' => $this->identifyBank($iban),
            ];
        }

        return null;
    }

    /**
     * Extract Belgian structured communication
     */
    protected function extractStructuredCommunication(string $text): ?array
    {
        $patterns = [
            '/\+{3}(\d{3})[\/\s]?(\d{4})[\/\s]?(\d{5})\+{3}/',
            '/(\d{3})[\/\s](\d{4})[\/\s](\d{5})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $communication = $matches[1] . $matches[2] . $matches[3];

                // Validate checksum (modulo 97)
                $base = intval(substr($communication, 0, 10));
                $check = intval(substr($communication, 10, 2));
                $isValid = ($base % 97 === $check) || ($base % 97 === 0 && $check === 97);

                return [
                    'value' => '+++' . $matches[1] . '/' . $matches[2] . '/' . $matches[3] . '+++',
                    'raw' => $communication,
                    'confidence' => $isValid ? 0.95 : 0.6,
                    'is_valid' => $isValid,
                ];
            }
        }

        return null;
    }

    /**
     * Extract amounts from invoice
     */
    protected function extractAmounts(string $text): array
    {
        $amounts = [
            'total_excl_vat' => null,
            'vat_amount' => null,
            'total_incl_vat' => null,
            'vat_details' => [],
        ];

        // Total TTC / Total incl. VAT
        $totalPatterns = [
            '/(?:total\s*(?:à\s*payer|ttc|tvac)|te\s*betalen|amount\s*due|totaal)\s*[:\s]?\s*€?\s*([\d\s,\.]+)/i',
            '/€?\s*([\d\s,\.]+)\s*(?:eur|€)?\s*$/im',
        ];

        foreach ($totalPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $amounts['total_incl_vat'] = [
                    'value' => $this->parseAmount($matches[1]),
                    'confidence' => 0.85,
                ];
                break;
            }
        }

        // VAT amounts
        $vatPatterns = [
            '/(?:tva|btw|vat)\s*(\d+)\s*%\s*[:\s]?\s*€?\s*([\d\s,\.]+)/i',
            '/(\d+)\s*%\s*(?:tva|btw|vat)\s*[:\s]?\s*€?\s*([\d\s,\.]+)/i',
        ];

        foreach ($vatPatterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $rate = intval($match[1]);
                $amount = $this->parseAmount($match[2]);

                if (in_array($rate, [6, 12, 21])) {
                    $amounts['vat_details'][$rate] = [
                        'rate' => $rate,
                        'amount' => $amount,
                        'confidence' => 0.85,
                    ];
                }
            }
        }

        // Calculate totals if we have partial data
        if (!empty($amounts['vat_details'])) {
            $totalVat = array_sum(array_column($amounts['vat_details'], 'amount'));
            $amounts['vat_amount'] = ['value' => $totalVat, 'confidence' => 0.8];

            if ($amounts['total_incl_vat'] && !isset($amounts['total_excl_vat'])) {
                $amounts['total_excl_vat'] = [
                    'value' => $amounts['total_incl_vat']['value'] - $totalVat,
                    'confidence' => 0.75,
                ];
            }
        }

        // Total HT / excl. VAT
        $htPatterns = [
            '/(?:total\s*(?:ht|htva|hors\s*tva)|sous-?total|subtotal|netto)\s*[:\s]?\s*€?\s*([\d\s,\.]+)/i',
        ];

        foreach ($htPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $amounts['total_excl_vat'] = [
                    'value' => $this->parseAmount($matches[1]),
                    'confidence' => 0.85,
                ];
                break;
            }
        }

        return $amounts;
    }

    /**
     * Extract line items from invoice
     */
    protected function extractLineItems(string $text): array
    {
        $items = [];

        // Common line item patterns
        $patterns = [
            '/^(.{10,50})\s+(\d+(?:[,\.]\d+)?)\s+(\d+(?:[,\.]\d{2})?)\s+(\d+(?:[,\.]\d{2})?)$/m',
            '/(\d+)\s*[xX]\s*(.{10,50})\s+@?\s*€?\s*(\d+(?:[,\.]\d{2})?)/m',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $description = trim($match[1] ?? $match[2]);
                $quantity = $this->parseAmount($match[2] ?? $match[1] ?? '1');
                $unitPrice = $this->parseAmount($match[3] ?? '0');
                $totalPrice = $this->parseAmount($match[4] ?? ($quantity * $unitPrice));

                if ($description && $totalPrice > 0) {
                    $items[] = [
                        'description' => $description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'confidence' => 0.7,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Parse amount string to float
     */
    protected function parseAmount(string $amount): float
    {
        // Remove spaces and currency symbols
        $amount = preg_replace('/[^\d,\.]/', '', $amount);

        // Handle European format (1.234,56) vs US format (1,234.56)
        if (preg_match('/,\d{2}$/', $amount)) {
            // European format
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
        } else {
            // US format or no decimals
            $amount = str_replace(',', '', $amount);
        }

        return floatval($amount);
    }

    /**
     * Extract supplier information
     */
    protected function extractSupplierInfo(string $text): array
    {
        $info = [
            'name' => null,
            'address' => null,
            'city' => null,
            'postal_code' => null,
        ];

        // Usually supplier info is in the top portion
        $topPortion = substr($text, 0, min(1500, strlen($text)));

        // Extract company name (usually first bold/large text or after specific keywords)
        $namePatterns = [
            '/^([A-Z][A-Za-z\s&\-\.]+(?:SA|SPRL|SRL|BVBA|BV|NV|VOF))/m',
            '/(?:de|van|from)\s*[:\s]?\s*([A-Z][A-Za-z\s&\-\.]+)/i',
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $topPortion, $matches)) {
                $info['name'] = [
                    'value' => trim($matches[1]),
                    'confidence' => 0.7,
                ];
                break;
            }
        }

        // Extract Belgian postal code and city
        if (preg_match('/\b(\d{4})\s+([A-Za-zÀ-ÿ\-\s]+)\b/', $topPortion, $matches)) {
            $info['postal_code'] = ['value' => $matches[1], 'confidence' => 0.85];
            $info['city'] = ['value' => trim($matches[2]), 'confidence' => 0.8];
        }

        // Extract address
        $addressPatterns = [
            '/(?:rue|straat|avenue|boulevard|chaussée|steenweg)\s+[A-Za-zÀ-ÿ\s\-]+,?\s*\d+/i',
            '/\d+,?\s*(?:rue|straat|avenue|boulevard|chaussée|steenweg)\s+[A-Za-zÀ-ÿ\s\-]+/i',
        ];

        foreach ($addressPatterns as $pattern) {
            if (preg_match($pattern, $topPortion, $matches)) {
                $info['address'] = ['value' => trim($matches[0]), 'confidence' => 0.75];
                break;
            }
        }

        return $info;
    }

    /**
     * Extract currency
     */
    protected function extractCurrency(string $text): array
    {
        if (preg_match('/(?:EUR|€)/i', $text)) {
            return ['value' => 'EUR', 'confidence' => 0.95];
        }

        return ['value' => 'EUR', 'confidence' => 0.5]; // Default for Belgium
    }

    /**
     * Enhance extracted data with AI
     */
    protected function enhanceWithAI(array $data, string $type): array
    {
        // Categorize expenses using ML model
        if ($type === 'invoice' && isset($data['line_items'])) {
            foreach ($data['line_items'] as &$item) {
                $categorization = $this->categorizationService->categorize($item['description']);
                $item['suggested_account'] = $categorization['account'];
                $item['suggested_vat_code'] = $categorization['vat_code'];
                $item['category'] = $categorization['category'];
            }
        }

        // Predict payment terms based on supplier history
        if (isset($data['vat_number']['value'])) {
            $supplierHistory = $this->analyzeSupplierHistory($data['vat_number']['value']);
            $data['predicted_payment_terms'] = $supplierHistory['average_payment_days'] ?? 30;
        }

        return $data;
    }

    /**
     * Match extracted data with existing database records
     */
    protected function matchWithExistingData(array $data): array
    {
        // Try to match supplier
        if (isset($data['vat_number']['value'])) {
            $partner = Partner::where('vat_number', $data['vat_number']['value'])->first();

            if ($partner) {
                $data['matched_partner'] = [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'confidence' => 0.99,
                    'match_type' => 'vat_number',
                ];
            }
        }

        // Try fuzzy name matching if no VAT match
        if (!isset($data['matched_partner']) && isset($data['supplier']['name']['value'])) {
            $partners = Partner::where('type', 'supplier')
                ->where('name', 'LIKE', '%' . $data['supplier']['name']['value'] . '%')
                ->limit(5)
                ->get();

            if ($partners->isNotEmpty()) {
                $bestMatch = $this->findBestNameMatch($data['supplier']['name']['value'], $partners);
                if ($bestMatch) {
                    $data['matched_partner'] = $bestMatch;
                }
            }
        }

        // Match structured communication with existing invoices (for payments)
        if (isset($data['structured_communication']['raw'])) {
            $existingInvoice = Invoice::where('structured_communication', $data['structured_communication']['raw'])
                ->first();

            if ($existingInvoice) {
                $data['matched_invoice'] = [
                    'id' => $existingInvoice->id,
                    'invoice_number' => $existingInvoice->invoice_number,
                    'confidence' => 0.99,
                ];
            }
        }

        return $data;
    }

    /**
     * Calculate confidence scores for each field
     */
    protected function calculateConfidenceScores(array $data): array
    {
        $scores = [];

        $fields = [
            'invoice_number', 'invoice_date', 'due_date', 'vat_number',
            'amounts', 'supplier', 'iban', 'structured_communication',
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                if (is_array($data[$field]) && isset($data[$field]['confidence'])) {
                    $scores[$field] = $data[$field]['confidence'];
                } elseif (is_array($data[$field]) && isset($data[$field]['value'])) {
                    $scores[$field] = $data[$field]['value'] ? 0.7 : 0;
                } else {
                    $scores[$field] = !empty($data[$field]) ? 0.6 : 0;
                }
            } else {
                $scores[$field] = 0;
            }
        }

        // Boost confidence if we have a matched partner
        if (isset($data['matched_partner'])) {
            $scores['supplier'] = max($scores['supplier'] ?? 0, $data['matched_partner']['confidence']);
        }

        return $scores;
    }

    /**
     * Calculate overall confidence
     */
    protected function calculateOverallConfidence(array $scores): float
    {
        $weights = [
            'invoice_number' => 1.5,
            'invoice_date' => 1.5,
            'vat_number' => 2.0,
            'amounts' => 2.0,
            'supplier' => 1.5,
            'due_date' => 0.5,
            'iban' => 0.5,
            'structured_communication' => 0.5,
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($scores as $field => $score) {
            $weight = $weights[$field] ?? 1.0;
            $weightedSum += $score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;
    }

    /**
     * Auto-create invoice from high-confidence scan
     */
    protected function autoCreateInvoice(DocumentScan $scan): Invoice
    {
        $data = $scan->extracted_data;

        $invoice = Invoice::create([
            'company_id' => $scan->company_id,
            'partner_id' => $data['matched_partner']['id'] ?? null,
            'type' => 'in', // Purchase invoice
            'status' => 'draft',
            'invoice_number' => $data['invoice_number']['value'] ?? null,
            'invoice_date' => $data['invoice_date']['value'] ?? now(),
            'due_date' => $data['due_date']['value'] ?? now()->addDays(30),
            'total_excl_vat' => $data['amounts']['total_excl_vat']['value'] ?? 0,
            'vat_amount' => $data['amounts']['vat_amount']['value'] ?? 0,
            'total_incl_vat' => $data['amounts']['total_incl_vat']['value'] ?? 0,
            'structured_communication' => $data['structured_communication']['raw'] ?? null,
            'source' => 'ocr_scan',
            'source_document_id' => $scan->id,
            'needs_review' => true,
        ]);

        // Create line items
        if (!empty($data['line_items'])) {
            foreach ($data['line_items'] as $index => $item) {
                $invoice->lines()->create([
                    'line_number' => $index + 1,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_excl_vat' => $item['total_price'],
                    'vat_rate' => 21, // Default
                    'account_id' => $item['suggested_account'] ?? null,
                ]);
            }
        }

        return $invoice;
    }

    /**
     * Helper methods
     */
    protected function parseTextBlocks(array $annotation): array
    {
        $blocks = [];
        foreach ($annotation['pages'] ?? [] as $page) {
            foreach ($page['blocks'] ?? [] as $block) {
                $text = '';
                foreach ($block['paragraphs'] ?? [] as $paragraph) {
                    foreach ($paragraph['words'] ?? [] as $word) {
                        foreach ($word['symbols'] ?? [] as $symbol) {
                            $text .= $symbol['text'] ?? '';
                        }
                        $text .= ' ';
                    }
                }
                $blocks[] = ['text' => trim($text), 'confidence' => $block['confidence'] ?? 0];
            }
        }
        return $blocks;
    }

    protected function parseRawText(string $text): array
    {
        return array_map(fn($line) => ['text' => $line, 'confidence' => 0.7],
            array_filter(explode("\n", $text)));
    }

    protected function extractAverageConfidence(array $result): float
    {
        $confidences = [];
        foreach ($result['responses'][0]['fullTextAnnotation']['pages'] ?? [] as $page) {
            foreach ($page['blocks'] ?? [] as $block) {
                if (isset($block['confidence'])) {
                    $confidences[] = $block['confidence'];
                }
            }
        }
        return count($confidences) > 0 ? array_sum($confidences) / count($confidences) : 0.7;
    }

    protected function identifyBank(string $iban): ?string
    {
        $bankCodes = [
            '0000' => 'Banque Nationale de Belgique',
            '0010' => 'BNP Paribas Fortis',
            '0012' => 'BNP Paribas Fortis',
            '0013' => 'BNP Paribas Fortis',
            '0014' => 'BNP Paribas Fortis',
            '0015' => 'BNP Paribas Fortis',
            '0016' => 'BNP Paribas Fortis',
            '0017' => 'BNP Paribas Fortis',
            '0019' => 'BNP Paribas Fortis',
            '0636' => 'KBC Bank',
            '0682' => 'KBC Bank',
            '0683' => 'KBC Bank',
            '0731' => 'Belfius Bank',
            '0910' => 'ING Belgium',
            '0920' => 'ING Belgium',
            '0979' => 'Argenta',
            '3631' => 'Crelan',
        ];

        $bankCode = substr($iban, 4, 4);
        return $bankCodes[$bankCode] ?? null;
    }

    protected function findBestNameMatch(string $name, $partners): ?array
    {
        $bestMatch = null;
        $bestScore = 0;

        foreach ($partners as $partner) {
            similar_text(strtolower($name), strtolower($partner->name), $score);
            if ($score > $bestScore && $score > 70) {
                $bestScore = $score;
                $bestMatch = [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'confidence' => $score / 100,
                    'match_type' => 'fuzzy_name',
                ];
            }
        }

        return $bestMatch;
    }

    protected function analyzeSupplierHistory(string $vatNumber): array
    {
        $partner = Partner::where('vat_number', $vatNumber)->first();

        if (!$partner) {
            return ['average_payment_days' => 30];
        }

        $invoices = Invoice::where('partner_id', $partner->id)
            ->where('type', 'in')
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->get();

        if ($invoices->isEmpty()) {
            return ['average_payment_days' => 30];
        }

        $paymentDays = $invoices->map(function ($invoice) {
            return $invoice->paid_at->diffInDays($invoice->invoice_date);
        });

        return [
            'average_payment_days' => round($paymentDays->avg()),
            'typical_due_days' => 30,
            'invoice_count' => $invoices->count(),
        ];
    }

    protected function extractReceiptData(array $ocrResult): array
    {
        // Similar to invoice but optimized for receipts
        return $this->extractInvoiceData($ocrResult);
    }

    protected function extractBankStatementData(array $ocrResult): array
    {
        // Extract bank statement specific data
        return [
            'account_number' => $this->extractIBAN($ocrResult['text']),
            'statement_date' => $this->extractDate($ocrResult['text'], 'invoice'),
            'transactions' => [],
        ];
    }

    protected function extractGenericData(array $ocrResult): array
    {
        return [
            'text' => $ocrResult['text'],
            'dates' => [],
            'amounts' => $this->extractAmounts($ocrResult['text']),
        ];
    }
}
