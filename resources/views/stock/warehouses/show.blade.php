@extends('layouts.app')

@section('title', $warehouse->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('stock.dashboard') }}" class="text-muted">Stock</a>
                <span class="text-muted">/</span>
                <a href="{{ route('stock.warehouses.index') }}" class="text-muted">Entrepôts</a>
                <span class="text-muted">/</span>
                {{ $warehouse->name }}
            </h4>
            <p class="text-muted mb-0">
                <span class="badge bg-label-secondary">{{ $warehouse->code }}</span>
                @if($warehouse->is_default)
                    <span class="badge bg-label-primary ms-1">Entrepôt par défaut</span>
                @endif
                @if(!$warehouse->is_active)
                    <span class="badge bg-label-danger ms-1">Inactif</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.warehouses.edit', $warehouse) }}" class="btn btn-outline-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ti ti-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('stock.movements.create', ['warehouse' => $warehouse->id]) }}">
                            <i class="ti ti-transfer me-2"></i> Nouveau mouvement
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('stock.inventories.create', ['warehouse' => $warehouse->id]) }}">
                            <i class="ti ti-clipboard-check me-2"></i> Nouvel inventaire
                        </a>
                    </li>
                    @if(!$warehouse->is_default)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('stock.warehouses.set-default', $warehouse) }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti ti-star me-2"></i> Définir par défaut
                            </button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-packages ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['total_products'] }}</h4>
                            <small class="text-muted">Produits en stock</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-currency-euro ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ number_format($stats['total_value'], 2, ',', ' ') }} €</h4>
                            <small class="text-muted">Valeur du stock</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-alert-triangle ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['low_stock'] }}</h4>
                            <small class="text-muted">Stock faible</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-arrows-exchange ti-md"></i>
                            </span>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $stats['movements_count'] }}</h4>
                            <small class="text-muted">Mouvements (30j)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Stock Levels -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Niveaux de stock</h5>
                    <a href="{{ route('stock.levels', ['warehouse' => $warehouse->id]) }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-center">Réservé</th>
                                <th class="text-center">Disponible</th>
                                <th class="text-end">Valeur</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="fw-medium">{{ $stock->product->name }}</span>
                                            <br><small class="text-muted">{{ $stock->product->sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ number_format($stock->quantity, 2, ',', ' ') }}</td>
                                <td class="text-center">{{ number_format($stock->reserved_quantity, 2, ',', ' ') }}</td>
                                <td class="text-center fw-medium">{{ number_format($stock->available_quantity, 2, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($stock->stock_value, 2, ',', ' ') }} €</td>
                                <td class="text-center">
                                    @php $status = $stock->getStatus(); @endphp
                                    <span class="badge bg-label-{{ $status['color'] }}">{{ $status['label'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="ti ti-package-off ti-xl mb-2 d-block"></i>
                                        <p class="mb-0">Aucun stock dans cet entrepôt</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stocks->hasPages())
                <div class="card-footer">
                    {{ $stocks->links() }}
                </div>
                @endif
            </div>

            <!-- Recent Movements -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Derniers mouvements</h5>
                    <a href="{{ route('stock.movements', ['warehouse' => $warehouse->id]) }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Référence</th>
                                <th>Type</th>
                                <th>Produit</th>
                                <th class="text-end">Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMovements as $movement)
                            <tr>
                                <td>
                                    <small>{{ $movement->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('stock.movements.show', $movement) }}" class="fw-medium">
                                        {{ $movement->reference }}
                                    </a>
                                </td>
                                <td>
                                    @php $typeInfo = $movement->getTypeInfo(); @endphp
                                    <span class="badge bg-label-{{ $typeInfo['color'] }}">
                                        <i class="ti ti-{{ $typeInfo['icon'] }} me-1"></i>
                                        {{ $typeInfo['label'] }}
                                    </span>
                                </td>
                                <td>{{ $movement->product->name }}</td>
                                <td class="text-end">
                                    @if(in_array($movement->type, ['in', 'production']))
                                        <span class="text-success">+{{ number_format($movement->quantity, 2, ',', ' ') }}</span>
                                    @elseif(in_array($movement->type, ['out', 'consumption']))
                                        <span class="text-danger">-{{ number_format($movement->quantity, 2, ',', ' ') }}</span>
                                    @else
                                        {{ number_format($movement->quantity, 2, ',', ' ') }}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <p class="mb-0">Aucun mouvement récent</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    @if($warehouse->description)
                        <p class="text-muted mb-3">{{ $warehouse->description }}</p>
                    @endif

                    @if($warehouse->address || $warehouse->city)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Adresse</label>
                        <div>
                            @if($warehouse->address){{ $warehouse->address }}<br>@endif
                            @if($warehouse->postal_code || $warehouse->city)
                                {{ $warehouse->postal_code }} {{ $warehouse->city }}
                            @endif
                            @if($warehouse->country)
                                <br>{{ $warehouse->country }}
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($warehouse->phone)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Téléphone</label>
                        <div>
                            <a href="tel:{{ $warehouse->phone }}">{{ $warehouse->phone }}</a>
                        </div>
                    </div>
                    @endif

                    @if($warehouse->email)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Email</label>
                        <div>
                            <a href="mailto:{{ $warehouse->email }}">{{ $warehouse->email }}</a>
                        </div>
                    </div>
                    @endif

                    @if($warehouse->manager)
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Responsable</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ strtoupper(substr($warehouse->manager->name, 0, 1)) }}
                                </span>
                            </div>
                            <span>{{ $warehouse->manager->name }}</span>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stock négatif autorisé</span>
                        <span>
                            @if($warehouse->allow_negative_stock)
                                <span class="badge bg-label-success">Oui</span>
                            @else
                                <span class="badge bg-label-secondary">Non</span>
                            @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Créé le</span>
                        <span>{{ $warehouse->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('stock.movements.create', ['warehouse' => $warehouse->id, 'type' => 'in']) }}" class="btn btn-outline-success">
                            <i class="ti ti-package-import me-1"></i> Entrée de stock
                        </a>
                        <a href="{{ route('stock.movements.create', ['warehouse' => $warehouse->id, 'type' => 'out']) }}" class="btn btn-outline-danger">
                            <i class="ti ti-package-export me-1"></i> Sortie de stock
                        </a>
                        <a href="{{ route('stock.movements.create', ['warehouse' => $warehouse->id, 'type' => 'transfer']) }}" class="btn btn-outline-info">
                            <i class="ti ti-transfer me-1"></i> Transfert
                        </a>
                        <a href="{{ route('stock.inventories.create', ['warehouse' => $warehouse->id]) }}" class="btn btn-outline-primary">
                            <i class="ti ti-clipboard-check me-1"></i> Inventaire
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if($alerts->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Alertes</h5>
                    <span class="badge bg-danger">{{ $alerts->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($alerts->take(5) as $alert)
                    <div class="list-group-item">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded bg-label-{{ $alert->getTypeColor() }}">
                                    <i class="ti ti-{{ $alert->getTypeIcon() }} ti-sm"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $alert->product->name }}</div>
                                <small class="text-muted">{{ $alert->getTypeLabel() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($alerts->count() > 5)
                <div class="card-footer text-center">
                    <a href="{{ route('stock.alerts', ['warehouse' => $warehouse->id]) }}">
                        Voir toutes les alertes
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
