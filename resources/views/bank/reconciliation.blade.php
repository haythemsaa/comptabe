@extends('layouts.app')

@section('title', 'Réconciliation Bancaire')

@section('content')
<div class="space-y-6" x-data="reconciliation()" x-init="init()">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Réconciliation Bancaire</h1>
            <p class="text-secondary-500 dark:text-secondary-400 mt-1">Réconciliez automatiquement vos transactions bancaires avec vos factures</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('bank.index') }}" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <button @click="batchReconcile()" class="btn btn-primary" :disabled="loading">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!loading">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" x-show="loading" x-cloak>
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Traitement...' : 'Réconcilier automatiquement'"></span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400">Transactions totales</p>
                        <p class="text-3xl font-bold text-secondary-900 dark:text-white">{{ $stats['total_transactions'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400">Réconciliées</p>
                        <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $stats['reconciled'] ?? 0 }}</p>
                        <p class="text-xs text-secondary-400 mt-1">Dont {{ $stats['auto_reconciled'] ?? 0 }} auto</p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400">En attente</p>
                        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-500 dark:text-secondary-400">Taux de réconciliation</p>
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ number_format($stats['reconciliation_rate'] ?? 0, 0) }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Transactions à réconcilier ({{ $transactions->total() }})
            </h3>
        </div>
        <div class="card-body p-0">
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Contrepartie</th>
                                <th>Communication</th>
                                <th class="text-right">Montant</th>
                                <th>Statut</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                @php
                                    $result = $transaction->reconciliation_result ?? [];
                                    $hasMatch = isset($result['matched']) && $result['matched'];
                                    $hasSuggestions = isset($result['suggestions']) && count($result['suggestions']) > 0;
                                @endphp
                                <tr x-data="{ showDetails: false }">
                                    <td class="whitespace-nowrap">
                                        <span class="font-medium">{{ $transaction->date->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <div class="font-medium text-secondary-900 dark:text-white">{{ Str::limit($transaction->counterparty_name ?? 'N/A', 30) }}</div>
                                        @if($transaction->counterparty_iban)
                                            <div class="text-xs text-secondary-500 font-mono">{{ Str::limit($transaction->counterparty_iban, 25) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-xs bg-secondary-100 dark:bg-secondary-700 px-2 py-1 rounded">
                                            {{ Str::limit($transaction->communication ?? '-', 40) }}
                                        </code>
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <span class="font-bold {{ $transaction->amount > 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                            {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2, ',', ' ') }} €
                                        </span>
                                    </td>
                                    <td>
                                        @if($hasMatch)
                                            <span class="badge badge-success">
                                                Match {{ round($result['confidence'] * 100) }}%
                                            </span>
                                        @elseif($hasSuggestions)
                                            <button @click="showDetails = !showDetails" class="badge badge-warning cursor-pointer hover:opacity-80">
                                                {{ count($result['suggestions']) }} suggestion(s)
                                                <svg class="w-3 h-3 ml-1 transition-transform" :class="showDetails && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        @else
                                            <span class="badge badge-secondary">
                                                Sans correspondance
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($hasMatch)
                                                <button @click="acceptAutoMatch('{{ $transaction->id }}')" class="btn btn-sm btn-success" :disabled="loading">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            <button @click="openManualModal('{{ $transaction->id }}')" class="btn btn-sm btn-secondary" :disabled="loading">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Suggestions Row -->
                                @if($hasSuggestions)
                                <tr x-show="showDetails" x-collapse>
                                    <td colspan="6" class="bg-secondary-50 dark:bg-secondary-800/50 p-4">
                                        <div class="space-y-3">
                                            <h6 class="font-semibold text-secondary-700 dark:text-secondary-300">
                                                Suggestions de correspondance
                                            </h6>
                                            @foreach($result['suggestions'] as $suggestion)
                                                <div class="flex items-center justify-between p-3 bg-white dark:bg-secondary-800 rounded-lg border border-secondary-200 dark:border-secondary-700">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $suggestion['confidence'] >= 0.8 ? 'bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400' : ($suggestion['confidence'] >= 0.6 ? 'bg-warning-100 dark:bg-warning-900/30 text-warning-600 dark:text-warning-400' : 'bg-secondary-100 dark:bg-secondary-700 text-secondary-600 dark:text-secondary-400') }}">
                                                            <span class="font-bold">{{ round($suggestion['confidence'] * 100) }}%</span>
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-secondary-900 dark:text-white">
                                                                {{ $suggestion['invoice']['invoice_number'] ?? 'N/A' }}
                                                                <span class="text-secondary-500 mx-2">-</span>
                                                                {{ $suggestion['invoice']['partner']['name'] ?? 'N/A' }}
                                                            </div>
                                                            <div class="text-sm text-secondary-500">
                                                                Montant: {{ number_format($suggestion['invoice']['amount_due'] ?? 0, 2, ',', ' ') }} €
                                                                <span class="mx-2">|</span>
                                                                Échéance: {{ isset($suggestion['invoice']['due_date']) ? \Carbon\Carbon::parse($suggestion['invoice']['due_date'])->format('d/m/Y') : '-' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button @click="reconcileWith('{{ $transaction->id }}', '{{ $suggestion['invoice']['id'] }}')" class="btn btn-sm btn-primary" :disabled="loading">
                                                        Réconcilier
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="card-footer">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-secondary-900 dark:text-white mb-2">Toutes les transactions sont réconciliées!</h4>
                    <p class="text-secondary-500 dark:text-secondary-400 mb-4">Aucune transaction en attente de réconciliation.</p>
                    <a href="{{ route('bank.import') }}" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importer des transactions
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function reconciliation() {
    return {
        loading: false,

        init() {
            console.log('Reconciliation module initialized');
        },

        async acceptAutoMatch(transactionId) {
            if (!confirm('Valider cette réconciliation automatique?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/api/v1/bank/reconcile/auto/${transactionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('success', 'Réconciliation effectuée avec succès!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('error', data.message || 'Erreur lors de la réconciliation');
                }
            } catch (error) {
                console.error('Reconciliation error:', error);
                this.showNotification('error', 'Erreur réseau');
            } finally {
                this.loading = false;
            }
        },

        async reconcileWith(transactionId, invoiceId) {
            if (!confirm('Confirmer la réconciliation avec cette facture?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch('/api/v1/bank/reconcile/manual', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        transaction_id: transactionId,
                        invoice_id: invoiceId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('success', 'Réconciliation effectuée avec succès!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('error', data.message || 'Erreur lors de la réconciliation');
                }
            } catch (error) {
                console.error('Manual reconciliation error:', error);
                this.showNotification('error', 'Erreur réseau');
            } finally {
                this.loading = false;
            }
        },

        openManualModal(transactionId) {
            alert('Fonctionnalité de sélection manuelle à venir');
        },

        async batchReconcile() {
            const count = {{ $transactions->count() }};
            if (!confirm(`Lancer la réconciliation automatique de ${count} transaction(s)?`)) {
                return;
            }

            this.loading = true;

            try {
                const transactionIds = @json($transactions->pluck('id'));

                const response = await fetch('/api/v1/bank/reconcile/batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ transaction_ids: transactionIds })
                });

                const data = await response.json();

                if (data.success) {
                    const r = data.results;
                    this.showNotification('success',
                        `Terminé! ${r.auto_matched || 0} réconciliées automatiquement, ${r.suggestions || 0} avec suggestions`
                    );
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    this.showNotification('error', data.message || 'Erreur lors du traitement');
                }
            } catch (error) {
                console.error('Batch reconciliation error:', error);
                this.showNotification('error', 'Erreur réseau');
            } finally {
                this.loading = false;
            }
        },

        showNotification(type, message) {
            // Use the app's notification system if available
            if (window.dispatchEvent) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message }
                }));
            } else {
                alert(message);
            }
        }
    };
}
</script>
@endpush
@endsection
