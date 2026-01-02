<x-admin-layout>
    <x-slot name="title">Support Client</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Support Client</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.support.export') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Total Tickets</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Ouverts</p>
            <p class="text-2xl font-bold text-primary-400">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">En cours</p>
            <p class="text-2xl font-bold text-warning-400">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Résolus</p>
            <p class="text-2xl font-bold text-success-400">{{ $stats['resolved'] }}</p>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Urgents</p>
            <p class="text-2xl font-bold text-danger-400">{{ $stats['urgent'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Non assignés</p>
            <p class="text-2xl font-bold text-secondary-400">{{ $stats['unassigned'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Temps réponse moyen</p>
            <p class="text-2xl font-bold text-white">
                @if($stats['avg_response_time'])
                    {{ $stats['avg_response_time'] }}h
                @else
                    -
                @endif
            </p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Temps résolution moyen</p>
            <p class="text-2xl font-bold text-white">
                @if($stats['avg_resolution_time'])
                    {{ $stats['avg_resolution_time'] }}h
                @else
                    -
                @endif
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.support.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher ticket..." class="flex-1 min-w-[200px] bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">

            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Ouverts</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="waiting_customer" {{ request('status') === 'waiting_customer' ? 'selected' : '' }}>En attente client</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolus</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Fermés</option>
            </select>

            <select name="priority" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes priorités</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Haute</option>
                <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normale</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Basse</option>
            </select>

            <select name="category" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes catégories</option>
                <option value="technical" {{ request('category') === 'technical' ? 'selected' : '' }}>Technique</option>
                <option value="billing" {{ request('category') === 'billing' ? 'selected' : '' }}>Facturation</option>
                <option value="feature_request" {{ request('category') === 'feature_request' ? 'selected' : '' }}>Demande fonctionnalité</option>
                <option value="bug" {{ request('category') === 'bug' ? 'selected' : '' }}>Bug</option>
                <option value="question" {{ request('category') === 'question' ? 'selected' : '' }}>Question</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Autre</option>
            </select>

            <select name="assigned_to" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous assignés</option>
                <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Non assignés</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ request('assigned_to') === $admin->id ? 'selected' : '' }}>{{ $admin->full_name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                Rechercher
            </button>

            @if(request()->hasAny(['search', 'status', 'priority', 'category', 'assigned_to']))
                <a href="{{ route('admin.support.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Ticket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Société / Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Sujet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Priorité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Assigné à</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Créé</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <p class="font-mono text-sm text-primary-400">{{ $ticket->ticket_number }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->company)
                                <p class="font-medium text-white">{{ $ticket->company->name }}</p>
                            @endif
                            @if($ticket->user)
                                <p class="text-sm text-secondary-400">{{ $ticket->user->full_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-white">{{ Str::limit($ticket->subject, 50) }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-primary-500/20 text-primary-400">
                                {{ ucfirst($ticket->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->priority_color }}-500/20 text-{{ $ticket->priority_color }}-400">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($ticket->assignedTo)
                                <p class="text-sm text-white">{{ $ticket->assignedTo->full_name }}</p>
                            @else
                                <p class="text-sm text-secondary-500">Non assigné</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-secondary-400">{{ $ticket->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.support.show', $ticket) }}" class="px-3 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors text-sm">
                                Voir détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-secondary-500">
                            Aucun ticket trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
