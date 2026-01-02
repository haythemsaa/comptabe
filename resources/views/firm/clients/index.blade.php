<x-firm-layout>
    <x-slot name="title">Mes clients</x-slot>
    <x-slot name="header">Gestion des clients</x-slot>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('firm.clients.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (nom, TVA)..."
                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendus</option>
                <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Termines</option>
            </select>
            <select name="manager" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les gestionnaires</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" {{ request('manager') === $manager->id ? 'selected' : '' }}>
                        {{ $manager->full_name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'status', 'manager']))
                <a href="{{ route('firm.clients.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Reinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Header with Add button -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-secondary-400">{{ $clients->total() }} client(s) trouve(s)</p>
        <a href="{{ route('firm.clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajouter un client
        </a>
    </div>

    <!-- Clients Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Type de mandat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Gestionnaire</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Taches</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($clients as $mandate)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-primary-400">
                                    {{ strtoupper(substr($mandate->company->name ?? 'N', 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('firm.clients.show', $mandate) }}" class="font-medium text-white hover:text-primary-400">
                                        {{ $mandate->company->name ?? 'N/A' }}
                                    </a>
                                    <p class="text-sm text-secondary-500">{{ $mandate->company->vat_number ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            {{ $mandate->type_label }}
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            @if($mandate->manager)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-secondary-700 flex items-center justify-center text-xs font-medium">
                                        {{ $mandate->manager->initials }}
                                    </div>
                                    {{ $mandate->manager->full_name }}
                                </div>
                            @else
                                <span class="text-secondary-500">Non assigne</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $pendingTasks = $mandate->tasks()->pending()->count();
                                $overdueTasks = $mandate->tasks()->overdue()->count();
                            @endphp
                            <div class="flex items-center gap-2">
                                @if($overdueTasks > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-danger-500/20 text-danger-400">
                                        {{ $overdueTasks }} en retard
                                    </span>
                                @endif
                                @if($pendingTasks > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-warning-500/20 text-warning-400">
                                        {{ $pendingTasks }} en cours
                                    </span>
                                @endif
                                @if($pendingTasks === 0 && $overdueTasks === 0)
                                    <span class="text-secondary-500 text-sm">Aucune</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $mandate->status_color }}-500/20 text-{{ $mandate->status_color }}-400">
                                {{ $mandate->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('firm.clients.show', $mandate) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('firm.clients.edit', $mandate) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('firm.tasks.create', ['mandate' => $mandate->id]) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-primary-400" title="Nouvelle tache">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="text-lg font-medium">Aucun client trouve</p>
                                <p class="mt-1">Commencez par ajouter votre premier client</p>
                                <a href="{{ route('firm.clients.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Ajouter un client
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($clients->hasPages())
        <div class="mt-6">
            {{ $clients->links() }}
        </div>
    @endif
</x-firm-layout>
