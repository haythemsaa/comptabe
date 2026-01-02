<?php

namespace App\Services\Export;

use App\Models\Company;
use Illuminate\Support\Collection;

/**
 * Exporter pour Winbooks
 * Format XML conforme au schéma Winbooks Import
 */
class WinbooksExporter extends BaseExporter
{
    /**
     * Export des factures vers Winbooks (XML)
     */
    public function exportInvoices(Collection $invoices): array
    {
        $entries = [];

        foreach ($invoices as $invoice) {
            $journal = $this->getJournalCode($invoice->type);
            $date = $invoice->invoice_date->format('Y-m-d');

            $entry = [
                'transaction' => [
                    'journal' => $journal,
                    'date' => $date,
                    'period' => $invoice->invoice_date->format('Ym'),
                    'docnumber' => $invoice->invoice_number,
                    'comment' => $this->sanitize($invoice->partner->name),
                    'lines' => [],
                ],
            ];

            // Ligne client/fournisseur (compte tiers)
            $partnerAccount = $this->getPartnerAccount($invoice);

            $entry['transaction']['lines'][] = [
                'account' => $partnerAccount,
                'debitcredit' => $invoice->type === 'out' ? 'D' : 'C',
                'amount' => $this->formatAmount($invoice->total_incl_vat),
                'comment' => $this->sanitize($invoice->invoice_number),
                'duedate' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
            ];

            // Lignes de facture groupées par taux de TVA
            $linesByVatRate = $invoice->lines->groupBy('vat_rate');

            foreach ($linesByVatRate as $vatRate => $lines) {
                $totalHT = $lines->sum('line_amount');
                $totalTVA = $lines->sum('vat_amount');

                // Ligne HT (produit/charge)
                $revenueAccount = $this->getRevenueOrExpenseAccount($invoice);

                $entry['transaction']['lines'][] = [
                    'account' => $revenueAccount,
                    'debitcredit' => $invoice->type === 'out' ? 'C' : 'D',
                    'amount' => $this->formatAmount($totalHT),
                    'comment' => 'TVA ' . $vatRate . '%',
                    'vatcode' => $this->getVatCode($vatRate),
                    'vatbase' => $this->formatAmount($totalHT),
                ];

                // Ligne TVA
                if ($totalTVA > 0) {
                    $vatAccount = $this->getVatAccount($invoice->type);

                    $entry['transaction']['lines'][] = [
                        'account' => $vatAccount,
                        'debitcredit' => $invoice->type === 'out' ? 'C' : 'D',
                        'amount' => $this->formatAmount($totalTVA),
                        'comment' => 'TVA ' . $vatRate . '%',
                        'vatcode' => $this->getVatCode($vatRate),
                    ];
                }
            }

            $entries[] = $entry;
        }

        $xml = $this->generateWinbooksXml($entries);

        return [
            'content' => $xml,
            'filename' => 'winbooks-import.xml',
            'mime' => 'application/xml',
        ];
    }

    /**
     * Export du journal
     */
    public function exportJournal(Collection $invoices): array
    {
        return $this->exportInvoices($invoices);
    }

    /**
     * Export TVA
     */
    public function exportVat(Collection $invoices): array
    {
        $vatData = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $code = $this->getVatCode($line->vat_rate);

                if (!isset($vatData[$code])) {
                    $vatData[$code] = [
                        'code' => $code,
                        'rate' => $line->vat_rate,
                        'base' => 0,
                        'vat' => 0,
                    ];
                }

                $multiplier = $invoice->type === 'out' ? 1 : -1;

                $vatData[$code]['base'] += $line->line_amount * $multiplier;
                $vatData[$code]['vat'] += $line->vat_amount * $multiplier;
            }
        }

        // Générer CSV pour la déclaration TVA
        $rows = [];
        $rows[] = ['Code TVA', 'Taux', 'Base HT', 'Montant TVA'];

        foreach ($vatData as $data) {
            $rows[] = [
                $data['code'],
                $data['rate'] . '%',
                $this->formatAmount($data['base']),
                $this->formatAmount($data['vat']),
            ];
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'winbooks-tva.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Générer le XML Winbooks
     */
    protected function generateWinbooksXml(array $entries): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><import></import>');

        $xml->addAttribute('version', '1.0');
        $xml->addChild('company', $this->sanitize($this->company->name));
        $xml->addChild('fiscalyear', date('Y'));

        $transactions = $xml->addChild('transactions');

        foreach ($entries as $entry) {
            $transaction = $transactions->addChild('transaction');

            $transData = $entry['transaction'];

            $transaction->addChild('journal', $transData['journal']);
            $transaction->addChild('date', $transData['date']);
            $transaction->addChild('period', $transData['period']);
            $transaction->addChild('docnumber', $transData['docnumber']);
            $transaction->addChild('comment', $transData['comment']);

            $lines = $transaction->addChild('lines');

            foreach ($transData['lines'] as $lineData) {
                $line = $lines->addChild('line');

                $line->addChild('account', $lineData['account']);
                $line->addChild('debitcredit', $lineData['debitcredit']);
                $line->addChild('amount', $lineData['amount']);
                $line->addChild('comment', $lineData['comment']);

                if (!empty($lineData['duedate'])) {
                    $line->addChild('duedate', $lineData['duedate']);
                }

                if (!empty($lineData['vatcode'])) {
                    $line->addChild('vatcode', $lineData['vatcode']);
                }

                if (!empty($lineData['vatbase'])) {
                    $line->addChild('vatbase', $lineData['vatbase']);
                }
            }
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Obtenir le compte tiers
     */
    protected function getPartnerAccount($invoice): string
    {
        // Winbooks: 400000 pour clients, 440000 pour fournisseurs
        return $invoice->type === 'out' ? '400000' : '440000';
    }

    /**
     * Obtenir le compte de produit ou charge
     */
    protected function getRevenueOrExpenseAccount($invoice): string
    {
        return $invoice->type === 'out' ? '700000' : '600000';
    }

    /**
     * Obtenir le compte TVA
     */
    protected function getVatAccount(string $type): string
    {
        return $type === 'out' ? '451000' : '411000';
    }

    /**
     * Obtenir le code TVA Winbooks
     */
    protected function getVatCode($rate): string
    {
        return match ((int) $rate) {
            21 => '1',
            12 => '2',
            6 => '3',
            0 => '0',
            default => 'X',
        };
    }
}
