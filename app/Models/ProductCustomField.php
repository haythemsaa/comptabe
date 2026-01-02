<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductCustomField extends Model
{
    use HasUuid, HasTenant;

    protected $fillable = [
        'company_id',
        'product_type_id',
        'name',
        'slug',
        'label',
        'description',
        'type',
        'options',
        'default_value',
        'is_required',
        'is_searchable',
        'is_filterable',
        'show_in_list',
        'show_in_invoice',
        'group',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'is_filterable' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_invoice' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (empty($field->slug)) {
                $field->slug = Str::slug($field->name, '_');
            }
            if (empty($field->label)) {
                $field->label = $field->name;
            }
        });
    }

    /**
     * Available field types.
     */
    public const FIELD_TYPES = [
        'text' => [
            'label' => 'Texte court',
            'icon' => 'text',
            'component' => 'input',
        ],
        'textarea' => [
            'label' => 'Texte long',
            'icon' => 'align-left',
            'component' => 'textarea',
        ],
        'number' => [
            'label' => 'Nombre entier',
            'icon' => 'hash',
            'component' => 'input',
        ],
        'decimal' => [
            'label' => 'Nombre décimal',
            'icon' => 'percent',
            'component' => 'input',
        ],
        'currency' => [
            'label' => 'Montant',
            'icon' => 'euro',
            'component' => 'input',
        ],
        'date' => [
            'label' => 'Date',
            'icon' => 'calendar',
            'component' => 'input',
        ],
        'datetime' => [
            'label' => 'Date et heure',
            'icon' => 'clock',
            'component' => 'input',
        ],
        'boolean' => [
            'label' => 'Oui/Non',
            'icon' => 'toggle-left',
            'component' => 'checkbox',
        ],
        'select' => [
            'label' => 'Liste déroulante',
            'icon' => 'list',
            'component' => 'select',
        ],
        'multiselect' => [
            'label' => 'Sélection multiple',
            'icon' => 'check-square',
            'component' => 'multiselect',
        ],
        'radio' => [
            'label' => 'Boutons radio',
            'icon' => 'circle',
            'component' => 'radio',
        ],
        'url' => [
            'label' => 'URL',
            'icon' => 'link',
            'component' => 'input',
        ],
        'email' => [
            'label' => 'Email',
            'icon' => 'mail',
            'component' => 'input',
        ],
        'phone' => [
            'label' => 'Téléphone',
            'icon' => 'phone',
            'component' => 'input',
        ],
        'file' => [
            'label' => 'Fichier',
            'icon' => 'file',
            'component' => 'file',
        ],
        'image' => [
            'label' => 'Image',
            'icon' => 'image',
            'component' => 'file',
        ],
        'color' => [
            'label' => 'Couleur',
            'icon' => 'droplet',
            'component' => 'color',
        ],
        'json' => [
            'label' => 'JSON',
            'icon' => 'code',
            'component' => 'textarea',
        ],
        'richtext' => [
            'label' => 'Texte riche',
            'icon' => 'bold',
            'component' => 'richtext',
        ],
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Product type relationship.
     */
    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    /**
     * Get field type info.
     */
    public function getTypeInfoAttribute(): array
    {
        return static::FIELD_TYPES[$this->type] ?? [
            'label' => $this->type,
            'icon' => 'help-circle',
            'component' => 'input',
        ];
    }

    /**
     * Scope active fields.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for list display.
     */
    public function scopeForList($query)
    {
        return $query->where('show_in_list', true);
    }

    /**
     * Scope for invoice display.
     */
    public function scopeForInvoice($query)
    {
        return $query->where('show_in_invoice', true);
    }

    /**
     * Scope searchable fields.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope filterable fields.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for specific product type (or global).
     */
    public function scopeForType($query, ?string $productTypeId)
    {
        return $query->where(function ($q) use ($productTypeId) {
            $q->whereNull('product_type_id');
            if ($productTypeId) {
                $q->orWhere('product_type_id', $productTypeId);
            }
        });
    }

    /**
     * Get validation rules for this field.
     */
    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->type) {
            case 'text':
            case 'textarea':
            case 'richtext':
                $rules[] = 'string';
                if ($this->options['min_length'] ?? null) {
                    $rules[] = 'min:' . $this->options['min_length'];
                }
                if ($this->options['max_length'] ?? null) {
                    $rules[] = 'max:' . $this->options['max_length'];
                }
                break;

            case 'number':
                $rules[] = 'integer';
                if (isset($this->options['min'])) {
                    $rules[] = 'min:' . $this->options['min'];
                }
                if (isset($this->options['max'])) {
                    $rules[] = 'max:' . $this->options['max'];
                }
                break;

            case 'decimal':
            case 'currency':
                $rules[] = 'numeric';
                if (isset($this->options['min'])) {
                    $rules[] = 'min:' . $this->options['min'];
                }
                if (isset($this->options['max'])) {
                    $rules[] = 'max:' . $this->options['max'];
                }
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'datetime':
                $rules[] = 'date';
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'select':
            case 'radio':
                $rules[] = 'string';
                if ($choices = $this->options['choices'] ?? null) {
                    $rules[] = 'in:' . implode(',', array_keys($choices));
                }
                break;

            case 'multiselect':
                $rules[] = 'array';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'phone':
                $rules[] = 'string';
                $rules[] = 'max:30';
                break;

            case 'file':
            case 'image':
                $rules[] = 'file';
                if ($this->type === 'image') {
                    $rules[] = 'image';
                }
                if ($maxSize = $this->options['max_size'] ?? null) {
                    $rules[] = 'max:' . $maxSize;
                }
                break;

            case 'color':
                $rules[] = 'string';
                $rules[] = 'regex:/^#[0-9A-Fa-f]{6}$/';
                break;

            case 'json':
                $rules[] = 'json';
                break;
        }

        return $rules;
    }

    /**
     * Cast value according to field type.
     */
    public function castValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        switch ($this->type) {
            case 'number':
                return (int) $value;

            case 'decimal':
            case 'currency':
                return (float) $value;

            case 'boolean':
                return (bool) $value;

            case 'date':
                return is_string($value) ? $value : $value->format('Y-m-d');

            case 'datetime':
                return is_string($value) ? $value : $value->format('Y-m-d H:i:s');

            case 'multiselect':
            case 'json':
                return is_array($value) ? $value : json_decode($value, true);

            default:
                return (string) $value;
        }
    }

    /**
     * Format value for display.
     */
    public function formatValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        switch ($this->type) {
            case 'boolean':
                return $value ? 'Oui' : 'Non';

            case 'currency':
                return number_format((float) $value, 2, ',', ' ') . ' €';

            case 'decimal':
                $decimals = $this->options['decimals'] ?? 2;
                return number_format((float) $value, $decimals, ',', ' ');

            case 'date':
                return \Carbon\Carbon::parse($value)->format('d/m/Y');

            case 'datetime':
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');

            case 'select':
            case 'radio':
                $choices = $this->options['choices'] ?? [];
                return $choices[$value] ?? $value;

            case 'multiselect':
                $choices = $this->options['choices'] ?? [];
                if (is_array($value)) {
                    return implode(', ', array_map(fn($v) => $choices[$v] ?? $v, $value));
                }
                return $value;

            case 'url':
                return "<a href=\"{$value}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">{$value}</a>";

            case 'email':
                return "<a href=\"mailto:{$value}\" class=\"text-primary-600 hover:underline\">{$value}</a>";

            case 'color':
                return "<span class=\"inline-flex items-center gap-1\"><span class=\"w-4 h-4 rounded\" style=\"background-color: {$value}\"></span> {$value}</span>";

            default:
                return (string) $value;
        }
    }
}
