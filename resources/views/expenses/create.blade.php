@extends('layouts.app')

@section('title', isset($expense) ? 'Modifier la dépense' : 'Nouvelle dépense')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('expenses.dashboard') }}" class="text-muted">Notes de frais</a>
                <span class="text-muted">/</span>
                {{ isset($expense) ? 'Modifier' : 'Nouvelle dépense' }}
            </h4>
        </div>
    </div>

    <form action="{{ isset($expense) ? route('expenses.update', $expense) : route('expenses.store') }}"
          method="POST" enctype="multipart/form-data" id="expenseForm">
        @csrf
        @if(isset($expense))
            @method('PUT')
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Type Selection -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="expense_type" id="type_standard"
                                       value="standard" {{ old('is_mileage', $expense->is_mileage ?? false) ? '' : 'checked' }}
                                       onchange="toggleExpenseType()">
                                <label class="form-check-label" for="type_standard">
                                    <i class="ti ti-receipt me-1"></i> Dépense standard
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="expense_type" id="type_mileage"
                                       value="mileage" {{ old('is_mileage', $expense->is_mileage ?? false) ? 'checked' : '' }}
                                       onchange="toggleExpenseType()">
                                <label class="form-check-label" for="type_mileage">
                                    <i class="ti ti-car me-1"></i> Indemnité kilométrique
                                </label>
                            </div>
                        </div>
                        <input type="hidden" name="is_mileage" id="is_mileage" value="{{ old('is_mileage', $expense->is_mileage ?? 0) }}">
                    </div>
                </div>

                <!-- Standard Expense -->
                <div id="standardExpense" class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations de la dépense</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="expense_date">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                       id="expense_date" name="expense_date"
                                       value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}" required>
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="category_id">Catégorie</label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($categories->where('is_mileage', false) as $cat)
                                        <option value="{{ $cat->id }}"
                                                data-vat="{{ $cat->default_vat_rate ?? 21 }}"
                                                {{ old('category_id', $expense->category_id ?? $selectedCategory?->id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="merchant">Fournisseur / Marchand</label>
                                <input type="text" class="form-control @error('merchant') is-invalid @enderror"
                                       id="merchant" name="merchant"
                                       value="{{ old('merchant', $expense->merchant ?? '') }}"
                                       placeholder="Nom du fournisseur">
                                @error('merchant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="payment_method">Mode de paiement <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror"
                                        id="payment_method" name="payment_method" required>
                                    @foreach(\App\Models\EmployeeExpense::PAYMENT_METHODS as $key => $method)
                                        <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method ?? 'personal_card') == $key ? 'selected' : '' }}>
                                            {{ $method['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2" required
                                          placeholder="Description de la dépense...">{{ old('description', $expense->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mileage Expense -->
                <div id="mileageExpense" class="card mb-4" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Trajet</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="expense_date_mileage">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                       id="expense_date_mileage"
                                       max="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="vehicle_type">Type de véhicule</label>
                                <select class="form-select @error('vehicle_type') is-invalid @enderror"
                                        id="vehicle_type" name="vehicle_type" onchange="updateMileageRate()">
                                    @foreach(\App\Models\EmployeeExpense::VEHICLE_TYPES as $key => $vehicle)
                                        <option value="{{ $key }}"
                                                data-rate="{{ $vehicle['rate'] }}"
                                                {{ old('vehicle_type', $expense->vehicle_type ?? 'car') == $key ? 'selected' : '' }}>
                                            {{ $vehicle['label'] }} ({{ number_format($vehicle['rate'], 4, ',', '') }} €/km)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="distance_km">Distance (km) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('distance_km') is-invalid @enderror"
                                       id="distance_km" name="distance_km" step="0.1" min="0"
                                       value="{{ old('distance_km', $expense->distance_km ?? '') }}"
                                       onchange="calculateMileageAmount()">
                                @error('distance_km')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="departure">Départ</label>
                                <input type="text" class="form-control @error('departure') is-invalid @enderror"
                                       id="departure" name="departure"
                                       value="{{ old('departure', $expense->departure ?? '') }}"
                                       placeholder="Lieu de départ">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="destination">Destination</label>
                                <input type="text" class="form-control @error('destination') is-invalid @enderror"
                                       id="destination" name="destination"
                                       value="{{ old('destination', $expense->destination ?? '') }}"
                                       placeholder="Lieu d'arrivée">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description_mileage">Motif du déplacement <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description_mileage" rows="2"
                                          placeholder="Motif du déplacement..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div class="card mb-4" id="amountCard">
                    <div class="card-header">
                        <h5 class="mb-0">Montant</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="amount">Montant TTC <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" step="0.01" min="0.01"
                                           value="{{ old('amount', $expense->amount ?? '') }}"
                                           onchange="calculateVat()" required>
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="vatRateContainer">
                                <label class="form-label" for="vat_rate">Taux TVA</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('vat_rate') is-invalid @enderror"
                                           id="vat_rate" name="vat_rate" step="0.01" min="0" max="100"
                                           value="{{ old('vat_rate', $expense->vat_rate ?? 21) }}"
                                           onchange="calculateVat()">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-4" id="vatAmountContainer">
                                <label class="form-label">TVA calculée</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="vat_display" readonly>
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Justificatif</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="receipt">Télécharger un justificatif</label>
                            <input type="file" class="form-control @error('receipt') is-invalid @enderror"
                                   id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Formats acceptés: JPG, PNG, PDF (max 10MB)</small>
                            @error('receipt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if(isset($expense) && $expense->has_receipt)
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-file-check me-2"></i>
                            Justificatif existant: {{ $expense->receipt_original_name }}
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes additionnelles</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="2"
                                  placeholder="Notes ou commentaires...">{{ old('notes', $expense->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Report Assignment -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Rapport de frais</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select @error('expense_report_id') is-invalid @enderror"
                                id="expense_report_id" name="expense_report_id">
                            <option value="">-- Non assigné --</option>
                            @foreach($reports as $report)
                                <option value="{{ $report->id }}" {{ old('expense_report_id', $expense->expense_report_id ?? '') == $report->id ? 'selected' : '' }}>
                                    {{ $report->reference }} - {{ $report->title }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">
                            <i class="ti ti-info-circle me-1"></i>
                            Vous pouvez assigner cette dépense à un rapport existant ou la laisser non assignée.
                        </small>
                    </div>
                </div>

                <!-- Billable -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Refacturation</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_billable" name="is_billable" value="1"
                                   {{ old('is_billable', $expense->is_billable ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_billable">Refacturable au client</label>
                        </div>
                        <div id="billableOptions" style="display: none;">
                            <label class="form-label" for="partner_id">Client à facturer</label>
                            <select class="form-select" id="partner_id" name="partner_id">
                                <option value="">-- Sélectionner --</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ old('partner_id', $expense->partner_id ?? '') == $partner->id ? 'selected' : '' }}>
                                        {{ $partner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Résumé</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Montant HT</span>
                            <span id="summary_net">0,00 €</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">TVA</span>
                            <span id="summary_vat">0,00 €</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-medium">Total TTC</span>
                            <span class="fw-medium text-primary" id="summary_total">0,00 €</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ isset($expense) ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">
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
function toggleExpenseType() {
    const isMileage = document.getElementById('type_mileage').checked;
    document.getElementById('is_mileage').value = isMileage ? '1' : '0';
    document.getElementById('standardExpense').style.display = isMileage ? 'none' : 'block';
    document.getElementById('mileageExpense').style.display = isMileage ? 'block' : 'none';
    document.getElementById('vatRateContainer').style.display = isMileage ? 'none' : 'block';
    document.getElementById('vatAmountContainer').style.display = isMileage ? 'none' : 'block';

    if (isMileage) {
        calculateMileageAmount();
    } else {
        calculateVat();
    }
}

function calculateVat() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const rate = parseFloat(document.getElementById('vat_rate').value) || 0;

    const net = amount / (1 + rate/100);
    const vat = amount - net;

    document.getElementById('vat_display').value = vat.toLocaleString('fr-BE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    updateSummary(net, vat, amount);
}

function calculateMileageAmount() {
    const distance = parseFloat(document.getElementById('distance_km').value) || 0;
    const vehicleSelect = document.getElementById('vehicle_type');
    const rate = parseFloat(vehicleSelect.selectedOptions[0]?.dataset.rate) || 0.4259;

    const amount = distance * rate;
    document.getElementById('amount').value = amount.toFixed(2);

    updateSummary(amount, 0, amount);
}

function updateMileageRate() {
    calculateMileageAmount();
}

function updateSummary(net, vat, total) {
    document.getElementById('summary_net').textContent = net.toLocaleString('fr-BE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
    document.getElementById('summary_vat').textContent = vat.toLocaleString('fr-BE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
    document.getElementById('summary_total').textContent = total.toLocaleString('fr-BE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    toggleExpenseType();

    // Category change updates VAT rate
    document.getElementById('category_id').addEventListener('change', function() {
        const option = this.selectedOptions[0];
        if (option && option.dataset.vat) {
            document.getElementById('vat_rate').value = option.dataset.vat;
            calculateVat();
        }
    });

    // Billable toggle
    document.getElementById('is_billable').addEventListener('change', function() {
        document.getElementById('billableOptions').style.display = this.checked ? 'block' : 'none';
    });

    // Initialize billable
    if (document.getElementById('is_billable').checked) {
        document.getElementById('billableOptions').style.display = 'block';
    }

    calculateVat();
});
</script>
@endpush
