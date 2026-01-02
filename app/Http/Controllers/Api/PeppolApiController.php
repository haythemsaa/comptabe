<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Services\PeppolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PeppolApiController extends Controller
{
    /**
     * Lookup a Peppol participant.
     */
    public function lookup(string $participantId): JsonResponse
    {
        // TODO: Implement actual Peppol SMP lookup
        return response()->json([
            'success' => true,
            'data' => [
                'participant_id' => $participantId,
                'registered' => false,
                'message' => 'Peppol lookup not yet implemented',
            ],
        ]);
    }

    /**
     * Get Peppol inbox (received invoices).
     */
    public function inbox(Request $request): JsonResponse
    {
        $invoices = Invoice::where('type', 'in')
            ->whereNotNull('peppol_received_at')
            ->orderByDesc('peppol_received_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Send invoice via Peppol.
     */
    public function send(Invoice $invoice): JsonResponse
    {
        if (!$invoice->canSendViaPeppol()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice cannot be sent via Peppol',
            ], 422);
        }

        // TODO: Implement actual Peppol sending
        $invoice->update([
            'peppol_status' => 'pending',
            'peppol_sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice queued for Peppol delivery',
            'data' => [
                'peppol_status' => $invoice->peppol_status,
                'peppol_sent_at' => $invoice->peppol_sent_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Handle Peppol webhooks (generic).
     */
    public function webhook(Request $request): JsonResponse
    {
        Log::info('Peppol webhook received (generic)', [
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
        ]);

        // Try to identify company from payload
        $peppolId = $request->input('recipient_id')
            ?? $request->input('receiver.peppol_id')
            ?? null;

        if ($peppolId) {
            $company = Company::where('peppol_id', $peppolId)->first();

            if ($company) {
                $peppolService = app(PeppolService::class);
                $result = $peppolService->handleWebhook($company, $request->all());

                return response()->json($result);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received',
        ]);
    }

    /**
     * Handle company-specific Peppol webhooks with secret validation.
     */
    public function companyWebhook(Request $request, string $secret): JsonResponse
    {
        // Find company by webhook secret
        $company = Company::where('peppol_webhook_secret', $secret)->first();

        if (!$company) {
            Log::warning('Peppol webhook received with invalid secret', [
                'secret_prefix' => substr($secret, 0, 8) . '...',
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook secret',
            ], 401);
        }

        Log::info('Peppol webhook received', [
            'company_id' => $company->id,
            'event_type' => $request->input('event_type') ?? $request->input('type'),
        ]);

        try {
            $peppolService = app(PeppolService::class);
            $result = $peppolService->handleWebhook($company, $request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Peppol webhook processing failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}
