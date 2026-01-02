<?php

namespace App\Services\Peppol;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeppolDirectoryService
{
    /**
     * Peppol SML domains for DNS lookup.
     */
    protected array $smlDomains = [
        'production' => 'edelivery.tech.ec.europa.eu',
        'test' => 'acc.edelivery.tech.ec.europa.eu',
    ];

    /**
     * Peppol Directory API endpoints.
     */
    protected array $directoryEndpoints = [
        'production' => 'https://directory.peppol.eu/search/1.0/json',
        'test' => 'https://test-directory.peppol.eu/search/1.0/json',
    ];

    /**
     * Participant identifier schemes.
     */
    protected array $schemes = [
        '0208' => 'Belgian Enterprise Number (KBO/BCE)',
        '9925' => 'Belgian VAT Number',
        '0088' => 'EAN/GLN',
        '0192' => 'Norwegian Organization Number',
        '0007' => 'Swedish Organization Number',
    ];

    protected bool $testMode;

    public function __construct(bool $testMode = false)
    {
        $this->testMode = $testMode;
    }

    /**
     * Look up a participant in the Peppol Directory.
     */
    public function lookup(string $identifier, string $scheme = '0208'): array
    {
        $cacheKey = "peppol_lookup_{$scheme}_{$identifier}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($identifier, $scheme) {
            // Try SMP lookup first (most reliable)
            $smpResult = $this->lookupViaSmp($identifier, $scheme);
            if ($smpResult['found']) {
                return $smpResult;
            }

            // Fallback to Directory API
            return $this->lookupViaDirectory($identifier, $scheme);
        });
    }

    /**
     * Check if a participant is registered in Peppol network.
     */
    public function isRegistered(string $identifier, string $scheme = '0208'): bool
    {
        $result = $this->lookup($identifier, $scheme);
        return $result['found'] ?? false;
    }

    /**
     * Look up participant via SMP DNS.
     *
     * The SMP lookup uses DNS to check if a participant is registered.
     * Format: B-<hash>.<scheme>.<sml-domain>
     */
    public function lookupViaSmp(string $identifier, string $scheme): array
    {
        try {
            // Clean identifier
            $cleanIdentifier = $this->cleanIdentifier($identifier);

            // Generate participant ID hash
            $participantId = $this->formatParticipantId($scheme, $cleanIdentifier);
            $hash = $this->hashParticipantId($participantId);

            // Construct DNS name
            $smlDomain = $this->testMode ? $this->smlDomains['test'] : $this->smlDomains['production'];
            $dnsName = "B-{$hash}.iso6523-actorid-upis.{$smlDomain}";

            // DNS lookup
            $records = @dns_get_record($dnsName, DNS_CNAME);

            if ($records && count($records) > 0) {
                // Found in SMP - get more details
                $smpUrl = $records[0]['target'] ?? null;

                return [
                    'found' => true,
                    'source' => 'smp',
                    'participant_id' => $participantId,
                    'smp_url' => $smpUrl,
                    'services' => $this->getSmpServices($smpUrl, $participantId),
                ];
            }

            return [
                'found' => false,
                'source' => 'smp',
                'message' => 'Participant not found in SMP',
            ];
        } catch (\Exception $e) {
            Log::warning('Peppol SMP lookup failed', [
                'identifier' => $identifier,
                'scheme' => $scheme,
                'error' => $e->getMessage(),
            ]);

            return [
                'found' => false,
                'source' => 'smp',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Look up participant via Peppol Directory API.
     */
    public function lookupViaDirectory(string $identifier, string $scheme): array
    {
        try {
            $cleanIdentifier = $this->cleanIdentifier($identifier);
            $endpoint = $this->testMode ? $this->directoryEndpoints['test'] : $this->directoryEndpoints['production'];

            $response = Http::timeout(10)->get($endpoint, [
                'participant' => "{$scheme}:{$cleanIdentifier}",
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['matches']) && count($data['matches']) > 0) {
                    $match = $data['matches'][0];

                    return [
                        'found' => true,
                        'source' => 'directory',
                        'participant_id' => "{$scheme}:{$cleanIdentifier}",
                        'name' => $match['name'] ?? null,
                        'country_code' => $match['countryCode'] ?? null,
                        'registration_date' => $match['registrationDate'] ?? null,
                        'document_types' => $match['docTypes'] ?? [],
                    ];
                }
            }

            return [
                'found' => false,
                'source' => 'directory',
                'message' => 'Participant not found in Peppol Directory',
            ];
        } catch (\Exception $e) {
            Log::warning('Peppol Directory lookup failed', [
                'identifier' => $identifier,
                'scheme' => $scheme,
                'error' => $e->getMessage(),
            ]);

            return [
                'found' => false,
                'source' => 'directory',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search Peppol Directory by name.
     */
    public function searchByName(string $name, ?string $countryCode = null): array
    {
        try {
            $endpoint = $this->testMode ? $this->directoryEndpoints['test'] : $this->directoryEndpoints['production'];

            $params = ['name' => $name];
            if ($countryCode) {
                $params['country'] = strtoupper($countryCode);
            }

            $response = Http::timeout(10)->get($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'total' => $data['total'] ?? 0,
                    'matches' => array_map(function ($match) {
                        return [
                            'participant_id' => $match['participantID'] ?? null,
                            'name' => $match['name'] ?? null,
                            'country_code' => $match['countryCode'] ?? null,
                            'registration_date' => $match['registrationDate'] ?? null,
                        ];
                    }, $data['matches'] ?? []),
                ];
            }

            return [
                'success' => false,
                'error' => 'Directory search failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get services available for a participant from SMP.
     */
    protected function getSmpServices(?string $smpUrl, string $participantId): array
    {
        if (!$smpUrl) {
            return [];
        }

        try {
            // Construct full SMP URL for service metadata
            $fullUrl = "https://{$smpUrl}/{$participantId}";

            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/xml'])
                ->get($fullUrl);

            if ($response->successful()) {
                // Parse XML response
                $xml = simplexml_load_string($response->body());
                $services = [];

                if ($xml && isset($xml->ServiceMetadataReferenceCollection)) {
                    foreach ($xml->ServiceMetadataReferenceCollection->ServiceMetadataReference as $ref) {
                        $services[] = (string) $ref['href'];
                    }
                }

                return $services;
            }
        } catch (\Exception $e) {
            Log::debug('Could not fetch SMP services', [
                'smp_url' => $smpUrl,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Clean identifier (remove spaces, dashes, etc).
     */
    protected function cleanIdentifier(string $identifier): string
    {
        // Remove common prefixes
        $identifier = preg_replace('/^(BE|NL|FR|DE|AT)/i', '', $identifier);

        // Remove non-alphanumeric characters
        return preg_replace('/[^a-zA-Z0-9]/', '', $identifier);
    }

    /**
     * Format participant ID.
     */
    protected function formatParticipantId(string $scheme, string $identifier): string
    {
        return "iso6523-actorid-upis::{$scheme}:{$identifier}";
    }

    /**
     * Hash participant ID for SML lookup.
     *
     * Uses MD5 hash encoded in lowercase hex as per Peppol specifications.
     */
    protected function hashParticipantId(string $participantId): string
    {
        return strtolower(md5(strtolower($participantId)));
    }

    /**
     * Verify a Belgian VAT number against Peppol.
     */
    public function verifyBelgianVat(string $vatNumber): array
    {
        // Clean and format VAT number
        $cleanVat = preg_replace('/[^0-9]/', '', $vatNumber);

        // Belgian enterprise numbers are 10 digits
        if (strlen($cleanVat) === 10) {
            // Try enterprise number scheme first (0208)
            $result = $this->lookup($cleanVat, '0208');

            if ($result['found']) {
                return array_merge($result, [
                    'scheme' => '0208',
                    'peppol_id' => "0208:{$cleanVat}",
                ]);
            }
        }

        // Try VAT scheme (9925)
        $vatWithPrefix = 'BE' . $cleanVat;
        $result = $this->lookup($vatWithPrefix, '9925');

        if ($result['found']) {
            return array_merge($result, [
                'scheme' => '9925',
                'peppol_id' => "9925:{$vatWithPrefix}",
            ]);
        }

        return [
            'found' => false,
            'message' => 'Entreprise non trouvee dans le reseau Peppol',
        ];
    }

    /**
     * Get supported schemes.
     */
    public function getSupportedSchemes(): array
    {
        return $this->schemes;
    }

    /**
     * Set test mode.
     */
    public function setTestMode(bool $testMode): self
    {
        $this->testMode = $testMode;
        return $this;
    }
}
