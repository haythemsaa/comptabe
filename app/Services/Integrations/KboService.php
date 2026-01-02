<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service d'intégration avec la Banque-Carrefour des Entreprises (KBO/BCE)
 *
 * API publique belge pour récupérer les informations d'entreprises
 * via leur numéro d'entreprise ou de TVA
 */
class KboService
{
    /**
     * API endpoints
     */
    const KBO_API_BASE = 'https://kbopub.economie.fgov.be/kbopub';
    const KBO_SEARCH_ENDPOINT = '/rempreintnamenwebservicewebservicekbopub/webservice/GetEnterprise';

    /**
     * Cache duration (24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Rechercher une entreprise par numéro d'entreprise KBO (10 chiffres)
     *
     * @param string $enterpriseNumber Numéro d'entreprise (ex: 0123456789 ou BE0123456789)
     * @return array|null
     */
    public function getEnterpriseByNumber(string $enterpriseNumber): ?array
    {
        // Normaliser le numéro (enlever BE, espaces, points)
        $cleanNumber = $this->normalizeEnterpriseNumber($enterpriseNumber);

        if (!$this->isValidEnterpriseNumber($cleanNumber)) {
            Log::warning('KBO: Invalid enterprise number', ['number' => $enterpriseNumber]);
            return null;
        }

        // Check cache
        $cacheKey = "kbo_enterprise_{$cleanNumber}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // KBO Public Search API
            $response = Http::timeout(10)
                ->get(self::KBO_API_BASE . '/rest/company', [
                    'enterpriseNumber' => $cleanNumber,
                ])
                ->throw();

            $data = $response->json();

            if (empty($data)) {
                Log::info('KBO: Enterprise not found', ['number' => $cleanNumber]);
                return null;
            }

            $parsed = $this->parseKboResponse($data);

            // Cache for 24h
            Cache::put($cacheKey, $parsed, self::CACHE_TTL);

            return $parsed;

        } catch (\Exception $e) {
            Log::error('KBO API error', [
                'enterprise_number' => $cleanNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Rechercher une entreprise par numéro de TVA (BE + 10 chiffres)
     *
     * @param string $vatNumber Numéro de TVA (ex: BE0123456789)
     * @return array|null
     */
    public function getEnterpriseByVat(string $vatNumber): ?array
    {
        // Extraire le numéro d'entreprise du numéro de TVA
        $enterpriseNumber = $this->vatToEnterpriseNumber($vatNumber);

        if (!$enterpriseNumber) {
            return null;
        }

        return $this->getEnterpriseByNumber($enterpriseNumber);
    }

    /**
     * Rechercher des entreprises par nom
     *
     * @param string $name Nom de l'entreprise (minimum 3 caractères)
     * @param int $limit Nombre maximum de résultats (default: 20, max: 100)
     * @return array
     */
    public function searchByName(string $name, int $limit = 20): array
    {
        if (strlen($name) < 3) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->get(self::KBO_API_BASE . '/rest/search', [
                    'name' => $name,
                    'limit' => min($limit, 100),
                ])
                ->throw();

            $results = $response->json();

            if (empty($results)) {
                return [];
            }

            return array_map(function ($item) {
                return [
                    'enterprise_number' => $item['enterpriseNumber'] ?? null,
                    'vat_number' => $this->formatVatNumber($item['enterpriseNumber'] ?? null),
                    'name' => $item['denomination'] ?? null,
                    'legal_form' => $item['juridicalForm'] ?? null,
                    'address' => $this->formatAddress($item),
                    'status' => $item['status'] ?? 'unknown',
                ];
            }, $results);

        } catch (\Exception $e) {
            Log::error('KBO search error', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Enrichir les données d'un partenaire avec les infos KBO
     *
     * @param string $enterpriseNumber
     * @return array Données prêtes à merger avec un Partner model
     */
    public function enrichPartnerData(string $enterpriseNumber): array
    {
        $data = $this->getEnterpriseByNumber($enterpriseNumber);

        if (!$data) {
            return [];
        }

        return [
            'name' => $data['name'] ?? null,
            'vat_number' => $data['vat_number'] ?? null,
            'enterprise_number' => $data['enterprise_number'] ?? null,
            'street' => $data['address']['street'] ?? null,
            'house_number' => $data['address']['house_number'] ?? null,
            'box' => $data['address']['box'] ?? null,
            'postal_code' => $data['address']['postal_code'] ?? null,
            'city' => $data['address']['city'] ?? null,
            'country_code' => $data['address']['country_code'] ?? 'BE',
            'legal_form' => $data['legal_form'] ?? null,
        ];
    }

    /**
     * Parse KBO API response
     *
     * @param array $data
     * @return array
     */
    protected function parseKboResponse(array $data): array
    {
        $main = $data[0] ?? $data;

        return [
            'enterprise_number' => $main['EnterpriseNumber'] ?? null,
            'vat_number' => $this->formatVatNumber($main['EnterpriseNumber'] ?? null),
            'name' => $main['Denomination'] ?? null,
            'legal_form' => $main['JuridicalForm'] ?? null,
            'legal_situation' => $main['JuridicalSituation'] ?? null,
            'status' => $main['Status'] ?? 'unknown',
            'start_date' => $main['StartDate'] ?? null,
            'address' => [
                'street' => $main['Street'] ?? null,
                'house_number' => $main['HouseNumber'] ?? null,
                'box' => $main['Box'] ?? null,
                'postal_code' => $main['Zipcode'] ?? null,
                'city' => $main['Municipality'] ?? null,
                'country_code' => $main['CountryNL'] === 'België' || $main['CountryFR'] === 'Belgique' ? 'BE' : null,
            ],
            'activities' => $main['Activities'] ?? [],
            'contacts' => [
                'phone' => $main['Phone'] ?? null,
                'email' => $main['Email'] ?? null,
                'website' => $main['WebAddress'] ?? null,
            ],
        ];
    }

    /**
     * Format address from API response
     *
     * @param array $data
     * @return string
     */
    protected function formatAddress(array $data): string
    {
        $parts = array_filter([
            trim(($data['street'] ?? '') . ' ' . ($data['houseNumber'] ?? '')),
            $data['zipcode'] ?? null,
            $data['municipality'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Normalize enterprise number (remove BE, spaces, dots)
     *
     * @param string $number
     * @return string
     */
    protected function normalizeEnterpriseNumber(string $number): string
    {
        $clean = strtoupper($number);
        $clean = str_replace(['BE', ' ', '.', '-'], '', $clean);

        return $clean;
    }

    /**
     * Convert VAT number to enterprise number
     *
     * @param string $vatNumber
     * @return string|null
     */
    protected function vatToEnterpriseNumber(string $vatNumber): ?string
    {
        $clean = $this->normalizeEnterpriseNumber($vatNumber);

        if ($this->isValidEnterpriseNumber($clean)) {
            return $clean;
        }

        return null;
    }

    /**
     * Validate enterprise number (must be 10 digits)
     *
     * @param string $number
     * @return bool
     */
    protected function isValidEnterpriseNumber(string $number): bool
    {
        return preg_match('/^\d{10}$/', $number);
    }

    /**
     * Format enterprise number as VAT number (BE + 10 digits formatted)
     *
     * @param string|null $enterpriseNumber
     * @return string|null
     */
    protected function formatVatNumber(?string $enterpriseNumber): ?string
    {
        if (!$enterpriseNumber || !$this->isValidEnterpriseNumber($enterpriseNumber)) {
            return null;
        }

        // Format: BE 0123.456.789
        return 'BE ' .
            substr($enterpriseNumber, 0, 4) . '.' .
            substr($enterpriseNumber, 4, 3) . '.' .
            substr($enterpriseNumber, 7, 3);
    }

    /**
     * Vérifier si un numéro d'entreprise existe dans le KBO
     *
     * @param string $enterpriseNumber
     * @return bool
     */
    public function exists(string $enterpriseNumber): bool
    {
        $data = $this->getEnterpriseByNumber($enterpriseNumber);
        return $data !== null;
    }

    /**
     * Clear cache for an enterprise
     *
     * @param string $enterpriseNumber
     * @return void
     */
    public function clearCache(string $enterpriseNumber): void
    {
        $cleanNumber = $this->normalizeEnterpriseNumber($enterpriseNumber);
        $cacheKey = "kbo_enterprise_{$cleanNumber}";
        Cache::forget($cacheKey);
    }
}
