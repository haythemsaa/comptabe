<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('products.index') }}" class="text-secondary-500 hover:text-secondary-700">Produits & Services</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $product->name }}</span>
    @endsection

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('products.index') }}" class="btn btn-secondary btn-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $product->name }}</h1>
                        @if(!$product->is_active)
                            <span class="badge badge-secondary">Inactif</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 text-sm {{ $product->type === 'service' ? 'text-primary-600' : 'text-warning-600' }}">
                            @if($product->type === 'service')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            @endif
                            {{ $product->type_label }}
                        </span>
                        @if($product->code)
                            <span class="text-secondary-400">|</span>
                            <span class="text-sm text-secondary-500">{{ $product->code }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.duplicate', $product) }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Dupliquer
                </a>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informations</h2>
                    </div>
                    <div class="card-body">
                        @if($product->description)
                            <p class="text-secondary-700 dark:text-secondary-300 mb-4">{{ $product->description }}</p>
                        @endif

                        <dl class="grid gap-4 sm:grid-cols-2">
                            @if($product->sku)
                            <div>
                                <dt class="text-sm text-secondary-500">SKU</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->sku }}</dd>
                            </div>
                            @endif
                            @if($product->barcode)
                            <div>
                                <dt class="text-sm text-secondary-500">Code-barres</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->barcode }}</dd>
                            </div>
                            @endif
                            @if($product->brand)
                            <div>
                                <dt class="text-sm text-secondary-500">Marque</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->brand }}</dd>
                            </div>
                            @endif
                            @if($product->manufacturer)
                            <div>
                                <dt class="text-sm text-secondary-500">Fabricant</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->manufacturer }}</dd>
                            </div>
                            @endif
                            @if($product->productType)
                            <div>
                                <dt class="text-sm text-secondary-500">Type de produit</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->productType->name }}</dd>
                            </div>
                            @endif
                            @if($product->productCategory)
                            <div>
                                <dt class="text-sm text-secondary-500">Catégorie</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->productCategory->name }}</dd>
                            </div>
                            @elseif($product->category)
                            <div>
                                <dt class="text-sm text-secondary-500">Catégorie</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->category }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Custom Fields -->
                @if($customFields->isNotEmpty() && $product->custom_fields)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Champs personnalisés</h2>
                    </div>
                    <div class="card-body">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            @foreach($customFields as $field)
                                @php
                                    $value = $product->custom_fields[$field->slug] ?? null;
                                @endphp
                                @if($value !== null && $value !== '')
                                <div>
                                    <dt class="text-sm text-secondary-500">{{ $field->label }}</dt>
                                    <dd class="font-medium text-secondary-900 dark:text-white">
                                        @if($field->type === 'boolean')
                                            @if($value)
                                                <span class="text-success-600">Oui</span>
                                            @else
                                                <span class="text-secondary-400">Non</span>
                                            @endif
                                        @elseif($field->type === 'currency')
                                            {{ number_format($value, 2, ',', ' ') }} &euro;
                                        @elseif($field->type === 'date')
                                            {{ \Carbon\Carbon::parse($value)->format('d/m/Y') }}
                                        @elseif($field->type === 'datetime')
                                            {{ \Carbon\Carbon::parse($value)->format('d/m/Y H:i') }}
                                        @elseif($field->type === 'color')
                                            <span class="inline-flex items-center gap-2">
                                                <span class="w-4 h-4 rounded" style="background-color: {{ $value }}"></span>
                                                {{ $value }}
                                            </span>
                                        @elseif($field->type === 'url')
                                            <a href="{{ $value }}" target="_blank" class="text-primary-600 hover:underline">{{ $value }}</a>
                                        @elseif($field->type === 'email')
                                            <a href="mailto:{{ $value }}" class="text-primary-600 hover:underline">{{ $value }}</a>
                                        @elseif($field->type === 'multiselect' && is_array($value))
                                            {{ implode(', ', $value) }}
                                        @elseif($field->type === 'select' && isset($field->options['choices'][$value]))
                                            {{ $field->options['choices'][$value] }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </dd>
                                </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                </div>
                @endif

                <!-- Stock Information (for products with inventory tracking) -->
                @if($product->type === 'product' && $product->track_inventory)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Stock</h2>
                    </div>
                    <div class="card-body">
                        <dl class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-sm text-secondary-500">Quantité en stock</dt>
                                <dd class="font-medium text-2xl {{ $product->stock_quantity <= ($product->low_stock_threshold ?? 0) ? 'text-danger-600' : 'text-secondary-900 dark:text-white' }}">
                                    {{ $product->stock_quantity ?? 0 }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-secondary-500">Seuil d'alerte</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->low_stock_threshold ?? 5 }}</dd>
                            </div>
                            @if($product->weight)
                            <div>
                                <dt class="text-sm text-secondary-500">Poids</dt>
                                <dd class="font-medium text-secondary-900 dark:text-white">{{ $product->weight }} kg</dd>
                            </div>
                            @endif
                        </dl>

                        @if($product->stock_quantity <= ($product->low_stock_threshold ?? 0))
                            <div class="mt-4 p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg flex items-center gap-2 text-danger-700 dark:text-danger-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Stock bas - pensez à réapprovisionner</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Pricing Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Tarification</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <span class="text-sm text-secondary-500">Prix de vente HT</span>
                            <div class="text-3xl font-bold text-primary-600">{{ $product->formatted_price }}</div>
                        </div>

                        @if($product->cost_price)
                        <div>
                            <span class="text-sm text-secondary-500">Prix d'achat HT</span>
                            <div class="text-lg font-medium text-secondary-900 dark:text-white">{{ number_format($product->cost_price, 2, ',', ' ') }} &euro;</div>
                            @php
                                $margin = $product->unit_price > 0 ? (($product->unit_price - $product->cost_price) / $product->unit_price) * 100 : 0;
                            @endphp
                            <span class="text-sm text-secondary-500">Marge: {{ number_format($margin, 1) }}%</span>
                        </div>
                        @endif

                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary-500">TVA</span>
                                <span class="font-medium text-secondary-900 dark:text-white">{{ number_format($product->vat_rate, 0) }}%</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-secondary-500">Unité</span>
                                <span class="font-medium text-secondary-900 dark:text-white">{{ \App\Models\Product::UNITS[$product->unit] ?? $product->unit }}</span>
                            </div>
                            <div class="flex justify-between mt-2 pt-2 border-t border-secondary-200 dark:border-secondary-700">
                                <span class="font-medium text-secondary-700 dark:text-secondary-300">Prix TTC</span>
                                <span class="font-bold text-secondary-900 dark:text-white">{{ number_format($product->unit_price * (1 + $product->vat_rate / 100), 2, ',', ' ') }} &euro;</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accounting -->
                @if($product->accounting_code)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Comptabilité</h2>
                    </div>
                    <div class="card-body">
                        <div>
                            <span class="text-sm text-secondary-500">Code comptable</span>
                            <div class="font-medium text-secondary-900 dark:text-white">{{ $product->accounting_code }}</div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                <div class="card">
                    <div class="card-body text-sm text-secondary-500 space-y-1">
                        <div>Créé le {{ $product->created_at->format('d/m/Y à H:i') }}</div>
                        @if($product->updated_at->ne($product->created_at))
                            <div>Modifié le {{ $product->updated_at->format('d/m/Y à H:i') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
