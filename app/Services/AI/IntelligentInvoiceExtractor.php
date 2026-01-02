<?php

namespace App\Services\AI;

use App\Models\Partner;
use App\Models\Invoice;
use App\Models\VatCode;
use App\Models\Account;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Intelligent Invoice Extractor using Ollama LLM
 *
 * Uses local Ollama for zero-cost AI-powered invoice data extraction and validation
 */
class IntelligentInvoiceExtractor
{
    protected string $ollamaEndpoint;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        // Support both OLLAMA_ENDPOINT and OLLAMA_BASE_URL
        $this->ollamaEndpoint = env('OLLAMA_ENDPOINT', env('OLLAMA_BASE_URL', 'http://localhost:11434'));
        $this->model = env('OLLAMA_MODEL', 'llama3.1');
        $this->timeout = env('OLLAMA_TIMEOUT', 30); // seconds
    }

    /**
     * Enhance OCR-extracted data using Ollama AI
     *
     * @param array $ocrData Raw OCR extracted data
     * @param string $rawText Full OCR text
     * @return array Enhanced and validated data
     */
    public function enhanceExtraction(array $ocrData, string $rawText): array
    {
        try {
            // Build context prompt for Ollama
            $prompt = $this->buildExtractionPrompt($ocrData, $rawText);

            // Call Ollama API
            $response = $this->callOllama($prompt);

            // Parse AI response
            $aiData = $this->parseOllamaResponse($response);

            // Merge OCR data with AI enhancements
            $enhanced = $this->mergeData($ocrData, $aiData);

            // Validate and score confidence
            $enhanced['ai_confidence'] = $this->calculateAIConfidence($enhanced, $ocrData);
            $enhanced['ai_suggestions'] = $aiData['suggestions'] ?? [];

            return $enhanced;

        } catch (\Exception $e) {
            Log::warning('Ollama enhancement failed', [
                'error' => $e->getMessage(),
                'fallback' => 'Using OCR data only',
            ]);

            // Return original OCR data if AI fails
            $ocrData['ai_confidence'] = 0.5;
            $ocrData['ai_suggestions'] = [];
            return $ocrData;
        }
    }

    /**
     * Build extraction prompt for Ollama
     */
    protected function buildExtractionPrompt(array $ocrData, string $rawText): string
    {
        $json = json_encode($ocrData, JSON_PRETTY_PRINT);

        return <<<PROMPT
Vous êtes un assistant comptable belge expert en extraction de données de factures.

**Texte OCR brut:**
{$rawText}

**Données déjà extraites (par OCR classique):**
{$json}

**Votre mission:**
1. Valider et corriger les données extraites si nécessaire
2. Extraire les informations manquantes ou peu claires
3. Identifier le type de facture (achat/vente)
4. Suggérer le compte comptable approprié (PCMN belge)
5. Détecter d'éventuelles anomalies

**Format de réponse (JSON strict):**
```json
{
    "invoice_number": "corrigé si nécessaire",
    "invoice_date": "YYYY-MM-DD",
    "due_date": "YYYY-MM-DD",
    "supplier_name": "nom exact du fournisseur",
    "vat_number": "BE0123456789 format",
    "iban": "BExx xxxx xxxx xxxx format",
    "total_excl_vat": 0.00,
    "total_vat": 0.00,
    "total_incl_vat": 0.00,
    "currency": "EUR",
    "line_items": [
        {
            "description": "description du produit/service",
            "quantity": 1,
            "unit_price": 0.00,
            "vat_rate": 21,
            "amount": 0.00,
            "suggested_account": "code PCMN (ex: 600000)",
            "suggested_category": "Achats / Services / etc"
        }
    ],
    "payment_terms": "nombre de jours (ex: 30)",
    "invoice_type": "purchase ou sale",
    "anomalies": ["liste des anomalies détectées"],
    "suggestions": ["suggestions d'amélioration"],
    "confidence": 0.95
}
```

Répondez UNIQUEMENT avec le JSON, sans texte additionnel.
PROMPT;
    }

    /**
     * Call Ollama API
     */
    protected function callOllama(string $prompt): array
    {
        $response = Http::timeout($this->timeout)
            ->post("{$this->ollamaEndpoint}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.3, // Low temperature for factual extraction
                    'top_p' => 0.9,
                    'num_predict' => 2048,
                ],
            ]);

        if (!$response->successful()) {
            throw new \Exception("Ollama API failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Parse Ollama JSON response
     */
    protected function parseOllamaResponse(array $response): array
    {
        $text = $response['response'] ?? '';

        // Extract JSON from response (might be wrapped in markdown)
        if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/{.*}/s', $text, $matches)) {
            $json = $matches[0];
        } else {
            $json = $text;
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Ollama returned invalid JSON', [
                'response' => $text,
                'error' => json_last_error_msg(),
            ]);
            return [];
        }

        return $data;
    }

    /**
     * Merge OCR data with AI enhancements
     */
    protected function mergeData(array $ocrData, array $aiData): array
    {
        $merged = $ocrData;

        // For each field, prefer AI data if confidence is high
        foreach ($aiData as $key => $value) {
            if ($key === 'confidence' || $key === 'suggestions' || $key === 'anomalies') {
                $merged[$key] = $value;
                continue;
            }

            // Skip if AI data is empty
            if (empty($value)) {
                continue;
            }

            // For line items, merge intelligently
            if ($key === 'line_items' && is_array($value)) {
                $merged[$key] = $this->mergeLineItems($ocrData[$key] ?? [], $value);
                continue;
            }

            // For simple fields, use AI data if more complete
            if (!isset($merged[$key]) || empty($merged[$key])) {
                $merged[$key] = $value;
            } elseif (is_array($merged[$key]) && isset($merged[$key]['confidence'])) {
                // If OCR has low confidence, prefer AI
                if ($merged[$key]['confidence'] < 0.7 && ($aiData['confidence'] ?? 0) > 0.7) {
                    $merged[$key] = ['value' => $value, 'confidence' => $aiData['confidence'], 'source' => 'ai'];
                }
            }
        }

        return $merged;
    }

    /**
     * Merge line items from OCR and AI
     */
    protected function mergeLineItems(array $ocrItems, array $aiItems): array
    {
        // If OCR has no items, use AI items
        if (empty($ocrItems)) {
            return $aiItems;
        }

        // If AI has more complete items, prefer AI
        if (count($aiItems) > count($ocrItems)) {
            return $aiItems;
        }

        // Otherwise merge both (add suggested_account and category from AI)
        $merged = [];
        for ($i = 0; $i < count($ocrItems); $i++) {
            $merged[$i] = $ocrItems[$i];

            if (isset($aiItems[$i])) {
                $merged[$i]['suggested_account'] = $aiItems[$i]['suggested_account'] ?? null;
                $merged[$i]['suggested_category'] = $aiItems[$i]['suggested_category'] ?? null;

                // Fix description if AI has better one
                if (!empty($aiItems[$i]['description']) && strlen($aiItems[$i]['description']) > 5) {
                    $merged[$i]['description'] = $aiItems[$i]['description'];
                }
            }
        }

        return $merged;
    }

    /**
     * Calculate overall AI confidence score
     */
    protected function calculateAIConfidence(array $enhanced, array $original): float
    {
        $scores = [];

        // Check if critical fields were enhanced
        $criticalFields = ['invoice_number', 'invoice_date', 'supplier_name', 'vat_number', 'total_incl_vat'];

        foreach ($criticalFields as $field) {
            if (isset($enhanced[$field]) && !empty($enhanced[$field])) {
                $scores[] = 1.0;
            } else {
                $scores[] = 0.5;
            }
        }

        // Check line items quality
        if (isset($enhanced['line_items']) && count($enhanced['line_items']) > 0) {
            $lineItemScore = 0;
            foreach ($enhanced['line_items'] as $item) {
                if (isset($item['suggested_account'])) {
                    $lineItemScore += 0.2;
                }
            }
            $scores[] = min(1.0, $lineItemScore);
        }

        // Use AI's own confidence if available
        if (isset($enhanced['confidence'])) {
            $scores[] = $enhanced['confidence'];
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Match supplier with existing partners using AI
     */
    public function matchSupplier(array $supplierData): ?Partner
    {
        // Try exact VAT match first
        if (isset($supplierData['vat_number']['value'])) {
            $vat = $supplierData['vat_number']['value'];
            $partner = Partner::where('vat_number', $vat)->first();
            if ($partner) {
                return $partner;
            }
        }

        // Try fuzzy name matching with AI
        if (isset($supplierData['supplier']['name'])) {
            $name = $supplierData['supplier']['name'];

            // Get all suppliers and use Ollama for similarity matching
            $partners = Partner::where('partner_type', 'supplier')
                ->orWhere('partner_type', 'both')
                ->limit(50)
                ->get();

            if ($partners->isEmpty()) {
                return null;
            }

            return $this->findBestMatch($name, $partners);
        }

        return null;
    }

    /**
     * Find best matching partner using Ollama
     */
    protected function findBestMatch(string $searchName, $partners): ?Partner
    {
        $cacheKey = "supplier_match:" . md5($searchName);

        return Cache::remember($cacheKey, 3600, function () use ($searchName, $partners) {
            $partnersList = $partners->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'vat' => $p->vat_number,
            ])->toArray();

            $prompt = <<<PROMPT
Tu dois identifier le meilleur match entre le nom de fournisseur "{$searchName}" et cette liste:

{$this->formatPartnersForPrompt($partnersList)}

Réponds UNIQUEMENT avec l'ID du meilleur match, ou "null" si aucun match évident.
PROMPT;

            try {
                $response = $this->callOllama($prompt);
                $idString = trim($response['response'] ?? '');

                if ($idString === 'null' || empty($idString)) {
                    return null;
                }

                $id = filter_var($idString, FILTER_VALIDATE_INT);
                return $partners->firstWhere('id', $id);

            } catch (\Exception $e) {
                // Fallback to simple string matching
                return $this->simpleFuzzyMatch($searchName, $partners);
            }
        });
    }

    /**
     * Format partners list for AI prompt
     */
    protected function formatPartnersForPrompt(array $partners): string
    {
        $lines = [];
        foreach ($partners as $p) {
            $lines[] = "ID {$p['id']}: {$p['name']} ({$p['vat']})";
        }
        return implode("\n", $lines);
    }

    /**
     * Simple fuzzy string matching fallback
     */
    protected function simpleFuzzyMatch(string $search, $partners): ?Partner
    {
        $search = strtolower($search);
        $bestMatch = null;
        $bestScore = 0;

        foreach ($partners as $partner) {
            similar_text($search, strtolower($partner->name), $score);

            if ($score > $bestScore && $score > 70) {
                $bestScore = $score;
                $bestMatch = $partner;
            }
        }

        return $bestMatch;
    }

    /**
     * Detect duplicate invoices using AI
     */
    public function detectDuplicate(array $invoiceData): ?array
    {
        // Check for exact duplicate by number
        if (isset($invoiceData['invoice_number'])) {
            $existing = Invoice::where('invoice_number', $invoiceData['invoice_number'])
                ->where('company_id', auth()->user()->current_company_id)
                ->first();

            if ($existing) {
                return [
                    'duplicate' => true,
                    'confidence' => 0.95,
                    'existing_invoice_id' => $existing->id,
                    'match_type' => 'exact_number',
                ];
            }
        }

        // Check for similar invoices (same supplier, similar amount, similar date)
        $similarInvoices = $this->findSimilarInvoices($invoiceData);

        if ($similarInvoices->isNotEmpty()) {
            // Use AI to confirm if it's a duplicate
            return $this->aiDuplicateCheck($invoiceData, $similarInvoices);
        }

        return ['duplicate' => false, 'confidence' => 0.9];
    }

    /**
     * Find similar invoices in database
     */
    protected function findSimilarInvoices(array $data)
    {
        $query = Invoice::where('company_id', auth()->user()->current_company_id);

        // Similar amount (±5%)
        if (isset($data['total_incl_vat'])) {
            $amount = $data['total_incl_vat'];
            $query->whereBetween('total_incl_vat', [$amount * 0.95, $amount * 1.05]);
        }

        // Similar date (±7 days)
        if (isset($data['invoice_date'])) {
            $date = $data['invoice_date'];
            $query->whereBetween('invoice_date', [
                \Carbon\Carbon::parse($date)->subDays(7),
                \Carbon\Carbon::parse($date)->addDays(7),
            ]);
        }

        return $query->limit(5)->get();
    }

    /**
     * Use AI to confirm duplicate
     */
    protected function aiDuplicateCheck(array $newInvoice, $existingInvoices): array
    {
        $prompt = <<<PROMPT
Compare cette nouvelle facture avec les factures existantes et détermine s'il s'agit d'un doublon.

Nouvelle facture:
{$this->formatInvoiceForPrompt($newInvoice)}

Factures existantes:
{$this->formatExistingInvoices($existingInvoices)}

Réponds en JSON:
{
    "is_duplicate": true/false,
    "confidence": 0.0-1.0,
    "matching_invoice_id": "id ou null",
    "reason": "explication"
}
PROMPT;

        try {
            $response = $this->callOllama($prompt);
            $result = $this->parseOllamaResponse($response);

            return [
                'duplicate' => $result['is_duplicate'] ?? false,
                'confidence' => $result['confidence'] ?? 0.5,
                'existing_invoice_id' => $result['matching_invoice_id'] ?? null,
                'reason' => $result['reason'] ?? '',
                'match_type' => 'ai_similarity',
            ];

        } catch (\Exception $e) {
            return ['duplicate' => false, 'confidence' => 0.5];
        }
    }

    /**
     * Format invoice for AI prompt
     */
    protected function formatInvoiceForPrompt(array $invoice): string
    {
        return json_encode([
            'number' => $invoice['invoice_number'] ?? 'N/A',
            'date' => $invoice['invoice_date'] ?? 'N/A',
            'supplier' => $invoice['supplier_name'] ?? 'N/A',
            'amount' => $invoice['total_incl_vat'] ?? 0,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Format existing invoices for AI
     */
    protected function formatExistingInvoices($invoices): string
    {
        return $invoices->map(fn($inv) => [
            'id' => $inv->id,
            'number' => $inv->invoice_number,
            'date' => $inv->invoice_date?->format('Y-m-d'),
            'supplier' => $inv->partner?->name,
            'amount' => $inv->total_incl_vat,
        ])->toJson(JSON_PRETTY_PRINT);
    }
}
