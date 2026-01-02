<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::with(['children' => fn($q) => $q->withCount('products')->ordered()])
            ->withCount('products')
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        return view('settings.product-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ProductCategory::getNestedOptions(session('current_tenant_id'));

        return view('settings.product-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_categories')->where('company_id', session('current_tenant_id')),
            ],
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = session('current_tenant_id');
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = ProductCategory::create($validated);

        return redirect()
            ->route('settings.product-categories.index')
            ->with('success', "Catégorie \"{$category->name}\" créée.");
    }

    public function edit(ProductCategory $productCategory)
    {
        $parentCategories = ProductCategory::getNestedOptions(session('current_tenant_id'));

        // Remove current category and its descendants from options
        unset($parentCategories[$productCategory->id]);
        foreach ($productCategory->descendants as $descendant) {
            unset($parentCategories[$descendant->id]);
        }

        return view('settings.product-categories.edit', compact('productCategory', 'parentCategories'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_categories')
                    ->where('company_id', session('current_tenant_id'))
                    ->ignore($productCategory->id),
            ],
            'parent_id' => [
                'nullable',
                'exists:product_categories,id',
                function ($attribute, $value, $fail) use ($productCategory) {
                    // Prevent setting parent to self or descendants
                    if ($value == $productCategory->id) {
                        $fail('Une catégorie ne peut pas être son propre parent.');
                    }
                    $descendantIds = $productCategory->descendants->pluck('id')->toArray();
                    if (in_array($value, $descendantIds)) {
                        $fail('Une catégorie ne peut pas avoir un de ses descendants comme parent.');
                    }
                },
            ],
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $productCategory->update($validated);

        return redirect()
            ->route('settings.product-categories.index')
            ->with('success', "Catégorie \"{$productCategory->name}\" mise à jour.");
    }

    public function destroy(ProductCategory $productCategory)
    {
        // Check if category has products
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Cette catégorie contient des produits et ne peut pas être supprimée.');
        }

        // Check if category has children
        if ($productCategory->children()->exists()) {
            return back()->with('error', 'Cette catégorie a des sous-catégories et ne peut pas être supprimée.');
        }

        $name = $productCategory->name;
        $productCategory->delete();

        return redirect()
            ->route('settings.product-categories.index')
            ->with('success', "Catégorie \"{$name}\" supprimée.");
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:product_categories,id',
            'categories.*.parent_id' => 'nullable|exists:product_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $item) {
            ProductCategory::where('id', $item['id'])
                ->where('company_id', session('current_tenant_id'))
                ->update([
                    'parent_id' => $item['parent_id'],
                    'sort_order' => $item['sort_order'],
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get categories as JSON for AJAX.
     */
    public function search(Request $request)
    {
        $query = ProductCategory::active()->ordered();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $categories = $query->limit(20)->get();

        return response()->json($categories->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'path' => $cat->path,
            'color' => $cat->color,
        ]));
    }
}
