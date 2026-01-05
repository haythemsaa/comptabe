<x-app-layout>
    <x-slot name="title">Gestion de Stock</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Stock</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Tableau de bord Stock</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Vue d'ensemble de votre inventaire</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('stock.movements.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau mouvement
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Entrepôts</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $warehouses->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Produits suivis</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $totalProducts }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Valeur totale</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($totalStockValue, 0, ',', ' ') }} EUR</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Alertes actives</p>
                            <p class="text-2xl font-bold text-warning-600">{{ $alerts->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('stock.warehouses.index') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body text-center">
                    <svg class="w-8 h-8 mx-auto text-primary-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-medium text-secondary-900 dark:text-white">Entrepôts</span>
                </div>
            </a>
            <a href="{{ route('stock.levels') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body text-center">
                    <svg class="w-8 h-8 mx-auto text-success-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="font-medium text-secondary-900 dark:text-white">Niveaux de stock</span>
                </div>
            </a>
            <a href="{{ route('stock.movements.index') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body text-center">
                    <svg class="w-8 h-8 mx-auto text-info-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-medium text-secondary-900 dark:text-white">Mouvements</span>
                </div>
            </a>
            <a href="{{ route('stock.inventories.index') }}" class="card hover:shadow-lg transition-shadow">
                <div class="card-body text-center">
                    <svg class="w-8 h-8 mx-auto text-warning-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="font-medium text-secondary-900 dark:text-white">Inventaires</span>
                </div>
            </a>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Low Stock Products -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Stock faible</h3>
                    <a href="{{ route('stock.levels', ['status' => 'low_stock']) }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    @forelse($lowStockProducts as $stock)
                    <div class="flex items-center justify-between px-4 py-3 border-b border-secondary-100 dark:border-secondary-700 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $stock->product->name }}</p>
                                <p class="text-xs text-secondary-500">{{ $stock->warehouse->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-warning-600">{{ $stock->quantity }} {{ $stock->product->unit }}</p>
                            <p class="text-xs text-secondary-500">Min: {{ $stock->min_quantity }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-secondary-500">
                        Aucun produit en stock faible
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Movements -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Mouvements récents</h3>
                    <a href="{{ route('stock.movements.index') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentMovements as $movement)
                    <div class="flex items-center justify-between px-4 py-3 border-b border-secondary-100 dark:border-secondary-700 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-{{ $movement->getTypeColor() }}-100 dark:bg-{{ $movement->getTypeColor() }}-900/30 flex items-center justify-center">
                                @if($movement->type === 'in')
                                <svg class="w-5 h-5 text-{{ $movement->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                                @elseif($movement->type === 'out')
                                <svg class="w-5 h-5 text-{{ $movement->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-{{ $movement->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-secondary-900 dark:text-white">{{ $movement->product->name }}</p>
                                <p class="text-xs text-secondary-500">{{ $movement->getTypeLabel() }} - {{ $movement->warehouse->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold {{ $movement->isIncoming() ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $movement->isIncoming() ? '+' : '-' }}{{ $movement->quantity }}
                            </p>
                            <p class="text-xs text-secondary-500">{{ $movement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center text-secondary-500">
                        Aucun mouvement récent
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if($alerts->count() > 0)
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">Alertes</h3>
                <a href="{{ route('stock.alerts.index') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
            </div>
            <div class="card-body p-0">
                @foreach($alerts as $alert)
                <div class="flex items-center justify-between px-4 py-3 border-b border-secondary-100 dark:border-secondary-700 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-{{ $alert->getTypeColor() }}-100 dark:bg-{{ $alert->getTypeColor() }}-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-{{ $alert->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-secondary-900 dark:text-white">{{ $alert->product->name }}</p>
                            <p class="text-xs text-secondary-500">{{ $alert->getTypeLabel() }} @if($alert->warehouse) - {{ $alert->warehouse->name }} @endif</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $alert->getTypeColor() }}-100 text-{{ $alert->getTypeColor() }}-700">
                            {{ $alert->current_quantity }} en stock
                        </span>
                        <form action="{{ route('stock.alerts.resolve', $alert) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost text-success-600" title="Résoudre">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Warehouses Overview -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">Entrepôts</h3>
                <a href="{{ route('stock.warehouses.create') }}" class="btn btn-sm btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Entrepôt</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Localisation</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Produits</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Valeur</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="font-medium text-secondary-900 dark:text-white">
                                        {{ $warehouse->name }}
                                        @if($warehouse->is_default)
                                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-primary-100 text-primary-700 rounded">Par défaut</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-secondary-500">{{ $warehouse->code }}</div>
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $warehouse->city ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center text-secondary-600 dark:text-secondary-400">
                                {{ $warehouse->products_count ?? 0 }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($warehouse->getTotalStockValue(), 0, ',', ' ') }} EUR
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('stock.warehouses.show', $warehouse) }}" class="btn btn-sm btn-ghost">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Aucun entrepôt configuré.
                                <a href="{{ route('stock.warehouses.create') }}" class="text-primary-600 hover:underline">Créer le premier</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
