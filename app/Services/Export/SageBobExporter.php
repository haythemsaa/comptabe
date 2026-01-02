<?php

namespace App\Services\Export;

use App\Models\Company;
use Illuminate\Support\Collection;

/**
 * Exporter pour Sage BOB 50
 * Format CSV spécifique avec séparateur point-virgule
 *
 * Structure:
 * - Journal
 * - Date
 * - Pièce
 * - Compte général
 * - Compte tiers
 * - Libellé
 * - Débit
 * - Crédit
 * - TVA
 * - Code TVA
 */
class SageBobExporter extends BaseExporter
{
    /**
     * Export des factures vers Sage BOB
     */
    public function exportInvoices(Collection $invoices): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'Journal',
            'Date',
            'Pièce',
            'Compte Général',
            'Compte Tiers',
            'Libellé',
            'Débit',
            'Crédit',
            'TVA',
            'Code TVA',
            'Échéance',
        ];

        foreach ($invoices as $invoice) {
            // Journal: VTE pour ventes, ACH pour achats
            $journal = $invoice->type === 'out' ? 'VTE' : 'ACH';

            // Date comptable
            $date = $invoice->invoice_date->format('d/m/Y');

            // Numéro de pièce
            $piece = $invoice->invoice_number;

            // Libellé général
            $libelle = $this->sanitize("{$invoice->partner->name} - {$invoice->invoice_number}");

            // Échéance
            $echeance = $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '';

            // 1. Écriture client/fournisseur (total TTC)
            $compteTiers = $this->getPartnerAccount($invoice);

            $rows[] = [
                $journal,
                $date,
                $piece,
                '',  // Pas de compte général pour cette ligne
                $compteTiers,
                $libelle,
                $invoice->type === 'out' ? $this->formatAmount($invoice->total_incl_vat) : '',  // Débit si vente
                $invoice->type === 'in' ? $this->formatAmount($invoice->total_incl_vat) : '',   // Crédit si achat
                '',
                '',
                $echeance,
            ];

            // 2. Écritures par ligne de facture (HT par taux de TVA)
            $linesByVatRate = $invoice->lines->groupBy('vat_rate');

            foreach ($linesByVatRate as $vatRate => $lines) {
                $totalHT = $lines->sum('line_amount');
                $totalTVA = $lines->sum('vat_amount');

                // Compte de produit/charge (selon type facture)
                $compteGeneral = $this->getRevenueOrExpenseAccount($invoice, $vatRate);

                // Ligne HT
                $rows[] = [
                    $journal,
                    $date,
                    $piece,
                    $compteGeneral,
                    '',
                    $libelle . ' (' . $vatRate . '%)',
                    $invoice->type === 'in' ? $this->formatAmount($totalHT) : '',   // Débit si achat
                    $invoice->type === 'out' ? $this->formatAmount($totalHT) : '',  // Crédit si vente
                    '',
                    '',
                    '',
                ];

                // Ligne TVA
                if ($totalTVA > 0) {
                    $compteTVA = $this->getVatAccount($invoice->type, $vatRate);
                    $codeTVA = $this->getVatCode($vatRate);

                    $rows[] = [
                        $journal,
                        $date,
                        $piece,
                        $compteTVA,
                        '',
                        "TVA {$vatRate}%",
                        $invoice->type === 'in' ? $this->formatAmount($totalTVA) : '',   // Débit si achat
                        $invoice->type === 'out' ? $this->formatAmount($totalTVA) : '',  // Crédit si vente
                        $this->formatAmount($totalTVA),
                        $codeTVA,
                        '',
                    ];
                }
            }

            // Ligne vide entre factures pour lisibilité
            $rows[] = array_fill(0, 11, '');
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'sage-bob-export.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Export du journal comptable
     */
    public function exportJournal(Collection $invoices): array
    {
        // Pour Sage BOB, c'est le même format que les factures
        return $this->exportInvoices($invoices);
    }

    /**
     * Export TVA
     */
    public function exportVat(Collection $invoices): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'Code TVA',
            'Taux',
            'Base HT',
            'Montant TVA',
            'Grille',
        ];

        // Grouper par taux de TVA
        $vatSummary = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->lines as $line) {
                $rate = $line->vat_rate;

                if (!isset($vatSummary[$rate])) {
                    $vatSummary[$rate] = [
                        'base' => 0,
                        'vat' => 0,
                    ];
                }

                if ($invoice->type === 'out') {
                    // Ventes
                    $vatSummary[$rate]['base'] += $line->line_amount;
                    $vatSummary[$rate]['vat'] += $line->vat_amount;
                } else {
                    // Achats (en négatif)
                    $vatSummary[$rate]['base'] -= $line->line_amount;
                    $vatSummary[$rate]['vat'] -= $line->vat_amount;
                }
            }
        }

        // Générer les lignes
        foreach ($vatSummary as $rate => $amounts) {
            $codeTVA = $this->getVatCode($rate);
            $grille = $this->getVatGridCode($rate);

            $rows[] = [
                $codeTVA,
                $rate . '%',
                $this->formatAmount($amounts['base']),
                $this->formatAmount($amounts['vat']),
                $grille,
            ];
        }

        return [
            'content' => $this->arrayToCsv($rows, ';'),
            'filename' => 'sage-bob-tva.csv',
            'mime' => 'text/csv',
        ];
    }

    /**
     * Obtenir le compte tiers (client/fournisseur)
     */
    protected function getPartnerAccount($invoice): string
    {
        // Format Sage BOB: 400xxx pour clients, 440xxx pour fournisseurs
        $prefix = $invoice->type === 'out' ? '400' : '440';

        // Utiliser l'ID du partenaire ou son numéro de compte s'il existe
        $partnerId = str_pad($invoice->partner_id % 1000, 3, '0', STR_PAD_LEFT);

        return $prefix . $partnerId;
    }

    /**
     * Obtenir le compte de produit ou charge
     */
    protected function getRevenueOrExpenseAccount($invoice, $vatRate): string
    {
        if ($invoice->type === 'out') {
            // Compte de vente (70xxxx)
            return '700000';  // Ventes de marchandises par défaut
        } else {
            // Compte d'achat (60xxxx)
            return '600000';  // Achats de marchandises par défaut
        }
    }

    /**
     * Obtenir le compte de TVA
     */
    protected function getVatAccount(string $type, $rate): string
    {
        if ($type === 'out') {
            // TVA collectée (451xxx)
            return match ((int) $rate) {
                21 => '451100',  // TVA 21%
                12 => '451200',  // TVA 12%
                6 => '451300',   // TVA 6%
                default => '451000',
            };
        } else {
            // TVA déductible (411xxx)
            return match ((int) $rate) {
                21 => '411100',  // TVA déductible 21%
                12 => '411200',  // TVA déductible 12%
                6 => '411300',   // TVA déductible 6%
                default => '411000',
            };
        }
    }

    /**
     * Obtenir le code TVA
     */
    protected function getVatCode($rate): string
    {
        return match ((int) $rate) {
            21 => 'V21',
            12 => 'V12',
            6 => 'V06',
            0 => 'V00',
            default => 'VXX',
        };
    }

    /**
     * Obtenir le code de grille TVA
     */
    protected function getVatGridCode($rate): string
    {
        return match ((int) $rate) {
            21 => '03',  // Grille 03 pour 21%
            12 => '02',  // Grille 02 pour 12%
            6 => '01',   // Grille 01 pour 6%
            0 => '00',
            default => '99',
        };
    }
}
