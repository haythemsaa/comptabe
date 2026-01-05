@extends('layouts.app')

@section('title', 'Kanban - ' . $project->name)

@push('styles')
<style>
    .kanban-column {
        min-height: 400px;
    }
    .kanban-card {
        cursor: grab;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card.dragging {
        opacity: 0.5;
    }
    .kanban-column.drag-over {
        background-color: rgba(59, 130, 246, 0.1);
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="kanbanBoard()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm mb-1">
                <a href="{{ route('projects.index') }}" class="text-secondary-500 hover:text-primary-600">Projets</a>
                <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('projects.show', $project) }}" class="text-secondary-500 hover:text-primary-600">{{ $project->reference ?? $project->name }}</a>
                <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-secondary-900 dark:text-white">Kanban</span>
            </nav>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Vue détails
            </a>
            <button @click="showAddTaskModal = true; newTaskStatus = 'todo'" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle tâche
            </button>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach(\App\Models\ProjectTask::STATUSES as $status => $config)
            <div class="kanban-column flex-shrink-0 w-72 bg-secondary-100 dark:bg-dark-200 rounded-xl p-4"
                 data-status="{{ $status }}"
                 @dragover.prevent="onDragOver($event)"
                 @dragleave="onDragLeave($event)"
                 @drop="onDrop($event, '{{ $status }}')">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-{{ $config['color'] }}-500"></span>
                        {{ $config['label'] }}
                        <span class="text-sm font-normal text-secondary-500">({{ $tasksByStatus[$status]->count() }})</span>
                    </h3>
                    <button @click="showAddTaskModal = true; newTaskStatus = '{{ $status }}'" class="text-secondary-500 hover:text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 min-h-[200px]" data-status="{{ $status }}">
                    @foreach($tasksByStatus[$status] as $task)
                        <div class="kanban-card bg-white dark:bg-dark-100 rounded-lg p-4 shadow-sm border border-secondary-200 dark:border-secondary-700 hover:shadow-md transition"
                             draggable="true"
                             data-task-id="{{ $task->id }}"
                             @dragstart="onDragStart($event, '{{ $task->id }}')"
                             @dragend="onDragEnd($event)">

                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h4 class="font-medium text-secondary-900 dark:text-white text-sm">{{ $task->title }}</h4>
                                @if($task->priority !== 'medium')
                                    <span class="badge badge-sm badge-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                                @endif
                            </div>

                            @if($task->description)
                                <p class="text-xs text-secondary-600 dark:text-secondary-400 mb-3 line-clamp-2">{{ $task->description }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-secondary-500">
                                <div class="flex items-center gap-2">
                                    @if($task->assignee)
                                        <div class="flex items-center gap-1" title="{{ $task->assignee->full_name }}">
                                            <div class="w-5 h-5 rounded-full bg-primary-500 flex items-center justify-center text-white text-[10px] font-medium">
                                                {{ strtoupper(substr($task->assignee->first_name, 0, 1)) }}
                                            </div>
                                        </div>
                                    @endif
                                    @if($task->actual_hours > 0)
                                        <span>{{ number_format($task->actual_hours, 1) }}h</span>
                                    @endif
                                </div>
                                @if($task->due_date)
                                    <span class="{{ $task->is_overdue ? 'text-danger-600 font-medium' : '' }}">
                                        {{ $task->due_date->format('d/m') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Task Modal -->
    <div x-show="showAddTaskModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="showAddTaskModal = false">
        <div class="absolute inset-0 bg-black/50" @click="showAddTaskModal = false"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-dark-100 rounded-xl shadow-xl w-full max-w-lg" @click.stop>
                <form action="{{ route('projects.tasks.store', $project) }}" method="POST">
                    @csrf
                    <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Nouvelle tâche</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="label">Titre <span class="text-danger-500">*</span></label>
                            <input type="text" name="title" required class="input" autofocus>
                        </div>
                        <div>
                            <label class="label">Description</label>
                            <textarea name="description" rows="2" class="input"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Statut</label>
                                <select name="status" x-model="newTaskStatus" class="input">
                                    @foreach(\App\Models\ProjectTask::STATUSES as $key => $config)
                                        <option value="{{ $key }}">{{ $config['label'] }}</option>
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
                        <button type="button" @click="showAddTaskModal = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer la tâche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function kanbanBoard() {
    return {
        showAddTaskModal: false,
        newTaskStatus: 'todo',
        draggedTaskId: null,

        onDragStart(event, taskId) {
            this.draggedTaskId = taskId;
            event.target.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', taskId);
        },

        onDragEnd(event) {
            event.target.classList.remove('dragging');
            document.querySelectorAll('.kanban-column').forEach(col => {
                col.classList.remove('drag-over');
            });
        },

        onDragOver(event) {
            event.preventDefault();
            event.currentTarget.classList.add('drag-over');
        },

        onDragLeave(event) {
            event.currentTarget.classList.remove('drag-over');
        },

        onDrop(event, newStatus) {
            event.preventDefault();
            event.currentTarget.classList.remove('drag-over');

            const taskId = event.dataTransfer.getData('text/plain');
            if (!taskId) return;

            // Optimistic UI update
            const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
            const targetColumn = event.currentTarget.querySelector(`[data-status="${newStatus}"]`);

            if (taskCard && targetColumn) {
                targetColumn.appendChild(taskCard);
            }

            // Send update to server
            fetch(`{{ route('projects.tasks.update', [$project->id, '']) }}/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus,
                    sort_order: Array.from(targetColumn.children).indexOf(taskCard)
                })
            }).then(response => {
                if (!response.ok) {
                    // Revert on error
                    window.location.reload();
                }
            }).catch(() => {
                window.location.reload();
            });
        }
    }
}
</script>
@endpush
@endsection
