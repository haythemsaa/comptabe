@extends('layouts.app')

@section('title', 'Sessions d\'inventaire')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span> Inventaires
            </h4>
            <p class="text-muted mb-0">Gestion des sessions d'inventaire physique</p>
        </div>
        <a href="{{ route('stock.inventories.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nouvel inventaire
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h4>
                            <small class="text-muted">En cours</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-loader ti-md"></i>
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
                            <h4 class="mb-0">{{ $stats['review'] ?? 0 }}</h4>
                            <small class="text-muted">En révision</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-eye-check ti-md"></i>
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
                            <h4 class="mb-0">{{ $stats['validated'] ?? 0 }}</h4>
                            <small class="text-muted">Validés ce mois</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-check ti-md"></i>
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
                            <h4 class="mb-0">{{ number_format($stats['total_adjustment'] ?? 0, 2, ',', ' ') }} €</h4>
                            <small class="text-muted">Ajustements (mois)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.inventories.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
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
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="">Tous</option>
                            @foreach(\App\Models\InventorySession::TYPES as $key => $type)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            <option value="">Tous</option>
                            @foreach(\App\Models\InventorySession::STATUSES as $key => $status)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $status['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Recherche</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Référence...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('stock.inventories.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventories List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Entrepôt</th>
                        <th>Date prévue</th>
                        <th class="text-center">Progression</th>
                        <th class="text-center">Écarts</th>
                        <th class="text-end">Valeur écarts</th>
                        <th class="text-center">Statut</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inventory)
                    <tr>
                        <td>
                            <a href="{{ route('stock.inventories.show', $inventory) }}" class="fw-medium">
                                {{ $inventory->reference }}
                            </a>
                            <br><small class="text-muted">{{ $inventory->name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $inventory->getTypeColor() }}">
                                {{ $inventory->getTypeLabel() }}
                            </span>
                        </td>
                        <td>
                            {{ $inventory->warehouse->name }}
                        </td>
                        <td>
                            {{ $inventory->scheduled_date ? $inventory->scheduled_date->format('d/m/Y') : '-' }}
                            @if($inventory->started_at)
                                <br><small class="text-muted">Démarré: {{ $inventory->started_at->format('d/m H:i') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $progress = $inventory->getProgressPercentage(); @endphp
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress" style="width: 80px; height: 6px;">
                                    <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}"
                                         style="width: {{ $progress }}%"></div>
                                </div>
                                <small>{{ $progress }}%</small>
                            </div>
                            <small class="text-muted">{{ $inventory->counted_products }}/{{ $inventory->total_products }}</small>
                        </td>
                        <td class="text-center">
                            @if($inventory->discrepancies > 0)
                                <span class="badge bg-label-warning">{{ $inventory->discrepancies }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($inventory->total_value_difference != 0)
                                <span class="{{ $inventory->total_value_difference > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $inventory->total_value_difference > 0 ? '+' : '' }}{{ number_format($inventory->total_value_difference, 2, ',', ' ') }} €
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-{{ $inventory->getStatusColor() }}">
                                {{ $inventory->getStatusLabel() }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('stock.inventories.show', $inventory) }}">
                                        <i class="ti ti-eye me-2"></i> Voir détails
                                    </a>
                                    @if($inventory->isDraft())
                                    <a class="dropdown-item" href="{{ route('stock.inventories.edit', $inventory) }}">
                                        <i class="ti ti-edit me-2"></i> Modifier
                                    </a>
                                    <form action="{{ route('stock.inventories.start', $inventory) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-player-play me-2"></i> Démarrer
                                        </button>
                                    </form>
                                    @endif
                                    @if($inventory->isInProgress())
                                    <a class="dropdown-item" href="{{ route('stock.inventories.count', $inventory) }}">
                                        <i class="ti ti-list-check me-2"></i> Continuer comptage
                                    </a>
                                    @endif
                                    @if($inventory->canBeValidated())
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.inventories.validate', $inventory) }}" method="POST" class="d-inline" onsubmit="return confirm('Valider cet inventaire ? Les stocks seront ajustés.')">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="ti ti-check me-2"></i> Valider
                                        </button>
                                    </form>
                                    @endif
                                    @if(!$inventory->isValidated())
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.inventories.cancel', $inventory) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler cet inventaire ?')">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti ti-x me-2"></i> Annuler
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
                                <i class="ti ti-clipboard-list ti-xl mb-2 d-block"></i>
                                <p class="mb-2">Aucune session d'inventaire</p>
                                <a href="{{ route('stock.inventories.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Créer un inventaire
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inventories->hasPages())
        <div class="card-footer">
            {{ $inventories->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
