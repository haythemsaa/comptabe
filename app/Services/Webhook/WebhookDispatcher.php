<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Jobs\SendWebhookJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    /**
     * Événements disponibles
     */
    const EVENTS = [
        // Invoices
        'invoice.created',
        'invoice.updated',
        'invoice.deleted',
        'invoice.validated',
        'invoice.sent',
        'invoice.paid',
        'invoice.payment_received',
        'invoice.overdue',

        // Partners
        'partner.created',
        'partner.updated',
        'partner.deleted',

        // Bank
        'bank.transaction_imported',
        'bank.transaction_matched',
        'bank.account_synced',

        // Approvals
        'approval.requested',
        'approval.approved',
        'approval.rejected',
        'approval.expired',

        // Documents
        'document.scanned',
        'document.processed',

        // Treasury
        'treasury.alert',
        'treasury.forecast_updated',
    ];

    /**
     * Dispatcher un événement à tous les webhooks abonnés
     */
    public function dispatch(string $event, mixed $payload, ?int $companyId = null): void
    {
        $companyId = $companyId ?? auth()->user()?->current_company_id;

        if (!$companyId) {
            return;
        }

        $webhooks = Webhook::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) use ($event) {
                $query->whereJsonContains('events', $event)
                      ->orWhereJsonContains('events', '*');
            })
            ->get();

        foreach ($webhooks as $webhook) {
            SendWebhookJob::dispatch($webhook, $event, $payload);
        }
    }

    /**
     * Envoyer un webhook de manière synchrone
     */
    public function send(Webhook $webhook, string $event, mixed $payload): WebhookDelivery
    {
        $deliveryId = Str::uuid()->toString();

        $body = [
            'id' => $deliveryId,
            'event' => $event,
            'created_at' => now()->toIso8601String(),
            'data' => $payload,
        ];

        // Signer le payload
        $signature = $this->generateSignature($body, $webhook->secret);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-ID' => $deliveryId,
            'X-Webhook-Event' => $event,
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => now()->timestamp,
            'User-Agent' => 'ComptaBE-Webhook/1.0',
        ];

        // Ajouter les headers personnalisés
        if ($webhook->headers) {
            $headers = array_merge($headers, $webhook->headers);
        }

        $startTime = microtime(true);
        $delivery = new WebhookDelivery([
            'webhook_id' => $webhook->id,
            'delivery_id' => $deliveryId,
            'event' => $event,
            'payload' => $body,
            'request_headers' => $headers,
        ]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout($webhook->timeout ?? 30)
                ->retry($webhook->max_retries ?? 3, 1000)
                ->post($webhook->url, $body);

            $delivery->fill([
                'response_status' => $response->status(),
                'response_headers' => $response->headers(),
                'response_body' => Str::limit($response->body(), 10000),
                'response_time_ms' => (microtime(true) - $startTime) * 1000,
                'success' => $response->successful(),
                'delivered_at' => now(),
            ]);

            // Mettre à jour les statistiques du webhook
            $webhook->increment('delivery_count');
            if ($response->successful()) {
                $webhook->increment('success_count');
                $webhook->update(['last_success_at' => now()]);
            } else {
                $webhook->increment('failure_count');
                $webhook->update(['last_failure_at' => now()]);
            }

        } catch (\Exception $e) {
            $delivery->fill([
                'success' => false,
                'error_message' => $e->getMessage(),
                'response_time_ms' => (microtime(true) - $startTime) * 1000,
            ]);

            $webhook->increment('delivery_count');
            $webhook->increment('failure_count');
            $webhook->update(['last_failure_at' => now()]);

            Log::error('Webhook delivery failed', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }

        $delivery->save();

        // Désactiver le webhook si trop d'échecs consécutifs
        $this->checkWebhookHealth($webhook);

        return $delivery;
    }

    /**
     * Générer la signature HMAC
     */
    public function generateSignature(array $payload, string $secret): string
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return 'sha256=' . hash_hmac('sha256', $jsonPayload, $secret);
    }

    /**
     * Vérifier la signature d'un webhook entrant
     */
    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Vérifier la santé du webhook et le désactiver si nécessaire
     */
    protected function checkWebhookHealth(Webhook $webhook): void
    {
        // Vérifier les 10 dernières livraisons
        $recentDeliveries = $webhook->deliveries()
            ->latest()
            ->limit(10)
            ->pluck('success');

        // Si toutes les 10 dernières ont échoué, désactiver
        if ($recentDeliveries->count() >= 10 && !$recentDeliveries->contains(true)) {
            $webhook->update([
                'is_active' => false,
                'disabled_reason' => 'Désactivé automatiquement: 10 échecs consécutifs',
                'disabled_at' => now(),
            ]);

            Log::warning('Webhook auto-disabled due to consecutive failures', [
                'webhook_id' => $webhook->id,
                'url' => $webhook->url,
            ]);
        }
    }

    /**
     * Renvoyer une livraison échouée
     */
    public function retry(WebhookDelivery $delivery): WebhookDelivery
    {
        $webhook = $delivery->webhook;

        if (!$webhook->is_active) {
            throw new \Exception('Le webhook est désactivé');
        }

        return $this->send($webhook, $delivery->event, $delivery->payload['data']);
    }

    /**
     * Ping un webhook pour vérifier la connectivité
     */
    public function ping(Webhook $webhook): array
    {
        $body = [
            'event' => 'webhook.ping',
            'created_at' => now()->toIso8601String(),
            'data' => ['test' => true],
        ];

        $signature = $this->generateSignature($body, $webhook->secret);

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => 'webhook.ping',
                'X-Webhook-Signature' => $signature,
            ])
            ->timeout(10)
            ->post($webhook->url, $body);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'message' => $response->successful() ? 'Webhook accessible' : 'Erreur: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => null,
                'response_time_ms' => round((microtime(true) - $startTime) * 1000),
                'message' => 'Erreur: ' . $e->getMessage(),
            ];
        }
    }
}
