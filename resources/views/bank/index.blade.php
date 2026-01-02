<x-app-layout>
    <x-slot name="title">Banque</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Banque</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Banque</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez vos comptes et transactions bancaires</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bank.reconciliation') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Réconciliation
                </a>
                <a href="{{ route('bank.import') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Importer CODA
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-500">Solde total</div>
                        <div class="text-xl font-bold text-secondary-900 dark:text-white">@currency($stats['total_balance'])</div>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-500">À réconcilier</div>
                        <div class="text-xl font-bold text-warning-600">{{ $stats['unreconciled'] }}</div>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-500">Entrées ce mois</div>
                        <div class="text-xl font-bold text-success-600">@currency($stats['this_month_in'])</div>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-danger-100 dark:bg-danger-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-secondary-500">Sorties ce mois</div>
                        <div class="text-xl font-bold text-danger-600">@currency($stats['this_month_out'])</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Bank Accounts -->
            <div class="lg:col-span-1 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Comptes bancaires</h2>
                    <a href="{{ route('bank.accounts') }}" class="text-sm text-primary-600 hover:text-primary-700">Gérer</a>
                </div>

                @forelse($accounts as $account)
                    <div class="card card-hover">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($account->bank_name ?? $account->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-secondary-900 dark:text-white">{{ $account->name }}</div>
                                        <div class="text-sm font-mono text-secondary-500">{{ $account->formatted_iban }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-lg {{ $account->current_balance >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                        @currency($account->current_balance)
                                    </div>
                                </div>
                            </div>

                            @if($account->statements->count() > 0)
                                <div class="mt-3 pt-3 border-t border-secondary-100 dark:border-secondary-800">
                                    <div class="text-xs text-secondary-500">
                                        Dernier extrait: {{ $account->statements->first()->statement_date->format('d/m/Y') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-secondary-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-secondary-500 mb-3">Aucun compte bancaire</p>
                            <a href="{{ route('bank.accounts') }}" class="btn btn-primary btn-sm">Ajouter un compte</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Recent Transactions -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Transactions récentes</h2>
                        @if($stats['unreconciled'] > 0)
                            <a href="{{ route('bank.reconciliation') }}" class="badge badge-warning">
                                {{ $stats['unreconciled'] }} à réconcilier
                            </a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Contrepartie</th>
                                    <th class="text-right">Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr class="animate-fade-in">
                                        <td class="whitespace-nowrap">
                                            <div class="font-medium">{{ $transaction->value_date->format('d/m/Y') }}</div>
                                            <div class="text-xs text-secondary-500">{{ $transaction->bankAccount->name }}</div>
                                        </td>
                                        <td>
                                            <div class="max-w-xs truncate">{{ $transaction->communication }}</div>
                                            @if($transaction->structured_communication)
                                                <div class="text-xs font-mono text-secondary-500">{{ $transaction->structured_communication }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->partner)
                                                <a href="{{ route('partners.show', $transaction->partner) }}" class="text-primary-600 hover:text-primary-700">
                                                    {{ $transaction->partner->name }}
                                                </a>
                                            @elseif($transaction->counterparty_name)
                                                {{ $transaction->counterparty_name }}
                                            @else
                                                <span class="text-secondary-400">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right whitespace-nowrap font-medium {{ $transaction->amount >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                            {{ $transaction->amount >= 0 ? '+' : '' }}@currency($transaction->amount)
                                        </td>
                                        <td>
                                            @if($transaction->reconciliation_status !== 'pending')
                                                @if($transaction->invoice)
                                                    <a href="{{ route('invoices.show', $transaction->invoice) }}" class="badge badge-success">
                                                        {{ $transaction->invoice->invoice_number }}
                                                    </a>
                                                @else
                                                    <span class="badge badge-{{ $transaction->status_color }}">Réconcilié</span>
                                                @endif
                                            @else
                                                <span class="badge badge-warning">En attente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-secondary-500">
                                            Aucune transaction
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
