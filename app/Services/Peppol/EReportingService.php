<?php

namespace App\Services\Peppol;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\EReportingSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Belgian e-Reporting Service (5-Corner Model)
 *
 * Starting 2028, Belgium mandates B2B e-invoicing with real-time reporting
 * to the SPF Finances (5th corner). This service handles the Continuous
 * Transaction Controls (CTC) aspect of the Belgian e-invoicing mandate.
 *
 * Architecture:
 * - Corner 1: Seller
 * - Corner 2: Seller's Access Point (Peppol AP)
 * - Corner 3: Buyer's Access Point (Peppol AP)
 * - Corner 4: Buyer
 * - Corner 5: Government (SPF Finances) - NEW for 2028
 */
class EReportingService
{
    protected ?Company $company;
    protected string $apiBaseUrl;
    protected bool $testMode;

    // Belgian e-Reporting status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ERROR = 'error';

    // Report types
    public const TYPE_SALES = 'sales';          // Outgoing invoices
    public const TYPE_PURCHASES = 'purchases';   // Incoming invoices
    public const TYPE_CORRECTION = 'correction'; // Credit notes

    public function __construct(?Company $company = null)
    {
        $this->company = $company ?? Company::current();
        $this->testMode = $this->company?->ereporting_test_mode ?? true;

        // Belgian government e-Reporting API endpoints (placeholder URLs)
        $this->apiBaseUrl = $this->testMode
            ? 'https://api.sandbox.ereporting.belgium.be/v1'
            : 'https://api.ereporting.belgium.be/v1';
    }

    /**
     * Set test mode.
     */
    public function setTestMode(bool $testMode): self
    {
        $this->testMode = $testMode;
        $this->apiBaseUrl = $testMode
            ? 'https://api.sandbox.ereporting.belgium.be/v1'
            : 'https://api.ereporting.belgium.be/v1';
        return $this;
    }

