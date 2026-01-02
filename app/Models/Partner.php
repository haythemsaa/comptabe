<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'reference',
        'name',
        'vat_number',
        'enterprise_number',
        'is_company',
        'street',
        'house_number',
        'box',
        'postal_code',
        'city',
        'country_code',
        'email',
        'phone',
        'mobile',
        'contact_person',
        'peppol_id',
        'peppol_capable',
        'peppol_verified_at',
        'default_account_receivable_id',
        'default_account_payable_id',
        'payment_terms_days',
        'default_vat_code',
        'iban',
        'bic',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_company' => 'boolean',
            'peppol_capable' => 'boolean',
            'peppol_verified_at' => 'datetime',
            'payment_terms_days' => 'integer',
            'is_active' => 'boolean',
            // SECURITY: Encrypt sensitive banking data
            'iban' => 'encrypted',
            'bic' => 'encrypted',
        ];
    }

    /**
     * Scope for customers only.
     */
    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    /**
     * Scope for suppliers only.
     */
    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }

    /**
     * Scope for Peppol-capable partners.
     */
    public function scopePeppolCapable($query)
    {
        return $query->where('peppol_capable', true);
    }

    /**
     * Get formatted VAT number.
     */
    public function getFormattedVatNumberAttribute(): ?string
    {
        if (!$this->vat_number) return null;

        $vat = preg_replace('/[^0-9]/', '', $this->vat_number);
        if (strlen($vat) === 10) {
            return 'BE ' . substr($vat, 0, 4) . '.' . substr($vat, 4, 3) . '.' . substr($vat, 7);
        }
        return $this->vat_number;
    }

    /**
     * Get Peppol identifier (alias for peppol_id).
     */
    public function getPeppolIdentifierAttribute(): ?string
    {
        return $this->peppol_id;
    }

    /**
     * Get the street address (street + number + box).
     */
    public function getAddressAttribute(): ?string
    {
        if (!$this->street) return null;

        $address = $this->street;
        if ($this->house_number) {
            $address .= ' ' . $this->house_number;
        }
        if ($this->box) {
            $address .= ' bte ' . $this->box;
        }
        return $address;
    }

    /**
     * Get country name from country_code.
     */
    public function getCountryAttribute(): ?string
    {
        if (!$this->country_code) return null;

        $countries = [
            'BE' => 'Belgique',
            'FR' => 'France',
            'NL' => 'Pays-Bas',
            'DE' => 'Allemagne',
            'LU' => 'Luxembourg',
            'GB' => 'Royaume-Uni',
            'ES' => 'Espagne',
            'IT' => 'Italie',
        ];

        return $countries[$this->country_code] ?? $this->country_code;
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postal_code . ' ' . $this->city,
            $this->country_code !== 'BE' ? $this->country : null,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Check if partner is a customer.
     */
    public function isCustomer(): bool
    {
        return in_array($this->type, ['customer', 'both']);
    }

    /**
     * Get is_customer attribute.
     */
    public function getIsCustomerAttribute(): bool
    {
        return $this->isCustomer();
    }

    /**
     * Check if partner is a supplier.
     */
    public function isSupplier(): bool
    {
        return in_array($this->type, ['supplier', 'both']);
    }

    /**
     * Get is_supplier attribute.
     */
    public function getIsSupplierAttribute(): bool
    {
        return $this->isSupplier();
    }

    /**
     * Get invoices for this partner.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get sales invoices for this partner.
     */
    public function salesInvoices(): HasMany
    {
        return $this->invoices()->where('type', 'out');
    }

    /**
     * Get purchase invoices from this partner.
     */
    public function purchaseInvoices(): HasMany
    {
        return $this->invoices()->where('type', 'in');
    }

    /**
     * Get outstanding invoices.
     */
    public function outstandingInvoices()
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'received', 'partial'])
            ->where('amount_due', '>', 0);
    }

    /**
     * Get total outstanding amount.
     */
    public function getTotalOutstandingAttribute(): float
    {
        return $this->outstandingInvoices()->sum('amount_due');
    }

    /**
     * Generate next reference for this type.
     */
    public static function generateNextReference(string $companyId, string $type): string
    {
        $prefix = match ($type) {
            'customer' => 'C',
            'supplier' => 'F',
            default => 'P',
        };

        $lastRef = static::where('company_id', $companyId)
            ->where('reference', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(reference, 2) AS UNSIGNED) DESC')
            ->value('reference');

        if ($lastRef) {
            $number = (int) substr($lastRef, 1) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
