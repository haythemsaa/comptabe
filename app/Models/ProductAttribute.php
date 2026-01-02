<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    use HasUuid, HasTenant;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'values',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'values' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name, '_');
            }
        });
    }

    /**
     * Attribute types.
     */
    public const TYPES = [
        'select' => 'Liste de valeurs',
        'color' => 'Couleur',
        'size' => 'Taille',
        'text' => 'Texte libre',
    ];

    /**
     * Common predefined attributes.
     */
    public const COMMON_ATTRIBUTES = [
        [
            'name' => 'Couleur',
            'slug' => 'color',
            'type' => 'color',
            'values' => [
                'red' => ['label' => 'Rouge', 'color' => '#EF4444'],
                'blue' => ['label' => 'Bleu', 'color' => '#3B82F6'],
                'green' => ['label' => 'Vert', 'color' => '#10B981'],
                'yellow' => ['label' => 'Jaune', 'color' => '#F59E0B'],
                'black' => ['label' => 'Noir', 'color' => '#1F2937'],
                'white' => ['label' => 'Blanc', 'color' => '#F9FAFB'],
                'gray' => ['label' => 'Gris', 'color' => '#6B7280'],
            ],
        ],
        [
            'name' => 'Taille',
            'slug' => 'size',
            'type' => 'size',
            'values' => [
                'xs' => ['label' => 'XS', 'order' => 1],
                's' => ['label' => 'S', 'order' => 2],
                'm' => ['label' => 'M', 'order' => 3],
                'l' => ['label' => 'L', 'order' => 4],
                'xl' => ['label' => 'XL', 'order' => 5],
                'xxl' => ['label' => 'XXL', 'order' => 6],
            ],
        ],
        [
            'name' => 'Pointure',
            'slug' => 'shoe_size',
            'type' => 'size',
            'values' => array_combine(
                range(35, 48),
                array_map(fn($s) => ['label' => (string) $s, 'order' => $s], range(35, 48))
            ),
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
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return static::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Get values as options for select.
     */
    public function getOptionsAttribute(): array
    {
        if (empty($this->values)) {
            return [];
        }

        $options = [];
        foreach ($this->values as $key => $value) {
            $options[$key] = is_array($value) ? ($value['label'] ?? $key) : $value;
        }

        return $options;
    }

    /**
     * Add a value to the attribute.
     */
    public function addValue(string $key, array|string $value): void
    {
        $values = $this->values ?? [];
        $values[$key] = $value;
        $this->update(['values' => $values]);
    }

    /**
     * Remove a value from the attribute.
     */
    public function removeValue(string $key): void
    {
        $values = $this->values ?? [];
        unset($values[$key]);
        $this->update(['values' => $values]);
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Seed common attributes for a company.
     */
    public static function seedDefaultsForCompany(string $companyId): void
    {
        foreach (static::COMMON_ATTRIBUTES as $index => $attribute) {
            static::firstOrCreate(
                ['company_id' => $companyId, 'slug' => $attribute['slug']],
                array_merge($attribute, ['sort_order' => $index])
            );
        }
    }
}
