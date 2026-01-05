@extends('layouts.app')

@section('title', 'Mouvement ' . $movement->reference)

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
                {{ $movement->reference }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            @if($movement->status === 'draft')
            <a href="{{ route('stock.movements.edit', $movement) }}" class="btn btn-outline-primary">
                <i class="ti ti-edit me-1"></i> Modifier
            </a>
            <form action="{{ route('stock.movements.validate', $movement) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Valider ce mouvement ? Cette action est irréversible.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="ti ti-check me-1"></i> Valider
                </button>
            </form>
            @endif
            <a href="{{ route('stock.movements.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <!-- Movement Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails du mouvement</h5>
                    @php
                        $types = \App\Models\StockMovement::TYPES;
                        $type = $types[$movement->type] ?? ['label' => $movement->type, 'color' => 'secondary', 'icon' => 'package'];
                    @endphp
                    <span class="badge bg-label-{{ $type['color'] }}">
                        <i class="ti ti-{{ $type['icon'] }} me-1"></i>
                        {{ $type['label'] }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Produit</small>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-label-primary">
                                        <span class="avatar-initial rounded">
                                            <i class="ti ti-package ti-xs"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="{{ route('products.show', $movement->product) }}" class="fw-medium">
                                            {{ $movement->product->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $movement->product->sku }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Quantité</small>
                                <h3 class="mb-0 {{ $movement->type === 'out' ? 'text-danger' : ($movement->type === 'in' ? 'text-success' : 'text-primary') }}">
                                    {{ $movement->type === 'out' ? '-' : ($movement->type === 'in' ? '+' : '') }}{{ number_format($movement->quantity, 2, ',', ' ') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouses -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Entrepôt(s)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block mb-2">
                                    {{ $movement->type === 'transfer' ? 'Entrepôt source' : 'Entrepôt' }}
                                </small>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-label-secondary">
                                        <span class="avatar-initial rounded">
                                            <i class="ti ti-building-warehouse ti-xs"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="{{ route('stock.warehouses.show', $movement->warehouse) }}" class="fw-medium">
                                            {{ $movement->warehouse->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $movement->warehouse->code }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($movement->type === 'transfer' && $movement->destinationWarehouse)
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <small class="text-muted d-block mb-2">Entrepôt destination</small>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 bg-label-info">
                                        <span class="avatar-initial rounded">
                                            <i class="ti ti-building-warehouse ti-xs"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="{{ route('stock.warehouses.show', $movement->destinationWarehouse) }}" class="fw-medium">
                                            {{ $movement->destinationWarehouse->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $movement->destinationWarehouse->code }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Financial Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations financières</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <small class="text-muted d-block mb-1">Coût unitaire</small>
                                <h4 class="mb-0">{{ number_format($movement->unit_cost ?? 0, 2, ',', ' ') }} €</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <small class="text-muted d-block mb-1">Quantité</small>
                                <h4 class="mb-0">{{ number_format($movement->quantity, 2, ',', ' ') }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Valeur totale</small>
                                <h4 class="mb-0 text-primary">{{ number_format($movement->total_cost ?? 0, 2, ',', ' ') }} €</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            @if($movement->reason || $movement->batch_number || $movement->expiry_date || $movement->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Détails supplémentaires</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($movement->reason)
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Motif</small>
                            <span class="fw-medium">{{ \App\Models\StockMovement::REASONS[$movement->reason] ?? $movement->reason }}</span>
                        </div>
                        @endif
                        @if($movement->batch_number)
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">N° de lot</small>
                            <span class="fw-medium">{{ $movement->batch_number }}</span>
                        </div>
                        @endif
                        @if($movement->expiry_date)
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Date d'expiration</small>
                            <span class="fw-medium {{ $movement->expiry_date->isPast() ? 'text-danger' : '' }}">
                                {{ $movement->expiry_date->format('d/m/Y') }}
                            </span>
                        </div>
                        @endif
                        @if($movement->notes)
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Notes</small>
                            <p class="mb-0">{{ $movement->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
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
                        @php
                            $statusColors = ['draft' => 'secondary', 'validated' => 'success', 'cancelled' => 'danger'];
                            $statusLabels = ['draft' => 'Brouillon', 'validated' => 'Validé', 'cancelled' => 'Annulé'];
                            $statusIcons = ['draft' => 'file', 'validated' => 'check', 'cancelled' => 'x'];
                        @endphp
                        <div class="avatar avatar-lg">
                            <span class="avatar-initial rounded bg-label-{{ $statusColors[$movement->status] ?? 'secondary' }}">
                                <i class="ti ti-{{ $statusIcons[$movement->status] ?? 'file' }} ti-lg"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $statusLabels[$movement->status] ?? ucfirst($movement->status) }}</h5>
                            @if($movement->validated_at)
                            <small class="text-muted">Validé le {{ $movement->validated_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    </div>

                    @if($movement->status === 'draft')
                    <div class="alert alert-warning mb-0">
                        <i class="ti ti-alert-triangle me-1"></i>
                        Ce mouvement n'a pas encore été validé. Les stocks n'ont pas été mis à jour.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Historique</h5>
                </div>
                <div class="card-body">
                    <ul class="timeline mb-0">
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-primary">
                                <i class="ti ti-plus"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <small class="text-muted">Créé</small>
                                </div>
                                <p class="mb-0">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                                @if($movement->createdBy)
                                <small class="text-muted">par {{ $movement->createdBy->name }}</small>
                                @endif
                            </div>
                        </li>
                        @if($movement->validated_at)
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-success">
                                <i class="ti ti-check"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <small class="text-muted">Validé</small>
                                </div>
                                <p class="mb-0">{{ $movement->validated_at->format('d/m/Y H:i') }}</p>
                                @if($movement->validatedBy)
                                <small class="text-muted">par {{ $movement->validatedBy->name }}</small>
                                @endif
                            </div>
                        </li>
                        @endif
                        @if($movement->status === 'cancelled')
                        <li class="timeline-item">
                            <span class="timeline-indicator timeline-indicator-danger">
                                <i class="ti ti-x"></i>
                            </span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <small class="text-muted">Annulé</small>
                                </div>
                                <p class="mb-0">{{ $movement->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            @if($movement->status === 'draft')
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('stock.movements.cancel', $movement) }}" method="POST"
                              onsubmit="return confirm('Annuler ce mouvement ?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-x me-1"></i> Annuler le mouvement
                            </button>
                        </form>
                        <form action="{{ route('stock.movements.destroy', $movement) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce mouvement ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="ti ti-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    list-style: none;
}
.timeline-item {
    position: relative;
    padding-bottom: 1rem;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -1.5rem;
    top: 1.5rem;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item:last-child::before {
    display: none;
}
.timeline-indicator {
    position: absolute;
    left: -2rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.625rem;
    color: #fff;
}
.timeline-indicator-primary { background: var(--bs-primary); }
.timeline-indicator-success { background: var(--bs-success); }
.timeline-indicator-danger { background: var(--bs-danger); }
</style>
@endpush
