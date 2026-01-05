@extends('layouts.app')

@section('title', 'Modifier le mouvement')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span>
                <a href="{{ route('stock.movements.index') }}" class="text-muted">Mouvements</a>
                <span class="text-muted">/</span>
                Modifier #{{ $movement->reference }}
            </h4>
        </div>
    </div>

    @if($movement->status !== 'draft')
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-2"></i>
        Ce mouvement a déjà été validé et ne peut plus être modifié. Seules les notes peuvent être mises à jour.
    </div>
    @endif

    <form action="{{ route('stock.movements.update', $movement) }}" method="POST" id="movementForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Type & Basic Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Type de mouvement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                @php $types = \App\Models\StockMovement::TYPES; @endphp
                                <div class="btn-group w-100" role="group">
                                    @foreach($types as $key => $type)
                                    <input type="radio" class="btn-check" name="type" id="type_{{ $key }}"
                                           value="{{ $key }}" {{ old('type', $movement->type) == $key ? 'checked' : '' }}
                                           {{ $movement->status !== 'draft' ? 'disabled' : '' }}
                                           onchange="updateFormForType('{{ $key }}')">
                                    <label class="btn btn-outline-{{ $type['color'] }}" for="type_{{ $key }}">
                                        <i class="ti ti-{{ $type['icon'] }} me-1"></i>
                                        {{ $type['label'] }}
                                    </label>
                                    @endforeach
                                </div>
                                @error('type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product & Quantity -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Produit et quantité</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="product_id">Produit <span class="text-danger">*</span></label>
                                <select class="form-select @error('product_id') is-invalid @enderror"
                                        id="product_id" name="product_id" required
                                        {{ $movement->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner un produit --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                                data-sku="{{ $product->sku }}"
                                                data-cost="{{ $product->cost_price ?? 0 }}"
                                                {{ old('product_id', $movement->product_id) == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="quantity">Quantité <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity" name="quantity" step="0.01" min="0.01"
                                       value="{{ old('quantity', $movement->quantity) }}"
                                       {{ $movement->status !== 'draft' ? 'readonly' : '' }}
                                       required onchange="calculateTotal()">
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="unit_cost">Coût unitaire</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('unit_cost') is-invalid @enderror"
                                           id="unit_cost" name="unit_cost" step="0.01" min="0"
                                           value="{{ old('unit_cost', $movement->unit_cost) }}"
                                           {{ $movement->status !== 'draft' ? 'readonly' : '' }}
                                           onchange="calculateTotal()">
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('unit_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Coût total</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="total_cost_display" readonly
                                           value="{{ number_format($movement->total_cost ?? 0, 2, ',', ' ') }}">
                                    <span class="input-group-text">€</span>
                                </div>
                                <input type="hidden" name="total_cost" id="total_cost" value="{{ $movement->total_cost }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Entrepôt</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="warehouse_id">
                                    <span id="warehouse_label">{{ $movement->type === 'transfer' ? 'Entrepôt source' : 'Entrepôt' }}</span> <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                        id="warehouse_id" name="warehouse_id" required
                                        {{ $movement->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('warehouse_id', $movement->warehouse_id) == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }} ({{ $wh->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6" id="destination_warehouse_container" style="{{ $movement->type === 'transfer' ? '' : 'display: none;' }}">
                                <label class="form-label" for="destination_warehouse_id">
                                    Entrepôt de destination <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('destination_warehouse_id') is-invalid @enderror"
                                        id="destination_warehouse_id" name="destination_warehouse_id"
                                        {{ $movement->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('destination_warehouse_id', $movement->destination_warehouse_id) == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }} ({{ $wh->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reason & Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Détails</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="reason">Motif</label>
                                <select class="form-select @error('reason') is-invalid @enderror"
                                        id="reason" name="reason"
                                        {{ $movement->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach(\App\Models\StockMovement::REASONS as $key => $reason)
                                        <option value="{{ $key }}" {{ old('reason', $movement->reason) == $key ? 'selected' : '' }}>
                                            {{ $reason }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="batch_number">N° de lot</label>
                                <input type="text" class="form-control @error('batch_number') is-invalid @enderror"
                                       id="batch_number" name="batch_number"
                                       value="{{ old('batch_number', $movement->batch_number) }}"
                                       {{ $movement->status !== 'draft' ? 'readonly' : '' }}
                                       placeholder="LOT-2026-001">
                                @error('batch_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="expiry_date">Date d'expiration</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror"
                                       id="expiry_date" name="expiry_date"
                                       value="{{ old('expiry_date', $movement->expiry_date ? $movement->expiry_date->format('Y-m-d') : '') }}"
                                       {{ $movement->status !== 'draft' ? 'readonly' : '' }}>
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          placeholder="Notes ou commentaires...">{{ old('notes', $movement->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Référence</span>
                            <span class="fw-medium">{{ $movement->reference }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Statut</span>
                            @php $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger']; @endphp
                            <span class="badge bg-label-{{ $statusColors[$movement->status] ?? 'secondary' }}">
                                {{ ucfirst($movement->status) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Créé le</span>
                            <span class="fw-medium">{{ $movement->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($movement->validated_at)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Validé le</span>
                            <span class="fw-medium">{{ $movement->validated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($movement->validatedBy)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Validé par</span>
                            <span class="fw-medium">{{ $movement->validatedBy->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($movement->status === 'draft')
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            <button type="submit" name="action" value="validate" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Valider le mouvement
                            </button>
                            @else
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer les notes
                            </button>
                            @endif
                            <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary">
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
const types = @json(\App\Models\StockMovement::TYPES);

function updateFormForType(type) {
    const transferContainer = document.getElementById('destination_warehouse_container');
    const warehouseLabel = document.getElementById('warehouse_label');
    const destinationSelect = document.getElementById('destination_warehouse_id');

    if (type === 'transfer') {
        transferContainer.style.display = 'block';
        warehouseLabel.textContent = 'Entrepôt source';
        destinationSelect.required = true;
    } else {
        transferContainer.style.display = 'none';
        warehouseLabel.textContent = 'Entrepôt';
        destinationSelect.required = false;
    }
}

function calculateTotal() {
    const qty = parseFloat(document.getElementById('quantity').value) || 0;
    const cost = parseFloat(document.getElementById('unit_cost').value) || 0;
    const total = qty * cost;

    document.getElementById('total_cost').value = total.toFixed(2);
    document.getElementById('total_cost_display').value = total.toLocaleString('fr-BE', {minimumFractionDigits: 2});
}

document.addEventListener('DOMContentLoaded', function() {
    const checkedType = document.querySelector('input[name="type"]:checked');
    if (checkedType) {
        updateFormForType(checkedType.value);
    }
    calculateTotal();
});
</script>
@endpush
