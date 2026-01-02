<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Service pour générer des QR codes EPC (European Payments Council) SEPA
 * Conforme au standard EPC QR Code Guidelines Version 2.1
 *
 * Format: BCD (Binary Coded Decimal) pour les applications de paiement bancaire
 */
class EpcQrCodeService
{
    /**
     * Générer un QR Code EPC SEPA pour un paiement
     *
     * @param string $beneficiaryName Nom du bénéficiaire (max 70 caractères)
     * @param string $iban IBAN du bénéficiaire
     * @param float $amount Montant en EUR
     * @param string $reference Communication structurée ou libre
     * @param string|null $bic BIC du bénéficiaire (optionnel mais recommandé)
     * @param string|null $remittanceInfo Information supplémentaire (max 140 caractères)
     * @return string Base64 encoded QR code image
     */
    public function generateEpcQrCode(
        string $beneficiaryName,
        string $iban,
        float $amount,
        string $reference = '',
        ?string $bic = null,
        ?string $remittanceInfo = null
    ): string {
        // Nettoyer et formater l'IBAN (supprimer espaces)
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Formater le BIC si fourni
        $bic = $bic ? strtoupper(str_replace(' ', '', $bic)) : '';

        // Formater le montant (EUR avec 2 décimales, max 999999999.99)
        $formattedAmount = number_format($amount, 2, '.', '');

        // Construire le payload EPC selon le standard
        $epcData = $this->buildEpcPayload(
            $beneficiaryName,
            $iban,
            $formattedAmount,
            $reference,
            $bic,
            $remittanceInfo
        );

        // Générer le QR code
        // Size: 200x200 pixels minimum pour une bonne lisibilité
        // Error correction: M (15% - bon compromis)
        $qrCode = QrCode::format('png')
            ->size(200)
            ->errorCorrection('M')
            ->encoding('UTF-8')
            ->generate($epcData);

        // Retourner en base64 pour inclusion dans le PDF
        return base64_encode($qrCode);
    }

    /**
     * Construire le payload EPC selon le standard
     * Format:
     * Line 1: BCD (Service Tag)
     * Line 2: 002 (Version)
     * Line 3: 1 (Character Set: UTF-8)
     * Line 4: SCT (Identification: SEPA Credit Transfer)
     * Line 5: BIC (optionnel)
     * Line 6: Beneficiary Name
     * Line 7: Beneficiary IBAN
     * Line 8: Amount (EUR)
     * Line 9-10: Purpose + Structured Reference (optionnels)
     * Line 11: Remittance Information (optionnel)
     * Line 12: Beneficiary to Originator Information (optionnel)
     */
    protected function buildEpcPayload(
        string $beneficiaryName,
        string $iban,
        string $amount,
        string $reference,
        ?string $bic,
        ?string $remittanceInfo
    ): string {
        $lines = [
            'BCD',                              // Service Tag
            '002',                              // Version
            '1',                                // Character set (UTF-8)
            'SCT',                              // Identification (SEPA Credit Transfer)
            $bic ?? '',                         // BIC (optionnel)
            $this->sanitize($beneficiaryName, 70),  // Beneficiary name (max 70 chars)
            $iban,                              // Beneficiary account (IBAN)
            'EUR' . $amount,                    // Amount with currency
            '',                                 // Purpose (optionnel, vide)
            $this->sanitize($reference, 35),    // Structured reference (max 35 chars)
            $this->sanitize($remittanceInfo ?? '', 140), // Remittance info (max 140 chars)
            '',                                 // Beneficiary to Originator info (optionnel)
        ];

        // Joindre avec retours à la ligne
        return implode("\n", $lines);
    }

    /**
     * Nettoyer et tronquer une chaîne selon les spécifications EPC
     * Supprime les caractères non autorisés et limite la longueur
     */
    protected function sanitize(string $text, int $maxLength): string
    {
        // Supprimer les retours à la ligne et tabulations
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);

        // Tronquer à la longueur maximale
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength);
        }

        return $text;
    }

    /**
     * Générer un QR code pour une facture (helper)
     *
     * @param \App\Models\Invoice $invoice
     * @return string Base64 encoded QR code
     */
    public function generateForInvoice($invoice): string
    {
        $company = $invoice->company;

        return $this->generateEpcQrCode(
            beneficiaryName: $company->name,
            iban: $company->default_iban ?? '',
            amount: $invoice->amount_due,
            reference: $invoice->structured_communication ?? '',
            bic: $company->default_bic,
            remittanceInfo: 'Facture ' . $invoice->invoice_number
        );
    }

    /**
     * Valider un IBAN
     */
    public function validateIban(string $iban): bool
    {
        // Supprimer les espaces
        $iban = strtoupper(str_replace(' ', '', $iban));

        // Vérifier la longueur (15-34 caractères)
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        // Vérifier le format (2 lettres + 2 chiffres + code)
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }

        // Algorithme de validation IBAN (modulo 97)
        $iban = substr($iban, 4) . substr($iban, 0, 4);
        $iban = str_replace(
            range('A', 'Z'),
            range(10, 35),
            $iban
        );

        return bcmod($iban, '97') === '1';
    }
}
