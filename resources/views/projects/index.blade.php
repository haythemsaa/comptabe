@extends('layouts.app')

@section('title', 'Projets')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Gestion de Projets</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Gérez vos projets et suivez leur avancement</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau Projet
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-primary-100 dark:bg-primary-900/30">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Total Projets</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-success-100 dark:bg-success-900/30">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">En cours</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-info-100 dark:bg-info-900/30">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Terminés</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-warning-100 dark:bg-warning-900/30">
                    <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Budget Total</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['total_budget'] ?? 0, 2) }} €</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un projet..." class="input w-full">
            </div>
            <div class="w-40">
                <select name="status" class="input w-full">
                    <option value="">Tous les statuts</option>
                    @foreach(\App\Models\Project::STATUSES as $key => $config)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <select name="partner_id" class="input w-full">
                    <option value="">Tous les clients</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'partner_id']))
                <a href="{{ route('projects.index') }}" class="btn btn-outline">Effacer</a>
            @endif
        </form>
    </div>

    <!-- Projects List -->
    <div class="card overflow-hidden">
        @if($projects->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-dark-200 border-b border-secondary-200 dark:border-secondary-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Projet</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Progression</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Dates</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">Budget</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($projects as $project)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-dark-200 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($project->color)
                                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $project->color }}"></div>
                                        @endif
                                        <div>
                                            <a href="{{ route('projects.show', $project) }}" class="font-medium text-secondary-900 dark:text-white hover:text-primary-600">
                                                {{ $project->name }}
                                            </a>
                                            @if($project->reference)
                                                <p class="text-xs text-secondary-500">{{ $project->reference }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $project->partner?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-{{ $project->status_color }}">{{ $project->status_label }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 h-2 bg-secondary-200 dark:bg-secondary-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary-500 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                                        </div>
                                        <span class="text-xs text-secondary-600 dark:text-secondary-400">{{ $project->progress_percent }}%</span>
                                    </div>
                                    <p class="text-xs text-secondary-500 mt-1">{{ $project->tasks_count ?? 0 }} tâches</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    @if($project->start_date && $project->end_date)
                                        {{ $project->start_date->format('d/m/Y') }} - {{ $project->end_date->format('d/m/Y') }}
                                    @elseif($project->start_date)
                                        Début: {{ $project->start_date->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($project->budget)
                                        <span class="text-secondary-900 dark:text-white font-medium">{{ number_format($project->budget, 2) }} €</span>
                                        @if($project->actual_cost > 0)
                                            <p class="text-xs text-secondary-500">Réel: {{ number_format($project->actual_cost, 2) }} €</p>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('projects.kanban', $project) }}" class="btn btn-sm btn-outline" title="Tableau Kanban">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-secondary">Voir</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($projects->hasPages())
                <div class="px-4 py-3 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-secondary-900 dark:text-white">Aucun projet</h3>
                <p class="mt-1 text-sm text-secondary-500">Commencez par créer votre premier projet.</p>
                <div class="mt-6">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer un projet
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
