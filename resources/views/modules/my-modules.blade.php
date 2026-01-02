<x-app-layout>
    <x-slot name="title">Mes Modules</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Mes Modules</span>
    @endsection

    <div class="space-y-6" x-data="{ activeTab: 'modules' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Mes Modules</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez les modules actifs de votre entreprise</p>
            </div>
            <a href="{{ route('modules.marketplace') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Explorer la Marketplace
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

        <!-- Tabs -->
        <div class="border-b border-secondary-200 dark:border-secondary-700">
            <nav class="flex gap-4">
                <button
                    @click="activeTab = 'modules'"
                    :class="activeTab === 'modules' ? 'tab-active' : ''"
                    class="tab"
                >
                    Modules Actifs
                    <span class="ml-2 px-2 py-0.5 text-xs bg-success-100 dark:bg-success-900/30 text-success-600 rounded-full">{{ $modules->flatten()->count() }}</span>
                </button>
                <button
                    @click="activeTab = 'requests'"
                    :class="activeTab === 'requests' ? 'tab-active' : ''"
                    class="tab"
                >
                    Mes Demandes
                    @if($requests->where('status', 'pending')->count() > 0)
                        <span class="ml-2 px-2 py-0.5 text-xs bg-warning-100 dark:bg-warning-900/30 text-warning-600 rounded-full">{{ $requests->where('status', 'pending')->count() }}</span>
                    @endif
                </button>
            </nav>
        </div>

        <!-- Modules Tab -->
        <div x-show="activeTab === 'modules'" x-transition>
            @forelse($modules as $category => $categoryModules)
            <div class="card mb-6">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white capitalize">{{ ucfirst($category) }}</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($categoryModules as $module)
                        @php
                            $pivot = $module->pivot;
                            $isTrialing = $pivot->status === 'trial' && $pivot->trial_ends_at;
                            $trialDaysLeft = $isTrialing ? now()->diffInDays($pivot->trial_ends_at, false) : 0;
                            $isExpiringSoon = $isTrialing && $trialDaysLeft <= 7 && $trialDaysLeft > 0;
                            $isExpired = $isTrialing && $trialDaysLeft <= 0;
                        @endphp
                        <div class="relative bg-secondary-50 dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-xl p-5 {{ $pivot->is_enabled ? '' : 'opacity-60' }}">
                            <!-- Status Badges -->
                            <div class="absolute top-3 right-3 flex items-center gap-2">
                                @if(!$pivot->is_enabled)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-200 dark:bg-secondary-700 text-secondary-600 dark:text-secondary-400">
                                        Désactivé
                                    </span>
                                @elseif($isExpired)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-danger-100 dark:bg-danger-900/30 text-danger-600">
                                        Trial expiré
                                    </span>
                                @elseif($isExpiringSoon)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/30 text-warning-600 animate-pulse">
                                        {{ $trialDaysLeft }}j restants
                                    </span>
                                @elseif($isTrialing)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-info-100 dark:bg-info-900/30 text-info-600">
                                        Trial - {{ $trialDaysLeft }}j
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-600">
                                        Actif
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

                            <!-- Activation Info -->
                            <div class="text-xs text-secondary-500 mb-4">
                                @if($pivot->enabled_at)
                                    Activé le {{ \Carbon\Carbon::parse($pivot->enabled_at)->format('d/m/Y') }}
                                @endif
                                @if($isTrialing && $pivot->trial_ends_at)
                                    <br>Trial jusqu'au {{ \Carbon\Carbon::parse($pivot->trial_ends_at)->format('d/m/Y') }}
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-secondary-200 dark:border-secondary-700">
                                <div class="flex items-center">
                                    <span class="text-sm text-secondary-500 mr-2">Visible:</span>
                                    <button
                                        type="button"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $pivot->is_visible ? 'bg-primary-600' : 'bg-secondary-300 dark:bg-secondary-600' }}"
                                        role="switch"
                                        aria-checked="{{ $pivot->is_visible ? 'true' : 'false' }}"
                                        onclick="toggleVisibility({{ $module->id }}, this)"
                                    >
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $pivot->is_visible ? 'translate-x-5' : 'translate-x-0' }}"
                                        ></span>
                                    </button>
                                </div>

                                @if($module->route_name)
                                    <a href="{{ route($module->route_name) }}" class="btn btn-sm btn-primary">
                                        Ouvrir
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-secondary-500 text-lg mb-4">Aucun module activé</p>
                    <a href="{{ route('modules.marketplace') }}" class="btn btn-primary">
                        Explorer la Marketplace
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Requests Tab -->
        <div x-show="activeTab === 'requests'" x-transition x-cloak>
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Historique des Demandes</h3>
                </div>
                @if($requests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-secondary-50 dark:bg-secondary-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Réponse</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-100 dark:divide-secondary-700">
                            @foreach($requests as $request)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center text-primary-600 mr-3">
                                            @if(!empty($request->module->icon))
                                                {!! $request->module->icon !!}
                                            @else
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-secondary-900 dark:text-white">{{ $request->module->name }}</div>
                                            @if($request->message)
                                                <div class="text-xs text-secondary-500 truncate max-w-xs">{{ $request->message }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($request->status)
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400">
                                                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                En attente
                                            </span>
                                            @break
                                        @case('approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Approuvée
                                            </span>
                                            @break
                                        @case('rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-400">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                                Refusée
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-4">
                                    @if($request->admin_message)
                                        <div class="text-sm text-secondary-600 dark:text-secondary-400 max-w-xs">
                                            {{ $request->admin_message }}
                                        </div>
                                    @elseif($request->status === 'pending')
                                        <span class="text-sm text-secondary-400 italic">En cours de traitement...</span>
                                    @else
                                        <span class="text-sm text-secondary-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-secondary-500 text-lg mb-4">Aucune demande effectuée</p>
                    <a href="{{ route('modules.marketplace') }}" class="btn btn-primary">
                        Demander un module
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleVisibility(moduleId, button) {
            fetch(`/modules/${moduleId}/toggle-visibility`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const span = button.querySelector('span');
                    if (data.visible) {
                        button.classList.remove('bg-secondary-300', 'dark:bg-secondary-600');
                        button.classList.add('bg-primary-600');
                        span.classList.remove('translate-x-0');
                        span.classList.add('translate-x-5');
                    } else {
                        button.classList.remove('bg-primary-600');
                        button.classList.add('bg-secondary-300', 'dark:bg-secondary-600');
                        span.classList.remove('translate-x-5');
                        span.classList.add('translate-x-0');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
    @endpush
</x-app-layout>
