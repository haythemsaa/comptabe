@extends('layouts.app')

@section('title', 'Rapports de dépenses')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                Rapports de dépenses
            </h4>
            <p class="text-muted mb-0">Gérez vos rapports de notes de frais</p>
        </div>
        <a href="{{ route('expenses.reports.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nouveau rapport
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['draft'] ?? 0 }}</h4>
                            <small class="text-muted">Brouillons</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-file ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['submitted'] ?? 0 }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-clock ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['approved'] ?? 0 }}</h4>
                            <small class="text-muted">Approuvés</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-check ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_amount'] ?? 0, 2, ',', ' ') }} €</h4>
                            <small class="text-muted">Total en cours</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Titre</th>
                        <th>Période</th>
                        <th class="text-center">Dépenses</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center">Statut</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>
                            <a href="{{ route('expenses.reports.show', $report) }}" class="fw-medium">
                                {{ $report->reference }}
                            </a>
                        </td>
                        <td>{{ $report->title }}</td>
                        <td>
                            <small class="text-muted">
                                {{ $report->period_start?->format('d/m/Y') }} - {{ $report->period_end?->format('d/m/Y') }}
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ $report->expenses_count ?? $report->expenses->count() }}</span>
                        </td>
                        <td class="text-end fw-medium">
                            {{ number_format($report->total_amount ?? $report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €
                        </td>
                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'submitted' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'paid' => 'primary'
                                ];
                                $statusLabels = [
                                    'draft' => 'Brouillon',
                                    'submitted' => 'Soumis',
                                    'approved' => 'Approuvé',
                                    'rejected' => 'Rejeté',
                                    'paid' => 'Payé'
                                ];
                            @endphp
                            <span class="badge bg-label-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('expenses.reports.show', $report) }}">
                                        <i class="ti ti-eye me-2"></i> Voir
                                    </a>
                                    @if($report->status === 'draft')
                                    <form action="{{ route('expenses.reports.submit', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-send me-2"></i> Soumettre
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('expenses.reports.destroy', $report) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce rapport ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti ti-trash me-2"></i> Supprimer
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-file-off ti-xl mb-2 d-block"></i>
                                <p class="mb-2">Aucun rapport de dépenses</p>
                                <a href="{{ route('expenses.reports.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Créer un rapport
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="card-footer">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
