<x-admin-layout>
    <x-slot name="title">{{ $module->name }} - Détails</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.modules.index') }}" class="text-secondary-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $module->name }}</h1>
                    <p class="text-secondary-400 text-sm">Module: {{ $module->code }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Module Info Card -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Informations</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-secondary-400 text-sm">Code</dt>
                        <dd class="text-white font-mono">{{ $module->code }}</dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400 text-sm">Description</dt>
                        <dd class="text-white">{{ $module->description ?? 'Aucune description' }}</dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400 text-sm">Catégorie</dt>
                        <dd class="text-white capitalize">{{ ucfirst($module->category) }}</dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400 text-sm">Version</dt>
                        <dd class="text-white">{{ $module->version }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Configuration</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-secondary-400 text-sm">Type</dt>
                        <dd class="flex gap-2">
                            @if($module->is_core)
                                <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Core</span>
                            @endif
                            @if($module->is_premium)
                                <span class="px-2 py-1 bg-warning-500/20 text-warning-400 text-xs rounded">Premium</span>
                            @endif
                            @if(!$module->is_core && !$module->is_premium)
                                <span class="px-2 py-1 bg-secondary-500/20 text-secondary-400 text-xs rounded">Standard</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400 text-sm">Prix</dt>
                        <dd class="text-white">
                            @if($module->monthly_price > 0)
                                <span class="text-success-400 font-semibold">{{ number_format($module->monthly_price, 2) }} €/mois</span>
                            @else
                                <span class="text-secondary-500">Gratuit</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400 text-sm">Statut</dt>
                        <dd>
                            @if($module->is_active)
                                <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Actif</span>
                            @else
                                <span class="px-2 py-1 bg-danger-500/20 text-danger-400 text-xs rounded">Inactif</span>
                            @endif
                        </dd>
                    </div>
                    @if($module->dependencies && count($module->dependencies) > 0)
                    <div>
                        <dt class="text-secondary-400 text-sm">Dépendances</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach($module->dependencies as $dep)
                                <code class="px-2 py-1 bg-secondary-900 text-primary-400 text-xs rounded">{{ $dep }}</code>
                            @endforeach
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Companies Using This Module -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700">
        <div class="px-6 py-4 border-b border-secondary-700 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Entreprises ({{ $module->companies->count() }})</h3>
                <p class="text-secondary-400 text-sm">Liste des entreprises ayant ce module</p>
            </div>
        </div>

        @if($module->companies->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Entreprise</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Activé le</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Essai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Visible</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-700">
                    @foreach($module->companies as $company)
                    <tr class="hover:bg-secondary-900/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-white font-medium">{{ $company->name }}</div>
                                <div class="text-secondary-400 text-sm">{{ $company->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($company->pivot->is_enabled)
                                <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Activé</span>
                            @else
                                <span class="px-2 py-1 bg-danger-500/20 text-danger-400 text-xs rounded">Désactivé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-secondary-300 text-sm">
                            {{ $company->pivot->enabled_at ? $company->pivot->enabled_at->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($company->pivot->status === 'trial')
                                <span class="px-2 py-1 bg-warning-500/20 text-warning-400 text-xs rounded">
                                    Trial {{ $company->pivot->trial_ends_at ? '(' . $company->pivot->trial_ends_at->diffForHumans() . ')' : '' }}
                                </span>
                            @elseif($company->pivot->status === 'active')
                                <span class="px-2 py-1 bg-success-500/20 text-success-400 text-xs rounded">Actif</span>
                            @else
                                <span class="px-2 py-1 bg-secondary-500/20 text-secondary-400 text-xs rounded">{{ ucfirst($company->pivot->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($company->pivot->is_visible)
                                <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    onclick="toggleModule('{{ $company->id }}', '{{ $module->id }}')"
                                    class="text-sm {{ $company->pivot->is_enabled ? 'text-danger-400 hover:text-danger-300' : 'text-success-400 hover:text-success-300' }}">
                                    {{ $company->pivot->is_enabled ? 'Désactiver' : 'Activer' }}
                                </button>
                                <form action="{{ route('admin.modules.detach', [$company, $module]) }}" method="POST"
                                      onsubmit="return confirm('Retirer ce module de {{ $company->name }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-danger-400 hover:text-danger-300">Retirer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-secondary-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-secondary-400">Aucune entreprise n'utilise ce module</p>
        </div>
        @endif
    </div>

    <script>
    function toggleModule(companyId, moduleId) {
        fetch(`/admin/modules/${companyId}/${moduleId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erreur lors du changement de statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du changement de statut');
        });
    }
    </script>
</x-admin-layout>
