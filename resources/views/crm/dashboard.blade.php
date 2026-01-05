<x-app-layout>
    <x-slot name="title">Dashboard CRM</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Dashboard CRM</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Dashboard CRM</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Vue d'ensemble de vos opportunités commerciales</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('crm.pipeline') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    Pipeline
                </a>
                <a href="{{ route('crm.opportunities.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle Opportunité
                </a>
            </div>
        </div>

        <!-- Stats Row 1: Pipeline -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500">Opportunités ouvertes</p>
                        <p class="text-3xl font-bold text-secondary-900 dark:text-white">{{ $pipelineStats['total_open'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500">Valeur pipeline</p>
                        <p class="text-3xl font-bold text-secondary-900 dark:text-white">{{ number_format($pipelineStats['total_amount'], 0, ',', ' ') }} <span class="text-lg">EUR</span></p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500">Gagnées ce mois</p>
                        <p class="text-3xl font-bold text-success-600">{{ number_format($wonThisMonth, 0, ',', ' ') }} <span class="text-lg">EUR</span></p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500">Perdues ce mois</p>
                        <p class="text-3xl font-bold text-danger-600">{{ number_format($lostThisMonth, 0, ',', ' ') }} <span class="text-lg">EUR</span></p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 dark:bg-danger-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row 2: Activities -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 border-l-4 border-warning-500">
                <p class="text-sm text-secondary-500">Activités en attente</p>
                <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $activityStats['pending'] }}</p>
            </div>
            <div class="card p-4 border-l-4 border-danger-500">
                <p class="text-sm text-secondary-500">En retard</p>
                <p class="text-2xl font-bold {{ $activityStats['overdue'] > 0 ? 'text-danger-600' : 'text-secondary-400' }}">{{ $activityStats['overdue'] }}</p>
            </div>
            <div class="card p-4 border-l-4 border-primary-500">
                <p class="text-sm text-secondary-500">Aujourd'hui</p>
                <p class="text-2xl font-bold text-primary-600">{{ $activityStats['due_today'] }}</p>
            </div>
            <div class="card p-4 border-l-4 border-success-500">
                <p class="text-sm text-secondary-500">Terminées aujourd'hui</p>
                <p class="text-2xl font-bold text-success-600">{{ $activityStats['completed_today'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today's Activities -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Activités du jour</h3>
                    <a href="{{ route('crm.activities.index') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
                </div>
                <div class="card-body">
                    @forelse($todaysActivities as $activity)
                    <div class="flex items-center gap-4 py-3 {{ !$loop->last ? 'border-b border-secondary-100 dark:border-secondary-700' : '' }}">
                        <div class="w-10 h-10 bg-{{ $activity->getTypeColor() }}-100 dark:bg-{{ $activity->getTypeColor() }}-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-{{ $activity->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($activity->type === 'call')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                @elseif($activity->type === 'email')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                @elseif($activity->type === 'meeting')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-secondary-900 dark:text-white truncate">{{ $activity->subject }}</p>
                            <p class="text-sm text-secondary-500">{{ $activity->getTypeLabel() }} - {{ $activity->due_date?->format('H:i') }}</p>
                        </div>
                        <button onclick="toggleActivity({{ $activity->id }})" class="btn btn-sm btn-ghost">
                            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-8 text-secondary-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p>Aucune activité prévue aujourd'hui</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue Opportunities -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Opportunités en retard</h3>
                    <a href="{{ route('crm.opportunities.index') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
                </div>
                <div class="card-body">
                    @forelse($overdueOpportunities as $opportunity)
                    <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="flex items-center gap-4 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 -mx-4 px-4 {{ !$loop->last ? 'border-b border-secondary-100 dark:border-secondary-700' : '' }}">
                        <div class="w-10 h-10 bg-danger-100 dark:bg-danger-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-secondary-900 dark:text-white truncate">{{ $opportunity->title }}</p>
                            <p class="text-sm text-secondary-500">{{ $opportunity->partner?->name ?? 'Sans client' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-secondary-900 dark:text-white">{{ number_format($opportunity->amount, 0, ',', ' ') }} EUR</p>
                            <p class="text-xs text-danger-600">{{ $opportunity->expected_close_date->diffForHumans() }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-8 text-secondary-400">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>Aucune opportunité en retard</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Opportunities -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">Opportunités récentes</h3>
                <a href="{{ route('crm.opportunities.index') }}" class="text-sm text-primary-600 hover:underline">Voir tout</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Opportunité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Étape</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Montant</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Probabilité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Clôture prévue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($recentOpportunities as $opportunity)
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="font-medium text-secondary-900 dark:text-white hover:text-primary-600">
                                    {{ $opportunity->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->partner?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $opportunity->getStageColor() }}-100 dark:bg-{{ $opportunity->getStageColor() }}-900/30 text-{{ $opportunity->getStageColor() }}-700 dark:text-{{ $opportunity->getStageColor() }}-300">
                                    {{ $opportunity->getStageLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($opportunity->amount, 0, ',', ' ') }} EUR
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-secondary-600 dark:text-secondary-400">{{ $opportunity->probability }}%</span>
                            </td>
                            <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                {{ $opportunity->expected_close_date?->format('d/m/Y') ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleActivity(id) {
            fetch(`/crm/activities/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
