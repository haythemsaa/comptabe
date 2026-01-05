@extends('layouts.app')

@section('title', 'Inventaire ' . $inventory->reference)

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
                {{ $inventory->reference }}
            </h4>
            <p class="text-muted mb-0">{{ $inventory->name }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($inventory->isDraft())
            <a href="{{ route('stock.inventories.edit', $inventory) }}" class="btn btn-outline-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            <form action="{{ route('stock.inventories.start', $inventory) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-player-play me-1"></i> Démarrer
                </button>
            </form>
            @elseif($inventory->isInProgress())
            <a href="{{ route('stock.inventories.count', $inventory) }}" class="btn btn-primary">
                <i class="ti ti-list-check me-1"></i> Continuer le comptage
            </a>
            @endif
            @if($inventory->canBeValidated())
            <form action="{{ route('stock.inventories.validate', $inventory) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Valider cet inventaire ? Les stocks seront ajustés.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-check me-1"></i> Valider
                </button>
            </form>
            @endif
            <a href="{{ route('stock.inventories.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar mx-auto mb-2 bg-label-primary">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-packages ti-md"></i>
                                </span>
                            </div>
                            <h4 class="mb-0">{{ $inventory->total_products }}</h4>
                            <small class="text-muted">Produits</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar mx-auto mb-2 bg-label-success">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-check ti-md"></i>
                                </span>
                            </div>
                            <h4 class="mb-0">{{ $inventory->counted_products }}</h4>
                            <small class="text-muted">Comptés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar mx-auto mb-2 bg-label-warning">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-alert-triangle ti-md"></i>
                                </span>
                            </div>
                            <h4 class="mb-0">{{ $inventory->discrepancies }}</h4>
                            <small class="text-muted">Écarts</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="avatar mx-auto mb-2 {{ $inventory->total_value_difference >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-currency-euro ti-md"></i>
                                </span>
                            </div>
                            <h4 class="mb-0 {{ $inventory->total_value_difference >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $inventory->total_value_difference >= 0 ? '+' : '' }}{{ number_format($inventory->total_value_difference, 2, ',', ' ') }} €
                            </h4>
                            <small class="text-muted">Écart valeur</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress -->
            @if(!$inventory->isValidated())
            <div class="card mb-4">
                <div class="card-body">
                    @php $progress = $inventory->getProgressPercentage(); @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-medium">Progression du comptage</span>
                        <span class="text-muted">{{ $progress }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Products List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détail des produits</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="all">Tous</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="uncounted">Non comptés</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="discrepancy">Écarts</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Emplacement</th>
                                <th class="text-end">Théorique</th>
                                <th class="text-end">Compté</th>
                                <th class="text-end">Écart</th>
                                <th class="text-end">Écart valeur</th>
                                <th class="text-center">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lines as $line)
                            <tr class="inventory-line {{ !$line->counted_at ? 'uncounted' : ($line->quantity_difference != 0 ? 'discrepancy' : 'ok') }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="fw-medium">{{ $line->product->name }}</span>
                                            <br><small class="text-muted">{{ $line->product->sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $line->location ?: '-' }}</small>
                                </td>
                                <td class="text-end">
                                    {{ number_format($line->expected_quantity, 2, ',', ' ') }}
                                </td>
                                <td class="text-end">
                                    @if($line->counted_at)
                                        <span class="fw-medium">{{ number_format($line->counted_quantity, 2, ',', ' ') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($line->counted_at)
                                        @if($line->quantity_difference > 0)
                                            <span class="text-success">+{{ number_format($line->quantity_difference, 2, ',', ' ') }}</span>
                                        @elseif($line->quantity_difference < 0)
                                            <span class="text-danger">{{ number_format($line->quantity_difference, 2, ',', ' ') }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($line->counted_at && $line->value_difference != 0)
                                        <span class="{{ $line->value_difference > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $line->value_difference > 0 ? '+' : '' }}{{ number_format($line->value_difference, 2, ',', ' ') }} €
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!$line->counted_at)
                                        <span class="badge bg-label-secondary">Non compté</span>
                                    @elseif($line->quantity_difference != 0)
                                        <span class="badge bg-label-warning">Écart</span>
                                    @else
                                        <span class="badge bg-label-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="ti ti-package-off ti-xl mb-2 d-block"></i>
                                        <p class="mb-0">Aucun produit dans cet inventaire</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($lines->hasPages())
                <div class="card-footer">
                    {{ $lines->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Statut</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-{{ $inventory->getStatusColor() }}">
                                <i class="ti ti-clipboard-list ti-lg"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $inventory->getStatusLabel() }}</h5>
                            <small class="text-muted">
                                @if($inventory->validated_at)
                                    Validé le {{ $inventory->validated_at->format('d/m/Y H:i') }}
                                @elseif($inventory->started_at)
                                    Démarré le {{ $inventory->started_at->format('d/m/Y H:i') }}
                                @else
                                    Créé le {{ $inventory->created_at->format('d/m/Y') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Référence</span>
                        <span class="fw-medium">{{ $inventory->reference }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Type</span>
                        <span class="badge bg-label-{{ $inventory->getTypeColor() }}">{{ $inventory->getTypeLabel() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Entrepôt</span>
                        <a href="{{ route('stock.warehouses.show', $inventory->warehouse) }}">
                            {{ $inventory->warehouse->name }}
                        </a>
                    </div>
                    @if($inventory->scheduled_date)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Date prévue</span>
                        <span class="fw-medium">{{ $inventory->scheduled_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($inventory->assignedTo)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Assigné à</span>
                        <span class="fw-medium">{{ $inventory->assignedTo->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            @if($inventory->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $inventory->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if(!$inventory->isValidated())
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($inventory->isDraft())
                        <form action="{{ route('stock.inventories.destroy', $inventory) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette session d\'inventaire ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-trash me-1"></i> Supprimer
                            </button>
                        </form>
                        @else
                        <form action="{{ route('stock.inventories.cancel', $inventory) }}" method="POST"
                              onsubmit="return confirm('Annuler cette session d\'inventaire ?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-x me-1"></i> Annuler l'inventaire
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;

        // Update active button
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Filter rows
        document.querySelectorAll('.inventory-line').forEach(row => {
            if (filter === 'all') {
                row.style.display = '';
            } else if (filter === 'uncounted') {
                row.style.display = row.classList.contains('uncounted') ? '' : 'none';
            } else if (filter === 'discrepancy') {
                row.style.display = row.classList.contains('discrepancy') ? '' : 'none';
            }
        });
    });
});
</script>
@endpush
