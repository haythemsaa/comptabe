<?php

namespace App\Services\Integrations;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Export accounting data to external software
 *
 * Formats: Winbooks, Popsy, Octopus, Yuki, Generic CSV
 */
class AccountingSoftwareExportService
{
    /**
     * Export to Winbooks format
     */
    public function exportToWinbooks(
        string $companyId,
        Carbon $dateFrom,
        Carbon $dateTo,
        string $exportType = 'journal'
    ): string {
        $company = Company::find($companyId);

        if ($exportType === 'journal') {
            return $this->exportJournalToWinbooks($company, $dateFrom, $dateTo);
        } elseif ($exportType === 'invoices') {
            return $this->exportInvoicesToWinbooks($company, $dateFrom, $dateTo);
        }

        throw new \Exception("Unknown export type: {$exportType}");
    }

    /**
     * Export journal entries to Winbooks format
     */
    protected function exportJournalToWinbooks(Company $company, Carbon $dateFrom, Carbon $dateTo): string
    {
        $entries = JournalEntry::where('company_id', $company->id)
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->with('lines')
            ->orderBy('entry_date')
            ->get();

        $lines = [];

        // Winbooks format: DBKCODE,DOCTYPE,DBKTYPE,DOCLIGNE,DOCNUMBER,DOCDATE,DUEDATE,ACCOUNTGL,ACCOUNTRP,
        //                  COMMENT,AMOUNT1,AMOUNTEUR,VATBASE,VATCODE,CURRAMOUNT,CURCODE,CURRDATE,CURRRATE

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $account = Account::find($line->account_id);

                $lines[] = [
                    'DBKCODE' => 'DIV', // Journal code
                    'DOCTYPE' => '0', // Document type
                    'DBKTYPE' => '0',
                    'DOCLIGNE' => $line->line_number ?? 1,
                    'DOCNUMBER' => $entry->entry_number,
                    'DOCDATE' => $entry->entry_date->format('d/m/Y'),
                    'DUEDATE' => $entry->entry_date->format('d/m/Y'),
                    'ACCOUNTGL' => $account->account_number ?? '',
                    'ACCOUNTRP' => '', // Partner account
                    'COMMENT' => $line->description ?? '',
                    'AMOUNT1' => $line->debit > 0 ? $line->debit : -$line->credit,
                    'AMOUNTEUR' => $line->debit > 0 ? $line->debit : -$line->credit,
                    'VATBASE' => 0,
                    'VATCODE' => '',
                    'CURRAMOUNT' => $line->debit > 0 ? $line->debit : -$line->credit,
                    'CURCODE' => 'EUR',
                    'CURRDATE' => $entry->entry_date->format('d/m/Y'),
                    'CURRRATE' => 1,
                ];
            }
        }

        // Generate CSV
        $filename = "winbooks_export_{$dateFrom->format('Ymd')}_{$dateTo->format('Ymd')}.csv";
        $path = "exports/{$company->id}/{$filename}";

        $csv = $this->generateCsv($lines, ';');

        Storage::put($path, $csv);

        return $path;
    }

    /**
     * Export invoices to Winbooks format
     */
    protected function exportInvoicesToWinbooks(Company $company, Carbon $dateFrom, Carbon $dateTo): string
    {
        $invoices = Invoice::where('company_id', $company->id)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with('partner', 'items')
            ->get();

        $lines = [];

        foreach ($invoices as $invoice) {
            $lineNumber = 1;

            foreach ($invoice->items as $item) {
                $lines[] = [
                    'DBKCODE' => 'VEN', // Sales journal
                    'DOCTYPE' => '1', // Invoice
                    'DBKTYPE' => '0',
                    'DOCLIGNE' => $lineNumber++,
                    'DOCNUMBER' => $invoice->invoice_number,
                    'DOCDATE' => $invoice->issue_date->format('d/m/Y'),
                    'DUEDATE' => $invoice->due_date->format('d/m/Y'),
                    'ACCOUNTGL' => '700000', // Sales account
                    'ACCOUNTRP' => $invoice->partner->account_number ?? '',
                    'COMMENT' => $item->description,
                    'AMOUNT1' => $item->total_amount,
                    'AMOUNTEUR' => $item->total_amount,
                    'VATBASE' => $item->total_amount / (1 + $item->vat_rate / 100),
                    'VATCODE' => $this->getVatCode($item->vat_rate),
                    'CURRAMOUNT' => $item->total_amount,
                    'CURCODE' => $invoice->currency ?? 'EUR',
                    'CURRDATE' => $invoice->issue_date->format('d/m/Y'),
                    'CURRRATE' => 1,
                ];
            }

            // VAT line
            if ($invoice->vat_amount > 0) {
                $lines[] = [
                    'DBKCODE' => 'VEN',
                    'DOCTYPE' => '1',
                    'DBKTYPE' => '0',
                    'DOCLIGNE' => $lineNumber++,
                    'DOCNUMBER' => $invoice->invoice_number,
                    'DOCDATE' => $invoice->issue_date->format('d/m/Y'),
                    'DUEDATE' => $invoice->due_date->format('d/m/Y'),
                    'ACCOUNTGL' => '451000', // VAT account
                    'ACCOUNTRP' => $invoice->partner->account_number ?? '',
                    'COMMENT' => 'TVA',
                    'AMOUNT1' => $invoice->vat_amount,
                    'AMOUNTEUR' => $invoice->vat_amount,
                    'VATBASE' => 0,
                    'VATCODE' => '',
                    'CURRAMOUNT' => $invoice->vat_amount,
                    'CURCODE' => $invoice->currency ?? 'EUR',
                    'CURRDATE' => $invoice->issue_date->format('d/m/Y'),
                    'CURRRATE' => 1,
                ];
            }

            // Customer debit line
            $lines[] = [
                'DBKCODE' => 'VEN',
                'DOCTYPE' => '1',
                'DBKTYPE' => '0',
                'DOCLIGNE' => $lineNumber,
                'DOCNUMBER' => $invoice->invoice_number,
                'DOCDATE' => $invoice->issue_date->format('d/m/Y'),
                'DUEDATE' => $invoice->due_date->format('d/m/Y'),
                'ACCOUNTGL' => '400000', // Customers account
                'ACCOUNTRP' => $invoice->partner->account_number ?? '',
                'COMMENT' => "Facture {$invoice->invoice_number}",
                'AMOUNT1' => -$invoice->total_amount,
                'AMOUNTEUR' => -$invoice->total_amount,
                'VATBASE' => 0,
                'VATCODE' => '',
                'CURRAMOUNT' => -$invoice->total_amount,
                'CURCODE' => $invoice->currency ?? 'EUR',
                'CURRDATE' => $invoice->issue_date->format('d/m/Y'),
                'CURRRATE' => 1,
            ];
        }

        $filename = "winbooks_invoices_{$dateFrom->format('Ymd')}_{$dateTo->format('Ymd')}.csv";
        $path = "exports/{$company->id}/{$filename}";

        $csv = $this->generateCsv($lines, ';');

        Storage::put($path, $csv);

        return $path;
    }

    /**
     * Export to Octopus format
     */
    public function exportToOctopus(
        string $companyId,
        Carbon $dateFrom,
        Carbon $dateTo
    ): string {
        $company = Company::find($companyId);

        $entries = JournalEntry::where('company_id', $company->id)
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->with('lines.account')
            ->orderBy('entry_date')
            ->get();

        $lines = [];

        // Octopus format: Journal,Date,Account,Debit,Credit,Description,Reference
        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $lines[] = [
                    'Journal' => 'DIV',
                    'Date' => $entry->entry_date->format('d/m/Y'),
                    'Account' => $line->account->account_number ?? '',
                    'Debit' => $line->debit > 0 ? number_format($line->debit, 2, '.', '') : '',
                    'Credit' => $line->credit > 0 ? number_format($line->credit, 2, '.', '') : '',
                    'Description' => $line->description ?? '',
                    'Reference' => $entry->entry_number,
                ];
            }
        }

        $filename = "octopus_export_{$dateFrom->format('Ymd')}_{$dateTo->format('Ymd')}.csv";
        $path = "exports/{$company->id}/{$filename}";

        $csv = $this->generateCsv($lines, ',');

        Storage::put($path, $csv);

        return $path;
    }

    /**
     * Export to generic CSV format
     */
    public function exportToGenericCsv(
        string $companyId,
        Carbon $dateFrom,
        Carbon $dateTo,
        string $type = 'journal'
    ): string {
        $company = Company::find($companyId);

        if ($type === 'journal') {
            $data = $this->getJournalData($company->id, $dateFrom, $dateTo);
        } elseif ($type === 'invoices') {
            $data = $this->getInvoicesData($company->id, $dateFrom, $dateTo);
        } else {
            throw new \Exception("Unknown export type: {$type}");
        }

        $filename = "export_{$type}_{$dateFrom->format('Ymd')}_{$dateTo->format('Ymd')}.csv";
        $path = "exports/{$company->id}/{$filename}";

        $csv = $this->generateCsv($data, ',');

        Storage::put($path, $csv);

        return $path;
    }

    /**
     * Get journal data for export
     */
    protected function getJournalData(string $companyId, Carbon $dateFrom, Carbon $dateTo): array
    {
        $entries = JournalEntry::where('company_id', $companyId)
            ->whereBetween('entry_date', [$dateFrom, $dateTo])
            ->with('lines.account')
            ->get();

        $data = [];

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $data[] = [
                    'Date' => $entry->entry_date->format('Y-m-d'),
                    'Entry Number' => $entry->entry_number,
                    'Account Number' => $line->account->account_number ?? '',
                    'Account Name' => $line->account->account_name ?? '',
                    'Description' => $line->description ?? '',
                    'Debit' => $line->debit,
                    'Credit' => $line->credit,
                    'Balance' => $line->debit - $line->credit,
                ];
            }
        }

        return $data;
    }

    /**
     * Get invoices data for export
     */
    protected function getInvoicesData(string $companyId, Carbon $dateFrom, Carbon $dateTo): array
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->whereBetween('issue_date', [$dateFrom, $dateTo])
            ->with('partner')
            ->get();

        $data = [];

        foreach ($invoices as $invoice) {
            $data[] = [
                'Invoice Number' => $invoice->invoice_number,
                'Issue Date' => $invoice->issue_date->format('Y-m-d'),
                'Due Date' => $invoice->due_date->format('Y-m-d'),
                'Customer' => $invoice->partner->name ?? '',
                'Customer VAT' => $invoice->partner->vat_number ?? '',
                'Subtotal' => $invoice->subtotal_amount,
                'VAT Amount' => $invoice->vat_amount,
                'Total Amount' => $invoice->total_amount,
                'Status' => $invoice->status,
                'Payment Date' => $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : '',
            ];
        }

        return $data;
    }

    /**
     * Generate CSV from array data
     */
    protected function generateCsv(array $data, string $delimiter = ','): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, array_keys($data[0]), $delimiter);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get VAT code for rate
     */
    protected function getVatCode(float $vatRate): string
    {
        return match ((int) $vatRate) {
            21 => '1',
            12 => '2',
            6 => '3',
            0 => '0',
            default => '1',
        };
    }

    /**
     * Get available export formats
     */
    public function getAvailableFormats(): array
    {
        return [
            [
                'code' => 'winbooks',
                'name' => 'Winbooks',
                'description' => 'Format compatible avec Winbooks (Belgique)',
                'extensions' => ['csv'],
            ],
            [
                'code' => 'octopus',
                'name' => 'Octopus',
                'description' => 'Format compatible avec Octopus Accounting',
                'extensions' => ['csv'],
            ],
            [
                'code' => 'popsy',
                'name' => 'Popsy',
                'description' => 'Format compatible avec Popsy',
                'extensions' => ['csv'],
            ],
            [
                'code' => 'yuki',
                'name' => 'Yuki',
                'description' => 'Format compatible avec Yuki',
                'extensions' => ['csv'],
            ],
            [
                'code' => 'generic',
                'name' => 'Generic CSV',
                'description' => 'Format CSV générique',
                'extensions' => ['csv'],
            ],
        ];
    }
}
