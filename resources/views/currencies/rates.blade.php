<x-app-layout>
    <x-slot name="title">Historique taux - {{ $currency->code }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('currencies.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Devises</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">{{ $currency->code }}</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Historique {{ $currency->name }}</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Evolution du taux {{ $currency->code }}/{{ $baseCurrency->code }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('currencies.index') }}" class="btn btn-outline-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <button type="button" onclick="document.getElementById('rate-modal').classList.remove('hidden')" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter un taux
                </button>
            </div>
        </div>

        <!-- Taux actuel -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-6 col-span-2 bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-primary-600 dark:text-primary-400">Taux actuel</p>
                        <p class="text-3xl font-bold text-primary-800 dark:text-primary-200">{{ number_format($currency->current_rate, 4) }}</p>
                        <p class="text-sm text-primary-600 dark:text-primary-400 mt-1">1 {{ $baseCurrency->code }} = {{ number_format($currency->current_rate, 4) }} {{ $currency->code }}</p>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-primary-200 dark:bg-primary-800 flex items-center justify-center text-2xl font-bold text-primary-700 dark:text-primary-300">
                        {{ $currency->symbol }}
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Plus haut (30j)</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($highRate, 4) }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-danger-100 text-danger-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Plus bas (30j)</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($lowRate, 4) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white">Evolution du taux</h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="setChartPeriod(7)" class="btn btn-sm btn-outline-secondary chart-period" data-days="7">7j</button>
                    <button type="button" onclick="setChartPeriod(30)" class="btn btn-sm btn-primary chart-period active" data-days="30">30j</button>
                    <button type="button" onclick="setChartPeriod(90)" class="btn btn-sm btn-outline-secondary chart-period" data-days="90">90j</button>
                    <button type="button" onclick="setChartPeriod(365)" class="btn btn-sm btn-outline-secondary chart-period" data-days="365">1an</button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="ratesChart"></canvas>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Du</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Au</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['from', 'to']))
                    <a href="{{ route('currencies.rates', $currency) }}" class="btn btn-outline-secondary">Reinitialiser</a>
                @endif
            </form>
        </div>

        <!-- Historique des taux -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Taux</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Variation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Source</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($rates as $rate)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $rate->rate_date->format('d/m/Y') }}
                                    @if($rate->rate_date->isToday())
                                        <span class="badge badge-success ml-2">Aujourd'hui</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">
                                    {{ number_format($rate->rate, 6) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($rate->variation !== null)
                                        @if($rate->variation > 0)
                                            <span class="text-success-600 text-sm">+{{ number_format($rate->variation, 4) }}%</span>
                                        @elseif($rate->variation < 0)
                                            <span class="text-danger-600 text-sm">{{ number_format($rate->variation, 4) }}%</span>
                                        @else
                                            <span class="text-secondary-400 text-sm">0%</span>
                                        @endif
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    @if($rate->source == 'ecb')
                                        <span class="badge badge-info">BCE</span>
                                    @elseif($rate->source == 'manual')
                                        <span class="badge badge-warning">Manuel</span>
                                    @elseif($rate->source == 'api')
                                        <span class="badge badge-primary">API</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $rate->source }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($rate->source == 'manual')
                                        <form action="{{ route('currencies.rates.destroy', [$currency, $rate]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce taux ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-secondary-500 hover:text-danger-500">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-secondary-500">Aucun historique de taux</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rates->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $rates->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Taux -->
    <div id="rate-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('rate-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Ajouter un taux manuel</h3>
                <form action="{{ route('currencies.rates.store', $currency) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Date <span class="text-danger-500">*</span></label>
                            <input type="date" name="rate_date" value="{{ date('Y-m-d') }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Taux <span class="text-danger-500">*</span></label>
                            <input type="number" step="0.000001" name="rate" class="form-input" placeholder="1.0856" required>
                            <p class="text-xs text-secondary-500 mt-1">1 {{ $baseCurrency->code }} = X {{ $currency->code }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('rate-modal').classList.add('hidden')" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($chartData);
        let ratesChart;

        function initChart(days = 30) {
            const filteredData = chartData.slice(-days);
            const ctx = document.getElementById('ratesChart').getContext('2d');

            if (ratesChart) {
                ratesChart.destroy();
            }

            ratesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: filteredData.map(d => d.date),
                    datasets: [{
                        label: '{{ $currency->code }}/{{ $baseCurrency->code }}',
                        data: filteredData.map(d => d.rate),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }

        function setChartPeriod(days) {
            document.querySelectorAll('.chart-period').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            });
            document.querySelector(`.chart-period[data-days="${days}"]`).classList.remove('btn-outline-secondary');
            document.querySelector(`.chart-period[data-days="${days}"]`).classList.add('btn-primary');
            initChart(days);
        }

        // Init chart
        initChart(30);
    </script>
    @endpush
</x-app-layout>
