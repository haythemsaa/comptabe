@extends('layouts.app')

@section('title', 'Paramètres de stock')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span>
                <a href="{{ route('stock.levels') }}" class="text-muted">Niveaux</a>
                <span class="text-muted">/</span>
                Paramètres
            </h4>
            <p class="text-muted mb-0">Configurer les seuils d'alerte pour ce produit</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('stock.levels.update', $stock) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Product Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Produit</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-package ti-lg"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $stock->product->name }}</h5>
                                <div class="text-muted">
                                    <span class="me-3"><i class="ti ti-barcode me-1"></i>{{ $stock->product->sku }}</span>
                                    <span><i class="ti ti-building-warehouse me-1"></i>{{ $stock->warehouse->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Stock -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Stock actuel</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Quantité en stock</small>
                                    <h4 class="mb-0">{{ number_format($stock->quantity, 2, ',', ' ') }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Quantité réservée</small>
                                    <h4 class="mb-0 text-warning">{{ number_format($stock->reserved_quantity ?? 0, 2, ',', ' ') }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted d-block mb-1">Disponible</small>
                                    <h4 class="mb-0 text-success">{{ number_format($stock->available_quantity, 2, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Parameters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Seuils et alertes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="min_quantity">
                                    Quantité minimale
                                    <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Alerte stock faible si le stock descend sous ce seuil"></i>
                                </label>
                                <input type="number" class="form-control @error('min_quantity') is-invalid @enderror"
                                       id="min_quantity" name="min_quantity" step="0.01" min="0"
                                       value="{{ old('min_quantity', $stock->min_quantity) }}"
                                       placeholder="Ex: 10">
                                @error('min_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Seuil d'alerte stock faible</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="max_quantity">
                                    Quantité maximale
                                    <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Alerte sur-stock si le stock dépasse ce seuil"></i>
                                </label>
                                <input type="number" class="form-control @error('max_quantity') is-invalid @enderror"
                                       id="max_quantity" name="max_quantity" step="0.01" min="0"
                                       value="{{ old('max_quantity', $stock->max_quantity) }}"
                                       placeholder="Ex: 100">
                                @error('max_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Seuil d'alerte sur-stock</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="reorder_quantity">
                                    Quantité de réapprovisionnement
                                    <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip" title="Quantité suggérée lors d'une commande"></i>
                                </label>
                                <input type="number" class="form-control @error('reorder_quantity') is-invalid @enderror"
                                       id="reorder_quantity" name="reorder_quantity" step="0.01" min="0"
                                       value="{{ old('reorder_quantity', $stock->reorder_quantity) }}"
                                       placeholder="Ex: 50">
                                @error('reorder_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Quantité à commander</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Emplacement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="location">Emplacement dans l'entrepôt</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location"
                                       value="{{ old('location', $stock->location) }}"
                                       placeholder="Ex: Allée A, Étagère 3, Niveau 2">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Emplacement physique du produit</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('stock.levels') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statut actuel</h5>
                </div>
                <div class="card-body">
                    @php $status = $stock->getStatus(); @endphp
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-{{ $status['color'] }}">
                                <i class="ti ti-package ti-lg"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $status['label'] }}</h5>
                            <small class="text-muted">{{ $status['description'] ?? 'Stock normal' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('stock.movements.create', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id, 'type' => 'in']) }}" class="btn btn-outline-success">
                            <i class="ti ti-package-import me-1"></i> Nouvelle entrée
                        </a>
                        <a href="{{ route('stock.movements.create', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id, 'type' => 'out']) }}" class="btn btn-outline-danger">
                            <i class="ti ti-package-export me-1"></i> Nouvelle sortie
                        </a>
                        <a href="{{ route('stock.movements', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id]) }}" class="btn btn-outline-secondary">
                            <i class="ti ti-history me-1"></i> Historique mouvements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
