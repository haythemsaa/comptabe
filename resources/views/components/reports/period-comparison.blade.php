@props([
    'reportType' => 'profit_loss',
    'defaultPeriods' => 'last_6_months'
])

<div x-data="periodComparison(@js($reportType), @js($defaultPeriods))" x-init="init()">
    <!-- Period Selector -->
    <div class="card mb-6">
        <div class="card-header">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-secondary-900 dark:text-white">Sélection des périodes</h3>
                <div class="flex gap-2">
                    <button @click="loadComparison()" :disabled="loading" class="btn btn-primary btn-sm">
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg x-show="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Chargement...' : 'Actualiser'"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Preset Periods -->
                <div>
                    <label class="label">Périodes prédéfinies</label>
                    <select x-model="selectedPreset" @change="applyPreset()" class="input">
                        <option value="current_month">Mois en cours</option>
                        <option value="current_quarter">Trimestre en cours</option>
                        <option value="current_year">Année en cours</option>
                        <option value="last_3_months">3 derniers mois</option>
                        <option value="last_6_months">6 derniers mois</option>
                        <option value="last_12_months">12 derniers mois</option>
                        <option value="last_4_quarters">4 derniers trimestres</option>
                        <option value="year_comparison">Comparaison annuelle (N vs N-1)</option>
                        <option value="custom">Personnalisé</option>
                    </select>
                </div>

                <!-- Custom Date Range (if custom selected) -->
                <template x-if="selectedPreset === 'custom'">
                    <div>
                        <label class="label">Date de début</label>
                        <input type="date" x-model="customStart" class="input">
                    </div>
                </template>

                <template x-if="selectedPreset === 'custom'">
                    <div>
                        <label class="label">Date de fin</label>
                        <input type="date" x-model="customEnd" class="input">
                    </div>
                </template>

                <!-- Comparison Type -->
                <div>
                    <label class="label">Type de comparaison</label>
                    <select x-model="comparisonType" class="input">
                        <option value="side_by_side">Côte à côte</option>
                        <option value="variation">Variations</option>
                        <option value="chart">Graphique</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <template x-if="loading">
        <div class="card">
            <div class="card-body text-center py-12">
                <svg class="animate-spin w-12 h-12 mx-auto text-primary-500 mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-secondary-500">Chargement des données...</p>
            </div>
        </div>
    </template>

    <!-- Side by Side Comparison -->
    <template x-if="!loading && comparisonType === 'side_by_side' && data.length > 0">
        <div class="card overflow-x-auto">
            <div class="card-body p-0">
                <table class="min-w-full divide-y divide-secondary-200 dark:divide-secondary-700">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider sticky left-0 bg-secondary-50 dark:bg-secondary-800">
                                Compte
                            </th>
                            <template x-for="period in data" :key="period.label">
                                <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    <span x-text="period.label"></span>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-secondary-900 divide-y divide-secondary-200 dark:divide-secondary-700">
                        <!-- Revenue Section (for P&L) -->
                        <template x-if="reportType === 'profit_loss'">
                            <template x-for="(row, index) in getComparisonRows()" :key="index">
                                <tr :class="row.isBold ? 'font-semibold bg-secondary-50 dark:bg-secondary-800' : ''">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white sticky left-0 bg-white dark:bg-secondary-900"
                                        :class="row.indent ? 'pl-12' : ''"
                                        x-text="row.label"></td>
                                    <template x-for="value in row.values" :key="value">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right"
                                            :class="value < 0 ? 'text-danger-600' : 'text-secondary-900 dark:text-white'"
                                            x-text="formatCurrency(value)"></td>
                                    </template>
                                </tr>
                            </template>
                        </template>

                        <!-- Assets/Liabilities Section (for Balance Sheet) -->
                        <template x-if="reportType === 'balance_sheet'">
                            <template x-for="(row, index) in getBalanceSheetRows()" :key="index">
                                <tr :class="row.isBold ? 'font-semibold bg-secondary-50 dark:bg-secondary-800' : ''">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900 dark:text-white sticky left-0 bg-white dark:bg-secondary-900"
                                        :class="row.indent ? 'pl-12' : ''"
                                        x-text="row.label"></td>
                                    <template x-for="value in row.values" :key="value">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-secondary-900 dark:text-white"
                                            x-text="formatCurrency(value)"></td>
                                    </template>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>

    <!-- Variation View -->
    <template x-if="!loading && comparisonType === 'variation' && data.length > 1">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="metric in getKeyMetrics()" :key="metric.label">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-secondary-500" x-text="metric.label"></h4>
                            <div :class="metric.trend === 'up' ? 'text-success-500' : metric.trend === 'down' ? 'text-danger-500' : 'text-secondary-500'">
                                <svg x-show="metric.trend === 'up'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg x-show="metric.trend === 'down'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-2xl font-bold text-secondary-900 dark:text-white" x-text="formatCurrency(metric.current)"></p>
                                <p class="text-xs text-secondary-500 mt-1">Période actuelle</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold" :class="metric.variationPct >= 0 ? 'text-success-600' : 'text-danger-600'">
                                    <span x-text="metric.variationPct >= 0 ? '+' : ''"></span><span x-text="metric.variationPct.toFixed(1)"></span>%
                                </p>
                                <p class="text-xs text-secondary-500 mt-1" x-text="formatCurrency(metric.variation)"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Chart View -->
    <template x-if="!loading && comparisonType === 'chart' && data.length > 0">
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Évolution</h3>
            </div>
            <div class="card-body">
                <div x-ref="chartContainer" style="min-height: 400px;"></div>
            </div>
        </div>
    </template>

    <!-- Financial Ratios (for P&L) -->
    <template x-if="!loading && reportType === 'profit_loss' && data.length > 0">
        <div class="card mt-6">
            <div class="card-header">
                <h3 class="font-semibold">Ratios Financiers</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template x-for="ratio in calculateRatios()" :key="ratio.label">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300" x-text="ratio.label"></span>
                                <span class="text-xs text-secondary-500" x-text="ratio.description"></span>
                            </div>
                            <div class="flex items-center gap-4">
                                <template x-for="(value, index) in ratio.values" :key="index">
                                    <div>
                                        <p class="text-xl font-bold text-secondary-900 dark:text-white" x-text="value.toFixed(1) + '%'"></p>
                                        <p class="text-xs text-secondary-500" x-text="data[index]?.label"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <!-- No Data -->
    <template x-if="!loading && data.length === 0">
        <div class="card">
            <div class="card-body text-center py-12">
                <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-secondary-500">Aucune donnée disponible pour la période sélectionnée</p>
            </div>
        </div>
    </template>
