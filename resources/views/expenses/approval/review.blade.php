@extends('layouts.app')

@section('title', 'Examiner le rapport ' . $report->reference)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                <a href="{{ route('expenses.approval') }}" class="text-muted">Approbation</a>
                <span class="text-muted">/</span>
                {{ $report->reference }}
            </h4>
            <p class="text-muted mb-0">Examiner et approuver/rejeter ce rapport</p>
        </div>
        <a href="{{ route('expenses.approval') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Report Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ $report->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Employé</small>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($report->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $report->user->name }}</span>
                                    <br><small class="text-muted">{{ $report->user->email }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Période</small>
                            <span class="fw-medium">
                                {{ $report->period_start?->format('d/m/Y') }} - {{ $report->period_end?->format('d/m/Y') }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Soumis le</small>
                            <span class="fw-medium">{{ $report->submitted_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    @if($report->notes)
                    <hr>
                    <small class="text-muted d-block mb-1">Notes de l'employé</small>
                    <p class="mb-0">{{ $report->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Expenses List -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dépenses ({{ $report->expenses->count() }})</h5>
                    <span class="badge bg-primary">
                        Total: {{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th>Justificatif</th>
                                <th class="text-end">Montant TTC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-medium">{{ $expense->description }}</span>
                                    @if($expense->is_mileage)
                                        <br><small class="text-muted">
                                            <i class="ti ti-car me-1"></i>{{ number_format($expense->mileage_distance, 1) }} km
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $expense->category->name ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($expense->attachments && $expense->attachments->count() > 0)
                                        <a href="{{ Storage::url($expense->attachments->first()->path) }}"
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file me-1"></i> Voir
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium">{{ number_format($expense->amount_incl_vat, 2, ',', ' ') }} €</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4">Total</th>
                                <th class="text-end">{{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar - Actions -->
        <div class="col-lg-4">
            <!-- Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Nombre de dépenses</span>
                        <span class="fw-medium">{{ $report->expenses->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total HT</span>
                        <span class="fw-medium">{{ number_format($report->expenses->sum('amount_excl_vat'), 2, ',', ' ') }} €</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total TVA</span>
                        <span class="fw-medium">{{ number_format($report->expenses->sum('vat_amount'), 2, ',', ' ') }} €</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">Total TTC</span>
                        <span class="fw-bold text-primary">{{ number_format($report->expenses->sum('amount_incl_vat'), 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>

            <!-- Decision -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Décision</h5>
                </div>
                <div class="card-body">
                    <!-- Approve Form -->
                    <form action="{{ route('expenses.approval.approve', $report) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Commentaire (optionnel)</label>
                            <textarea class="form-control" name="comment" rows="2" placeholder="Commentaire d'approbation..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-check me-1"></i> Approuver
                        </button>
                    </form>

                    <hr class="my-3">

                    <!-- Reject Form -->
                    <form action="{{ route('expenses.approval.reject', $report) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="rejection_reason" rows="2" required
                                      placeholder="Expliquez pourquoi ce rapport est rejeté..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-danger w-100"
                                onclick="return confirm('Rejeter ce rapport ?')">
                            <i class="ti ti-x me-1"></i> Rejeter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
