<?php

namespace App\Services\Export;

use App\Models\Company;

/**
 * Classe de base pour tous les exporters comptables
 * Fournit les méthodes utilitaires communes
 */
abstract class BaseExporter
{
    protected Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Formater un montant pour l'export
     * Format belge : virgule comme séparateur décimal
     */
    protected function formatAmount(float $amount, int $decimals = 2): string
    {
        return number_format($amount, $decimals, ',', '');
    }

    /**
     * Nettoyer un texte pour l'export CSV
     * Supprimer caractères spéciaux, quotes, etc.
     */
    protected function sanitize(string $text): string
    {
        // Supprimer les retours à la ligne et tabulations
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);

        // Supprimer les quotes doubles
        $text = str_replace('"', "'", $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Convertir un tableau en CSV
     */
    protected function arrayToCsv(array $rows, string $delimiter = ';', string $enclosure = '"'): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($output, $row, $delimiter, $enclosure);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Ajouter BOM UTF-8 pour Excel
        return "\xEF\xBB\xBF" . $csv;
    }

    /**
     * Convertir un tableau en XML
     */
    protected function arrayToXml(array $data, string $rootElement = 'root'): string
    {
        $xml = new \SimpleXMLElement("<{$rootElement}/>");

        $this->arrayToXmlRecursive($data, $xml);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Convertir récursivement un tableau en XML
     */
    protected function arrayToXmlRecursive(array $data, \SimpleXMLElement &$xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Obtenir le journal comptable selon le type
     */
    protected function getJournalCode(string $invoiceType): string
    {
        return match ($invoiceType) {
            'out' => 'VTE',  // Journal des ventes
            'in' => 'ACH',   // Journal des achats
            default => 'DIV', // Journal divers
        };
    }

    /**
     * Obtenir le sens (débit/crédit) selon le type d'écriture
     */
    protected function getDebitCredit(string $invoiceType, string $accountType): array
    {
        // Retourne ['debit' => amount, 'credit' => 0] ou ['debit' => 0, 'credit' => amount]

        $rules = [
            'out' => [  // Facture de vente
                'customer' => 'debit',   // Client au débit
                'revenue' => 'credit',   // Produit au crédit
                'vat' => 'credit',       // TVA collectée au crédit
            ],
            'in' => [   // Facture d'achat
                'supplier' => 'credit',  // Fournisseur au crédit
                'expense' => 'debit',    // Charge au débit
                'vat' => 'debit',        // TVA déductible au débit
            ],
        ];

        $side = $rules[$invoiceType][$accountType] ?? 'debit';

        return $side;
    }

    /**
     * Formater une date selon le format demandé
     */
    protected function formatDate(\Carbon\Carbon $date, string $format = 'd/m/Y'): string
    {
        return $date->format($format);
    }

    /**
     * Obtenir le mois comptable (format MMYYYY)
     */
    protected function getAccountingPeriod(\Carbon\Carbon $date): string
    {
        return $date->format('mY');
    }

    /**
     * Générer un numéro de pièce unique
     */
    protected function generatePieceNumber(string $journal, \Carbon\Carbon $date, int $sequence): string
    {
        return sprintf('%s%s%04d', $journal, $date->format('ym'), $sequence);
    }

    /**
     * Convertir un code pays en numérique (ISO)
     */
    protected function getCountryNumericCode(string $countryCode): string
    {
        $codes = [
            'BE' => '056',
            'FR' => '250',
            'NL' => '528',
            'DE' => '276',
            'LU' => '442',
            'GB' => '826',
            'IT' => '380',
            'ES' => '724',
            'PT' => '620',
            'US' => '840',
        ];

        return $codes[strtoupper($countryCode)] ?? '999';
    }

    /**
     * Vérifier si un montant est débiteur
     */
    protected function isDebit(float $amount): bool
    {
        return $amount > 0;
    }

    /**
     * Obtenir la valeur absolue d'un montant
     */
    protected function absoluteAmount(float $amount): float
    {
        return abs($amount);
    }
}
