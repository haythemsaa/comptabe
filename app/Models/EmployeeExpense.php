<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class EmployeeExpense extends Model
{
    use HasUuid, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'expense_report_id',
        'category_id',
        'user_id',
        'expense_date',
        'merchant',
        'description',
        'amount',
        'vat_amount',
        'net_amount',
        'vat_rate',
        'currency',
        'exchange_rate',
        'amount_eur',
        'payment_method',
        'is_billable',
        'project_id',
        'partner_id',
        'status',
        'is_mileage',
        'distance_km',
        'departure',
        'destination',
        'vehicle_type',
        'receipt_path',
        'receipt_original_name',
        'has_receipt',
        'ocr_data',
        'ocr_processed',
        'accounting_code',
        'is_booked',
        'accounting_entry_id',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_eur' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_mileage' => 'boolean',
        'has_receipt' => 'boolean',
        'ocr_data' => 'array',
        'ocr_processed' => 'boolean',
        'is_booked' => 'boolean',
    ];

    public const STATUSES = [
        'draft' => ['label' => 'Brouillon', 'color' => 'secondary'],
        'pending' => ['label' => 'En attente', 'color' => 'warning'],
        'approved' => ['label' => 'Approuvé', 'color' => 'success'],
        'rejected' => ['label' => 'Rejeté', 'color' => 'danger'],
        'reimbursed' => ['label' => 'Remboursé', 'color' => 'info'],
    ];

    public const PAYMENT_METHODS = [
        'personal_card' => ['label' => 'Carte personnelle', 'icon' => 'credit-card'],
        'company_card' => ['label' => 'Carte entreprise', 'icon' => 'building-bank'],
        'cash' => ['label' => 'Espèces', 'icon' => 'cash'],
        'bank_transfer' => ['label' => 'Virement', 'icon' => 'arrows-exchange'],
        'other' => ['label' => 'Autre', 'icon' => 'dots'],
    ];

    public const VEHICLE_TYPES = [
        'car' => ['label' => 'Voiture', 'rate' => 0.4259],
        'motorcycle' => ['label' => 'Moto', 'rate' => 0.2495],
        'bike' => ['label' => 'Vélo', 'rate' => 0.27],
        'electric' => ['label' => 'Véhicule électrique', 'rate' => 0.4259],
    ];

    // Relations
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function expenseReport(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ExpenseAttachment::class, 'employee_expense_id');
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeReimbursed($query)
    {
        return $query->where('status', 'reimbursed');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('expense_report_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInPeriod($query, $start, $end)
    {
        return $query->whereBetween('expense_date', [$start, $end]);
    }

    public function scopeMileage($query)
    {
        return $query->where('is_mileage', true);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    // Helpers
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function getPaymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method]['label'] ?? $this->payment_method;
    }

    public function getVehicleTypeLabel(): string
    {
        return self::VEHICLE_TYPES[$this->vehicle_type]['label'] ?? $this->vehicle_type;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isReimbursed(): bool
    {
        return $this->status === 'reimbursed';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    public function needsReceipt(): bool
    {
        return !$this->has_receipt && $this->category?->requires_receipt;
    }

    // Actions
    public function submit(): void
    {
        if (!$this->canBeSubmitted()) return;
        $this->status = 'pending';
        $this->save();
    }

    public function approve(): void
    {
        if ($this->status !== 'pending') return;
        $this->status = 'approved';
        $this->save();
    }

    public function reject(string $reason): void
    {
        if ($this->status !== 'pending') return;
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
    }

    public function markReimbursed(): void
    {
        if ($this->status !== 'approved') return;
        $this->status = 'reimbursed';
        $this->save();
    }

    // Calculate mileage amount
    public function calculateMileageAmount(): float
    {
        if (!$this->is_mileage || !$this->distance_km) return 0;

        $rate = $this->category?->mileage_rate
            ?? self::VEHICLE_TYPES[$this->vehicle_type]['rate']
            ?? 0.4259;

        return round($this->distance_km * $rate, 2);
    }

    // Auto-calculate amounts
    public function calculateAmounts(): void
    {
        if ($this->is_mileage) {
            $this->amount = $this->calculateMileageAmount();
            $this->net_amount = $this->amount;
            $this->vat_amount = 0;
            $this->vat_rate = 0;
        } else {
            $this->net_amount = round($this->amount / (1 + ($this->vat_rate / 100)), 2);
            $this->vat_amount = $this->amount - $this->net_amount;
        }

        // Convert to EUR if different currency
        if ($this->currency !== 'EUR' && $this->exchange_rate) {
            $this->amount_eur = round($this->amount * $this->exchange_rate, 2);
        } else {
            $this->amount_eur = $this->amount;
        }
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            $expense->calculateAmounts();
        });

        static::updating(function ($expense) {
            if ($expense->isDirty(['amount', 'vat_rate', 'distance_km', 'currency', 'exchange_rate'])) {
                $expense->calculateAmounts();
            }
        });
    }
}
