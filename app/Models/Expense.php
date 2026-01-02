<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'invoice_id',
        'bank_transaction_id',
        'date',
        'amount',
        'currency',
        'description',
        'label',
        'category',
        'account_code',
        'vat_code',
        'vat_amount',
        'notes',
        'receipt_path',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function isCategorized(): bool
    {
        return !is_null($this->category);
    }

    public function scopeUncategorized($query)
    {
        return $query->whereNull('category');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
