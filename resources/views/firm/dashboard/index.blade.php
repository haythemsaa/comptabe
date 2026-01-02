@extends('layouts.app')

@section('title', 'Dashboard Cabinet - ' . $firm->name)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900" x-data="firmDashboard()">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        📊 Dashboard Cabinet
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $firm->name }} • {{ $mandates->count() }} clients actifs
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 gap-2">
                    <!-- Period Filter -->
                    <select x-model="selectedPeriod"
                            @change="window.location.href = '?period=' + selectedPeriod"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="current_month" {{ $period === 'current_month' ? 'selected' : '' }}>Mois en cours</option>
                        <option value="current_quarter" {{ $period === 'current_quarter' ? 'selected' : '' }}>Trimestre en cours</option>
                        <option value="current_year" {{ $period === 'current_year' ? 'selected' : '' }}>Année en cours</option>
                        <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Mois dernier</option>
                        <option value="last_quarter" {{ $period === 'last_quarter' ? 'selected' : '' }}>Trimestre dernier</option>
                    </select>

                    <a href="{{ route('firm.clients.list') }}" class="btn btn-primary">
                        Voir tous les clients
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Clients -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-primary-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Total Clients
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ $portfolioMetrics['total_clients'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-green-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    CA Total Portfolio
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    @currency($portfolioMetrics['total_revenue'])
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TVA Balance -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-blue-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Solde TVA Net
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    @currency($portfolioMetrics['net_vat_balance'])
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outstanding -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-md bg-orange-500 p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Impayés
                                </dt>
                                <dd class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    @currency($portfolioMetrics['total_outstanding'])
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Client Health Scores -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Santé des Clients
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Score de santé (0-100) basé sur activité, finances, conformité
                        </p>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if(count($clientsWithScores) > 0)
                            <div class="space-y-4">
                                @foreach(array_slice($clientsWithScores, 0, 10) as $client)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer"
                                         onclick="window.location.href='{{ route('firm.clients.show', $client['mandate']->id) }}'">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $client['company']->name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $client['company']->vat_number ?? 'N° TVA non renseigné' }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <!-- Score Badge -->
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                @if($client['color'] === 'green') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($client['color'] === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($client['color'] === 'yellow') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @elseif($client['color'] === 'orange') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                @endif">
                                                {{ $client['score'] }}/100
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 min-w-[80px] text-right">
                                                {{ $client['status'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 text-center">
                                <a href="{{ route('firm.clients.list', ['sort' => 'health']) }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                    Voir tous les clients →
                                </a>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Aucun client actif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Activities & Tasks -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Recent Activities -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Activités Récentes
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if(count($recentActivities) > 0)
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($recentActivities as $index => $activity)
                                        <li>
                                            <div class="relative pb-8">
                                                @if($index < count($recentActivities) - 1)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                <span class="font-medium text-gray-900 dark:text-white">{{ $activity->user_name }}</span>
                                                                • {{ $activity->description }}
                                                            </p>
                                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                                {{ $activity->company_name }}
                                                            </p>
                                                        </div>
                                                        <div class="text-right text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                            {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                Aucune activité récente
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Tâches à Venir
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        @if(count($upcomingTasks) > 0)
                            <ul class="space-y-3">
                                @foreach($upcomingTasks as $task)
                                    <li class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-gray-600 transition cursor-pointer">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $task->title }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $task->company_name }}
                                                </p>
                                                @if($task->assigned_to_name)
                                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                        Assigné à: {{ $task->assigned_to_name }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="ml-3 flex flex-col items-end gap-1">
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                    @if($task->priority === 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                    @elseif($task->priority === 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200
                                                    @endif">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-4 text-center">
                                <a href="{{ route('firm.tasks.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                    Voir toutes les tâches →
                                </a>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                Aucune tâche à venir
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function firmDashboard() {
    return {
        selectedPeriod: '{{ $period }}',

        init() {
            console.log('Firm Dashboard initialized');
        }
    };
}
</script>
@endpush
@endsection
