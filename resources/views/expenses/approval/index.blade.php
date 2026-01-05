@extends('layouts.app')

@section('title', 'Approbation des notes de frais')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                Approbation
            </h4>
            <p class="text-muted mb-0">Rapports de dépenses en attente d'approbation</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['pending'] ?? 0 }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
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
                            <h4 class="mb-0">{{ $stats['approved_today'] ?? 0 }}</h4>
                            <small class="text-muted">Approuvés aujourd'hui</small>
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
                            <h4 class="mb-0">{{ number_format($stats['pending_amount'] ?? 0, 0, ',', ' ') }} €</h4>
                            <small class="text-muted">Montant en attente</small>
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
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['rejected_this_month'] ?? 0 }}</h4>
                            <small class="text-muted">Rejetés (mois)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-x ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Rapports en attente d'approbation</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Employé</th>
                        <th>Titre</th>
                        <th>Soumis le</th>
                        <th class="text-center">Dépenses</th>
                        <th class="text-end">Montant</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>
                            <a href="{{ route('expenses.approval.review', $report) }}" class="fw-medium">
                                {{ $report->reference }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($report->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <span>{{ $report->user->name }}</span>
                            </div>
                        </td>
                        <td>{{ Str::limit($report->title, 30) }}</td>
                        <td>
                            {{ $report->submitted_at?->format('d/m/Y') }}
                            <br><small class="text-muted">{{ $report->submitted_at?->diffForHumans() }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ $report->expenses->count() }}</span>
                        </td>
                        <td class="text-end fw-medium">
                            {{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €
                        </td>
                        <td>
                            <a href="{{ route('expenses.approval.review', $report) }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-eye me-1"></i> Examiner
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-check-circle ti-xl mb-2 d-block text-success"></i>
                                <p class="mb-0">Aucun rapport en attente d'approbation</p>
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
