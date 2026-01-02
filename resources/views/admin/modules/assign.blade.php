<x-admin-layout>
    <x-slot name="title">Assigner Modules - {{ $company->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.companies.show', $company) }}" class="text-secondary-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Assigner des Modules</h1>
                <p class="text-secondary-400 text-sm">{{ $company->name }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Company Current Modules -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Modules Actuellement Assignés</h3>
        @if($company->modules->count() > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($company->modules as $mod)
                    <span class="px-3 py-1 bg-primary-500/20 text-primary-400 text-sm rounded-lg flex items-center gap-2">
                        {{ $mod->name }}
                        @if($mod->pivot->is_enabled)
                            <svg class="w-4 h-4 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-secondary-400">Aucun module assigné</p>
        @endif

        @if($company->moduleRequests->count() > 0)
            <div class="mt-4 pt-4 border-t border-secondary-700">
                <h4 class="text-sm font-semibold text-white mb-2">Demandes en Attente ({{ $company->moduleRequests->where('status', 'pending')->count() }})</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($company->moduleRequests->where('status', 'pending') as $request)
                        <span class="px-3 py-1 bg-warning-500/20 text-warning-400 text-sm rounded-lg">
                            {{ $request->module->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Assignment Form -->
    <form action="{{ route('admin.modules.assign', $company) }}" method="POST" x-data="assignmentForm">
        @csrf

        <!-- Module Selection -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 mb-6">
            <div class="px-6 py-4 border-b border-secondary-700">
                <h3 class="text-lg font-semibold text-white">Sélectionner les Modules</h3>
                <p class="text-secondary-400 text-sm">Choisissez les modules à assigner</p>
            </div>

            <div class="p-6">
                @foreach($allModules->groupBy('category') as $category => $modules)
                <div class="mb-6 last:mb-0">
                    <h4 class="text-white font-semibold mb-3 capitalize">{{ ucfirst($category) }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($modules as $module)
                        <label class="flex items-start gap-3 p-3 bg-secondary-900 border border-secondary-700 rounded-lg cursor-pointer hover:border-primary-500 transition {{ in_array($module->id, $enabledModuleIds) ? 'ring-2 ring-success-500/50' : '' }}">
                            <input
                                type="checkbox"
                                name="modules[]"
                                value="{{ $module->id }}"
                                {{ in_array($module->id, $enabledModuleIds) ? 'checked' : '' }}
                                class="mt-1 rounded border-secondary-600 bg-secondary-700 text-primary-500 focus:ring-primary-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-white font-medium text-sm">{{ $module->name }}</span>
                                    @if($module->is_core)
                                        <span class="px-1.5 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Core</span>
                                    @endif
                                    @if($module->is_premium)
                                        <span class="px-1.5 py-0.5 bg-warning-500/20 text-warning-400 text-xs rounded">Premium</span>
                                    @endif
                                </div>
                                <p class="text-secondary-400 text-xs">{{ Str::limit($module->description, 60) }}</p>
                                @if($module->monthly_price > 0)
                                    <p class="text-success-400 text-xs font-semibold mt-1">{{ number_format($module->monthly_price, 2) }} €/mois</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Assignment Options -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 mb-6">
            <div class="px-6 py-4 border-b border-secondary-700">
                <h3 class="text-lg font-semibold text-white">Options d'Assignation</h3>
            </div>

            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Type d'Accès</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-4 bg-secondary-900 border border-secondary-700 rounded-lg cursor-pointer hover:border-primary-500">
                            <input type="radio" name="status" value="trial" checked
                                   class="rounded-full border-secondary-600 bg-secondary-700 text-primary-500 focus:ring-primary-500">
                            <div>
                                <div class="text-white font-medium">Essai Gratuit</div>
                                <div class="text-secondary-400 text-xs">Période d'essai limitée</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 bg-secondary-900 border border-secondary-700 rounded-lg cursor-pointer hover:border-primary-500">
                            <input type="radio" name="status" value="active"
                                   class="rounded-full border-secondary-600 bg-secondary-700 text-primary-500 focus:ring-primary-500">
                            <div>
                                <div class="text-white font-medium">Actif Permanent</div>
                                <div class="text-secondary-400 text-xs">Accès illimité</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div x-show="document.querySelector('input[name=status]:checked')?.value === 'trial'">
                    <label for="trial_days" class="block text-sm font-medium text-white mb-2">Durée de l'Essai (jours)</label>
                    <input type="number" id="trial_days" name="trial_days" value="30" min="1" max="365"
                           class="w-full bg-secondary-900 border border-secondary-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-secondary-400 text-xs mt-1">Entre 1 et 365 jours</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.companies.show', $company) }}" class="btn-secondary">
                Annuler
            </a>
            <button type="submit" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Assigner les Modules
            </button>
        </div>
    </form>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assignmentForm', () => ({
            init() {
                // Handle status radio change
                document.querySelectorAll('input[name=status]').forEach(radio => {
                    radio.addEventListener('change', () => {
                        this.$nextTick(() => {
                            // Alpine will handle the x-show directive
                        });
                    });
                });
            }
        }));
    });
    </script>
</x-admin-layout>