    /**
     * Submit invoice to Belgian e-Reporting platform.
     * This is the 5th corner submission - reporting to SPF Finances.
     */
    public function submitInvoice(Invoice $invoice): EReportingSubmission
    {
        $invoice->load(['company', 'partner', 'lines']);

        // Create submission record
        $submission = EReportingSubmission::create([
            'company_id' => $invoice->company_id,
            'invoice_id' => $invoice->id,
            'submission_id' => Str::uuid()->toString(),
            'type' => $invoice->type === 'out' ? self::TYPE_SALES : self::TYPE_PURCHASES,
            'status' => self::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        try {
            // Generate e-Reporting data payload
            $payload = $this->generateReportingPayload($invoice);

            // Store the payload for audit
            $submission->update(['request_payload' => json_encode($payload)]);

            // In test mode without API key, simulate success
            if ($this->testMode && !$this->company->ereporting_api_key) {
                return $this->simulateSubmission($submission, $payload);
            }

            // Submit to government API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->company->ereporting_api_key,
                'Content-Type' => 'application/json',
                'X-Submission-Id' => $submission->submission_id,
                'X-Test-Mode' => $this->testMode ? 'true' : 'false',
            ])->post("{$this->apiBaseUrl}/invoices/submit", $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                $submission->update([
                    'status' => self::STATUS_SUBMITTED,
                    'government_reference' => $responseData['reference'] ?? null,
                    'response_payload' => $response->body(),
                ]);

                // Update invoice e-reporting status
                $invoice->update([
                    'ereporting_status' => self::STATUS_SUBMITTED,
                    'ereporting_submitted_at' => now(),
                    'ereporting_reference' => $responseData['reference'] ?? null,
                ]);

                Log::info('E-Reporting submission successful', [
                    'invoice_id' => $invoice->id,
                    'submission_id' => $submission->submission_id,
                    'reference' => $responseData['reference'] ?? null,
                ]);
            } else {
                throw new \Exception('E-Reporting API error: ' . $response->body());
            }

            return $submission;

        } catch (\Exception $e) {
            $submission->update([
                'status' => self::STATUS_ERROR,
                'error_message' => $e->getMessage(),
            ]);

            $invoice->update([
                'ereporting_status' => self::STATUS_ERROR,
                'ereporting_error' => $e->getMessage(),
            ]);

            Log::error('E-Reporting submission failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate the reporting payload for SPF Finances.
     * Based on expected Belgian e-Reporting requirements.
     */
    protected function generateReportingPayload(Invoice $invoice): array
    {
        $company = $invoice->company;
        $partner = $invoice->partner;

        return [
            'document' => [
                'type' => $this->getDocumentType($invoice),
                'number' => $invoice->invoice_number,
                'issue_date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'currency' => $invoice->currency ?? 'EUR',
            ],
            'seller' => [
                'vat_number' => $this->cleanVatNumber($company->vat_number),
                'enterprise_number' => $this->cleanEnterpriseNumber($company->enterprise_number ?? $company->vat_number),
                'name' => $company->name,
                'address' => [
                    'street' => $company->street . ' ' . $company->house_number,
                    'city' => $company->city,
                    'postal_code' => $company->postal_code,
                    'country' => $company->country_code ?? 'BE',
                ],
            ],
            'buyer' => [
                'vat_number' => $partner->vat_number ? $this->cleanVatNumber($partner->vat_number) : null,
                'enterprise_number' => $partner->enterprise_number ? $this->cleanEnterpriseNumber($partner->enterprise_number) : null,
                'name' => $partner->name,
                'address' => [
                    'street' => $partner->street . ' ' . $partner->house_number,
                    'city' => $partner->city,
                    'postal_code' => $partner->postal_code,
                    'country' => $partner->country_code ?? 'BE',
                ],
                'is_b2b' => $this->isB2B($partner),
            ],
            'amounts' => [
                'total_excl_vat' => (float) $invoice->total_excl_vat,
                'total_vat' => (float) $invoice->total_vat,
                'total_incl_vat' => (float) $invoice->total_incl_vat,
            ],
            'vat_breakdown' => $this->getVatBreakdown($invoice),
            'lines' => $this->getLinesSummary($invoice),
            'payment' => [
                'method' => 'credit_transfer',
                'iban' => $company->default_iban,
                'structured_communication' => $invoice->structured_communication,
            ],
            'peppol' => [
                'transmitted' => !empty($invoice->peppol_message_id),
                'message_id' => $invoice->peppol_message_id,
                'status' => $invoice->peppol_status,
            ],
            'metadata' => [
                'software' => 'ComptaBE',
                'version' => config('app.version', '1.0.0'),
                'submission_time' => now()->toIso8601String(),
                'test_mode' => $this->testMode,
            ],
        ];
    }

    /**
     * Get document type code.
     */
    protected function getDocumentType(Invoice $invoice): string
    {
        if ($invoice->type === 'credit') {
            return '381'; // Credit note
        }
        return '380'; // Commercial invoice
    }

    /**
     * Get VAT breakdown by rate.
     */
    protected function getVatBreakdown(Invoice $invoice): array
    {
        $breakdown = [];
        $grouped = $invoice->lines->groupBy('vat_rate');

        foreach ($grouped as $rate => $lines) {
            $breakdown[] = [
                'rate' => (float) $rate,
                'taxable_amount' => (float) $lines->sum('line_amount'),
                'vat_amount' => (float) $lines->sum('vat_amount'),
                'category' => $rate > 0 ? 'S' : 'Z', // Standard or Zero-rated
            ];
        }

        return $breakdown;
    }

    /**
     * Get invoice lines summary for reporting.
     */
    protected function getLinesSummary(Invoice $invoice): array
    {
        return $invoice->lines->map(function ($line) {
            return [
                'description' => $line->description,
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'line_amount' => (float) $line->line_amount,
                'vat_rate' => (float) $line->vat_rate,
                'vat_amount' => (float) $line->vat_amount,
            ];
        })->toArray();
    }

    /**
     * Check if partner is B2B (has VAT number).
     */
    protected function isB2B($partner): bool
    {
        return !empty($partner->vat_number) || !empty($partner->enterprise_number);
    }

    /**
     * Clean VAT number format.
     */
    protected function cleanVatNumber(?string $vatNumber): ?string
    {
        if (!$vatNumber) return null;
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($vatNumber));
    }

    /**
     * Clean enterprise number format.
     */
    protected function cleanEnterpriseNumber(?string $number): ?string
    {
        if (!$number) return null;
        return preg_replace('/[^0-9]/', '', $number);
    }

    /**
     * Simulate submission in test mode.
     */
    protected function simulateSubmission(EReportingSubmission $submission, array $payload): EReportingSubmission
    {
        $simulatedReference = 'TEST-' . strtoupper(Str::random(12));

        $submission->update([
            'status' => self::STATUS_SUBMITTED,
            'government_reference' => $simulatedReference,
            'response_payload' => json_encode([
                'success' => true,
                'reference' => $simulatedReference,
                'message' => 'Test mode - simulated e-Reporting submission',
                'test_mode' => true,
            ]),
        ]);

        if ($submission->invoice) {
            $submission->invoice->update([
                'ereporting_status' => self::STATUS_SUBMITTED,
                'ereporting_submitted_at' => now(),
                'ereporting_reference' => $simulatedReference,
            ]);
        }

        Log::info('E-Reporting simulated submission', [
            'submission_id' => $submission->submission_id,
            'reference' => $simulatedReference,
        ]);

        return $submission;
    }

    /**
     * Check submission status with government API.
     */
    public function checkStatus(EReportingSubmission $submission): string
    {
        if ($this->testMode && !$this->company->ereporting_api_key) {
            // Simulate acceptance after some time
            if ($submission->status === self::STATUS_SUBMITTED) {
                $minutesSinceSubmission = $submission->submitted_at->diffInMinutes(now());
                if ($minutesSinceSubmission >= 1) {
                    $submission->update([
                        'status' => self::STATUS_ACCEPTED,
                        'accepted_at' => now(),
                    ]);

                    if ($submission->invoice) {
                        $submission->invoice->update([
                            'ereporting_status' => self::STATUS_ACCEPTED,
                        ]);
                    }
                }
            }
            return $submission->fresh()->status;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->company->ereporting_api_key,
            ])->get("{$this->apiBaseUrl}/submissions/{$submission->submission_id}/status");

            if ($response->successful()) {
                $data = $response->json();
                $newStatus = $data['status'] ?? $submission->status;

                $updateData = ['status' => $newStatus];
                if ($newStatus === self::STATUS_ACCEPTED) {
                    $updateData['accepted_at'] = now();
                } elseif ($newStatus === self::STATUS_REJECTED) {
                    $updateData['error_message'] = $data['rejection_reason'] ?? null;
                }

                $submission->update($updateData);

                if ($submission->invoice) {
                    $submission->invoice->update([
                        'ereporting_status' => $newStatus,
                    ]);
                }

                return $newStatus;
            }
        } catch (\Exception $e) {
            Log::error('E-Reporting status check failed', [
                'submission_id' => $submission->submission_id,
                'error' => $e->getMessage(),
            ]);
        }

        return $submission->status;
    }

