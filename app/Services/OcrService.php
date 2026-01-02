<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * Service OCR pour extraire les données des factures scannées
 * Supporte Google Vision API ou extraction basique
 */
class OcrService
{
    /**
     * Extraire les données d'une facture depuis une image/PDF
     */
    public function extractInvoiceData(UploadedFile $file): array
    {
        // Sauvegarder temporairement le fichier
        $path = $file->store('temp/ocr', 'local');
        $fullPath = storage_path('app/' . $path);

        try {
            // Optimiser l'image pour l'OCR
            $processedImage = $this->preprocessImage($fullPath);

            // Extraire le texte avec Google Vision API si configuré
            if ($this->isGoogleVisionEnabled()) {
                $text = $this->extractTextWithGoogleVision($processedImage);
            } else {
                // Mode démo : simulation d'extraction OCR
                $text = $this->simulateOcrExtraction($file->getClientOriginalName());
            }

            // Parser les données de la facture
            $data = $this->parseInvoiceData($text);

            // Ajouter les métadonnées
            $data['original_filename'] = $file->getClientOriginalName();
            $data['ocr_confidence'] = $this->calculateConfidence($data);
            $data['needs_review'] = $data['ocr_confidence'] < 0.8;

            return $data;

        } finally {
            // Nettoyer les fichiers temporaires
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Prétraiter l'image pour améliorer la reconnaissance OCR
     */
    protected function preprocessImage(string $path): string
    {
        try {
            $image = Image::read($path);

            // Convertir en niveaux de gris
            $image->greyscale();

            // Augmenter le contraste
            $image->contrast(30);

            // Augmenter la luminosité si nécessaire
            $image->brightness(10);

            // Sauvegarder l'image traitée
            $processedPath = str_replace('.', '_processed.', $path);
            $image->save($processedPath);

            return $processedPath;
        } catch (\Exception $e) {
            // Si le traitement échoue, retourner l'original
            \Log::warning('Image preprocessing failed', ['error' => $e->getMessage()]);
            return $path;
        }
    }

    /**
     * Vérifier si Google Vision API est configurée
     */
    protected function isGoogleVisionEnabled(): bool
    {
        return !empty(config('services.google_vision.credentials'));
    }

    /**
     * Extraire le texte avec Google Vision API
     */
    protected function extractTextWithGoogleVision(string $imagePath): string
    {
        // TODO: Implémenter l'appel à Google Vision API
        // Pour l'instant, retourner une simulation
        return $this->simulateOcrExtraction(basename($imagePath));
    }

    /**
     * Simuler l'extraction OCR pour la démo
     * En production, ceci serait remplacé par un vrai OCR
     */
    protected function simulateOcrExtraction(string $filename): string
    {
        // Simulation de texte extrait d'une facture typique
        return <<<TEXT
FACTURE N° INV-2025-001234

Date: 15/12/2025
Date d'échéance: 14/01/2026

FOURNISSEUR DEMO SPRL
Rue de la Démo 123
1000 Bruxelles
BE 0123.456.789

CLIENT
Votre Entreprise
Avenue Test 456
1050 Bruxelles

DESCRIPTION                          MONTANT
Prestations de service              1.500,00 €
Matériel informatique                 450,50 €

Sous-total HTVA:                    1.950,50 €
TVA 21%:                              409,61 €
TOTAL TTC:                          2.360,11 €

À payer avant le 14/01/2026
TEXT;
    }

    /**
     * Parser les données de la facture depuis le texte OCR
     */
    protected function parseInvoiceData(string $text): array
    {
        $data = [
            'invoice_number' => null,
            'invoice_date' => null,
            'due_date' => null,
            'supplier_name' => null,
            'supplier_vat' => null,
            'subtotal' => null,
            'vat_amount' => null,
            'total_amount' => null,
            'vat_rate' => null,
            'currency' => 'EUR',
            'extracted_text' => $text,
        ];

        // Extraire le numéro de facture
        if (preg_match('/(?:FACTURE|INVOICE|N°|NO|#)\s*:?\s*([A-Z0-9\-\/]+)/i', $text, $matches)) {
            $data['invoice_number'] = trim($matches[1]);
        }

        // Extraire la date de facture
        if (preg_match('/(?:Date|du)\s*:?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i', $text, $matches)) {
            $data['invoice_date'] = $this->parseDate($matches[1]);
        }

        // Extraire la date d'échéance
        if (preg_match('/(?:échéance|due date|à payer avant)\s*:?\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i', $text, $matches)) {
            $data['due_date'] = $this->parseDate($matches[1]);
        }

        // Extraire le nom du fournisseur (première ligne après les métadonnées)
        $lines = explode("\n", $text);
        foreach ($lines as $index => $line) {
            $line = trim($line);
            // Chercher après "FACTURE" et avant "CLIENT"
            if (preg_match('/[A-Z]{2,}.*(?:SPRL|SA|SRL|ASBL|BV|NV)/i', $line) && !stripos($line, 'CLIENT')) {
                $data['supplier_name'] = $line;
                break;
            }
        }

        // Extraire le numéro de TVA
        if (preg_match('/BE\s*[\s\.]?(\d{4})[\s\.]?(\d{3})[\s\.]?(\d{3})/i', $text, $matches)) {
            $data['supplier_vat'] = 'BE' . $matches[1] . '.' . $matches[2] . '.' . $matches[3];
        }

        // Extraire le montant total TTC
        if (preg_match('/(?:TOTAL|TTC|Grand Total)\s*:?\s*([0-9\.,]+)\s*€?/i', $text, $matches)) {
            $data['total_amount'] = $this->parseAmount($matches[1]);
        }

        // Extraire le sous-total HTVA
        if (preg_match('/(?:Sous-total|HTVA|Subtotal|HT)\s*:?\s*([0-9\.,]+)\s*€?/i', $text, $matches)) {
            $data['subtotal'] = $this->parseAmount($matches[1]);
        }

        // Extraire le montant de TVA
        if (preg_match('/TVA\s*(?:(\d+)%)?\s*:?\s*([0-9\.,]+)\s*€?/i', $text, $matches)) {
            if (!empty($matches[1])) {
                $data['vat_rate'] = floatval($matches[1]);
            }
            $data['vat_amount'] = $this->parseAmount($matches[2]);
        }

        // Calculer les montants manquants si possible
        if ($data['total_amount'] && $data['vat_amount'] && !$data['subtotal']) {
            $data['subtotal'] = $data['total_amount'] - $data['vat_amount'];
        }

        if ($data['subtotal'] && $data['vat_amount'] && !$data['vat_rate']) {
            $data['vat_rate'] = round(($data['vat_amount'] / $data['subtotal']) * 100, 0);
        }

        return $data;
    }

    /**
     * Parser une date au format européen
     */
    protected function parseDate(string $dateString): ?string
    {
        // Nettoyer la chaîne
        $dateString = trim($dateString);

        // Essayer différents formats
        $formats = ['d/m/Y', 'd-m-Y', 'd.m.Y', 'd/m/y', 'd-m-y', 'd.m.y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Parser un montant avec séparateurs européens
     */
    protected function parseAmount(string $amountString): ?float
    {
        // Enlever les espaces et les symboles monétaires
        $amountString = preg_replace('/[€$\s]/', '', $amountString);

        // Gérer le format européen (1.234,56) et anglo-saxon (1,234.56)
        if (substr_count($amountString, '.') > 1) {
            // Format avec plusieurs points (séparateurs de milliers)
            $amountString = str_replace('.', '', $amountString);
            $amountString = str_replace(',', '.', $amountString);
        } elseif (substr_count($amountString, ',') > 1) {
            // Format avec plusieurs virgules (séparateurs de milliers)
            $amountString = str_replace(',', '', $amountString);
        } elseif (strpos($amountString, ',') !== false && strpos($amountString, '.') !== false) {
            // Les deux présents : déterminer lequel est le séparateur décimal
            if (strrpos($amountString, ',') > strrpos($amountString, '.')) {
                // Format européen: 1.234,56
                $amountString = str_replace('.', '', $amountString);
                $amountString = str_replace(',', '.', $amountString);
            } else {
                // Format anglo-saxon: 1,234.56
                $amountString = str_replace(',', '', $amountString);
            }
        } elseif (strpos($amountString, ',') !== false) {
            // Seulement une virgule : format européen
            $amountString = str_replace(',', '.', $amountString);
        }

        return floatval($amountString);
    }

    /**
     * Calculer le score de confiance de l'extraction
     */
    protected function calculateConfidence(array $data): float
    {
        $score = 0.0;
        $maxScore = 0;

        $checks = [
            'invoice_number' => 0.15,
            'invoice_date' => 0.15,
            'supplier_name' => 0.15,
            'supplier_vat' => 0.10,
            'total_amount' => 0.25,
            'vat_amount' => 0.10,
            'subtotal' => 0.10,
        ];

        foreach ($checks as $field => $weight) {
            $maxScore += $weight;
            if (!empty($data[$field])) {
                $score += $weight;
            }
        }

        return $maxScore > 0 ? round($score / $maxScore, 2) : 0.0;
    }

    /**
     * Valider une extraction OCR
     */
    public function validateExtraction(array $data): array
    {
        $errors = [];

        if (empty($data['total_amount'])) {
            $errors[] = 'Le montant total est requis';
        }

        if (empty($data['invoice_date'])) {
            $errors[] = 'La date de facture est requise';
        }

        if (empty($data['supplier_name'])) {
            $errors[] = 'Le nom du fournisseur est requis';
        }

        // Vérifier la cohérence des montants
        if ($data['subtotal'] && $data['vat_amount'] && $data['total_amount']) {
            $calculated = $data['subtotal'] + $data['vat_amount'];
            $diff = abs($calculated - $data['total_amount']);

            if ($diff > 0.02) { // Tolérance de 2 centimes
                $errors[] = 'Incohérence dans les montants (sous-total + TVA ≠ total)';
            }
        }

        return $errors;
    }
}
