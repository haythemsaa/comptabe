<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PartnerApiController extends Controller
{
    public function __construct(
        protected WebhookDispatcher $webhookDispatcher
    ) {}

    /**
     * Liste des partenaires
     */
    public function index(Request $request): JsonResponse
    {
        $query = Partner::where('company_id', $request->user()->current_company_id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('vat_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('is_customer')) {
            $query->where('is_customer', true);
        }

        if ($request->boolean('is_supplier')) {
            $query->where('is_supplier', true);
        }

        $perPage = min($request->integer('per_page', 25), 100);
        $partners = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $partners->items(),
            'meta' => [
                'current_page' => $partners->currentPage(),
                'last_page' => $partners->lastPage(),
                'per_page' => $partners->perPage(),
                'total' => $partners->total(),
            ],
        ]);
    }

    /**
     * Détail d'un partenaire
     */
    public function show(Request $request, Partner $partner): JsonResponse
    {
        $this->authorizeForCompany($request, $partner);

        $partner->load(['invoices' => function ($q) {
            $q->orderByDesc('issue_date')->limit(10);
        }]);

        // Statistiques
        $stats = [
            'total_invoiced' => $partner->invoices()->where('type', 'sale')->sum('total_amount'),
            'total_purchased' => $partner->invoices()->where('type', 'purchase')->sum('total_amount'),
            'unpaid_amount' => $partner->invoices()
                ->whereIn('status', ['validated', 'sent'])
                ->sum('total_amount'),
            'invoice_count' => $partner->invoices()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $partner,
            'stats' => $stats,
        ]);
    }

    /**
     * Créer un partenaire
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:company,individual',
            'vat_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'notes' => 'nullable|string|max:2000',
            'peppol_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $partner = Partner::create([
            'company_id' => $request->user()->current_company_id,
            ...$request->only([
                'name', 'type', 'vat_number', 'email', 'phone', 'website',
                'address_line1', 'address_line2', 'postal_code', 'city', 'country',
                'iban', 'bic', 'payment_terms', 'is_customer', 'is_supplier',
                'notes', 'peppol_id'
            ]),
        ]);

        $this->webhookDispatcher->dispatch('partner.created', $partner);

        return response()->json([
            'success' => true,
            'data' => $partner,
            'message' => 'Partenaire créé avec succès.',
        ], 201);
    }

    /**
     * Mettre à jour un partenaire
     */
    public function update(Request $request, Partner $partner): JsonResponse
    {
        $this->authorizeForCompany($request, $partner);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:company,individual',
            'vat_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'notes' => 'nullable|string|max:2000',
            'peppol_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $partner->update($request->only([
            'name', 'type', 'vat_number', 'email', 'phone', 'website',
            'address_line1', 'address_line2', 'postal_code', 'city', 'country',
            'iban', 'bic', 'payment_terms', 'is_customer', 'is_supplier',
            'notes', 'peppol_id'
        ]));

        $this->webhookDispatcher->dispatch('partner.updated', $partner);

        return response()->json([
            'success' => true,
            'data' => $partner,
        ]);
    }

    /**
     * Supprimer un partenaire
     */
    public function destroy(Request $request, Partner $partner): JsonResponse
    {
        $this->authorizeForCompany($request, $partner);

        // Vérifier qu'il n'y a pas de factures liées
        if ($partner->invoices()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer: des factures sont liées à ce partenaire.',
            ], 400);
        }

        $partnerData = $partner->toArray();
        $partner->delete();

        $this->webhookDispatcher->dispatch('partner.deleted', $partnerData);

        return response()->json([
            'success' => true,
            'message' => 'Partenaire supprimé.',
        ]);
    }

    /**
     * Vérifier numéro TVA via VIES
     */
    public function verifyVat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vat_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simulation vérification VIES
        // En production, utiliser le service VIES réel
        $vatNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper($request->vat_number));
        $isValid = strlen($vatNumber) >= 9 && strlen($vatNumber) <= 12;

        return response()->json([
            'success' => true,
            'data' => [
                'vat_number' => $vatNumber,
                'is_valid' => $isValid,
                'country' => substr($vatNumber, 0, 2),
                'verified_at' => now()->toIso8601String(),
            ],
        ]);
    }

    protected function authorizeForCompany(Request $request, Partner $partner): void
    {
        if ($partner->company_id !== $request->user()->current_company_id) {
            abort(403, 'Non autorisé');
        }
    }
}
