import Alpine from 'alpinejs';

Alpine.data('dashboardCustomizer', () => ({
    widgets: {
        stats: { enabled: true, order: 1, title: 'Statistiques clés' },
        cashFlow: { enabled: true, order: 2, title: 'Flux de trésorerie' },
        revenueChart: { enabled: true, order: 3, title: 'Évolution CA' },
        actionItems: { enabled: true, order: 4, title: 'Actions requises' },
        recentInvoices: { enabled: true, order: 5, title: 'Factures récentes' },
        overdueInvoices: { enabled: true, order: 6, title: 'Factures en retard' },
        topClients: { enabled: true, order: 7, title: 'Top clients' },
        expenseBreakdown: { enabled: true, order: 8, title: 'Répartition charges' },
        pendingTransactions: { enabled: true, order: 9, title: 'Transactions à rapprocher' },
    },
    showCustomizer: false,
    loading: false,

    init() {
        // Load saved preferences
        const saved = localStorage.getItem('dashboard_widgets');
        if (saved) {
            try {
                this.widgets = { ...this.widgets, ...JSON.parse(saved) };
            } catch (e) {
                console.error('Failed to load dashboard preferences:', e);
            }
        }

        // Load charts after a brief delay to show skeletons first
        setTimeout(() => {
            this.loadCharts();
        }, 500);
    },

    toggleWidget(widgetKey) {
        this.widgets[widgetKey].enabled = !this.widgets[widgetKey].enabled;
        this.savePreferences();
    },

    savePreferences() {
        localStorage.setItem('dashboard_widgets', JSON.stringify(this.widgets));
    },

    resetToDefault() {
        Object.keys(this.widgets).forEach(key => {
            this.widgets[key].enabled = true;
        });
        this.savePreferences();
        window.location.reload();
    },

    get enabledWidgets() {
        return Object.entries(this.widgets)
            .filter(([_, widget]) => widget.enabled)
            .sort(([_, a], [__, b]) => a.order - b.order)
            .map(([key]) => key);
    },

    loadCharts() {
        // Revenue Chart
        if (this.widgets.revenueChart.enabled && window.revenueChartData) {
            this.renderRevenueChart();
        }

        // Cash Flow Chart
        if (this.widgets.cashFlow.enabled && window.cashFlowData) {
            this.renderCashFlowChart();
        }

        // Expense Breakdown Chart
        if (this.widgets.expenseBreakdown.enabled && window.expenseData) {
            this.renderExpenseChart();
        }
    },

    renderRevenueChart() {
        const data = window.revenueChartData;
        if (!data) return;

        const options = {
            series: [{
                name: 'Revenus',
                data: data.revenue
            }, {
                name: 'Dépenses',
                data: data.expenses
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: false,
                    }
                },
                zoom: { enabled: true }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                }
            },
            xaxis: {
                categories: data.labels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-BE', {
                            style: 'currency',
                            currency: 'EUR',
                            minimumFractionDigits: 0
                        }).format(val);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-BE', {
                            style: 'currency',
                            currency: 'EUR'
                        }).format(val);
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            colors: ['#3b82f6', '#f59e0b']
        };

        const chart = new ApexCharts(document.querySelector("#revenue-chart"), options);
        chart.render();
    },

    renderCashFlowChart() {
        const data = window.cashFlowData;
        if (!data) return;

        const options = {
            series: [{
                name: 'Solde',
                data: data.balances
            }, {
                name: 'Entrées',
                data: data.inflows
            }, {
                name: 'Sorties',
                data: data.outflows
            }],
            chart: {
                height: 300,
                type: 'line',
                toolbar: { show: false }
            },
            stroke: {
                curve: 'smooth',
                width: [3, 2, 2]
            },
            xaxis: {
                categories: data.labels
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-BE', {
                            style: 'currency',
                            currency: 'EUR',
                            minimumFractionDigits: 0
                        }).format(val);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-BE', {
                            style: 'currency',
                            currency: 'EUR'
                        }).format(val);
                    }
                }
            },
            colors: ['#3b82f6', '#10b981', '#ef4444']
        };

        const chart = new ApexCharts(document.querySelector("#cashflow-chart"), options);
        chart.render();
    },

    renderExpenseChart() {
        const data = window.expenseData;
        if (!data || data.length === 0) return;

        const options = {
            series: data.map(item => item.total),
            chart: {
                type: 'donut',
                height: 300
            },
            labels: data.map(item => item.category),
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function(w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return new Intl.NumberFormat('fr-BE', {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 0
                                    }).format(total);
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-BE', {
                            style: 'currency',
                            currency: 'EUR'
                        }).format(val);
                    }
                }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
        };

        const chart = new ApexCharts(document.querySelector("#expense-chart"), options);
        chart.render();
    }
}));

// Counter animation for stat cards
Alpine.directive('counter', (el, { expression }, { evaluate }) => {
    const targetValue = evaluate(expression);
    const duration = 2000; // 2 seconds
    const startTime = Date.now();
    const startValue = 0;

    const animate = () => {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function (easeOutExpo)
        const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);

        const currentValue = startValue + (targetValue - startValue) * easeProgress;

        // Format based on data attributes
        const decimals = el.dataset.decimals ? parseInt(el.dataset.decimals) : 0;
        const suffix = el.dataset.suffix || '';
        const prefix = el.dataset.prefix || '';

        el.textContent = prefix + currentValue.toLocaleString('fr-BE', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }) + suffix;

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };

    // Start animation after a brief delay
    setTimeout(() => requestAnimationFrame(animate), 100);
});

// Initialize counters on page load
document.addEventListener('alpine:init', () => {
    // Counter animations will be handled by Alpine directive
});
