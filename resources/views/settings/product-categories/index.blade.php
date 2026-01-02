<x-app-layout>
    <x-slot name="title">Catégories de produits</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('settings.company') }}" class="text-secondary-500 hover:text-secondary-700">Paramètres</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Catégories</span>
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
                <x-settings-nav active="product-categories" />
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Catégories de produits</h2>
                        <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                            Organisez vos produits en catégories et sous-catégories.
                        </p>
                    </div>
                    <a href="{{ route('settings.product-categories.create') }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle catégorie
                    </a>
                </div>

                <!-- Categories Tree -->
                @if($categories->isEmpty())
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <div class="w-16 h-16 mx-auto bg-secondary-100 dark:bg-secondary-800 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-1">Aucune catégorie</h3>
                            <p class="text-secondary-500 dark:text-secondary-400 mb-4">
                                Créez des catégories pour organiser vos produits.
                            </p>
                            <a href="{{ route('settings.product-categories.create') }}" class="btn btn-primary">
                                Créer une catégorie
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                @foreach($categories as $category)
                                    @include('settings.product-categories._category-row', ['category' => $category, 'level' => 0])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
