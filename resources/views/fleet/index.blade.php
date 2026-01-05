<x-app-layout>
    <x-slot name="title">Gestion de flotte</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Gestion de flotte</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Gestion de flotte</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Vehicules, depenses et ATN belge</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('fleet.atn-report') }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Rapport ATN
                </a>
                <a href="{{ route('fleet.expenses') }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Depenses
                </a>
                <a href="{{ route('fleet.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau vehicule
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Total vehicules</p>
                        <h3 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Actifs</p>
                        <h3 class="text-2xl font-semibold text-success-500">{{ $stats['active'] }}</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Valeur totale</p>
                        <h3 class="text-2xl font-semibold text-info-500">{{ number_format($stats['total_value'], 0, ',', ' ') }} &euro;</h3>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center gap-4">
                    <div class="stat-icon stat-icon-warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 mb-1">Rappels en attente</p>
                        <h3 class="text-2xl font-semibold text-warning-500">{{ $stats['pending_reminders'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="card">
            <div class="p-4 border-b border-secondary-200 dark:border-secondary-700">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher marque, modele, plaque..." class="form-input w-full">
                    </div>
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                        <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>Cede</option>
                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendu</option>
                    </select>
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="car" {{ request('type') == 'car' ? 'selected' : '' }}>Voiture</option>
                        <option value="van" {{ request('type') == 'van' ? 'selected' : '' }}>Utilitaire</option>
                        <option value="truck" {{ request('type') == 'truck' ? 'selected' : '' }}>Camion</option>
                        <option value="motorcycle" {{ request('type') == 'motorcycle' ? 'selected' : '' }}>Moto</option>
                        <option value="electric_bike" {{ request('type') == 'electric_bike' ? 'selected' : '' }}>Velo electrique</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Vehicule</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Plaque</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Attribue a</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">CO2</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Valeur catalogue</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($vehicles as $vehicle)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-secondary-100 dark:bg-secondary-700 flex items-center justify-center">
                                            @if($vehicle->type == 'car')
                                                <svg class="w-6 h-6 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                                </svg>
                                            @elseif($vehicle->fuel_type == 'electric')
                                                <svg class="w-6 h-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-secondary-800 dark:text-white">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                            <div class="text-xs text-secondary-500">{{ $vehicle->year }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm font-mono text-secondary-800 dark:text-white">{{ $vehicle->license_plate ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    @switch($vehicle->type)
                                        @case('car') Voiture @break
                                        @case('van') Utilitaire @break
                                        @case('truck') Camion @break
                                        @case('motorcycle') Moto @break
                                        @case('electric_bike') Velo electrique @break
                                        @default {{ $vehicle->type }}
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $vehicle->assignedUser->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-800 dark:text-white">
                                    @if($vehicle->co2_emission)
                                        {{ $vehicle->co2_emission }} g/km
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-800 dark:text-white">
                                    @if($vehicle->catalog_value)
                                        {{ number_format($vehicle->catalog_value + $vehicle->options_value, 0, ',', ' ') }} &euro;
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @switch($vehicle->status)
                                        @case('active')
                                            <span class="badge badge-success">Actif</span>
                                            @break
                                        @case('maintenance')
                                            <span class="badge badge-warning">Maintenance</span>
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
                                        <a href="{{ route('fleet.show', $vehicle) }}" class="text-secondary-500 hover:text-primary-500" title="Voir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('fleet.edit', $vehicle) }}" class="text-secondary-500 hover:text-warning-500" title="Modifier">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                    <p>Aucun vehicule trouve</p>
                                    <a href="{{ route('fleet.create') }}" class="btn btn-primary btn-sm mt-3">Ajouter un vehicule</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vehicles->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
