@extends('layouts.app')

@section('title', 'Alertes stock')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span> Alertes
            </h4>
            <p class="text-muted mb-0">Alertes de stock et notifications</p>
        </div>
        <div class="d-flex gap-2">
            @if($unreadCount > 0)
            <form action="{{ route('stock.alerts.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="ti ti-checks me-1"></i> Tout marquer comme lu
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ti ti-x-circle ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['out_of_stock'] ?? 0 }}</h4>
                    <small class="text-muted">Ruptures</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ti ti-alert-triangle ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['low_stock'] ?? 0 }}</h4>
                    <small class="text-muted">Stock faible</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="ti ti-package ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['overstock'] ?? 0 }}</h4>
                    <small class="text-muted">Sur-stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti ti-shopping-cart ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['reorder_point'] ?? 0 }}</h4>
                    <small class="text-muted">À commander</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ti ti-clock ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['expiring_soon'] ?? 0 }}</h4>
                    <small class="text-muted">Exp. proche</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ti ti-alert-octagon ti-md"></i>
                        </span>
                    </div>
                    <h4 class="mb-0">{{ $summary['expired'] ?? 0 }}</h4>
                    <small class="text-muted">Périmés</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.alerts.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="">Tous</option>
                            @foreach(\App\Models\StockAlert::TYPES as $key => $type)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Entrepôt</label>
                        <select class="form-select" name="warehouse">
                            <option value="">Tous</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            <option value="">Toutes</option>
                            <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Non lues</option>
                            <option value="unresolved" {{ request('status') == 'unresolved' ? 'selected' : '' }}>Non résolues</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Résolues</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Priorité</label>
                        <select class="form-select" name="priority">
                            <option value="">Toutes</option>
                            <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critiques</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Produit...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('stock.alerts.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="40"></th>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Entrepôt</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Seuil</th>
                        <th>Date</th>
                        <th class="text-center">Statut</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="{{ !$alert->is_read ? 'fw-medium' : '' }} {{ $alert->isCritical() && !$alert->is_resolved ? 'table-danger' : '' }}">
                        <td class="text-center">
                            @if(!$alert->is_read)
                                <span class="badge badge-dot bg-primary" title="Non lu"></span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-{{ $alert->getTypeColor() }}">
                                        <i class="ti ti-{{ $alert->getTypeIcon() }} ti-sm"></i>
                                    </span>
                                </div>
                                <span class="badge bg-label-{{ $alert->getTypeColor() }}">
                                    {{ $alert->getTypeLabel() }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('products.show', $alert->product) }}" class="text-body">
                                {{ $alert->product->name }}
                            </a>
                            <br><small class="text-muted">{{ $alert->product->sku }}</small>
                        </td>
                        <td>
                            {{ $alert->warehouse->name }}
                        </td>
                        <td class="text-end">
                            <span class="{{ $alert->current_quantity <= 0 ? 'text-danger' : '' }}">
                                {{ number_format($alert->current_quantity, 2, ',', ' ') }}
                            </span>
                        </td>
                        <td class="text-end text-muted">
                            {{ number_format($alert->threshold_quantity, 2, ',', ' ') }}
                        </td>
                        <td>
                            <small>{{ $alert->created_at->format('d/m/Y H:i') }}</small>
                            @if($alert->expiry_date)
                                <br><small class="text-muted">Exp: {{ $alert->expiry_date->format('d/m/Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($alert->is_resolved)
                                <span class="badge bg-label-success">Résolu</span>
                            @else
                                <span class="badge bg-label-secondary">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @if(!$alert->is_read)
                                    <form action="{{ route('stock.alerts.mark-read', $alert) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-eye me-2"></i> Marquer comme lu
                                        </button>
                                    </form>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('stock.movements.create', ['product' => $alert->product_id, 'warehouse' => $alert->warehouse_id, 'type' => 'in']) }}">
                                        <i class="ti ti-package-import me-2 text-success"></i> Entrée stock
                                    </a>
                                    <a class="dropdown-item" href="{{ route('stock.levels', ['product' => $alert->product_id]) }}">
                                        <i class="ti ti-packages me-2"></i> Voir niveaux
                                    </a>
                                    @if(!$alert->is_resolved)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.alerts.resolve', $alert) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="ti ti-check me-2"></i> Marquer résolu
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-bell-check ti-xl mb-2 d-block text-success"></i>
                                <p class="mb-0">Aucune alerte de stock</p>
                                <small>Tout est en ordre !</small>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alerts->hasPages())
        <div class="card-footer">
            {{ $alerts->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
