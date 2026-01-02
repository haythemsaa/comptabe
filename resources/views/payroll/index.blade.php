@php
    $title = 'Module Paie';
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Module Paie</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    Gestion des employés et fiches de paie
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('payroll.employees.index') }}" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Voir les employés
                </a>
                <a href="{{ route('payroll.payslips.index') }}" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Voir les fiches de paie
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Employees -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Total Employés</p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['total_employees'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Employees -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Actifs</p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['active_employees'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Payslips This Month -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Fiches {{ now()->format('m/Y') }}</p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $stats['payslips_this_month'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Payroll Cost -->
            <div class="card">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Coût {{ now()->format('m/Y') }}</p>
                        <p class="text-2xl font-bold text-secondary-900 dark:text-white">@currency($stats['total_payroll_cost'])</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Actions rapides</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="flex items-center p-4 border border-secondary-200 dark:border-dark-100 rounded-lg hover:bg-secondary-50 dark:hover:bg-dark-100 transition-colors cursor-pointer">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-900 dark:text-white">Ajouter un employé</p>
                            <p class="text-xs text-secondary-500">Utilisez l'AI Chat</p>
                        </div>
                    </div>

                    <div class="flex items-center p-4 border border-secondary-200 dark:border-dark-100 rounded-lg hover:bg-secondary-50 dark:hover:bg-dark-100 transition-colors cursor-pointer">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-900 dark:text-white">Générer fiches de paie</p>
                            <p class="text-xs text-secondary-500">Utilisez l'AI Chat</p>
                        </div>
                    </div>

                    <div class="flex items-center p-4 border border-secondary-200 dark:border-dark-100 rounded-lg hover:bg-secondary-50 dark:hover:bg-dark-100 transition-colors cursor-pointer">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-900 dark:text-white">Déclarations sociales</p>
                            <p class="text-xs text-secondary-500">À venir</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200">Utilisation du module Paie</h3>
                    <div class="mt-2 text-sm text-blue-800 dark:text-blue-300">
                        <p>Pour gérer votre paie facilement, utilisez l'assistant AI :</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li>"Ajoute un employé Jean Dupont, né le 30/07/1985, numéro national 85073003328"</li>
                            <li>"Génère la fiche de paie de décembre 2025 pour Jean Dupont avec 10h supplémentaires"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
