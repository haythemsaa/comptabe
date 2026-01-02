<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\Webhook\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class InvoiceApiController extends Controller
{
    public function __construct(
        protected WebhookDispatcher $webhookDispatcher
    ) {}

    /**
     * Liste des factures
     *
     * @OA\Get(
     *     path="/api/v1/invoices",
     *     summary="Liste des factures",
     *     tags={"Invoices"},
     *     @OA\Parameter(name="type", in="query", description="sale|purchase"),
     *     @OA\Parameter(name="status", in="query", description="draft|validated|sent|paid"),
     *     @OA\Parameter(name="from", in="query", description="Date début (Y-m-d)"),
     *     @OA\Parameter(name="to", in="query", description="Date fin (Y-m-d)"),
     *     @OA\Parameter(name="partner_id", in="query", description="ID du partenaire"),
     *     @OA\Parameter(name="page", in="query", description="Numéro de page"),
     *     @OA\Parameter(name="per_page", in="query", description="Éléments par page (max 100)"),
     *     @OA\Response(response=200, description="Liste paginée des factures")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::where('company_id', $request->user()->current_company_id)
            ->with(['partner:id,name,vat_number', 'lines']);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('issue_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('issue_date', '<=', $request->to);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        $perPage = min($request->integer('per_page', 25), 100);

        $invoices = $query->orderByDesc('issue_date')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Détail d'une facture
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        $invoice->load(['partner', 'lines', 'payments', 'journalEntries']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Créer une facture
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:sale,purchase',
            'partner_id' => 'required|exists:partners,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'reference' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:2000',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:500',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate' => 'required|numeric|in:0,6,12,21',
            'lines.*.account_code' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $companyId = $request->user()->current_company_id;

        // Créer la facture
        $invoice = Invoice::create([
            'company_id' => $companyId,
            'type' => $request->type,
            'partner_id' => $request->partner_id,
            'number' => $this->generateInvoiceNumber($companyId, $request->type),
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date ?? now()->addDays(30),
            'currency' => $request->currency ?? 'EUR',
            'reference' => $request->reference,
            'notes' => $request->notes,
            'status' => 'draft',
        ]);

        // Créer les lignes
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($request->lines as $lineData) {
            $lineTotal = $lineData['quantity'] * $lineData['unit_price'];
            $lineTax = $lineTotal * ($lineData['vat_rate'] / 100);

            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'description' => $lineData['description'],
                'quantity' => $lineData['quantity'],
                'unit_price' => $lineData['unit_price'],
                'vat_rate' => $lineData['vat_rate'],
                'total_excl_vat' => $lineTotal,
                'vat_amount' => $lineTax,
                'total_incl_vat' => $lineTotal + $lineTax,
                'account_code' => $lineData['account_code'] ?? null,
            ]);

            $subtotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        // Mettre à jour les totaux
        $invoice->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxTotal,
            'total_amount' => $subtotal + $taxTotal,
        ]);

        $invoice->load(['partner', 'lines']);

        // Déclencher le webhook
        $this->webhookDispatcher->dispatch('invoice.created', $invoice);

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'message' => 'Facture créée avec succès.',
        ], 201);
    }

    /**
     * Mettre à jour une facture
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        if (!in_array($invoice->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Seules les factures en brouillon peuvent être modifiées.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'partner_id' => 'sometimes|exists:partners,id',
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:issue_date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice->update($request->only([
            'partner_id', 'issue_date', 'due_date', 'reference', 'notes'
        ]));

        $invoice->load(['partner', 'lines']);

        $this->webhookDispatcher->dispatch('invoice.updated', $invoice);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        if (!in_array($invoice->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Seules les factures en brouillon peuvent être supprimées.',
            ], 400);
        }

        $invoiceData = $invoice->toArray();
        $invoice->lines()->delete();
        $invoice->delete();

        $this->webhookDispatcher->dispatch('invoice.deleted', $invoiceData);

        return response()->json([
            'success' => true,
            'message' => 'Facture supprimée.',
        ]);
    }

    /**
     * Valider une facture
     */
    public function markAsValidated(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cette facture ne peut pas être validée.',
            ], 400);
        }

        $invoice->update([
            'status' => 'validated',
            'validated_at' => now(),
        ]);

        $this->webhookDispatcher->dispatch('invoice.validated', $invoice);

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'message' => 'Facture validée.',
        ]);
    }

    /**
     * Enregistrer un paiement
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = $invoice->payments()->create([
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
        ]);

        // Vérifier si facture entièrement payée
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->webhookDispatcher->dispatch('invoice.paid', $invoice);
        } else {
            $this->webhookDispatcher->dispatch('invoice.payment_received', [
                'invoice' => $invoice,
                'payment' => $payment,
                'remaining' => $invoice->total_amount - $totalPaid,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $payment,
                'invoice_status' => $invoice->fresh()->status,
                'total_paid' => $totalPaid,
                'remaining' => max(0, $invoice->total_amount - $totalPaid),
            ],
        ]);
    }

    /**
     * Télécharger PDF
     */
    public function downloadPdf(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        // Générer l'URL temporaire du PDF
        $pdfUrl = route('invoices.pdf', $invoice);

        return response()->json([
            'success' => true,
            'data' => [
                'pdf_url' => $pdfUrl,
                'expires_at' => now()->addHours(1)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Télécharger UBL (Peppol)
     */
    public function downloadUbl(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeForCompany($request, $invoice);

        $ublUrl = route('invoices.ubl', $invoice);

        return response()->json([
            'success' => true,
            'data' => [
                'ubl_url' => $ublUrl,
                'format' => 'UBL 2.1',
                'peppol_compliant' => true,
            ],
        ]);
    }

    // ===== HELPERS =====

    protected function authorizeForCompany(Request $request, Invoice $invoice): void
    {
        if ($invoice->company_id !== $request->user()->current_company_id) {
            abort(403, 'Non autorisé');
        }
    }

    protected function generateInvoiceNumber(int $companyId, string $type): string
    {
        $prefix = $type === 'sale' ? 'FAC' : 'ACH';
        $year = now()->format('Y');

        $lastNumber = Invoice::where('company_id', $companyId)
            ->where('type', $type)
            ->whereYear('created_at', $year)
            ->max('number');

        if ($lastNumber) {
            $sequence = intval(substr($lastNumber, -5)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . $year . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
