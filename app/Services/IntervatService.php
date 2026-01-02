<?php

namespace App\Services;

use App\Models\VatDeclaration;
use App\Models\Company;
use Illuminate\Support\Collection;

class IntervatService
{
    protected ?Company $company;

    public function __construct()
    {
        $this->company = auth()->user()?->currentCompany ?? Company::current();
    }

    /**
     * Generate Intervat XML for VAT declaration
     */
    public function generateXml(VatDeclaration $declaration): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <ns2:VATConsignment xmlns="http://www.minfin.fgov.be/InputCommon"
                xmlns:ns2="http://www.minfin.fgov.be/VATConsignment"
                VATDeclarationsNbr="1">
            </ns2:VATConsignment>');

        // Representative
        $representative = $xml->addChild('ns2:Representative');
        $repId = $representative->addChild('RepresentativeID', $this->cleanVatNumber($this->company->vat_number), 'http://www.minfin.fgov.be/InputCommon');
        $repId->addAttribute('identificationType', 'NVAT');

        // VAT Declaration
        $vatDecl = $xml->addChild('ns2:VATDeclaration');
        $vatDecl->addAttribute('SequenceNumber', '1');
        $vatDecl->addAttribute('DeclarantReference', $declaration->id);

        // Declarant
        $declarant = $vatDecl->addChild('ns2:Declarant');
        $vatNumber = $declarant->addChild('VATNumber', $this->cleanVatNumber($this->company->vat_number), 'http://www.minfin.fgov.be/InputCommon');
        $vatNumber->addAttribute('issuedBy', 'BE');
        $declarant->addChild('Name', htmlspecialchars($this->company->name), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('Street', htmlspecialchars($this->company->address ?? ''), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('PostCode', $this->company->postal_code ?? '', 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('City', htmlspecialchars($this->company->city ?? ''), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('CountryCode', 'BE', 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('EmailAddress', $this->company->email ?? '', 'http://www.minfin.fgov.be/InputCommon');

        // Period
        $period = $vatDecl->addChild('ns2:Period');
        if ($declaration->period_type === 'monthly') {
            $period->addChild('ns2:Month', $declaration->period_start->format('n'));
        } else {
            $period->addChild('ns2:Quarter', ceil($declaration->period_start->month / 3));
        }
        $period->addChild('ns2:Year', $declaration->period_start->year);

        // Data
        $data = $vatDecl->addChild('ns2:Data');

        // Add grid values
        $gridValues = $declaration->grid_values ?? [];

        // Output grids
        $this->addGridAmount($data, '00', $gridValues['00'] ?? 0);
        $this->addGridAmount($data, '01', $gridValues['01'] ?? 0);
        $this->addGridAmount($data, '02', $gridValues['02'] ?? 0);
        $this->addGridAmount($data, '03', $gridValues['03'] ?? 0);
        $this->addGridAmount($data, '44', $gridValues['44'] ?? 0);
        $this->addGridAmount($data, '45', $gridValues['45'] ?? 0);
        $this->addGridAmount($data, '46', $gridValues['46'] ?? 0);
        $this->addGridAmount($data, '47', $gridValues['47'] ?? 0);
        $this->addGridAmount($data, '48', $gridValues['48'] ?? 0);
        $this->addGridAmount($data, '49', $gridValues['49'] ?? 0);
        $this->addGridAmount($data, '54', $gridValues['54'] ?? 0);
        $this->addGridAmount($data, '55', $gridValues['55'] ?? 0);
        $this->addGridAmount($data, '56', $gridValues['56'] ?? 0);
        $this->addGridAmount($data, '57', $gridValues['57'] ?? 0);
        $this->addGridAmount($data, '59', $gridValues['59'] ?? 0);
        $this->addGridAmount($data, '61', $gridValues['61'] ?? 0);
        $this->addGridAmount($data, '62', $gridValues['62'] ?? 0);
        $this->addGridAmount($data, '63', $gridValues['63'] ?? 0);
        $this->addGridAmount($data, '64', $gridValues['64'] ?? 0);
        $this->addGridAmount($data, '71', $gridValues['71'] ?? 0);
        $this->addGridAmount($data, '72', $gridValues['72'] ?? 0);
        $this->addGridAmount($data, '81', $gridValues['81'] ?? 0);
        $this->addGridAmount($data, '82', $gridValues['82'] ?? 0);
        $this->addGridAmount($data, '83', $gridValues['83'] ?? 0);
        $this->addGridAmount($data, '86', $gridValues['86'] ?? 0);
        $this->addGridAmount($data, '87', $gridValues['87'] ?? 0);
        $this->addGridAmount($data, '88', $gridValues['88'] ?? 0);

        // Format XML nicely
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Generate client listing XML
     */
    public function generateClientListing(Collection $clients, int $year): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <ns2:ClientListingConsignment xmlns="http://www.minfin.fgov.be/InputCommon"
                xmlns:ns2="http://www.minfin.fgov.be/ClientListingConsignment"
                ClientListingsNbr="1">
            </ns2:ClientListingConsignment>');

        // Representative
        $representative = $xml->addChild('ns2:Representative');
        $repId = $representative->addChild('RepresentativeID', $this->cleanVatNumber($this->company->vat_number), 'http://www.minfin.fgov.be/InputCommon');
        $repId->addAttribute('identificationType', 'NVAT');

        // Client Listing
        $listing = $xml->addChild('ns2:ClientListing');
        $listing->addAttribute('SequenceNumber', '1');
        $listing->addAttribute('ClientsNbr', $clients->count());
        $listing->addAttribute('DeclarantReference', 'CL-' . $year);
        $listing->addAttribute('TurnOverSum', number_format($clients->sum('total_excl'), 2, '.', ''));
        $listing->addAttribute('VATAmountSum', number_format($clients->sum('total_vat'), 2, '.', ''));

        // Declarant
        $declarant = $listing->addChild('ns2:Declarant');
        $vatNumber = $declarant->addChild('VATNumber', $this->cleanVatNumber($this->company->vat_number), 'http://www.minfin.fgov.be/InputCommon');
        $vatNumber->addAttribute('issuedBy', 'BE');
        $declarant->addChild('Name', htmlspecialchars($this->company->name), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('Street', htmlspecialchars($this->company->address ?? ''), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('PostCode', $this->company->postal_code ?? '', 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('City', htmlspecialchars($this->company->city ?? ''), 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('CountryCode', 'BE', 'http://www.minfin.fgov.be/InputCommon');
        $declarant->addChild('EmailAddress', $this->company->email ?? '', 'http://www.minfin.fgov.be/InputCommon');

        // Period
        $period = $listing->addChild('ns2:Period');
        $period->addChild('ns2:Year', $year);

        // Clients
        $sequence = 1;
        foreach ($clients as $client) {
            if (!$client['vat_number']) {
                continue;
            }

            $clientNode = $listing->addChild('ns2:Client');
            $clientNode->addAttribute('SequenceNumber', $sequence++);

            $companyVat = $clientNode->addChild('ns2:CompanyVATNumber');
            $countryCode = substr($client['vat_number'], 0, 2);
            $vatNum = preg_replace('/[^0-9]/', '', substr($client['vat_number'], 2));

            $companyVat->addChild('ns2:CountryCode', $countryCode);
            $companyVat->addChild('ns2:VATNumber', $vatNum);

            $clientNode->addChild('ns2:TurnOver', number_format($client['total_excl'], 2, '.', ''));
            $clientNode->addChild('ns2:VATAmount', number_format($client['total_vat'], 2, '.', ''));
        }

        // Format XML nicely
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Generate intrastat declaration XML
     */
    public function generateIntrastat(Collection $arrivals, Collection $dispatches, int $year, int $month): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
            <Intrastat xmlns="http://www.nbb.be/intrastat">
            </Intrastat>');

        $xml->addChild('Administration');
        $admin = $xml->Administration;
        $admin->addChild('From', $this->cleanVatNumber($this->company->vat_number));
        $admin->addChild('To', 'NBB');
        $admin->addChild('Domain', 'SXX');

        $report = $xml->addChild('Report');
        $report->addAttribute('action', 'new');
        $report->addAttribute('date', now()->format('Y-m-d'));

        $reportData = $report->addChild('Data');
        $reportData->addAttribute('form', 'EXF19');
        $reportData->addAttribute('type', 'month');
        $reportData->addAttribute('code', sprintf('%d%02d', $year, $month));

        // Dispatches
        foreach ($dispatches as $dispatch) {
            $item = $reportData->addChild('Item');
            $item->addChild('Cn8', '00000000'); // Should be filled with actual CN8 code
            $item->addChild('InvoiceValue', round($dispatch->total_excl_vat));
            $item->addChild('StatisticalValue', round($dispatch->total_excl_vat));
            $item->addChild('NetMass', '0');
            $item->addChild('SupplementaryUnits', '0');
            $item->addChild('NatureOfTransaction', 'A');
            $item->addChild('ModeOfTransport', '3');
            $item->addChild('RegionOfOrigin', 'VLG');
            $item->addChild('DestinationCountry', $this->getCountryCode($dispatch->partner->vat_number ?? ''));
        }

        // Format XML nicely
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Add grid amount to XML
     */
    protected function addGridAmount(\SimpleXMLElement $parent, string $grid, float $amount): void
    {
        if ($amount == 0) {
            return;
        }

        $amountNode = $parent->addChild('ns2:Amount', number_format(abs($amount), 2, '.', ''));
        $amountNode->addAttribute('GridNumber', $grid);
    }

    /**
     * Clean VAT number (remove country code and dots/spaces)
     */
    protected function cleanVatNumber(?string $vatNumber): string
    {
        if (!$vatNumber) {
            return '';
        }

        $vatNumber = preg_replace('/[^A-Z0-9]/i', '', $vatNumber);

        // Remove BE prefix if present
        if (str_starts_with(strtoupper($vatNumber), 'BE')) {
            $vatNumber = substr($vatNumber, 2);
        }

        return $vatNumber;
    }

    /**
     * Get country code from VAT number
     */
    protected function getCountryCode(?string $vatNumber): string
    {
        if (!$vatNumber || strlen($vatNumber) < 2) {
            return 'XX';
        }

        return strtoupper(substr($vatNumber, 0, 2));
    }
}