</div>

<script>
function periodComparison(reportType, defaultPeriods) {
    return {
        reportType: reportType,
        selectedPreset: defaultPeriods,
        customStart: null,
        customEnd: null,
        comparisonType: 'side_by_side',
        loading: false,
        data: [],
        chart: null,

        init() {
            this.applyPreset();
            this.loadComparison();
        },

        applyPreset() {
            // Will be populated by AJAX call
        },

        async loadComparison() {
            this.loading = true;

            try {
                const response = await fetch('/reports/comparison', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        type: this.reportType,
                        preset: this.selectedPreset,
                        custom_start: this.customStart,
                        custom_end: this.customEnd,
                    }),
                });

                const result = await response.json();
                this.data = result.data || [];

                if (this.comparisonType === 'chart') {
                    this.$nextTick(() => this.renderChart());
                }
            } catch (error) {
                console.error('Error loading comparison:', error);
                window.showToast?.('Erreur lors du chargement des données', 'error');
            } finally {
                this.loading = false;
            }
        },

        getComparisonRows() {
            if (!this.data.length) return [];

            const rows = [];

            // Revenue section
            rows.push({ label: 'PRODUITS', isBold: true, values: this.data.map(() => '') });
            rows.push({ label: 'Ventes', indent: true, values: this.data.map(p => p.revenue?.sales || 0) });
            rows.push({ label: 'Produits financiers', indent: true, values: this.data.map(p => p.revenue?.financial_income || 0) });
            rows.push({ label: 'Autres produits', indent: true, values: this.data.map(p => p.revenue?.other_income || 0) });
            rows.push({ label: 'Total Produits', isBold: true, values: this.data.map(p => p.totals?.revenue || 0) });

            rows.push({ label: '', values: this.data.map(() => '') });

            // Expenses section
            rows.push({ label: 'CHARGES', isBold: true, values: this.data.map(() => '') });
            rows.push({ label: 'Achats', indent: true, values: this.data.map(p => p.expenses?.purchases || 0) });
            rows.push({ label: 'Services', indent: true, values: this.data.map(p => p.expenses?.services || 0) });
            rows.push({ label: 'Salaires', indent: true, values: this.data.map(p => p.expenses?.salaries || 0) });
            rows.push({ label: 'Amortissements', indent: true, values: this.data.map(p => p.expenses?.depreciation || 0) });
            rows.push({ label: 'Autres charges', indent: true, values: this.data.map(p => p.expenses?.other_expenses || 0) });
            rows.push({ label: 'Total Charges', isBold: true, values: this.data.map(p => p.totals?.expenses || 0) });

            rows.push({ label: '', values: this.data.map(() => '') });
            rows.push({ label: 'RÉSULTAT NET', isBold: true, values: this.data.map(p => p.totals?.net_profit || 0) });

            return rows;
        },

        getBalanceSheetRows() {
            if (!this.data.length) return [];

            const rows = [];

            // Assets
            rows.push({ label: 'ACTIF', isBold: true, values: this.data.map(() => '') });
            rows.push({ label: 'Actifs immobilisés', indent: true, values: this.data.map(p => p.assets?.fixed_assets || 0) });
            rows.push({ label: 'Stocks', indent: true, values: this.data.map(p => p.assets?.current_assets?.inventory || 0) });
            rows.push({ label: 'Créances', indent: true, values: this.data.map(p => p.assets?.current_assets?.receivables || 0) });
            rows.push({ label: 'Trésorerie', indent: true, values: this.data.map(p => p.assets?.current_assets?.cash || 0) });
            rows.push({ label: 'Total Actif', isBold: true, values: this.data.map(p => p.totals?.assets || 0) });

            rows.push({ label: '', values: this.data.map(() => '') });

            // Liabilities
            rows.push({ label: 'PASSIF', isBold: true, values: this.data.map(() => '') });
            rows.push({ label: 'Capitaux propres', indent: true, values: this.data.map(p => p.liabilities?.equity || 0) });
            rows.push({ label: 'Dettes long terme', indent: true, values: this.data.map(p => p.liabilities?.long_term_debt || 0) });
            rows.push({ label: 'Dettes court terme', indent: true, values: this.data.map(p => p.liabilities?.current_liabilities?.short_term_debt || 0) });
            rows.push({ label: 'Total Passif', isBold: true, values: this.data.map(p => p.totals?.liabilities || 0) });

            return rows;
        },

        getKeyMetrics() {
            if (this.data.length < 2) return [];

            const current = this.data[this.data.length - 1];
            const previous = this.data[this.data.length - 2];

            const metrics = [];

            if (this.reportType === 'profit_loss') {
                const revenueVar = current.totals.revenue - previous.totals.revenue;
                const revenueVarPct = previous.totals.revenue > 0 ? (revenueVar / previous.totals.revenue) * 100 : 0;

                metrics.push({
                    label: 'Chiffre d\'affaires',
                    current: current.totals.revenue,
                    previous: previous.totals.revenue,
                    variation: revenueVar,
                    variationPct: revenueVarPct,
                    trend: revenueVar > 0 ? 'up' : revenueVar < 0 ? 'down' : 'neutral',
                });

                const profitVar = current.totals.net_profit - previous.totals.net_profit;
                const profitVarPct = previous.totals.net_profit !== 0 ? (profitVar / Math.abs(previous.totals.net_profit)) * 100 : 0;

                metrics.push({
                    label: 'Résultat net',
                    current: current.totals.net_profit,
                    previous: previous.totals.net_profit,
                    variation: profitVar,
                    variationPct: profitVarPct,
                    trend: profitVar > 0 ? 'up' : profitVar < 0 ? 'down' : 'neutral',
                });

                const marginVar = current.ratios.net_margin_pct - previous.ratios.net_margin_pct;

                metrics.push({
                    label: 'Marge nette',
                    current: current.ratios.net_margin_pct,
                    previous: previous.ratios.net_margin_pct,
                    variation: marginVar,
                    variationPct: marginVar,
                    trend: marginVar > 0 ? 'up' : marginVar < 0 ? 'down' : 'neutral',
                });
            }

            return metrics;
        },

        calculateRatios() {
            if (this.reportType !== 'profit_loss' || !this.data.length) return [];

            return [
                {
                    label: 'Marge brute',
                    description: 'Rentabilité des ventes',
                    values: this.data.map(p => p.ratios?.gross_margin_pct || 0),
                },
                {
                    label: 'Marge EBITDA',
                    description: 'Performance opérationnelle',
                    values: this.data.map(p => p.ratios?.ebitda_margin_pct || 0),
                },
                {
                    label: 'Marge nette',
                    description: 'Rentabilité finale',
                    values: this.data.map(p => p.ratios?.net_margin_pct || 0),
                },
            ];
        },

        renderChart() {
            if (!this.$refs.chartContainer || !window.ApexCharts) return;

            const categories = this.data.map(p => p.label);
            let series = [];

            if (this.reportType === 'profit_loss') {
                series = [
                    {
                        name: 'Produits',
                        data: this.data.map(p => p.totals?.revenue || 0),
                    },
                    {
                        name: 'Charges',
                        data: this.data.map(p => p.totals?.expenses || 0),
                    },
                    {
                        name: 'Résultat net',
                        data: this.data.map(p => p.totals?.net_profit || 0),
                    },
                ];
            } else if (this.reportType === 'balance_sheet') {
                series = [
                    {
                        name: 'Actif',
                        data: this.data.map(p => p.totals?.assets || 0),
                    },
                    {
                        name: 'Passif',
                        data: this.data.map(p => p.totals?.liabilities || 0),
                    },
                ];
            }

            const options = {
                chart: {
                    type: 'line',
                    height: 400,
                    toolbar: { show: true },
                },
                series: series,
                xaxis: {
                    categories: categories,
                },
                yaxis: {
                    labels: {
                        formatter: (value) => this.formatCurrency(value),
                    },
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                markers: {
                    size: 5,
                },
                tooltip: {
                    y: {
                        formatter: (value) => this.formatCurrency(value),
                    },
                },
                colors: ['#3b82f6', '#ef4444', '#10b981'],
            };

            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new ApexCharts(this.$refs.chartContainer, options);
            this.chart.render();
        },

        formatCurrency(value) {
            if (value === null || value === undefined || value === '') return '-';
            return new Intl.NumberFormat('fr-BE', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(value);
        },
    };
}
</script>
