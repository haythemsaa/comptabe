<?php

namespace App\Services\Peppol;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Partner;
use App\Models\PeppolTransmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UblParserService
{
    /**
     * UBL namespaces.
     */
    protected array $namespaces = [
        'ubl' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
        'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
        'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
    ];

    /**
     * Parse UBL XML and create a purchase invoice.
     */
    public function parseAndCreateInvoice(Company $company, string $ublContent, ?string $transmissionId = null): Invoice
    {
        try {
            $xml = $this->loadXml($ublContent);

            return DB::transaction(function () use ($company, $xml, $ublContent, $transmissionId) {
                // Extract invoice data
                $invoiceData = $this->extractInvoiceData($xml);

                // Find or create supplier
                $supplier = $this->findOrCreateSupplier($company, $invoiceData['supplier']);

                // Create invoice
                $invoice = $this->createInvoice($company, $supplier, $invoiceData, $ublContent);

                // Create invoice lines
                $this->createInvoiceLines($invoice, $invoiceData['lines']);

                // Recalculate totals
                $invoice->refresh();
                $invoice->calculateTotals();
                $invoice->save();

                // Record transmission
                $this->recordTransmission($company, $invoice, $transmissionId, $ublContent);

                Log::info('Purchase invoice created from UBL', [
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'supplier' => $supplier->name,
                ]);

                return $invoice;
            });
        } catch (Exception $e) {
            Log::error('Failed to parse UBL document', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Load and validate XML.
     */
    protected function loadXml(string $content): \SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($content);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception('Invalid XML: ' . ($errors[0]->message ?? 'Unknown error'));
        }

        // Register namespaces
        foreach ($this->namespaces as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }

        return $xml;
    }

    /**
     * Extract invoice data from UBL XML.
     */
    protected function extractInvoiceData(\SimpleXMLElement $xml): array
    {
        $cbc = $xml->children($this->namespaces['cbc']);
        $cac = $xml->children($this->namespaces['cac']);

        // Basic invoice info
        $data = [
            'invoice_number' => (string) $cbc->ID,
            'invoice_date' => $this->parseDate((string) $cbc->IssueDate),
            'due_date' => $cbc->DueDate ? $this->parseDate((string) $cbc->DueDate) : null,
            'currency' => (string) ($cbc->DocumentCurrencyCode ?? 'EUR'),
            'reference' => (string) ($cbc->BuyerReference ?? ''),
            'note' => (string) ($cbc->Note ?? ''),
            'invoice_type_code' => (string) ($cbc->InvoiceTypeCode ?? '380'),
        ];

        // Supplier info
        $data['supplier'] = $this->extractPartyData($cac->AccountingSupplierParty);

        // Payment info
        if ($cac->PaymentMeans) {
            $paymentMeans = $cac->PaymentMeans->children($this->namespaces['cbc']);
            $data['payment_reference'] = (string) ($paymentMeans->PaymentID ?? '');

            // Bank account
            $payeeAccount = $cac->PaymentMeans->children($this->namespaces['cac'])->PayeeFinancialAccount;
            if ($payeeAccount) {
                $data['supplier']['iban'] = (string) $payeeAccount->children($this->namespaces['cbc'])->ID;
            }
        }

        // Tax totals
        $data['total_vat'] = 0;
        if ($cac->TaxTotal) {
            $taxAmount = $cac->TaxTotal->children($this->namespaces['cbc'])->TaxAmount;
            $data['total_vat'] = (float) $taxAmount;
        }

        // Monetary totals
        if ($cac->LegalMonetaryTotal) {
            $lmt = $cac->LegalMonetaryTotal->children($this->namespaces['cbc']);
            $data['total_excl_vat'] = (float) ($lmt->TaxExclusiveAmount ?? $lmt->LineExtensionAmount ?? 0);
            $data['total_incl_vat'] = (float) ($lmt->TaxInclusiveAmount ?? 0);
            $data['amount_due'] = (float) ($lmt->PayableAmount ?? $data['total_incl_vat']);
        }

        // Invoice lines
        $data['lines'] = $this->extractInvoiceLines($xml);

        return $data;
    }

    /**
     * Extract party (supplier/customer) data.
     */
    protected function extractPartyData($accountingParty): array
    {
        $party = $accountingParty->children($this->namespaces['cac'])->Party;
        $cbc = $party->children($this->namespaces['cbc']);
        $cac = $party->children($this->namespaces['cac']);

        $data = [
            'name' => '',
            'vat_number' => '',
            'peppol_id' => '',
            'street' => '',
            'postal_code' => '',
            'city' => '',
            'country_code' => 'BE',
            'email' => '',
            'phone' => '',
        ];

        // Endpoint ID (Peppol identifier)
        if ($cbc->EndpointID) {
            $data['peppol_id'] = (string) $cbc->EndpointID;
        }

        // Legal entity name
        if ($cac->PartyLegalEntity) {
            $legalEntity = $cac->PartyLegalEntity->children($this->namespaces['cbc']);
            $data['name'] = (string) $legalEntity->RegistrationName;
            $data['enterprise_number'] = (string) ($legalEntity->CompanyID ?? '');
        }

        // Party name fallback
        if (empty($data['name']) && $cac->PartyName) {
            $data['name'] = (string) $cac->PartyName->children($this->namespaces['cbc'])->Name;
        }

        // VAT number
        if ($cac->PartyTaxScheme) {
            $data['vat_number'] = (string) $cac->PartyTaxScheme->children($this->namespaces['cbc'])->CompanyID;
        }

        // Postal address
        if ($cac->PostalAddress) {
            $address = $cac->PostalAddress->children($this->namespaces['cbc']);
            $data['street'] = (string) ($address->StreetName ?? '');
            $data['postal_code'] = (string) ($address->PostalZone ?? '');
            $data['city'] = (string) ($address->CityName ?? '');

            $country = $cac->PostalAddress->children($this->namespaces['cac'])->Country;
            if ($country) {
                $data['country_code'] = (string) $country->children($this->namespaces['cbc'])->IdentificationCode;
            }
        }

        // Contact info
        if ($cac->Contact) {
            $contact = $cac->Contact->children($this->namespaces['cbc']);
            $data['email'] = (string) ($contact->ElectronicMail ?? '');
            $data['phone'] = (string) ($contact->Telephone ?? '');
        }

        return $data;
    }

    /**
     * Extract invoice lines.
     */
    protected function extractInvoiceLines(\SimpleXMLElement $xml): array
    {
        $lines = [];
        $cac = $xml->children($this->namespaces['cac']);

        foreach ($cac->InvoiceLine as $xmlLine) {
            $lineCbc = $xmlLine->children($this->namespaces['cbc']);
            $lineCac = $xmlLine->children($this->namespaces['cac']);

            $line = [
                'line_number' => (int) $lineCbc->ID,
                'quantity' => (float) $lineCbc->InvoicedQuantity,
                'unit_code' => (string) ($lineCbc->InvoicedQuantity['unitCode'] ?? 'C62'),
                'line_amount' => (float) $lineCbc->LineExtensionAmount,
                'description' => '',
                'unit_price' => 0,
                'vat_rate' => 0,
                'vat_category' => 'S',
                'discount_percent' => 0,
            ];

            // Item details
            if ($lineCac->Item) {
                $item = $lineCac->Item->children($this->namespaces['cbc']);
                $itemCac = $lineCac->Item->children($this->namespaces['cac']);

                $line['description'] = (string) ($item->Description ?? $item->Name ?? '');
                $line['product_code'] = '';

                // Product identification
                if ($itemCac->SellersItemIdentification) {
                    $line['product_code'] = (string) $itemCac->SellersItemIdentification->children($this->namespaces['cbc'])->ID;
                }

                // Tax category
                if ($itemCac->ClassifiedTaxCategory) {
                    $taxCat = $itemCac->ClassifiedTaxCategory->children($this->namespaces['cbc']);
                    $line['vat_rate'] = (float) ($taxCat->Percent ?? 0);
                    $line['vat_category'] = (string) ($taxCat->ID ?? 'S');
                }
            }

            // Price
            if ($lineCac->Price) {
                $price = $lineCac->Price->children($this->namespaces['cbc']);
                $line['unit_price'] = (float) $price->PriceAmount;
            }

            // Allowance/Charge (discount)
            if ($lineCac->AllowanceCharge) {
                $allowance = $lineCac->AllowanceCharge->children($this->namespaces['cbc']);
                if ((string) $allowance->ChargeIndicator === 'false') {
                    $line['discount_percent'] = (float) ($allowance->MultiplierFactorNumeric ?? 0);
                }
            }

            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Find or create supplier partner.
     */
    protected function findOrCreateSupplier(Company $company, array $supplierData): Partner
    {
        // Try to find by VAT number first
        $partner = null;

        if (!empty($supplierData['vat_number'])) {
            $vatNumber = $this->normalizeVatNumber($supplierData['vat_number']);
            $partner = Partner::where('company_id', $company->id)
                ->where(function ($q) use ($vatNumber, $supplierData) {
                    $q->where('vat_number', 'like', '%' . $vatNumber . '%')
                      ->orWhere('peppol_id', $supplierData['peppol_id']);
                })
                ->first();
        }

        // Try to find by Peppol ID
        if (!$partner && !empty($supplierData['peppol_id'])) {
            $partner = Partner::where('company_id', $company->id)
                ->where('peppol_id', $supplierData['peppol_id'])
                ->first();
        }

        // Create new partner if not found
        if (!$partner) {
            $partner = Partner::create([
                'company_id' => $company->id,
                'type' => 'supplier',
                'reference' => Partner::generateNextReference($company->id, 'supplier'),
                'name' => $supplierData['name'],
                'vat_number' => $supplierData['vat_number'],
                'enterprise_number' => $supplierData['enterprise_number'] ?? null,
                'is_company' => true,
                'street' => $supplierData['street'],
                'postal_code' => $supplierData['postal_code'],
                'city' => $supplierData['city'],
                'country_code' => $supplierData['country_code'],
                'email' => $supplierData['email'],
                'phone' => $supplierData['phone'],
                'peppol_id' => $supplierData['peppol_id'],
                'peppol_capable' => true,
                'peppol_verified_at' => now(),
                'iban' => $supplierData['iban'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Created new supplier from Peppol invoice', [
                'company_id' => $company->id,
                'partner_id' => $partner->id,
                'name' => $partner->name,
            ]);
        } else {
            // Update Peppol info if not set
            if (empty($partner->peppol_id) && !empty($supplierData['peppol_id'])) {
                $partner->update([
                    'peppol_id' => $supplierData['peppol_id'],
                    'peppol_capable' => true,
                    'peppol_verified_at' => now(),
                ]);
            }

            // Ensure partner is also a supplier
            if (!$partner->isSupplier()) {
                $partner->update(['type' => 'both']);
            }
        }

        return $partner;
    }

    /**
     * Create purchase invoice.
     */
    protected function createInvoice(Company $company, Partner $supplier, array $data, string $ublContent): Invoice
    {
        // Generate internal number for purchase invoice
        $internalNumber = Invoice::generateNextNumber($company->id, 'in');

        // Determine status based on invoice type code
        // 380 = Commercial Invoice, 381 = Credit Note
        $documentType = $data['invoice_type_code'] === '381' ? 'credit_note' : 'invoice';

        return Invoice::create([
            'company_id' => $company->id,
            'partner_id' => $supplier->id,
            'type' => 'in', // Purchase invoice
            'document_type' => $documentType,
            'status' => 'received',
            'invoice_number' => $internalNumber,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'reference' => $data['invoice_number'], // Supplier's invoice number as reference
            'order_reference' => $data['reference'],
            'total_excl_vat' => $data['total_excl_vat'] ?? 0,
            'total_vat' => $data['total_vat'] ?? 0,
            'total_incl_vat' => $data['total_incl_vat'] ?? 0,
            'amount_paid' => 0,
            'amount_due' => $data['amount_due'] ?? $data['total_incl_vat'] ?? 0,
            'currency' => $data['currency'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'ubl_xml' => $ublContent,
            'notes' => $data['note'] ?? null,
            'peppol_status' => 'received',
        ]);
    }

    /**
     * Create invoice lines.
     */
    protected function createInvoiceLines(Invoice $invoice, array $lines): void
    {
        foreach ($lines as $lineData) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'line_number' => $lineData['line_number'],
                'product_code' => $lineData['product_code'] ?? null,
                'description' => $lineData['description'],
                'quantity' => $lineData['quantity'],
                'unit_code' => $lineData['unit_code'],
                'unit_price' => $lineData['unit_price'],
                'discount_percent' => $lineData['discount_percent'],
                'discount_amount' => 0, // Will be calculated
                'line_amount' => $lineData['line_amount'],
                'vat_category' => $lineData['vat_category'],
                'vat_rate' => $lineData['vat_rate'],
                'vat_amount' => round($lineData['line_amount'] * ($lineData['vat_rate'] / 100), 2),
            ]);
        }
    }

    /**
     * Record Peppol transmission.
     */
    protected function recordTransmission(Company $company, Invoice $invoice, ?string $transmissionId, string $ublContent): void
    {
        PeppolTransmission::create([
            'company_id' => $company->id,
            'transmittable_type' => Invoice::class,
            'transmittable_id' => $invoice->id,
            'direction' => 'inbound',
            'document_type' => 'invoice',
            'participant_id' => $invoice->partner->peppol_id ?? $invoice->partner->vat_number,
            'transmission_id' => $transmissionId,
            'status' => 'received',
            'ubl_content' => $ublContent,
            'received_at' => now(),
        ]);
    }

    /**
     * Normalize VAT number for comparison.
     */
    protected function normalizeVatNumber(string $vatNumber): string
    {
        // Remove country code prefix and special characters
        $vatNumber = preg_replace('/^[A-Z]{2}/i', '', $vatNumber);
        return preg_replace('/[\s.\-]/', '', $vatNumber);
    }

    /**
     * Parse date string.
     */
    protected function parseDate(string $dateString): Carbon
    {
        return Carbon::parse($dateString);
    }

    /**
     * Validate UBL document structure.
     */
    public function validate(string $ublContent): array
    {
        $errors = [];
        $warnings = [];

        try {
            $xml = $this->loadXml($ublContent);
            $cbc = $xml->children($this->namespaces['cbc']);
            $cac = $xml->children($this->namespaces['cac']);

            // Check required elements
            if (empty((string) $cbc->ID)) {
                $errors[] = 'Missing invoice number (cbc:ID)';
            }

            if (empty((string) $cbc->IssueDate)) {
                $errors[] = 'Missing issue date (cbc:IssueDate)';
            }

            if (!$cac->AccountingSupplierParty) {
                $errors[] = 'Missing supplier information (cac:AccountingSupplierParty)';
            }

            if (!$cac->InvoiceLine || count($cac->InvoiceLine) === 0) {
                $errors[] = 'Missing invoice lines (cac:InvoiceLine)';
            }

            if (!$cac->LegalMonetaryTotal) {
                $warnings[] = 'Missing monetary totals (cac:LegalMonetaryTotal)';
            }

            // Validate VAT number format
            if ($cac->AccountingSupplierParty) {
                $supplierData = $this->extractPartyData($cac->AccountingSupplierParty);
                if (empty($supplierData['vat_number']) && empty($supplierData['peppol_id'])) {
                    $warnings[] = 'Supplier has no VAT number or Peppol ID';
                }
            }

        } catch (Exception $e) {
            $errors[] = 'XML parsing error: ' . $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
