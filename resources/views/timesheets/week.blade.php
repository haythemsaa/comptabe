@extends('layouts.app')

@section('title', 'Timesheet semaine')

@section('content')
<div class="space-y-6" x-data="timesheetWeek()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Feuille de temps</h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Semaine du {{ $startOfWeek->format('d/m/Y') }} au {{ $endOfWeek->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('timesheets.week', ['date' => $startOfWeek->copy()->subWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <a href="{{ route('timesheets.week') }}" class="btn btn-secondary">Aujourd'hui</a>
            <a href="{{ route('timesheets.week', ['date' => $startOfWeek->copy()->addWeek()->format('Y-m-d')]) }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="{{ route('timesheets.index') }}" class="btn btn-outline">Liste complète</a>
        </div>
    </div>

    <!-- Week Overview -->
    <div class="card p-4">
        <div class="flex items-center justify-between">
            <span class="text-secondary-600 dark:text-secondary-400">Total de la semaine</span>
            <span class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($weekTotal, 1) }}h</span>
        </div>
        <div class="mt-2 h-2 bg-secondary-200 dark:bg-secondary-700 rounded-full overflow-hidden">
            <div class="h-full bg-primary-500 rounded-full" style="width: {{ min(($weekTotal / 40) * 100, 100) }}%"></div>
        </div>
        <p class="mt-1 text-xs text-secondary-500">{{ round(($weekTotal / 40) * 100) }}% de 40h</p>
    </div>

    <!-- Week Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
        @foreach($weekDays as $dayKey => $dayData)
            @php
                $isToday = $dayData['date']->isToday();
                $isWeekend = $dayData['date']->isWeekend();
            @endphp
            <div class="card {{ $isToday ? 'ring-2 ring-primary-500' : '' }} {{ $isWeekend ? 'bg-secondary-50 dark:bg-dark-200' : '' }}">
                <div class="p-3 border-b border-secondary-200 dark:border-secondary-700 {{ $isToday ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-secondary-500 uppercase">{{ $dayData['date']->translatedFormat('l') }}</p>
                            <p class="font-semibold text-secondary-900 dark:text-white {{ $isToday ? 'text-primary-600 dark:text-primary-400' : '' }}">
                                {{ $dayData['date']->format('d/m') }}
                            </p>
                        </div>
                        <span class="text-lg font-bold {{ $dayData['total'] > 0 ? 'text-secondary-900 dark:text-white' : 'text-secondary-400' }}">
                            {{ number_format($dayData['total'], 1) }}h
                        </span>
                    </div>
                </div>

                <div class="p-3 space-y-2 min-h-[150px]">
                    @foreach($dayData['entries'] as $entry)
                        <div class="p-2 rounded-lg bg-secondary-100 dark:bg-dark-100 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-secondary-900 dark:text-white truncate">{{ $entry->project->name }}</span>
                                <span class="text-secondary-600 dark:text-secondary-400">{{ number_format($entry->hours, 1) }}h</span>
                            </div>
                            @if($entry->description)
                                <p class="text-xs text-secondary-500 truncate mt-1">{{ $entry->description }}</p>
                            @endif
                        </div>
                    @endforeach

                    <button @click="openAddModal('{{ $dayKey }}')" class="w-full p-2 border-2 border-dashed border-secondary-300 dark:border-secondary-600 rounded-lg text-secondary-500 hover:border-primary-500 hover:text-primary-600 transition text-sm flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Time Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="showAddModal = false">
        <div class="absolute inset-0 bg-black/50" @click="showAddModal = false"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-dark-100 rounded-xl shadow-xl w-full max-w-md" @click.stop>
                <form action="{{ route('timesheets.store') }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    <input type="hidden" name="date" x-model="selectedDate">

                    <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Ajouter du temps</h3>
                        <p class="text-sm text-secondary-500" x-text="formatDate(selectedDate)"></p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="label">Projet <span class="text-danger-500">*</span></label>
                            <select name="project_id" x-model="selectedProject" @change="loadTasks()" required class="input">
                                <option value="">-- Sélectionner un projet --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->reference ?? $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="label">Tâche</label>
                            <select name="task_id" x-model="selectedTask" class="input" :disabled="!selectedProject">
                                <option value="">-- Sélectionner une tâche (optionnel) --</option>
                                <template x-for="task in tasks" :key="task.id">
                                    <option :value="task.id" x-text="task.title"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="label">Heures <span class="text-danger-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="hours = Math.max(0.25, hours - 0.25)" class="btn btn-secondary btn-sm">-</button>
                                <input type="number" name="hours" x-model="hours" step="0.25" min="0.25" max="24" required class="input text-center flex-1">
                                <button type="button" @click="hours = Math.min(24, hours + 0.25)" class="btn btn-secondary btn-sm">+</button>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" @click="hours = 0.5" class="btn btn-outline btn-sm">30min</button>
                                <button type="button" @click="hours = 1" class="btn btn-outline btn-sm">1h</button>
                                <button type="button" @click="hours = 2" class="btn btn-outline btn-sm">2h</button>
                                <button type="button" @click="hours = 4" class="btn btn-outline btn-sm">4h</button>
                                <button type="button" @click="hours = 8" class="btn btn-outline btn-sm">8h</button>
                            </div>
                        </div>

                        <div>
                            <label class="label">Description</label>
                            <textarea name="description" rows="2" class="input" placeholder="Ce que vous avez fait..."></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="billable" value="1" checked class="w-4 h-4 text-primary-600 rounded border-secondary-300 focus:ring-primary-500">
                            <label class="text-sm text-secondary-700 dark:text-secondary-300">Temps facturable</label>
                        </div>
                    </div>

                    <div class="p-6 border-t border-secondary-200 dark:border-secondary-700 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            <span x-show="loading" class="animate-spin mr-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function timesheetWeek() {
    return {
        showAddModal: false,
        selectedDate: '',
        selectedProject: '',
        selectedTask: '',
        tasks: [],
        hours: 1,
        loading: false,

        openAddModal(date) {
            this.selectedDate = date;
            this.selectedProject = '';
            this.selectedTask = '';
            this.tasks = [];
            this.hours = 1;
            this.showAddModal = true;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },

        async loadTasks() {
            if (!this.selectedProject) {
                this.tasks = [];
                return;
            }

            try {
                const response = await fetch(`{{ url('/timesheets/projects') }}/${this.selectedProject}/tasks`);
                this.tasks = await response.json();
            } catch (error) {
                console.error('Error loading tasks:', error);
                this.tasks = [];
            }
        },

        handleSubmit(event) {
            this.loading = true;
        }
    }
}
</script>
@endpush
@endsection
