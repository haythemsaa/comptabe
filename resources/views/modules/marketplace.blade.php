<x-app-layout>
    <x-slot name="title">Marketplace des Modules</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Marketplace</span>
    @endsection

    <div class="space-y-6" x-data="{ showRequestModal: false, selectedModule: null }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Marketplace des Modules</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Explorez et demandez des modules pour votre entreprise</p>
            </div>
            <a href="{{ route('modules.my-modules') }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Mes Modules
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-success-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-success-700 dark:text-success-300">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-danger-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-danger-700 dark:text-danger-300">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Modules by Category -->
        @foreach($allModules as $category => $modules)
        <div class="card">
            <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white capitalize flex items-center">
                    @switch($category)
                        @case('core')
                            <span class="w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            @break
                        @case('business')
                            <span class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            @break
                        @case('hr')
                            <span class="w-8 h-8 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                            @break
                        @case('finance')
                            <span class="w-8 h-8 bg-info-100 dark:bg-info-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            @break
                        @case('projects')
                            <span class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            @break
                        @case('tech')
                            <span class="w-8 h-8 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </span>
                            @break
                        @default
                            <span class="w-8 h-8 bg-secondary-100 dark:bg-secondary-700 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </span>
                    @endswitch
                    {{ ucfirst($category) }}
                </h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($modules as $module)
                    @php
                        $isEnabled = in_array($module->id, $enabledModuleIds);
                        $isPending = in_array($module->id, $requestedModuleIds);
                    @endphp
                    <div class="relative bg-secondary-50 dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-xl p-5 hover:border-primary-300 dark:hover:border-primary-600 transition-all {{ $isEnabled ? 'ring-2 ring-success-500' : '' }}">
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            @if($isEnabled)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Actif
                                </span>
                            @elseif($isPending)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400">
                                    <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    En attente
                                </span>
                            @endif
                        </div>

                        <!-- Module Icon & Info -->
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center flex-shrink-0 text-primary-600">
                                @if(!empty($module->icon))
                                    {!! $module->icon !!}
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-secondary-900 dark:text-white truncate">{{ $module->name }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($module->is_core)
                                        <span class="px-2 py-0.5 bg-success-100 dark:bg-success-900/30 text-success-600 text-xs rounded font-medium">Core</span>
                                    @endif
                                    @if($module->is_premium)
                                        <span class="px-2 py-0.5 bg-warning-100 dark:bg-warning-900/30 text-warning-600 text-xs rounded font-medium">Premium</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-4 line-clamp-2">
                            {{ $module->description ?? 'Aucune description disponible.' }}
                        </p>

                        <!-- Price & Action -->
                        <div class="flex items-center justify-between pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <div>
                                @if($module->monthly_price > 0)
                                    <span class="text-lg font-bold text-secondary-900 dark:text-white">{{ number_format($module->monthly_price, 2) }} &euro;</span>
                                    <span class="text-xs text-secondary-500">/mois</span>
                                @else
                                    <span class="text-success-600 dark:text-success-400 font-medium">Gratuit</span>
                                @endif
                            </div>

                            @if($isEnabled)
                                <a href="{{ route('modules.my-modules') }}" class="btn btn-sm btn-secondary">
                                    Voir
                                </a>
                            @elseif($isPending)
                                <button disabled class="btn btn-sm btn-secondary opacity-50 cursor-not-allowed">
                                    En attente
                                </button>
                            @else
                                <button
                                    @click="selectedModule = {{ json_encode(['id' => $module->id, 'name' => $module->name, 'description' => $module->description]) }}; showRequestModal = true"
                                    class="btn btn-sm btn-primary"
                                >
                                    Demander
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <!-- Request Modal -->
        <div x-show="showRequestModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRequestModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-secondary-900/75 transition-opacity" @click="showRequestModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showRequestModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block align-bottom bg-white dark:bg-secondary-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" :action="selectedModule ? '{{ url('modules') }}/' + selectedModule.id + '/request' : '#'">
                        @csrf
                        <div class="px-6 pt-6 pb-4">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white" x-text="selectedModule ? 'Demander: ' + selectedModule.name : 'Demander un module'"></h3>
                                    <p class="text-sm text-secondary-500">Votre demande sera examinee par l'administrateur</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="message" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                                        Message (optionnel)
                                    </label>
                                    <textarea
                                        name="message"
                                        id="message"
                                        rows="3"
                                        class="form-textarea w-full"
                                        placeholder="Expliquez pourquoi vous avez besoin de ce module..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900/50 flex justify-end gap-3">
                            <button type="button" @click="showRequestModal = false" class="btn btn-secondary">
                                Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Envoyer la demande
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
