<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\ProductCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductTypeController extends Controller
{
    public function index()
    {
        $productTypes = ProductType::withCount('products')
            ->with('customFields')
            ->ordered()
            ->get();

        return view('settings.product-types.index', compact('productTypes'));
    }

    public function create()
    {
        return view('settings.product-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_types')->where('company_id', session('current_tenant_id')),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_service' => 'boolean',
            'track_inventory' => 'boolean',
            'has_variants' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = session('current_tenant_id');
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_service'] = $request->boolean('is_service');
        $validated['track_inventory'] = $request->boolean('track_inventory');
        $validated['has_variants'] = $request->boolean('has_variants');
        $validated['is_active'] = $request->boolean('is_active', true);

        $productType = ProductType::create($validated);

        return redirect()
            ->route('settings.product-types.edit', $productType)
            ->with('success', "Type de produit \"{$productType->name}\" créé. Vous pouvez maintenant ajouter des champs personnalisés.");
    }

    public function edit(ProductType $productType)
    {
        $productType->load(['customFields' => fn($q) => $q->orderBy('sort_order')]);

        $fieldTypes = ProductCustomField::FIELD_TYPES;

        return view('settings.product-types.edit', compact('productType', 'fieldTypes'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_types')
                    ->where('company_id', session('current_tenant_id'))
                    ->ignore($productType->id),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_service' => 'boolean',
            'track_inventory' => 'boolean',
            'has_variants' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_service'] = $request->boolean('is_service');
        $validated['track_inventory'] = $request->boolean('track_inventory');
        $validated['has_variants'] = $request->boolean('has_variants');
        $validated['is_active'] = $request->boolean('is_active', true);

        $productType->update($validated);

        return back()->with('success', "Type de produit \"{$productType->name}\" mis à jour.");
    }

    public function destroy(ProductType $productType)
    {
        // Check if any products use this type
        if ($productType->products()->exists()) {
            return back()->with('error', 'Ce type ne peut pas être supprimé car il est utilisé par des produits.');
        }

        $name = $productType->name;
        $productType->delete();

        return redirect()
            ->route('settings.product-types.index')
            ->with('success', "Type de produit \"{$name}\" supprimé.");
    }

    /**
     * Add a custom field to the product type.
     */
    public function addField(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'label' => 'nullable|string|max:100',
            'type' => 'required|string|in:' . implode(',', array_keys(ProductCustomField::FIELD_TYPES)),
            'description' => 'nullable|string|max:255',
            'is_required' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_invoice' => 'boolean',
            'is_searchable' => 'boolean',
            'is_filterable' => 'boolean',
            'default_value' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'options.choices' => 'nullable|array',
            'options.min' => 'nullable|numeric',
            'options.max' => 'nullable|numeric',
            'options.step' => 'nullable|numeric',
            'options.min_length' => 'nullable|integer|min:0',
            'options.max_length' => 'nullable|integer|min:1',
            'group' => 'nullable|string|max:50',
        ]);

        $validated['company_id'] = session('current_tenant_id');
        $validated['product_type_id'] = $productType->id;
        $validated['slug'] = Str::slug($validated['name'], '_');
        $validated['label'] = $validated['label'] ?? $validated['name'];
        $validated['is_required'] = $request->boolean('is_required');
        $validated['show_in_list'] = $request->boolean('show_in_list');
        $validated['show_in_invoice'] = $request->boolean('show_in_invoice');
        $validated['is_searchable'] = $request->boolean('is_searchable');
        $validated['is_filterable'] = $request->boolean('is_filterable');
        $validated['sort_order'] = $productType->customFields()->max('sort_order') + 1;

        // Clean up options
        if (isset($validated['options'])) {
            $validated['options'] = array_filter($validated['options'], fn($v) => $v !== null && $v !== '');
        }

        $field = ProductCustomField::create($validated);

        return back()->with('success', "Champ \"{$field->label}\" ajouté.");
    }

    /**
     * Update a custom field.
     */
    public function updateField(Request $request, ProductType $productType, ProductCustomField $field)
    {
        if ($field->product_type_id !== $productType->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'label' => 'nullable|string|max:100',
            'type' => 'required|string|in:' . implode(',', array_keys(ProductCustomField::FIELD_TYPES)),
            'description' => 'nullable|string|max:255',
            'is_required' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_invoice' => 'boolean',
            'is_searchable' => 'boolean',
            'is_filterable' => 'boolean',
            'default_value' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'group' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['label'] = $validated['label'] ?? $validated['name'];
        $validated['is_required'] = $request->boolean('is_required');
        $validated['show_in_list'] = $request->boolean('show_in_list');
        $validated['show_in_invoice'] = $request->boolean('show_in_invoice');
        $validated['is_searchable'] = $request->boolean('is_searchable');
        $validated['is_filterable'] = $request->boolean('is_filterable');
        $validated['is_active'] = $request->boolean('is_active', true);

        $field->update($validated);

        return back()->with('success', "Champ \"{$field->label}\" mis à jour.");
    }

    /**
     * Delete a custom field.
     */
    public function deleteField(ProductType $productType, ProductCustomField $field)
    {
        if ($field->product_type_id !== $productType->id) {
            abort(404);
        }

        $label = $field->label;
        $field->delete();

        return back()->with('success', "Champ \"{$label}\" supprimé.");
    }

    /**
     * Reorder custom fields.
     */
    public function reorderFields(Request $request, ProductType $productType)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'exists:product_custom_fields,id',
        ]);

        foreach ($request->fields as $index => $fieldId) {
            ProductCustomField::where('id', $fieldId)
                ->where('product_type_id', $productType->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Seed default product types.
     */
    public function seedDefaults()
    {
        ProductType::seedDefaultsForCompany(session('current_tenant_id'));

        return back()->with('success', 'Types de produits par défaut créés.');
    }
}
