@extends('layouts.app')

@section('title', 'Prévision Trésorerie IA')

@section('content')
<div x-data="treasuryApp()" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Prévision Trésorerie Intelligente
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Analyse prédictive de votre cash-flow basée sur l'IA
            </p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="forecastDays" @change="loadForecast()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                <option value="30">30 jours</option>
                <option value="60">60 jours</option>
                <option value="90" selected>90 jours</option>
                <option value="180">6 mois</option>
                <option value="365">1 an</option>
            </select>
            <a href="{{ route('ai.treasury.export') }}?days=90&format=csv"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm">Solde actuel</p>
                    <p class="text-3xl font-bold mt-1">
                        {{ number_format($forecast['current_balance'], 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        @php
            $endBalance = end($forecast['daily_forecast'])['projected_balance'] ?? $forecast['current_balance'];
            $variation = $endBalance - $forecast['current_balance'];
            $variationPercent = $forecast['current_balance'] != 0 ? ($variation / $forecast['current_balance']) * 100 : 0;
        @endphp

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm">Solde prévu (fin période)</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format($endBalance, 0, ',', ' ') }} €
                    </p>
                    <p class="text-sm mt-1 {{ $variation >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $variation >= 0 ? '+' : '' }}{{ number_format($variationPercent, 1) }}%
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $variation >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                    <svg class="w-6 h-6 {{ $variation >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($variation >= 0)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        @endif
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm">Entrées prévues</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        +{{ number_format(array_sum(array_column($forecast['daily_forecast'], 'expected_inflows')), 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 text-sm">Sorties prévues</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">
                        -{{ number_format(array_sum(array_column($forecast['daily_forecast'], 'expected_outflows')), 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique Principal -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Projection du solde
                </h3>
                <div class="flex items-center gap-4 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="showScenarios" class="rounded text-primary-600 focus:ring-primary-500">
                        <span class="text-secondary-600 dark:text-secondary-400">Scénarios</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-secondary-600 dark:text-secondary-400">Réaliste</span>
                    </div>
                </div>
            </div>
        </x-slot:header>

        <div class="h-96">
            <canvas x-ref="mainChart"></canvas>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Scénarios -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Scénarios
                </h3>
            </x-slot:header>

            <div class="space-y-4">
                @foreach($forecast['scenarios'] as $key => $scenario)
                    <div class="p-4 rounded-lg {{ $key === 'optimistic' ? 'bg-green-50 dark:bg-green-900/20' : ($key === 'pessimistic' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-secondary-900 dark:text-white">
                                {{ $key === 'optimistic' ? 'Optimiste' : ($key === 'pessimistic' ? 'Pessimiste' : 'Réaliste') }}
                            </span>
                            <x-badge :color="$key === 'optimistic' ? 'green' : ($key === 'pessimistic' ? 'red' : 'blue')">
                                {{ number_format($scenario['probability'] * 100, 0) }}%
                            </x-badge>
                        </div>
                        <div class="text-2xl font-bold {{ $key === 'optimistic' ? 'text-green-600' : ($key === 'pessimistic' ? 'text-red-600' : 'text-blue-600') }}">
                            {{ number_format($scenario['end_balance'], 0, ',', ' ') }} €
                        </div>
                        <p class="text-sm text-secondary-500 mt-1">{{ $scenario['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Alertes -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">
                        Alertes
                    </h3>
                    <x-badge color="red">{{ count($forecast['alerts']) }}</x-badge>
                </div>
            </x-slot:header>

            @if(empty($forecast['alerts']))
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-secondary-600 dark:text-secondary-400">Aucune alerte détectée</p>
                </div>
            @else
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @foreach($forecast['alerts'] as $alert)
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $alert['type'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20' : ($alert['type'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $alert['type'] === 'critical' ? 'bg-red-100 dark:bg-red-900/40' : ($alert['type'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/40' : 'bg-blue-100 dark:bg-blue-900/40') }}">
                                <svg class="w-4 h-4 {{ $alert['type'] === 'critical' ? 'text-red-600' : ($alert['type'] === 'warning' ? 'text-yellow-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($alert['type'] === 'critical')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    @elseif($alert['type'] === 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    @endif
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-secondary-900 dark:text-white">{{ $alert['message'] }}</p>
                                <p class="text-xs text-secondary-500 mt-1">{{ $alert['date'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Recommandations -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Recommandations IA
                </h3>
            </x-slot:header>

            @if(empty($forecast['recommendations']))
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-secondary-100 dark:bg-secondary-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <p class="text-secondary-600 dark:text-secondary-400">Pas de recommandation pour l'instant</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($forecast['recommendations'] as $recommendation)
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-secondary-900 dark:text-white">{{ $recommendation['title'] }}</p>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">{{ $recommendation['description'] }}</p>
                                    @if(isset($recommendation['impact']))
                                        <p class="text-xs text-purple-600 mt-2">Impact: {{ $recommendation['impact'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <!-- Détail des flux -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Détail des flux prévus
                </h3>
                <div class="flex items-center gap-2">
                    <button @click="viewMode = 'chart'" :class="viewMode === 'chart' ? 'bg-primary-100 text-primary-700' : 'text-secondary-600'"
                            class="px-3 py-1 rounded-lg text-sm transition-colors">
                        Graphique
                    </button>
                    <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-primary-100 text-primary-700' : 'text-secondary-600'"
                            class="px-3 py-1 rounded-lg text-sm transition-colors">
                        Tableau
                    </button>
                </div>
            </div>
        </x-slot:header>

        <div x-show="viewMode === 'chart'" class="h-64">
            <canvas x-ref="flowChart"></canvas>
        </div>

        <div x-show="viewMode === 'table'" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Entrées</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Sorties</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Solde</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Confiance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                    @foreach(array_slice($forecast['daily_forecast'], 0, 30) as $day)
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                            <td class="px-4 py-3 text-sm text-secondary-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}
                                @if($day['is_weekend'] ?? false)
                                    <span class="text-xs text-secondary-400 ml-1">(WE)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">
                                @if($day['expected_inflows'] > 0)
                                    +{{ number_format($day['expected_inflows'], 2, ',', ' ') }} €
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-red-600 font-medium">
                                @if($day['expected_outflows'] > 0)
                                    -{{ number_format($day['expected_outflows'], 2, ',', ' ') }} €
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold {{ $day['projected_balance'] < 0 ? 'text-red-600' : 'text-secondary-900 dark:text-white' }}">
                                {{ number_format($day['projected_balance'], 2, ',', ' ') }} €
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-12 bg-secondary-200 dark:bg-secondary-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $day['confidence'] >= 0.8 ? 'bg-green-500' : ($day['confidence'] >= 0.5 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                             style="width: {{ $day['confidence'] * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs text-secondary-500">{{ number_format($day['confidence'] * 100, 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Score de confiance global -->
    <div class="bg-gradient-to-r from-secondary-100 to-secondary-200 dark:from-secondary-800 dark:to-secondary-700 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Score de confiance global</h3>
                <p class="text-secondary-600 dark:text-secondary-400">Basé sur la qualité des données et l'historique</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-4xl font-bold text-secondary-900 dark:text-white">
                    {{ number_format($forecast['confidence_score'] * 100, 0) }}%
                </div>
                <div class="w-24 h-24">
                    <svg viewBox="0 0 36 36" class="circular-chart">
                        <path class="circle-bg" stroke="#e2e8f0" stroke-width="3" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" stroke="{{ $forecast['confidence_score'] >= 0.7 ? '#10b981' : ($forecast['confidence_score'] >= 0.4 ? '#f59e0b' : '#ef4444') }}"
                              stroke-width="3" stroke-linecap="round" fill="none"
                              stroke-dasharray="{{ $forecast['confidence_score'] * 100 }}, 100"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function treasuryApp() {
    return {
        forecastDays: 90,
        showScenarios: false,
        viewMode: 'chart',
        mainChart: null,
        flowChart: null,
        forecast: @json($forecast),

        init() {
            this.renderMainChart();
            this.renderFlowChart();

            this.$watch('showScenarios', () => this.renderMainChart());
        },

        renderMainChart() {
            const ctx = this.$refs.mainChart.getContext('2d');

            if (this.mainChart) {
                this.mainChart.destroy();
            }

            const datasets = [{
                label: 'Solde réaliste',
                data: this.forecast.daily_forecast.map(d => d.projected_balance),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
            }];

            if (this.showScenarios && this.forecast.scenarios) {
                // Add optimistic scenario
                const optimisticData = this.forecast.daily_forecast.map((d, i) => {
                    const factor = 1 + (this.forecast.scenarios.optimistic.end_balance - this.forecast.scenarios.realistic.end_balance) /
                                   this.forecast.scenarios.realistic.end_balance * (i / this.forecast.daily_forecast.length);
                    return d.projected_balance * factor;
                });

                datasets.push({
                    label: 'Optimiste',
                    data: optimisticData,
                    borderColor: 'rgba(34, 197, 94, 0.5)',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                });

                // Add pessimistic scenario
                const pessimisticData = this.forecast.daily_forecast.map((d, i) => {
                    const factor = 1 + (this.forecast.scenarios.pessimistic.end_balance - this.forecast.scenarios.realistic.end_balance) /
                                   this.forecast.scenarios.realistic.end_balance * (i / this.forecast.daily_forecast.length);
                    return d.projected_balance * factor;
                });

                datasets.push({
                    label: 'Pessimiste',
                    data: pessimisticData,
                    borderColor: 'rgba(239, 68, 68, 0.5)',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4,
                });
            }

            this.mainChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.forecast.daily_forecast.map(d => d.date),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: this.showScenarios,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return context.dataset.label + ': ' +
                                           new Intl.NumberFormat('fr-BE', { style: 'currency', currency: 'EUR' })
                                               .format(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            ticks: {
                                maxTicksLimit: 10,
                                callback: function(val, index) {
                                    const date = new Date(this.getLabelForValue(val));
                                    return date.toLocaleDateString('fr-BE', { day: '2-digit', month: 'short' });
                                }
                            }
                        },
                        y: {
                            display: true,
                            ticks: {
                                callback: value => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    notation: 'compact'
                                }).format(value)
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        },

        renderFlowChart() {
            const ctx = this.$refs.flowChart.getContext('2d');

            if (this.flowChart) {
                this.flowChart.destroy();
            }

            this.flowChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.forecast.daily_forecast.slice(0, 30).map(d => d.date),
                    datasets: [
                        {
                            label: 'Entrées',
                            data: this.forecast.daily_forecast.slice(0, 30).map(d => d.expected_inflows),
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        },
                        {
                            label: 'Sorties',
                            data: this.forecast.daily_forecast.slice(0, 30).map(d => -d.expected_outflows),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
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
                        x: {
                            stacked: true,
                            ticks: {
                                maxTicksLimit: 10,
                                callback: function(val, index) {
                                    const date = new Date(this.getLabelForValue(val));
                                    return date.toLocaleDateString('fr-BE', { day: '2-digit', month: 'short' });
                                }
                            }
                        },
                        y: {
                            stacked: true,
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
        },

        async loadForecast() {
            try {
                const response = await fetch(`/ai/treasury/forecast?days=${this.forecastDays}`);
                this.forecast = await response.json();
                this.renderMainChart();
                this.renderFlowChart();
            } catch (error) {
                console.error('Error loading forecast:', error);
            }
        }
    }
}
</script>
<style>
.circular-chart {
    transform: rotate(-90deg);
}
</style>
@endpush
@endsection
