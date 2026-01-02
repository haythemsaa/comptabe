<x-firm-layout>
    <x-slot name="title">Mes Tâches</x-slot>
    <x-slot name="header">Mes tâches</x-slot>

    <!-- Header with task count -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-secondary-400">{{ $tasks->total() }} tâche(s) assignée(s)</p>
    </div>

    <!-- Tasks Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Échéance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Priorité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($tasks as $task)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <a href="{{ route('firm.tasks.show', $task) }}" class="font-medium text-white hover:text-primary-400">
                                {{ $task->title }}
                            </a>
                            @if($task->description)
                                <p class="text-sm text-secondary-500 truncate mt-1">{{ Str::limit($task->description, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary-500/20 flex items-center justify-center font-bold text-primary-400 text-xs">
                                    {{ strtoupper(substr($task->clientMandate->company->name ?? 'N', 0, 2)) }}
                                </div>
                                <div>
                                    <a href="{{ route('firm.clients.show', $task->clientMandate) }}" class="text-sm text-white hover:text-primary-400">
                                        {{ $task->clientMandate->company->name ?? 'N/A' }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($task->due_date)
                                <div class="flex items-center gap-2">
                                    <span class="{{ $task->isOverdue() ? 'text-danger-400' : ($task->days_until_due <= 3 ? 'text-warning-400' : 'text-secondary-300') }}">
                                        {{ $task->due_date->format('d/m/Y') }}
                                    </span>
                                    @if($task->isOverdue())
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-danger-500/20 text-danger-400">
                                            En retard
                                        </span>
                                    @elseif($task->days_until_due <= 3)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-warning-500/20 text-warning-400">
                                            Urgent
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-secondary-500 mt-1">{{ $task->due_date->diffForHumans() }}</p>
                            @else
                                <span class="text-secondary-500">Non définie</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->priority_color }}-500/20 text-{{ $task->priority_color }}-400">
                                {{ $task->priority_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $task->status_color }}-500/20 text-{{ $task->status_color }}-400">
                                {{ $task->status_label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <p class="text-lg font-medium">Aucune tâche assignée</p>
                                <p class="mt-1">Vous n'avez actuellement aucune tâche à faire</p>
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
