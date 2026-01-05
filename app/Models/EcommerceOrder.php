<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcommerceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'connection_id',
        'partner_id',
        'invoice_id',
        'external_id',
        'order_number',
        'status',
        'sync_status',
        'currency',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'total',
        'payment_method',
        'payment_status',
        'shipping_method',
        'billing_address',
        'shipping_address',
        'customer_data',
        'customer_note',
        'order_date',
        'paid_at',
        'shipped_at',
        'raw_data',
        'sync_error',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'customer_data' => 'array',
        'order_date' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(EcommerceConnection::class, 'connection_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EcommerceOrderItem::class, 'order_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'on_hold' => 'En attente',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            'failed' => 'Échouée',
            'shipped' => 'Expédiée',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed', 'shipped' => 'green',
            'processing' => 'blue',
            'pending', 'on_hold' => 'yellow',
            'cancelled', 'failed', 'refunded' => 'red',
            default => 'gray',
        };
    }

    public function getSyncStatusLabelAttribute(): string
    {
        return match ($this->sync_status) {
            'pending' => 'En attente',
            'synced' => 'Synchronisée',
            'invoiced' => 'Facturée',
            'error' => 'Erreur',
            default => $this->sync_status,
        };
    }

    public function canBeInvoiced(): bool
    {
        return $this->sync_status !== 'invoiced'
            && $this->invoice_id === null
            && in_array($this->status, ['processing', 'completed', 'shipped']);
    }

    public function createInvoice(): ?Invoice
    {
        if (!$this->canBeInvoiced()) {
            return null;
        }

        // Créer ou récupérer le client
        $partner = $this->partner ?? $this->createPartnerFromOrder();

        $invoice = Invoice::create([
            'company_id' => $this->company_id,
            'partner_id' => $partner->id,
            'type' => 'invoice',
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'notes' => "Commande e-commerce #{$this->order_number}",
        ]);

        // Copier les lignes
        foreach ($this->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'vat_rate' => $item->tax_amount > 0 ? ($item->tax_amount / ($item->unit_price * $item->quantity)) * 100 : 21,
            ]);
        }

        // Ajouter les frais de port
        if ($this->shipping_total > 0) {
            $invoice->items()->create([
                'description' => 'Frais de port',
                'quantity' => 1,
                'unit_price' => $this->shipping_total,
                'vat_rate' => 21,
            ]);
        }

        $invoice->calculateTotals();

        $this->update([
            'invoice_id' => $invoice->id,
            'partner_id' => $partner->id,
            'sync_status' => 'invoiced',
        ]);

        return $invoice;
    }

    protected function createPartnerFromOrder(): Partner
    {
        $billingAddress = $this->billing_address ?? [];
        $customerData = $this->customer_data ?? [];

        return Partner::create([
            'company_id' => $this->company_id,
            'type' => 'customer',
            'name' => $customerData['name'] ?? ($billingAddress['first_name'] ?? '') . ' ' . ($billingAddress['last_name'] ?? ''),
            'email' => $customerData['email'] ?? $billingAddress['email'] ?? null,
            'phone' => $customerData['phone'] ?? $billingAddress['phone'] ?? null,
            'address_line1' => $billingAddress['address_1'] ?? null,
            'address_line2' => $billingAddress['address_2'] ?? null,
            'city' => $billingAddress['city'] ?? null,
            'postal_code' => $billingAddress['postcode'] ?? null,
            'country' => $billingAddress['country'] ?? 'BE',
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', 'pending');
    }

    public function scopeNotInvoiced($query)
    {
        return $query->whereNull('invoice_id')
            ->where('sync_status', '!=', 'invoiced');
    }
}
