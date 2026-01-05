<x-app-layout>
    <x-slot name="title">Immobilisations</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Immobilisations</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Immobilisations</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gestion des actifs et amortissements</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('assets.categories') }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Categories
                </a>
                <a href="{{ route('assets.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle immobilisation
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total actifs</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Valeur acquisition</p>
                        <h3 class="text-2xl font-semibold text-success-500">{{ number_format($stats['total_acquisition'], 2, ',', ' ') }} &euro;</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Valeur nette comptable</p>
                        <h3 class="text-2xl font-semibold text-info-500">{{ number_format($stats['total_current'], 2, ',', ' ') }} &euro;</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Amort. cumules</p>
                        <h3 class="text-2xl font-semibold text-warning-500">{{ number_format($stats['total_depreciation'], 2, ',', ' ') }} &euro;</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="card">
            <div class="p-4 border-b border-secondary-200 dark:border-secondary-700">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="form-input w-full">
                    </div>
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="fully_depreciated" {{ request('status') == 'fully_depreciated' ? 'selected' : '' }}>Totalement amorti</option>
                        <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Cede</option>
                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendu</option>
                    </select>
                    <select name="category_id" class="form-select">
                        <option value="">Toutes les categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Designation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Categorie</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date acquisition</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Valeur achat</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">VNC</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3 text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->reference ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->name }}</div>
                                    @if($asset->serial_number)
                                        <div class="text-xs text-secondary-500">S/N: {{ $asset->serial_number }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $asset->category->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $asset->acquisition_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-800 dark:text-white">{{ number_format($asset->acquisition_cost, 2, ',', ' ') }} &euro;</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">{{ number_format($asset->current_value, 2, ',', ' ') }} &euro;</td>
                                <td class="px-4 py-3 text-center">
                                    @switch($asset->status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Brouillon</span>
                                            @break
                                        @case('active')
                                            <span class="badge badge-success">Actif</span>
                                            @break
                                        @case('fully_depreciated')
                                            <span class="badge badge-warning">Amorti</span>
                                            @break
                                        @case('disposed')
                                            <span class="badge badge-danger">Cede</span>
                                            @break
                                        @case('sold')
                                            <span class="badge badge-info">Vendu</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('assets.show', $asset) }}" class="text-secondary-500 hover:text-primary-500" title="Voir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('assets.edit', $asset) }}" class="text-secondary-500 hover:text-warning-500" title="Modifier">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-secondary-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <p>Aucune immobilisation trouvee</p>
                                    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm mt-3">Creer une immobilisation</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assets->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
