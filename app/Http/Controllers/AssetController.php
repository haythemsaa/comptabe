<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDepreciation;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::where('company_id', auth()->user()->current_company_id)
            ->with(['category', 'partner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderByDesc('acquisition_date')->paginate(20);

        $categories = AssetCategory::where('company_id', auth()->user()->current_company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total_value' => Asset::where('company_id', auth()->user()->current_company_id)
                ->whereIn('status', ['active', 'fully_depreciated'])
                ->sum('acquisition_cost'),
            'net_value' => Asset::where('company_id', auth()->user()->current_company_id)
                ->whereIn('status', ['active', 'fully_depreciated'])
                ->sum('current_value'),
            'total_depreciation' => Asset::where('company_id', auth()->user()->current_company_id)
                ->whereIn('status', ['active', 'fully_depreciated'])
                ->sum('accumulated_depreciation'),
            'active_count' => Asset::where('company_id', auth()->user()->current_company_id)
                ->where('status', 'active')
                ->count(),
        ];

        return view('assets.index', compact('assets', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = AssetCategory::where('company_id', auth()->user()->current_company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $partners = Partner::where('company_id', auth()->user()->current_company_id)
            ->where('type', 'supplier')
            ->orderBy('name')
            ->get();

        return view('assets.create', compact('categories', 'partners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:asset_categories,id',
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'acquisition_date' => 'required|date',
            'service_date' => 'required|date|after_or_equal:acquisition_date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'numeric|min:0',
            'depreciation_method' => 'required|in:linear,degressive,units_of_production',
            'useful_life' => 'required|numeric|min:0.5|max:50',
            'degressive_rate' => 'nullable|numeric|min:0|max:100',
            'total_units' => 'nullable|integer|min:1',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;
        $validated['status'] = 'draft';
        $validated['current_value'] = $validated['acquisition_cost'];
        $validated['accumulated_depreciation'] = 0;

        // Copy defaults from category
        if ($request->filled('category_id')) {
            $category = AssetCategory::find($request->category_id);
            if ($category) {
                $validated['depreciation_method'] = $validated['depreciation_method'] ?? $category->depreciation_method;
                $validated['useful_life'] = $validated['useful_life'] ?? $category->default_useful_life;
                $validated['degressive_rate'] = $validated['degressive_rate'] ?? $category->degressive_rate;
            }
        }

        $asset = Asset::create($validated);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Immobilisation créée avec succès.');
    }

    public function show(Asset $asset)
    {
        $this->authorize('view', $asset);

        $asset->load(['category', 'partner', 'depreciations', 'logs.user', 'vehicle']);

        // Generate depreciation schedule if not exists
        if ($asset->depreciations->isEmpty() && $asset->status !== 'draft') {
            $this->generateDepreciationSchedule($asset);
            $asset->load('depreciations');
        }

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $this->authorize('update', $asset);

        $categories = AssetCategory::where('company_id', auth()->user()->current_company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $partners = Partner::where('company_id', auth()->user()->current_company_id)
            ->where('type', 'supplier')
            ->orderBy('name')
            ->get();

        return view('assets.edit', compact('asset', 'categories', 'partners'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'category_id' => 'nullable|exists:asset_categories,id',
            'partner_id' => 'nullable|exists:partners,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);

        $oldValues = $asset->only(array_keys($validated));
        $asset->update($validated);
        $asset->log('modified', 'Immobilisation modifiée', $oldValues, $validated);

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Immobilisation mise à jour.');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('success', 'Immobilisation supprimée.');
    }

    public function activate(Asset $asset)
    {
        $this->authorize('update', $asset);

        $asset->activate();
        $this->generateDepreciationSchedule($asset);

        return back()->with('success', 'Immobilisation mise en service.');
    }

    public function dispose(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'disposal_amount' => 'nullable|numeric|min:0',
            'disposal_notes' => 'nullable|string',
        ]);

        $asset->dispose($validated['disposal_amount'] ?? null, $validated['disposal_notes'] ?? null);

        return back()->with('success', 'Immobilisation sortie du patrimoine.');
    }

    public function sell(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'disposal_amount' => 'required|numeric|min:0',
            'disposal_notes' => 'nullable|string',
        ]);

        $asset->sell($validated['disposal_amount'], $validated['disposal_notes'] ?? null);

        return back()->with('success', 'Immobilisation vendue.');
    }

    public function postDepreciation(AssetDepreciation $depreciation)
    {
        $this->authorize('update', $depreciation->asset);

        if (!$depreciation->canBePosted()) {
            return back()->with('error', 'Cet amortissement ne peut pas encore être comptabilisé.');
        }

        $depreciation->post();

        return back()->with('success', 'Amortissement comptabilisé.');
    }

    public function postAllDueDepreciations()
    {
        $dueDepreciations = AssetDepreciation::whereHas('asset', function ($q) {
            $q->where('company_id', auth()->user()->current_company_id);
        })
            ->dueForPosting()
            ->get();

        $count = 0;
        foreach ($dueDepreciations as $depreciation) {
            $depreciation->post();
            $count++;
        }

        return back()->with('success', "{$count} amortissement(s) comptabilisé(s).");
    }

    protected function generateDepreciationSchedule(Asset $asset): void
    {
        $schedule = $asset->generateDepreciationSchedule();

        foreach ($schedule as $line) {
            AssetDepreciation::create([
                'asset_id' => $asset->id,
                'period_start' => $line['period_start'],
                'period_end' => $line['period_end'],
                'year_number' => $line['year_number'],
                'depreciation_amount' => $line['depreciation_amount'],
                'accumulated_depreciation' => $line['accumulated_depreciation'],
                'book_value' => $line['book_value'],
                'status' => 'planned',
            ]);
        }
    }

    // Categories
    public function categories()
    {
        $categories = AssetCategory::where('company_id', auth()->user()->current_company_id)
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return view('assets.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:linear,degressive,units_of_production',
            'default_useful_life' => 'required|numeric|min:0.5|max:50',
            'degressive_rate' => 'nullable|numeric|min:0|max:100',
            'accounting_asset_account' => 'nullable|string|max:10',
            'accounting_depreciation_account' => 'nullable|string|max:10',
            'accounting_expense_account' => 'nullable|string|max:10',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;

        AssetCategory::create($validated);

        return back()->with('success', 'Catégorie créée.');
    }

    public function updateCategory(Request $request, AssetCategory $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:linear,degressive,units_of_production',
            'default_useful_life' => 'required|numeric|min:0.5|max:50',
            'degressive_rate' => 'nullable|numeric|min:0|max:100',
            'accounting_asset_account' => 'nullable|string|max:10',
            'accounting_depreciation_account' => 'nullable|string|max:10',
            'accounting_expense_account' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(AssetCategory $category)
    {
        $this->authorize('delete', $category);

        if ($category->assets()->exists()) {
            return back()->with('error', 'Impossible de supprimer une catégorie avec des immobilisations.');
        }

        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
