@extends('layouts.app')

@section('title', 'Intelligence Artificielle')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Centre d'Intelligence Artificielle
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Automatisation, prévisions et analyses intelligentes
            </p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('ai.scanner') }}"
           class="group relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-1">Scanner OCR</h3>
                <p class="text-blue-100 text-sm">Extraction automatique de factures</p>
            </div>
        </a>

        <a href="{{ route('ai.treasury') }}"
           class="group relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-1">Prévision Trésorerie</h3>
                <p class="text-emerald-100 text-sm">Cash-flow prédictif IA</p>
            </div>
        </a>

        <a href="{{ route('ai.categorization') }}"
           class="group relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-1">Catégorisation</h3>
                <p class="text-purple-100 text-sm">Classification intelligente</p>
            </div>
        </a>

        <a href="{{ route('ai.anomalies') }}"
           class="group relative overflow-hidden bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">
            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-lg mb-1">Détection Anomalies</h3>
                <p class="text-orange-100 text-sm">Alertes et fraudes</p>
            </div>
        </a>
    </div>

    <!-- Stats OCR -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-stat-card
            label="Documents scannés"
            :value="$ocrStats['total_scans']"
            icon="document-text"
        />
        <x-stat-card
            label="En attente"
            :value="$ocrStats['pending']"
            icon="clock"
            color="yellow"
        />
        <x-stat-card
            label="Traités"
            :value="$ocrStats['processed']"
            icon="check-circle"
            color="green"
        />
        <x-stat-card
            label="Créés auto"
            :value="$ocrStats['auto_created']"
            icon="lightning-bolt"
            color="purple"
        />
        <x-stat-card
            label="Confiance moyenne"
            :value="number_format($ocrStats['avg_confidence'] * 100, 0) . '%'"
            icon="chart-bar"
            color="blue"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Prévision Trésorerie Mini -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">
                        Prévision Trésorerie (30 jours)
                    </h3>
                    <a href="{{ route('ai.treasury') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        Voir détails &rarr;
                    </a>
                </div>
            </x-slot:header>

            <div class="space-y-4">
                <!-- Solde actuel -->
                <div class="flex items-center justify-between p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                    <span class="text-secondary-600 dark:text-secondary-400">Solde actuel</span>
                    <span class="text-2xl font-bold text-secondary-900 dark:text-white">
                        <x-currency :amount="$treasuryForecast['current_balance']" />
                    </span>
                </div>

                <!-- Graphique mini -->
                <div x-data="treasuryMiniChart()" x-init="init()" class="h-40">
                    <canvas x-ref="chart"></canvas>
                </div>

                <!-- Alertes -->
                @if(!empty($treasuryForecast['alerts']))
                    <div class="space-y-2">
                        @foreach(array_slice($treasuryForecast['alerts'], 0, 3) as $alert)
                            <div class="flex items-start gap-3 p-3 rounded-lg {{ $alert['type'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20' : ($alert['type'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                                <svg class="w-5 h-5 flex-shrink-0 {{ $alert['type'] === 'critical' ? 'text-red-500' : ($alert['type'] === 'warning' ? 'text-yellow-500' : 'text-blue-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-secondary-900 dark:text-white">{{ $alert['message'] }}</p>
                                    <p class="text-xs text-secondary-500">{{ $alert['date'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Catégorisation -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">
                        Catégorisation Automatique
                    </h3>
                    <a href="{{ route('ai.categorization') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        Gérer &rarr;
                    </a>
                </div>
            </x-slot:header>

            <div class="space-y-4">
                <!-- Progress -->
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-secondary-600 dark:text-secondary-400">Taux de catégorisation</span>
                        <span class="font-medium text-secondary-900 dark:text-white">{{ $categorizationStats['rate'] }}%</span>
                    </div>
                    <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500"
                             style="width: {{ $categorizationStats['rate'] }}%"></div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-3 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                        <div class="text-2xl font-bold text-secondary-900 dark:text-white">
                            {{ $categorizationStats['total'] }}
                        </div>
                        <div class="text-xs text-secondary-500">Total</div>
                    </div>
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">
                            {{ $categorizationStats['categorized'] }}
                        </div>
                        <div class="text-xs text-secondary-500">Catégorisés</div>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">
                            {{ $categorizationStats['uncategorized'] }}
                        </div>
                        <div class="text-xs text-secondary-500">En attente</div>
                    </div>
                </div>

                @if($categorizationStats['uncategorized'] > 0)
                    <a href="{{ route('ai.categorization') }}"
                       class="block w-full py-3 text-center bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        Catégoriser {{ $categorizationStats['uncategorized'] }} éléments
                    </a>
                @endif
            </div>
        </x-card>
    </div>

    <!-- Derniers Scans -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">
                    Derniers Documents Scannés
                </h3>
                <a href="{{ route('ai.scanner') }}" class="text-sm text-primary-600 hover:text-primary-700">
                    Voir tout &rarr;
                </a>
            </div>
        </x-slot:header>

        @if($recentScans->isEmpty())
            <x-empty-state
                title="Aucun document scanné"
                description="Commencez par scanner vos premières factures pour profiter de l'extraction automatique."
                icon="document-text"
                :action="['label' => 'Scanner un document', 'url' => route('ai.scanner')]"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Document</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Fournisseur détecté</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Confiance</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @foreach($recentScans as $scan)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('ai.scan.show', $scan) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                        {{ Str::limit($scan->original_filename, 30) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <x-badge :color="$scan->document_type === 'invoice' ? 'blue' : 'gray'">
                                        {{ ucfirst($scan->document_type) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3 text-secondary-600 dark:text-secondary-400">
                                    {{ $scan->extracted_data['supplier_name'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-secondary-900 dark:text-white">
                                    @if(isset($scan->extracted_data['total_amount']))
                                        <x-currency :amount="$scan->extracted_data['total_amount']" />
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $scan->confidence_score >= 0.8 ? 'bg-green-500' : ($scan->confidence_score >= 0.5 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                 style="width: {{ $scan->confidence_score * 100 }}%"></div>
                                        </div>
                                        <span class="text-xs text-secondary-500">{{ number_format($scan->confidence_score * 100, 0) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @switch($scan->status)
                                        @case('completed')
                                            <x-badge color="green">Traité</x-badge>
                                            @break
                                        @case('pending')
                                            <x-badge color="yellow">En attente</x-badge>
                                            @break
                                        @case('needs_review')
                                            <x-badge color="orange">À vérifier</x-badge>
                                            @break
                                        @case('failed')
                                            <x-badge color="red">Échec</x-badge>
                                            @break
                                        @default
                                            <x-badge>{{ $scan->status }}</x-badge>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-500">
                                    {{ $scan->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function treasuryMiniChart() {
    return {
        chart: null,
        init() {
            const ctx = this.$refs.chart.getContext('2d');
            const forecast = @json(array_slice($treasuryForecast['daily_forecast'], 0, 30));

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: forecast.map(d => d.date),
                    datasets: [{
                        label: 'Solde prévu',
                        data: forecast.map(d => d.projected_balance),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: { display: false },
                        y: {
                            display: true,
                            grid: { display: false },
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
        }
    }
}
</script>
@endpush
@endsection
