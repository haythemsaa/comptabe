<x-firm-layout>
    <x-slot name="title">Tableau de bord</x-slot>
    <x-slot name="header">Tableau de bord Cabinet</x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Clients -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <a href="{{ route('firm.clients.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">Voir tout</a>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ $stats['total_clients'] }}</h3>
            <p class="text-secondary-400 text-sm">Clients actifs</p>
        </div>

        <!-- Pending Tasks -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-warning-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <a href="{{ route('firm.tasks.index') }}" class="text-warning-400 hover:text-warning-300 text-sm">Voir tout</a>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ $stats['pending_tasks'] }}</h3>
            <p class="text-secondary-400 text-sm">Taches en cours</p>
        </div>

        <!-- Overdue Tasks -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-danger-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <a href="{{ route('firm.tasks.index', ['status' => 'overdue']) }}" class="text-danger-400 hover:text-danger-300 text-sm">Voir tout</a>
            </div>
            <h3 class="text-3xl font-bold mb-1 {{ $stats['overdue_tasks'] > 0 ? 'text-danger-400' : '' }}">{{ $stats['overdue_tasks'] }}</h3>
            <p class="text-secondary-400 text-sm">Taches en retard</p>
        </div>

        <!-- Unread Messages -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-success-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-3xl font-bold mb-1">{{ $stats['unread_messages'] }}</h3>
            <p class="text-secondary-400 text-sm">Messages non lus</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Tasks -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="p-6 border-b border-secondary-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Prochaines echeances</h2>
                <a href="{{ route('firm.tasks.index') }}" class="text-sm text-primary-400 hover:text-primary-300">Voir tout</a>
            </div>
            <div class="divide-y divide-secondary-700">
                @forelse($upcomingTasks as $task)
                    <a href="{{ route('firm.tasks.show', $task) }}" class="flex items-center gap-4 p-4 hover:bg-secondary-700/50 transition-colors">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-medium
                            {{ $task->isOverdue() ? 'bg-danger-500/20 text-danger-400' : ($task->days_until_due <= 3 ? 'bg-warning-500/20 text-warning-400' : 'bg-secondary-700 text-secondary-300') }}">
                            @if($task->due_date)
                                {{ $task->due_date->format('d') }}
                            @else
                                --
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate">{{ $task->title }}</p>
                            <p class="text-sm text-secondary-400 truncate">{{ $task->clientMandate->company->name ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $task->priority_color }}-500/20 text-{{ $task->priority_color }}-400">
                                {{ $task->priority_label }}
                            </span>
                            @if($task->due_date)
                                <p class="text-xs text-secondary-500 mt-1">{{ $task->due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-secondary-500">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p>Aucune tache a venir</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Clients -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="p-6 border-b border-secondary-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Clients recents</h2>
                <a href="{{ route('firm.clients.create') }}" class="text-sm text-primary-400 hover:text-primary-300 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter
                </a>
            </div>
            <div class="divide-y divide-secondary-700">
                @forelse($recentClients as $mandate)
                    <a href="{{ route('firm.clients.show', $mandate) }}" class="flex items-center gap-4 p-4 hover:bg-secondary-700/50 transition-colors">
                        <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center font-bold text-primary-400">
                            {{ strtoupper(substr($mandate->company->name ?? 'N', 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate">{{ $mandate->company->name ?? 'N/A' }}</p>
                            <p class="text-sm text-secondary-400">{{ $mandate->type_label }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mandate->status_color }}-500/20 text-{{ $mandate->status_color }}-400">
                                {{ $mandate->status_label }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-secondary-500">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p>Aucun client pour le moment</p>
                        <a href="{{ route('firm.clients.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter un client
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="mt-6 bg-secondary-800 rounded-xl border border-secondary-700">
        <div class="p-6 border-b border-secondary-700">
            <h2 class="text-lg font-semibold">Activite recente</h2>
        </div>
        <div class="divide-y divide-secondary-700">
            @forelse($recentActivities as $activity)
                <div class="flex items-start gap-4 p-4">
                    <div class="w-8 h-8 bg-secondary-700 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm">
                            <span class="font-medium">{{ $activity->user->full_name ?? 'Systeme' }}</span>
                            <span class="text-secondary-400">{{ $activity->description ?? $activity->type_label }}</span>
                        </p>
                        <p class="text-xs text-secondary-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-secondary-500">
                    <p>Aucune activite recente</p>
                </div>
            @endforelse
        </div>
    </div>
</x-firm-layout>
