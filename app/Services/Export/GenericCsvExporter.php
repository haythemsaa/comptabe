<?php

namespace App\Services\Export;

use Illuminate\Support\Collection;

/**
 * Exporter CSV générique
 * Format simple et universel compatible avec tous les logiciels
 */
class GenericCsvExporter extends BaseExporter
{
    /**
     * Export des factures en CSV générique
     */
    public function exportInvoices(Collection $invoices): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'N° Facture',
            'Date',
            'Échéance',
            'Type',
            'Client/Fournisseur',
            'N° TVA',
            'Description',
            'Montant HT',
            'TVA',
            'Montant TTC',
            'Statut',
            'Peppol',
        ];

        foreach ($invoices as $invoice) {
            $rows[] = [
                $invoice->invoice_number,
                $invoice->invoice_date->format('d/m/Y'),
                $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '',
                $invoice->type === 'out' ? 'Vente' : 'Achat',
                $this->sanitize($invoice->partner->name),
                $invoice->partner->vat_number ?? '',
                $this->sanitize($invoice->notes ?? 'Facture ' . $invoice->invoice_number),
                $this->formatAmount($invoice->total_excl_vat),
                $this->formatAmount($invoice->total_vat),
                $this->formatAmount($invoice->total_incl_vat),
                $invoice->status_label,
                $invoice->peppol_sent_at ? 'Oui' : 'Non',
            ];
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'factures-export.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Export du journal
     */
    public function exportJournal(Collection $invoices): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'Date',
            'Journal',
            'N° Pièce',
            'Compte',
            'Libellé',
            'Débit',
            'Crédit',
        ];

        foreach ($invoices as $invoice) {
            $journal = $invoice->type === 'out' ? 'VTE' : 'ACH';
            $date = $invoice->invoice_date->format('d/m/Y');

            // Ligne client/fournisseur
            $rows[] = [
                $date,
                $journal,
                $invoice->invoice_number,
                $invoice->type === 'out' ? '400000' : '440000',
                $this->sanitize($invoice->partner->name),
                $invoice->type === 'out' ? $this->formatAmount($invoice->total_incl_vat) : '',
                $invoice->type === 'in' ? $this->formatAmount($invoice->total_incl_vat) : '',
            ];

            // Lignes de produits/charges
            foreach ($invoice->lines->groupBy('vat_rate') as $rate => $lines) {
                $total = $lines->sum('line_amount');

                $rows[] = [
                    $date,
                    $journal,
                    $invoice->invoice_number,
                    $invoice->type === 'out' ? '700000' : '600000',
                    'TVA ' . $rate . '%',
                    $invoice->type === 'in' ? $this->formatAmount($total) : '',
                    $invoice->type === 'out' ? $this->formatAmount($total) : '',
                ];
            }
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'journal-export.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Export TVA
     */
    public function exportVat(Collection $invoices): array
    {
        $rows = [];
        $rows[] = ['Taux TVA', 'Base HT Ventes', 'TVA Ventes', 'Base HT Achats', 'TVA Achats', 'Solde'];

        $vatData = $this->calculateVatSummary($invoices);

        foreach ($vatData as $rate => $data) {
            $rows[] = [
                $rate . '%',
                $this->formatAmount($data['sales_base']),
                $this->formatAmount($data['sales_vat']),
                $this->formatAmount($data['purchases_base']),
                $this->formatAmount($data['purchases_vat']),
                $this->formatAmount($data['sales_vat'] - $data['purchases_vat']),
            ];
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'tva-export.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Export balance
     */
    public function exportBalance(Collection $invoices): array
    {
        $rows = [];
        $rows[] = ['Compte', 'Libellé', 'Débit', 'Crédit', 'Solde'];

        // Calculer les soldes par compte
        $accounts = [];

        foreach ($invoices as $invoice) {
            $partner = $invoice->type === 'out' ? '400000' : '440000';
            $revenue = $invoice->type === 'out' ? '700000' : '600000';

            // Compte tiers
            if (!isset($accounts[$partner])) {
                $accounts[$partner] = ['label' => 'Clients/Fournisseurs', 'debit' => 0, 'credit' => 0];
            }

            if ($invoice->type === 'out') {
                $accounts[$partner]['debit'] += $invoice->total_incl_vat;
            } else {
                $accounts[$partner]['credit'] += $invoice->total_incl_vat;
            }

            // Compte produit/charge
            if (!isset($accounts[$revenue])) {
                $accounts[$revenue] = ['label' => 'Ventes/Achats', 'debit' => 0, 'credit' => 0];
            }

            if ($invoice->type === 'out') {
                $accounts[$revenue]['credit'] += $invoice->total_excl_vat;
            } else {
                $accounts[$revenue]['debit'] += $invoice->total_excl_vat;
            }
        }

        foreach ($accounts as $account => $data) {
            $solde = $data['debit'] - $data['credit'];

            $rows[] = [
                $account,
                $data['label'],
                $this->formatAmount($data['debit']),
                $this->formatAmount($data['credit']),
                $this->formatAmount($solde),
            ];
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'balance-export.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Calculer le résumé TVA
     */
    protected function calculateVatSummary(Collection $invoices): array
    {
        $summary = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $rate = $line->vat_rate;

                if (!isset($summary[$rate])) {
                    $summary[$rate] = [
                        'sales_base' => 0,
                        'sales_vat' => 0,
                        'purchases_base' => 0,
                        'purchases_vat' => 0,
                    ];
                }

                if ($invoice->type === 'out') {
                    $summary[$rate]['sales_base'] += $line->line_amount;
                    $summary[$rate]['sales_vat'] += $line->vat_amount;
                } else {
                    $summary[$rate]['purchases_base'] += $line->line_amount;
                    $summary[$rate]['purchases_vat'] += $line->vat_amount;
                }
            }
        }

        ksort($summary);

        return $summary;
    }
}
