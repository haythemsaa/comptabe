@extends('layouts.app')

@section('title', 'Réconciliation Bancaire Intelligente')

@section('content')
<div x-data="reconciliation()" x-init="init()" class="container-fluid">
    {{-- En-tête avec statistiques --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="mb-1">
                        <i class="fas fa-magic text-primary"></i>
                        Réconciliation Bancaire Intelligente
                    </h1>
                    <p class="text-muted mb-0">L'IA réconcilie automatiquement vos transactions</p>
                </div>
                <div>
                    <button @click="batchReconcile()" class="btn btn-primary btn-lg" :disabled="loading">
                        <i class="fas fa-bolt"></i>
                        <span x-show="!loading">Réconcilier automatiquement</span>
                        <span x-show="loading" x-cloak>
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase small">Transactions totales</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total_transactions'] ?? 0 }}</h2>
                        </div>
                        <div class="text-primary fs-1">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase small">Réconciliées</h6>
                            <h2 class="mb-0 fw-bold text-success">{{ $stats['reconciled'] ?? 0 }}</h2>
                            <small class="text-muted">Dont {{ $stats['auto_reconciled'] ?? 0 }} auto</small>
                        </div>
                        <div class="text-success fs-1">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase small">En attente</h6>
                            <h2 class="mb-0 fw-bold text-warning">{{ $stats['pending'] ?? 0 }}</h2>
                        </div>
                        <div class="text-warning fs-1">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 text-uppercase small">Taux automatique</h6>
                            <h2 class="mb-0 fw-bold text-info">
                                {{ number_format($stats['auto_reconciliation_rate'] ?? 0, 0) }}<small class="fs-6">%</small>
                            </h2>
                        </div>
                        <div class="text-info fs-1">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des transactions --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-list"></i>
                        Transactions à réconcilier ({{ $transactions->total() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Date</th>
                                        <th>Contrepartie</th>
                                        <th>Communication</th>
                                        <th class="text-end" style="width: 130px;">Montant</th>
                                        <th style="width: 300px;">Suggestions IA</th>
                                        <th class="text-end" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        @php
                                            $result = $transaction->reconciliation_result ?? [];
                                            $hasMatch = isset($result['matched']) && $result['matched'];
                                            $hasSuggestions = isset($result['suggestions']) && count($result['suggestions']) > 0;
                                        @endphp
                                        <tr x-data="{
                                            transactionId: '{{ $transaction->id }}',
                                            showDetails: false
                                        }">
                                            <td class="text-nowrap">
                                                <small class="fw-semibold">{{ $transaction->date->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ Str::limit($transaction->counterparty_name ?? 'N/A', 30) }}</div>
                                                    @if($transaction->counterparty_iban)
                                                        <small class="text-muted font-monospace">{{ Str::limit($transaction->counterparty_iban, 20) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <code class="small bg-light px-2 py-1 rounded">
                                                    {{ Str::limit($transaction->communication ?? '-', 35) }}
                                                </code>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold fs-6 {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format(abs($transaction->amount), 2, ',', ' ') }} €
                                                </span>
                                            </td>
                                            <td>
                                                @if($hasMatch)
                                                    {{-- Auto-matched with high confidence --}}
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-success px-3 py-2">
                                                            <i class="fas fa-check-double"></i>
                                                            Match {{ round($result['confidence'] * 100) }}%
                                                        </span>
                                                        <button
                                                            @click="acceptAutoMatch(transactionId)"
                                                            class="btn btn-sm btn-success"
                                                            :disabled="loading"
                                                        >
                                                            <i class="fas fa-thumbs-up"></i> Valider
                                                        </button>
                                                    </div>
                                                @elseif($hasSuggestions)
                                                    {{-- Has suggestions but needs confirmation --}}
                                                    <button
                                                        @click="showDetails = !showDetails"
                                                        class="btn btn-sm btn-outline-primary w-100"
                                                    >
                                                        <i class="fas fa-lightbulb"></i>
                                                        {{ count($result['suggestions']) }} suggestion(s)
                                                        <i class="fas" :class="showDetails ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary px-3 py-2">
                                                        <i class="fas fa-question-circle"></i>
                                                        Aucune correspondance
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button
                                                    @click="openManualModal(transactionId)"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    :disabled="loading"
                                                    title="Sélectionner manuellement"
                                                >
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Suggestions details (expandable) --}}
                                        @if($hasSuggestions)
                                            <tr x-show="showDetails" x-collapse style="display: none;">
                                                <td colspan="6" class="bg-light p-0">
                                                    <div class="p-4">
                                                        <h6 class="fw-bold text-primary mb-3">
                                                            <i class="fas fa-brain"></i>
                                                            Suggestions IA (classées par confiance)
                                                        </h6>
                                                        <div class="row g-3">
                                                            @foreach($result['suggestions'] as $index => $suggestion)
                                                                <div class="col-12">
                                                                    <div class="card shadow-sm border-start border-4 {{ $suggestion['confidence'] >= 0.8 ? 'border-success' : ($suggestion['confidence'] >= 0.6 ? 'border-warning' : 'border-secondary') }}">
                                                                        <div class="card-body p-3">
                                                                            <div class="row align-items-center">
                                                                                <div class="col-auto">
                                                                                    <div class="position-relative" style="width: 60px; height: 60px;">
                                                                                        <svg class="position-absolute" width="60" height="60">
                                                                                            <circle cx="30" cy="30" r="25" fill="none" stroke="#e9ecef" stroke-width="5"/>
                                                                                            <circle
                                                                                                cx="30" cy="30" r="25" fill="none"
                                                                                                stroke="{{ $suggestion['confidence'] >= 0.8 ? '#28a745' : ($suggestion['confidence'] >= 0.6 ? '#ffc107' : '#6c757d') }}"
                                                                                                stroke-width="5"
                                                                                                stroke-dasharray="{{ $suggestion['confidence'] * 157 }} 157"
                                                                                                transform="rotate(-90 30 30)"
                                                                                            />
                                                                                        </svg>
                                                                                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                                                            <div class="fw-bold">{{ round($suggestion['confidence'] * 100) }}<small>%</small></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col">
                                                                                    <div class="mb-2">
                                                                                        <span class="badge bg-primary me-2">
                                                                                            {{ $suggestion['invoice']['invoice_number'] }}
                                                                                        </span>
                                                                                        <span class="fw-semibold">
                                                                                            {{ $suggestion['invoice']['partner']['name'] ?? 'N/A' }}
                                                                                        </span>
                                                                                    </div>
                                                                                    <div class="d-flex gap-3 small text-muted">
                                                                                        <span>
                                                                                            <i class="fas fa-euro-sign"></i>
                                                                                            {{ number_format($suggestion['invoice']['amount_due'], 2, ',', ' ') }} €
                                                                                        </span>
                                                                                        <span>
                                                                                            <i class="fas fa-calendar"></i>
                                                                                            Échéance: {{ \Carbon\Carbon::parse($suggestion['invoice']['due_date'])->format('d/m/Y') }}
                                                                                        </span>
                                                                                    </div>
                                                                                    @if(isset($suggestion['details']))
                                                                                        <div class="mt-2">
                                                                                            @foreach($suggestion['details'] as $key => $detail)
                                                                                                @if(isset($detail['match']))
                                                                                                    <span class="badge bg-{{ $detail['match'] === 'exact' ? 'success' : 'info' }} me-1">
                                                                                                        <i class="fas fa-{{ $detail['match'] === 'exact' ? 'check' : 'info-circle' }}"></i>
                                                                                                        {{ ucfirst($key) }}: {{ $detail['match'] }}
                                                                                                    </span>
                                                                                                @endif
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="col-auto">
                                                                                    <button
                                                                                        @click="reconcileWith(transactionId, '{{ $suggestion['invoice']['id'] }}')"
                                                                                        class="btn btn-primary"
                                                                                        :disabled="loading"
                                                                                    >
                                                                                        <i class="fas fa-link"></i>
                                                                                        Réconcilier
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($transactions->hasPages())
                            <div class="card-footer bg-white">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="p-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            </div>
                            <h4 class="fw-bold text-success mb-2">Toutes les transactions sont réconciliées!</h4>
                            <p class="text-muted">Aucune transaction en attente de réconciliation.</p>
                            <a href="{{ route('bank.import') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-upload"></i>
                                Importer des transactions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reconciliation() {
    return {
        loading: false,

        init() {
            console.log('Reconciliation module with AI initialized');
        },

        async acceptAutoMatch(transactionId) {
            if (!confirm('Valider cette réconciliation automatique?')) {
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post(`/api/v1/bank/reconcile/auto/${transactionId}`);

                if (response.data.success) {
                    this.showSuccess('Réconciliation effectuée avec succès!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showError(response.data.message || 'Erreur lors de la réconciliation');
                }
            } catch (error) {
                console.error('Reconciliation error:', error);
                this.showError(error.response?.data?.message || 'Erreur réseau');
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
                const response = await axios.post('/api/v1/bank/reconcile/manual', {
                    transaction_id: transactionId,
                    invoice_id: invoiceId
                });

                if (response.data.success) {
                    this.showSuccess('Réconciliation effectuée avec succès!');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showError(response.data.message || 'Erreur lors de la réconciliation');
                }
            } catch (error) {
                console.error('Manual reconciliation error:', error);
                this.showError(error.response?.data?.message || 'Erreur réseau');
            } finally {
                this.loading = false;
            }
        },

        openManualModal(transactionId) {
            alert('Modal de sélection manuelle à implémenter');
            // TODO: Ouvrir modal Bootstrap avec recherche de factures
        },

        async batchReconcile() {
            const count = {{ $transactions->count() }};
            if (!confirm(`Lancer la réconciliation automatique de ${count} transaction(s)?`)) {
                return;
            }

            this.loading = true;

            try {
                // Récupérer tous les IDs depuis le backend
                const transactionIds = @json($transactions->pluck('id'));

                const response = await axios.post('/api/v1/bank/reconcile/batch', {
                    transaction_ids: transactionIds
                });

                if (response.data.success) {
                    const r = response.data.results;
                    this.showSuccess(
                        `Terminé! ${r.auto_matched} réconciliées automatiquement, ` +
                        `${r.suggestions} avec suggestions, ${r.no_match} sans correspondance`
                    );
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    this.showError(response.data.message || 'Erreur lors du traitement');
                }
            } catch (error) {
                console.error('Batch reconciliation error:', error);
                this.showError(error.response?.data?.message || 'Erreur réseau');
            } finally {
                this.loading = false;
            }
        },

        showSuccess(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                alert(message);
            }
        },

        showError(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert('Erreur: ' + message);
            }
        }
    };
}
</script>
@endpush
@endsection
