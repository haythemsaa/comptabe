@extends('layouts.app')

@section('title', 'Analyse de rentabilité')

@section('content')
<div x-data="profitabilityAnalytics()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-secondary-500 dark:text-secondary-400 mb-1">
                <a href="{{ route('analytics.index') }}" class="hover:text-primary-600">Analytics</a>
                <span>/</span>
                <span>Rentabilité</span>
            </div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Analyse de rentabilité
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Suivi de la performance et des marges
            </p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="year" @change="updateYear()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Main KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br {{ ($kpis['net_profit'] ?? 0) >= 0 ? 'from-emerald-500 to-emerald-600' : 'from-red-500 to-red-600' }} rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white/80 text-sm">Résultat net</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($kpis['net_profit'] ?? 0, 0, ',', ' ') }} €</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Marge brute</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format($kpis['gross_margin'] ?? 0, 1) }}%
                    </p>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">Marge nette</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format($kpis['net_margin'] ?? 0, 1) }}%
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow-sm border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-500 dark:text-secondary-400 text-sm">ROI</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">
                        {{ number_format($kpis['roi'] ?? 0, 1) }}%
                    </p>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Profitability Chart -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Évolution mensuelle</h3>
            <div class="h-80">
                <canvas id="monthlyProfitChart"></canvas>
            </div>
        </div>

        <!-- Revenue vs Expenses -->
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700 p-6">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Revenus vs Dépenses</h3>
            <div class="h-80">
                <canvas id="revenueExpensesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Client Profitability -->
    <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm border border-secondary-200 dark:border-secondary-700">
        <div class="p-6 border-b border-secondary-200 dark:border-secondary-700">
            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Rentabilité par client</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50 dark:bg-secondary-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">CA</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Coûts</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Marge</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">% Marge</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                    @forelse($clientProfitability ?? [] as $client)
                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-secondary-900 dark:text-white">{{ $client['name'] }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-secondary-600 dark:text-secondary-400">
                            {{ number_format($client['revenue'] ?? 0, 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 text-right text-secondary-600 dark:text-secondary-400">
                            {{ number_format($client['costs'] ?? 0, 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 text-right font-medium {{ ($client['margin'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($client['margin'] ?? 0, 2, ',', ' ') }} €
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php $marginPercent = ($client['revenue'] ?? 0) > 0 ? (($client['margin'] ?? 0) / $client['revenue']) * 100 : 0; @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $marginPercent >= 20 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : ($marginPercent >= 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                                {{ number_format($marginPercent, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-secondary-500 dark:text-secondary-400">
                            Aucune donnée disponible pour cette période
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function profitabilityAnalytics() {
    return {
        year: {{ $year }},
        updateYear() {
            window.location.href = '{{ route("analytics.profitability") }}?year=' + this.year;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyProfitability ?? []);

    // Monthly Profitability Chart
    if (document.getElementById('monthlyProfitChart')) {
        new Chart(document.getElementById('monthlyProfitChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Résultat',
                    data: monthlyData.map(d => d.profit),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: {
                            callback: value => value.toLocaleString('fr-BE') + ' €'
                        }
                    }
                }
            }
        });
    }

    // Revenue vs Expenses Chart
    if (document.getElementById('revenueExpensesChart')) {
        new Chart(document.getElementById('revenueExpensesChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [
                    {
                        label: 'Revenus',
                        data: monthlyData.map(d => d.revenue),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Dépenses',
                        data: monthlyData.map(d => d.expenses),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
});
</script>
@endpush
