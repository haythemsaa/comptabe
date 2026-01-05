@extends('layouts.app')

@section('title', 'Compte: ' . ($account->name ?? 'Compte bancaire'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('bank.accounts') }}" class="text-muted">Comptes</a>
                <span class="text-muted">/</span> {{ $account->name }}
            </h4>
            <p class="text-muted mb-0">{{ $account->iban ?? $account->account_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('openbanking.sync', $account) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="ti ti-refresh me-1"></i> Synchroniser
                </button>
            </form>
            <a href="{{ route('bank.accounts') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <!-- Balance Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <small class="text-muted">Solde actuel</small>
                    <h2 class="mb-0 {{ ($account->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($account->balance ?? 0, 2, ',', ' ') }} €
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <small class="text-muted">Entrées (mois)</small>
                    <h2 class="mb-0 text-success">+{{ number_format($stats['income'] ?? 0, 2, ',', ' ') }} €</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <small class="text-muted">Sorties (mois)</small>
                    <h2 class="mb-0 text-danger">-{{ number_format($stats['expenses'] ?? 0, 2, ',', ' ') }} €</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transactions récentes</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="date" class="form-control form-control-sm" name="from" value="{{ request('from') }}">
                <input type="date" class="form-control form-control-sm" name="to" value="{{ request('to') }}">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th class="text-end">Montant</th>
                        <th>Rapprochement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $tx)
                    <tr>
                        <td>{{ $tx->transaction_date?->format('d/m/Y') }}</td>
                        <td>
                            <span class="fw-medium">{{ Str::limit($tx->description, 40) }}</span>
                            @if($tx->counterparty_name)
                                <br><small class="text-muted">{{ $tx->counterparty_name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($tx->category)
                                <span class="badge bg-label-secondary">{{ $tx->category }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-medium {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount, 2, ',', ' ') }} €
                            </span>
                        </td>
                        <td>
                            @if($tx->is_reconciled)
                                <span class="badge bg-label-success"><i class="ti ti-check"></i> Rapproché</span>
                            @elseif($tx->matched_invoice_id)
                                <a href="{{ route('invoices.show', $tx->matched_invoice_id) }}" class="badge bg-label-info">
                                    Lié
                                </a>
                            @else
                                <span class="badge bg-label-warning">En attente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Aucune transaction
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($transactions) && $transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
