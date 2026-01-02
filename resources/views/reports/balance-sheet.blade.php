@extends('layouts.app')

@section('title', 'Bilan Comptable')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">
                    Bilan Comptable
                </h1>
                <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $company->name }} - Au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </p>
            </div>

            <div class="flex gap-3">
                <button onclick="window.print()" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimer
                </button>

                <a href="{{ route('reports.index') }}" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Exporter
                </a>
            </div>
        </div>
    </div>

    @if(isset($report['data']['balanced']) && !$report['data']['balanced'])
        <div class="mb-6 bg-warning-50 border border-warning-200 text-warning-900 px-4 py-3 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="font-semibold">Bilan non équilibré</h3>
                    <p class="text-sm mt-1">L'actif et le passif ne sont pas égaux. Vérifiez vos écritures comptables.</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- ACTIF -->
        <div class="card">
            <div class="card-header bg-primary-50 dark:bg-primary-900/20">
                <h2 class="text-xl font-bold text-primary-900 dark:text-primary-100">
                    ACTIF
                </h2>
            </div>
            <div class="card-body p-0">
                @php
                    $assets = $report['data']['assets'];
                @endphp

                <!-- Actifs immobilisés -->
                @if(isset($assets['categories']['fixed']) && count($assets['categories']['fixed']['items']) > 0)
                <div class="border-b border-secondary-200 dark:border-secondary-700">
                    <div class="bg-secondary-100 dark:bg-secondary-800 px-6 py-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-secondary-900 dark:text-white">
                                {{ $assets['categories']['fixed']['label'] }}
                            </h3>
                            <span class="font-bold text-lg text-secondary-900 dark:text-white">
                                {{ number_format($assets['categories']['fixed']['total'], 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($assets['categories']['fixed']['items'] as $item)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actifs circulants -->
                @if(isset($assets['categories']['current']) && count($assets['categories']['current']['items']) > 0)
                <div>
                    <div class="bg-secondary-100 dark:bg-secondary-800 px-6 py-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-secondary-900 dark:text-white">
                                {{ $assets['categories']['current']['label'] }}
                            </h3>
                            <span class="font-bold text-lg text-secondary-900 dark:text-white">
                                {{ number_format($assets['categories']['current']['total'], 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($assets['categories']['current']['items'] as $item)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Total Actif -->
                <div class="bg-primary-600 dark:bg-primary-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">
                            TOTAL ACTIF
                        </h3>
                        <span class="text-2xl font-bold text-white">
                            {{ number_format($assets['total'], 2, ',', ' ') }} €
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PASSIF -->
        <div class="card">
            <div class="card-header bg-success-50 dark:bg-success-900/20">
                <h2 class="text-xl font-bold text-success-900 dark:text-success-100">
                    PASSIF
                </h2>
            </div>
            <div class="card-body p-0">
                @php
                    $liabilities = $report['data']['liabilities'];
                @endphp

                <!-- Capitaux propres -->
                @if(isset($liabilities['categories']['equity']) && count($liabilities['categories']['equity']['items']) > 0)
                <div class="border-b border-secondary-200 dark:border-secondary-700">
                    <div class="bg-secondary-100 dark:bg-secondary-800 px-6 py-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-secondary-900 dark:text-white">
                                {{ $liabilities['categories']['equity']['label'] }}
                            </h3>
                            <span class="font-bold text-lg text-secondary-900 dark:text-white">
                                {{ number_format($liabilities['categories']['equity']['total'], 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($liabilities['categories']['equity']['items'] as $item)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Provisions -->
                @if(isset($liabilities['categories']['provisions']) && count($liabilities['categories']['provisions']['items']) > 0)
                <div class="border-b border-secondary-200 dark:border-secondary-700">
                    <div class="bg-secondary-100 dark:bg-secondary-800 px-6 py-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-secondary-900 dark:text-white">
                                {{ $liabilities['categories']['provisions']['label'] }}
                            </h3>
                            <span class="font-bold text-lg text-secondary-900 dark:text-white">
                                {{ number_format($liabilities['categories']['provisions']['total'], 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($liabilities['categories']['provisions']['items'] as $item)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Dettes -->
                @if(isset($liabilities['categories']['debts']) && count($liabilities['categories']['debts']['items']) > 0)
                <div>
                    <div class="bg-secondary-100 dark:bg-secondary-800 px-6 py-3">
                        <div class="flex justify-between items-center">
                            <h3 class="font-semibold text-secondary-900 dark:text-white">
                                {{ $liabilities['categories']['debts']['label'] }}
                            </h3>
                            <span class="font-bold text-lg text-secondary-900 dark:text-white">
                                {{ number_format($liabilities['categories']['debts']['total'], 2, ',', ' ') }} €
                            </span>
                        </div>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                        @foreach($liabilities['categories']['debts']['items'] as $item)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-secondary-900 dark:text-white">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Total Passif -->
                <div class="bg-success-600 dark:bg-success-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">
                            TOTAL PASSIF
                        </h3>
                        <span class="text-2xl font-bold text-white">
                            {{ number_format($liabilities['total'], 2, ',', ' ') }} €
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Équilibre du bilan -->
    <div class="mt-6 card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                        Vérification de l'équilibre
                    </h3>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                        Actif = Passif selon le principe de la partie double
                    </p>
                </div>
                <div class="text-right">
                    @if(isset($report['data']['balanced']) && $report['data']['balanced'])
                        <span class="inline-flex items-center px-4 py-2 bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-200 rounded-lg font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Bilan équilibré
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-200 rounded-lg font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Bilan déséquilibré
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Analyse IA -->
    <div class="mt-6 card" x-data="{
        loading: false,
        analysis: null,
        error: null,
        async analyzeFinancials() {
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch('{{ route('reports.analyze-financials') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        date_from: '{{ $dateFrom }}',
                        date_to: '{{ $dateTo }}'
                    })
                });

                if (!response.ok) throw new Error('Erreur lors de l\'analyse');

                const data = await response.json();
                this.analysis = data;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        }
    }">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Analyse Financière IA
                    </h3>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                        Analyse approfondie par intelligence artificielle (Claude AI)
                    </p>
                </div>
                <button
                    @click="analyzeFinancials()"
                    :disabled="loading"
                    class="btn btn-primary"
                >
                    <svg x-show="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <svg x-show="loading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Analyse en cours...' : 'Lancer l\'analyse'"></span>
                </button>
            </div>

            <div x-show="error" class="alert alert-danger mb-4">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span x-text="error"></span>
            </div>

            <div x-show="analysis" x-cloak>
                <!-- Score de santé financière -->
                <div class="mb-6 p-6 rounded-lg" :class="{
                    'bg-success-50 dark:bg-success-900/20': analysis?.health_score?.score >= 80,
                    'bg-info-50 dark:bg-info-900/20': analysis?.health_score?.score >= 60 && analysis?.health_score?.score < 80,
                    'bg-warning-50 dark:bg-warning-900/20': analysis?.health_score?.score >= 40 && analysis?.health_score?.score < 60,
                    'bg-danger-50 dark:bg-danger-900/20': analysis?.health_score?.score < 40
                }">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-bold" :class="{
                                'text-success-900 dark:text-success-100': analysis?.health_score?.score >= 80,
                                'text-info-900 dark:text-info-100': analysis?.health_score?.score >= 60 && analysis?.health_score?.score < 80,
                                'text-warning-900 dark:text-warning-100': analysis?.health_score?.score >= 40 && analysis?.health_score?.score < 60,
                                'text-danger-900 dark:text-danger-100': analysis?.health_score?.score < 40
                            }">
                                Score de Santé Financière
                            </h4>
                            <p class="text-sm mt-1" :class="{
                                'text-success-700 dark:text-success-300': analysis?.health_score?.score >= 80,
                                'text-info-700 dark:text-info-300': analysis?.health_score?.score >= 60 && analysis?.health_score?.score < 80,
                                'text-warning-700 dark:text-warning-300': analysis?.health_score?.score >= 40 && analysis?.health_score?.score < 60,
                                'text-danger-700 dark:text-danger-300': analysis?.health_score?.score < 40
                            }" x-text="analysis?.health_score?.status"></p>
                        </div>
                        <div class="text-right">
                            <div class="text-4xl font-bold" :class="{
                                'text-success-600': analysis?.health_score?.score >= 80,
                                'text-info-600': analysis?.health_score?.score >= 60 && analysis?.health_score?.score < 80,
                                'text-warning-600': analysis?.health_score?.score >= 40 && analysis?.health_score?.score < 60,
                                'text-danger-600': analysis?.health_score?.score < 40
                            }" x-text="analysis?.health_score?.score + '/100'"></div>
                        </div>
                    </div>
                </div>

                <!-- Ratios financiers -->
                <div class="mb-6">
                    <h4 class="text-md font-bold text-secondary-900 dark:text-white mb-4">Ratios Financiers</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="(ratio, key) in analysis?.ratios" :key="key">
                            <div class="p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                                <div class="text-xs text-secondary-600 dark:text-secondary-400 mb-1" x-text="ratio.label"></div>
                                <div class="text-2xl font-bold text-secondary-900 dark:text-white" x-text="ratio.value + ratio.unit"></div>
                                <div class="text-xs text-secondary-500 dark:text-secondary-500 mt-1">
                                    Benchmark: <span x-text="ratio.benchmark + ratio.unit"></span>
                                </div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400 mt-2" x-text="ratio.interpretation"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Anomalies -->
                <div x-show="analysis?.anomalies?.length > 0" class="mb-6">
                    <h4 class="text-md font-bold text-secondary-900 dark:text-white mb-4">Anomalies Détectées</h4>
                    <div class="space-y-3">
                        <template x-for="anomaly in analysis?.anomalies" :key="anomaly.type">
                            <div class="p-4 rounded-lg border" :class="{
                                'bg-danger-50 border-danger-200 dark:bg-danger-900/20 dark:border-danger-800': anomaly.severity === 'critical',
                                'bg-warning-50 border-warning-200 dark:bg-warning-900/20 dark:border-warning-800': anomaly.severity === 'warning',
                                'bg-info-50 border-info-200 dark:bg-info-900/20 dark:border-info-800': anomaly.severity === 'info'
                            }">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" :class="{
                                        'text-danger-600': anomaly.severity === 'critical',
                                        'text-warning-600': anomaly.severity === 'warning',
                                        'text-info-600': anomaly.severity === 'info'
                                    }" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <div class="font-semibold" :class="{
                                            'text-danger-900 dark:text-danger-100': anomaly.severity === 'critical',
                                            'text-warning-900 dark:text-warning-100': anomaly.severity === 'warning',
                                            'text-info-900 dark:text-info-100': anomaly.severity === 'info'
                                        }" x-text="anomaly.message"></div>
                                        <div class="text-sm mt-1" :class="{
                                            'text-danger-700 dark:text-danger-300': anomaly.severity === 'critical',
                                            'text-warning-700 dark:text-warning-300': anomaly.severity === 'warning',
                                            'text-info-700 dark:text-info-300': anomaly.severity === 'info'
                                        }" x-text="anomaly.recommendation"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Insights IA (Claude) -->
                <div x-show="analysis?.ai_insights" class="mb-6">
                    <h4 class="text-md font-bold text-secondary-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Analyse Claude AI
                        <span class="ml-2 text-xs text-secondary-500 dark:text-secondary-400 font-normal" x-text="'(Modèle: ' + analysis?.ai_insights?.model + ')'"></span>
                    </h4>
                    <div class="p-6 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-lg border border-primary-200 dark:border-primary-800">
                        <div class="prose dark:prose-invert max-w-none text-secondary-900 dark:text-secondary-100" x-html="analysis?.ai_insights?.analysis?.replace(/\n/g, '<br>')"></div>
                    </div>
                </div>

                <!-- Recommandations -->
                <div x-show="analysis?.recommendations?.length > 0">
                    <h4 class="text-md font-bold text-secondary-900 dark:text-white mb-4">Recommandations</h4>
                    <div class="space-y-3">
                        <template x-for="(rec, index) in analysis?.recommendations" :key="index">
                            <div class="p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg flex items-start">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300 flex items-center justify-center text-sm font-bold mr-3" x-text="index + 1"></span>
                                <div class="text-sm text-secondary-700 dark:text-secondary-300" x-text="rec.message"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, nav, aside { display: none; }
        .card { page-break-inside: avoid; }
    }
</style>
@endpush
