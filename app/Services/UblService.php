<?php

namespace App\Services;

use App\Models\Invoice;
use SimpleXMLElement;

class UblService
{
    /**
     * Generate UBL 2.1 XML from an invoice
     */
    public function generateInvoiceUbl(Invoice $invoice): string
    {
        $invoice->load(['partner', 'lines', 'company']);

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"></Invoice>');

        // UBL Version
        $xml->addChild('cbc:UBLVersionID', '2.1', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Customization ID (Peppol BIS 3.0)
        $xml->addChild('cbc:CustomizationID', config('peppol.customization_id'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Profile ID
        $xml->addChild('cbc:ProfileID', config('peppol.profile_id'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Invoice number
        $xml->addChild('cbc:ID', $invoice->invoice_number, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Issue date
        $xml->addChild('cbc:IssueDate', $invoice->invoice_date->format('Y-m-d'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Due date
        if ($invoice->due_date) {
            $xml->addChild('cbc:DueDate', $invoice->due_date->format('Y-m-d'), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Invoice type code (380 = Commercial Invoice)
        $xml->addChild('cbc:InvoiceTypeCode', '380', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Currency
        $xml->addChild('cbc:DocumentCurrencyCode', $invoice->currency ?? 'EUR', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Accounting Supplier Party (Seller)
        $supplierParty = $xml->addChild('cac:AccountingSupplierParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $this->addPartyInfo($supplierParty, $invoice->company, 'supplier');

        // Accounting Customer Party (Buyer)
        $customerParty = $xml->addChild('cac:AccountingCustomerParty', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $this->addPartyInfo($customerParty, $invoice->partner, 'customer');

        // Payment terms
        if ($invoice->payment_terms) {
            $paymentTerms = $xml->addChild('cac:PaymentTerms', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $paymentTerms->addChild('cbc:Note', $invoice->payment_terms, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Invoice lines
        foreach ($invoice->lines as $index => $line) {
            $this->addInvoiceLine($xml, $line, $index + 1);
        }

        // Tax total
        $this->addTaxTotal($xml, $invoice);

        // Legal monetary total
        $this->addLegalMonetaryTotal($xml, $invoice);

        return $xml->asXML();
    }

    /**
     * Parse UBL XML and extract invoice data
     */
    public function parseInvoiceUbl(string $ublXml): array
    {
        $xml = new SimpleXMLElement($ublXml);
        $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Extract basic invoice info
        $invoiceNumber = (string) $xml->xpath('//cbc:ID')[0] ?? 'UNKNOWN';
        $invoiceDate = (string) $xml->xpath('//cbc:IssueDate')[0] ?? now()->format('Y-m-d');
        $dueDate = (string) ($xml->xpath('//cbc:DueDate')[0] ?? null);
        $currency = (string) ($xml->xpath('//cbc:DocumentCurrencyCode')[0] ?? 'EUR');

        // Extract supplier info
        $supplier = $this->parsePartyInfo($xml, '//cac:AccountingSupplierParty');

        // Extract customer info
        $customer = $this->parsePartyInfo($xml, '//cac:AccountingCustomerParty');

        // Extract monetary totals
        $subtotal = (float) ($xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxExclusiveAmount')[0] ?? 0);
        $vatAmount = (float) ($xml->xpath('//cac:TaxTotal/cbc:TaxAmount')[0] ?? 0);
        $totalAmount = (float) ($xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0] ?? 0);

        // Extract invoice lines
        $lines = [];
        $lineNodes = $xml->xpath('//cac:InvoiceLine');
        foreach ($lineNodes as $lineNode) {
            $lines[] = $this->parseInvoiceLine($lineNode);
        }

        return [
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'currency' => $currency,
            'supplier' => $supplier,
            'customer' => $customer,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'lines' => $lines,
            'notes' => (string) ($xml->xpath('//cbc:Note')[0] ?? null),
        ];
    }

    /**
     * Add party information to XML
     */
    protected function addPartyInfo(SimpleXMLElement $parentNode, $party, string $role): void
    {
        $partyNode = $parentNode->addChild('cac:Party', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Endpoint ID (Peppol ID)
        if (isset($party->peppol_id) && $party->peppol_id) {
            $endpoint = $partyNode->addChild('cbc:EndpointID', $party->peppol_id, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $endpoint->addAttribute('schemeID', '0208');
        }

        // Party name
        $partyName = $partyNode->addChild('cac:PartyName', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $partyName->addChild('cbc:Name', htmlspecialchars($party->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Postal address
        if (isset($party->street) || isset($party->city)) {
            $address = $partyNode->addChild('cac:PostalAddress', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            if ($party->street) {
                $address->addChild('cbc:StreetName', htmlspecialchars($party->street), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
            if ($party->city) {
                $address->addChild('cbc:CityName', htmlspecialchars($party->city), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
            if ($party->postal_code) {
                $address->addChild('cbc:PostalZone', $party->postal_code, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
            if ($party->country_code) {
                $country = $address->addChild('cac:Country', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
                $country->addChild('cbc:IdentificationCode', $party->country_code, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
        }

        // Party tax scheme (VAT)
        if (isset($party->vat_number) && $party->vat_number) {
            $partyTaxScheme = $partyNode->addChild('cac:PartyTaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $partyTaxScheme->addChild('cbc:CompanyID', $party->vat_number, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxScheme = $partyTaxScheme->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxScheme->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Party legal entity
        $partyLegalEntity = $partyNode->addChild('cac:PartyLegalEntity', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $partyLegalEntity->addChild('cbc:RegistrationName', htmlspecialchars($party->name), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        if (isset($party->enterprise_number) && $party->enterprise_number) {
            $partyLegalEntity->addChild('cbc:CompanyID', $party->enterprise_number, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }

        // Contact
        if (isset($party->email) || isset($party->phone)) {
            $contact = $partyNode->addChild('cac:Contact', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            if ($party->email) {
                $contact->addChild('cbc:ElectronicMail', $party->email, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
            if ($party->phone) {
                $contact->addChild('cbc:Telephone', $party->phone, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            }
        }
    }

    /**
     * Add invoice line to XML
     */
    protected function addInvoiceLine(SimpleXMLElement $xml, $line, int $lineNumber): void
    {
        $invoiceLine = $xml->addChild('cac:InvoiceLine', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Line ID
        $invoiceLine->addChild('cbc:ID', $lineNumber, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Quantity
        $quantity = $invoiceLine->addChild('cbc:InvoicedQuantity', $line->quantity, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $quantity->addAttribute('unitCode', 'C62'); // C62 = piece/unit

        // Line extension amount (total before VAT)
        $lineExtension = $invoiceLine->addChild('cbc:LineExtensionAmount', number_format($line->subtotal ?? ($line->quantity * $line->unit_price), 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $lineExtension->addAttribute('currencyID', 'EUR');

        // Item
        $item = $invoiceLine->addChild('cac:Item', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $item->addChild('cbc:Description', htmlspecialchars($line->description), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $item->addChild('cbc:Name', htmlspecialchars(substr($line->description, 0, 100)), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Tax category
        $classifiedTaxCategory = $item->addChild('cac:ClassifiedTaxCategory', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $classifiedTaxCategory->addChild('cbc:ID', 'S', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'); // S = Standard rate
        $classifiedTaxCategory->addChild('cbc:Percent', $line->vat_rate, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxScheme = $classifiedTaxCategory->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $taxScheme->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        // Price
        $price = $invoiceLine->addChild('cac:Price', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $priceAmount = $price->addChild('cbc:PriceAmount', number_format($line->unit_price, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $priceAmount->addAttribute('currencyID', 'EUR');
    }

    /**
     * Add tax total to XML
     */
    protected function addTaxTotal(SimpleXMLElement $xml, Invoice $invoice): void
    {
        $taxTotal = $xml->addChild('cac:TaxTotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Total tax amount
        $taxAmount = $taxTotal->addChild('cbc:TaxAmount', number_format($invoice->vat_amount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Tax subtotal per rate
        $vatRates = $invoice->lines->groupBy('vat_rate');
        foreach ($vatRates as $rate => $lines) {
            $taxSubtotal = $taxTotal->addChild('cac:TaxSubtotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

            $taxableAmount = $taxSubtotal->addChild('cbc:TaxableAmount', number_format($lines->sum('subtotal'), 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxableAmount->addAttribute('currencyID', $invoice->currency ?? 'EUR');

            $taxAmountSubtotal = $taxSubtotal->addChild('cbc:TaxAmount', number_format($lines->sum('vat_amount'), 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxAmountSubtotal->addAttribute('currencyID', $invoice->currency ?? 'EUR');

            $taxCategory = $taxSubtotal->addChild('cac:TaxCategory', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxCategory->addChild('cbc:ID', 'S', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxCategory->addChild('cbc:Percent', $rate, 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $taxScheme = $taxCategory->addChild('cac:TaxScheme', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $taxScheme->addChild('cbc:ID', 'VAT', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        }
    }

    /**
     * Add legal monetary total to XML
     */
    protected function addLegalMonetaryTotal(SimpleXMLElement $xml, Invoice $invoice): void
    {
        $legalMonetaryTotal = $xml->addChild('cac:LegalMonetaryTotal', null, 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        // Line extension amount (subtotal)
        $lineExtension = $legalMonetaryTotal->addChild('cbc:LineExtensionAmount', number_format($invoice->subtotal, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $lineExtension->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Tax exclusive amount
        $taxExclusive = $legalMonetaryTotal->addChild('cbc:TaxExclusiveAmount', number_format($invoice->subtotal, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxExclusive->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Tax inclusive amount (total)
        $taxInclusive = $legalMonetaryTotal->addChild('cbc:TaxInclusiveAmount', number_format($invoice->total_amount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $taxInclusive->addAttribute('currencyID', $invoice->currency ?? 'EUR');

        // Payable amount
        $payable = $legalMonetaryTotal->addChild('cbc:PayableAmount', number_format($invoice->total_amount, 2, '.', ''), 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $payable->addAttribute('currencyID', $invoice->currency ?? 'EUR');
    }

    /**
     * Parse party info from UBL XML
     */
    protected function parsePartyInfo(SimpleXMLElement $xml, string $xpath): array
    {
        $partyNode = $xml->xpath($xpath . '/cac:Party')[0] ?? null;
        if (!$partyNode) {
            return [];
        }

        $partyNode->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $partyNode->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        return [
            'name' => (string) ($partyNode->xpath('.//cac:PartyLegalEntity/cbc:RegistrationName')[0]
                ?? $partyNode->xpath('.//cac:PartyName/cbc:Name')[0]
                ?? ''),
            'peppol_id' => (string) ($partyNode->xpath('.//cbc:EndpointID')[0] ?? null),
            'vat_number' => (string) ($partyNode->xpath('.//cac:PartyTaxScheme/cbc:CompanyID')[0] ?? null),
            'street' => (string) ($partyNode->xpath('.//cac:PostalAddress/cbc:StreetName')[0] ?? null),
            'city' => (string) ($partyNode->xpath('.//cac:PostalAddress/cbc:CityName')[0] ?? null),
            'postal_code' => (string) ($partyNode->xpath('.//cac:PostalAddress/cbc:PostalZone')[0] ?? null),
            'country_code' => (string) ($partyNode->xpath('.//cac:PostalAddress/cac:Country/cbc:IdentificationCode')[0] ?? null),
            'email' => (string) ($partyNode->xpath('.//cac:Contact/cbc:ElectronicMail')[0] ?? null),
            'phone' => (string) ($partyNode->xpath('.//cac:Contact/cbc:Telephone')[0] ?? null),
        ];
    }

    /**
     * Parse invoice line from UBL XML
     */
    protected function parseInvoiceLine(SimpleXMLElement $lineNode): array
    {
        $lineNode->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $lineNode->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $quantity = (float) ($lineNode->xpath('.//cbc:InvoicedQuantity')[0] ?? 1);
        $unitPrice = (float) ($lineNode->xpath('.//cac:Price/cbc:PriceAmount')[0] ?? 0);
        $vatRate = (float) ($lineNode->xpath('.//cac:Item/cac:ClassifiedTaxCategory/cbc:Percent')[0] ?? 21);
        $subtotal = $quantity * $unitPrice;
        $vatAmount = $subtotal * ($vatRate / 100);

        return [
            'description' => (string) ($lineNode->xpath('.//cac:Item/cbc:Description')[0]
                ?? $lineNode->xpath('.//cac:Item/cbc:Name')[0]
                ?? ''),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => $subtotal + $vatAmount,
            'account_code' => null,
        ];
    }
}
