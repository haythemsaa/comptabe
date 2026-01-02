<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecurringInvoice extends Model
{
    use HasFactory, HasUuid, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'partner_id',
        'invoice_template_id',
        'name',
        'description',
        'frequency',
        'interval',
        'day_of_month',
        'day_of_week',
        'start_date',
        'end_date',
        'next_invoice_date',
        'last_invoice_date',
        'invoices_generated_count',
        'max_invoices',
        'payment_terms_days',
        'auto_send_email',
        'auto_validate',
        'line_items',
        'subtotal',
        'vat_amount',
        'total',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_invoice_date' => 'date',
            'last_invoice_date' => 'date',
            'subtotal' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'interval' => 'integer',
            'day_of_month' => 'integer',
            'invoices_generated_count' => 'integer',
            'max_invoices' => 'integer',
            'payment_terms_days' => 'integer',
            'auto_send_email' => 'boolean',
            'auto_validate' => 'boolean',
            'line_items' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Status colors for UI.
     */
    public const STATUS_COLORS = [
        'active' => 'success',
        'paused' => 'warning',
        'completed' => 'secondary',
        'cancelled' => 'danger',
    ];

    /**
     * Status labels.
     */
    public const STATUS_LABELS = [
        'active' => 'Actif',
        'paused' => 'En pause',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
    ];

    /**
     * Frequency labels.
     */
    public const FREQUENCY_LABELS = [
        'daily' => 'Quotidien',
        'weekly' => 'Hebdomadaire',
        'biweekly' => 'Bimensuel',
        'monthly' => 'Mensuel',
        'bimonthly' => 'Bimestriel',
        'quarterly' => 'Trimestriel',
        'semiannual' => 'Semestriel',
        'annual' => 'Annuel',
    ];

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCY_LABELS[$this->frequency] ?? $this->frequency;
    }

    /**
     * Partner relationship.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Lines relationship.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(RecurringInvoiceLine::class)->orderBy('line_number');
    }

    /**
     * Generated invoices.
     */
    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoiceHistory::class);
    }

    /**
     * Creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate totals from line items.
     */
    public function calculateTotals(): void
    {
        if (is_array($this->line_items)) {
            $subtotal = 0;
            $vatAmount = 0;

            foreach ($this->line_items as $item) {
                $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                $lineVat = $lineTotal * (($item['vat_rate'] ?? 0) / 100);

                $subtotal += $lineTotal;
                $vatAmount += $lineVat;
            }

            $this->subtotal = $subtotal;
            $this->vat_amount = $vatAmount;
            $this->total = $subtotal + $vatAmount;
        }
    }

    /**
     * Check if due for generation.
     */
    public function isDueForGeneration(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        if ($this->max_invoices && $this->invoices_generated_count >= $this->max_invoices) {
            return false;
        }

        return $this->next_invoice_date && $this->next_invoice_date->lte(now());
    }

    /**
     * Generate invoice from template.
     */
    public function generateInvoice(): Invoice
    {
        if (!$this->isDueForGeneration()) {
            throw new \Exception('Ce modèle récurrent n\'est pas prêt pour la génération.');
        }

        return DB::transaction(function () {
            // Create invoice
            $invoice = Invoice::create([
                'company_id' => $this->company_id,
                'partner_id' => $this->partner_id,
                'type' => 'out',
                'document_type' => 'invoice',
                'status' => 'draft',
                'invoice_number' => Invoice::generateNextNumber($this->company_id, 'out'),
                'invoice_date' => now(),
                'due_date' => now()->addDays($this->payment_terms_days),
                'notes' => $this->description,
            ]);

            // Copy line items from JSON
            if (is_array($this->line_items)) {
                $lineNumber = 1;
                foreach ($this->line_items as $item) {
                    $invoice->lines()->create([
                        'line_number' => $lineNumber++,
                        'description' => $this->processDescription($item['description'] ?? ''),
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'vat_rate' => $item['vat_rate'] ?? 21,
                    ]);
                }
            }

            // Calculate totals
            $invoice->calculateTotals();
            $invoice->save();

            // Update recurring invoice
            $this->update([
                'last_invoice_date' => now(),
                'next_invoice_date' => $this->calculateNextDate(),
                'invoices_generated_count' => $this->invoices_generated_count + 1,
            ]);

            // Check if completed
            if ($this->max_invoices && $this->invoices_generated_count >= $this->max_invoices) {
                $this->update(['status' => 'completed']);
            }

            // Auto-send if enabled
            if ($this->auto_send_email && $invoice->partner->email) {
                $invoice->update(['status' => 'sent']);
            }

            return $invoice;
        });
    }

    /**
     * Process description placeholders.
     */
    protected function processDescription(string $description): string
    {
        $replacements = [
            '{mois}' => now()->translatedFormat('F'),
            '{month}' => now()->format('F'),
            '{annee}' => now()->format('Y'),
            '{year}' => now()->format('Y'),
            '{periode}' => now()->format('m/Y'),
            '{trimestre}' => 'T' . ceil(now()->month / 3),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $description
        );
    }

    /**
     * Calculate next invoice date.
     */
    public function calculateNextDate(): ?Carbon
    {
        $current = $this->next_invoice_date ?? $this->start_date;
        $interval = $this->interval ?? 1;

        switch ($this->frequency) {
            case 'weekly':
                $next = $current->copy()->addWeeks($interval);
                if ($this->day_of_week !== null) {
                    // Convert day name to day number (0 = Monday)
                    $dayMap = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3, 'friday' => 4, 'saturday' => 5, 'sunday' => 6];
                    $dayNum = $dayMap[$this->day_of_week] ?? 0;
                    $next = $next->startOfWeek()->addDays($dayNum);
                }
                break;

            case 'monthly':
                $next = $current->copy()->addMonths($interval);
                if ($this->day_of_month) {
                    $day = min($this->day_of_month, $next->daysInMonth);
                    $next = $next->setDay($day);
                }
                break;

            case 'quarterly':
                $next = $current->copy()->addMonths(3 * $interval);
                if ($this->day_of_month) {
                    $day = min($this->day_of_month, $next->daysInMonth);
                    $next = $next->setDay($day);
                }
                break;

            case 'annual':
            case 'yearly':
                $next = $current->copy()->addYears($interval);
                break;

            default:
                return null;
        }

        // Check end date
        if ($this->end_date && $next->gt($this->end_date)) {
            return null;
        }

        return $next;
    }

    /**
     * Scope for active recurring invoices.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for due recurring invoices.
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->where('next_invoice_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_invoices')
                    ->orWhereRaw('invoices_generated_count < max_invoices');
            });
    }
}
