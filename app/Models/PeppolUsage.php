<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeppolUsage extends Model
{
    use HasFactory;

    protected $table = 'peppol_usage';

    protected $fillable = [
        'company_id',
        'invoice_id',
        'action',
        'document_type',
        'transmission_id',
        'participant_id',
        'status',
        'error_message',
        'cost',
        'counted_in_quota',
        'month',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:4',
            'counted_in_quota' => 'boolean',
            'month' => 'integer',
            'year' => 'integer',
        ];
    }

    /**
     * Company relationship
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Invoice relationship
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Log a Peppol send action
     */
    public static function logSend(
        string $companyId,
        ?string $invoiceId,
        string $transmissionId,
        string $participantId,
        string $documentType = 'invoice',
        float $cost = 0
    ): self {
        return self::create([
            'company_id' => $companyId,
            'invoice_id' => $invoiceId,
            'action' => 'send',
            'document_type' => $documentType,
            'transmission_id' => $transmissionId,
            'participant_id' => $participantId,
            'status' => 'success',
            'cost' => $cost,
            'counted_in_quota' => true,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    /**
     * Log a Peppol receive action
     */
    public static function logReceive(
        string $companyId,
        ?string $invoiceId,
        string $participantId,
        string $documentType = 'invoice'
    ): self {
        return self::create([
            'company_id' => $companyId,
            'invoice_id' => $invoiceId,
            'action' => 'receive',
            'document_type' => $documentType,
            'participant_id' => $participantId,
            'status' => 'success',
            'cost' => 0,
            'counted_in_quota' => true,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    /**
     * Log a failed action
     */
    public static function logFailed(
        string $companyId,
        string $action,
        string $errorMessage,
        ?string $invoiceId = null,
        ?string $participantId = null
    ): self {
        return self::create([
            'company_id' => $companyId,
            'invoice_id' => $invoiceId,
            'action' => $action,
            'participant_id' => $participantId,
            'status' => 'failed',
            'error_message' => $errorMessage,
            'cost' => 0,
            'counted_in_quota' => false,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    /**
     * Get usage for a company in a specific month
     */
    public static function getMonthlyUsage(string $companyId, ?int $month = null, ?int $year = null): int
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return self::where('company_id', $companyId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'success')
            ->where('counted_in_quota', true)
            ->count();
    }

    /**
     * Get total cost for a company in a specific month
     */
    public static function getMonthlyCost(string $companyId, ?int $month = null, ?int $year = null): float
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return (float) self::where('company_id', $companyId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'success')
            ->sum('cost');
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('month', now()->month)
            ->where('year', now()->year);
    }

    /**
     * Scope for successful transmissions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed transmissions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
