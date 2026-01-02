@extends('layouts.app')

@section('title', 'Compte de Résultat')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">
                    Compte de Résultat
                </h1>
                <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $company->name }} - Du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
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

    @php
        $data = $report['data'];
        $netResult = $data['net_result'];
        $isProfit = $netResult >= 0;
    @endphp

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Chiffre d'affaires -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">
                            Chiffre d'affaires
                        </p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-2">
                            {{ number_format($data['revenue']['total'], 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charges -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">
                            Charges totales
                        </p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-2">
                            {{ number_format($data['expenses']['total'], 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 dark:bg-danger-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-600 dark:text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultat net -->
        <div class="card {{ $isProfit ? 'bg-success-50 dark:bg-success-900/20' : 'bg-danger-50 dark:bg-danger-900/20' }}">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium {{ $isProfit ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300' }}">
                            Résultat net
                        </p>
                        <p class="text-2xl font-bold {{ $isProfit ? 'text-success-900 dark:text-success-100' : 'text-danger-900 dark:text-danger-100' }} mt-2">
                            {{ number_format($netResult, 0, ',', ' ') }} €
                        </p>
                    </div>
                    <div class="w-12 h-12 {{ $isProfit ? 'bg-success-200 dark:bg-success-800' : 'bg-danger-200 dark:bg-danger-800' }} rounded-lg flex items-center justify-center">
                        @if($isProfit)
                            <svg class="w-6 h-6 text-success-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-danger-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Marge -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">
                            Marge nette
                        </p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white mt-2">
                            {{ number_format($data['margin'], 1) }}%
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- PRODUITS -->
        <div class="card">
            <div class="card-header bg-success-50 dark:bg-success-900/20">
                <h2 class="text-xl font-bold text-success-900 dark:text-success-100">
                    PRODUITS (Classe 7)
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                    @foreach($data['revenue']['items'] as $item)
                        @if($item['amount'] != 0)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-success-700 dark:text-success-300">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                <!-- Total Produits -->
                <div class="bg-success-600 dark:bg-success-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">
                            TOTAL PRODUITS
                        </h3>
                        <span class="text-2xl font-bold text-white">
                            {{ number_format($data['revenue']['total'], 2, ',', ' ') }} €
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARGES -->
        <div class="card">
            <div class="card-header bg-danger-50 dark:bg-danger-900/20">
                <h2 class="text-xl font-bold text-danger-900 dark:text-danger-100">
                    CHARGES (Classe 6)
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-secondary-100 dark:divide-secondary-700">
                    @foreach($data['expenses']['items'] as $item)
                        @if($item['amount'] != 0)
                        <div class="px-6 py-3 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-secondary-700 dark:text-secondary-300">
                                    <span class="font-mono text-xs text-secondary-500 mr-2">{{ $item['code'] }}</span>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-medium text-danger-700 dark:text-danger-300">
                                    {{ number_format($item['amount'], 2, ',', ' ') }} €
                                </span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>

                <!-- Total Charges -->
                <div class="bg-danger-600 dark:bg-danger-700 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-white">
                            TOTAL CHARGES
                        </h3>
                        <span class="text-2xl font-bold text-white">
                            {{ number_format($data['expenses']['total'], 2, ',', ' ') }} €
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats intermédiaires -->
    <div class="mt-6 card">
        <div class="card-header">
            <h2 class="text-xl font-bold text-secondary-900 dark:text-white">
                Résultats intermédiaires
            </h2>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <!-- Résultat d'exploitation -->
                <div class="flex justify-between items-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                    <div>
                        <h3 class="font-semibold text-secondary-900 dark:text-white">
                            Résultat d'exploitation
                        </h3>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                            Produits d'exploitation - Charges d'exploitation
                        </p>
                    </div>
                    <span class="text-xl font-bold {{ $data['operating_result'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">
                        {{ number_format($data['operating_result'], 2, ',', ' ') }} €
                    </span>
                </div>

                <!-- Résultat financier -->
                <div class="flex justify-between items-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                    <div>
                        <h3 class="font-semibold text-secondary-900 dark:text-white">
                            Résultat financier
                        </h3>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                            Produits financiers - Charges financières
                        </p>
                    </div>
                    <span class="text-xl font-bold {{ $data['financial_result'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">
                        {{ number_format($data['financial_result'], 2, ',', ' ') }} €
                    </span>
                </div>

                <!-- Résultat exceptionnel -->
                <div class="flex justify-between items-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                    <div>
                        <h3 class="font-semibold text-secondary-900 dark:text-white">
                            Résultat exceptionnel
                        </h3>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                            Produits exceptionnels - Charges exceptionnelles
                        </p>
                    </div>
                    <span class="text-xl font-bold {{ $data['exceptional_result'] >= 0 ? 'text-success-700' : 'text-danger-700' }}">
                        {{ number_format($data['exceptional_result'], 2, ',', ' ') }} €
                    </span>
                </div>

                <!-- Impôts -->
                <div class="flex justify-between items-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                    <div>
                        <h3 class="font-semibold text-secondary-900 dark:text-white">
                            Impôts sur le résultat
                        </h3>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">
                            Classe 67 - Impôts
                        </p>
                    </div>
                    <span class="text-xl font-bold text-danger-700">
                        {{ number_format($data['taxes'], 2, ',', ' ') }} €
                    </span>
                </div>

                <!-- Résultat net final -->
                <div class="flex justify-between items-center p-4 {{ $isProfit ? 'bg-success-100 dark:bg-success-900/30' : 'bg-danger-100 dark:bg-danger-900/30' }} rounded-lg border-2 {{ $isProfit ? 'border-success-300 dark:border-success-700' : 'border-danger-300 dark:border-danger-700' }}">
                    <div>
                        <h3 class="text-lg font-bold {{ $isProfit ? 'text-success-900 dark:text-success-100' : 'text-danger-900 dark:text-danger-100' }}">
                            Résultat net de l'exercice
                        </h3>
                        <p class="text-sm {{ $isProfit ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300' }} mt-1">
                            {{ $isProfit ? 'Bénéfice' : 'Perte' }}
                        </p>
                    </div>
                    <span class="text-3xl font-bold {{ $isProfit ? 'text-success-900 dark:text-success-100' : 'text-danger-900 dark:text-danger-100' }}">
                        {{ number_format($netResult, 2, ',', ' ') }} €
                    </span>
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
