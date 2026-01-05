<x-app-layout>
    <x-slot name="title">Activités CRM</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('crm.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">CRM</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Activités</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Activités</h1>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $activities->total() }} activité(s)</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('crm.activities.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="form-input">
                    </div>
                    <select name="type" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Tous les types</option>
                        @foreach(\App\Models\Activity::TYPES as $type => $config)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $config['label'] }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                    </select>
                    <select name="assigned_to" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="">Tous les responsables</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary">Filtrer</button>
                    @if(request()->hasAny(['search', 'type', 'status', 'assigned_to']))
                    <a href="{{ route('crm.activities.index') }}" class="text-sm text-secondary-500 hover:text-secondary-700">Réinitialiser</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Aujourd'hui</p>
                            <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ $stats['due_today'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">En retard</p>
                            <p class="text-xl font-bold text-danger-600">{{ $stats['overdue'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">En attente</p>
                            <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ $stats['pending'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Terminées (auj.)</p>
                            <p class="text-xl font-bold text-success-600">{{ $stats['completed_today'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="card">
            <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                @forelse($activities as $activity)
                <div class="p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 {{ $activity->isOverdue() ? 'bg-danger-50 dark:bg-danger-900/10' : '' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                            @switch($activity->type)
                                @case('call') bg-blue-100 dark:bg-blue-900/30 @break
                                @case('email') bg-purple-100 dark:bg-purple-900/30 @break
                                @case('meeting') bg-green-100 dark:bg-green-900/30 @break
                                @case('note') bg-yellow-100 dark:bg-yellow-900/30 @break
                                @case('task') bg-orange-100 dark:bg-orange-900/30 @break
                                @case('demo') bg-pink-100 dark:bg-pink-900/30 @break
                                @default bg-secondary-100 dark:bg-secondary-700 @break
                            @endswitch">
                            @switch($activity->type)
                                @case('call')
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                @break
                                @case('email')
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                @break
                                @case('meeting')
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @break
                                @case('note')
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                @break
                                @case('task')
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                @break
                                @case('demo')
                                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                @break
                                @default
                                <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endswitch
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-medium text-secondary-900 dark:text-white {{ $activity->is_completed ? 'line-through text-secondary-500' : '' }}">
                                        {{ $activity->subject }}
                                    </h3>
                                    @if($activity->related)
                                    <p class="text-sm text-secondary-500">
                                        @if($activity->related_type === 'App\Models\Opportunity')
                                        <a href="{{ route('crm.opportunities.show', $activity->related_id) }}" class="text-primary-600 hover:underline">
                                            {{ $activity->related->title }}
                                        </a>
                                        @elseif($activity->related_type === 'App\Models\Partner')
                                        <a href="{{ route('partners.show', $activity->related_id) }}" class="text-primary-600 hover:underline">
                                            {{ $activity->related->name }}
                                        </a>
                                        @endif
                                    </p>
                                    @endif
                                    @if($activity->description)
                                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400 line-clamp-2">{{ $activity->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Priority Badge -->
                                    @if($activity->priority !== 'medium')
                                    <span class="px-2 py-0.5 text-xs rounded-full
                                        @if($activity->priority === 'urgent') bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300
                                        @elseif($activity->priority === 'high') bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-300
                                        @else bg-secondary-100 text-secondary-700 dark:bg-secondary-700 dark:text-secondary-300
                                        @endif">
                                        {{ \App\Models\Activity::PRIORITIES[$activity->priority]['label'] }}
                                    </span>
                                    @endif

                                    <!-- Status Toggle -->
                                    <form action="{{ route('crm.activities.toggle', $activity) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1 rounded hover:bg-secondary-100 dark:hover:bg-secondary-700" title="{{ $activity->is_completed ? 'Marquer en attente' : 'Marquer terminé' }}">
                                            @if($activity->is_completed)
                                            <svg class="w-5 h-5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            @else
                                            <svg class="w-5 h-5 text-secondary-400 hover:text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('crm.activities.destroy', $activity) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette activité ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-secondary-400 hover:text-danger-500 hover:bg-secondary-100 dark:hover:bg-secondary-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-secondary-500">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ \App\Models\Activity::TYPES[$activity->type]['label'] }}
                                </span>
                                @if($activity->due_date)
                                <span class="inline-flex items-center gap-1 {{ $activity->isOverdue() ? 'text-danger-600 font-medium' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $activity->due_date->format('d/m/Y H:i') }}
                                    @if($activity->isOverdue())
                                    <span class="text-danger-600">(en retard)</span>
                                    @endif
                                </span>
                                @endif
                                @if($activity->assignedTo)
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $activity->assignedTo->full_name }}
                                </span>
                                @endif
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-secondary-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Aucune activité trouvée
                </div>
                @endforelse
            </div>
            @if($activities->hasPages())
            <div class="px-4 py-3 border-t border-secondary-100 dark:border-secondary-700">
                {{ $activities->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
