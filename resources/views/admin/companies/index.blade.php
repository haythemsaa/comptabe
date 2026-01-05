<x-admin-layout>
    <x-slot name="title">Gestion des Entreprises</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Entreprises</span>
            <a href="{{ route('admin.companies.create') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-xl font-medium transition-colors flex items-center gap-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvelle Entreprise
            </a>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.companies.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (nom, TVA, email)..." class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actives</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendues</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Companies Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">TVA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Utilisateurs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Factures</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Créé le</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($companies as $company)
                    <tr class="hover:bg-secondary-700/50 {{ $company->trashed() ? 'opacity-60' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-primary-400">
                                    {{ strtoupper(substr($company->name, 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.companies.show', $company) }}" class="font-medium text-white hover:text-primary-400">
                                        {{ $company->name }}
                                    </a>
                                    @if($company->email)
                                        <p class="text-sm text-secondary-500">{{ $company->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-secondary-300">
                            {{ $company->vat_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            {{ $company->users_count }}
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            {{ number_format($company->invoices_count) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($company->trashed())
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">Suspendue</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-400">
                            {{ $company->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.companies.show', $company) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(!$company->trashed())
                                    <form action="{{ route('admin.companies.impersonate', $company) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-warning-400" title="Impersonner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.companies.suspend', $company) }}" method="POST" class="inline" onsubmit="return confirm('Suspendre cette entreprise?')">
                                        @csrf
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-danger-400" title="Suspendre">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.companies.restore', $company) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-success-400" title="Réactiver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-secondary-500">
                            Aucune entreprise trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($companies->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
