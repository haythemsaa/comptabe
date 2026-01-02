<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\InvoiceTemplate;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class CreateInvoiceTemplateTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_invoice_template';
    }

    public function getDescription(): string
    {
        return 'Creates a customizable invoice template with branding, layout, colors, and formatting options. Perfect for creating professional invoice designs.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Template name (e.g., "Professionnel Bleu", "Moderne Rouge")',
                ],
                'description' => [
                    'type' => 'string',
                    'description' => 'Optional description of the template',
                ],
                'layout' => [
                    'type' => 'string',
                    'enum' => ['classic', 'modern', 'minimal', 'professional'],
                    'description' => 'Layout style',
                ],
                'primary_color' => [
                    'type' => 'string',
                    'description' => 'Primary color in hex format (e.g., #3B82F6 for blue)',
                ],
                'secondary_color' => [
                    'type' => 'string',
                    'description' => 'Secondary color in hex format',
                ],
                'font_family' => [
                    'type' => 'string',
                    'enum' => ['Inter', 'Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Merriweather', 'Playfair Display'],
                    'description' => 'Font family to use',
                ],
                'show_logo' => [
                    'type' => 'boolean',
                    'description' => 'Show company logo on invoice',
                ],
                'header_position' => [
                    'type' => 'string',
                    'enum' => ['left', 'center', 'right'],
                    'description' => 'Header position',
                ],
                'payment_terms' => [
                    'type' => 'string',
                    'description' => 'Default payment terms text',
                ],
                'footer_text' => [
                    'type' => 'string',
                    'description' => 'Footer text',
                ],
                'is_default' => [
                    'type' => 'boolean',
                    'description' => 'Set as default template',
                ],
            ],
            'required' => ['name', 'layout'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return false;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Create template
        $template = InvoiceTemplate::create([
            'company_id' => $context->company->id,
            'name' => $input['name'],
            'description' => $input['description'] ?? null,
            'layout' => $input['layout'],
            'primary_color' => $input['primary_color'] ?? '#3B82F6',
            'secondary_color' => $input['secondary_color'] ?? '#1E40AF',
            'font_family' => $input['font_family'] ?? 'Inter',
            'header_position' => $input['header_position'] ?? 'left',
            'show_logo' => $input['show_logo'] ?? true,
            'show_company_info' => true,
            'show_footer' => true,
            'payment_terms' => $input['payment_terms'] ?? 'Paiement sous 30 jours.',
            'footer_text' => $input['footer_text'] ?? null,
            'is_default' => $input['is_default'] ?? false,
            'is_active' => true,
        ]);

        // If set as default, update other templates
        if ($template->is_default) {
            $template->setAsDefault();
        }

        return [
            'success' => true,
            'message' => "Template de facture créé : {$template->name}",
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'layout' => $template->layout,
                'primary_color' => $template->primary_color,
                'secondary_color' => $template->secondary_color,
                'font_family' => $template->font_family,
                'is_default' => $template->is_default,
            ],
            'preview' => "Le template utilise un design {$template->layout} avec la couleur primaire {$template->primary_color}",
            'next_steps' => [
                'Testez le template en créant une facture',
                'Ajoutez votre logo si nécessaire',
                'Personnalisez le texte du pied de page',
                'Définissez comme template par défaut si souhaité',
            ],
        ];
    }
}
