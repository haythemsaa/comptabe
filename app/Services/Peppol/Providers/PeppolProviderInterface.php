<?php

namespace App\Services\Peppol\Providers;

use App\Models\PeppolTransmission;

interface PeppolProviderInterface
{
    /**
     * Send invoice via Peppol Access Point.
     *
     * @param string $ublXml UBL 2.1 XML content
     * @param PeppolTransmission $transmission Transmission record
     * @return array Result with keys: success, provider, test_mode, transmission_id, message_id, status, response_data
     * @throws \Exception if sending fails
     */
    public function sendInvoice(string $ublXml, PeppolTransmission $transmission): array;

    /**
     * Get provider internal name.
     */
    public function getName(): string;

    /**
     * Get provider display name.
     */
    public function getDisplayName(): string;

    /**
     * Check if provider is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Get configuration errors.
     *
     * @return array List of error messages
     */
    public function getConfigurationErrors(): array;
}
