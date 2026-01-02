<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookDispatcher $dispatcher
    ) {}

    /**
     * Liste des événements disponibles
     */
    public function events(): JsonResponse
    {
        $events = collect(WebhookDispatcher::EVENTS)->map(function ($event) {
            $parts = explode('.', $event);
            return [
                'event' => $event,
                'category' => $parts[0],
                'action' => $parts[1] ?? '',
                'description' => $this->getEventDescription($event),
            ];
        })->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Liste des webhooks
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = Webhook::where('company_id', $request->user()->current_company_id)
            ->withCount(['deliveries', 'deliveries as failed_deliveries_count' => function ($q) {
                $q->where('success', false);
            }])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $webhooks,
        ]);
    }

    /**
     * Créer un webhook
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', [...WebhookDispatcher::EVENTS, '*']),
            'headers' => 'nullable|array',
            'timeout' => 'nullable|integer|min:5|max:60',
            'max_retries' => 'nullable|integer|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Générer un secret
        $secret = Str::random(64);

        $webhook = Webhook::create([
            'company_id' => $request->user()->current_company_id,
            'name' => $request->name,
            'url' => $request->url,
            'secret' => $secret,
            'events' => $request->events,
            'headers' => $request->headers,
            'timeout' => $request->timeout ?? 30,
            'max_retries' => $request->max_retries ?? 3,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'secret' => $secret, // Affiché une seule fois
            'message' => 'Webhook créé. Conservez le secret précieusement, il ne sera plus affiché.',
        ], 201);
    }

    /**
     * Détail d'un webhook
     */
    public function show(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $webhook->load(['deliveries' => function ($q) {
            $q->orderByDesc('created_at')->limit(20);
        }]);

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'stats' => [
                'total_deliveries' => $webhook->delivery_count,
                'successful' => $webhook->success_count,
                'failed' => $webhook->failure_count,
                'success_rate' => $webhook->delivery_count > 0
                    ? round(($webhook->success_count / $webhook->delivery_count) * 100, 1)
                    : 0,
            ],
        ]);
    }

    /**
     * Mettre à jour un webhook
     */
    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string|in:' . implode(',', [...WebhookDispatcher::EVENTS, '*']),
            'headers' => 'nullable|array',
            'timeout' => 'nullable|integer|min:5|max:60',
            'max_retries' => 'nullable|integer|min:0|max:5',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webhook->update($request->only([
            'name', 'url', 'events', 'headers', 'timeout', 'max_retries', 'is_active'
        ]));

        // Si réactivé, effacer la raison de désactivation
        if ($request->boolean('is_active') && $webhook->disabled_at) {
            $webhook->update([
                'disabled_reason' => null,
                'disabled_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $webhook,
        ]);
    }

    /**
     * Supprimer un webhook
     */
    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $webhook->deliveries()->delete();
        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook supprimé.',
        ]);
    }

    /**
     * Régénérer le secret
     */
    public function regenerateSecret(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $newSecret = Str::random(64);
        $webhook->update(['secret' => $newSecret]);

        return response()->json([
            'success' => true,
            'secret' => $newSecret,
            'message' => 'Secret régénéré. Mettez à jour votre endpoint.',
        ]);
    }

    /**
     * Tester un webhook (ping)
     */
    public function ping(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $result = $this->dispatcher->ping($webhook);

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
        ]);
    }

    /**
     * Historique des livraisons
     */
    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        $query = $webhook->deliveries()->orderByDesc('created_at');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('success')) {
            $query->where('success', $request->boolean('success'));
        }

        $perPage = min($request->integer('per_page', 25), 100);
        $deliveries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
            ],
        ]);
    }

    /**
     * Détail d'une livraison
     */
    public function deliveryDetail(Request $request, Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        if ($delivery->webhook_id !== $webhook->id) {
            abort(404);
        }

        return response()->json([
            'success' => true,
            'data' => $delivery,
        ]);
    }

    /**
     * Renvoyer une livraison
     */
    public function retry(Request $request, Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        $this->authorizeForCompany($request, $webhook);

        if ($delivery->webhook_id !== $webhook->id) {
            abort(404);
        }

        try {
            $newDelivery = $this->dispatcher->retry($delivery);

            return response()->json([
                'success' => true,
                'data' => $newDelivery,
                'message' => $newDelivery->success ? 'Livraison réussie' : 'Échec de la livraison',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // ===== HELPERS =====

    protected function authorizeForCompany(Request $request, Webhook $webhook): void
    {
        if ($webhook->company_id !== $request->user()->current_company_id) {
            abort(403, 'Non autorisé');
        }
    }

    protected function getEventDescription(string $event): string
    {
        $descriptions = [
            'invoice.created' => 'Une nouvelle facture a été créée',
            'invoice.updated' => 'Une facture a été modifiée',
            'invoice.deleted' => 'Une facture a été supprimée',
            'invoice.validated' => 'Une facture a été validée',
            'invoice.sent' => 'Une facture a été envoyée',
            'invoice.paid' => 'Une facture a été entièrement payée',
            'invoice.payment_received' => 'Un paiement partiel a été reçu',
            'invoice.overdue' => 'Une facture est en retard de paiement',
            'partner.created' => 'Un nouveau partenaire a été créé',
            'partner.updated' => 'Un partenaire a été modifié',
            'partner.deleted' => 'Un partenaire a été supprimé',
            'bank.transaction_imported' => 'Des transactions bancaires ont été importées',
            'bank.transaction_matched' => 'Une transaction a été rapprochée',
            'bank.account_synced' => 'Un compte bancaire a été synchronisé',
            'approval.requested' => 'Une approbation a été demandée',
            'approval.approved' => 'Une demande a été approuvée',
            'approval.rejected' => 'Une demande a été rejetée',
            'approval.expired' => 'Une demande d\'approbation a expiré',
            'document.scanned' => 'Un document a été scanné',
            'document.processed' => 'Un document a été traité par OCR',
            'treasury.alert' => 'Une alerte de trésorerie a été déclenchée',
            'treasury.forecast_updated' => 'Les prévisions de trésorerie ont été mises à jour',
        ];

        return $descriptions[$event] ?? $event;
    }
}
