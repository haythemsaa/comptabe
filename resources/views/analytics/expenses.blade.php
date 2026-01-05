@extends('layouts.app')

@section('title', 'Analyse des dépenses')

@section('content')
<div x-data="expensesAnalytics()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-secondary-500 dark:text-secondary-400 mb-1">
                <a href="{{ route('analytics.index') }}" class="hover:text-primary-600">Analytics</a>
                <span>/</span>
                <span>Dépenses</span>
            </div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Analyse des dépenses
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Suivi détaillé de vos charges et dépenses
            </p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="year" @change="updateYear()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('analytics.export', ['type' => 'expenses', 'year' => $year]) }}"
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
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Dépenses totales {{ $year }}</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyExpenses)->sum('total'), 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Moyenne mensuelle</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyExpenses)->avg('total') ?? 0, 0, ',', ' ') }} €
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
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Mois le plus élevé</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format(collect($monthlyExpenses)->max('total') ?? 0, 0, ',', ' ') }} €
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Fournisseurs</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ count($expensesBySupplier ?? []) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Expenses Chart -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Évolution mensuelle</h3>
            <div class="h-80">
                <canvas id="monthlyExpensesChart"></canvas>
            </div>
        </div>

        <!-- Expenses by Category -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Répartition par catégorie</h3>
            <div class="h-80">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Suppliers -->
    <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700">
        <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Principaux fournisseurs</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50 dark:bg-secondary-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Fournisseur</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Total dépenses</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Factures</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">% des dépenses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                    @php $totalExpenses = collect($expensesBySupplier)->sum('total'); @endphp
                    @forelse($expensesBySupplier ?? [] as $supplier)
                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-secondary-900 dark:text-white">{{ $supplier['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-secondary-900 dark:text-white">
                            {{ number_format($supplier['total'], 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 text-right text-secondary-600 dark:text-secondary-400">
                            {{ $supplier['count'] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                {{ $totalExpenses > 0 ? number_format(($supplier['total'] / $totalExpenses) * 100, 1) : 0 }}%
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

    <!-- Trend Analysis -->
    @if(!empty($trend))
    <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Tendance des dépenses</h3>
        <div class="h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function expensesAnalytics() {
    return {
        year: {{ $year }},
        updateYear() {
            window.location.href = '{{ route("analytics.expenses") }}?year=' + this.year;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyExpenses ?? []);
    const categoryData = @json($expensesByCategory ?? []);

    // Monthly Expenses Chart
    if (document.getElementById('monthlyExpensesChart')) {
        new Chart(document.getElementById('monthlyExpensesChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Dépenses',
                    data: monthlyData.map(d => d.total),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
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
                        '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6',
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
