<x-admin-layout>
    <x-slot name="title">Analytics & Statistiques</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Analytics & Statistiques</span>
            <div class="flex gap-2">
                <button onclick="refreshAnalytics()" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Rafraîchir
                </button>
                <a href="{{ route('admin.analytics.export', ['format' => 'csv']) }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exporter
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Global Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Companies Card -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-secondary-400">Entreprises</h3>
                <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <p class="text-3xl font-bold text-white" x-show="show" x-transition>{{ number_format($stats['companies']['total']) }}</p>
                    <p class="text-xs text-secondary-500">Total</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div>
                        <p class="text-success-400 font-medium">{{ number_format($stats['companies']['active']) }}</p>
                        <p class="text-xs text-secondary-500">Actives</p>
                    </div>
                    <div>
                        <p class="text-white font-medium">+{{ $stats['companies']['new_this_month'] }}</p>
                        <p class="text-xs text-secondary-500">Ce mois</p>
                    </div>
                    <div class="flex-1 text-right">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $stats['companies']['growth_rate'] > 0 ? 'bg-success-500/20 text-success-400' : 'bg-secondary-700 text-secondary-400' }}">
                            +{{ $stats['companies']['growth_rate'] }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 200)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-secondary-400">Utilisateurs</h3>
                <div class="w-10 h-10 bg-success-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <p class="text-3xl font-bold text-white" x-show="show" x-transition>{{ number_format($stats['users']['total']) }}</p>
                    <p class="text-xs text-secondary-500">Total</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div>
                        <p class="text-success-400 font-medium">{{ number_format($stats['users']['active']) }}</p>
                        <p class="text-xs text-secondary-500">Actifs</p>
                    </div>
                    <div>
                        <p class="text-white font-medium">+{{ $stats['users']['new_this_month'] }}</p>
                        <p class="text-xs text-secondary-500">Ce mois</p>
                    </div>
                    <div class="flex-1 text-right">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">
                            {{ $stats['users']['activity_rate'] }}% actifs
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Card -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 300)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-secondary-400">Facturation</h3>
                <div class="w-10 h-10 bg-warning-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <p class="text-3xl font-bold text-white" x-show="show" x-transition>{{ number_format($stats['business']['total_invoices']) }}</p>
                    <p class="text-xs text-secondary-500">Factures totales</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div>
                        <p class="text-white font-medium">{{ number_format($stats['business']['invoices_this_month']) }}</p>
                        <p class="text-xs text-secondary-500">Ce mois</p>
                    </div>
                    <div class="flex-1 text-right">
                        <p class="text-success-400 font-medium">{{ number_format($stats['business']['average_invoice'], 2) }}€</p>
                        <p class="text-xs text-secondary-500">Moyenne</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-secondary-800 rounded-xl border border-success-500/20 p-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 400)">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-secondary-400">Revenus</h3>
                <div class="w-10 h-10 bg-success-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="space-y-2">
                <div>
                    <p class="text-3xl font-bold text-success-400" x-show="show" x-transition>{{ number_format($stats['business']['total_revenue'], 0) }}€</p>
                    <p class="text-xs text-secondary-500">Total</p>
                </div>
                <div>
                    <p class="text-white font-medium">+{{ number_format($stats['business']['revenue_this_month'], 0) }}€</p>
                    <p class="text-xs text-secondary-500">Ce mois</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Trends Chart -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Tendances (12 derniers mois)</h3>
            <canvas id="trendsChart" height="300"></canvas>
        </div>

        <!-- System Health -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Santé Système</h3>
            <div class="space-y-4">
                <!-- Database -->
                <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-success-500/20' : 'bg-danger-500/20' }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $systemHealth['database']['status'] === 'healthy' ? 'text-success-400' : 'text-danger-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">Base de données</p>
                            @if(isset($systemHealth['database']['response_time']))
                                <p class="text-xs text-secondary-500">{{ $systemHealth['database']['response_time'] }}ms</p>
                            @endif
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-success-500/20 text-success-400' : 'bg-danger-500/20 text-danger-400' }}">
                        {{ ucfirst($systemHealth['database']['status']) }}
                    </span>
                </div>

                <!-- Cache -->
                <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-success-500/20' : 'bg-danger-500/20' }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $systemHealth['cache']['status'] === 'healthy' ? 'text-success-400' : 'text-danger-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">Cache</p>
                            <p class="text-xs text-secondary-500">{{ $systemHealth['cache']['driver'] }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-success-500/20 text-success-400' : 'bg-danger-500/20 text-danger-400' }}">
                        {{ ucfirst($systemHealth['cache']['status']) }}
                    </span>
                </div>

                <!-- Queue -->
                <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $systemHealth['queue']['status'] === 'healthy' ? 'bg-success-500/20' : 'bg-warning-500/20' }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $systemHealth['queue']['status'] === 'healthy' ? 'text-success-400' : 'text-warning-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">Files d'attente</p>
                            <p class="text-xs text-secondary-500">{{ $systemHealth['queue']['failed_jobs'] }} échecs</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $systemHealth['queue']['status'] === 'healthy' ? 'bg-success-500/20 text-success-400' : 'bg-warning-500/20 text-warning-400' }}">
                        {{ ucfirst($systemHealth['queue']['status']) }}
                    </span>
                </div>

                <!-- Storage -->
                <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $systemHealth['storage']['status'] === 'healthy' ? 'bg-success-500/20' : 'bg-warning-500/20' }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $systemHealth['storage']['status'] === 'healthy' ? 'text-success-400' : 'text-warning-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">Stockage</p>
                            @if(isset($systemHealth['storage']['used_percentage']))
                                <p class="text-xs text-secondary-500">{{ $systemHealth['storage']['used_percentage'] }}% utilisé</p>
                            @endif
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $systemHealth['storage']['status'] === 'healthy' ? 'bg-success-500/20 text-success-400' : 'bg-warning-500/20 text-warning-400' }}">
                        {{ ucfirst($systemHealth['storage']['status']) }}
                    </span>
                </div>

                <!-- System Errors -->
                <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $stats['system']['errors_critical'] > 0 ? 'bg-danger-500/20' : 'bg-success-500/20' }} flex items-center justify-center">
                            <svg class="w-4 h-4 {{ $stats['system']['errors_critical'] > 0 ? 'text-danger-400' : 'text-success-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white">Erreurs système</p>
                            <p class="text-xs text-secondary-500">{{ $stats['system']['errors_unresolved'] }} non résolues</p>
                        </div>
                    </div>
                    @if($stats['system']['errors_critical'] > 0)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">
                            {{ $stats['system']['errors_critical'] }} critiques
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">
                            OK
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Companies & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Companies -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Top Entreprises (Revenus)</h3>
            <div class="space-y-2">
                @forelse($topCompanies['by_revenue']->take(5) as $company)
                    <div class="flex items-center justify-between p-3 bg-secondary-900 rounded-lg hover:bg-secondary-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center font-bold text-primary-400 text-sm">
                                {{ substr($company->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ $company->name }}</p>
                                <p class="text-xs text-secondary-500">{{ $company->vat_number }}</p>
                            </div>
                        </div>
                        <p class="text-success-400 font-bold">{{ number_format($company->total_revenue ?? 0, 0) }}€</p>
                    </div>
                @empty
                    <p class="text-center text-secondary-500 py-8">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Activité Récente</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($recentActivity as $activity)
                    <div class="flex items-start gap-3 p-3 bg-secondary-900 rounded-lg">
                        <div class="w-2 h-2 rounded-full bg-primary-400 mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white">{{ $activity['description'] }}</p>
                            <div class="flex items-center gap-2 mt-1 text-xs text-secondary-500">
                                @if($activity['user'])
                                    <span>{{ $activity['user'] }}</span>
                                    <span>•</span>
                                @endif
                                @if($activity['company'])
                                    <span>{{ $activity['company'] }}</span>
                                    <span>•</span>
                                @endif
                                <span>{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-secondary-500 py-8">Aucune activité récente</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: @json($trends['labels']),
                datasets: [
                    {
                        label: 'Entreprises',
                        data: @json($trends['companies']),
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Utilisateurs',
                        data: @json($trends['users']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Factures',
                        data: @json($trends['invoices']),
                        borderColor: 'rgb(251, 191, 36)',
                        backgroundColor: 'rgba(251, 191, 36, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#9CA3AF'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#9CA3AF'
                        },
                        grid: {
                            color: '#374151'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9CA3AF'
                        },
                        grid: {
                            color: '#374151'
                        }
                    }
                }
            }
        });

        // Refresh function
        function refreshAnalytics() {
            window.location.reload();
        }

        // Auto-refresh every 5 minutes
        setTimeout(() => {
            refreshAnalytics();
        }, 300000);
    </script>
    @endpush
</x-admin-layout>
