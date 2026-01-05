@extends('layouts.app')

@section('title', 'Notes de frais')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Notes de frais</h4>
            <p class="text-muted mb-0">Gérez vos dépenses professionnelles</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('expenses.reports.create') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-plus me-1"></i> Nouveau rapport
            </a>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Nouvelle dépense
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-file ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $myStats['draft'] }}</h4>
                            <small class="text-muted">Brouillons</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-clock ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $myStats['pending'] }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-check ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $myStats['approved'] }}</h4>
                            <small class="text-muted">Approuvées</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ number_format($myStats['month_total'], 2, ',', ' ') }} €</h4>
                            <small class="text-muted">Ce mois</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Add -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Ajouter une dépense</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($categories->take(8) as $category)
                        <div class="col-6">
                            <a href="{{ route('expenses.create', ['category' => $category->id]) }}"
                               class="btn btn-outline-secondary w-100 py-3 text-start">
                                <i class="ti ti-{{ $category->icon ?? 'receipt' }} me-2"
                                   style="color: {{ $category->color }}"></i>
                                <span class="d-block text-truncate">{{ $category->name }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('expenses.create') }}" class="btn btn-primary w-100">
                            <i class="ti ti-plus me-1"></i> Autre dépense
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Reports -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mes rapports de frais</h5>
                    <a href="{{ route('expenses.reports.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Titre</th>
                                <th class="text-center">Dépenses</th>
                                <th class="text-end">Montant</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myReports as $report)
                            <tr>
                                <td>
                                    <a href="{{ route('expenses.reports.show', $report) }}" class="fw-medium">
                                        {{ $report->reference }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($report->title, 30) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-secondary">{{ $report->expenses->count() }}</span>
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($report->total_amount, 2, ',', ' ') }} €
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $report->getStatusColor() }}">
                                        {{ $report->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-2">Aucun rapport de frais</p>
                                    <a href="{{ route('expenses.reports.create') }}" class="btn btn-sm btn-primary">
                                        Créer un rapport
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Expenses -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mes dernières dépenses</h5>
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th class="text-end">Montant</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myExpenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('expenses.show', $expense) }}" class="text-body">
                                        {{ Str::limit($expense->description, 40) }}
                                    </a>
                                    @if($expense->merchant)
                                        <br><small class="text-muted">{{ $expense->merchant }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->category)
                                        <span class="badge" style="background-color: {{ $expense->category->color }}20; color: {{ $expense->category->color }}">
                                            {{ $expense->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($expense->amount, 2, ',', ' ') }} €
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $expense->getStatusColor() }}">
                                        {{ $expense->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <p class="text-muted mb-0">Aucune dépense enregistrée</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- By Category -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Par catégorie (ce mois)</h5>
                </div>
                <div class="card-body">
                    @forelse($byCategory as $cat)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded" style="background-color: {{ $cat->category?->color ?? '#6366f1' }}20">
                                <i class="ti ti-{{ $cat->category?->icon ?? 'receipt' }}"
                                   style="color: {{ $cat->category?->color ?? '#6366f1' }}"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span>{{ $cat->category?->name ?? 'Non catégorisé' }}</span>
                                <span class="fw-medium">{{ number_format($cat->total, 2, ',', ' ') }} €</span>
                            </div>
                            <small class="text-muted">{{ $cat->count }} dépense(s)</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Aucune dépense ce mois</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($pendingApproval->count() > 0)
    <!-- Pending Approval (Manager view) -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ti ti-clock-hour-4 text-warning me-2"></i>
                Rapports en attente d'approbation
            </h5>
            <a href="{{ route('expenses.approval.index') }}" class="btn btn-sm btn-outline-warning">
                Voir tout
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Employé</th>
                        <th>Titre</th>
                        <th class="text-center">Dépenses</th>
                        <th class="text-end">Montant</th>
                        <th>Soumis le</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingApproval as $report)
                    <tr>
                        <td>
                            <a href="{{ route('expenses.approval.review', $report) }}" class="fw-medium">
                                {{ $report->reference }}
                            </a>
                        </td>
                        <td>{{ $report->user->name }}</td>
                        <td>{{ Str::limit($report->title, 30) }}</td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ $report->expenses->count() }}</span>
                        </td>
                        <td class="text-end fw-medium">
                            {{ number_format($report->total_amount, 2, ',', ' ') }} €
                        </td>
                        <td>{{ $report->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('expenses.approval.review', $report) }}" class="btn btn-sm btn-primary">
                                Examiner
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
