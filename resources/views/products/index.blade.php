<x-app-layout>
    <x-slot name="title">Produits & Services</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Produits & Services</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Produits & Services</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez votre catalogue de produits et services</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.export') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter
                </a>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau
                </a>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-secondary-200 dark:border-secondary-700">
            <nav class="flex gap-4">
                <a
                    href="{{ route('products.index', array_merge(request()->except('type'), [])) }}"
                    class="tab {{ !request('type') ? 'tab-active' : '' }}"
                >
                    Tous
                </a>
                <a
                    href="{{ route('products.index', array_merge(request()->except('type'), ['type' => 'service'])) }}"
                    class="tab {{ request('type') === 'service' ? 'tab-active' : '' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Services
                </a>
                <a
                    href="{{ route('products.index', array_merge(request()->except('type'), ['type' => 'product'])) }}"
                    class="tab {{ request('type') === 'product' ? 'tab-active' : '' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Produits
                </a>
            </nav>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="p-4 flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <form action="{{ route('products.index') }}" method="GET" class="relative">
                        @if(request('type'))
                            <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Rechercher par nom, code..."
                            class="form-input pl-10"
                        >
                    </form>
                </div>

                <!-- Category Filter -->
                @if($categories->isNotEmpty())
                    <select name="category" onchange="window.location.href='{{ route('products.index') }}?category='+this.value+'&type={{ request('type') }}'" class="form-select w-auto">
                        <option value="">Toutes catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Status Filter -->
                <select name="status" onchange="window.location.href='{{ route('products.index') }}?status='+this.value+'&type={{ request('type') }}'" class="form-select w-auto">
                    <option value="">Actifs uniquement</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tous</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                <thead class="bg-secondary-50 dark:bg-secondary-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Produit/Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Prix HT</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">TVA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-secondary-900 divide-y divide-secondary-200 dark:divide-secondary-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 {{ !$product->is_active ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $product->type === 'service' ? 'bg-primary-100 dark:bg-primary-900/30' : 'bg-warning-100 dark:bg-warning-900/30' }} flex items-center justify-center">
                                        @if($product->type === 'service')
                                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-secondary-900 dark:text-white">
                                            {{ $product->name }}
                                        </div>
                                        @if($product->code)
                                            <div class="text-sm text-secondary-500 font-mono">{{ $product->code }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->type === 'service' ? 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' }}">
                                    {{ $product->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-secondary-900 dark:text-white">
                                {{ $product->formatted_price }}
                            </td>
                            <td class="px-6 py-4 text-center text-secondary-600 dark:text-secondary-400">
                                {{ number_format($product->vat_rate, 0) }}%
                            </td>
                            <td class="px-6 py-4 text-secondary-600 dark:text-secondary-400">
                                {{ $product->category ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-700 dark:text-secondary-400">
                                        Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('products.edit', $product) }}" class="p-2 text-secondary-400 hover:text-primary-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('products.duplicate', $product) }}" class="p-2 text-secondary-400 hover:text-warning-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Dupliquer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('products.toggle-active', $product) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-secondary-400 hover:text-{{ $product->is_active ? 'danger' : 'success' }}-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="{{ $product->is_active ? 'Désactiver' : 'Activer' }}">
                                            @if($product->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce {{ strtolower($product->type_label) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-secondary-400 hover:text-danger-600 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <p class="text-secondary-500 dark:text-secondary-400 mb-4">Aucun produit ou service trouvé</p>
                                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                                        Créer votre premier article
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
