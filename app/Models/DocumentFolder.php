<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentFolder extends Model
{
    use HasFactory, HasUuid, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'color',
        'icon',
        'sort_order',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Default system folders to create for new companies.
     */
    public const SYSTEM_FOLDERS = [
        ['name' => 'Factures', 'icon' => 'receipt', 'color' => 'blue'],
        ['name' => 'Tickets de caisse', 'icon' => 'shopping-cart', 'color' => 'green'],
        ['name' => 'Extraits bancaires', 'icon' => 'building-library', 'color' => 'purple'],
        ['name' => 'Contrats', 'icon' => 'document-text', 'color' => 'orange'],
        ['name' => 'Documents fiscaux', 'icon' => 'calculator', 'color' => 'red'],
        ['name' => 'Fiches de paie', 'icon' => 'banknotes', 'color' => 'yellow'],
    ];

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Parent folder relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    /**
     * Child folders relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(DocumentFolder::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Documents in this folder.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    /**
     * Get document count.
     */
    public function getDocumentCountAttribute(): int
    {
        return $this->documents()->count();
    }

    /**
     * Get full path (Parent > Child).
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_path . ' / ' . $this->name;
        }

        return $this->name;
    }

    /**
     * Scope for root folders (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Create system folders for a company.
     */
    public static function createSystemFoldersFor(Company $company): void
    {
        foreach (self::SYSTEM_FOLDERS as $index => $folder) {
            static::create([
                'company_id' => $company->id,
                'name' => $folder['name'],
                'icon' => $folder['icon'],
                'color' => $folder['color'],
                'sort_order' => $index,
                'is_system' => true,
            ]);
        }
    }
}
