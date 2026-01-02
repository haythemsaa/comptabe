<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CreditNote extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'invoice_id',
        'credit_note_number',
        'status',
        'credit_note_date',
        'reference',
        'reason',
        'total_excl_vat',
        'total_vat',
        'total_incl_vat',
        'validated_at',
        'sent_at',
        'applied_at',
        'peppol_id',
        'peppol_sent_at',
        'structured_communication',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'credit_note_date' => 'date',
            'validated_at' => 'datetime',
            'sent_at' => 'datetime',
            'applied_at' => 'datetime',
            'peppol_sent_at' => 'datetime',
            'total_excl_vat' => 'decimal:2',
            'total_vat' => 'decimal:2',
            'total_incl_vat' => 'decimal:2',
        ];
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Original invoice relationship.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Lines relationship.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(CreditNoteLine::class)->orderBy('line_number');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Company relationship.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate next credit note number.
     */
    public static function generateNextNumber(string $companyId): string
    {
        $year = date('Y');
        $prefix = "NC{$year}-";

        $lastNumber = static::where('company_id', $companyId)
            ->where('credit_note_number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(credit_note_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->value('credit_note_number');

        if ($lastNumber) {
            $number = (int) substr($lastNumber, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate structured communication.
     */
    public static function generateStructuredCommunication(): string
    {
        $base = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        $checksum = str_pad((int) $base % 97 ?: 97, 2, '0', STR_PAD_LEFT);

        return '+++' . substr($base, 0, 3) . '/' . substr($base, 3, 4) . '/' . substr($base, 7, 3) . $checksum . '+++';
    }

    /**
     * Calculate totals from lines.
     */
    public function calculateTotals(): void
    {
        $this->total_excl_vat = $this->lines->sum('line_total');
        $this->total_vat = $this->lines->sum('vat_amount');
        $this->total_incl_vat = $this->total_excl_vat + $this->total_vat;
    }

    /**
     * VAT summary for display.
     */
    public function vatSummary(): array
    {
        return $this->lines->groupBy('vat_rate')
            ->map(fn($lines) => $lines->sum('vat_amount'))
            ->toArray();
    }

    /**
     * Check if editable.
     */
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Create credit note from invoice.
     */
    public static function createFromInvoice(Invoice $invoice, ?string $reason = null): self
    {
        return DB::transaction(function () use ($invoice, $reason) {
            $creditNote = static::create([
                'company_id' => $invoice->company_id,
                'partner_id' => $invoice->partner_id,
                'invoice_id' => $invoice->id,
                'credit_note_number' => static::generateNextNumber($invoice->company_id),
                'status' => 'draft',
                'credit_note_date' => now(),
                'reference' => "Ref: {$invoice->invoice_number}",
                'reason' => $reason,
                'structured_communication' => static::generateStructuredCommunication(),
                'created_by' => auth()->id(),
            ]);

            foreach ($invoice->lines as $line) {
                $creditNote->lines()->create([
                    'line_number' => $line->line_number,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'unit_price' => $line->unit_price,
                    'discount_percent' => $line->discount_percent,
                    'vat_rate' => $line->vat_rate,
                    'vat_category' => $line->vat_category,
                    'account_id' => $line->account_id,
                ]);
            }

            $creditNote->calculateTotals();
            $creditNote->save();

            return $creditNote;
        });
    }

    /**
     * Validate credit note.
     */
    public function validate(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Seules les notes de credit en brouillon peuvent etre validees.');
        }

        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        // Update invoice if linked
        if ($this->invoice) {
            // Use increment to safely add the credit amount without SQL injection risk
            $this->invoice->increment('amount_credited', (float) $this->total_incl_vat);
        }
    }

    /**
     * Status label accessor.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'validated' => 'Validee',
            'sent' => 'Envoyee',
            'applied' => 'Appliquee',
            default => $this->status,
        };
    }

    /**
     * Status color accessor.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'validated' => 'info',
            'sent' => 'primary',
            'applied' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Scope for drafts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for validated.
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }
}
