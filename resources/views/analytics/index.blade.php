@extends('layouts.app')

@section('title', 'Tableau de bord analytique')

@section('content')
<div x-data="analyticsApp()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Tableau de bord analytique
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Vue d'ensemble de la performance de votre entreprise
            </p>
        </div>
        <div class="flex items-center gap-3">
            <select x-model="period" @change="updatePeriod()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                <option value="month">Ce mois</option>
                <option value="quarter">Ce trimestre</option>
                <option value="year" selected>Cette année</option>
            </select>
            <select x-model="year" @change="updatePeriod()"
                    class="rounded-lg border-secondary-300 dark:border-secondary-600 dark:bg-secondary-800 text-sm">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter
                </button>
                <div x-show="open" @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-secondary-800 rounded-lg shadow-lg border border-secondary-200 dark:border-secondary-700 py-1 z-50">
                    <a href="{{ route('analytics.export', ['type' => 'summary', 'year' => $year]) }}"
                       class="block px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700">
                        Résumé (CSV)
                    </a>
                    <a href="{{ route('analytics.export', ['type' => 'revenue', 'year' => $year]) }}"
                       class="block px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700">
                        Revenus (CSV)
                    </a>
                    <a href="{{ route('analytics.export', ['type' => 'expenses', 'year' => $year]) }}"
                       class="block px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700">
                        Dépenses (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs principaux -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm">Chiffre d'affaires</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($kpis['revenue'], 0, ',', ' ') }} €</p>
                    @if(isset($comparison['revenue']))
                        <p class="text-sm mt-1 {{ $comparison['revenue']['change'] >= 0 ? 'text-emerald-200' : 'text-red-200' }}">
                            {{ $comparison['revenue']['change'] >= 0 ? '+' : '' }}{{ number_format($comparison['revenue']['change'], 1) }}% vs période préc.
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Dépenses</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($kpis['expenses'], 0, ',', ' ') }} €</p>
                    @if(isset($comparison['expenses']))
                        <p class="text-sm mt-1 {{ $comparison['expenses']['change'] <= 0 ? 'text-green-200' : 'text-red-200' }}">
                            {{ $comparison['expenses']['change'] >= 0 ? '+' : '' }}{{ number_format($comparison['expenses']['change'], 1) }}% vs période préc.
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br {{ $kpis['profit'] >= 0 ? 'from-blue-500 to-blue-600' : 'from-orange-500 to-orange-600' }} rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="{{ $kpis['profit'] >= 0 ? 'text-blue-100' : 'text-orange-100' }} text-sm">Résultat</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($kpis['profit'], 0, ',', ' ') }} €</p>
                    <p class="text-sm mt-1 {{ $kpis['profit'] >= 0 ? 'text-blue-200' : 'text-orange-200' }}">
                        Marge: {{ $kpis['gross_margin'] }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Factures impayées</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($kpis['unpaid_invoices'], 0, ',', ' ') }} €</p>
                    <p class="text-sm mt-1 text-purple-200">
                        {{ $kpis['invoices_count'] }} factures
                    </p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques secondaires -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Clients actifs</p>
                    <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ $kpis['active_clients'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Délai moyen paiement</p>
                    <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ $kpis['avg_payment_delay'] }} jours</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Factures émises</p>
                    <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ $kpis['invoices_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-5 shadow border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Panier moyen</p>
                    <p class="text-xl font-bold text-secondary-900 dark:text-white">
                        {{ $kpis['invoices_count'] > 0 ? number_format($kpis['revenue'] / $kpis['invoices_count'], 0, ',', ' ') : 0 }} €
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Évolution CA -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">Évolution du chiffre d'affaires</h3>
            </x-slot:header>
            <div class="h-72">
                <canvas x-ref="revenueChart"></canvas>
            </div>
        </x-card>

        <!-- Cash flow -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">Cash flow</h3>
            </x-slot:header>
            <div class="h-72">
                <canvas x-ref="cashflowChart"></canvas>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top clients -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Top 10 Clients</h3>
                    <a href="{{ route('analytics.revenue') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        Voir détails &rarr;
                    </a>
                </div>
            </x-slot:header>
            @if(empty($topClients))
                <p class="text-center text-secondary-500 py-8">Aucune donnée disponible</p>
            @else
                <div class="space-y-3">
                    @foreach($topClients as $index => $client)
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-xs font-bold flex items-center justify-center">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $client['partner'] }}
                                </p>
                                <p class="text-xs text-secondary-500">{{ $client['invoice_count'] }} factures</p>
                            </div>
                            <span class="font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($client['total'], 0, ',', ' ') }} €
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Top fournisseurs -->
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Top 10 Fournisseurs</h3>
                    <a href="{{ route('analytics.expenses') }}" class="text-sm text-primary-600 hover:text-primary-700">
                        Voir détails &rarr;
                    </a>
                </div>
            </x-slot:header>
            @if(empty($topSuppliers))
                <p class="text-center text-secondary-500 py-8">Aucune donnée disponible</p>
            @else
                <div class="space-y-3">
                    @foreach($topSuppliers as $index => $supplier)
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 text-xs font-bold flex items-center justify-center">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $supplier['partner'] }}
                                </p>
                                <p class="text-xs text-secondary-500">{{ $supplier['invoice_count'] }} factures</p>
                            </div>
                            <span class="font-semibold text-secondary-900 dark:text-white">
                                {{ number_format($supplier['total'], 0, ',', ' ') }} €
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Aging Report -->
        <x-card>
            <x-slot:header>
                <h3 class="font-semibold text-secondary-900 dark:text-white">Échéancier créances</h3>
            </x-slot:header>
            <div class="space-y-3">
                @php
                    $agingColors = [
                        'current' => 'green',
                        '1_30' => 'yellow',
                        '31_60' => 'orange',
                        '61_90' => 'red',
                        'over_90' => 'red',
                    ];
                    $agingLabels = [
                        'current' => 'Non échu',
                        '1_30' => '1-30 jours',
                        '31_60' => '31-60 jours',
                        '61_90' => '61-90 jours',
                        'over_90' => '> 90 jours',
                    ];
                    $totalAging = array_sum(array_column($aging, 'amount'));
                @endphp
                @foreach($aging as $key => $data)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-secondary-600 dark:text-secondary-400">{{ $agingLabels[$key] }}</span>
                            <span class="font-medium text-secondary-900 dark:text-white">
                                {{ number_format($data['amount'], 0, ',', ' ') }} €
                                <span class="text-secondary-500">({{ $data['count'] }})</span>
                            </span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-{{ $agingColors[$key] }}-500 h-2 rounded-full transition-all duration-500"
                                 style="width: {{ $totalAging > 0 ? ($data['amount'] / $totalAging) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <!-- Répartition des dépenses -->
    <x-card>
        <x-slot:header>
            <h3 class="font-semibold text-secondary-900 dark:text-white">Répartition des dépenses par catégorie</h3>
        </x-slot:header>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="h-64">
                <canvas x-ref="expenseChart"></canvas>
            </div>
            <div class="space-y-3">
                @php
                    $totalExpenses = array_sum(array_column($expenseBreakdown, 'total'));
                    $colors = ['blue', 'purple', 'green', 'yellow', 'red', 'indigo', 'pink', 'cyan'];
                @endphp
                @foreach($expenseBreakdown as $index => $expense)
                    @php $color = $colors[$index % count($colors)]; @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-{{ $color }}-500"></span>
                        <div class="flex-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary-700 dark:text-secondary-300">{{ ucfirst(str_replace('_', ' ', $expense['category'])) }}</span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($expense['total'], 0, ',', ' ') }} €
                                </span>
                            </div>
                            <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-1 mt-1">
                                <div class="bg-{{ $color }}-500 h-1 rounded-full"
                                     style="width: {{ $totalExpenses > 0 ? ($expense['total'] / $totalExpenses) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <span class="text-xs text-secondary-500">
                            {{ $totalExpenses > 0 ? number_format(($expense['total'] / $totalExpenses) * 100, 1) : 0 }}%
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('analytics.revenue') }}"
           class="flex items-center gap-4 p-4 bg-white dark:bg-secondary-800 rounded-xl border border-secondary-200 dark:border-secondary-700 hover:border-primary-500 transition-colors group">
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-secondary-900 dark:text-white">Analyse Revenus</p>
                <p class="text-sm text-secondary-500">Détails et prévisions</p>
            </div>
        </a>

        <a href="{{ route('analytics.expenses') }}"
           class="flex items-center gap-4 p-4 bg-white dark:bg-secondary-800 rounded-xl border border-secondary-200 dark:border-secondary-700 hover:border-primary-500 transition-colors group">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-secondary-900 dark:text-white">Analyse Dépenses</p>
                <p class="text-sm text-secondary-500">Par fournisseur et catégorie</p>
            </div>
        </a>

        <a href="{{ route('analytics.profitability') }}"
           class="flex items-center gap-4 p-4 bg-white dark:bg-secondary-800 rounded-xl border border-secondary-200 dark:border-secondary-700 hover:border-primary-500 transition-colors group">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-secondary-900 dark:text-white">Rentabilité</p>
                <p class="text-sm text-secondary-500">Marges et performance</p>
            </div>
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function analyticsApp() {
    return {
        period: '{{ $period }}',
        year: '{{ $year }}',

        init() {
            this.renderRevenueChart();
            this.renderCashflowChart();
            this.renderExpenseChart();
        },

        updatePeriod() {
            window.location.href = `{{ route('analytics.index') }}?period=${this.period}&year=${this.year}`;
        },

        renderRevenueChart() {
            const ctx = this.$refs.revenueChart.getContext('2d');
            const data = @json($revenueEvolution);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => {
                        const [year, month] = d.period.split('-');
                        return new Date(year, month - 1).toLocaleDateString('fr-BE', { month: 'short', year: '2-digit' });
                    }),
                    datasets: [{
                        label: 'Chiffre d\'affaires',
                        data: data.map(d => d.total),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: value => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    notation: 'compact'
                                }).format(value)
                            }
                        }
                    }
                }
            });
        },

        renderCashflowChart() {
            const ctx = this.$refs.cashflowChart.getContext('2d');
            const data = @json($cashFlow);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => {
                        const [year, month] = d.period.split('-');
                        return new Date(year, month - 1).toLocaleDateString('fr-BE', { month: 'short' });
                    }),
                    datasets: [
                        {
                            label: 'Entrées',
                            data: data.map(d => d.inflows),
                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        },
                        {
                            label: 'Sorties',
                            data: data.map(d => -d.outflows),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        x: { stacked: true },
                        y: {
                            stacked: true,
                            ticks: {
                                callback: value => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    notation: 'compact'
                                }).format(value)
                            }
                        }
                    }
                }
            });
        },

        renderExpenseChart() {
            const ctx = this.$refs.expenseChart.getContext('2d');
            const data = @json($expenseBreakdown);
            const colors = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(234, 179, 8, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(6, 182, 212, 0.8)',
            ];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.category.replace('_', ' ')),
                    datasets: [{
                        data: data.map(d => d.total),
                        backgroundColor: colors.slice(0, data.length),
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection
