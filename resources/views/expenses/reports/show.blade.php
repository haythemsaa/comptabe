@extends('layouts.app')

@section('title', 'Rapport ' . $report->reference)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                <a href="{{ route('expenses.reports.index') }}" class="text-muted">Rapports</a>
                <span class="text-muted">/</span>
                {{ $report->reference }}
            </h4>
            <p class="text-muted mb-0">{{ $report->title }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($report->status === 'draft')
            <form action="{{ route('expenses.reports.submit', $report) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-send me-1"></i> Soumettre
                </button>
            </form>
            @endif
            <a href="{{ route('expenses.reports.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Summary -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Résumé du rapport</h5>
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
                    <span class="badge bg-label-{{ $statusColors[$report->status] ?? 'secondary' }} fs-6">
                        {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="avatar avatar-lg mx-auto mb-2 bg-label-primary">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-receipt ti-lg"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ $report->expenses->count() }}</h3>
                            <small class="text-muted">Dépenses</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="avatar avatar-lg mx-auto mb-2 bg-label-success">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-currency-euro ti-lg"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €</h3>
                            <small class="text-muted">Montant total TTC</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="avatar avatar-lg mx-auto mb-2 bg-label-info">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-calendar ti-lg"></i>
                                </span>
                            </div>
                            <h3 class="mb-0">{{ $report->period_start?->format('d/m') }} - {{ $report->period_end?->format('d/m') }}</h3>
                            <small class="text-muted">Période</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Dépenses incluses</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th class="text-end">Montant HT</th>
                                <th class="text-end">TVA</th>
                                <th class="text-end">Total TTC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report->expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('expenses.show', $expense) }}">
                                        {{ Str::limit($expense->description, 40) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $expense->category->name ?? '-' }}</span>
                                </td>
                                <td class="text-end">{{ number_format($expense->amount_excl_vat, 2, ',', ' ') }} €</td>
                                <td class="text-end">{{ number_format($expense->vat_amount, 2, ',', ' ') }} €</td>
                                <td class="text-end fw-medium">{{ number_format($expense->amount_incl_vat, 2, ',', ' ') }} €</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Aucune dépense dans ce rapport
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">Total</th>
                                <th class="text-end">{{ number_format($report->expenses->sum('amount_excl_vat'), 2, ',', ' ') }} €</th>
                                <th class="text-end">{{ number_format($report->expenses->sum('vat_amount'), 2, ',', ' ') }} €</th>
                                <th class="text-end">{{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Notes -->
            @if($report->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $report->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Rejection reason -->
            @if($report->status === 'rejected' && $report->rejection_reason)
            <div class="alert alert-danger">
                <h6 class="alert-heading">
                    <i class="ti ti-x me-1"></i> Rapport rejeté
                </h6>
                <p class="mb-0">{{ $report->rejection_reason }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Employé</span>
                        <span class="fw-medium">{{ $report->user->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créé le</span>
                        <span class="fw-medium">{{ $report->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($report->submitted_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Soumis le</span>
                        <span class="fw-medium">{{ $report->submitted_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($report->approvedBy)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Approuvé par</span>
                        <span class="fw-medium">{{ $report->approvedBy->name }}</span>
                    </div>
                    @endif
                    @if($report->approved_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Approuvé le</span>
                        <span class="fw-medium">{{ $report->approved_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($report->paidBy)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payé par</span>
                        <span class="fw-medium">{{ $report->paidBy->name }}</span>
                    </div>
                    @endif
                    @if($report->paid_at)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Payé le</span>
                        <span class="fw-medium">{{ $report->paid_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($report->status === 'draft')
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('expenses.reports.submit', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-send me-1"></i> Soumettre pour approbation
                            </button>
                        </form>
                        <form action="{{ route('expenses.reports.destroy', $report) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce rapport ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
