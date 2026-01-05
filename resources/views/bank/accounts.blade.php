@extends('layouts.app')

@section('title', 'Comptes bancaires')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Comptes bancaires</h4>
            <p class="text-muted mb-0">Gérez vos comptes bancaires connectés</p>
        </div>
        <a href="{{ route('openbanking.connect') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Connecter un compte
        </a>
    </div>

    <div class="row g-4">
        @forelse($accounts ?? [] as $account)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-building-bank ti-md"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-{{ $account->is_active ? 'success' : 'secondary' }}">
                            {{ $account->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $account->name }}</h5>
                    <p class="text-muted mb-2">{{ $account->bank_name ?? 'Banque' }}</p>
                    <p class="mb-0">
                        <code>{{ $account->iban ?? $account->account_number }}</code>
                    </p>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Solde actuel</small>
                            <h4 class="mb-0 {{ ($account->balance ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($account->balance ?? 0, 2, ',', ' ') }} €
                            </h4>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-icon btn-outline-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('openbanking.account', $account) }}">
                                    <i class="ti ti-eye me-2"></i> Voir les transactions
                                </a>
                                <a class="dropdown-item" href="{{ route('openbanking.sync', $account) }}">
                                    <i class="ti ti-refresh me-2"></i> Synchroniser
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('openbanking.disconnect', $account) }}" method="POST"
                                      onsubmit="return confirm('Déconnecter ce compte ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ti ti-unlink me-2"></i> Déconnecter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        <i class="ti ti-refresh me-1"></i>
                        Dernière sync: {{ $account->last_sync_at?->diffForHumans() ?? 'Jamais' }}
                    </small>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-building-bank ti-xl text-muted mb-3 d-block"></i>
                    <h5>Aucun compte bancaire connecté</h5>
                    <p class="text-muted mb-3">Connectez vos comptes pour synchroniser automatiquement vos transactions</p>
                    <a href="{{ route('openbanking.connect') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Connecter un compte
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
