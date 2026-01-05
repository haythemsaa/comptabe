@extends('layouts.app')

@section('title', 'Mes dépenses')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span> Mes dépenses
            </h4>
            <p class="text-muted mb-0">{{ $stats['total'] }} dépenses au total</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nouvelle dépense
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-label-warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-warning">{{ $stats['pending'] }}</h5>
                            <small>En attente</small>
                        </div>
                        <i class="ti ti-clock ti-xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-label-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-primary">{{ number_format($stats['this_month'], 2, ',', ' ') }} €</h5>
                            <small>Ce mois</small>
                        </div>
                        <i class="ti ti-calendar ti-xl text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-label-secondary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small>Total dépenses</small>
                        </div>
                        <i class="ti ti-receipt ti-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Date début</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date fin</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="category">
                            <option value="">Toutes</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            <option value="">Tous</option>
                            @foreach(\App\Models\EmployeeExpense::STATUSES as $key => $status)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $status['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Description...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Paiement</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center">Justif.</th>
                        <th class="text-center">Statut</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr>
                        <td>
                            <span>{{ $expense->expense_date->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('expenses.show', $expense) }}" class="fw-medium text-body">
                                {{ Str::limit($expense->description, 50) }}
                            </a>
                            @if($expense->merchant)
                                <br><small class="text-muted">{{ $expense->merchant }}</small>
                            @endif
                            @if($expense->expenseReport)
                                <br><small class="text-muted">
                                    <i class="ti ti-folder"></i> {{ $expense->expenseReport->reference }}
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($expense->category)
                                <span class="badge" style="background-color: {{ $expense->category->color }}20; color: {{ $expense->category->color }}">
                                    <i class="ti ti-{{ $expense->category->icon ?? 'receipt' }} me-1"></i>
                                    {{ $expense->category->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $expense->getPaymentMethodLabel() }}</small>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($expense->amount, 2, ',', ' ') }} €</span>
                            @if($expense->vat_amount > 0)
                                <br><small class="text-muted">TVA: {{ number_format($expense->vat_amount, 2, ',', ' ') }} €</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($expense->has_receipt)
                                <span class="text-success" title="Justificatif présent">
                                    <i class="ti ti-file-check ti-md"></i>
                                </span>
                            @else
                                <span class="text-muted" title="Pas de justificatif">
                                    <i class="ti ti-file-x ti-md"></i>
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-{{ $expense->getStatusColor() }}">
                                {{ $expense->getStatusLabel() }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('expenses.show', $expense) }}">
                                        <i class="ti ti-eye me-2"></i> Voir
                                    </a>
                                    @if($expense->canBeEdited())
                                    <a class="dropdown-item" href="{{ route('expenses.edit', $expense) }}">
                                        <i class="ti ti-edit me-2"></i> Modifier
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Supprimer cette dépense ?')">
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
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-receipt-off ti-xl mb-2 d-block"></i>
                                <p class="mb-2">Aucune dépense enregistrée</p>
                                <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Ajouter une dépense
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="card-footer">
            {{ $expenses->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
