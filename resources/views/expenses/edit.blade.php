@extends('layouts.app')

@section('title', 'Modifier la dépense')

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
                Modifier
            </h4>
        </div>
    </div>

    @if($expense->status !== 'draft')
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-2"></i>
        Cette dépense ne peut plus être modifiée car elle a été soumise.
    </div>
    @endif

    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Type Selection -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="expense_type" id="type_standard"
                                       value="standard" {{ !$expense->is_mileage ? 'checked' : '' }}
                                       onchange="toggleExpenseType()" {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="type_standard">
                                    <i class="ti ti-receipt me-1"></i> Dépense standard
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="expense_type" id="type_mileage"
                                       value="mileage" {{ $expense->is_mileage ? 'checked' : '' }}
                                       onchange="toggleExpenseType()" {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="type_mileage">
                                    <i class="ti ti-car me-1"></i> Indemnité kilométrique
                                </label>
                            </div>
                        </div>
                        <input type="hidden" name="is_mileage" id="is_mileage" value="{{ $expense->is_mileage ? 1 : 0 }}">
                    </div>
                </div>

                <!-- Standard Expense -->
                <div id="standardExpense" class="card mb-4" style="{{ $expense->is_mileage ? 'display:none;' : '' }}">
                    <div class="card-header">
                        <h5 class="mb-0">Informations de la dépense</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="expense_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                       id="expense_date" name="expense_date"
                                       value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}" required {{ $expense->status !== 'draft' ? 'readonly' : '' }}>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="category_id">Catégorie</label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($categories->where('is_mileage', false) as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('description') is-invalid @enderror"
                                       id="description" name="description"
                                       value="{{ old('description', $expense->description) }}"
                                       placeholder="Ex: Déjeuner client, Fournitures bureau..." required
                                       {{ $expense->status !== 'draft' ? 'readonly' : '' }}>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="amount_excl_vat">Montant HT <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('amount_excl_vat') is-invalid @enderror"
                                           id="amount_excl_vat" name="amount_excl_vat" step="0.01" min="0"
                                           value="{{ old('amount_excl_vat', $expense->amount_excl_vat) }}"
                                           onchange="calculateVAT()" required {{ $expense->status !== 'draft' ? 'readonly' : '' }}>
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('amount_excl_vat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="vat_rate">Taux TVA</label>
                                <select class="form-select @error('vat_rate') is-invalid @enderror"
                                        id="vat_rate" name="vat_rate" onchange="calculateVAT()"
                                        {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="0" {{ old('vat_rate', $expense->vat_rate) == 0 ? 'selected' : '' }}>0%</option>
                                    <option value="6" {{ old('vat_rate', $expense->vat_rate) == 6 ? 'selected' : '' }}>6%</option>
                                    <option value="12" {{ old('vat_rate', $expense->vat_rate) == 12 ? 'selected' : '' }}>12%</option>
                                    <option value="21" {{ old('vat_rate', $expense->vat_rate) == 21 ? 'selected' : '' }}>21%</option>
                                </select>
                                @error('vat_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total TTC</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="total_display" readonly
                                           value="{{ number_format($expense->amount_incl_vat, 2, ',', ' ') }}">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Partner -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Fournisseur (optionnel)</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select @error('partner_id') is-invalid @enderror"
                                id="partner_id" name="partner_id" {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                            <option value="">-- Aucun fournisseur --</option>
                            @foreach($partners ?? [] as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id', $expense->partner_id) == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3"
                                  placeholder="Notes complémentaires..."
                                  {{ $expense->status !== 'draft' ? 'readonly' : '' }}>{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Report -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Rapport de dépenses</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select @error('expense_report_id') is-invalid @enderror"
                                id="expense_report_id" name="expense_report_id"
                                {{ $expense->status !== 'draft' ? 'disabled' : '' }}>
                            <option value="">-- Non assigné --</option>
                            @foreach($reports ?? [] as $report)
                                <option value="{{ $report->id }}" {{ old('expense_report_id', $expense->expense_report_id) == $report->id ? 'selected' : '' }}>
                                    {{ $report->reference }} - {{ $report->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('expense_report_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($expense->status === 'draft')
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            @endif
                            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Retour
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
function toggleExpenseType() {
    const isMileage = document.getElementById('type_mileage').checked;
    document.getElementById('is_mileage').value = isMileage ? 1 : 0;
    document.getElementById('standardExpense').style.display = isMileage ? 'none' : 'block';
}

function calculateVAT() {
    const amountHT = parseFloat(document.getElementById('amount_excl_vat').value) || 0;
    const vatRate = parseFloat(document.getElementById('vat_rate').value) || 0;
    const total = amountHT * (1 + vatRate / 100);
    document.getElementById('total_display').value = total.toLocaleString('fr-BE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>
@endpush
