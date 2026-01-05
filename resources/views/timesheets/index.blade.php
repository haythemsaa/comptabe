@extends('layouts.app')

@section('title', 'Feuilles de temps')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Feuilles de temps</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Gérez et suivez le temps passé sur vos projets</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('timesheets.week') }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Vue semaine
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-primary-100 dark:bg-primary-900/30">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Total heures</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['total_hours'], 1) }}h</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-success-100 dark:bg-success-900/30">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Cette semaine</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['this_week'], 1) }}h</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-info-100 dark:bg-info-900/30">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Ce mois</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['this_month'], 1) }}h</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-warning-100 dark:bg-warning-900/30">
                    <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">En attente</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['pending_approval'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('timesheets.index') }}" class="flex flex-wrap gap-4">
            <div class="w-40">
                <select name="project_id" class="input w-full">
                    <option value="">Tous les projets</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->reference ?? $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <select name="status" class="input w-full">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Timesheet::STATUSES as $key => $config)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <input type="date" name="from_date" value="{{ request('from_date') }}" placeholder="Du" class="input w-full">
            </div>
            <div class="w-36">
                <input type="date" name="to_date" value="{{ request('to_date') }}" placeholder="Au" class="input w-full">
            </div>
            <div class="w-36">
                <select name="billable" class="input w-full">
                    <option value="">Facturable?</option>
                    <option value="1" {{ request('billable') === '1' ? 'selected' : '' }}>Oui</option>
                    <option value="0" {{ request('billable') === '0' ? 'selected' : '' }}>Non</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['project_id', 'status', 'from_date', 'to_date', 'billable']))
                <a href="{{ route('timesheets.index') }}" class="btn btn-outline">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Timesheets List -->
    <div class="card overflow-hidden">
        @if($timesheets->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-dark-200 border-b border-secondary-200 dark:border-secondary-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Projet / Tâche</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Heures</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Montant</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($timesheets as $timesheet)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-200 transition">
                                <td class="px-4 py-3 text-sm text-secondary-900 dark:text-white">
                                    {{ $timesheet->date->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $timesheet->user->full_name }}
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <a href="{{ route('projects.show', $timesheet->project_id) }}" class="font-medium text-secondary-900 dark:text-white hover:text-primary-600">
                                            {{ $timesheet->project->name }}
                                        </a>
                                        @if($timesheet->task)
                                            <p class="text-xs text-secondary-500">{{ $timesheet->task->title }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400 max-w-xs truncate">
                                    {{ $timesheet->description ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-medium text-secondary-900 dark:text-white">{{ number_format($timesheet->hours, 2) }}h</span>
                                    @if($timesheet->billable)
                                        <span class="ml-1 text-xs text-success-600">€</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if($timesheet->amount)
                                        {{ number_format($timesheet->amount, 2) }} €
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-{{ $timesheet->status_color }}">{{ $timesheet->status_label }}</span>
                                    @if($timesheet->invoiced)
                                        <span class="badge badge-sm badge-info ml-1">Facturé</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($timesheet->status === 'draft')
                                        <form action="{{ route('timesheets.destroy', $timesheet) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette entrée ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger-600 hover:text-danger-800 text-sm">
                                                Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($timesheets->hasPages())
                <div class="px-4 py-3 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $timesheets->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-secondary-900 dark:text-white">Aucune entrée de temps</h3>
                <p class="mt-1 text-sm text-secondary-500">Commencez par enregistrer du temps sur un projet.</p>
                <div class="mt-6">
                    <a href="{{ route('timesheets.week') }}" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter du temps
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