    /**
     * Batch submit multiple invoices.
     */
    public function batchSubmit(array $invoiceIds): array
    {
        $results = [];
        $invoices = Invoice::whereIn('id', $invoiceIds)
            ->where('company_id', $this->company->id)
            ->whereNull('ereporting_submitted_at')
            ->get();

        foreach ($invoices as $invoice) {
            try {
                $submission = $this->submitInvoice($invoice);
                $results[] = [
                    'invoice_id' => $invoice->id,
                    'success' => true,
                    'submission_id' => $submission->submission_id,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'invoice_id' => $invoice->id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get submission statistics for dashboard.
     */
    public function getStatistics(?string $period = 'month'): array
    {
        $query = EReportingSubmission::where('company_id', $this->company->id);

        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->startOfMonth());
                break;
            case 'quarter':
                $query->where('created_at', '>=', now()->startOfQuarter());
                break;
            case 'year':
                $query->where('created_at', '>=', now()->startOfYear());
                break;
        }

        $total = $query->count();
        $accepted = (clone $query)->where('status', self::STATUS_ACCEPTED)->count();
        $rejected = (clone $query)->where('status', self::STATUS_REJECTED)->count();
        $pending = (clone $query)->whereIn('status', [self::STATUS_PENDING, self::STATUS_SUBMITTED])->count();
        $errors = (clone $query)->where('status', self::STATUS_ERROR)->count();

        return [
            'period' => $period,
            'total' => $total,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'pending' => $pending,
            'errors' => $errors,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Check if e-Reporting is required for an invoice.
     * Based on Belgian B2B mandate rules.
     */
    public function isEReportingRequired(Invoice $invoice): bool
    {
        // e-Reporting is only for Belgian B2B transactions
        if (!$invoice->partner) {
            return false;
        }

        $partner = $invoice->partner;

        // Must have Belgian VAT number or be in Belgium
        $isBelgianPartner = ($partner->country_code === 'BE' || $partner->country_code === null)
            && (!empty($partner->vat_number) || !empty($partner->enterprise_number));

        // B2C transactions are excluded
        if (!$isBelgianPartner) {
            return false;
        }

        // Check if company has e-Reporting enabled
        if (!$this->company->ereporting_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Generate compliance report for a period.
     */
    public function generateComplianceReport(string $startDate, string $endDate): array
    {
        $submissions = EReportingSubmission::where('company_id', $this->company->id)
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->with('invoice')
            ->get();

        $salesSubmissions = $submissions->where('type', self::TYPE_SALES);
        $purchaseSubmissions = $submissions->where('type', self::TYPE_PURCHASES);

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'company' => [
                'name' => $this->company->name,
                'vat_number' => $this->company->vat_number,
            ],
            'sales' => [
                'count' => $salesSubmissions->count(),
                'total_excl_vat' => $salesSubmissions->sum(fn($s) => $s->invoice?->total_excl_vat ?? 0),
                'total_vat' => $salesSubmissions->sum(fn($s) => $s->invoice?->total_vat ?? 0),
                'accepted' => $salesSubmissions->where('status', self::STATUS_ACCEPTED)->count(),
                'rejected' => $salesSubmissions->where('status', self::STATUS_REJECTED)->count(),
            ],
            'purchases' => [
                'count' => $purchaseSubmissions->count(),
                'total_excl_vat' => $purchaseSubmissions->sum(fn($s) => $s->invoice?->total_excl_vat ?? 0),
                'total_vat' => $purchaseSubmissions->sum(fn($s) => $s->invoice?->total_vat ?? 0),
                'accepted' => $purchaseSubmissions->where('status', self::STATUS_ACCEPTED)->count(),
                'rejected' => $purchaseSubmissions->where('status', self::STATUS_REJECTED)->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
