<x-app-layout>
    <x-slot name="title">Analytics IA</x-slot>

    <div class="space-y-6" x-data="analyticsApp()">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white flex items-center gap-3">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Analytics & Intelligence Artificielle
                </h1>
                <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                    Tableau de bord analytique avec insights IA en temps réel
                </p>
            </div>
            <div class="flex gap-2">
                <button @click="refreshData" class="btn btn-secondary btn-sm" :disabled="loading">
                    <svg class="w-4 h-4 mr-2" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </button>
                <button @click="exportPDF" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Health Score -->
        <div class="card bg-gradient-to-br from-primary-500 to-primary-700 text-white">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Overall Score -->
                    <div class="md:col-span-1 flex flex-col items-center justify-center border-r border-white/20">
                        <div class="text-sm font-medium opacity-90 mb-2">Score de Santé Global</div>
                        <div class="relative w-32 h-32">
                            <svg class="transform -rotate-90 w-32 h-32">
                                <circle cx="64" cy="64" r="56" stroke="rgba(255,255,255,0.2)" stroke-width="8" fill="none"/>
                                <circle
                                    cx="64" cy="64" r="56"
                                    stroke="white"
                                    stroke-width="8"
                                    fill="none"
                                    :stroke-dasharray="352"
                                    :stroke-dashoffset="352 - (352 * {{ $healthScore['overall'] }} / 100)"
                                    class="transition-all duration-1000"
                                />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-4xl font-bold">{{ $healthScore['overall'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="badge" style="background: rgba(255,255,255,0.2);">
                                {{ $healthScore['rating']['label'] }}
                            </span>
                            @if($healthScore['trend'] > 0)
                                <span class="text-success-300 flex items-center text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    +{{ number_format($healthScore['trend'], 1) }}
                                </span>
                            @elseif($healthScore['trend'] < 0)
                                <span class="text-danger-300 flex items-center text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ number_format($healthScore['trend'], 1) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Components Breakdown -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold mb-4">Composantes du Score</h3>
                        <div class="space-y-3">
                            @foreach($healthScore['components'] as $key => $component)
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium">{{ $component['label'] }}</span>
                                        <span class="text-sm font-bold">{{ $component['score'] }}/100</span>
                                    </div>
                                    <div class="w-full bg-white/20 rounded-full h-2">
                                        <div
                                            class="bg-white h-2 rounded-full transition-all duration-1000"
                                            style="width: {{ $component['score'] }}%"
                                        ></div>
                                    </div>
                                    <p class="text-xs opacity-75 mt-1">{{ $component['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Chiffre d'affaires (3m)</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                                {{ number_format($kpis['total_revenue'], 2) }} €
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Dépenses (3m)</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                                {{ number_format($kpis['total_expenses'], 2) }} €
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-danger-100 dark:bg-danger-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-danger-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Factures impayées</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                                {{ number_format($kpis['outstanding_invoices'], 2) }} €
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">Délai paiement moyen</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                                {{ round($kpis['avg_payment_delay']) }} jours
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-info-100 dark:bg-info-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insights & Anomalies -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- AI Insights -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                        </svg>
                        Insights IA
                    </h2>
                    <span class="badge badge-primary">{{ count($insights) }}</span>
                </div>
                <div class="card-body">
                    @if(empty($insights))
                        <div class="text-center py-8 text-secondary-500">
                            <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Aucun insight pour le moment</p>
                            <p class="text-sm mt-1">L'IA analyse vos données...</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($insights as $insight)
                                <div class="border-l-4 p-4 rounded-r-lg {{ $insight['severity'] === 'high' ? 'border-danger-500 bg-danger-50 dark:bg-danger-900/20' : ($insight['severity'] === 'medium' ? 'border-warning-500 bg-warning-50 dark:bg-warning-900/20' : 'border-info-500 bg-info-50 dark:bg-info-900/20') }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="font-semibold text-secondary-900 dark:text-white">{{ $insight['title'] }}</h3>
                                        <span class="badge badge-{{ $insight['severity'] === 'high' ? 'danger' : ($insight['severity'] === 'medium' ? 'warning' : 'info') }} text-xs">
                                            {{ ucfirst($insight['severity']) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-secondary-700 dark:text-secondary-300 mb-2">{{ $insight['description'] }}</p>
                                    <p class="text-xs text-secondary-600 dark:text-secondary-400 mb-3"><strong>Impact:</strong> {{ $insight['impact'] }}</p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs italic text-secondary-600 dark:text-secondary-400">💡 {{ $insight['recommendation'] }}</p>
                                        @if(isset($insight['action_url']))
                                            <a href="{{ $insight['action_url'] }}" class="text-xs text-primary-600 hover:underline">
                                                {{ $insight['action_text'] }} →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Anomalies -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-danger-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Anomalies Détectées
                    </h2>
                    <span class="badge badge-danger">{{ count($anomalies) }}</span>
                </div>
                <div class="card-body">
                    @if(empty($anomalies))
                        <div class="text-center py-8 text-secondary-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="font-medium text-success-600">Aucune anomalie détectée</p>
                            <p class="text-sm mt-1">Vos données sont conformes</p>
                        </div>
                    @else
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($anomalies as $anomaly)
                                <div class="border border-secondary-200 dark:border-secondary-700 rounded-lg p-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $anomaly['severity'] === 'high' ? 'bg-danger-100 text-danger-600' : ($anomaly['severity'] === 'medium' ? 'bg-warning-100 text-warning-600' : 'bg-info-100 text-info-600') }}">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-secondary-900 dark:text-white">{{ $anomaly['title'] }}</h4>
                                            <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">{{ $anomaly['description'] }}</p>
                                            @if(isset($anomaly['details']))
                                                <p class="text-xs text-secondary-500 mt-1">{{ $anomaly['details'] }}</p>
                                            @endif
                                            @if(isset($anomaly['date']))
                                                <p class="text-xs text-secondary-400 mt-1">{{ $anomaly['date'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Trends Chart -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Tendances (12 derniers mois)</h2>
            </div>
            <div class="card-body">
                <canvas id="trendsChart" height="80"></canvas>
            </div>
        </div>

        <!-- Predictions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Prediction -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                        Prévisions CA
                    </h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-secondary-600 dark:text-secondary-400">3 mois</span>
                                <span class="font-bold text-secondary-900 dark:text-white">{{ number_format($predictions['revenue']['3_months'], 2) }} €</span>
                            </div>
                            <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                <div class="bg-success-500 h-2 rounded-full" style="width: 33%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-secondary-600 dark:text-secondary-400">6 mois</span>
                                <span class="font-bold text-secondary-900 dark:text-white">{{ number_format($predictions['revenue']['6_months'], 2) }} €</span>
                            </div>
                            <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                <div class="bg-success-500 h-2 rounded-full" style="width: 66%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-secondary-600 dark:text-secondary-400">12 mois</span>
                                <span class="font-bold text-secondary-900 dark:text-white">{{ number_format($predictions['revenue']['12_months'], 2) }} €</span>
                            </div>
                            <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                <div class="bg-success-500 h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="pt-3 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-secondary-600 dark:text-secondary-400">Confiance IA</span>
                                <span class="font-medium">{{ round($predictions['revenue']['confidence'] * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Flow Prediction -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Scénarios Trésorerie
                    </h2>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 rounded-lg">
                            <div>
                                <p class="text-xs text-success-700 dark:text-success-400">Optimiste</p>
                                <p class="font-bold text-success-900 dark:text-success-300">{{ number_format($predictions['cash_flow']['scenarios']['optimistic'], 2) }} €</p>
                            </div>
                            <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-info-50 dark:bg-info-900/20 rounded-lg">
                            <div>
                                <p class="text-xs text-info-700 dark:text-info-400">Réaliste</p>
                                <p class="font-bold text-info-900 dark:text-info-300">{{ number_format($predictions['cash_flow']['scenarios']['realistic'], 2) }} €</p>
                            </div>
                            <svg class="w-6 h-6 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                            <div>
                                <p class="text-xs text-warning-700 dark:text-warning-400">Pessimiste</p>
                                <p class="font-bold text-warning-900 dark:text-warning-300">{{ number_format($predictions['cash_flow']['scenarios']['pessimistic'], 2) }} €</p>
                            </div>
                            <svg class="w-6 h-6 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="pt-3 border-t border-secondary-200 dark:border-secondary-700">
                            <p class="text-xs text-secondary-600 dark:text-secondary-400">{{ $predictions['cash_flow']['recommendation'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses Prediction -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-danger-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"/>
                        </svg>
                        Prévisions Dépenses
                    </h2>
                </div>
                <div class="card-body">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">Mois prochain</p>
                            <p class="text-3xl font-bold text-secondary-900 dark:text-white">{{ number_format($predictions['expenses']['next_month'], 2) }} €</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">Trimestre prochain</p>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($predictions['expenses']['next_quarter'], 2) }} €</p>
                        </div>
                        <div class="pt-3 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-secondary-600 dark:text-secondary-400">Confiance IA</span>
                                <span class="font-medium">{{ round($predictions['expenses']['confidence'] * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function analyticsApp() {
            return {
                loading: false,

                async refreshData() {
                    this.loading = true;
                    try {
                        const response = await fetch('{{ route("ai.analytics.refresh") }}', {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.showToast('Données actualisées', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    } catch (error) {
                        console.error('Refresh error:', error);
                        window.showToast('Erreur lors de l\'actualisation', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async exportPDF() {
                    window.showToast('Export PDF sera disponible dans Phase 4.1', 'info');
                }
            };
        }

        // Initialize trends chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('trendsChart');
            if (!ctx) return;

            const trendsData = @json($trends);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendsData.labels,
                    datasets: [
                        {
                            label: 'Chiffre d\'affaires',
                            data: trendsData.revenue,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Dépenses',
                            data: trendsData.expenses,
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Profit',
                            data: trendsData.profit,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat('fr-BE', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    }).format(context.parsed.y);
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('fr-BE', {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 0
                                    }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
