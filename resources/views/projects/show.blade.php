@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($project->color)
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $project->color }}"></div>
            @endif
            <div>
                <nav class="flex items-center gap-2 text-sm mb-1">
                    <a href="{{ route('projects.index') }}" class="text-secondary-500 hover:text-primary-600">Projets</a>
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-secondary-900 dark:text-white">{{ $project->reference ?? $project->name }}</span>
                </nav>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $project->name }}</h1>
                @if($project->partner)
                    <p class="text-secondary-600 dark:text-secondary-400">Client: {{ $project->partner->name }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge badge-{{ $project->status_color }}">{{ $project->status_label }}</span>
            <a href="{{ route('projects.kanban', $project) }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                Kanban
            </a>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary">Modifier</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-sm text-secondary-600 dark:text-secondary-400">Progression</p>
            <div class="flex items-center gap-3 mt-2">
                <div class="flex-1 h-2 bg-secondary-200 dark:bg-secondary-700 rounded-full overflow-hidden">
                    <div class="h-full bg-primary-500 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                </div>
                <span class="text-lg font-bold text-secondary-900 dark:text-white">{{ $project->progress_percent }}%</span>
            </div>
        </div>

        <div class="card p-4">
            <p class="text-sm text-secondary-600 dark:text-secondary-400">Tâches</p>
            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                {{ $taskStats['done'] ?? 0 }} / {{ array_sum($taskStats) }}
            </p>
            <p class="text-xs text-secondary-500">terminées</p>
        </div>

        <div class="card p-4">
            <p class="text-sm text-secondary-600 dark:text-secondary-400">Heures</p>
            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">{{ number_format($timeStats['total_hours'], 1) }}h</p>
            @if($project->estimated_hours)
                <p class="text-xs text-secondary-500">sur {{ $project->estimated_hours }}h estimées</p>
            @endif
        </div>

        <div class="card p-4">
            <p class="text-sm text-secondary-600 dark:text-secondary-400">Budget</p>
            @if($project->budget)
                <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">{{ number_format($project->actual_cost, 2) }} €</p>
                <p class="text-xs text-secondary-500">sur {{ number_format($project->budget, 2) }} € prévus</p>
            @else
                <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">{{ number_format($timeStats['total_amount'] ?? 0, 2) }} €</p>
                <p class="text-xs text-secondary-500">facturable</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            @if($project->description)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-3">Description</h2>
                    <p class="text-secondary-600 dark:text-secondary-400 whitespace-pre-line">{{ $project->description }}</p>
                </div>
            @endif

            <!-- Tasks Section -->
            <div class="card">
                <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Tâches</h2>
                    <button onclick="document.getElementById('newTaskModal').classList.remove('hidden')" class="btn btn-sm btn-primary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvelle tâche
                    </button>
                </div>

                @if($project->tasks->count() > 0)
                    <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($project->tasks->sortBy('sort_order') as $task)
                            <div class="p-4 hover:bg-secondary-50 dark:hover:bg-dark-200 transition">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox"
                                        {{ $task->status === 'done' ? 'checked' : '' }}
                                        onchange="updateTaskStatus('{{ $task->id }}', this.checked ? 'done' : 'todo')"
                                        class="mt-1 w-4 h-4 text-primary-600 rounded border-secondary-300 focus:ring-primary-500">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-medium text-secondary-900 dark:text-white {{ $task->status === 'done' ? 'line-through text-secondary-500' : '' }}">
                                                {{ $task->title }}
                                            </h3>
                                            <span class="badge badge-sm badge-{{ $task->status_color }}">{{ $task->status_label }}</span>
                                            @if($task->priority !== 'medium')
                                                <span class="badge badge-sm badge-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                                            @endif
                                        </div>
                                        @if($task->description)
                                            <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1 line-clamp-2">{{ $task->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 mt-2 text-xs text-secondary-500">
                                            @if($task->assignee)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $task->assignee->full_name }}
                                                </span>
                                            @endif
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1 {{ $task->is_overdue ? 'text-danger-600' : '' }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $task->due_date->format('d/m/Y') }}
                                                </span>
                                            @endif
                                            @if($task->actual_hours > 0)
                                                <span>{{ number_format($task->actual_hours, 1) }}h passées</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-secondary-500">
                        <p>Aucune tâche pour ce projet.</p>
                        <button onclick="document.getElementById('newTaskModal').classList.remove('hidden')" class="text-primary-600 hover:underline mt-2">
                            Créer la première tâche
                        </button>
                    </div>
                @endif
            </div>

            <!-- Recent Timesheets -->
            <div class="card">
                <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Temps récents</h2>
                    <a href="{{ route('timesheets.index', ['project_id' => $project->id]) }}" class="text-sm text-primary-600 hover:underline">
                        Voir tout
                    </a>
                </div>

                @if($project->timesheets->count() > 0)
                    <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($project->timesheets as $timesheet)
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-secondary-900 dark:text-white">{{ $timesheet->user->full_name }}</p>
                                        <p class="text-sm text-secondary-500">{{ $timesheet->date->format('d/m/Y') }}</p>
                                        @if($timesheet->description)
                                            <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">{{ $timesheet->description }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-secondary-900 dark:text-white">{{ number_format($timesheet->hours, 1) }}h</p>
                                        @if($timesheet->amount)
                                            <p class="text-sm text-secondary-500">{{ number_format($timesheet->amount, 2) }} €</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-secondary-500">
                        <p>Aucun temps enregistré.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Project Details -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Détails</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-secondary-500">Priorité</dt>
                        <dd><span class="badge badge-{{ $project->priority_color }}">{{ $project->priority_label }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-500">Type de facturation</dt>
                        <dd class="text-secondary-900 dark:text-white">{{ $project->billing_type_label }}</dd>
                    </div>
                    @if($project->hourly_rate)
                        <div>
                            <dt class="text-sm text-secondary-500">Taux horaire</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ number_format($project->hourly_rate, 2) }} €/h</dd>
                        </div>
                    @endif
                    @if($project->start_date)
                        <div>
                            <dt class="text-sm text-secondary-500">Date de début</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $project->start_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($project->end_date)
                        <div>
                            <dt class="text-sm text-secondary-500">Date de fin</dt>
                            <dd class="text-secondary-900 dark:text-white">{{ $project->end_date->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm text-secondary-500">Créé le</dt>
                        <dd class="text-secondary-900 dark:text-white">{{ $project->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Team -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Équipe</h2>
                @if($project->manager)
                    <div class="flex items-center gap-3 p-2 rounded-lg bg-primary-50 dark:bg-primary-900/20 mb-3">
                        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white font-medium text-sm">
                            {{ strtoupper(substr($project->manager->first_name, 0, 1) . substr($project->manager->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-secondary-900 dark:text-white text-sm">{{ $project->manager->full_name }}</p>
                            <p class="text-xs text-primary-600 dark:text-primary-400">Chef de projet</p>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                    @foreach($project->members as $member)
                        @if(!$project->manager || $member->id !== $project->manager->id)
                            <div class="flex items-center gap-3 p-2">
                                <div class="w-8 h-8 rounded-full bg-secondary-300 dark:bg-secondary-600 flex items-center justify-center text-secondary-700 dark:text-secondary-200 font-medium text-sm">
                                    {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-secondary-900 dark:text-white text-sm">{{ $member->full_name }}</p>
                                    <p class="text-xs text-secondary-500">{{ ucfirst($member->pivot->role) }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if($project->members->isEmpty() && !$project->manager)
                    <p class="text-sm text-secondary-500 text-center py-4">Aucun membre assigné</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Actions rapides</h2>
                <div class="space-y-2">
                    <a href="{{ route('timesheets.week', ['project_id' => $project->id]) }}" class="btn btn-secondary w-full justify-start">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ajouter du temps
                    </a>
                    <a href="{{ route('projects.kanban', $project) }}" class="btn btn-secondary w-full justify-start">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Tableau Kanban
                    </a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Supprimer ce projet ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer le projet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Task Modal -->
<div id="newTaskModal" class="fixed inset-0 z-50 hidden" x-data="{ show: true }">
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('newTaskModal').classList.add('hidden')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-dark-100 rounded-xl shadow-xl w-full max-w-lg relative">
            <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
                @csrf
                <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Nouvelle tâche</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="label">Titre <span class="text-danger-500">*</span></label>
                        <input type="text" name="title" required class="input">
                    </div>
                    <div>
                        <label class="label">Description</label>
                        <textarea name="description" rows="2" class="input"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Statut</label>
                            <select name="status" class="input">
                                @foreach(\App\Models\ProjectTask::STATUSES as $key => $config)
                                    <option value="{{ $key }}" {{ $key === 'todo' ? 'selected' : '' }}>{{ $config['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Priorité</label>
                            <select name="priority" class="input">
                                @foreach(\App\Models\ProjectTask::PRIORITIES as $key => $config)
                                    <option value="{{ $key }}" {{ $key === 'medium' ? 'selected' : '' }}>{{ $config['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Date d'échéance</label>
                            <input type="date" name="due_date" class="input">
                        </div>
                        <div>
                            <label class="label">Assigné à</label>
                            <select name="assigned_to" class="input">
                                <option value="">-- Sélectionner --</option>
                                @foreach($project->members as $member)
                                    <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="label">Heures estimées</label>
                        <input type="number" name="estimated_hours" min="0" class="input" placeholder="4">
                    </div>
                </div>
                <div class="p-6 border-t border-secondary-200 dark:border-secondary-700 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('newTaskModal').classList.add('hidden')" class="btn btn-secondary">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer la tâche</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateTaskStatus(taskId, status) {
    fetch(`{{ route('projects.tasks.update', [$project->id, '']) }}/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    }).then(response => {
        if (response.ok) {
            window.location.reload();
        }
    });
}
</script>
@endpush
@endsection
