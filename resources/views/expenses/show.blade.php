@extends('layouts.app')

@section('title', 'Détail de la dépense')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                <a href="{{ route('expenses.index') }}" class="text-muted">Mes dépenses</a>
                <span class="text-muted">/</span>
                {{ $expense->reference ?? 'Dépense' }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            @if($expense->status === 'draft')
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            @endif
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Expense Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails de la dépense</h5>
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
                    <span class="badge bg-label-{{ $statusColors[$expense->status] ?? 'secondary' }}">
                        {{ $statusLabels[$expense->status] ?? ucfirst($expense->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Description</small>
                            <p class="fw-medium mb-0">{{ $expense->description }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Date</small>
                            <p class="fw-medium mb-0">{{ $expense->expense_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Catégorie</small>
                            <p class="fw-medium mb-0">{{ $expense->category->name ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block mb-1">Montant HT</small>
                            <h4 class="mb-0">{{ number_format($expense->amount_excl_vat, 2, ',', ' ') }} €</h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block mb-1">TVA ({{ $expense->vat_rate ?? 0 }}%)</small>
                            <h4 class="mb-0">{{ number_format($expense->vat_amount, 2, ',', ' ') }} €</h4>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block mb-1">Total TTC</small>
                            <h4 class="mb-0 text-primary">{{ number_format($expense->amount_incl_vat, 2, ',', ' ') }} €</h4>
                        </div>
                    </div>

                    @if($expense->is_mileage)
                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Distance</small>
                            <p class="fw-medium mb-0">{{ number_format($expense->mileage_distance, 1, ',', ' ') }} km</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Tarif/km</small>
                            <p class="fw-medium mb-0">{{ number_format($expense->mileage_rate, 4, ',', ' ') }} €</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Départ → Arrivée</small>
                            <p class="fw-medium mb-0">{{ $expense->departure_location ?? '-' }} → {{ $expense->arrival_location ?? '-' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($expense->notes)
                    <hr class="my-4">
                    <div>
                        <small class="text-muted d-block mb-1">Notes</small>
                        <p class="mb-0">{{ $expense->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attachments -->
            @if($expense->attachments && $expense->attachments->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Justificatifs</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($expense->attachments as $attachment)
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center">
                                @if(Str::startsWith($attachment->mime_type, 'image/'))
                                    <img src="{{ Storage::url($attachment->path) }}" class="img-fluid rounded mb-2" style="max-height: 150px;" alt="{{ $attachment->filename }}">
                                @else
                                    <div class="py-4">
                                        <i class="ti ti-file-text ti-xl text-muted"></i>
                                    </div>
                                @endif
                                <p class="mb-1 text-truncate small">{{ $attachment->filename }}</p>
                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-download me-1"></i> Télécharger
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
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
                        <span class="fw-medium">{{ $expense->user->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Créée le</span>
                        <span class="fw-medium">{{ $expense->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($expense->expenseReport)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Rapport</span>
                        <a href="{{ route('expenses.reports.show', $expense->expenseReport) }}">
                            {{ $expense->expenseReport->reference }}
                        </a>
                    </div>
                    @endif
                    @if($expense->partner)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Fournisseur</span>
                        <span class="fw-medium">{{ $expense->partner->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($expense->status === 'draft')
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i> Modifier
                        </a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette dépense ?')">
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
