<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VatCode;
use App\Services\Peppol\PeppolDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query()
            ->withCount('invoices')
            ->withSum('invoices', 'total_incl_vat');

        // Type filter
        if ($request->filled('type')) {
            if ($request->type === 'customer') {
                $query->customers();
            } elseif ($request->type === 'supplier') {
                $query->suppliers();
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Peppol filter
        if ($request->filled('peppol')) {
            $query->where('peppol_capable', $request->peppol === '1');
        }

        // PERFORMANCE: Increase pagination from 12 to 50
        $partners = $query->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        // Add computed property
        $partners->getCollection()->transform(function ($partner) {
            $partner->total_revenue = $partner->invoices_sum_total_incl_vat ?? 0;
            return $partner;
        });

        // PERFORMANCE: Optimize stats with single query instead of 3
        $statsQuery = Partner::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN type IN (\'customer\', \'both\') THEN 1 END) as customers,
            COUNT(CASE WHEN type IN (\'supplier\', \'both\') THEN 1 END) as suppliers
        ')->first();

        $stats = [
            'total' => $statsQuery->total ?? 0,
            'customers' => $statsQuery->customers ?? 0,
            'suppliers' => $statsQuery->suppliers ?? 0,
        ];

        return view('partners.index', compact('partners', 'stats'));
    }

    public function create()
    {
        $vatCodes = VatCode::where('is_active', true)->orderBy('rate', 'desc')->get();

        return view('partners.create', compact('vatCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'enterprise_number' => 'nullable|string|max:50',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|size:2',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'peppol_identifier' => 'nullable|string|max:255',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'default_vat_rate' => 'nullable|numeric|min:0|max:100',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
        ]);

        // Normalize checkboxes
        $validated['is_customer'] = $request->boolean('is_customer');
        $validated['is_supplier'] = $request->boolean('is_supplier');

        // Check Peppol capability if VAT number provided
        if (!empty($validated['vat_number'])) {
            $validated['peppol_capable'] = $this->checkPeppolCapability($validated['vat_number']);
        }

        $partner = Partner::create($validated);

        return redirect()
            ->route('partners.show', $partner)
            ->with('success', 'Partenaire créé avec succès.');
    }

    public function show(Partner $partner)
    {
        $partner->load(['invoices' => function ($query) {
            $query->latest('invoice_date')->limit(10);
        }]);

        $stats = [
            'total_invoices' => $partner->invoices()->count(),
            'total_revenue' => $partner->invoices()->sum('total_incl_vat'),
            'unpaid_amount' => $partner->invoices()->whereIn('status', ['sent', 'overdue'])->sum('total_incl_vat'),
            'avg_payment_days' => $partner->invoices()
                ->whereNotNull('paid_at')
                ->selectRaw('AVG(DATEDIFF(paid_at, invoice_date)) as avg_days')
                ->value('avg_days'),
        ];

        return view('partners.show', compact('partner', 'stats'));
    }

    public function edit(Partner $partner)
    {
        $vatCodes = VatCode::where('is_active', true)->orderBy('rate', 'desc')->get();

        return view('partners.edit', compact('partner', 'vatCodes'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:50',
            'enterprise_number' => 'nullable|string|max:50',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|size:2',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'peppol_identifier' => 'nullable|string|max:255',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'default_vat_rate' => 'nullable|numeric|min:0|max:100',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
        ]);

        $validated['is_customer'] = $request->boolean('is_customer');
        $validated['is_supplier'] = $request->boolean('is_supplier');

        // Re-check Peppol if VAT number changed
        if (!empty($validated['vat_number']) && $validated['vat_number'] !== $partner->vat_number) {
            $validated['peppol_capable'] = $this->checkPeppolCapability($validated['vat_number']);
        }

        $partner->update($validated);

        return redirect()
            ->route('partners.show', $partner)
            ->with('success', 'Partenaire mis à jour avec succès.');
    }

    public function destroy(Partner $partner)
    {
        // Check if partner has invoices
        if ($partner->invoices()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer ce partenaire car il a des factures associées.');
        }

        $partner->delete();

        return redirect()
            ->route('partners.index')
            ->with('success', 'Partenaire supprimé avec succès.');
    }

    /**
     * Check if a VAT number is Peppol capable (via Peppol Directory lookup)
     */
    protected function checkPeppolCapability(string $vatNumber): bool
    {
        try {
            $directoryService = app(PeppolDirectoryService::class);
            $result = $directoryService->verifyBelgianVat($vatNumber);

            return $result['found'] ?? false;
        } catch (\Exception $e) {
            // Fallback: Belgian companies will be Peppol-enabled from 2026
            $cleanVat = preg_replace('/[^a-zA-Z0-9]/', '', $vatNumber);
            if (str_starts_with(strtoupper($cleanVat), 'BE')) {
                return true;
            }

            return false;
        }
    }

    /**
     * Verify Peppol registration for a VAT number (AJAX endpoint).
     */
    public function verifyPeppol(Request $request, PeppolDirectoryService $directoryService)
    {
        $request->validate([
            'vat_number' => 'required|string',
        ]);

        $result = $directoryService->verifyBelgianVat($request->vat_number);

        return response()->json([
            'registered' => $result['found'] ?? false,
            'peppol_id' => $result['peppol_id'] ?? null,
            'name' => $result['name'] ?? null,
            'message' => $result['message'] ?? ($result['found'] ? 'Enregistre dans le reseau Peppol' : 'Non enregistre'),
        ]);
    }

    /**
     * Search Peppol Directory by name (AJAX endpoint).
     */
    public function searchPeppol(Request $request, PeppolDirectoryService $directoryService)
    {
        $request->validate([
            'name' => 'required|string|min:3',
            'country' => 'nullable|string|size:2',
        ]);

        $result = $directoryService->searchByName(
            $request->name,
            $request->country ?? 'BE'
        );

        return response()->json($result);
    }
}
