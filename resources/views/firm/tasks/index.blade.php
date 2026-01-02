<x-firm-layout>
    <x-slot name="title">Tâches</x-slot>
    <x-slot name="header">Toutes les tâches</x-slot>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('firm.tasks.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (titre, description)..."
                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>À faire</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>À réviser</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
            </select>
            <select name="assigned_to" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                        {{ $user->full_name }}
                    </option>
                @endforeach
            </select>
            <select name="client" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                        {{ $client->company->name ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'status', 'assigned_to', 'client']))
                <a href="{{ route('firm.tasks.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Header with Add button -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-secondary-400">{{ $tasks->total() }} tâche(s) trouvée(s)</p>
        <a href="{{ route('firm.tasks.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvelle tâche
        </a>
    </div>

    <!-- Tasks Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Assigné à</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Échéance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Priorité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($tasks as $task)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div>
                                <a href="{{ route('firm.tasks.show', $task) }}" class="font-medium text-white hover:text-primary-400">
                                    {{ $task->title }}
                                </a>
                                @if($task->task_type)
                                    <p class="text-sm text-secondary-500">{{ $task->type_label }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($task->clientMandate && $task->clientMandate->company)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center text-xs font-bold text-primary-400">
                                        {{ strtoupper(substr($task->clientMandate->company->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('firm.clients.show', $task->clientMandate) }}" class="text-sm text-white hover:text-primary-400">
                                            {{ $task->clientMandate->company->name }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <span class="text-sm text-secondary-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            @if($task->assignedUser)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-secondary-700 flex items-center justify-center text-xs font-medium">
                                        {{ $task->assignedUser->initials }}
                                    </div>
                                    {{ $task->assignedUser->full_name }}
                                </div>
                            @else
                                <span class="text-secondary-500">Non assigné</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($task->due_date)
                                <div class="text-sm">
                                    <div class="text-white">{{ $task->due_date->format('d/m/Y') }}</div>
                                    @if($task->isOverdue())
                                        <div class="text-xs text-danger-400">
                                            En retard de {{ abs($task->days_until_due) }} jour(s)
                                        </div>
                                    @elseif($task->days_until_due !== null && $task->days_until_due <= 7 && $task->days_until_due >= 0)
                                        <div class="text-xs text-warning-400">
                                            Dans {{ $task->days_until_due }} jour(s)
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-secondary-500">Aucune</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($task->priority)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->priority_color }}-500/20 text-{{ $task->priority_color }}-400">
                                    {{ $task->priority_label }}
                                </span>
                            @else
                                <span class="text-sm text-secondary-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->status_color }}-500/20 text-{{ $task->status_color }}-400">
                                {{ $task->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('firm.tasks.show', $task) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('firm.tasks.edit', $task) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if(!$task->isCompleted())
                                    <form action="{{ route('firm.tasks.status', $task) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-success-400" title="Marquer comme terminé">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <p class="text-lg font-medium">Aucune tâche trouvée</p>
                                <p class="mt-1">Commencez par ajouter votre première tâche</p>
                                <a href="{{ route('firm.tasks.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nouvelle tâche
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    @endif
</x-firm-layout>
