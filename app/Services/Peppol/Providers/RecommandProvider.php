<?php

namespace App\Services\Peppol\Providers;

use App\Models\Company;
use App\Models\PeppolTransmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommandProvider implements PeppolProviderInterface
{
    protected Company $company;
    protected bool $testMode;
    protected string $apiBaseUrl;

    public function __construct(Company $company, bool $testMode = true)
    {
        $this->company = $company;
        $this->testMode = $testMode;

        // Recommand.eu uses same base URL for both test and production
        // Playground teams don't send over real Peppol network
        $this->apiBaseUrl = 'https://peppol.recommand.eu/api/peppol';
    }

    /**
     * Convert country name to ISO 3166-1 Alpha-2 code.
     */
    protected function getCountryCode(?string $country): string
    {
        if (!$country) {
            return 'BE';
        }

        // Already a 2-letter code
        if (strlen($country) === 2) {
            return strtoupper($country);
        }

        // Convert country name to ISO code
        $countryMap = [
            'belgique' => 'BE',
            'belgium' => 'BE',
            'belgië' => 'BE',
            'france' => 'FR',
            'nederland' => 'NL',
            'netherlands' => 'NL',
            'pays-bas' => 'NL',
            'deutschland' => 'DE',
            'germany' => 'DE',
            'allemagne' => 'DE',
        ];

        $normalized = strtolower(trim($country));
        return strtoupper($countryMap[$normalized] ?? 'BE');
    }

    /**
     * Format VAT number with country prefix (ISO 3166-1 Alpha-2).
     * Ensures PEPPOL compliance: BR-CO-09, PEPPOL-COMMON-R043
     */
    protected function formatVatNumber(?string $vatNumber, string $countryCode): string
    {
        if (!$vatNumber) {
            return '';
        }

        // Remove spaces and common separators
        $cleaned = preg_replace('/[\s\-\.]/', '', $vatNumber);

        // If already has country prefix (2 letters at start), return as-is (uppercased)
        if (preg_match('/^[A-Z]{2}/i', $cleaned)) {
            return strtoupper($cleaned);
        }

        // Add country prefix
        return strtoupper($countryCode . $cleaned);
    }

    /**
     * Format PEPPOL Participant ID for Belgian enterprises (Recommand.eu specific).
     *
     * Uses scheme 9925 (Belgian VAT) with country prefix:
     * - Scheme: 9925 (Belgian VAT number scheme)
     * - Identifier: VAT number WITH "BE" prefix
     * - Example: 9925:BE0123456789
     *
     * Alternative scheme 0208 (CBE/BCE) is also supported.
     */
    protected function formatPeppolId(?string $peppolId, ?string $vatNumber, string $countryCode): string
    {
        if (!$peppolId && !$vatNumber) {
            return '';
        }

        // If no Peppol ID, construct from VAT number
        if (!$peppolId) {
            // For Belgium, use scheme 9925 (Belgian VAT) with BE prefix
            if ($countryCode === 'BE') {
                $formattedVat = $this->formatVatNumber($vatNumber, $countryCode);
                return '9925:' . $formattedVat;
            }

            // For other countries, use formatted VAT number with scheme 9925
            $formattedVat = $this->formatVatNumber($vatNumber, $countryCode);
            return '9925:' . $formattedVat;
        }

        // Parse existing Peppol ID
        if (str_contains($peppolId, ':')) {
            [$scheme, $identifier] = explode(':', $peppolId, 2);

            Log::info('formatPeppolId - Before', [
                'original' => $peppolId,
                'scheme' => $scheme,
                'identifier_before' => $identifier,
                'country' => $countryCode,
            ]);

            // Remove spaces from identifier
            $identifier = preg_replace('/[\s\-\.]/', '', $identifier);

            // For Belgian schemes (0208 or 9925), KEEP the country prefix
            if (($scheme === '0208' || $scheme === '9925') && $countryCode === 'BE') {
                // Ensure BE prefix is present
                if (!preg_match('/^BE/i', $identifier)) {
                    $identifier = 'BE' . $identifier;
                }
            }

            $result = $scheme . ':' . strtoupper($identifier);

            Log::info('formatPeppolId - After', [
                'identifier_after' => $identifier,
                'result' => $result,
            ]);

            return $result;
        }

        // Invalid format, return as-is
        return $peppolId;
    }

    /**
     * Convert Laravel Invoice to Recommand.eu JSON format.
     */
    protected function convertInvoiceToRecommandFormat($invoice): array
    {
        $sellerCountry = $invoice->company->country ?? 'BE';
        $buyerCountry = $this->getCountryCode($invoice->partner->country);

        // Calculate totals with proper rounding for PEPPOL compliance (BR-CO-17)
        $totalExclVat = round((float) $invoice->total_excl_vat, 2);
        $totalVat = round((float) $invoice->total_vat, 2);
        $totalInclVat = round((float) $invoice->total_incl_vat, 2);

        return [
            'invoiceNumber' => $invoice->invoice_number,
            'issueDate' => $invoice->invoice_date->format('Y-m-d'),
            'dueDate' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
            'currency' => $invoice->currency ?? 'EUR',

            'seller' => [
                'name' => $invoice->company->name,
                'street' => $invoice->company->address ?? 'Rue de merode 288',
                'city' => $invoice->company->city ?? 'Forest',
                'postalZone' => $invoice->company->postal_code ?? '1190',
                'country' => $sellerCountry,
                'vatNumber' => $this->formatVatNumber($invoice->company->vat_number, $sellerCountry),
                'peppolId' => $this->formatPeppolId($invoice->company->peppol_id, $invoice->company->vat_number, $sellerCountry),
            ],

            'buyer' => [
                'name' => $invoice->partner->name,
                'street' => $invoice->partner->address ?? 'Unknown',
                'city' => $invoice->partner->city ?? 'Unknown',
                'postalZone' => $invoice->partner->postal_code ?? '0000',
                'country' => $buyerCountry,
                'vatNumber' => $this->formatVatNumber($invoice->partner->vat_number, $buyerCountry),
                'peppolId' => $this->formatPeppolId($invoice->partner->peppol_id, $invoice->partner->vat_number, $buyerCountry),
            ],

            'lines' => $invoice->lines->map(function ($line) {
                $itemName = $line->label ?? $line->description ?? 'Service';
                $description = $line->description ?? $line->label ?? '';
                $quantity = round((float) $line->quantity, 2);
                $unitPrice = round((float) $line->unit_price, 2);
                $vatRate = (float) ($line->vat_rate ?? 21);

                return [
                    'id' => (string) $line->id,
                    'name' => $itemName, // Item name (required)
                    'description' => $description,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'vat' => [
                        'percentage' => round($vatRate, 2), // Send as 21 (not 0.21) - Recommand.eu format
                        'category' => 'S', // S = Standard rate
                    ],
                    'netPriceAmount' => $unitPrice,
                ];
            })->toArray(),

            'totalExcludingVat' => $totalExclVat,
            'totalVat' => $totalVat,
            'totalIncludingVat' => $totalInclVat,
        ];
    }

    /**
     * Send invoice via Recommand.eu Peppol API.
     */
    public function sendInvoice(string $ublXml, PeppolTransmission $transmission): array
    {
        // Recommand.eu requires Company/Team ID in the URL
        $companyId = $this->company->peppol_company_id;

        if (!$companyId) {
            throw new \Exception('Recommand.eu Company ID not configured. Please set peppol_company_id in your company settings.');
        }

        // Get the invoice model from transmission
        $invoice = $transmission->invoice;
        if (!$invoice) {
            throw new \Exception('Invoice not found for transmission');
        }

        // Convert invoice to Recommand.eu JSON format
        $invoiceData = $this->convertInvoiceToRecommandFormat($invoice);

        // Log the FULL invoice data being sent for debugging
        Log::info('Recommand.eu FULL invoice data', [
            'invoice_data' => $invoiceData,
        ]);

        // Get recipient Peppol ID from transmission and format it correctly
        $buyerCountry = $this->getCountryCode($invoice->partner->country);
        $recipientId = $this->formatPeppolId(
            $transmission->receiver_id,
            $invoice->partner->vat_number,
            $buyerCountry
        );

        // Recommand.eu API uses Basic Authentication (base64 encoded key:secret)
        $credentials = base64_encode(
            $this->company->peppol_api_key . ':' . $this->company->peppol_api_secret
        );

        try {
            $requestPayload = [
                'recipient' => $recipientId,
                'documentType' => 'invoice',
                'document' => $invoiceData,
            ];

            Log::info('Recommand.eu API REQUEST', [
                'url' => "{$this->apiBaseUrl}/{$companyId}/sendDocument",
                'recipient' => $recipientId,
                'document_invoice_number' => $invoiceData['invoiceNumber'] ?? null,
            ]);

            // Recommand.eu accepts UBL XML in the document field
            // Documentation: https://recommand.eu/en/docs
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiBaseUrl}/{$companyId}/sendDocument", $requestPayload);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Recommand.eu Peppol invoice sent', [
                    'company_id' => $this->company->id,
                    'transmission_id' => $transmission->id,
                    'recipient' => $recipientId,
                    'test_mode' => $this->testMode,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'provider' => 'recommand',
                    'test_mode' => $this->testMode,
                    'transmission_id' => $responseData['id'] ?? null,
                    'message_id' => $responseData['messageId'] ?? $transmission->message_id,
                    'status' => $responseData['status'] ?? 'sent',
                    'response_data' => $responseData,
                ];
            }

            // Handle API errors
            $errorBody = $response->body();
            $errorData = $response->json();

            Log::error('Recommand.eu Peppol API error', [
                'company_id' => $this->company->id,
                'transmission_id' => $transmission->id,
                'status_code' => $response->status(),
                'error' => $errorBody,
            ]);

            throw new \Exception(
                'Recommand.eu API error (HTTP ' . $response->status() . '): ' .
                ($errorData['message'] ?? $errorBody)
            );

        } catch (\Exception $e) {
            Log::error('Recommand.eu Peppol send exception', [
                'company_id' => $this->company->id,
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get provider name.
     */
    public function getName(): string
    {
        return 'recommand';
    }

    /**
     * Get provider display name.
     */
    public function getDisplayName(): string
    {
        return 'Recommand.eu';
    }

    /**
     * Check if provider is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->company->peppol_api_key) &&
               !empty($this->company->peppol_api_secret) &&
               !empty($this->company->peppol_company_id);
    }

    /**
     * Get configuration errors.
     */
    public function getConfigurationErrors(): array
    {
        $errors = [];

        if (empty($this->company->peppol_api_key)) {
            $errors[] = 'API Key manquante (peppol_api_key)';
        }

        if (empty($this->company->peppol_api_secret)) {
            $errors[] = 'API Secret manquant (peppol_api_secret)';
        }

        if (empty($this->company->peppol_company_id)) {
            $errors[] = 'Company ID manquant (peppol_company_id) - Disponible dans votre compte Recommand.eu';
        }

        return $errors;
    }
}
