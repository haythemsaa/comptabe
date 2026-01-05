@extends('layouts.app')

@section('title', 'Mouvements de stock')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span> Mouvements
            </h4>
            <p class="text-muted mb-0">Historique des entrées, sorties et transferts</p>
        </div>
        <a href="{{ route('stock.movements.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Nouveau mouvement
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('stock.movements.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Date début</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date fin</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type">
                            <option value="">Tous</option>
                            @foreach(\App\Models\StockMovement::TYPES as $key => $type)
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
                            <option value="">Tous</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="validated" {{ request('status') == 'validated' ? 'selected' : '' }}>Validé</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary">
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
            <div class="card bg-label-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-success">{{ $stats['entries'] ?? 0 }}</h5>
                            <small>Entrées</small>
                        </div>
                        <i class="ti ti-package-import ti-xl text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-label-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-danger">{{ $stats['exits'] ?? 0 }}</h5>
                            <small>Sorties</small>
                        </div>
                        <i class="ti ti-package-export ti-xl text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-label-info">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-info">{{ $stats['transfers'] ?? 0 }}</h5>
                            <small>Transferts</small>
                        </div>
                        <i class="ti ti-transfer ti-xl text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-label-warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-warning">{{ $stats['adjustments'] ?? 0 }}</h5>
                            <small>Ajustements</small>
                        </div>
                        <i class="ti ti-adjustments ti-xl text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movements List -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Type</th>
                        <th>Produit</th>
                        <th>Entrepôt</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Valeur</th>
                        <th class="text-center">Statut</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr>
                        <td>
                            <span>{{ $movement->created_at->format('d/m/Y') }}</span>
                            <br><small class="text-muted">{{ $movement->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('stock.movements.show', $movement) }}" class="fw-medium">
                                {{ $movement->reference }}
                            </a>
                            @if($movement->source_type)
                                <br><small class="text-muted">{{ class_basename($movement->source_type) }}</small>
                            @endif
                        </td>
                        <td>
                            @php $typeInfo = $movement->getTypeInfo(); @endphp
                            <span class="badge bg-label-{{ $typeInfo['color'] }}">
                                <i class="ti ti-{{ $typeInfo['icon'] }} me-1"></i>
                                {{ $typeInfo['label'] }}
                            </span>
                            @if($movement->reason)
                                <br><small class="text-muted">{{ $movement->getReasonLabel() }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div>
                                    <span class="fw-medium">{{ $movement->product->name }}</span>
                                    <br><small class="text-muted">{{ $movement->product->sku }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span>{{ $movement->warehouse->name }}</span>
                            @if($movement->type === 'transfer' && $movement->destinationWarehouse)
                                <br><small class="text-muted">
                                    <i class="ti ti-arrow-right"></i> {{ $movement->destinationWarehouse->name }}
                                </small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(in_array($movement->type, ['in', 'production']))
                                <span class="text-success fw-medium">+{{ number_format($movement->quantity, 2, ',', ' ') }}</span>
                            @elseif(in_array($movement->type, ['out', 'consumption']))
                                <span class="text-danger fw-medium">-{{ number_format($movement->quantity, 2, ',', ' ') }}</span>
                            @else
                                <span class="fw-medium">{{ number_format($movement->quantity, 2, ',', ' ') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            {{ number_format($movement->total_cost ?? 0, 2, ',', ' ') }} €
                        </td>
                        <td class="text-center">
                            @php $statusInfo = $movement->getStatusInfo(); @endphp
                            <span class="badge bg-label-{{ $statusInfo['color'] }}">
                                {{ $statusInfo['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical ti-md"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('stock.movements.show', $movement) }}">
                                        <i class="ti ti-eye me-2"></i> Voir détails
                                    </a>
                                    @if($movement->status === 'draft')
                                    <a class="dropdown-item" href="{{ route('stock.movements.edit', $movement) }}">
                                        <i class="ti ti-edit me-2"></i> Modifier
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('stock.movements.validate', $movement) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-check me-2"></i> Valider
                                        </button>
                                    </form>
                                    <form action="{{ route('stock.movements.destroy', $movement) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce mouvement ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti ti-trash me-2"></i> Supprimer
                                        </button>
                                    </form>
                                    @endif
                                    @if($movement->status === 'validated')
                                    <form action="{{ route('stock.movements.cancel', $movement) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce mouvement ?')">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-warning">
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
                                <i class="ti ti-arrows-exchange-2 ti-xl mb-2 d-block"></i>
                                <p class="mb-2">Aucun mouvement de stock</p>
                                <a href="{{ route('stock.movements.create') }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Créer un mouvement
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer">
            {{ $movements->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
