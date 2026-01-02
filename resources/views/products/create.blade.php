<x-app-layout>
    <x-slot name="title">Nouveau Produit/Service</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('products.index') }}" class="text-secondary-500 hover:text-secondary-700">Produits & Services</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouveau</span>
    @endsection

    <div class="max-w-3xl mx-auto"
         x-data="productForm()"
         x-init="init()">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouveau Produit/Service</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Ajoutez un article à votre catalogue</p>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Type Selection -->
            <div class="card p-6">
                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-3">Type d'article</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="relative flex cursor-pointer rounded-xl border p-4 focus:outline-none transition-colors"
                           :class="type === 'service' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-secondary-200 dark:border-secondary-700 hover:bg-secondary-50 dark:hover:bg-secondary-800'">
                        <input type="radio" name="type" value="service" class="sr-only" x-model="type">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-secondary-900 dark:text-white">Service</div>
                                <div class="text-sm text-secondary-500">Prestation, consultation, etc.</div>
                            </div>
                        </div>
                    </label>
                    <label class="relative flex cursor-pointer rounded-xl border p-4 focus:outline-none transition-colors"
                           :class="type === 'product' ? 'border-warning-500 bg-warning-50 dark:bg-warning-900/20' : 'border-secondary-200 dark:border-secondary-700 hover:bg-secondary-50 dark:hover:bg-secondary-800'">
                        <input type="radio" name="type" value="product" class="sr-only" x-model="type">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-secondary-900 dark:text-white">Produit</div>
                                <div class="text-sm text-secondary-500">Bien physique, marchandise</div>
                            </div>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Product Type & Category (Advanced) -->
            @if($productTypes->isNotEmpty() || $productCategories->isNotEmpty())
            <div class="card p-6 space-y-4">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Classification</h2>

                <div class="grid grid-cols-2 gap-4">
                    @if($productTypes->isNotEmpty())
                    <div>
                        <label for="product_type_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Type de produit
                        </label>
                        <select name="product_type_id" id="product_type_id" class="form-select w-full" x-model="productTypeId" @change="loadCustomFields()">
                            <option value="">-- Sélectionner --</option>
                            @foreach($productTypes as $productType)
                                <option value="{{ $productType->id }}" {{ old('product_type_id', $selectedTypeId ?? '') == $productType->id ? 'selected' : '' }}>
                                    {{ $productType->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-secondary-500">Détermine les champs personnalisés disponibles</p>
                        @error('product_type_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    @if($productCategories->isNotEmpty())
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">
                            Catégorie
                        </label>
                        <select name="category_id" id="category_id" class="form-select w-full">
                            <option value="">-- Sélectionner --</option>
                            @foreach($productCategories as $id => $name)
                                <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Basic Info -->
            <div class="card p-6 space-y-4">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Informations</h2>

                <div class="grid grid-cols-6 gap-4">
                    <div class="col-span-2">
                        <label for="code" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Code / Réf.</label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="REF001" class="form-input w-full">
                        @error('code')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-span-2">
                        <label for="sku" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">SKU</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" placeholder="SKU001" class="form-input w-full">
                        @error('sku')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="col-span-2">
                        <label for="barcode" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Code-barres</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" placeholder="EAN13" class="form-input w-full">
                        @error('barcode')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Nom <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-input w-full" placeholder="Nom du produit ou service">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-input w-full" placeholder="Description détaillée (optionnel)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Catégorie (texte)</label>
                        <input type="text" name="category" id="category" value="{{ old('category') }}" class="form-input w-full" list="categories" placeholder="Ex: Consulting">
                        <datalist id="categories">
                            <option value="Consulting">
                            <option value="Développement">
                            <option value="Maintenance">
                            <option value="Formation">
                            <option value="Support">
                            <option value="Matériel">
                            <option value="Logiciel">
                        </datalist>
                        @error('category')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Marque</label>
                        <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="form-input w-full" placeholder="Ex: Apple">
                        @error('brand')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="manufacturer" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Fabricant</label>
                        <input type="text" name="manufacturer" id="manufacturer" value="{{ old('manufacturer') }}" class="form-input w-full" placeholder="Ex: Apple Inc.">
                        @error('manufacturer')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="card p-6 space-y-4">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Tarification</h2>

                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label for="unit_price" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Prix de vente HT <span class="text-danger-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', '0.00') }}" step="0.01" min="0" required class="form-input w-full pr-8" x-model="price">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">&euro;</span>
                        </div>
                        @error('unit_price')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cost_price" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Prix d'achat HT</label>
                        <div class="relative">
                            <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0" class="form-input w-full pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">&euro;</span>
                        </div>
                        @error('cost_price')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="unit" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Unité <span class="text-danger-500">*</span></label>
                        <select name="unit" id="unit" required class="form-select w-full">
                            @foreach(\App\Models\Product::UNITS as $value => $label)
                                <option value="{{ $value }}" {{ old('unit', 'unité') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('unit')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="vat_rate" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Taux TVA <span class="text-danger-500">*</span></label>
                        <select name="vat_rate" id="vat_rate" required class="form-select w-full" x-model="vat">
                            @foreach(\App\Models\Product::VAT_RATES as $value => $label)
                                <option value="{{ $value }}" {{ old('vat_rate', '21.00') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('vat_rate')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Price Preview -->
                <div class="bg-secondary-50 dark:bg-secondary-800 rounded-lg p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-secondary-600 dark:text-secondary-400">Prix HT</span>
                        <span class="font-medium" x-text="parseFloat(price || 0).toFixed(2) + ' &euro;'">0.00 &euro;</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-secondary-600 dark:text-secondary-400">TVA (<span x-text="parseFloat(vat || 0).toFixed(0)">21</span>%)</span>
                        <span class="font-medium" x-text="(parseFloat(price || 0) * parseFloat(vat || 0) / 100).toFixed(2) + ' &euro;'">0.00 &euro;</span>
                    </div>
                    <div class="flex justify-between text-base mt-2 pt-2 border-t border-secondary-200 dark:border-secondary-700">
                        <span class="font-medium text-secondary-900 dark:text-white">Prix TTC</span>
                        <span class="font-bold text-primary-600" x-text="(parseFloat(price || 0) * (1 + parseFloat(vat || 0) / 100)).toFixed(2) + ' &euro;'">0.00 &euro;</span>
                    </div>
                </div>
            </div>

            <!-- Inventory (for products) -->
            <div class="card p-6 space-y-4" x-show="type === 'product'" x-cloak>
                <h2 class="font-semibold text-secondary-900 dark:text-white">Stock & Inventaire</h2>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="track_inventory" id="track_inventory" value="1" {{ old('track_inventory') ? 'checked' : '' }} class="form-checkbox" x-model="trackInventory">
                    <div>
                        <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Suivre le stock</span>
                        <p class="text-xs text-secondary-500">Activer la gestion de l'inventaire pour ce produit</p>
                    </div>
                </label>

                <div class="grid grid-cols-3 gap-4" x-show="trackInventory" x-cloak>
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Quantité en stock</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="form-input w-full">
                        @error('stock_quantity')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="low_stock_threshold" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Seuil alerte stock bas</label>
                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0" class="form-input w-full">
                        @error('low_stock_threshold')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Poids (kg)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight') }}" step="0.01" min="0" class="form-input w-full">
                        @error('weight')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Custom Fields -->
            <div class="card p-6 space-y-4" x-show="customFieldsHtml" x-cloak>
                <h2 class="font-semibold text-secondary-900 dark:text-white">Champs personnalisés</h2>
                <div x-html="customFieldsHtml"></div>
            </div>

            @if($customFields->isNotEmpty())
            <div class="card p-6 space-y-4">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Champs personnalisés</h2>
                @include('products._custom-fields', ['customFields' => $customFields, 'values' => old('custom_fields', [])])
            </div>
            @endif

            <!-- Accounting -->
            <div class="card p-6 space-y-4">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Comptabilité</h2>

                <div>
                    <label for="accounting_code" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Code comptable</label>
                    <input type="text" name="accounting_code" id="accounting_code" value="{{ old('accounting_code') }}" class="form-input w-full" placeholder="Ex: 7000">
                    <p class="mt-1 text-sm text-secondary-500">Compte de produit associé (optionnel)</p>
                    @error('accounting_code')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="form-checkbox">
                    <label for="is_active" class="text-sm text-secondary-700 dark:text-secondary-300">
                        Article actif (visible dans les listes de sélection)
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer l'article
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function productForm() {
            return {
                type: '{{ old('type', 'service') }}',
                price: {{ old('unit_price', 0) }},
                vat: {{ old('vat_rate', 21) }},
                productTypeId: '{{ old('product_type_id', $selectedTypeId ?? '') }}',
                trackInventory: {{ old('track_inventory') ? 'true' : 'false' }},
                customFieldsHtml: '',
                loading: false,

                init() {
                    // Load custom fields if product type is pre-selected
                    if (this.productTypeId) {
                        this.loadCustomFields();
                    }
                },

                async loadCustomFields() {
                    if (!this.productTypeId) {
                        this.customFieldsHtml = '';
                        return;
                    }

                    this.loading = true;
                    try {
                        const response = await fetch(`{{ route('products.custom-fields') }}?product_type_id=${this.productTypeId}`);
                        const data = await response.json();
                        this.customFieldsHtml = data.html;

                        // Apply defaults from product type if any
                        if (data.productType && data.productType.default_values) {
                            const defaults = data.productType.default_values;
                            if (defaults.vat_rate && !this.vat) {
                                this.vat = defaults.vat_rate;
                            }
                        }
                    } catch (error) {
                        console.error('Error loading custom fields:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
