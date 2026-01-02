@extends('layouts.app')

@section('title', 'Open Banking')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                Open Banking (PSD2)
            </h1>
            <p class="text-secondary-600 dark:text-secondary-400">
                Connectez vos comptes bancaires belges pour une synchronisation automatique
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('openbanking.sync-all') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Synchroniser tout
                </button>
            </form>
            <a href="{{ route('openbanking.banks') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Connecter une banque
            </a>
        </div>
    </div>

    <!-- Alertes de santé -->
    @foreach($healthCheck as $bankId => $health)
        @if($health['status'] === 'expired')
            <x-alert type="error">
                <strong>{{ $health['bank_name'] }}:</strong> {{ $health['message'] }}
                <a href="{{ route('openbanking.renew', $bankId) }}" class="underline ml-2">Renouveler</a>
            </x-alert>
        @elseif($health['status'] === 'warning')
            <x-alert type="warning">
                <strong>{{ $health['bank_name'] }}:</strong> {{ $health['message'] }} ({{ $health['consent_expires'] }})
            </x-alert>
        @endif
    @endforeach

    <!-- Stats globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm">Solde total</p>
                    <p class="text-3xl font-bold mt-1">{{ number_format($totalBalance, 0, ',', ' ') }} €</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Banques connectées</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $connections->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Comptes actifs</p>
                    <p class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $accountsCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary-800 rounded-xl p-6 shadow-lg border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-secondary-500">Sécurité PSD2</p>
                    <p class="text-lg font-bold text-green-600">Certifié</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Connexions bancaires -->
    @if($connections->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto bg-secondary-100 dark:bg-secondary-700 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">Aucune banque connectée</h3>
                <p class="text-secondary-600 dark:text-secondary-400 mb-6">
                    Connectez vos comptes bancaires pour synchroniser automatiquement vos transactions.
                </p>
                <a href="{{ route('openbanking.banks') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Connecter une banque
                </a>
            </div>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach($connections as $connection)
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-secondary-100 dark:bg-secondary-700 rounded-xl flex items-center justify-center">
                                <img src="{{ $supportedBanks[array_search($connection->bank_id, array_column($supportedBanks, 'id'))]['logo'] ?? '' }}"
                                     alt="{{ $connection->bank_name }}"
                                     class="w-10 h-10"
                                     onerror="this.onerror=null;this.src='/images/bank-default.svg';">
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">
                                    {{ $connection->bank_name }}
                                </h3>
                                <p class="text-sm text-secondary-500">
                                    BIC: {{ $connection->bic }}
                                    &bull;
                                    Dernière sync: {{ $connection->last_sync_at?->diffForHumans() ?? 'Jamais' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($connection->status === 'active')
                                <x-badge color="green">Connecté</x-badge>
                            @elseif($connection->status === 'expired')
                                <x-badge color="red">Expiré</x-badge>
                            @else
                                <x-badge color="gray">{{ ucfirst($connection->status) }}</x-badge>
                            @endif

                            <div class="flex items-center gap-1">
                                <form action="{{ route('openbanking.sync-accounts', $connection) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Synchroniser"
                                            class="p-2 text-secondary-400 hover:text-primary-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('openbanking.disconnect', $connection) }}" method="POST"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir déconnecter cette banque?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Déconnecter"
                                            class="p-2 text-secondary-400 hover:text-red-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Comptes -->
                    @if($connection->accounts->isEmpty())
                        <p class="text-secondary-500 text-center py-4">Aucun compte synchronisé</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($connection->accounts as $account)
                                <a href="{{ route('openbanking.account', $account) }}"
                                   class="block p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors group">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-secondary-900 dark:text-white">
                                            {{ $account->name }}
                                        </span>
                                        <svg class="w-4 h-4 text-secondary-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-secondary-500 font-mono mb-2">
                                        {{ implode(' ', str_split($account->iban, 4)) }}
                                    </p>
                                    <p class="text-xl font-bold {{ $account->current_balance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ number_format($account->current_balance, 2, ',', ' ') }} €
                                    </p>
                                    @if($account->balance_updated_at)
                                        <p class="text-xs text-secondary-400 mt-1">
                                            Màj: {{ $account->balance_updated_at->diffForHumans() }}
                                        </p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Expiration du consentement -->
                    @if($connection->consent_expires_at)
                        <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-secondary-500">Consentement expire le</span>
                                <span class="{{ $connection->consent_expires_at->isPast() ? 'text-red-600 font-medium' : ($connection->consent_expires_at->diffInDays(now()) < 7 ? 'text-yellow-600' : 'text-secondary-700 dark:text-secondary-300') }}">
                                    {{ $connection->consent_expires_at->format('d/m/Y') }}
                                    ({{ $connection->consent_expires_at->diffForHumans() }})
                                </span>
                            </div>
                        </div>
                    @endif
                </x-card>
            @endforeach
        </div>
    @endif

    <!-- Informations PSD2 -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6">
        <h3 class="font-semibold text-secondary-900 dark:text-white mb-4">
            À propos de l'Open Banking PSD2
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Sécurité renforcée</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                        Authentification forte et chiffrement de bout en bout
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Contrôle total</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                        Révoquez l'accès à tout moment depuis votre banque
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-secondary-900 dark:text-white">Conforme RGPD</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                        Vos données sont protégées selon la réglementation européenne
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
