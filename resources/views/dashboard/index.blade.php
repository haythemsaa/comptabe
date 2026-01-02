<x-app-layout>
    <x-slot name="title">Tableau de bord</x-slot>

    @section('breadcrumb')
        <span class="text-secondary-900 dark:text-white font-medium">Tableau de bord</span>
    @endsection

    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                    Bonjour, {{ auth()->user()->first_name }}!
                </h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Voici un aperçu de votre activité
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle facture
                </a>
            </div>
        </div>

        <!-- Quick Actions Bar -->
        <div class="flex items-center gap-3 overflow-x-auto pb-2">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Nouvelle facture
            </a>
            <a href="{{ route('quotes.create') }}" class="btn btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Nouveau devis
            </a>
            <a href="{{ route('partners.create') }}" class="btn btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Nouveau client
            </a>
            <a href="{{ route('bank.reconciliation') }}" class="btn btn-secondary whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Réconciliation
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-6">
            <!-- Current Month Revenue -->
            <div class="stat-card card-hover">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">CA ce mois</p>
                        <p class="stat-value mt-1" data-counter="{{ $metrics['current_revenue'] }}" data-decimals="{{ $companyDecimalPlaces }}" data-suffix=" {{ $companyCurrencySymbol }}">
                            0 {{ $companyCurrencySymbol }}
                        </p>
                    </div>
                    <div class="stat-icon from-primary-500 to-primary-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                @if($metrics['revenue_growth'] != 0)
                    <div class="mt-3 flex items-center gap-2">
                        <span class="stat-change {{ $metrics['revenue_growth'] > 0 ? 'stat-change-up' : 'stat-change-down' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($metrics['revenue_growth'] > 0)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                                @endif
                            </svg>
                            {{ abs($metrics['revenue_growth']) }}% vs mois dernier
                        </span>
                    </div>
                @endif
            </div>

            <!-- Receivables -->
            <div class="stat-card card-hover">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">À recevoir</p>
                        <p class="stat-value mt-1" data-counter="{{ $metrics['receivables'] }}" data-decimals="{{ $companyDecimalPlaces }}" data-suffix=" {{ $companyCurrencySymbol }}">
                            0 {{ $companyCurrencySymbol }}
                        </p>
                    </div>
                    <div class="stat-icon from-primary-500 to-primary-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                @if($metrics['overdue_receivables'] > 0)
                    <div class="mt-3 flex items-center gap-2">
                        <span class="stat-change stat-change-down">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ number_format($metrics['overdue_receivables'], $companyDecimalPlaces, ',', ' ') }} {{ $companyCurrencySymbol }} en retard
                        </span>
                    </div>
                @endif
            </div>

            <!-- Payables -->
            <div class="stat-card card-hover">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">À payer</p>
                        <p class="stat-value mt-1" data-counter="{{ $metrics['payables'] }}" data-decimals="{{ $companyDecimalPlaces }}" data-suffix=" {{ $companyCurrencySymbol }}">
                            0 {{ $companyCurrencySymbol }}
                        </p>
                    </div>
                    <div class="stat-icon from-warning-500 to-warning-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Bank Balance -->
            <div class="stat-card card-hover">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">Solde bancaire</p>
                        <p class="stat-value mt-1" data-counter="{{ $metrics['bank_balance'] }}" data-decimals="{{ $companyDecimalPlaces }}" data-suffix=" {{ $companyCurrencySymbol }}">
                            0 {{ $companyCurrencySymbol }}
                        </p>
                    </div>
                    <div class="stat-icon from-success-500 to-success-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Net Position -->
            <div class="stat-card card-hover">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">Position nette</p>
                        @php $netPosition = $metrics['receivables'] - $metrics['payables']; @endphp
                        <p class="stat-value mt-1 {{ $netPosition >= 0 ? 'text-success-600' : 'text-danger-600' }}"
                           data-counter="{{ $netPosition }}" data-decimals="{{ $companyDecimalPlaces }}" data-prefix="{{ $netPosition >= 0 ? '+' : '' }}" data-suffix=" {{ $companyCurrencySymbol }}">
                            0 {{ $companyCurrencySymbol }}
                        </p>
                    </div>
                    <div class="stat-icon {{ $netPosition >= 0 ? 'from-success-500 to-success-600' : 'from-danger-500 to-danger-600' }} text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Forecast -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Prévision de trésorerie</h2>
                    <p class="text-sm text-secondary-500">Projection sur 30 jours</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($cashFlowForecast['summary']['trend'] === 'up')
                        <span class="flex items-center gap-1 text-success-600 dark:text-success-400 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            +{{ $cashFlowForecast['summary']['trend_percent'] }}%
                        </span>
                    @elseif($cashFlowForecast['summary']['trend'] === 'down')
                        <span class="flex items-center gap-1 text-danger-600 dark:text-danger-400 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            {{ $cashFlowForecast['summary']['trend_percent'] }}%
                        </span>
                    @else
                        <span class="flex items-center gap-1 text-secondary-500 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                            </svg>
                            Stable
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Current Balance -->
                    <div class="p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-secondary-200 dark:bg-secondary-700 flex items-center justify-center">
                                <svg class="w-5 h-5 text-secondary-600 dark:text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-secondary-500 dark:text-secondary-400">Solde actuel</p>
                                <p class="text-lg font-bold text-secondary-900 dark:text-white">@currency($cashFlowForecast['summary']['current_balance'])</p>
                            </div>
                        </div>
                    </div>

                    <!-- Expected Inflow -->
                    <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-success-200 dark:bg-success-800 flex items-center justify-center">
                                <svg class="w-5 h-5 text-success-600 dark:text-success-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-success-600 dark:text-success-400">Encaissements prévus</p>
                                <p class="text-lg font-bold text-success-700 dark:text-success-300">+@currency($cashFlowForecast['summary']['total_inflow'])</p>
                            </div>
                        </div>
                    </div>

                    <!-- Expected Outflow -->
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-danger-200 dark:bg-danger-800 flex items-center justify-center">
                                <svg class="w-5 h-5 text-danger-600 dark:text-danger-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-danger-600 dark:text-danger-400">Décaissements prévus</p>
                                <p class="text-lg font-bold text-danger-700 dark:text-danger-300">-@currency($cashFlowForecast['summary']['total_outflow'])</p>
                            </div>
                        </div>
                    </div>

                    <!-- Projected Balance -->
                    <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-primary-200 dark:bg-primary-800 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-primary-600 dark:text-primary-400">Solde projeté (J+30)</p>
                                <p class="text-lg font-bold text-primary-700 dark:text-primary-300">@currency($cashFlowForecast['summary']['projected_balance'])</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div id="cashFlowChart" class="h-64"></div>
            </div>
        </div>

        <!-- Action Items -->
        @if(count($actionItems) > 0)
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Actions requises</h2>
                </div>
                <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                    @foreach($actionItems as $item)
                        <a href="{{ $item['route'] }}" class="flex items-center gap-4 px-6 py-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                            <div class="w-10 h-10 rounded-xl bg-{{ $item['type'] }}-100 dark:bg-{{ $item['type'] }}-900/30 flex items-center justify-center text-{{ $item['type'] }}-600 dark:text-{{ $item['type'] }}-400">
                                @if($item['icon'] === 'document')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @elseif($item['icon'] === 'inbox')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                @elseif($item['icon'] === 'bank')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                @elseif($item['icon'] === 'alert')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                @endif
                            </div>
                            <span class="flex-1 text-secondary-700 dark:text-secondary-300">{{ $item['message'] }}</span>
                            <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Charts & Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Chiffre d'affaires</h2>
                    <span class="text-sm text-secondary-500">12 derniers mois</span>
                </div>
                <div class="card-body">
                    <div id="revenueChart" class="h-72"></div>
                </div>
            </div>

            <!-- VAT Info -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">TVA</h2>
                </div>
                <div class="card-body">
                    @if($upcomingVatDeclaration)
                        <div class="text-center py-4">
                            <div class="text-3xl font-bold text-secondary-900 dark:text-white">
                                {{ $upcomingVatDeclaration->period_name }}
                            </div>
                            <div class="mt-1 text-sm text-secondary-500">Prochaine déclaration</div>
                            <div class="mt-4">
                                <span class="badge badge-{{ $upcomingVatDeclaration->status === 'draft' ? 'warning' : 'success' }}">
                                    {{ $upcomingVatDeclaration->status_label }}
                                </span>
                            </div>
                            <a href="{{ route('vat.show', $upcomingVatDeclaration) }}" class="btn btn-secondary btn-sm mt-4">
                                Voir les détails
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8 text-secondary-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            <p>Aucune déclaration en cours</p>
                            <a href="{{ route('vat.create') }}" class="btn btn-primary btn-sm mt-4">
                                Créer une déclaration
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Clients & Expense Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Clients -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Top 5 Clients</h2>
                    <p class="text-sm text-secondary-500">12 derniers mois</p>
                </div>
                <div class="card-body">
                    @forelse($topClients as $client)
                        <div class="flex items-center justify-between py-3 border-b border-secondary-100 dark:border-secondary-800 last:border-0">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-secondary-900 dark:text-white truncate">
                                    {{ $client['name'] }}
                                </div>
                                <div class="text-sm text-secondary-500">
                                    {{ $client['invoice_count'] }} facture{{ $client['invoice_count'] > 1 ? 's' : '' }}
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <div class="font-bold text-primary-600">
                                    @currency($client['revenue'])
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-secondary-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p>Aucun client pour le moment</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Expense Breakdown -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Répartition des Dépenses</h2>
                    <p class="text-sm text-secondary-500">12 derniers mois</p>
                </div>
                <div class="card-body">
                    @if(count($expenseBreakdown) > 0)
                        <div id="expenseChart" class="h-64"></div>
                        <div class="mt-4 space-y-2">
                            @foreach($expenseBreakdown as $index => $expense)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ ['#0ea5e9', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899'][$index % 6] }}"></div>
                                        <span class="text-secondary-700 dark:text-secondary-300">{{ $expense['category'] }}</span>
                                    </div>
                                    <span class="font-semibold text-secondary-900 dark:text-white">@currency($expense['total'])</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-secondary-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-secondary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p>Aucune dépense pour le moment</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Invoices & Overdue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Dernières factures</h2>
                    <a href="{{ route('invoices.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Voir tout
                    </a>
                </div>
                <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                    @forelse($recentSalesInvoices as $invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-secondary-900 dark:text-white">{{ $invoice->invoice_number }}</span>
                                    <span class="badge badge-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                                </div>
                                <div class="mt-1 text-sm text-secondary-500 truncate">{{ $invoice->partner->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-secondary-900 dark:text-white">@currency($invoice->total_incl_vat)</div>
                                <div class="text-sm text-secondary-500">@dateFormat($invoice->invoice_date)</div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-secondary-500">
                            Aucune facture récente
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Factures en retard</h2>
                    @if($overdueInvoices->count() > 0)
                        <span class="badge badge-danger">{{ $overdueInvoices->count() }}</span>
                    @endif
                </div>
                <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                    @forelse($overdueInvoices as $invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-secondary-900 dark:text-white">{{ $invoice->invoice_number }}</div>
                                <div class="mt-1 text-sm text-secondary-500 truncate">{{ $invoice->partner->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-danger-600 dark:text-danger-400">@currency($invoice->amount_due)</div>
                                <div class="text-sm text-danger-500">{{ abs($invoice->days_until_due) }} jours de retard</div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-secondary-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-success-600 dark:text-success-400 font-medium">Aucune facture en retard!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Peppol 2026 Banner -->
        @if(!($currentTenant->peppol_registered ?? false))
            <div class="card bg-gradient-to-r from-primary-600 to-primary-700 text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="card-body relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold">Préparez-vous pour Peppol 2026</h3>
                            <p class="mt-1 text-primary-100">
                                La facturation électronique B2B devient obligatoire en Belgique à partir du 1er janvier 2026.
                                Configurez votre compte Peppol dès maintenant.
                            </p>
                        </div>
                        <a href="{{ route('settings.peppol') }}" class="btn bg-white text-primary-700 hover:bg-primary-50 flex-shrink-0">
                            Configurer Peppol
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cash Flow Forecast Chart
            const cashFlowData = @json($cashFlowForecast);

            if (document.getElementById('cashFlowChart')) {
                createChart('#cashFlowChart', {
                    chart: {
                        type: 'area',
                        height: 256,
                        toolbar: { show: false },
                        stacked: false
                    },
                    series: [
                        {
                            name: 'Solde projeté',
                            type: 'line',
                            data: cashFlowData.balances
                        },
                        {
                            name: 'Encaissements',
                            type: 'column',
                            data: cashFlowData.inflows
                        },
                        {
                            name: 'Décaissements',
                            type: 'column',
                            data: cashFlowData.outflows.map(v => -v)
                        }
                    ],
                    xaxis: {
                        categories: cashFlowData.labels,
                        labels: {
                            style: { colors: '#64748b', fontSize: '12px' }
                        }
                    },
                    yaxis: [
                        {
                            title: { text: 'Solde', style: { color: '#64748b' } },
                            labels: {
                                formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(val),
                                style: { colors: '#64748b', fontSize: '11px' }
                            }
                        },
                        {
                            opposite: true,
                            title: { text: 'Flux', style: { color: '#64748b' } },
                            labels: {
                                formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                    style: 'currency',
                                    currency: 'EUR',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(Math.abs(val)),
                                style: { colors: '#64748b', fontSize: '11px' }
                            }
                        },
                        { show: false }
                    ],
                    colors: ['#0ea5e9', '#10b981', '#ef4444'],
                    fill: {
                        type: ['gradient', 'solid', 'solid'],
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [3, 0, 0]
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                            borderRadius: 4
                        }
                    },
                    dataLabels: { enabled: false },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    tooltip: {
                        shared: true,
                        y: {
                            formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                style: 'currency',
                                currency: 'EUR'
                            }).format(Math.abs(val))
                        }
                    }
                });
            }

            // Expense Breakdown Donut Chart
            const expenseData = @json($expenseBreakdown);

            if (document.getElementById('expenseChart') && expenseData.length > 0) {
                createChart('#expenseChart', {
                    chart: {
                        type: 'donut',
                        height: 256
                    },
                    series: expenseData.map(e => parseFloat(e.total)),
                    labels: expenseData.map(e => e.category),
                    colors: ['#0ea5e9', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '14px'
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        fontWeight: 600,
                                        formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                            style: 'currency',
                                            currency: 'EUR',
                                            minimumFractionDigits: 0
                                        }).format(val)
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        fontSize: '14px',
                                        formatter: (w) => new Intl.NumberFormat('fr-BE', {
                                            style: 'currency',
                                            currency: 'EUR',
                                            minimumFractionDigits: 0
                                        }).format(w.globals.seriesTotals.reduce((a, b) => a + b, 0))
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                style: 'currency',
                                currency: 'EUR'
                            }).format(val)
                        }
                    }
                });
            }

            // Revenue Chart
            const chartData = @json($revenueChartData);

            if (document.getElementById('revenueChart')) {
                createChart('#revenueChart', {
                    chart: {
                        type: 'area',
                        height: 288,
                        toolbar: { show: false }
                    },
                    series: [
                        {
                            name: 'Revenus',
                            data: chartData.revenue
                        },
                        {
                            name: 'Dépenses',
                            data: chartData.expenses
                        }
                    ],
                    xaxis: {
                        categories: chartData.labels,
                        labels: {
                            style: { colors: '#64748b', fontSize: '12px' }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: (val) => new Intl.NumberFormat('fr-BE', {
                                style: 'currency',
                                currency: 'EUR',
                                minimumFractionDigits: 0
                            }).format(val),
                            style: { colors: '#64748b', fontSize: '12px' }
                        }
                    },
                    colors: ['#0ea5e9', '#f59e0b'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    dataLabels: { enabled: false },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
