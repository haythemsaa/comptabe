<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'user_id',
        'expense_id',
        'invoice_id',
        'type',
        'expense_date',
        'amount',
        'vat_amount',
        'quantity',
        'unit_price',
        'odometer',
        'supplier',
        'receipt_path',
        'notes',
        'is_private_use',
        'private_use_percent',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'odometer' => 'integer',
        'is_private_use' => 'boolean',
        'private_use_percent' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'fuel' => 'Carburant',
            'maintenance' => 'Entretien',
            'repair' => 'Réparation',
            'insurance' => 'Assurance',
            'tax' => 'Taxes',
            'parking' => 'Parking',
            'toll' => 'Péages',
            'washing' => 'Lavage',
            'tyre' => 'Pneus',
            'fine' => 'Amende',
            'other' => 'Autre',
            default => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'fuel' => 'fa-gas-pump',
            'maintenance' => 'fa-wrench',
            'repair' => 'fa-tools',
            'insurance' => 'fa-shield-alt',
            'tax' => 'fa-file-invoice',
            'parking' => 'fa-parking',
            'toll' => 'fa-road',
            'washing' => 'fa-soap',
            'tyre' => 'fa-tire',
            'fine' => 'fa-ticket-alt',
            default => 'fa-receipt',
        };
    }

    public function getTotalWithVatAttribute(): float
    {
        return $this->amount + $this->vat_amount;
    }

    public function getDeductibleAmountAttribute(): float
    {
        if ($this->is_private_use) {
            return $this->total_with_vat * (1 - $this->private_use_percent / 100);
        }
        return $this->total_with_vat;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFuel($query)
    {
        return $query->where('type', 'fuel');
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }
}
