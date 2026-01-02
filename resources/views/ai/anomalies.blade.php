@extends('layouts.app')

@section('title', 'Détection d\'Anomalies')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Détection d'Anomalies & Fraude
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Surveillance intelligente des transactions suspectes
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-badge color="green" class="px-3 py-1.5">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Surveillance active
                </span>
            </x-badge>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Anomalies détectées</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ count($anomalies) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">À vérifier</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">
                        {{ collect($anomalies)->where('severity', 'high')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Catégories analysées</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ count($trends) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Score de sécurité</p>
                    <p class="text-2xl font-bold text-green-600">{{ 100 - min(100, count($anomalies) * 5) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Anomalies détectées -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Anomalies Détectées
                </h3>
                <div class="flex items-center gap-2">
                    <select class="text-sm rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800">
                        <option>Toutes les catégories</option>
                        <option>Dépenses inhabituelles</option>
                        <option>Doublons potentiels</option>
                        <option>Transactions suspectes</option>
                    </select>
                </div>
            </div>
        </x-slot:header>

        @if(empty($anomalies))
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">Aucune anomalie détectée</h3>
                <p class="text-secondary-600 dark:text-secondary-400">Vos transactions semblent normales. La surveillance continue.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($anomalies as $anomaly)
                    <div x-data="{ expanded: false }"
                         class="border rounded-lg overflow-hidden {{ $anomaly['severity'] === 'high' ? 'border-red-300 dark:border-red-700' : ($anomaly['severity'] === 'medium' ? 'border-yellow-300 dark:border-yellow-700' : 'border-secondary-200 dark:border-secondary-700') }}">
                        <div class="p-4 flex items-center gap-4 cursor-pointer hover:bg-secondary-50 dark:hover:bg-secondary-800/50"
                             @click="expanded = !expanded">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                    {{ $anomaly['severity'] === 'high' ? 'bg-red-100 dark:bg-red-900/30' : ($anomaly['severity'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-blue-100 dark:bg-blue-900/30') }}">
                                    @if($anomaly['severity'] === 'high')
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @elseif($anomaly['severity'] === 'medium')
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-secondary-900 dark:text-white">
                                        {{ $anomaly['title'] ?? $anomaly['category'] }}
                                    </p>
                                    <x-badge :color="$anomaly['severity'] === 'high' ? 'red' : ($anomaly['severity'] === 'medium' ? 'yellow' : 'blue')">
                                        {{ $anomaly['severity'] === 'high' ? 'Critique' : ($anomaly['severity'] === 'medium' ? 'Attention' : 'Info') }}
                                    </x-badge>
                                </div>
                                <p class="text-sm text-secondary-500 mt-1">{{ $anomaly['description'] ?? $anomaly['message'] }}</p>
                            </div>

                            <div class="text-right">
                                @if(isset($anomaly['amount']))
                                    <p class="font-bold text-secondary-900 dark:text-white">
                                        {{ number_format($anomaly['amount'], 2, ',', ' ') }} €
                                    </p>
                                @endif
                                @if(isset($anomaly['z_score']))
                                    <p class="text-sm text-secondary-500">
                                        Score: {{ number_format(abs($anomaly['z_score']), 1) }}σ
                                    </p>
                                @endif
                            </div>

                            <svg class="w-5 h-5 text-secondary-400 transition-transform"
                                 :class="expanded ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <div x-show="expanded" x-collapse
                             class="border-t border-secondary-200 dark:border-secondary-700 p-4 bg-secondary-50 dark:bg-secondary-800/50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Détails de l'anomalie</h4>
                                    <dl class="space-y-2 text-sm">
                                        @if(isset($anomaly['category']))
                                            <div class="flex justify-between">
                                                <dt class="text-secondary-500">Catégorie</dt>
                                                <dd class="text-secondary-900 dark:text-white">{{ $anomaly['category'] }}</dd>
                                            </div>
                                        @endif
                                        @if(isset($anomaly['current_month']))
                                            <div class="flex justify-between">
                                                <dt class="text-secondary-500">Montant ce mois</dt>
                                                <dd class="text-secondary-900 dark:text-white">{{ number_format($anomaly['current_month'], 2, ',', ' ') }} €</dd>
                                            </div>
                                        @endif
                                        @if(isset($anomaly['average']))
                                            <div class="flex justify-between">
                                                <dt class="text-secondary-500">Moyenne historique</dt>
                                                <dd class="text-secondary-900 dark:text-white">{{ number_format($anomaly['average'], 2, ',', ' ') }} €</dd>
                                            </div>
                                        @endif
                                        @if(isset($anomaly['deviation']))
                                            <div class="flex justify-between">
                                                <dt class="text-secondary-500">Écart</dt>
                                                <dd class="{{ $anomaly['deviation'] > 0 ? 'text-red-600' : 'text-green-600' }} font-medium">
                                                    {{ $anomaly['deviation'] > 0 ? '+' : '' }}{{ number_format($anomaly['deviation'], 1) }}%
                                                </dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Actions recommandées</h4>
                                    <ul class="space-y-2 text-sm text-secondary-600 dark:text-secondary-400">
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Vérifier les factures correspondantes
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Confirmer avec le fournisseur si nécessaire
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Marquer comme vérifié si conforme
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700 flex justify-end gap-3">
                                <button class="px-4 py-2 text-secondary-600 hover:text-secondary-800 transition-colors">
                                    Ignorer
                                </button>
                                <button class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors">
                                    Marquer à vérifier
                                </button>
                                <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    Confirmer OK
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <!-- Tendances par catégorie -->
    @if(!empty($trends))
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Tendances de dépenses par catégorie
                </h3>
            </x-slot:header>

            <div x-data="trendsChart()" class="h-80">
                <canvas x-ref="chart"></canvas>
            </div>
        </x-card>
    @endif

    <!-- Types de fraudes surveillées -->
    <div class="bg-gradient-to-r from-secondary-100 to-secondary-200 dark:from-secondary-800 dark:to-secondary-700 rounded-xl p-6">
        <h3 class="font-semibold text-secondary-900 dark:text-white mb-4">Types de fraudes surveillées</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-white dark:bg-secondary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-secondary-600 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Doublons de factures</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Détection des factures similaires ou identiques</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-white dark:bg-secondary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-secondary-600 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Dépenses inhabituelles</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Montants anormalement élevés ou patterns suspects</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-white dark:bg-secondary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-secondary-600 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Fournisseurs fictifs</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">Validation des numéros de TVA et fournisseurs</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function trendsChart() {
    return {
        chart: null,
        init() {
            const ctx = this.$refs.chart.getContext('2d');
            const trends = @json($trends);

            const labels = Object.keys(trends).slice(0, 8);
            const currentData = labels.map(k => trends[k]?.current_month || 0);
            const averageData = labels.map(k => trends[k]?.average || 0);

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map(l => trends[l]?.label || l),
                    datasets: [
                        {
                            label: 'Ce mois',
                            data: currentData,
                            backgroundColor: 'rgba(139, 92, 246, 0.8)',
                        },
                        {
                            label: 'Moyenne',
                            data: averageData,
                            backgroundColor: 'rgba(156, 163, 175, 0.5)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    notation: 'compact'
                                }).format(value)
                            }
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection
