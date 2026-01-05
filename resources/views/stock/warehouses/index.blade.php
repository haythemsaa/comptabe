@extends('layouts.app')

@section('title', 'Entrepôts')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span> Entrepôts
            </h4>
            <p class="text-muted mb-0">Gérez vos lieux de stockage</p>
        </div>
        <a href="{{ route('stock.warehouses.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nouvel entrepôt
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-building-warehouse ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $warehouses->count() }}</h5>
                            <small class="text-muted">Entrepôts actifs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-packages ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $warehouses->sum('products_count') }}</h5>
                            <small class="text-muted">Produits stockés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($totalStockValue, 2, ',', ' ') }} €</h5>
                            <small class="text-muted">Valeur totale</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-alert-triangle ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $lowStockCount }}</h5>
                            <small class="text-muted">Alertes stock</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouses List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des entrepôts</h5>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" class="form-control" id="searchWarehouse" placeholder="Rechercher...">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="warehousesTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Responsable</th>
                        <th class="text-center">Produits</th>
                        <th class="text-end">Valeur Stock</th>
                        <th class="text-center">Statut</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $warehouse->code }}</span>
                            @if($warehouse->is_default)
                                <span class="badge bg-label-primary ms-1">Défaut</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('stock.warehouses.show', $warehouse) }}" class="text-body fw-medium">
                                {{ $warehouse->name }}
                            </a>
                        </td>
                        <td>
                            @if($warehouse->city)
                                <small class="text-muted">{{ $warehouse->city }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if($warehouse->manager)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-secondary">
                                            {{ strtoupper(substr($warehouse->manager->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span>{{ $warehouse->manager->name }}</span>
                                </div>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ $warehouse->products_count ?? 0 }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($warehouse->stock_value ?? 0, 2, ',', ' ') }} €</span>
                        </td>
                        <td class="text-center">
                            @if($warehouse->is_active)
                                <span class="badge bg-label-success">Actif</span>
                            @else
                                <span class="badge bg-label-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('stock.warehouses.show', $warehouse) }}">
                                        <i class="ti ti-eye me-2"></i> Voir détails
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.warehouses.edit', $warehouse) }}">
                                        <i class="ti ti-edit me-2"></i> Modifier
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.levels', ['warehouse' => $warehouse->id]) }}">
                                        <i class="ti ti-packages me-2"></i> Voir stock
                                    </a>
                                    @if(!$warehouse->is_default)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.warehouses.set-default', $warehouse) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-star me-2"></i> Définir par défaut
                                        </button>
                                    </form>
                                    @endif
                                    @if($warehouse->products_count == 0)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet entrepôt ?')">
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
                                <i class="ti ti-building-warehouse ti-xl mb-2 d-block"></i>
                                <p class="mb-2">Aucun entrepôt configuré</p>
                                <a href="{{ route('stock.warehouses.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Créer un entrepôt
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('searchWarehouse')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#warehousesTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endpush
