<x-app-layout>
    <x-slot name="title">Mappings produits - {{ $connection->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('ecommerce.connections') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">E-commerce</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Mappings</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Mappings produits</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">{{ $connection->name }} - Associez les produits e-commerce a vos produits</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('ecommerce.mappings.sync', $connection) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Synchroniser produits
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('mapping-modal').classList.remove('hidden')" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau mapping
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Produits e-commerce</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $externalProductsCount }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Mappes</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $mappings->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-warning-100 text-warning-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Non mappes</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $unmappedCount }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Produits locaux</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $localProductsCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="mapped" {{ request('status') == 'mapped' ? 'selected' : '' }}>Mappes</option>
                        <option value="unmapped" {{ request('status') == 'unmapped' ? 'selected' : '' }}>Non mappes</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="SKU, nom produit...">
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>

        <!-- Liste des mappings -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Produit E-commerce</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">
                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Produit Local</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Sync prix</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Sync stock</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($mappings as $mapping)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-secondary-800 dark:text-white">{{ $mapping->external_product_name }}</div>
                                    <div class="text-xs text-secondary-500">SKU: {{ $mapping->external_sku }} | ID: {{ $mapping->external_product_id }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($mapping->product_id)
                                        <span class="text-success-500">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    @else
                                        <span class="text-warning-500">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($mapping->product)
                                        <div class="font-medium text-secondary-800 dark:text-white">{{ $mapping->product->name }}</div>
                                        <div class="text-xs text-secondary-500">{{ $mapping->product->reference }}</div>
                                    @else
                                        <span class="text-secondary-400 italic">Non mappe</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($mapping->sync_price)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-secondary">Non</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($mapping->sync_stock)
                                        <span class="badge badge-success">Oui</span>
                                    @else
                                        <span class="badge badge-secondary">Non</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="editMapping({{ json_encode($mapping) }})" class="text-secondary-500 hover:text-warning-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('ecommerce.mappings.destroy', [$connection, $mapping]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce mapping ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-secondary-500 hover:text-danger-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-secondary-500">
                                    Aucun mapping. Synchronisez les produits pour commencer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($mappings->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $mappings->links() }}
                </div>
            @endif
        </div>

        <!-- Produits non mappes -->
        @if($unmappedProducts->count() > 0)
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">
                    Produits e-commerce non mappes ({{ $unmappedProducts->count() }})
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($unmappedProducts->take(9) as $product)
                        <div class="p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-secondary-800 dark:text-white">{{ $product['name'] }}</p>
                                    <p class="text-sm text-secondary-500">SKU: {{ $product['sku'] ?? 'N/A' }}</p>
                                    <p class="text-sm text-secondary-500">Prix: {{ number_format($product['price'] ?? 0, 2, ',', ' ') }} EUR</p>
                                </div>
                                <button type="button" onclick="quickMap('{{ $product['id'] }}', '{{ addslashes($product['name']) }}', '{{ $product['sku'] ?? '' }}')" class="btn btn-sm btn-primary">
                                    Mapper
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($unmappedProducts->count() > 9)
                    <p class="text-sm text-secondary-500 mt-4">Et {{ $unmappedProducts->count() - 9 }} autres produits...</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Modal Mapping -->
    <div id="mapping-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeMappingModal()"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 id="mapping-modal-title" class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Nouveau mapping</h3>
                <form id="mapping-form" method="POST" action="{{ route('ecommerce.mappings.store', $connection) }}">
                    @csrf
                    <input type="hidden" name="_method" id="mapping-method" value="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Produit E-commerce <span class="text-danger-500">*</span></label>
                            <select name="external_product_id" id="map_external_product" class="form-select" required>
                                <option value="">Selectionnez...</option>
                                @foreach($externalProducts as $product)
                                    <option value="{{ $product['id'] }}" data-name="{{ $product['name'] }}" data-sku="{{ $product['sku'] ?? '' }}">
                                        {{ $product['name'] }} ({{ $product['sku'] ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Produit local <span class="text-danger-500">*</span></label>
                            <select name="product_id" id="map_product" class="form-select" required>
                                <option value="">Selectionnez...</option>
                                @foreach($localProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->reference }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="sync_price" value="1" class="form-checkbox" checked>
                                    <span class="text-sm">Synchroniser les prix</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="sync_stock" value="1" class="form-checkbox">
                                    <span class="text-sm">Synchroniser le stock</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeMappingModal()" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function closeMappingModal() {
            document.getElementById('mapping-modal').classList.add('hidden');
            document.getElementById('mapping-form').reset();
            document.getElementById('mapping-form').action = "{{ route('ecommerce.mappings.store', $connection) }}";
            document.getElementById('mapping-method').value = 'POST';
            document.getElementById('mapping-modal-title').textContent = 'Nouveau mapping';
        }

        function editMapping(mapping) {
            document.getElementById('mapping-modal-title').textContent = 'Modifier le mapping';
            document.getElementById('mapping-form').action = `/ecommerce/connections/{{ $connection->id }}/mappings/${mapping.id}`;
            document.getElementById('mapping-method').value = 'PUT';
            document.getElementById('map_external_product').value = mapping.external_product_id;
            document.getElementById('map_product').value = mapping.product_id || '';
            document.querySelector('input[name="sync_price"]').checked = mapping.sync_price;
            document.querySelector('input[name="sync_stock"]').checked = mapping.sync_stock;
            document.getElementById('mapping-modal').classList.remove('hidden');
        }

        function quickMap(externalId, name, sku) {
            document.getElementById('map_external_product').value = externalId;
            document.getElementById('mapping-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
