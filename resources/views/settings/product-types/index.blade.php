<x-app-layout>
    <x-slot name="title">Types de produits</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('settings.company') }}" class="text-secondary-500 hover:text-secondary-700">Paramètres</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Types de produits</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Paramètres</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Configurez votre entreprise et vos préférences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:w-64 flex-shrink-0">
                <x-settings-nav active="product-types" />
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Types de produits</h2>
                        <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                            Configurez les types de produits et leurs champs personnalisés.
                        </p>
                    </div>
            <div class="flex items-center gap-3">
                @if($productTypes->isEmpty())
                    <form action="{{ route('settings.product-types.seed-defaults') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Ajouter types par défaut
                        </button>
                    </form>
                @endif
                <a href="{{ route('settings.product-types.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau type
                </a>
            </div>
        </div>

        <!-- Types List -->
        @if($productTypes->isEmpty())
            <div class="card">
                <div class="card-body text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-secondary-100 dark:bg-secondary-800 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-1">Aucun type de produit</h3>
                    <p class="text-secondary-500 dark:text-secondary-400 mb-4">
                        Créez des types de produits pour définir des champs personnalisés selon vos besoins.
                    </p>
                </div>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($productTypes as $type)
                    <div class="card hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center"
                                        style="background-color: {{ $type->color ?? '#6B7280' }}20; color: {{ $type->color ?? '#6B7280' }}"
                                    >
                                        @if($type->icon)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($type->icon === 'box')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                @elseif($type->icon === 'briefcase')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                @elseif($type->icon === 'download')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                @elseif($type->icon === 'refresh')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                @elseif($type->icon === 'clock')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                @endif
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-secondary-900 dark:text-white">{{ $type->name }}</h3>
                                        <p class="text-sm text-secondary-500 dark:text-secondary-400">{{ $type->slug }}</p>
                                    </div>
                                </div>
                                @if(!$type->is_active)
                                    <span class="badge badge-secondary">Inactif</span>
                                @endif
                            </div>

                            @if($type->description)
                                <p class="mt-3 text-sm text-secondary-600 dark:text-secondary-400">{{ $type->description }}</p>
                            @endif

                            <!-- Type Properties -->
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if($type->is_service)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Service
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-secondary-100 dark:bg-secondary-800 text-secondary-700 dark:text-secondary-300 rounded text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        Produit
                                    </span>
                                @endif

                                @if($type->track_inventory)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-success-50 dark:bg-success-900/30 text-success-700 dark:text-success-300 rounded text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Stock
                                    </span>
                                @endif

                                @if($type->has_variants)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-warning-50 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300 rounded text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        Variantes
                                    </span>
                                @endif
                            </div>

                            <!-- Stats -->
                            <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                                <div class="text-sm text-secondary-500 dark:text-secondary-400">
                                    <span class="font-medium text-secondary-700 dark:text-secondary-300">{{ $type->products_count }}</span> produits
                                    &bull;
                                    <span class="font-medium text-secondary-700 dark:text-secondary-300">{{ $type->customFields->count() }}</span> champs
                                </div>
                                <a
                                    href="{{ route('settings.product-types.edit', $type) }}"
                                    class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium"
                                >
                                    Configurer &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
            </div>
        </div>
    </div>
</x-app-layout>
