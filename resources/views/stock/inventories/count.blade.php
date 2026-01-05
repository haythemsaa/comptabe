@extends('layouts.app')

@section('title', 'Comptage inventaire')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span>
                <a href="{{ route('stock.inventories.index') }}" class="text-muted">Inventaires</a>
                <span class="text-muted">/</span>
                Comptage {{ $inventory->reference }}
            </h4>
            <p class="text-muted mb-0">{{ $inventory->name }} - {{ $inventory->warehouse->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.inventories.show', $inventory) }}" class="btn btn-outline-secondary">
                <i class="ti ti-eye me-1"></i> Voir détails
            </a>
            @if($inventory->canBeValidated())
            <form action="{{ route('stock.inventories.validate', $inventory) }}" method="POST"
                  onsubmit="return confirm('Valider cet inventaire ? Les stocks seront ajustés selon les comptages.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-check me-1"></i> Valider l'inventaire
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Progress -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    @php $progress = $inventory->getProgressPercentage(); @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Progression du comptage</span>
                        <span class="text-muted">{{ $inventory->counted_products }}/{{ $inventory->total_products }} produits</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <h3 class="mb-0 {{ $progress == 100 ? 'text-success' : 'text-primary' }}">{{ $progress }}%</h3>
                    <small class="text-muted">
                        @if($inventory->discrepancies > 0)
                            <span class="text-warning">{{ $inventory->discrepancies }} écart(s) détecté(s)</span>
                        @else
                            Aucun écart
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="searchProduct"
                               placeholder="Rechercher par SKU ou nom..." autofocus>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">Tous les produits</option>
                        <option value="uncounted">Non comptés</option>
                        <option value="counted">Comptés</option>
                        <option value="discrepancy">Avec écart</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterLocation">
                        <option value="">Tous les emplacements</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}">{{ $location ?: 'Sans emplacement' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="ti ti-x me-1"></i> Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Products List -->
    <form action="{{ route('stock.inventories.save-count', $inventory) }}" method="POST" id="countForm">
        @csrf

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Emplacement</th>
                            <th class="text-end">Stock théorique</th>
                            <th class="text-center" style="width: 150px;">Quantité comptée</th>
                            <th class="text-center">Écart</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="inventory-row"
                            data-sku="{{ strtolower($item->product->sku) }}"
                            data-name="{{ strtolower($item->product->name) }}"
                            data-location="{{ strtolower($item->location ?? '') }}"
                            data-status="{{ $item->counted_at ? ($item->quantity_difference != 0 ? 'discrepancy' : 'counted') : 'uncounted' }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 {{ $item->counted_at ? ($item->quantity_difference != 0 ? 'bg-label-warning' : 'bg-label-success') : 'bg-label-secondary' }}">
                                        <span class="avatar-initial rounded">
                                            <i class="ti ti-{{ $item->counted_at ? ($item->quantity_difference != 0 ? 'alert-triangle' : 'check') : 'package' }} ti-xs"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $item->product->name }}</span>
                                        <br><small class="text-muted">{{ $item->product->sku }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->location ?: '-' }}</small>
                            </td>
                            <td class="text-end">
                                <span class="fw-medium">{{ number_format($item->expected_quantity, 2, ',', ' ') }}</span>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       class="form-control form-control-sm text-center count-input"
                                       name="counts[{{ $item->id }}]"
                                       value="{{ $item->counted_quantity }}"
                                       step="0.01"
                                       min="0"
                                       data-expected="{{ $item->expected_quantity }}"
                                       data-item-id="{{ $item->id }}"
                                       onchange="updateDifference(this)"
                                       placeholder="-">
                            </td>
                            <td class="text-center">
                                <span class="difference-display" data-item-id="{{ $item->id }}">
                                    @if($item->counted_at)
                                        @if($item->quantity_difference > 0)
                                            <span class="text-success">+{{ number_format($item->quantity_difference, 2, ',', ' ') }}</span>
                                        @elseif($item->quantity_difference < 0)
                                            <span class="text-danger">{{ number_format($item->quantity_difference, 2, ',', ' ') }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-badge" data-item-id="{{ $item->id }}">
                                    @if($item->counted_at)
                                        @if($item->quantity_difference != 0)
                                            <span class="badge bg-label-warning">Écart</span>
                                        @else
                                            <span class="badge bg-label-success">OK</span>
                                        @endif
                                    @else
                                        <span class="badge bg-label-secondary">Non compté</span>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ti ti-package-off ti-xl mb-2 d-block"></i>
                                    <p class="mb-0">Aucun produit à compter</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Floating Save Button -->
        <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050;">
            <div class="card shadow-lg">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <small class="text-muted d-block">Modifications non sauvegardées</small>
                            <span class="fw-medium" id="unsavedCount">0</span> produit(s)
                        </div>
                        <button type="submit" class="btn btn-primary" id="saveBtn" disabled>
                            <i class="ti ti-device-floppy me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let unsavedChanges = new Set();

function updateDifference(input) {
    const expected = parseFloat(input.dataset.expected) || 0;
    const counted = parseFloat(input.value);
    const itemId = input.dataset.itemId;
    const differenceDisplay = document.querySelector(`.difference-display[data-item-id="${itemId}"]`);
    const statusBadge = document.querySelector(`.status-badge[data-item-id="${itemId}"]`);

    if (input.value === '' || isNaN(counted)) {
        differenceDisplay.innerHTML = '<span class="text-muted">-</span>';
        statusBadge.innerHTML = '<span class="badge bg-label-secondary">Non compté</span>';
        unsavedChanges.delete(itemId);
    } else {
        const diff = counted - expected;
        unsavedChanges.add(itemId);

        if (diff > 0) {
            differenceDisplay.innerHTML = `<span class="text-success">+${diff.toLocaleString('fr-BE', {minimumFractionDigits: 2})}</span>`;
            statusBadge.innerHTML = '<span class="badge bg-label-warning">Écart</span>';
        } else if (diff < 0) {
            differenceDisplay.innerHTML = `<span class="text-danger">${diff.toLocaleString('fr-BE', {minimumFractionDigits: 2})}</span>`;
            statusBadge.innerHTML = '<span class="badge bg-label-warning">Écart</span>';
        } else {
            differenceDisplay.innerHTML = '<span class="text-muted">0</span>';
            statusBadge.innerHTML = '<span class="badge bg-label-success">OK</span>';
        }
    }

    updateUnsavedCount();
}

function updateUnsavedCount() {
    const count = unsavedChanges.size;
    document.getElementById('unsavedCount').textContent = count;
    document.getElementById('saveBtn').disabled = count === 0;
}

function filterProducts() {
    const search = document.getElementById('searchProduct').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const location = document.getElementById('filterLocation').value.toLowerCase();

    document.querySelectorAll('.inventory-row').forEach(row => {
        const sku = row.dataset.sku;
        const name = row.dataset.name;
        const rowLocation = row.dataset.location;
        const rowStatus = row.dataset.status;

        let show = true;

        // Search filter
        if (search && !sku.includes(search) && !name.includes(search)) {
            show = false;
        }

        // Status filter
        if (status && rowStatus !== status) {
            show = false;
        }

        // Location filter
        if (location && rowLocation !== location) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('searchProduct').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterLocation').value = '';
    filterProducts();
}

document.addEventListener('DOMContentLoaded', function() {
    // Attach filter listeners
    document.getElementById('searchProduct').addEventListener('input', filterProducts);
    document.getElementById('filterStatus').addEventListener('change', filterProducts);
    document.getElementById('filterLocation').addEventListener('change', filterProducts);

    // Quick entry: Enter moves to next input
    document.querySelectorAll('.count-input').forEach((input, index, inputs) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextInput = inputs[index + 1];
                if (nextInput) {
                    nextInput.focus();
                    nextInput.select();
                }
            }
        });
    });

    // Warn on page leave with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (unsavedChanges.size > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Auto-save indicator
    document.getElementById('countForm').addEventListener('submit', function() {
        unsavedChanges.clear();
        updateUnsavedCount();
    });
});
</script>
@endpush

@push('styles')
<style>
.count-input {
    width: 100px;
    margin: 0 auto;
}
.count-input:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}
</style>
@endpush
