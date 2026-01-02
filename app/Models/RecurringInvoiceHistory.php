<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceHistory extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'recurring_invoice_history';

    protected $fillable = [
        'recurring_invoice_id',
        'invoice_id',
        'generated_date',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'generated_date' => 'date',
        ];
    }

    /**
     * Recurring invoice relationship.
     */
    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    /**
     * Invoice relationship.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
