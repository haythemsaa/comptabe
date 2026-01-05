@extends('layouts.app')

@section('title', 'Analyse des revenus')

@section('content')
<div x-data="revenueAnalytics()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-secondary-500 dark:text-secondary-400 mb-1">
                <a href="{{ route('analytics.index') }}" class="hover:text-primary-600">Analytics</a>
                <span>/</span>
                <span>Revenus</span>
            </div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Analyse des revenus
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Suivi détaillé de votre chiffre d'affaires
            </p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="year" @change="updateYear()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('analytics.export', ['type' => 'revenue', 'year' => $year]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">CA Total {{ $year }}</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyRevenue)->sum('total'), 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Moyenne mensuelle</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyRevenue)->avg('total') ?? 0, 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Meilleur mois</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyRevenue)->max('total') ?? 0, 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Clients actifs</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ count($revenueByClient ?? []) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Revenue Chart -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Évolution mensuelle</h3>
            <div class="h-80">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <!-- Revenue by Category -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Par catégorie</h3>
            <div class="h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Clients -->
    <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700">
        <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Top clients par chiffre d'affaires</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50 dark:bg-secondary-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">CA Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Factures</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">% du CA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                    @php $totalRevenue = collect($revenueByClient)->sum('total'); @endphp
                    @forelse($revenueByClient ?? [] as $client)
                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-secondary-900 dark:text-white">{{ $client['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-secondary-900 dark:text-white">
                            {{ number_format($client['total'], 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 text-right text-secondary-600 dark:text-secondary-400">
                            {{ $client['count'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                {{ $totalRevenue > 0 ? number_format(($client['total'] / $totalRevenue) * 100, 1) : 0 }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-secondary-500 dark:text-secondary-400">
                            Aucune donnée disponible pour cette période
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Forecast -->
    @if(!empty($forecast))
    <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Prévisions</h3>
        <div class="h-64">
            <canvas id="forecastChart"></canvas>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function revenueAnalytics() {
    return {
        year: {{ $year }},
        updateYear() {
            window.location.href = '{{ route("analytics.revenue") }}?year=' + this.year;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyRevenue ?? []);
    const categoryData = @json($revenueByCategory ?? []);

    // Monthly Revenue Chart
    if (document.getElementById('monthlyRevenueChart')) {
        new Chart(document.getElementById('monthlyRevenueChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Chiffre d\'affaires',
                    data: monthlyData.map(d => d.total),
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString('fr-BE') + ' €'
                        }
                    }
                }
            }
        });
    }

    // Category Chart
    if (document.getElementById('categoryChart') && categoryData.length > 0) {
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(d => d.name || 'Non catégorisé'),
                datasets: [{
                    data: categoryData.map(d => d.total),
                    backgroundColor: [
                        '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
});
</script>
@endpush
