<x-admin-layout>
    <x-slot name="title">Modules - Gestion</x-slot>
    <x-slot name="header">Catalogue des Modules</x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Total Modules</p>
                    <p class="text-3xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Modules Core</p>
                    <p class="text-3xl font-bold text-success-400">{{ $stats['core'] }}</p>
                </div>
                <div class="w-12 h-12 bg-success-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Modules Premium</p>
                    <p class="text-3xl font-bold text-warning-400">{{ $stats['premium'] }}</p>
                </div>
                <div class="w-12 h-12 bg-warning-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Actifs</p>
                    <p class="text-3xl font-bold text-primary-400">{{ $stats['active'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">Tous les Modules</h2>
            <p class="text-secondary-400 text-sm">Gérer le catalogue des modules disponibles</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.modules.requests') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Demandes Tenants
            </a>
            <form action="{{ route('admin.modules.assign-core-all') }}" method="POST" onsubmit="return confirm('Assigner tous les modules core à toutes les entreprises ?')">
                @csrf
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Activer Core pour Tous
                </button>
            </form>
        </div>
    </div>

    <!-- Modules by Category -->
    @foreach($modules as $category => $categoryModules)
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 mb-6">
        <div class="px-6 py-4 border-b border-secondary-700">
            <h3 class="text-lg font-semibold text-white capitalize">{{ ucfirst($category) }}</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryModules as $module)
                <div class="bg-secondary-900 border border-secondary-700 rounded-lg p-4 hover:border-primary-500 transition">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-white font-semibold">{{ $module->name }}</h4>
                                @if($module->is_core)
                                    <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Core</span>
                                @endif
                                @if($module->is_premium)
                                    <span class="px-2 py-1 bg-warning-500/20 text-warning-400 text-xs rounded">Premium</span>
                                @endif
                                @if(!$module->is_active)
                                    <span class="px-2 py-1 bg-danger-500/20 text-danger-400 text-xs rounded">Inactif</span>
                                @endif
                            </div>
                            <p class="text-secondary-400 text-sm">{{ Str::limit($module->description, 80) }}</p>
                        </div>
                        <div class="w-10 h-10 flex items-center justify-center text-primary-400 ml-3">
                            {!! $module->icon_html !!}
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-secondary-400 mb-3">
                        <span>Code: <code class="text-primary-400">{{ $module->code }}</code></span>
                        @if($module->monthly_price > 0)
                            <span class="text-success-400 font-semibold">{{ number_format($module->monthly_price, 2) }} €/mois</span>
                        @else
                            <span class="text-secondary-500">Gratuit</span>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.modules.show', $module) }}" class="flex-1 btn-secondary text-center text-sm py-2">
                            Voir Détails
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

</x-admin-layout>
