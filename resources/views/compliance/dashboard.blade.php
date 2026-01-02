@extends('layouts.app')

@section('title', 'Conformité Fiscale')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="complianceDashboard()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Conformité Fiscale Belge</h1>
            <p class="text-gray-600">Surveillance proactive de votre conformité TVA et fiscale</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('compliance.refresh') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </button>
            </form>
            <a href="{{ route('analytics') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Analytics
            </a>
        </div>
    </div>

    <!-- Alert Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- High Severity -->
        <div class="card bg-red-50 border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-600">Alertes Critiques</p>
                    <p class="text-3xl font-bold text-red-700 mt-1">{{ count($alertsBySeverity['high']) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            @if(count($alertsBySeverity['high']) > 0)
                <p class="text-sm text-red-600 mt-3">Action requise immédiatement</p>
            @endif
        </div>

        <!-- Medium Severity -->
        <div class="card bg-yellow-50 border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600">Alertes Moyennes</p>
                    <p class="text-3xl font-bold text-yellow-700 mt-1">{{ count($alertsBySeverity['medium']) }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            @if(count($alertsBySeverity['medium']) > 0)
                <p class="text-sm text-yellow-600 mt-3">À traiter prochainement</p>
            @endif
        </div>

        <!-- Low Severity -->
        <div class="card bg-blue-50 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600">Optimisations</p>
                    <p class="text-3xl font-bold text-blue-700 mt-1">{{ count($optimizations) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            @if(count($optimizations) > 0)
                <p class="text-sm text-blue-600 mt-3">Opportunités détectées</p>
            @endif
        </div>
    </div>

    <!-- Alerts Section -->
    @if(count($alerts) > 0)
    <div class="card mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Alertes de Conformité
        </h2>

        <div class="space-y-4">
            @foreach($alerts as $alert)
            <div class="border rounded-lg p-4
                @if($alert['severity'] === 'high') border-red-300 bg-red-50
                @elseif($alert['severity'] === 'medium') border-yellow-300 bg-yellow-50
                @else border-blue-300 bg-blue-50
                @endif">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-start gap-3 flex-1">
                        <span class="px-2 py-1 text-xs font-semibold rounded
                            @if($alert['severity'] === 'high') bg-red-200 text-red-800
                            @elseif($alert['severity'] === 'medium') bg-yellow-200 text-yellow-800
                            @else bg-blue-200 text-blue-800
                            @endif">
                            {{ strtoupper($alert['severity']) }}
                        </span>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">{{ $alert['title'] }}</h3>
                            <p class="text-sm text-gray-700 mb-2">{{ $alert['message'] }}</p>
                            <p class="text-xs text-gray-600 mb-2">
                                <strong>Impact:</strong> {{ $alert['impact'] }}
                            </p>
                            <p class="text-xs text-gray-600">
                                <strong>Recommandation:</strong> {{ $alert['recommendation'] }}
                            </p>
                        </div>
                    </div>
                    @if($alert['reference_type'] === 'invoice')
                        <a href="{{ route('invoices.show', $alert['reference_id']) }}" class="btn btn-sm btn-outline">
                            Voir
                        </a>
                    @elseif($alert['reference_type'] === 'partner')
                        <a href="{{ route('partners.edit', $alert['reference_id']) }}" class="btn btn-sm btn-outline">
                            Corriger
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Optimizations Section -->
    @if(count($optimizations) > 0)
    <div class="card mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Optimisations TVA Recommandées
        </h2>

        <div class="space-y-4">
            @foreach($optimizations as $optimization)
            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-1">{{ $optimization['title'] }}</h3>
                        <p class="text-sm text-gray-700 mb-2">{{ $optimization['description'] }}</p>
                        <div class="grid grid-cols-2 gap-4 text-xs mb-2">
                            <div>
                                <strong class="text-gray-600">Situation actuelle:</strong>
                                <p class="text-gray-700">{{ $optimization['current_situation'] }}</p>
                            </div>
                            <div>
                                <strong class="text-gray-600">Action requise:</strong>
                                <p class="text-gray-700">{{ $optimization['action_required'] }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-600">
                            <strong>Recommandation:</strong> {{ $optimization['recommendation'] }}
                        </p>
                        @if($optimization['estimated_benefit'] > 0)
                        <div class="mt-2 inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Bénéfice estimé: €{{ number_format($optimization['estimated_benefit'], 2, ',', ' ') }}
                        </div>
                        @endif
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700">
                        {{ ucfirst($optimization['complexity']) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Fiscal Calendar -->
    <div class="card">
        <h2 class="text-xl font-semibold mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Calendrier Fiscal {{ now()->year }}
        </h2>

        <div class="mb-4">
            <h3 class="font-semibold text-gray-700 mb-3">Échéances Prochaines (60 jours)</h3>
            <div class="space-y-3">
                @forelse($upcomingDeadlines as $deadline)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ $deadline['title'] }}</h4>
                        <p class="text-sm text-gray-600">{{ $deadline['description'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Période: {{ $deadline['period'] }}</p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm font-semibold
                            @if($deadline['deadline']->diffInDays(now()) <= 7) text-red-600
                            @elseif($deadline['deadline']->diffInDays(now()) <= 14) text-yellow-600
                            @else text-gray-600
                            @endif">
                            {{ $deadline['deadline']->format('d/m/Y') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Dans {{ $deadline['deadline']->diffInDays(now()) }} jours
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-gray-600 text-center py-4">Aucune échéance dans les 60 prochains jours</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6">
            <h3 class="font-semibold text-gray-700 mb-3">Toutes les Échéances {{ now()->year }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Période</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Échéance</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pénalité</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fiscalCalendar as $item)
                        <tr class="
                            @if($item['deadline']->isPast()) bg-gray-100 text-gray-500
                            @elseif($item['deadline']->diffInDays(now()) <= 7) bg-red-50
                            @elseif($item['deadline']->diffInDays(now()) <= 14) bg-yellow-50
                            @endif">
                            <td class="px-4 py-3 text-sm">{{ $item['title'] }}</td>
                            <td class="px-4 py-3 text-sm">{{ $item['period'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $item['deadline']->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item['penalty'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function complianceDashboard() {
    return {
        init() {
            console.log('Compliance dashboard initialized');
        }
    }
}
</script>
@endsection
