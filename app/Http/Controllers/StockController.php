<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\InventorySession;
use App\Models\InventoryLine;
use App\Models\StockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    protected function getCompanyId()
    {
        return session('current_tenant_id');
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $companyId = $this->getCompanyId();

        // Get warehouses with stats
        $warehouses = Warehouse::where('company_id', $companyId)
            ->active()
            ->withCount(['productStocks as products_count' => function ($q) {
                $q->where('quantity', '>', 0);
            }])
            ->get();

        // Stock stats
        $totalProducts = Product::where('company_id', $companyId)
            ->where('track_inventory', true)
            ->count();

        $totalStockValue = ProductStock::where('product_stocks.company_id', $companyId)
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(product_stocks.quantity * products.cost_price) as total')
            ->value('total') ?? 0;

        // Low stock & out of stock
        $lowStockProducts = ProductStock::where('company_id', $companyId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->where('quantity', '>', 0)
            ->with('product', 'warehouse')
            ->limit(10)
            ->get();

        $outOfStockProducts = ProductStock::where('company_id', $companyId)
            ->where('quantity', '<=', 0)
            ->with('product', 'warehouse')
            ->limit(10)
            ->get();

        // Recent movements
        $recentMovements = StockMovement::where('company_id', $companyId)
            ->with('product', 'warehouse', 'createdBy')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Alerts
        $alerts = StockAlert::where('company_id', $companyId)
            ->unresolved()
            ->with('product', 'warehouse')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Movement stats (last 30 days)
        $movementStats = StockMovement::getStats($companyId, null, [
            now()->subDays(30)->startOfDay(),
            now()->endOfDay()
        ]);

        return view('stock.dashboard', compact(
            'warehouses',
            'totalProducts',
            'totalStockValue',
            'lowStockProducts',
            'outOfStockProducts',
            'recentMovements',
            'alerts',
            'movementStats'
        ));
    }

    // ==================== WAREHOUSES ====================

    public function warehouses()
    {
        $companyId = $this->getCompanyId();

        $warehouses = Warehouse::where('company_id', $companyId)
            ->withCount(['productStocks as products_count' => function ($q) {
                $q->where('quantity', '>', 0);
            }])
            ->orderBy('sort_order')
            ->paginate(20);

        return view('stock.warehouses.index', compact('warehouses'));
    }

    public function createWarehouse()
    {
        $users = \App\Models\User::whereHas('companies', function ($q) {
            $q->where('companies.id', $this->getCompanyId());
        })->get();

        return view('stock.warehouses.create', compact('users'));
    }

    public function storeWarehouse(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|uuid|exists:users,id',
            'is_default' => 'boolean',
            'allow_negative_stock' => 'boolean',
        ]);

        $validated['company_id'] = $this->getCompanyId();
        $validated['is_active'] = true;

        $warehouse = Warehouse::create($validated);

        if ($validated['is_default'] ?? false) {
            $warehouse->setAsDefault();
        }

        return redirect()->route('stock.warehouses.index')
            ->with('success', "Entrepôt {$warehouse->name} créé avec succès.");
    }

    public function showWarehouse(Warehouse $warehouse)
    {
        $warehouse->load('manager');

        $productStocks = ProductStock::where('warehouse_id', $warehouse->id)
            ->with('product')
            ->orderByDesc('quantity')
            ->paginate(20);

        $recentMovements = StockMovement::where('warehouse_id', $warehouse->id)
            ->with('product', 'createdBy')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('stock.warehouses.show', compact('warehouse', 'productStocks', 'recentMovements'));
    }

    public function editWarehouse(Warehouse $warehouse)
    {
        $users = \App\Models\User::whereHas('companies', function ($q) {
            $q->where('companies.id', $this->getCompanyId());
        })->get();

        return view('stock.warehouses.edit', compact('warehouse', 'users'));
    }

    public function updateWarehouse(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|uuid|exists:users,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'allow_negative_stock' => 'boolean',
        ]);

        $warehouse->update($validated);

        if ($validated['is_default'] ?? false) {
            $warehouse->setAsDefault();
        }

        return redirect()->route('stock.warehouses.show', $warehouse)
            ->with('success', 'Entrepôt mis à jour avec succès.');
    }

    public function destroyWarehouse(Warehouse $warehouse)
    {
        // Check if warehouse has stock
        $hasStock = ProductStock::where('warehouse_id', $warehouse->id)
            ->where('quantity', '!=', 0)
            ->exists();

        if ($hasStock) {
            return back()->with('error', 'Impossible de supprimer un entrepôt contenant du stock.');
        }

        $warehouse->delete();

        return redirect()->route('stock.warehouses.index')
            ->with('success', 'Entrepôt supprimé avec succès.');
    }

    // ==================== STOCK LEVELS ====================

    public function stockLevels(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = ProductStock::where('company_id', $companyId)
            ->with('product', 'warehouse');

        // Filters
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->status) {
            switch ($request->status) {
                case 'low_stock':
                    $query->whereColumn('quantity', '<=', 'min_quantity')->where('quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
            }
        }

        if ($request->search) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')->paginate(30);

        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();

        return view('stock.levels.index', compact('stocks', 'warehouses'));
    }

    public function adjustStock(Request $request, ProductStock $productStock)
    {
        $validated = $request->validate([
            'adjustment' => 'required|numeric',
            'reason' => 'required|string|in:' . implode(',', array_keys(StockMovement::REASONS)),
            'notes' => 'nullable|string',
        ]);

        $productStock->adjustQuantity(
            $validated['adjustment'],
            $validated['reason']
        );

        // Check for alerts
        StockAlert::checkAndCreate(
            $productStock->company_id,
            $productStock->product_id,
            $productStock->warehouse_id
        );

        return back()->with('success', 'Stock ajusté avec succès.');
    }

    // ==================== MOVEMENTS ====================

    public function movements(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = StockMovement::where('company_id', $companyId)
            ->with('product', 'warehouse', 'destinationWarehouse', 'createdBy');

        // Filters
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->orderByDesc('created_at')->paginate(30);

        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();
        $products = Product::where('company_id', $companyId)->where('track_inventory', true)->get();

        return view('stock.movements.index', compact('movements', 'warehouses', 'products'));
    }

    public function createMovement()
    {
        $companyId = $this->getCompanyId();

        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();
        $products = Product::where('company_id', $companyId)
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->get();

        return view('stock.movements.create', compact('warehouses', 'products'));
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|uuid|exists:warehouses,id|different:warehouse_id',
            'type' => 'required|in:in,out,transfer,adjustment',
            'reason' => 'required|string|in:' . implode(',', array_keys(StockMovement::REASONS)),
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $companyId = $this->getCompanyId();

        // Get current stock
        $stock = ProductStock::getOrCreate(
            $companyId,
            $validated['product_id'],
            $validated['warehouse_id']
        );

        // Check stock availability for out/transfer
        if (in_array($validated['type'], ['out', 'transfer'])) {
            $warehouse = Warehouse::find($validated['warehouse_id']);
            if (!$warehouse->allow_negative_stock && $stock->available_quantity < $validated['quantity']) {
                return back()->withInput()->with('error', 'Stock insuffisant pour cette opération.');
            }
        }

        $product = Product::find($validated['product_id']);
        $unitCost = $validated['unit_cost'] ?? $product->cost_price ?? 0;

        $movement = StockMovement::create([
            'company_id' => $companyId,
            'reference' => StockMovement::generateReference($companyId),
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'destination_warehouse_id' => $validated['destination_warehouse_id'] ?? null,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'quantity' => $validated['quantity'],
            'quantity_before' => $stock->quantity,
            'quantity_after' => 0, // Will be set after validation
            'unit_cost' => $unitCost,
            'total_cost' => $validated['quantity'] * $unitCost,
            'batch_number' => $validated['batch_number'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        // Auto-validate
        $movement->validate(auth()->user());

        // Check for alerts
        StockAlert::checkAndCreate($companyId, $validated['product_id'], $validated['warehouse_id']);

        return redirect()->route('stock.movements.index')
            ->with('success', "Mouvement {$movement->reference} créé et validé.");
    }

    public function showMovement(StockMovement $movement)
    {
        $movement->load('product', 'warehouse', 'destinationWarehouse', 'createdBy', 'validatedBy');
        return view('stock.movements.show', compact('movement'));
    }

    // ==================== INVENTORY ====================

    public function inventories(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = InventorySession::where('company_id', $companyId)
            ->with('warehouse', 'createdBy');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $inventories = $query->orderByDesc('created_at')->paginate(20);

        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();

        return view('stock.inventories.index', compact('inventories', 'warehouses'));
    }

    public function createInventory()
    {
        $companyId = $this->getCompanyId();
        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();

        return view('stock.inventories.create', compact('warehouses'));
    }

    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'type' => 'required|in:full,partial,cycle,spot',
            'scheduled_date' => 'nullable|date',
        ]);

        $companyId = $this->getCompanyId();

        $inventory = InventorySession::create([
            'company_id' => $companyId,
            'reference' => InventorySession::generateReference($companyId),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'warehouse_id' => $validated['warehouse_id'],
            'type' => $validated['type'],
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        // Generate inventory lines
        $inventory->generateLines();

        return redirect()->route('stock.inventories.show', $inventory)
            ->with('success', "Inventaire {$inventory->reference} créé avec {$inventory->total_products} produits.");
    }

    public function showInventory(InventorySession $inventory)
    {
        $inventory->load('warehouse', 'createdBy', 'validatedBy');

        $lines = $inventory->lines()
            ->with('product', 'countedBy')
            ->orderBy('status')
            ->paginate(50);

        return view('stock.inventories.show', compact('inventory', 'lines'));
    }

    public function startInventory(InventorySession $inventory)
    {
        $inventory->start();

        return redirect()->route('stock.inventories.show', $inventory)
            ->with('success', 'Inventaire démarré.');
    }

    public function countInventoryLine(Request $request, InventoryLine $line)
    {
        $validated = $request->validate([
            'counted_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $line->recordCount(
            $validated['counted_quantity'],
            auth()->user(),
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Comptage enregistré.');
    }

    public function validateInventory(InventorySession $inventory)
    {
        if (!$inventory->canBeValidated()) {
            return back()->with('error', 'Cet inventaire ne peut pas être validé dans son état actuel.');
        }

        $inventory->validate(auth()->user());

        return redirect()->route('stock.inventories.show', $inventory)
            ->with('success', "Inventaire validé. {$inventory->discrepancies} ajustement(s) créé(s).");
    }

    public function cancelInventory(InventorySession $inventory)
    {
        $inventory->cancel();

        return redirect()->route('stock.inventories.index')
            ->with('success', 'Inventaire annulé.');
    }

    // ==================== ALERTS ====================

    public function alerts(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = StockAlert::where('company_id', $companyId)
            ->with('product', 'warehouse');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if (!$request->has('show_resolved') || !$request->show_resolved) {
            $query->unresolved();
        }

        $alerts = $query->orderByDesc('created_at')->paginate(30);

        return view('stock.alerts.index', compact('alerts'));
    }

    public function resolveAlert(StockAlert $alert)
    {
        $alert->resolve(auth()->user());

        return back()->with('success', 'Alerte résolue.');
    }

    public function markAlertRead(StockAlert $alert)
    {
        $alert->markAsRead();

        return back()->with('success', 'Alerte marquée comme lue.');
    }

    // ==================== API ====================

    public function getProductStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
        ]);

        $companyId = $this->getCompanyId();

        $query = ProductStock::where('company_id', $companyId)
            ->where('product_id', $validated['product_id'])
            ->with('warehouse');

        if ($validated['warehouse_id'] ?? null) {
            $query->where('warehouse_id', $validated['warehouse_id']);
        }

        $stocks = $query->get();

        return response()->json([
            'stocks' => $stocks,
            'total_quantity' => $stocks->sum('quantity'),
            'total_available' => $stocks->sum('available_quantity'),
        ]);
    }

    // ==================== MISSING METHODS ====================

    public function setDefaultWarehouse(Warehouse $warehouse)
    {
        $warehouse->setAsDefault();

        return back()->with('success', "Entrepôt {$warehouse->name} défini comme entrepôt par défaut.");
    }

    public function editStockLevel(ProductStock $stock)
    {
        $stock->load('product', 'warehouse');

        return view('stock.levels.edit', compact('stock'));
    }

    public function updateStockLevel(Request $request, ProductStock $stock)
    {
        $validated = $request->validate([
            'min_quantity' => 'nullable|numeric|min:0',
            'max_quantity' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
        ]);

        $stock->update($validated);

        return redirect()->route('stock.levels')
            ->with('success', 'Paramètres de stock mis à jour.');
    }

    public function editMovement(StockMovement $movement)
    {
        if ($movement->status !== 'draft') {
            return back()->with('error', 'Seuls les mouvements en brouillon peuvent être modifiés.');
        }

        $companyId = $this->getCompanyId();
        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();
        $products = Product::where('company_id', $companyId)
            ->where('track_inventory', true)
            ->where('is_active', true)
            ->get();

        return view('stock.movements.edit', compact('movement', 'warehouses', 'products'));
    }

    public function updateMovement(Request $request, StockMovement $movement)
    {
        if ($movement->status !== 'draft') {
            return back()->with('error', 'Seuls les mouvements en brouillon peuvent être modifiés.');
        }

        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'destination_warehouse_id' => 'nullable|uuid|exists:warehouses,id|different:warehouse_id',
            'type' => 'required|in:in,out,transfer,adjustment',
            'reason' => 'required|string|in:' . implode(',', array_keys(StockMovement::REASONS)),
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $movement->update($validated);

        return redirect()->route('stock.movements.show', $movement)
            ->with('success', 'Mouvement mis à jour.');
    }

    public function destroyMovement(StockMovement $movement)
    {
        if ($movement->status !== 'draft') {
            return back()->with('error', 'Seuls les mouvements en brouillon peuvent être supprimés.');
        }

        $movement->delete();

        return redirect()->route('stock.movements.index')
            ->with('success', 'Mouvement supprimé.');
    }

    public function validateMovement(StockMovement $movement)
    {
        if ($movement->status !== 'draft') {
            return back()->with('error', 'Ce mouvement a déjà été traité.');
        }

        $movement->validate(auth()->user());

        // Check for alerts
        StockAlert::checkAndCreate(
            $movement->company_id,
            $movement->product_id,
            $movement->warehouse_id
        );

        return back()->with('success', "Mouvement {$movement->reference} validé.");
    }

    public function cancelMovement(StockMovement $movement)
    {
        if (!in_array($movement->status, ['draft', 'validated'])) {
            return back()->with('error', 'Ce mouvement ne peut pas être annulé.');
        }

        $movement->cancel(auth()->user());

        return back()->with('success', "Mouvement {$movement->reference} annulé.");
    }

    public function editInventory(InventorySession $inventory)
    {
        if (!in_array($inventory->status, ['draft', 'in_progress'])) {
            return back()->with('error', 'Cet inventaire ne peut plus être modifié.');
        }

        $companyId = $this->getCompanyId();
        $warehouses = Warehouse::where('company_id', $companyId)->active()->get();

        return view('stock.inventories.edit', compact('inventory', 'warehouses'));
    }

    public function updateInventory(Request $request, InventorySession $inventory)
    {
        if (!in_array($inventory->status, ['draft', 'in_progress'])) {
            return back()->with('error', 'Cet inventaire ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
        ]);

        $inventory->update($validated);

        return redirect()->route('stock.inventories.show', $inventory)
            ->with('success', 'Inventaire mis à jour.');
    }

    public function destroyInventory(InventorySession $inventory)
    {
        if ($inventory->status === 'validated') {
            return back()->with('error', 'Un inventaire validé ne peut pas être supprimé.');
        }

        $inventory->lines()->delete();
        $inventory->delete();

        return redirect()->route('stock.inventories.index')
            ->with('success', 'Inventaire supprimé.');
    }

    public function countInventory(InventorySession $inventory)
    {
        if ($inventory->status !== 'in_progress') {
            return back()->with('error', 'Cet inventaire n\'est pas en cours de comptage.');
        }

        $inventory->load('warehouse');

        $lines = $inventory->lines()
            ->with('product')
            ->where('status', '!=', 'counted')
            ->orderBy('product_id')
            ->paginate(50);

        return view('stock.inventories.count', compact('inventory', 'lines'));
    }

    public function saveCount(Request $request, InventorySession $inventory)
    {
        if ($inventory->status !== 'in_progress') {
            return back()->with('error', 'Cet inventaire n\'est pas en cours de comptage.');
        }

        $validated = $request->validate([
            'counts' => 'required|array',
            'counts.*.line_id' => 'required|uuid|exists:inventory_lines,id',
            'counts.*.quantity' => 'required|numeric|min:0',
            'counts.*.notes' => 'nullable|string',
        ]);

        foreach ($validated['counts'] as $countData) {
            $line = InventoryLine::find($countData['line_id']);
            if ($line && $line->inventory_session_id === $inventory->id) {
                $line->recordCount(
                    $countData['quantity'],
                    auth()->user(),
                    $countData['notes'] ?? null
                );
            }
        }

        return back()->with('success', 'Comptages enregistrés.');
    }

    public function markAllAlertsRead()
    {
        $companyId = $this->getCompanyId();

        StockAlert::where('company_id', $companyId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les alertes ont été marquées comme lues.');
    }

    public function apiGetStockLevel(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|uuid|exists:products,id',
            'warehouse_id' => 'nullable|uuid|exists:warehouses,id',
        ]);

        $companyId = $this->getCompanyId();

        $query = ProductStock::where('company_id', $companyId)
            ->where('product_id', $validated['product_id']);

        if ($validated['warehouse_id'] ?? null) {
            $query->where('warehouse_id', $validated['warehouse_id']);
        }

        $stocks = $query->with('warehouse')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stocks' => $stocks,
                'total_quantity' => $stocks->sum('quantity'),
                'total_available' => $stocks->sum('available_quantity'),
                'total_reserved' => $stocks->sum('reserved_quantity'),
            ]
        ]);
    }
}
