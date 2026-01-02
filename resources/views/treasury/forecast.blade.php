@extends('layouts.app')

@section('title', 'Prévisions de Trésorerie')

@section('content')
<div x-data="treasuryForecast({{ $days }}, '{{ $selectedScenario }}')" class="space-y-6">
    <!-- Header avec actions -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900 dark:text-secondary-100">
                Prévisions de Trésorerie
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                Analyse prédictive sur <span x-text="days"></span> jours avec ML
            </p>
        </div>

        <div class="flex gap-3">
            <button @click="exportPDF()" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Export PDF
            </button>
            <button @click="exportExcel()" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Sélection période et scénario -->
    <div class="card">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Période -->
                <div>
                    <label class="label">Période de prévision</label>
                    <select x-model="days" @change="loadForecast()" class="select">
                        <option value="30">30 jours</option>
                        <option value="60">60 jours</option>
                        <option value="90">90 jours</option>
                        <option value="180">6 mois</option>
                        <option value="365">1 an</option>
                    </select>
                </div>

                <!-- Scénario -->
                <div>
                    <label class="label">Scénario</label>
                    <select x-model="scenario" @change="loadForecast()" class="select">
                        <option value="optimistic">Optimiste (+15% entrées, -10% sorties)</option>
                        <option value="realistic">Réaliste (basé sur historique)</option>
                        <option value="pessimistic">Pessimiste (-15% entrées, +10% sorties)</option>
                    </select>
                </div>

                <!-- Mode simulation -->
                <div>
                    <label class="label">&nbsp;</label>
                    <button @click="showWhatIf = !showWhatIf"
                            :class="showWhatIf ? 'btn-primary' : 'btn-secondary'"
                            class="btn w-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Simulation What-If
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principaux -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" x-show="forecast">
        <!-- Solde actuel -->
        <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Solde actuel</p>
                        <h3 class="text-3xl font-bold mt-1" x-text="formatCurrency(forecast?.summary?.current_balance)"></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solde projeté -->
        <div class="card" :class="forecast?.summary?.projected_balance >= 0 ? 'bg-gradient-to-br from-green-500 to-green-600' : 'bg-gradient-to-br from-red-500 to-red-600'" class="text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm font-medium">Solde projeté (J+<span x-text="days"></span>)</p>
                        <h3 class="text-3xl font-bold mt-1" x-text="formatCurrency(forecast?.summary?.projected_balance)"></h3>
                        <div class="flex items-center mt-2 text-sm">
                            <svg x-show="forecast?.summary?.trend === 'up'" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <svg x-show="forecast?.summary?.trend === 'down'" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="Math.abs(forecast?.summary?.trend_percent) + '%'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total entrées -->
        <div class="card bg-gradient-to-br from-emerald-500 to-emerald-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium">Entrées attendues</p>
                        <h3 class="text-3xl font-bold mt-1" x-text="formatCurrency(forecast?.summary?.total_inflow)"></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total sorties -->
        <div class="card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Sorties prévues</p>
                        <h3 class="text-3xl font-bold mt-1" x-text="formatCurrency(forecast?.summary?.total_outflow)"></h3>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique principal: Évolution Trésorerie -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Évolution Prévisionnelle de la Trésorerie</h3>
            <p class="text-sm text-secondary-500">Projection basée sur ML avec analyse historique</p>
        </div>
        <div class="card-body">
            <div id="chart-balance" style="min-height: 400px;"></div>
        </div>
    </div>

    <!-- Simulation What-If (collapsible) -->
    <div x-show="showWhatIf" x-transition class="card border-2 border-primary-200 dark:border-primary-800">
        <div class="card-header bg-primary-50 dark:bg-primary-900/20">
            <h3 class="card-title text-primary-700 dark:text-primary-300">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Simulation What-If
            </h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="label">Type de simulation</label>
                    <select x-model="whatIf.type" class="select">
                        <option value="revenue_increase">Augmentation revenus</option>
                        <option value="cost_reduction">Réduction coûts</option>
                        <option value="delayed_payment">Retard paiement</option>
                        <option value="new_expense">Nouvelle dépense</option>
                    </select>
                </div>

                <div>
                    <label class="label">Montant (€)</label>
                    <input type="number" x-model="whatIf.amount" class="input" placeholder="5000">
                </div>

                <div>
                    <label class="label">Date de début</label>
                    <input type="date" x-model="whatIf.startDate" class="input">
                </div>

                <div>
                    <label class="label">Fréquence</label>
                    <select x-model="whatIf.frequency" class="select">
                        <option value="once">Une fois</option>
                        <option value="monthly">Mensuel</option>
                        <option value="weekly">Hebdomadaire</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex gap-3">
                <button @click="runWhatIf()" class="btn btn-primary">
                    Simuler l'Impact
                </button>
                <button @click="resetWhatIf()" class="btn btn-secondary">
                    Réinitialiser
                </button>
            </div>

            <!-- Résultats simulation -->
            <div x-show="whatIfResult" x-transition class="mt-6 p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                <h4 class="font-semibold mb-3">Impact de la Simulation</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">Impact sur solde final</p>
                        <p class="text-2xl font-bold" :class="whatIfResult?.balance_impact >= 0 ? 'text-green-600' : 'text-red-600'" x-text="formatCurrency(whatIfResult?.balance_impact)"></p>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">Jour le plus critique</p>
                        <p class="text-lg font-semibold" x-text="whatIfResult?.worst_day_modified?.date"></p>
                        <p class="text-sm" x-text="formatCurrency(whatIfResult?.worst_day_modified?.balance)"></p>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">Amélioration pire jour</p>
                        <p class="text-2xl font-bold" :class="whatIfResult?.worst_day_improvement >= 0 ? 'text-green-600' : 'text-red-600'" x-text="formatCurrency(whatIfResult?.worst_day_improvement)"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques secondaires -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Flux entrants/sortants -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Flux de Trésorerie</h3>
            </div>
            <div class="card-body">
                <div id="chart-cashflow" style="min-height: 300px;"></div>
            </div>
        </div>

        <!-- Distribution cumulative -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Cumul Entrées vs Sorties</h3>
            </div>
            <div class="card-body">
                <div id="chart-cumulative" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Loading state -->
    <div x-show="loading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-secondary-800 rounded-lg p-8 flex flex-col items-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-primary-600 mb-4"></div>
            <p class="text-lg font-medium">Calcul des prévisions en cours...</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function treasuryForecast(initialDays, initialScenario) {
    return {
        days: initialDays,
        scenario: initialScenario,
        forecast: null,
        loading: false,
        showWhatIf: false,
        whatIf: {
            type: 'revenue_increase',
            amount: null,
            startDate: new Date().toISOString().split('T')[0],
            frequency: 'once'
        },
        whatIfResult: null,
        charts: {},

        async init() {
            await this.loadForecast();
        },

        async loadForecast() {
            this.loading = true;
            try {
                const response = await axios.get('/treasury/forecast/data', {
                    params: {
                        days: this.days,
                        scenario: this.scenario
                    }
                });

                this.forecast = response.data.data;
                this.$nextTick(() => this.renderCharts());
            } catch (error) {
                console.error('Forecast error:', error);
                window.showToast?.('Erreur lors du chargement des prévisions', 'error');
            } finally {
                this.loading = false;
            }
        },

        renderCharts() {
            if (!this.forecast) return;

            const daily = this.forecast.daily_forecast || [];
            const dates = daily.map(d => d.date);
            const balances = daily.map(d => d.balance);
            const inflows = daily.map(d => d.inflows);
            const outflows = daily.map(d => d.outflows);

            // Chart 1: Évolution solde
            if (this.charts.balance) this.charts.balance.destroy();
            this.charts.balance = new ApexCharts(document.querySelector("#chart-balance"), {
                series: [{
                    name: 'Solde prév',
                    data: balances
                }],
                chart: {
                    height: 400,
                    type: 'area',
                    toolbar: { show: true },
                    zoom: { enabled: true }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                    }
                },
                xaxis: {
                    categories: dates,
                    labels: { rotate: -45 }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => this.formatCurrency(val)
                    }
                },
                tooltip: {
                    y: {
                        formatter: (val) => this.formatCurrency(val)
                    }
                },
                colors: ['#3b82f6']
            });
            this.charts.balance.render();

            // Chart 2: Cash flow
            if (this.charts.cashflow) this.charts.cashflow.destroy();
            this.charts.cashflow = new ApexCharts(document.querySelector("#chart-cashflow"), {
                series: [{
                    name: 'Entrées',
                    data: inflows
                }, {
                    name: 'Sorties',
                    data: outflows
                }],
                chart: {
                    height: 300,
                    type: 'bar',
                    stacked: false
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                    },
                },
                xaxis: {
                    categories: dates,
                    labels: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => this.formatCurrency(val)
                    }
                },
                colors: ['#10b981', '#f59e0b']
            });
            this.charts.cashflow.render();

            // Chart 3: Cumulative
            const cumulativeIn = inflows.reduce((acc, val, i) => {
                acc.push((acc[i - 1] || 0) + val);
                return acc;
            }, []);
            const cumulativeOut = outflows.reduce((acc, val, i) => {
                acc.push((acc[i - 1] || 0) + val);
                return acc;
            }, []);

            if (this.charts.cumulative) this.charts.cumulative.destroy();
            this.charts.cumulative = new ApexCharts(document.querySelector("#chart-cumulative"), {
                series: [{
                    name: 'Cumul entrées',
                    data: cumulativeIn
                }, {
                    name: 'Cumul sorties',
                    data: cumulativeOut
                }],
                chart: {
                    height: 300,
                    type: 'line'
                },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: dates,
                    labels: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => this.formatCurrency(val)
                    }
                },
                colors: ['#10b981', '#ef4444']
            });
            this.charts.cumulative.render();
        },

        async runWhatIf() {
            this.loading = true;
            try {
                const response = await axios.post('/treasury/what-if', {
                    scenario_type: this.whatIf.type,
                    amount: this.whatIf.amount,
                    start_date: this.whatIf.startDate,
                    frequency: this.whatIf.frequency
                });

                this.whatIfResult = response.data.data.impact;
                window.showToast?.('Simulation terminée', 'success');
            } catch (error) {
                console.error('What-if error:', error);
                window.showToast?.('Erreur lors de la simulation', 'error');
            } finally {
                this.loading = false;
            }
        },

        resetWhatIf() {
            this.whatIf = {
                type: 'revenue_increase',
                amount: null,
                startDate: new Date().toISOString().split('T')[0],
                frequency: 'once'
            };
            this.whatIfResult = null;
        },

        async exportPDF() {
            window.open(`/treasury/export/pdf?days=${this.days}`, '_blank');
        },

        async exportExcel() {
            window.open(`/treasury/export/excel?days=${this.days}`, '_blank');
        },

        formatCurrency(value) {
            if (value == null) return '—';
            return new Intl.NumberFormat('fr-BE', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        }
    }
}
</script>
@endpush
@endsection
