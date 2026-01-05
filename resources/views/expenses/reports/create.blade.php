@extends('layouts.app')

@section('title', 'Nouveau rapport de dépenses')

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
                Nouveau rapport
            </h4>
        </div>
    </div>

    <form action="{{ route('expenses.reports.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations du rapport</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="title">Titre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title"
                                       value="{{ old('title') }}"
                                       placeholder="Ex: Déplacements Janvier 2026"
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="period_start">Début de période</label>
                                <input type="date" class="form-control @error('period_start') is-invalid @enderror"
                                       id="period_start" name="period_start"
                                       value="{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}">
                                @error('period_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="period_end">Fin de période</label>
                                <input type="date" class="form-control @error('period_end') is-invalid @enderror"
                                       id="period_end" name="period_end"
                                       value="{{ old('period_end', now()->endOfMonth()->format('Y-m-d')) }}">
                                @error('period_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          placeholder="Notes complémentaires...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Select Expenses -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Dépenses à inclure</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                Tout sélectionner
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                Tout désélectionner
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($unassignedExpenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAll(this)">
                                        </th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Catégorie</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unassignedExpenses as $expense)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input expense-checkbox"
                                                   name="expenses[]" value="{{ $expense->id }}"
                                                   data-amount="{{ $expense->amount_incl_vat }}"
                                                   onchange="updateTotal()">
                                        </td>
                                        <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                        <td>{{ Str::limit($expense->description, 40) }}</td>
                                        <td>
                                            <span class="badge bg-label-secondary">{{ $expense->category->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-end fw-medium">{{ number_format($expense->amount_incl_vat, 2, ',', ' ') }} €</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="ti ti-receipt-off ti-xl text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-2">Aucune dépense non assignée</p>
                            <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-plus me-1"></i> Créer une dépense
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Résumé</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Dépenses sélectionnées</span>
                            <span class="fw-medium" id="selectedCount">0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total</span>
                            <span class="fw-medium text-primary" id="selectedTotal">0,00 €</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Créer le rapport
                            </button>
                            <a href="{{ route('expenses.reports.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i> Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function selectAll() {
    document.querySelectorAll('.expense-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
    updateTotal();
}

function deselectAll() {
    document.querySelectorAll('.expense-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateTotal();
}

function toggleAll(checkbox) {
    document.querySelectorAll('.expense-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateTotal();
}

function updateTotal() {
    const checkboxes = document.querySelectorAll('.expense-checkbox:checked');
    let total = 0;
    checkboxes.forEach(cb => {
        total += parseFloat(cb.dataset.amount) || 0;
    });
    document.getElementById('selectedCount').textContent = checkboxes.length;
    document.getElementById('selectedTotal').textContent = total.toLocaleString('fr-BE', {minimumFractionDigits: 2}) + ' €';
}
</script>
@endpush
