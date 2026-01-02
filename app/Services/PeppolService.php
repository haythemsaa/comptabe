<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Company;
use App\Models\PeppolUsage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeppolService
{
    protected string $provider;
    protected string $plan;
    protected array $config;
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;
    protected bool $testMode;

    public function __construct()
    {
        // Utiliser les paramètres GLOBAUX du superadmin
        $this->provider = $this->getGlobalSetting('peppol_global_provider', 'recommand');
        $this->plan = $this->getGlobalSetting('peppol_global_plan', 'free');
        $this->apiKey = $this->getGlobalSetting('peppol_global_api_key', '');
        $this->apiSecret = $this->getGlobalSetting('peppol_global_api_secret', '');
        $this->testMode = (bool) $this->getGlobalSetting('peppol_global_test_mode', true);

        // Configuration du provider
        $providers = config('peppol_plans.providers', []);
        $this->config = $providers[$this->provider] ?? [];
        $this->apiUrl = $this->config['url'] ?? '';
    }

    /**
     * Obtenir un paramètre global système
     */
    protected function getGlobalSetting(string $key, $default = null)
    {
        try {
            return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = DB::table('system_settings')->where('key', $key)->first();
                if (!$setting) {
                    return $default;
                }
                return $this->castValue($setting->value, $setting->type);
            });
        } catch (\Exception $e) {
            // If table doesn't exist (during migrations), return default
            return $default;
        }
    }

    /**
     * Caster une valeur selon son type
     */
    protected function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Send an invoice via Peppol
     */
    public function sendInvoice(Invoice $invoice): array
    {
        try {
            $company = $invoice->company;

            // 1. VÉRIFIER LE QUOTA
            if (!$company->hasPeppolQuota()) {
                $planDetails = $company->getPeppolPlanDetails();
                $planName = $planDetails['name'] ?? $company->peppol_plan;

                // Logger l'échec
                PeppolUsage::logFailed(
                    companyId: $company->id,
                    action: 'send',
                    errorMessage: "Quota dépassé ({$company->peppol_usage_current_month}/{$company->peppol_quota_monthly})",
                    invoiceId: $invoice->id
                );

                return [
                    'success' => false,
                    'error' => "Quota Peppol dépassé ce mois ({$company->peppol_usage_current_month}/{$company->peppol_quota_monthly}). Veuillez upgrader votre plan ({$planName}).",
                    'quota_exceeded' => true,
                    'current_plan' => $company->peppol_plan,
                    'usage' => $company->peppol_usage_current_month,
                    'quota' => $company->peppol_quota_monthly,
                ];
            }

            // 2. VÉRIFIER SI PEPPOL EST ACTIVÉ GLOBALEMENT
            $peppolEnabled = (bool) $this->getGlobalSetting('peppol_enabled', true);
            if (!$peppolEnabled) {
                return [
                    'success' => false,
                    'error' => 'Peppol est temporairement désactivé. Contactez le support.',
                ];
            }

            // 3. VÉRIFIER SI API KEY EST CONFIGURÉE
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'API Peppol non configurée. Contactez l\'administrateur.',
                ];
            }

            // 4. GÉNÉRER UBL XML
            $ublService = new UblService();
            $ublXml = $ublService->generateInvoiceUbl($invoice);

            // 5. PRÉPARER RECIPIENT ID
            $recipientId = $this->formatParticipantId(
                $invoice->partner->peppol_id ?? $invoice->partner->vat_number
            );

            // 6. ENVOYER VIA PROVIDER
            $result = match($this->provider) {
                'recommand' => $this->sendViaRecommand($ublXml, $recipientId, $invoice),
                'digiteal' => $this->sendViaDigiteal($ublXml, $recipientId, $invoice),
                'peppol_box' => $this->sendViaPeppolBox($ublXml, $recipientId, $invoice),
                default => throw new \Exception("Unsupported Peppol provider: {$this->provider}"),
            };

            // 7. SI SUCCÈS, LOGGER ET INCRÉMENTER QUOTA
            if ($result['success']) {
                // Calculer le coût de cette transaction
                $cost = $this->calculateTransactionCost();

                // Logger dans peppol_usage
                PeppolUsage::logSend(
                    companyId: $company->id,
                    invoiceId: $invoice->id,
                    transmissionId: $result['transmission_id'] ?? '',
                    participantId: $recipientId,
                    documentType: 'invoice',
                    cost: $cost
                );

                // Incrémenter le compteur d'usage
                $company->incrementPeppolUsage();

                // Ajouter infos quota dans la réponse
                $result['quota_info'] = [
                    'used' => $company->peppol_usage_current_month,
                    'total' => $company->peppol_quota_monthly,
                    'remaining' => $company->getRemainingPeppolQuota(),
                    'percentage' => $company->getPeppolQuotaPercentage(),
                ];
            } else {
                // Logger l'échec
                PeppolUsage::logFailed(
                    companyId: $company->id,
                    action: 'send',
                    errorMessage: $result['error'] ?? 'Unknown error',
                    invoiceId: $invoice->id,
                    participantId: $recipientId
                );
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Peppol send failed', [
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ]);

            // Logger l'échec
            if (isset($invoice->company)) {
                PeppolUsage::logFailed(
                    companyId: $invoice->company_id,
                    action: 'send',
                    errorMessage: $e->getMessage(),
                    invoiceId: $invoice->id
                );
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer le coût de la transaction selon le plan provider
     */
    protected function calculateTransactionCost(): float
    {
        $plans = config('peppol_plans.providers', []);
        $providerPlans = $plans[$this->provider]['plans'] ?? [];
        $currentPlan = $providerPlans[$this->plan] ?? null;

        if (!$currentPlan) {
            return 0;
        }

        // Coût par document (overage cost si on dépasse le quota inclus)
        return $currentPlan['overage_cost'] ?? 0.10;
    }

    /**
     * Send invoice via Recommand.eu
     */
    protected function sendViaRecommand(string $ublXml, string $recipientId, Invoice $invoice): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(config('peppol.timeout', 30))
          ->post("{$this->apiUrl}/documents/send", [
            'document' => base64_encode($ublXml),
            'document_type' => 'invoice',
            'format' => 'ubl',
            'sender_id' => $this->getParticipantId(),
            'recipient_id' => $recipientId,
            'metadata' => [
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('Y-m-d'),
                'total_amount' => $invoice->total_amount,
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'transmission_id' => $data['transmission_id'] ?? null,
                'status' => $data['status'] ?? 'sent',
                'message' => 'Invoice sent successfully via Peppol (Recommand.eu)',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Unknown error',
            'status_code' => $response->status(),
        ];
    }

    /**
     * Send invoice via Digiteal
     */
    protected function sendViaDigiteal(string $ublXml, string $recipientId, Invoice $invoice): array
    {
        $token = $this->getDigitealToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->timeout(config('peppol.timeout', 30))
          ->post("{$this->apiUrl}/send", [
            'document_content' => base64_encode($ublXml),
            'document_format' => 'UBL',
            'sender_identifier' => $this->getParticipantId(),
            'receiver_identifier' => $recipientId,
            'document_id' => $invoice->invoice_number,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'transmission_id' => $data['message_id'] ?? null,
                'status' => $data['status'] ?? 'sent',
                'message' => 'Invoice sent successfully via Peppol (Digiteal)',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['error'] ?? 'Unknown error',
            'status_code' => $response->status(),
        ];
    }

    /**
     * Send invoice via B2Brouter
     */
    protected function sendViaB2Brouter(string $ublXml, string $recipientId, Invoice $invoice): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/xml',
        ])->timeout(config('peppol.timeout', 30))
          ->post("{$this->apiUrl}/peppol/send", [
            'xml_content' => $ublXml,
            'from' => $this->getParticipantId(),
            'to' => $recipientId,
            'document_type' => 'invoice',
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'transmission_id' => $data['transmission_uuid'] ?? null,
                'status' => 'sent',
                'message' => 'Invoice sent successfully via Peppol (B2Brouter)',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Unknown error',
            'status_code' => $response->status(),
        ];
    }

    /**
     * Verify if a participant ID is registered on Peppol
     */
    public function verifyParticipant(string $participantId): array
    {
        try {
            $formattedId = $this->formatParticipantId($participantId);

            return match($this->provider) {
                'recommand' => $this->verifyParticipantRecommand($formattedId),
                'digiteal' => $this->verifyParticipantDigiteal($formattedId),
                'b2brouter' => $this->verifyParticipantB2Brouter($formattedId),
                default => ['exists' => false, 'error' => 'Provider not configured'],
            };

        } catch (\Exception $e) {
            Log::error('Peppol participant verification failed', [
                'participant_id' => $participantId,
                'error' => $e->getMessage(),
            ]);

            return [
                'exists' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify participant via Recommand.eu
     */
    protected function verifyParticipantRecommand(string $participantId): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/participants/{$participantId}");

        if ($response->successful()) {
            $data = $response->json();

            return [
                'exists' => true,
                'participant_id' => $data['participant_id'] ?? $participantId,
                'name' => $data['name'] ?? null,
                'capabilities' => $data['capabilities'] ?? [],
            ];
        }

        return [
            'exists' => false,
            'error' => $response->json()['message'] ?? 'Participant not found',
        ];
    }

    /**
     * Verify participant via Digiteal
     */
    protected function verifyParticipantDigiteal(string $participantId): array
    {
        $token = $this->getDigitealToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/lookup/{$participantId}");

        if ($response->successful()) {
            $data = $response->json();

            return [
                'exists' => $data['registered'] ?? false,
                'participant_id' => $participantId,
                'name' => $data['participant_name'] ?? null,
            ];
        }

        return ['exists' => false];
    }

    /**
     * Verify participant via B2Brouter
     */
    protected function verifyParticipantB2Brouter(string $participantId): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/peppol/lookup", [
            'participant_id' => $participantId,
        ]);

        if ($response->successful() && $response->json()['found'] ?? false) {
            return [
                'exists' => true,
                'participant_id' => $participantId,
            ];
        }

        return ['exists' => false];
    }

    /**
     * Search for participants in Peppol directory
     */
    public function searchParticipants(string $query): array
    {
        try {
            return match($this->provider) {
                'recommand' => $this->searchRecommand($query),
                'digiteal' => $this->searchDigiteal($query),
                default => [],
            };

        } catch (\Exception $e) {
            Log::error('Peppol search failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Search via Recommand.eu
     */
    protected function searchRecommand(string $query): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/participants/search", [
            'q' => $query,
            'country' => 'BE',
        ]);

        if ($response->successful()) {
            return $response->json()['results'] ?? [];
        }

        return [];
    }

    /**
     * Search via Digiteal
     */
    protected function searchDigiteal(string $query): array
    {
        $token = $this->getDigitealToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/directory/search", [
            'name' => $query,
        ]);

        if ($response->successful()) {
            return $response->json()['participants'] ?? [];
        }

        return [];
    }

    /**
     * Get transmission status
     */
    public function getTransmissionStatus(string $transmissionId): array
    {
        try {
            return match($this->provider) {
                'recommand' => $this->getStatusRecommand($transmissionId),
                'digiteal' => $this->getStatusDigiteal($transmissionId),
                'b2brouter' => $this->getStatusB2Brouter($transmissionId),
                default => ['status' => 'unknown'],
            };

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get status via Recommand.eu
     */
    protected function getStatusRecommand(string $transmissionId): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/transmissions/{$transmissionId}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['status' => 'unknown'];
    }

    /**
     * Get status via Digiteal
     */
    protected function getStatusDigiteal(string $transmissionId): array
    {
        $token = $this->getDigitealToken();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/messages/{$transmissionId}/status");

        if ($response->successful()) {
            return $response->json();
        }

        return ['status' => 'unknown'];
    }

    /**
     * Get status via B2Brouter
     */
    protected function getStatusB2Brouter(string $transmissionId): array
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->timeout(config('peppol.timeout', 30))
          ->get("{$this->apiUrl}/peppol/status/{$transmissionId}");

        if ($response->successful()) {
            return $response->json();
        }

        return ['status' => 'unknown'];
    }

    /**
     * Get OAuth token for Digiteal
     */
    protected function getDigitealToken(): string
    {
        // Use API key directly if no OAuth is configured
        if (!isset($this->config['client_id']) || !isset($this->config['client_secret'])) {
            return $this->apiKey;
        }

        // Cache the token
        $cacheKey = "peppol_digiteal_token";

        return cache()->remember($cacheKey, 3600, function () {
            $response = Http::asForm()->post("{$this->apiUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            return $this->apiKey;
        });
    }

    /**
     * Format participant ID (scheme::identifier)
     */
    protected function formatParticipantId(string $identifier): string
    {
        // If already formatted, return as-is
        if (str_contains($identifier, ':')) {
            return $identifier;
        }

        // Remove 'BE' prefix from VAT number if present
        $identifier = str_replace('BE', '', $identifier);

        // Format as scheme::identifier (0208 is Belgian VAT)
        $scheme = config('peppol.scheme', '0208');

        return "{$scheme}:{$identifier}";
    }

    /**
     * Get current company's participant ID
     */
    protected function getParticipantId(): string
    {
        $company = Company::current();

        // Use configured participant ID or format from VAT
        return $company->peppol_participant_id
            ?? $this->formatParticipantId($company->vat_number);
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return $this->config['name'] ?? ucfirst($this->provider);
    }

    /**
     * Get provider features
     */
    public function getProviderFeatures(): array
    {
        return $this->config['features'] ?? [];
    }

    /**
     * Check if provider supports a feature
     */
    public function supportsFeature(string $feature): bool
    {
        return $this->config['features'][$feature] ?? false;
    }

    /**
     * Test connection to Peppol provider
     */
    public function testConnection(): array
    {
        try {
            // Try to verify our own participant ID
            $participantId = $this->getParticipantId();
            $result = $this->verifyParticipant($participantId);

            return [
                'success' => true,
                'provider' => $this->getProviderName(),
                'participant_id' => $participantId,
                'registered' => $result['exists'] ?? false,
                'message' => $result['exists']
                    ? 'Successfully connected to Peppol network'
                    : 'Connected to provider, but participant ID not registered',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ===========================================================================
    // Static helper methods (kept for backwards compatibility)
    // ===========================================================================

    /**
     * Get available Peppol Access Point providers (legacy)
     */
    public static function getProviders(): array
    {
        return array_merge(config('peppol.providers', []), [
            'basware' => [
                'name' => 'Basware',
                'description' => 'Global e-invoicing network',
                'countries' => ['be', 'nl', 'fr', 'de', 'lu'],
            ],
            'unifiedpost' => [
                'name' => 'Unifiedpost',
                'description' => 'Leader BeNeLux en e-invoicing',
                'countries' => ['be', 'nl', 'lu'],
            ],
        ]);
    }

    /**
     * Get Belgian Peppol scheme identifiers
     */
    public static function getSchemes(): array
    {
        return [
            '0208' => 'Numero d\'entreprise belge (BCE)',
            '0009' => 'SIRET francais',
            '0106' => 'Numero TVA intracommunautaire',
            '0190' => 'Numero DUNS',
            '0088' => 'EAN/GLN',
        ];
    }

    /**
     * Validate a Belgian Peppol identifier
     */
    public static function validateBelgianIdentifier(string $identifier): bool
    {
        if (preg_match('/^0208:(\d{10})$/', $identifier, $matches)) {
            $number = $matches[1];
            $base = intval(substr($number, 0, 8));
            $checksum = intval(substr($number, 8, 2));
            return (97 - ($base % 97)) === $checksum;
        }
        return false;
    }

    /**
     * Format enterprise number to Peppol identifier
     */
    public static function formatToPeppolId(string $vatNumber): string
    {
        $clean = preg_replace('/[^0-9]/', '', $vatNumber);

        if (strlen($clean) === 9) {
            $clean = '0' . $clean;
        }

        if (strlen($clean) !== 10) {
            return '';
        }

        return '0208:' . $clean;
    }

    /**
     * Get document types supported by Peppol BIS 3.0
     */
    public static function getSupportedDocumentTypes(): array
    {
        return [
            'invoice' => [
                'id' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
                'name' => 'Facture',
                'customization' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            ],
            'credit_note' => [
                'id' => 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2',
                'name' => 'Note de credit',
                'customization' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            ],
        ];
    }
}
