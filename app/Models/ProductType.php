<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductType extends Model
{
    use HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_service',
        'track_inventory',
        'has_variants',
        'default_values',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_service' => 'boolean',
            'track_inventory' => 'boolean',
            'has_variants' => 'boolean',
            'default_values' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    /**
     * Default product types that can be seeded.
     */
    public const DEFAULT_TYPES = [
        [
            'name' => 'Produit physique',
            'slug' => 'physical',
            'description' => 'Produits physiques avec gestion de stock',
            'icon' => 'box',
            'color' => '#3B82F6',
            'is_service' => false,
            'track_inventory' => true,
            'has_variants' => true,
        ],
        [
            'name' => 'Service',
            'slug' => 'service',
            'description' => 'Services sans stock',
            'icon' => 'briefcase',
            'color' => '#10B981',
            'is_service' => true,
            'track_inventory' => false,
            'has_variants' => false,
        ],
        [
            'name' => 'Produit numérique',
            'slug' => 'digital',
            'description' => 'Produits numériques (logiciels, licences, etc.)',
            'icon' => 'download',
            'color' => '#8B5CF6',
            'is_service' => false,
            'track_inventory' => false,
            'has_variants' => false,
        ],
        [
            'name' => 'Abonnement',
            'slug' => 'subscription',
            'description' => 'Services récurrents avec facturation périodique',
            'icon' => 'refresh',
            'color' => '#F59E0B',
            'is_service' => true,
            'track_inventory' => false,
            'has_variants' => false,
        ],
        [
            'name' => 'Location',
            'slug' => 'rental',
            'description' => 'Location de matériel ou équipement',
            'icon' => 'clock',
            'color' => '#EC4899',
            'is_service' => true,
            'track_inventory' => true,
            'has_variants' => false,
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
     * Products of this type.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_type_id');
    }

    /**
     * Custom fields for this type.
     */
    public function customFields(): HasMany
    {
        return $this->hasMany(ProductCustomField::class, 'product_type_id')->orderBy('sort_order');
    }

    /**
     * Scope active types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get product count.
     */
    public function getProductCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get default values merged with passed values.
     */
    public function getDefaultsForProduct(array $values = []): array
    {
        $defaults = $this->default_values ?? [];

        // Set type-based defaults
        if ($this->is_service) {
            $defaults['type'] = 'service';
        }

        if ($this->track_inventory) {
            $defaults['track_inventory'] = true;
        }

        return array_merge($defaults, $values);
    }

    /**
     * Seed default types for a company.
     */
    public static function seedDefaultsForCompany(string $companyId): void
    {
        foreach (static::DEFAULT_TYPES as $index => $type) {
            static::firstOrCreate(
                ['company_id' => $companyId, 'slug' => $type['slug']],
                array_merge($type, ['sort_order' => $index])
            );
        }
    }
}
