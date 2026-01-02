<?php

namespace Tests\Unit\Services\Peppol;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Services\Peppol\UblParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UblParserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UblParserService $parser;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new UblParserService();
        $this->company = Company::factory()->create([
            'vat_number' => 'BE0123456789',
        ]);
    }

    public function test_can_validate_valid_ubl(): void
    {
        $ubl = $this->getSampleUblInvoice();
        $result = $this->parser->validate($ubl);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_validates_missing_invoice_id(): void
    {
        $ubl = $this->getSampleUblInvoice();
        $ubl = str_replace('<cbc:ID>INV-2024-001</cbc:ID>', '', $ubl);

        $result = $this->parser->validate($ubl);

        $this->assertFalse($result['valid']);
        $this->assertContains('Missing invoice number (cbc:ID)', $result['errors']);
    }

    public function test_validates_missing_issue_date(): void
    {
        $ubl = $this->getSampleUblInvoice();
        $ubl = str_replace('<cbc:IssueDate>2024-01-15</cbc:IssueDate>', '', $ubl);

        $result = $this->parser->validate($ubl);

        $this->assertFalse($result['valid']);
        $this->assertContains('Missing issue date (cbc:IssueDate)', $result['errors']);
    }

    public function test_validates_invalid_xml(): void
    {
        $invalidXml = 'This is not valid XML';

        $result = $this->parser->validate($invalidXml);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_can_parse_and_create_invoice(): void
    {
        $ubl = $this->getSampleUblInvoice();

        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals('in', $invoice->type);
        $this->assertEquals('received', $invoice->status);
        $this->assertEquals('INV-2024-001', $invoice->reference);
        $this->assertNotNull($invoice->partner_id);
    }

    public function test_creates_new_partner_if_not_exists(): void
    {
        $ubl = $this->getSampleUblInvoice();

        $initialCount = Partner::count();
        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);

        $this->assertEquals($initialCount + 1, Partner::count());
        $this->assertEquals('Test Supplier SPRL', $invoice->partner->name);
    }

    public function test_reuses_existing_partner_by_vat(): void
    {
        $existingPartner = Partner::factory()->create([
            'company_id' => $this->company->id,
            'vat_number' => 'BE0987654321',
            'type' => 'supplier',
        ]);

        $ubl = $this->getSampleUblInvoice();

        $initialCount = Partner::count();
        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);

        $this->assertEquals($initialCount, Partner::count());
        $this->assertEquals($existingPartner->id, $invoice->partner_id);
    }

    public function test_parses_invoice_lines(): void
    {
        $ubl = $this->getSampleUblInvoice();

        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);
        $invoice->load('lines');

        $this->assertCount(2, $invoice->lines);
        $this->assertEquals('Consulting services', $invoice->lines[0]->description);
        $this->assertEquals(10, $invoice->lines[0]->quantity);
        $this->assertEquals(100.00, $invoice->lines[0]->unit_price);
    }

    public function test_calculates_totals_correctly(): void
    {
        $ubl = $this->getSampleUblInvoice();

        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);

        $this->assertEquals(1250.00, $invoice->total_excl_vat);
        $this->assertEquals(262.50, $invoice->total_vat);
        $this->assertEquals(1512.50, $invoice->total_incl_vat);
    }

    public function test_stores_original_ubl_content(): void
    {
        $ubl = $this->getSampleUblInvoice();

        $invoice = $this->parser->parseAndCreateInvoice($this->company, $ubl);

        $this->assertNotNull($invoice->ubl_xml);
        $this->assertStringContainsString('INV-2024-001', $invoice->ubl_xml);
    }

    protected function getSampleUblInvoice(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
    <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
    <cbc:ID>INV-2024-001</cbc:ID>
    <cbc:IssueDate>2024-01-15</cbc:IssueDate>
    <cbc:DueDate>2024-02-15</cbc:DueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
    <cbc:BuyerReference>PO-12345</cbc:BuyerReference>

    <cac:AccountingSupplierParty>
        <cac:Party>
            <cbc:EndpointID schemeID="0208">0987654321</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>Test Supplier SPRL</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>Rue du Test 123</cbc:StreetName>
                <cbc:CityName>Bruxelles</cbc:CityName>
                <cbc:PostalZone>1000</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>BE</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>BE0987654321</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>Test Supplier SPRL</cbc:RegistrationName>
                <cbc:CompanyID>0987654321</cbc:CompanyID>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>

    <cac:AccountingCustomerParty>
        <cac:Party>
            <cbc:EndpointID schemeID="0208">0123456789</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>My Company</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>Avenue Client 456</cbc:StreetName>
                <cbc:CityName>Liege</cbc:CityName>
                <cbc:PostalZone>4000</cbc:PostalZone>
                <cac:Country>
                    <cbc:IdentificationCode>BE</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>BE0123456789</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>My Company</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>

    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>30</cbc:PaymentMeansCode>
        <cbc:PaymentID>+++123/4567/89012+++</cbc:PaymentID>
        <cac:PayeeFinancialAccount>
            <cbc:ID>BE68539007547034</cbc:ID>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>

    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="EUR">262.50</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="EUR">1250.00</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="EUR">262.50</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>

    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="EUR">1250.00</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="EUR">1250.00</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="EUR">1512.50</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="EUR">1512.50</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>

    <cac:InvoiceLine>
        <cbc:ID>1</cbc:ID>
        <cbc:InvoicedQuantity unitCode="C62">10</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="EUR">1000.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Description>Consulting services</cbc:Description>
            <cbc:Name>Consulting</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="EUR">100.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>

    <cac:InvoiceLine>
        <cbc:ID>2</cbc:ID>
        <cbc:InvoicedQuantity unitCode="C62">5</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="EUR">250.00</cbc:LineExtensionAmount>
        <cac:Item>
            <cbc:Description>Travel expenses</cbc:Description>
            <cbc:Name>Travel</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21.00</cbc:Percent>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="EUR">50.00</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
</Invoice>
XML;
    }
}
