<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;

class InvoiceTemplateService
{
    /**
     * Available invoice templates with their configurations
     */
    public const TEMPLATES = [
        'classic' => [
            'name' => 'Classic',
            'description' => 'Design professionnel traditionnel avec lignes épurées',
            'preview' => 'templates/previews/classic.png',
            'features' => ['En-tête sobre', 'Tableau classique', 'Style formel'],
            'default_colors' => ['primary' => '#1e3a5f', 'secondary' => '#4a5568'],
        ],
        'modern' => [
            'name' => 'Modern',
            'description' => 'Design contemporain avec formes géométriques colorées',
            'preview' => 'templates/previews/modern.png',
            'features' => ['Bande colorée', 'Typographie moderne', 'Accents visuels'],
            'default_colors' => ['primary' => '#6366f1', 'secondary' => '#1e293b'],
        ],
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Design épuré avec beaucoup d\'espace blanc',
            'preview' => 'templates/previews/minimal.png',
            'features' => ['Ultra-clean', 'Espaces généreux', 'Focus contenu'],
            'default_colors' => ['primary' => '#0f172a', 'secondary' => '#64748b'],
        ],
        'creative' => [
            'name' => 'Creative',
            'description' => 'Design audacieux avec éléments graphiques distinctifs',
            'preview' => 'templates/previews/creative.png',
            'features' => ['Formes diagonales', 'Couleurs vives', 'Style unique'],
            'default_colors' => ['primary' => '#f59e0b', 'secondary' => '#1f2937'],
        ],
        'corporate' => [
            'name' => 'Corporate',
            'description' => 'Design institutionnel pour entreprises établies',
            'preview' => 'templates/previews/corporate.png',
            'features' => ['Style business', 'Sobre et élégant', 'Très professionnel'],
            'default_colors' => ['primary' => '#0369a1', 'secondary' => '#334155'],
        ],
        'elegant' => [
            'name' => 'Elegant',
            'description' => 'Design raffiné avec touches dorées',
            'preview' => 'templates/previews/elegant.png',
            'features' => ['Bordures fines', 'Style luxueux', 'Finitions soignées'],
            'default_colors' => ['primary' => '#78716c', 'secondary' => '#292524'],
        ],
    ];

    /**
     * Get all available templates
     */
    public static function getTemplates(): array
    {
        return self::TEMPLATES;
    }

    /**
     * Get a specific template configuration
     */
    public static function getTemplate(string $templateKey): ?array
    {
        return self::TEMPLATES[$templateKey] ?? null;
    }

    /**
     * Get template view path for PDF generation
     */
    public static function getTemplatePath(string $templateKey): string
    {
        $validTemplates = array_keys(self::TEMPLATES);

        if (!in_array($templateKey, $validTemplates)) {
            $templateKey = 'modern'; // Default fallback
        }

        return "invoices.templates.{$templateKey}";
    }

    /**
     * Get company's selected template with colors
     */
    public static function getCompanyTemplate(Company $company): array
    {
        $templateKey = $company->invoice_template ?? 'modern';
        $template = self::getTemplate($templateKey) ?? self::getTemplate('modern');

        return [
            'key' => $templateKey,
            'name' => $template['name'],
            'view' => self::getTemplatePath($templateKey),
            'colors' => [
                'primary' => $company->invoice_primary_color ?? $template['default_colors']['primary'],
                'secondary' => $company->invoice_secondary_color ?? $template['default_colors']['secondary'],
            ],
            'settings' => $company->invoice_template_settings ?? [],
        ];
    }

    /**
     * Generate CSS variables for template colors
     */
    public static function generateColorStyles(array $colors): string
    {
        $primary = $colors['primary'] ?? '#6366f1';
        $secondary = $colors['secondary'] ?? '#1e293b';

        // Generate lighter/darker variants
        $primaryLight = self::adjustBrightness($primary, 40);
        $primaryDark = self::adjustBrightness($primary, -20);

        return "
            :root {
                --color-primary: {$primary};
                --color-primary-light: {$primaryLight};
                --color-primary-dark: {$primaryDark};
                --color-secondary: {$secondary};
                --color-primary-rgb: " . self::hexToRgb($primary) . ";
            }
        ";
    }

    /**
     * Adjust color brightness
     */
    private static function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Convert hex to RGB string
     */
    private static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return sprintf(
            '%d, %d, %d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    /**
     * Get template preview data for selection UI
     */
    public static function getTemplatePreviewData(): array
    {
        $templates = [];

        foreach (self::TEMPLATES as $key => $template) {
            $templates[] = [
                'key' => $key,
                'name' => $template['name'],
                'description' => $template['description'],
                'preview' => $template['preview'],
                'features' => $template['features'],
                'colors' => $template['default_colors'],
            ];
        }

        return $templates;
    }

    /**
     * Generate sample invoice data for template preview
     */
    public static function getSampleInvoiceData(Company $company): array
    {
        // Mock partner
        $partner = new \stdClass();
        $partner->name = 'Entreprise Demo SPRL';
        $partner->street = 'Rue de la Demo';
        $partner->house_number = '123';
        $partner->postal_code = '1000';
        $partner->city = 'Bruxelles';
        $partner->country_code = 'BE';
        $partner->vat_number = 'BE0123456789';

        // Mock lines
        $lines = [];

        $line1 = new \stdClass();
        $line1->description = 'Consultation et analyse des besoins';
        $line1->quantity = 8;
        $line1->unit_price = 125.00;
        $line1->vat_rate = 21;
        $line1->discount_percent = 0;
        $line1->total_excl_vat = 1000.00;
        $lines[] = $line1;

        $line2 = new \stdClass();
        $line2->description = 'Developpement sur mesure';
        $line2->quantity = 4;
        $line2->unit_price = 150.00;
        $line2->vat_rate = 21;
        $line2->discount_percent = 10;
        $line2->total_excl_vat = 540.00;
        $lines[] = $line2;

        $line3 = new \stdClass();
        $line3->description = 'Formation utilisateurs (1 journee)';
        $line3->quantity = 1;
        $line3->unit_price = 450.00;
        $line3->vat_rate = 21;
        $line3->discount_percent = 0;
        $line3->total_excl_vat = 450.00;
        $lines[] = $line3;

        // Create a mock invoice object with vatSummary method using anonymous class
        $invoice = new class($partner, collect($lines)) {
            public string $invoice_number = 'PREVIEW-001';
            public $invoice_date;
            public $due_date;
            public string $reference = 'Commande #12345';
            public string $structured_communication = '+++123/4567/89012+++';
            public string $status = 'validated';
            public string $status_label = 'Valide';
            public float $total_excl_vat = 1500.00;
            public float $total_incl_vat = 1815.00;
            public float $amount_due = 1815.00;
            public string $notes = 'Merci pour votre confiance. Paiement sous 30 jours.';
            public $peppol_sent_at = null;
            public $partner;
            public $lines;

            public function __construct($partner, $lines) {
                $this->partner = $partner;
                $this->lines = $lines;
                $this->invoice_date = now();
                $this->due_date = now()->addDays(30);
            }

            public function vatSummary(): array {
                return [21 => 315.00];
            }
        };

        return [
            'invoice' => $invoice,
            'company' => $company,
        ];
    }
}
