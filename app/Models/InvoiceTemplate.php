<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InvoiceTemplate extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'is_default',
        'is_active',
        'logo_path',
        'primary_color',
        'secondary_color',
        'font_family',
        'layout',
        'header_position',
        'show_logo',
        'show_company_info',
        'show_footer',
        'header_text',
        'footer_text',
        'payment_terms',
        'notes',
        'legal_text',
        'show_item_code',
        'show_item_description',
        'show_quantity',
        'show_unit_price',
        'show_discount',
        'show_vat_rate',
        'show_subtotal',
        'date_format',
        'number_format',
        'currency_position',
        'custom_fields',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'show_logo' => 'boolean',
        'show_company_info' => 'boolean',
        'show_footer' => 'boolean',
        'show_item_code' => 'boolean',
        'show_item_description' => 'boolean',
        'show_quantity' => 'boolean',
        'show_unit_price' => 'boolean',
        'show_discount' => 'boolean',
        'show_vat_rate' => 'boolean',
        'show_subtotal' => 'boolean',
        'custom_fields' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot method to handle slug generation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function ($template) {
            // If setting as default, unset others
            if ($template->is_default && $template->isDirty('is_default')) {
                self::where('company_id', $template->company_id)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Relationships
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'invoice_template_id');
    }

    public function recurringInvoices()
    {
        return $this->hasMany(RecurringInvoice::class, 'invoice_template_id');
    }

    /**
     * Scopes
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Methods
     */

    /**
     * Set this template as default
     */
    public function setAsDefault(): void
    {
        // Unset all other defaults for this company
        self::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get the full configuration array for rendering
     */
    public function getConfig(): array
    {
        return [
            'name' => $this->name,
            'layout' => $this->layout,
            'colors' => [
                'primary' => $this->primary_color,
                'secondary' => $this->secondary_color,
            ],
            'font' => $this->font_family,
            'header' => [
                'position' => $this->header_position,
                'text' => $this->header_text,
                'show_logo' => $this->show_logo,
                'show_company_info' => $this->show_company_info,
                'logo_path' => $this->logo_path,
            ],
            'footer' => [
                'show' => $this->show_footer,
                'text' => $this->footer_text,
            ],
            'content' => [
                'payment_terms' => $this->payment_terms,
                'notes' => $this->notes,
                'legal_text' => $this->legal_text,
            ],
            'columns' => [
                'item_code' => $this->show_item_code,
                'description' => $this->show_item_description,
                'quantity' => $this->show_quantity,
                'unit_price' => $this->show_unit_price,
                'discount' => $this->show_discount,
                'vat_rate' => $this->show_vat_rate,
                'subtotal' => $this->show_subtotal,
            ],
            'formatting' => [
                'date_format' => $this->date_format,
                'number_format' => $this->number_format,
                'currency_position' => $this->currency_position,
            ],
            'custom_fields' => $this->custom_fields ?? [],
        ];
    }

    /**
     * Duplicate this template
     */
    public function duplicate(string $newName): self
    {
        $attributes = $this->toArray();

        // Remove unique/auto fields
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);

        // Update name and slug
        $attributes['name'] = $newName;
        $attributes['slug'] = Str::slug($newName);
        $attributes['is_default'] = false;

        return self::create($attributes);
    }

    /**
     * Get available layouts
     */
    public static function getAvailableLayouts(): array
    {
        return [
            'classic' => [
                'name' => 'Classique',
                'description' => 'Design traditionnel avec en-tête à gauche',
                'preview' => '/images/templates/classic.png',
            ],
            'modern' => [
                'name' => 'Moderne',
                'description' => 'Design contemporain avec couleurs accent',
                'preview' => '/images/templates/modern.png',
            ],
            'minimal' => [
                'name' => 'Minimaliste',
                'description' => 'Design épuré et sobre',
                'preview' => '/images/templates/minimal.png',
            ],
            'professional' => [
                'name' => 'Professionnel',
                'description' => 'Design corporate avec bandes colorées',
                'preview' => '/images/templates/professional.png',
            ],
        ];
    }

    /**
     * Get available fonts
     */
    public static function getAvailableFonts(): array
    {
        return [
            'Inter' => 'Inter (Moderne, sans-serif)',
            'Roboto' => 'Roboto (Google, sans-serif)',
            'Open Sans' => 'Open Sans (Lisible, sans-serif)',
            'Lato' => 'Lato (Élégant, sans-serif)',
            'Montserrat' => 'Montserrat (Géométrique, sans-serif)',
            'Merriweather' => 'Merriweather (Classique, serif)',
            'Playfair Display' => 'Playfair Display (Élégant, serif)',
        ];
    }

    /**
     * Create default template for a company
     */
    public static function createDefault(Company $company): self
    {
        return self::create([
            'company_id' => $company->id,
            'name' => 'Template par défaut',
            'slug' => 'default',
            'description' => 'Template créé automatiquement',
            'is_default' => true,
            'is_active' => true,
            'layout' => 'classic',
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'font_family' => 'Inter',
            'header_position' => 'left',
            'show_logo' => true,
            'show_company_info' => true,
            'show_footer' => true,
            'payment_terms' => 'Paiement sous 30 jours. En cas de retard, des intérêts de retard au taux légal de 10% seront dus.',
            'legal_text' => 'TVA BE ' . ($company->vat_number ?? '0XXX.XXX.XXX') . ' - ' . ($company->legal_form ?? 'SPRL') . ' - Siège social: ' . ($company->full_address ?? ''),
            'date_format' => 'd/m/Y',
            'number_format' => '0,00',
            'currency_position' => 'after',
        ]);
    }
}
