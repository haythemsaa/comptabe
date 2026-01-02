<x-app-layout>
    <x-slot name="title">Peppol - Dashboard</x-slot>

    @section('breadcrumb')
        <a href="{{ route('admin.dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Admin</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Peppol Dashboard</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Peppol Dashboard</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gestion centralisée et optimisation automatique</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.peppol.settings') }}" class="btn btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Configuration
                </a>
            </div>
        </div>

        <!-- Recommandation Card (si upgrade nécessaire) -->
        @if($recommendation['should_upgrade'] || $recommendation['should_downgrade'])
        <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 rounded-xl p-6 border-2 border-primary-200 dark:border-primary-700">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-2">
                        @if($recommendation['should_upgrade'])
                            ⚡ Recommandation: Upgrader votre plan
                        @else
                            💰 Recommandation: Optimiser vos coûts
                        @endif
                    </h3>
                    <p class="text-secondary-700 dark:text-secondary-300 mb-4">
                        {{ $recommendation['reason'] }}
                    </p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">Plan actuel</div>
                            <div class="text-lg font-semibold text-secondary-900 dark:text-white">
                                {{ ucfirst($recommendation['current']['provider']) }} {{ ucfirst($recommendation['current']['plan']) }}
                            </div>
                            <div class="text-sm text-secondary-600">€{{ number_format($recommendation['current']['cost'], 2) }}/mois</div>
                        </div>
                        <div>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">Plan optimal</div>
                            <div class="text-lg font-semibold text-primary-600">
                                {{ $recommendation['optimal']['provider_name'] ?? '' }} {{ $recommendation['optimal']['plan_name'] ?? '' }}
                            </div>
                            <div class="text-sm text-primary-600">€{{ number_format($recommendation['optimal']['total_cost'], 2) }}/mois</div>
                        </div>
                        <div>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">Économies</div>
                            <div class="text-lg font-semibold {{ $recommendation['savings'] > 0 ? 'text-success-600' : 'text-warning-600' }}">
                                {{ $recommendation['savings'] > 0 ? '+' : '' }}€{{ number_format(abs($recommendation['savings']), 2) }}
                            </div>
                            <div class="text-sm text-secondary-600">/mois</div>
                        </div>
                        <div>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">Marge</div>
                            <div class="text-lg font-semibold text-success-600">
                                {{ number_format($recommendation['revenue']['margin_percent'], 1) }}%
                            </div>
                            <div class="text-sm text-success-600">€{{ number_format($recommendation['revenue']['margin'], 2) }}/mois</div>
                        </div>
                    </div>

                    <form action="{{ route('admin.peppol.optimize.apply') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            Appliquer le plan optimal
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Volume ce mois -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Volume ce mois</div>
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-secondary-900 dark:text-white">
                    {{ $currentMonth['sends'] + $currentMonth['receives'] }}
                </div>
                <div class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                    {{ $currentMonth['sends'] }} envoyées · {{ $currentMonth['receives'] }} reçues
                </div>
            </div>

            <!-- Entreprises actives -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Entreprises actives</div>
                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-secondary-900 dark:text-white">
                    {{ $stats['active_companies'] }}
                </div>
                <div class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                    sur {{ $stats['total_companies'] }} total
                </div>
            </div>

            <!-- Coût provider -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Coût provider</div>
                    <div class="w-10 h-10 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-secondary-900 dark:text-white">
                    €{{ number_format($currentMonth['total_cost'], 2) }}
                </div>
                <div class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                    ce mois
                </div>
            </div>

            <!-- Revenus clients -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Revenus clients</div>
                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-success-600">
                    €{{ number_format($recommendation['revenue']['tenant_revenue'], 2) }}
                </div>
                <div class="text-sm text-success-600 mt-1">
                    Marge: €{{ number_format($recommendation['revenue']['margin'], 2) }}
                </div>
            </div>
        </div>

        <!-- Top Utilisateurs & Distribution Plans -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top 10 Utilisateurs -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">
                    Top 10 Entreprises Utilisatrices
                </h3>
                <div class="space-y-3">
                    @forelse($topUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-secondary-50 dark:bg-secondary-700/50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-secondary-900 dark:text-white">
                                {{ $user->name }}
                            </div>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                Plan: {{ ucfirst($user->peppol_plan ?? 'free') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-secondary-900 dark:text-white">
                                {{ $user->peppol_usage_current_month }}
                            </div>
                            <div class="text-xs text-secondary-600">
                                / {{ $user->peppol_quota_monthly }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-secondary-600 dark:text-secondary-400 text-center py-4">
                        Aucune utilisation ce mois
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Distribution des Plans -->
            <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">
                    Distribution des Plans
                </h3>
                <div class="space-y-4">
                    @php
                        $planLabels = [
                            'free' => 'Gratuit',
                            'starter' => 'Starter',
                            'pro' => 'Pro',
                            'business' => 'Business',
                            'enterprise' => 'Enterprise',
                        ];
                    @endphp
                    @foreach($stats['plan_distribution'] as $plan => $count)
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">
                                {{ $planLabels[$plan] ?? ucfirst($plan) }}
                            </span>
                            <span class="text-sm text-secondary-600 dark:text-secondary-400">
                                {{ $count }} entreprises
                            </span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ ($count / $stats['total_companies']) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions Rapides -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('admin.peppol.quotas') }}" class="block p-6 bg-white dark:bg-secondary-800 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-secondary-900 dark:text-white">Gérer les Quotas</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">Par entreprise</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.peppol.usage') }}" class="block p-6 bg-white dark:bg-secondary-800 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-secondary-900 dark:text-white">Historique d'Usage</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">Détails transmissions</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.peppol.optimize') }}" class="block p-6 bg-white dark:bg-secondary-800 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-secondary-900 dark:text-white">Optimiser le Plan</div>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">Calculer optimal</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
