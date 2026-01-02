<?php

namespace App\Services\Peppol;

use App\Models\Invoice;
use App\Models\Company;
use App\Models\PeppolTransmission;
use App\Services\Peppol\Providers\PeppolProviderInterface;
use App\Services\Peppol\Providers\RecommandProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PeppolService
{
    protected UblParserService $ublParser;
    protected ?EReportingService $eReportingService = null;
    protected ?Company $company;
    protected string $apiBaseUrl;
    protected bool $testMode;
    protected bool $autoEReporting = true; // Enable 5-corner model

    public function __construct(?UblParserService $ublParser = null, ?EReportingService $eReportingService = null)
    {
        $this->ublParser = $ublParser ?? new UblParserService();
        $this->company = Company::current();

        // Ensure company exists
        if (!$this->company) {
            throw new \Exception('No active company found. Please ensure you are logged in and have selected a company.');
        }

        $this->testMode = $this->company->peppol_test_mode ?? true;
        $this->apiBaseUrl = $this->testMode
            ? 'https://api.sandbox.peppol.be/v1'
            : 'https://api.peppol.be/v1';

        // Initialize e-Reporting service for 5-corner model
        if ($this->company->ereporting_enabled) {
            $this->eReportingService = $eReportingService ?? new EReportingService($this->company);
        }
    }

    /**
     * Enable or disable automatic e-Reporting (5th corner).
     */
    public function setAutoEReporting(bool $enabled): self
    {
        $this->autoEReporting = $enabled;
        return $this;
    }

    /**
     * Get the appropriate Peppol provider for the company.
     */
    protected function getProvider(): PeppolProviderInterface
    {
        $providerName = $this->company->peppol_provider ?? 'recommand';

        switch (strtolower($providerName)) {
            case 'recommand':
            case 'recommand.eu':
                return new RecommandProvider($this->company, $this->testMode);

            // Add other providers here as needed
            // case 'unifiedpost':
            //     return new UnifiedpostProvider($this->company, $this->testMode);

            default:
                throw new \Exception("Unsupported Peppol provider: {$providerName}. Supported: recommand");
        }
    }

    /**
     * Send invoice via Peppol network.
     */
    public function sendInvoice(Invoice $invoice): PeppolTransmission
    {
        $invoice->load(['partner', 'lines', 'company']);

        // Validate prerequisites
        $this->validateInvoiceForPeppol($invoice);

        // Generate UBL XML
        $ublXml = $this->generateUBL($invoice);

        // Create transmission record
        $transmission = PeppolTransmission::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'direction' => 'outbound',
            'sender_id' => $invoice->company->generatePeppolId(),
            'receiver_id' => $invoice->partner->peppol_id,
            'document_type' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice',
            'message_id' => Str::uuid()->toString(),
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        try {
            // Get the appropriate provider (Recommand.eu, etc.)
            $provider = $this->getProvider();

            // Check if provider is configured
            if (!$provider->isConfigured()) {
                $errors = implode(', ', $provider->getConfigurationErrors());
                throw new \Exception("Provider {$provider->getDisplayName()} not properly configured: {$errors}");
            }

            // Send invoice via provider
            $result = $provider->sendInvoice($ublXml, $transmission);

            // Update transmission with result
            $transmission->update([
                'status' => 'sent',
                'response_payload' => json_encode($result),
            ]);

            // Update invoice Peppol fields
            $invoice->update([
                'peppol_message_id' => $result['message_id'] ?? $transmission->message_id,
                'peppol_status' => $result['status'] ?? 'sent',
                'peppol_sent_at' => now(),
                'ubl_xml' => $ublXml,
            ]);

            Log::info('Peppol invoice sent via ' . $provider->getDisplayName(), [
                'invoice_id' => $invoice->id,
                'provider' => $provider->getName(),
                'test_mode' => $result['test_mode'] ?? false,
                'transmission_id' => $result['transmission_id'] ?? null,
            ]);

            // 5-corner model: Submit to e-Reporting (government)
            $this->submitToEReporting($invoice);

            return $transmission;

        } catch (\Exception $e) {
            $transmission->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $invoice->update([
                'peppol_status' => 'failed',
                'peppol_error' => $e->getMessage(),
            ]);

            Log::error('Peppol invoice send failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate UBL 2.1 XML for invoice.
     */
    public function generateUBL(Invoice $invoice): string
    {
        $invoice->load(['partner', 'lines', 'company']);

        $company = $invoice->company;
        $partner = $invoice->partner;

        // Belgian Peppol uses PINT (Peppol International) or BIS Billing 3.0
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
                     xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                     xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"></Invoice>');

        // Customization ID for Peppol BIS Billing 3.0
        $xml->addChild('cbc:CustomizationID', 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:ProfileID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Invoice identification
        $xml->addChild('cbc:ID', $invoice->invoice_number, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->addChild('cbc:IssueDate', $invoice->invoice_date->format('Y-m-d'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        if ($invoice->due_date) {
            $xml->addChild('cbc:DueDate', $invoice->due_date->format('Y-m-d'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Invoice type code (380 = Commercial invoice)
        $xml->addChild('cbc:InvoiceTypeCode', '380', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Notes
        if ($invoice->notes) {
            $xml->addChild('cbc:Note', htmlspecialchars($invoice->notes), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Currency
        $xml->addChild('cbc:DocumentCurrencyCode', $invoice->currency ?? 'EUR', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Buyer reference (order reference or structured communication)
        if ($invoice->order_reference) {
            $xml->addChild('cbc:BuyerReference', $invoice->order_reference, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        } elseif ($invoice->structured_communication) {
            $xml->addChild('cbc:BuyerReference', $invoice->structured_communication, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Order Reference
        if ($invoice->order_reference) {
            $orderRef = $xml->addChild('cac:OrderReference', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $orderRef->addChild('cbc:ID', $invoice->order_reference, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Supplier (AccountingSupplierParty)
        $supplierParty = $xml->addChild('cac:AccountingSupplierParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $party = $supplierParty->addChild('cac:Party', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Peppol endpoint
        $endpointId = $party->addChild('cbc:EndpointID', $company->generatePeppolId(), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $endpointId->addAttribute('schemeID', '0208'); // Belgian enterprise number scheme

        // Party name
        $partyName = $party->addChild('cac:PartyName', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $partyName->addChild('cbc:Name', htmlspecialchars($company->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Postal address
        $postalAddr = $party->addChild('cac:PostalAddress', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $postalAddr->addChild('cbc:StreetName', htmlspecialchars($company->street . ' ' . $company->house_number), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $postalAddr->addChild('cbc:CityName', htmlspecialchars($company->city), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $postalAddr->addChild('cbc:PostalZone', $company->postal_code, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $country = $postalAddr->addChild('cac:Country', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $country->addChild('cbc:IdentificationCode', $company->country_code ?? 'BE', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // VAT number
        $taxScheme = $party->addChild('cac:PartyTaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxScheme->addChild('cbc:CompanyID', $this->formatVatNumber($company->vat_number), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxSchemeInner = $taxScheme->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxSchemeInner->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Legal entity
        $legalEntity = $party->addChild('cac:PartyLegalEntity', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $legalEntity->addChild('cbc:RegistrationName', htmlspecialchars($company->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $companyId = $legalEntity->addChild('cbc:CompanyID', preg_replace('/[^0-9]/', '', $company->enterprise_number ?? $company->vat_number), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $companyId->addAttribute('schemeID', '0208');

        // Contact
        if ($company->email || $company->phone) {
            $contact = $party->addChild('cac:Contact', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            if ($company->email) {
                $contact->addChild('cbc:ElectronicMail', $company->email, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
            if ($company->phone) {
                $contact->addChild('cbc:Telephone', $company->phone, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
        }

        // Customer (AccountingCustomerParty)
        $customerParty = $xml->addChild('cac:AccountingCustomerParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $party = $customerParty->addChild('cac:Party', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Customer Peppol endpoint
        $endpointId = $party->addChild('cbc:EndpointID', $partner->peppol_id, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $endpointId->addAttribute('schemeID', $this->getPeppolSchemeId($partner->peppol_id));

        // Customer party name
        $partyName = $party->addChild('cac:PartyName', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $partyName->addChild('cbc:Name', htmlspecialchars($partner->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Customer postal address
        $postalAddr = $party->addChild('cac:PostalAddress', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $postalAddr->addChild('cbc:StreetName', htmlspecialchars($partner->street . ' ' . $partner->house_number), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $postalAddr->addChild('cbc:CityName', htmlspecialchars($partner->city), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $postalAddr->addChild('cbc:PostalZone', $partner->postal_code, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $country = $postalAddr->addChild('cac:Country', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $country->addChild('cbc:IdentificationCode', $partner->country_code ?? 'BE', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Customer VAT
        if ($partner->vat_number) {
            $taxScheme = $party->addChild('cac:PartyTaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxScheme->addChild('cbc:CompanyID', $this->formatVatNumber($partner->vat_number), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxSchemeInner = $taxScheme->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxSchemeInner->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Customer legal entity
        $legalEntity = $party->addChild('cac:PartyLegalEntity', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $legalEntity->addChild('cbc:RegistrationName', htmlspecialchars($partner->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Payment Means
        $paymentMeans = $xml->addChild('cac:PaymentMeans', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $paymentMeans->addChild('cbc:PaymentMeansCode', '30', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'); // Credit transfer

        // Structured communication for Belgian payments
        if ($invoice->structured_communication) {
            $paymentId = $paymentMeans->addChild('cbc:PaymentID', preg_replace('/[^0-9]/', '', $invoice->structured_communication), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Bank account
        if ($company->default_iban) {
            $payeeAccount = $paymentMeans->addChild('cac:PayeeFinancialAccount', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $payeeAccount->addChild('cbc:ID', str_replace(' ', '', $company->default_iban), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            if ($company->default_bic) {
                $financialInst = $payeeAccount->addChild('cac:FinancialInstitutionBranch', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
                $financialInst->addChild('cbc:ID', $company->default_bic, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
        }

        // Tax Total
        $taxTotal = $xml->addChild('cac:TaxTotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxAmount = $taxTotal->addChild('cbc:TaxAmount', number_format($invoice->total_vat, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Group lines by VAT rate for TaxSubtotal
        $vatGroups = $invoice->lines->groupBy('vat_rate');
        foreach ($vatGroups as $rate => $lines) {
            $taxableAmount = $lines->sum('line_amount');
            $vatAmount = $lines->sum('vat_amount');

            $taxSubtotal = $taxTotal->addChild('cac:TaxSubtotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxableAmountEl = $taxSubtotal->addChild('cbc:TaxableAmount', number_format($taxableAmount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxableAmountEl->addAttribute('currencyID', $invoice->currency ?? 'EUR');
            $taxAmountEl = $taxSubtotal->addChild('cbc:TaxAmount', number_format($vatAmount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxAmountEl->addAttribute('currencyID', $invoice->currency ?? 'EUR');

            $taxCategory = $taxSubtotal->addChild('cac:TaxCategory', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxCategory->addChild('cbc:ID', $rate > 0 ? 'S' : 'Z', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxCategory->addChild('cbc:Percent', number_format($rate, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxSchemeEl = $taxCategory->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxSchemeEl->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Legal Monetary Total
        $monetaryTotal = $xml->addChild('cac:LegalMonetaryTotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $lineExtAmount = $monetaryTotal->addChild('cbc:LineExtensionAmount', number_format($invoice->total_excl_vat, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $lineExtAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        $taxExclAmount = $monetaryTotal->addChild('cbc:TaxExclusiveAmount', number_format($invoice->total_excl_vat, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxExclAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        $taxInclAmount = $monetaryTotal->addChild('cbc:TaxInclusiveAmount', number_format($invoice->total_incl_vat, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxInclAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        $payableAmount = $monetaryTotal->addChild('cbc:PayableAmount', number_format($invoice->amount_due, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $payableAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Invoice Lines
        foreach ($invoice->lines as $index => $line) {
            $invoiceLine = $xml->addChild('cac:InvoiceLine', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $invoiceLine->addChild('cbc:ID', $line->line_number ?? ($index + 1), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

            $invoicedQty = $invoiceLine->addChild('cbc:InvoicedQuantity', number_format($line->quantity, 4, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $invoicedQty->addAttribute('unitCode', $line->unit ?? 'C62'); // C62 = unit

            $lineExtAmountEl = $invoiceLine->addChild('cbc:LineExtensionAmount', number_format($line->line_amount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $lineExtAmountEl->addAttribute('currencyID', $invoice->currency ?? 'EUR');

            // Line item
            $item = $invoiceLine->addChild('cac:Item', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $item->addChild('cbc:Description', htmlspecialchars($line->description), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $item->addChild('cbc:Name', htmlspecialchars(substr($line->description, 0, 100)), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

            // Classified tax category
            $classifiedTax = $item->addChild('cac:ClassifiedTaxCategory', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $classifiedTax->addChild('cbc:ID', $line->vat_rate > 0 ? 'S' : 'Z', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $classifiedTax->addChild('cbc:Percent', number_format($line->vat_rate, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxSchemeEl = $classifiedTax->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxSchemeEl->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

            // Price
            $price = $invoiceLine->addChild('cac:Price', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $priceAmount = $price->addChild('cbc:PriceAmount', number_format($line->unit_price, 4, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $priceAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');
        }

        // Format and return
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Validate invoice is ready for Peppol.
     */
    protected function validateInvoiceForPeppol(Invoice $invoice): void
    {
        $errors = [];

        if (!$invoice->partner) {
            $errors[] = 'Le client est requis.';
        } elseif (!$invoice->partner->peppol_id) {
            $errors[] = 'Le client n\'a pas d\'identifiant Peppol.';
        } elseif (!$invoice->partner->peppol_capable) {
            $errors[] = 'Le client n\'est pas activé pour Peppol.';
        }

        if (!$invoice->company) {
            $errors[] = 'L\'entreprise est requise.';
        } elseif (!$invoice->company->vat_number) {
            $errors[] = 'Le numéro de TVA de l\'entreprise est requis.';
        }

        if ($invoice->lines->isEmpty()) {
            $errors[] = 'La facture doit avoir au moins une ligne.';
        }

        if (!in_array($invoice->status, ['validated', 'sent'])) {
            $errors[] = 'La facture doit être validée avant envoi.';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }
    }

    /**
     * Format VAT number for UBL (with country prefix).
     */
    protected function formatVatNumber(?string $vatNumber): string
    {
        if (!$vatNumber) return '';

        $clean = preg_replace('/[^A-Z0-9]/', '', strtoupper($vatNumber));

        // Add BE prefix if not present
        if (!preg_match('/^[A-Z]{2}/', $clean)) {
            $clean = 'BE' . $clean;
        }

        return $clean;
    }

    /**
     * Get Peppol scheme ID from identifier.
     */
    protected function getPeppolSchemeId(string $peppolId): string
    {
        // Common Peppol scheme IDs
        // 0208 = Belgian enterprise number
        // 0088 = EAN/GLN
        // 9925 = Belgian VAT

        if (str_starts_with($peppolId, '0208:')) {
            return '0208';
        } elseif (str_starts_with($peppolId, '0088:')) {
            return '0088';
        } elseif (str_starts_with($peppolId, '9925:')) {
            return '9925';
        }

        // Default to Belgian enterprise number scheme
        return '0208';
    }

    /**
     * Check Peppol transmission status.
     */
    public function checkStatus(PeppolTransmission $transmission): string
    {
        if ($this->testMode && !$this->company->peppol_api_key) {
            // Simulate delivered status in test mode
            if ($transmission->status === 'sent' && $transmission->sent_at->diffInMinutes(now()) > 1) {
                $transmission->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);

                if ($transmission->invoice) {
                    $transmission->invoice->update([
                        'peppol_status' => 'delivered',
                        'peppol_delivered_at' => now(),
                    ]);
                }
            }
            return $transmission->fresh()->status;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->company->peppol_api_key,
            ])->get("{$this->apiBaseUrl}/documents/{$transmission->message_id}/status");

            if ($response->successful()) {
                $data = $response->json();
                $newStatus = $data['status'] ?? $transmission->status;

                $transmission->update([
                    'status' => $newStatus,
                    'delivered_at' => $newStatus === 'delivered' ? now() : $transmission->delivered_at,
                ]);

                if ($transmission->invoice && $newStatus === 'delivered') {
                    $transmission->invoice->update([
                        'peppol_status' => 'delivered',
                        'peppol_delivered_at' => now(),
                    ]);
                }

                return $newStatus;
            }
        } catch (\Exception $e) {
            Log::error('Peppol status check failed', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $transmission->status;
    }

    /**
     * Handle incoming webhook notification.
     */
    public function handleWebhook(Company $company, array $payload): array
    {
        $eventType = $payload['event_type'] ?? $payload['type'] ?? 'unknown';

        Log::info('Peppol webhook received', [
            'company_id' => $company->id,
            'event_type' => $eventType,
        ]);

        return match($eventType) {
            'document.received', 'invoice.received' => $this->handleIncomingDocument($company, $payload),
            'document.delivered', 'invoice.delivered' => $this->handleDeliveryConfirmation($payload),
            'document.failed', 'invoice.failed' => $this->handleDeliveryFailure($payload),
            default => ['success' => true, 'message' => 'Event acknowledged'],
        };
    }

    /**
     * Handle incoming document.
     */
    public function handleIncomingDocument(Company $company, array $payload): array
    {
        try {
            $ublContent = $this->extractUblContent($payload);

            if (!$ublContent) {
                Log::warning('No UBL content in Peppol webhook payload', [
                    'company_id' => $company->id,
                ]);
                return ['success' => false, 'message' => 'No UBL content found'];
            }

            // Validate first
            $validation = $this->ublParser->validate($ublContent);
            if (!$validation['valid']) {
                Log::error('Invalid UBL document received', [
                    'company_id' => $company->id,
                    'errors' => $validation['errors'],
                ]);
                return [
                    'success' => false,
                    'message' => 'Invalid UBL document',
                    'errors' => $validation['errors'],
                ];
            }

            // Create invoice
            $transmissionId = $payload['transmission_id'] ?? $payload['document_id'] ?? null;
            $invoice = $this->ublParser->parseAndCreateInvoice($company, $ublContent, $transmissionId);

            return [
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process incoming Peppol document', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process document: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import UBL file manually.
     */
    public function importUblFile(Company $company, string $ublContent): Invoice
    {
        // Validate
        $validation = $this->ublParser->validate($ublContent);
        if (!$validation['valid']) {
            throw new \Exception('Invalid UBL: ' . implode(', ', $validation['errors']));
        }

        return $this->ublParser->parseAndCreateInvoice($company, $ublContent);
    }

    /**
     * Extract UBL content from webhook payload.
     */
    protected function extractUblContent(array $payload): ?string
    {
        // Direct UBL content
        if (!empty($payload['ubl']) && is_string($payload['ubl'])) {
            return $payload['ubl'];
        }

        // Base64 encoded content
        if (!empty($payload['content'])) {
            $content = $payload['content'];
            if (is_string($content)) {
                $decoded = base64_decode($content, true);
                if ($decoded !== false && str_contains($decoded, '<?xml')) {
                    return $decoded;
                }
                if (str_contains($content, '<?xml') || str_contains($content, '<Invoice')) {
                    return $content;
                }
            }
        }

        // Document content
        if (!empty($payload['document']['content'])) {
            $content = $payload['document']['content'];
            $decoded = base64_decode($content, true);
            return $decoded !== false ? $decoded : $content;
        }

        // Storecove format
        if (!empty($payload['document']['raw_document'])) {
            return $payload['document']['raw_document'];
        }

        // XML embedded directly
        if (!empty($payload['xml'])) {
            return $payload['xml'];
        }

        return null;
    }

    /**
     * Handle delivery confirmation.
     */
    protected function handleDeliveryConfirmation(array $payload): array
    {
        $documentId = $payload['document_id'] ?? $payload['transmission_id'] ?? $payload['message_id'] ?? null;

        if ($documentId) {
            $transmission = PeppolTransmission::where('message_id', $documentId)->first();

            if ($transmission) {
                $transmission->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);

                if ($transmission->invoice) {
                    $transmission->invoice->update([
                        'peppol_status' => 'delivered',
                        'peppol_delivered_at' => now(),
                    ]);
                }
            }
        }

        return ['success' => true, 'message' => 'Delivery confirmed'];
    }

    /**
     * Handle delivery failure.
     */
    protected function handleDeliveryFailure(array $payload): array
    {
        $documentId = $payload['document_id'] ?? $payload['transmission_id'] ?? $payload['message_id'] ?? null;
        $errorMessage = $payload['error'] ?? $payload['message'] ?? 'Unknown error';

        if ($documentId) {
            $transmission = PeppolTransmission::where('message_id', $documentId)->first();

            if ($transmission) {
                $transmission->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                ]);

                if ($transmission->invoice) {
                    $transmission->invoice->update([
                        'peppol_status' => 'failed',
                        'peppol_error' => $errorMessage,
                    ]);
                }
            }
        }

        Log::error('Peppol delivery failed', [
            'document_id' => $documentId,
            'error' => $errorMessage,
        ]);

        return ['success' => true, 'message' => 'Failure recorded'];
    }

    /**
     * Submit invoice to e-Reporting (5th corner - government).
     * This is part of the Belgian 2028 B2B e-invoicing mandate.
     */
    protected function submitToEReporting(Invoice $invoice): void
    {
        // Check if e-Reporting is enabled and should auto-submit
        if (!$this->autoEReporting || !$this->eReportingService) {
            return;
        }

        // Check if invoice already submitted to e-Reporting
        if ($invoice->ereporting_submitted_at) {
            return;
        }

        // Check if e-Reporting is required for this invoice
        if (!$this->eReportingService->isEReportingRequired($invoice)) {
            return;
        }

        try {
            $this->eReportingService->submitInvoice($invoice);

            Log::info('Invoice automatically submitted to e-Reporting (5-corner model)', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the Peppol transmission
            Log::warning('E-Reporting auto-submission failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
