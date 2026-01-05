@extends('layouts.app')

@section('title', 'Modifier l\'inventaire')

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
                Modifier {{ $inventory->reference }}
            </h4>
            <p class="text-muted mb-0">Modifier les paramètres de la session d'inventaire</p>
        </div>
    </div>

    @if(!$inventory->isDraft())
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-2"></i>
        Cette session d'inventaire a déjà été démarrée. Seules certaines informations peuvent être modifiées.
    </div>
    @endif

    <form action="{{ route('stock.inventories.update', $inventory) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Nom de l'inventaire <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name', $inventory->name) }}"
                                       placeholder="Ex: Inventaire annuel 2026"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reference">Référence</label>
                                <input type="text" class="form-control" id="reference"
                                       value="{{ $inventory->reference }}" readonly>
                                <small class="text-muted">Référence générée automatiquement</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="type">Type d'inventaire <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required
                                        {{ !$inventory->isDraft() ? 'disabled' : '' }}>
                                    @foreach(\App\Models\InventorySession::TYPES as $key => $type)
                                        <option value="{{ $key }}" {{ old('type', $inventory->type) == $key ? 'selected' : '' }}>
                                            {{ $type['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="warehouse_id">Entrepôt <span class="text-danger">*</span></label>
                                <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                        id="warehouse_id" name="warehouse_id" required
                                        {{ !$inventory->isDraft() ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('warehouse_id', $inventory->warehouse_id) == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }} ({{ $wh->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Planification</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="scheduled_date">Date prévue</label>
                                <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date" name="scheduled_date"
                                       value="{{ old('scheduled_date', $inventory->scheduled_date ? $inventory->scheduled_date->format('Y-m-d') : '') }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="assigned_to">Assigné à</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror"
                                        id="assigned_to" name="assigned_to">
                                    <option value="">-- Non assigné --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $inventory->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                @if($inventory->isDraft())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filtres de produits</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="category_filter">Catégories</label>
                                <select class="form-select @error('category_filter') is-invalid @enderror"
                                        id="category_filter" name="category_filter[]" multiple>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ in_array($category->id, old('category_filter', $inventory->category_filter ?? [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Laisser vide pour tous les produits</small>
                                @error('category_filter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="location_filter">Emplacements</label>
                                <input type="text" class="form-control @error('location_filter') is-invalid @enderror"
                                       id="location_filter" name="location_filter"
                                       value="{{ old('location_filter', $inventory->location_filter) }}"
                                       placeholder="Ex: Allée A, Allée B">
                                <small class="text-muted">Séparer par des virgules</small>
                                @error('location_filter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3"
                                  placeholder="Instructions ou notes pour l'inventaire...">{{ old('notes', $inventory->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status -->
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
                                    @if($inventory->started_at)
                                        Démarré le {{ $inventory->started_at->format('d/m/Y H:i') }}
                                    @else
                                        En attente de démarrage
                                    @endif
                                </small>
                            </div>
                        </div>
                        @if($inventory->isInProgress())
                        <div class="mb-3">
                            <label class="form-label">Progression</label>
                            @php $progress = $inventory->getProgressPercentage(); @endphp
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $progress == 100 ? 'bg-success' : 'bg-primary' }}"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                            <small class="text-muted">{{ $inventory->counted_products }}/{{ $inventory->total_products }} produits comptés ({{ $progress }}%)</small>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            @if($inventory->isDraft())
                            <form action="{{ route('stock.inventories.start', $inventory) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="ti ti-player-play me-1"></i> Démarrer l'inventaire
                                </button>
                            </form>
                            @endif
                            @if($inventory->isInProgress())
                            <a href="{{ route('stock.inventories.count', $inventory) }}" class="btn btn-outline-primary">
                                <i class="ti ti-list-check me-1"></i> Continuer le comptage
                            </a>
                            @endif
                            <a href="{{ route('stock.inventories.show', $inventory) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-eye me-1"></i> Voir détails
                            </a>
                            <a href="{{ route('stock.inventories.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
