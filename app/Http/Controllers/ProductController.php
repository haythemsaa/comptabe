<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductCategory;
use App\Models\ProductCustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['productType', 'productCategory']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Type filter (legacy)
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Product type filter (advanced)
        if ($request->filled('product_type_id')) {
            $query->ofProductType($request->product_type_id);
        }

        // Category filter (legacy text)
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        // Category filter (advanced)
        if ($request->filled('category_id')) {
            $query->inCategoryId($request->category_id);
        }

        // Stock filter
        if ($request->filled('stock')) {
            if ($request->stock === 'in_stock') {
                $query->inStock();
            } elseif ($request->stock === 'low_stock') {
                $query->lowStock();
            } elseif ($request->stock === 'out_of_stock') {
                $query->outOfStock();
            }
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        } else {
            $query->active(); // By default show only active
        }

        $products = $query->ordered()
            ->paginate(20)
            ->withQueryString();

        // Get categories for filter
        $categories = Product::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort();

        // Get product types and categories for advanced filters
        $productTypes = ProductType::active()->ordered()->get();
        $productCategories = ProductCategory::active()->ordered()->get();

        return view('products.index', compact('products', 'categories', 'productTypes', 'productCategories'));
    }

    public function create(Request $request)
    {
        $productTypes = ProductType::active()->ordered()->get();
        $productCategories = collect(ProductCategory::getNestedOptions(session('current_tenant_id')));

        // Get custom fields based on selected product type
        $selectedTypeId = $request->query('product_type_id');
        $customFields = collect();

        if ($selectedTypeId) {
            $customFields = ProductCustomField::where('company_id', session('current_tenant_id'))
                ->forType($selectedTypeId)
                ->active()
                ->ordered()
                ->get();
        }

        return view('products.create', compact('productTypes', 'productCategories', 'customFields', 'selectedTypeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')->where('company_id', session('current_tenant_id')),
            ],
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:product,service',
            'product_type_id' => 'nullable|exists:product_types,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_price' => 'required|numeric|min:0|max:9999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:9999999.99',
            'unit' => 'required|string|max:50',
            'vat_rate' => 'required|numeric|in:0,6,12,21',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'track_inventory' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'accounting_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'custom_fields' => 'nullable|array',
        ]);

        $validated['company_id'] = session('current_tenant_id');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['track_inventory'] = $request->boolean('track_inventory');

        // Handle custom fields
        if ($request->has('custom_fields')) {
            $validated['custom_fields'] = $this->processCustomFields($request->custom_fields, $request->product_type_id);
        }

        $product = Product::create($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "{$product->type_label} \"{$product->name}\" créé avec succès.");
    }

    public function show(Product $product)
    {
        $product->load(['productType', 'productCategory', 'variants']);
        $customFields = $product->getCustomFieldDefinitions();

        return view('products.show', compact('product', 'customFields'));
    }

    public function edit(Product $product)
    {
        $productTypes = ProductType::active()->ordered()->get();
        $productCategories = collect(ProductCategory::getNestedOptions(session('current_tenant_id')));

        // Get custom fields for this product's type
        $customFields = $product->getCustomFieldDefinitions();

        return view('products.edit', compact('product', 'productTypes', 'productCategories', 'customFields'));
    }

    /**
     * Process custom fields and cast values appropriately.
     */
    protected function processCustomFields(array $fields, ?string $productTypeId): array
    {
        $processed = [];

        $fieldDefinitions = ProductCustomField::where('company_id', session('current_tenant_id'))
            ->forType($productTypeId)
            ->active()
            ->get()
            ->keyBy('slug');

        foreach ($fields as $slug => $value) {
            if ($definition = $fieldDefinitions->get($slug)) {
                $processed[$slug] = $definition->castValue($value);
            } else {
                $processed[$slug] = $value;
            }
        }

        return $processed;
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')
                    ->where('company_id', session('current_tenant_id'))
                    ->ignore($product->id),
            ],
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:product,service',
            'product_type_id' => 'nullable|exists:product_types,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'unit_price' => 'required|numeric|min:0|max:9999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:9999999.99',
            'unit' => 'required|string|max:50',
            'vat_rate' => 'required|numeric|in:0,6,12,21',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'track_inventory' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'accounting_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'custom_fields' => 'nullable|array',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['track_inventory'] = $request->boolean('track_inventory');

        // Handle custom fields
        if ($request->has('custom_fields')) {
            $validated['custom_fields'] = $this->processCustomFields($request->custom_fields, $request->product_type_id ?? $product->product_type_id);
        }

        $product->update($validated);

        return redirect()
            ->route('products.index')
            ->with('success', "{$product->type_label} \"{$product->name}\" mis à jour.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $type = $product->type_label;
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', "{$type} \"{$name}\" supprimé.");
    }

    /**
     * Toggle product active status.
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "{$product->type_label} \"{$product->name}\" {$status}.");
    }

    /**
     * Duplicate a product.
     */
    public function duplicate(Product $product)
    {
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (copie)';
        $newProduct->code = null; // Reset code to avoid unique constraint
        $newProduct->save();

        return redirect()
            ->route('products.edit', $newProduct)
            ->with('success', "{$product->type_label} dupliqué. Modifiez les détails si nécessaire.");
    }

    /**
     * Get products as JSON for AJAX requests (e.g., invoice line autocomplete).
     */
    public function search(Request $request)
    {
        $query = Product::active()->ordered();

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        $products = $query->limit(20)->get();

        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'description' => $product->description,
                'type' => $product->type,
                'unit_price' => $product->unit_price,
                'unit' => $product->unit,
                'vat_rate' => $product->vat_rate,
                'formatted_price' => $product->formatted_price,
                'label' => $product->code ? "[{$product->code}] {$product->name}" : $product->name,
            ];
        }));
    }

    /**
     * Get custom fields for a product type (AJAX).
     */
    public function getCustomFields(Request $request)
    {
        $productTypeId = $request->query('product_type_id');

        if (!$productTypeId) {
            return response()->json(['fields' => [], 'html' => '']);
        }

        $customFields = ProductCustomField::where('company_id', session('current_tenant_id'))
            ->forType($productTypeId)
            ->active()
            ->ordered()
            ->get();

        // Also get the product type to access default values
        $productType = ProductType::find($productTypeId);

        return response()->json([
            'fields' => $customFields,
            'productType' => $productType,
            'html' => view('products._custom-fields', [
                'customFields' => $customFields,
                'values' => [],
            ])->render(),
        ]);
    }

    /**
     * Import products from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        $header = fgetcsv($handle, 0, ';');

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 5) continue;

            try {
                Product::create([
                    'company_id' => session('current_tenant_id'),
                    'code' => $row[0] ?: null,
                    'name' => $row[1],
                    'type' => strtolower($row[2]) === 'produit' ? 'product' : 'service',
                    'unit_price' => floatval(str_replace(',', '.', $row[3])),
                    'vat_rate' => floatval(str_replace(',', '.', $row[4])),
                    'unit' => $row[5] ?? 'unité',
                    'category' => $row[6] ?? null,
                    'description' => $row[7] ?? null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($imported + count($errors) + 2) . ": " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "{$imported} produits/services importés.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " erreurs.";
        }

        return back()->with($errors ? 'warning' : 'success', $message);
    }

    /**
     * Export products to CSV.
     */
    public function export()
    {
        $products = Product::ordered()->get();

        $csv = "Code;Nom;Type;Prix HT;TVA %;Unité;Catégorie;Description\n";

        foreach ($products as $product) {
            $csv .= sprintf(
                "%s;%s;%s;%s;%s;%s;%s;%s\n",
                $product->code ?? '',
                str_replace(';', ',', $product->name),
                $product->type_label,
                number_format($product->unit_price, 2, ',', ''),
                number_format($product->vat_rate, 2, ',', ''),
                $product->unit,
                $product->category ?? '',
                str_replace([';', "\n", "\r"], [',', ' ', ''], $product->description ?? '')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="produits_' . date('Y-m-d') . '.csv"');
    }
}
