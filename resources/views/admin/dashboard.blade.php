<x-admin-layout>
    <x-slot name="title">Dashboard Admin</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Entreprises</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_companies']) }}</p>
                    <p class="text-xs text-secondary-500 mt-1">{{ $stats['active_companies'] }} actives</p>
                </div>
                <div class="w-14 h-14 bg-primary-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Utilisateurs</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-xs text-secondary-500 mt-1">{{ $stats['active_users'] }} actifs, {{ $stats['superadmins'] }} admins</p>
                </div>
                <div class="w-14 h-14 bg-success-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Factures Totales</p>
                    <p class="text-3xl font-bold text-white">{{ number_format($stats['total_invoices']) }}</p>
                    <p class="text-xs text-secondary-500 mt-1">{{ $stats['invoices_this_month'] }} ce mois</p>
                </div>
                <div class="w-14 h-14 bg-warning-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl p-6 border border-secondary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Système</p>
                    <p class="text-xl font-bold text-success-400">Opérationnel</p>
                    <p class="text-xs text-secondary-500 mt-1">Laravel {{ app()->version() }}</p>
                </div>
                <div class="w-14 h-14 bg-danger-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Stats -->
    <div class="bg-gradient-to-r from-primary-900/50 to-secondary-800 rounded-xl border border-primary-700/50 p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-white">Revenus Abonnements</h2>
                <p class="text-secondary-400 text-sm">Vue d'ensemble de votre SaaS</p>
            </div>
            <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-primary-400 hover:text-primary-300">
                Voir détails &rarr;
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-white">{{ number_format($subscriptionStats['mrr'], 0) }} €</div>
                <div class="text-xs text-secondary-400 mt-1">MRR</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-400">{{ number_format($subscriptionStats['arr'], 0) }} €</div>
                <div class="text-xs text-secondary-400 mt-1">ARR</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-400">{{ $subscriptionStats['active'] }}</div>
                <div class="text-xs text-secondary-400 mt-1">Actifs</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-info-400">{{ $subscriptionStats['trialing'] }}</div>
                <div class="text-xs text-secondary-400 mt-1">En essai</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-400">{{ $subscriptionStats['past_due'] }}</div>
                <div class="text-xs text-secondary-400 mt-1">Impayés</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ $subscriptionStats['total'] }}</div>
                <div class="text-xs text-secondary-400 mt-1">Total</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-400">{{ number_format($subscriptionStats['revenue_this_month'], 0) }} €</div>
                <div class="text-xs text-secondary-400 mt-1">Ce mois</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-warning-400">{{ number_format($subscriptionStats['pending_invoices'], 0) }} €</div>
                <div class="text-xs text-secondary-400 mt-1">En attente</div>
            </div>
        </div>
    </div>

    <!-- Expiring Trials Alert -->
    @if($expiringTrials->count() > 0)
        <div class="bg-info-500/10 border border-info-500/30 rounded-xl p-4 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-info-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-info-400 font-medium">{{ $expiringTrials->count() }} essai(s) expirant bientôt</p>
                        <p class="text-secondary-400 text-sm">
                            @foreach($expiringTrials->take(3) as $trial)
                                {{ $trial->company->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            @if($expiringTrials->count() > 3)
                                ...
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.subscriptions.expiring-trials') }}" class="px-4 py-2 bg-info-500/20 text-info-400 hover:bg-info-500/30 rounded-lg text-sm transition-colors">
                    Voir tout
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700 flex items-center justify-between">
                <h2 class="font-semibold">Activité Récente</h2>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-primary-400 hover:text-primary-300">Voir tout</a>
            </div>
            <div class="p-6">
                @forelse($recentLogs->take(10) as $log)
                    <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-secondary-700' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-{{ $log->action_color }}-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-{{ $log->action_color }}-400"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">{{ $log->description }}</p>
                            <p class="text-xs text-secondary-500">
                                {{ $log->user?->full_name ?? 'Système' }} &bull; {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary-500 text-center py-8">Aucune activité récente</p>
                @endforelse
            </div>
        </div>

        <!-- New Companies -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700 flex items-center justify-between">
                <h2 class="font-semibold">Nouvelles Entreprises</h2>
                <a href="{{ route('admin.companies.index') }}" class="text-sm text-primary-400 hover:text-primary-300">Voir tout</a>
            </div>
            <div class="p-6">
                @forelse($newCompanies as $company)
                    <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-secondary-700' : '' }}">
                        <div class="w-10 h-10 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-primary-400">
                            {{ strtoupper(substr($company->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.companies.show', $company) }}" class="text-sm font-medium text-white hover:text-primary-400 truncate block">
                                {{ $company->name }}
                            </a>
                            <p class="text-xs text-secondary-500">{{ $company->vat_number ?? 'Pas de TVA' }}</p>
                        </div>
                        <span class="text-xs text-secondary-500">{{ $company->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-secondary-500 text-center py-8">Aucune nouvelle entreprise ce mois</p>
                @endforelse
            </div>
        </div>

        <!-- New Users -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700 flex items-center justify-between">
                <h2 class="font-semibold">Nouveaux Utilisateurs</h2>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-primary-400 hover:text-primary-300">Voir tout</a>
            </div>
            <div class="p-6">
                @forelse($newUsers as $user)
                    <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-secondary-700' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-success-500/20 flex items-center justify-center font-bold text-success-400">
                            {{ $user->initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-medium text-white hover:text-primary-400 truncate block">
                                {{ $user->full_name }}
                            </a>
                            <p class="text-xs text-secondary-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs text-secondary-500">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-secondary-500 text-center py-8">Aucun nouvel utilisateur cette semaine</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700">
                <h2 class="font-semibold">Actions Rapides</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <a href="{{ route('admin.users.create') }}" class="p-4 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="text-sm font-medium">Nouvel Utilisateur</span>
                </a>

                <a href="{{ route('admin.audit-logs.export') }}" class="p-4 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium">Exporter Logs</span>
                </a>

                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full p-4 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span class="text-sm font-medium">Vider Cache</span>
                    </button>
                </form>

                <a href="{{ route('admin.settings.index') }}" class="p-4 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-medium">Paramètres</span>
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
