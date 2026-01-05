@extends('layouts.app')

@section('title', 'Niveaux de stock')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span> Niveaux de stock
            </h4>
            <p class="text-muted mb-0">Vue d'ensemble des quantités en stock</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="ti ti-printer me-1"></i> Imprimer
            </button>
            <a href="{{ route('stock.movements.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Mouvement
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.levels') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Entrepôt</label>
                        <select class="form-select" name="warehouse">
                            <option value="">Tous les entrepôts</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
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
                            <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>En stock</option>
                            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Rupture</option>
                            <option value="overstock" {{ request('status') == 'overstock' ? 'selected' : '' }}>Sur-stock</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="SKU, nom...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('stock.levels') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['total_products'] }}</h4>
                            <small class="text-muted">Produits en stock</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-packages ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_value'], 2, ',', ' ') }} €</h4>
                            <small class="text-muted">Valeur totale</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-warning">{{ $stats['low_stock'] }}</h4>
                            <small class="text-muted">Stock faible</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-alert-triangle ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-danger">{{ $stats['out_of_stock'] }}</h4>
                            <small class="text-muted">Rupture de stock</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-package-off ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Levels Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Entrepôt</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Réservé</th>
                        <th class="text-end">Disponible</th>
                        <th class="text-end">Min / Max</th>
                        <th class="text-end">Valeur</th>
                        <th class="text-center">Statut</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr class="{{ $stock->quantity <= 0 ? 'table-danger' : ($stock->isLowStock() ? 'table-warning' : '') }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <a href="{{ route('products.show', $stock->product) }}" class="fw-medium text-body">
                                        {{ $stock->product->name }}
                                    </a>
                                    <br><small class="text-muted">{{ $stock->product->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('stock.warehouses.show', $stock->warehouse) }}" class="text-body">
                                {{ $stock->warehouse->name }}
                            </a>
                            <br><small class="text-muted">{{ $stock->warehouse->code }}</small>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($stock->quantity, 2, ',', ' ') }}</span>
                        </td>
                        <td class="text-end">
                            @if($stock->reserved_quantity > 0)
                                <span class="text-warning">{{ number_format($stock->reserved_quantity, 2, ',', ' ') }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-medium {{ $stock->available_quantity <= 0 ? 'text-danger' : '' }}">
                                {{ number_format($stock->available_quantity, 2, ',', ' ') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <small>
                                {{ $stock->min_quantity ? number_format($stock->min_quantity, 0) : '-' }}
                                /
                                {{ $stock->max_quantity ? number_format($stock->max_quantity, 0) : '-' }}
                            </small>
                        </td>
                        <td class="text-end">
                            {{ number_format($stock->stock_value, 2, ',', ' ') }} €
                        </td>
                        <td class="text-center">
                            @php $status = $stock->getStatus(); @endphp
                            <span class="badge bg-label-{{ $status['color'] }}">
                                {{ $status['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('stock.movements', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id]) }}">
                                        <i class="ti ti-history me-2"></i> Historique
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.movements.create', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id, 'type' => 'in']) }}">
                                        <i class="ti ti-package-import me-2 text-success"></i> Entrée
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.movements.create', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id, 'type' => 'out']) }}">
                                        <i class="ti ti-package-export me-2 text-danger"></i> Sortie
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.movements.create', ['product' => $stock->product_id, 'warehouse' => $stock->warehouse_id, 'type' => 'transfer']) }}">
                                        <i class="ti ti-transfer me-2 text-info"></i> Transfert
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('stock.levels.edit', $stock) }}">
                                        <i class="ti ti-settings me-2"></i> Paramètres stock
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-packages ti-xl mb-2 d-block"></i>
                                <p class="mb-0">Aucun produit en stock</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="card-footer">
            {{ $stocks->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, .dropdown, form, nav, .sidebar, .navbar {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
    }
}
</style>
@endpush
@endsection
