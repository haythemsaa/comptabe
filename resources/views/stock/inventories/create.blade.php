@extends('layouts.app')

@section('title', 'Nouvelle session d\'inventaire')

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
                Nouvelle session
            </h4>
            <p class="text-muted mb-0">Créer une nouvelle session d'inventaire physique</p>
        </div>
    </div>

    <form action="{{ route('stock.inventories.store') }}" method="POST">
        @csrf

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
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nom de l'inventaire <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Inventaire annuel 2026"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required>
                                    @foreach(\App\Models\InventorySession::TYPES as $key => $type)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
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
                                        id="warehouse_id" name="warehouse_id" required>
                                    <option value="">-- Sélectionner un entrepôt --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }} ({{ $wh->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="scheduled_date">Date prévue</label>
                                <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror"
                                       id="scheduled_date" name="scheduled_date"
                                       value="{{ old('scheduled_date', date('Y-m-d')) }}">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Filtres de produits
                            <small class="text-muted fw-normal">(optionnel)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-info-circle me-1"></i>
                            Laissez vide pour inclure tous les produits de l'entrepôt sélectionné.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="category_filter">Catégories</label>
                                <select class="form-select @error('category_filter') is-invalid @enderror"
                                        id="category_filter" name="category_filter[]" multiple size="4">
                                    @if(isset($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ in_array($category->id, old('category_filter', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Maintenez Ctrl pour sélectionner plusieurs</small>
                                @error('category_filter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="location_filter">Emplacements</label>
                                <input type="text" class="form-control @error('location_filter') is-invalid @enderror"
                                       id="location_filter" name="location_filter"
                                       value="{{ old('location_filter') }}"
                                       placeholder="Ex: Allée A, Allée B, Rack 1">
                                <small class="text-muted">Séparer les emplacements par des virgules</small>
                                @error('location_filter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Instructions</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="4"
                                  placeholder="Instructions pour le comptage, remarques particulières...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Types d'inventaire</h5>
                    </div>
                    <div class="card-body">
                        @foreach(\App\Models\InventorySession::TYPES as $key => $type)
                        <div class="d-flex align-items-start mb-3">
                            <div class="avatar avatar-sm me-2 bg-label-{{ $type['color'] ?? 'primary' }}">
                                <span class="avatar-initial rounded">
                                    <i class="ti ti-{{ $type['icon'] ?? 'clipboard' }} ti-xs"></i>
                                </span>
                            </div>
                            <div>
                                <span class="fw-medium">{{ $type['label'] }}</span>
                                <br><small class="text-muted">{{ $type['description'] ?? '' }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="create" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Créer l'inventaire
                            </button>
                            <button type="submit" name="action" value="create_and_start" class="btn btn-success">
                                <i class="ti ti-player-play me-1"></i> Créer et démarrer
                            </button>
                            <a href="{{ route('stock.inventories.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i> Annuler
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="mb-3">
                            <i class="ti ti-help me-1 text-primary"></i>
                            Comment procéder ?
                        </h6>
                        <ol class="mb-0 ps-3">
                            <li class="mb-2">Créez la session d'inventaire avec les filtres souhaités</li>
                            <li class="mb-2">Démarrez l'inventaire quand vous êtes prêt</li>
                            <li class="mb-2">Comptez physiquement chaque produit</li>
                            <li class="mb-2">Saisissez les quantités comptées</li>
                            <li class="mb-0">Validez pour appliquer les ajustements de stock</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
