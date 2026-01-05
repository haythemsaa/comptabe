@extends('layouts.app')

@section('title', isset($warehouse) ? 'Modifier l\'entrepôt' : 'Nouvel entrepôt')

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
                {{ isset($warehouse) ? 'Modifier' : 'Nouveau' }}
            </h4>
        </div>
    </div>

    <form action="{{ isset($warehouse) ? route('stock.warehouses.update', $warehouse) : route('stock.warehouses.store') }}" method="POST">
        @csrf
        @if(isset($warehouse))
            @method('PUT')
        @endif

        <div class="row">
            <!-- Main Info -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code"
                                       value="{{ old('code', $warehouse->code ?? '') }}"
                                       placeholder="WH001" required maxlength="20">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name', $warehouse->name ?? '') }}"
                                       placeholder="Entrepôt principal" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="2"
                                          placeholder="Description de l'entrepôt...">{{ old('description', $warehouse->description ?? '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Adresse</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="address">Adresse</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address"
                                       value="{{ old('address', $warehouse->address ?? '') }}"
                                       placeholder="Rue et numéro">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="postal_code">Code postal</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                       id="postal_code" name="postal_code"
                                       value="{{ old('postal_code', $warehouse->postal_code ?? '') }}"
                                       placeholder="1000">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="city">Ville</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city"
                                       value="{{ old('city', $warehouse->city ?? '') }}"
                                       placeholder="Bruxelles">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="country">Pays</label>
                                <select class="form-select @error('country') is-invalid @enderror"
                                        id="country" name="country">
                                    <option value="BE" {{ old('country', $warehouse->country ?? 'BE') == 'BE' ? 'selected' : '' }}>Belgique</option>
                                    <option value="FR" {{ old('country', $warehouse->country ?? '') == 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="LU" {{ old('country', $warehouse->country ?? '') == 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                    <option value="NL" {{ old('country', $warehouse->country ?? '') == 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                    <option value="DE" {{ old('country', $warehouse->country ?? '') == 'DE' ? 'selected' : '' }}>Allemagne</option>
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Contact</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone"
                                       value="{{ old('phone', $warehouse->phone ?? '') }}"
                                       placeholder="+32 2 xxx xx xx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $warehouse->email ?? '') }}"
                                       placeholder="entrepot@exemple.be">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="manager_id">Responsable</label>
                            <select class="form-select @error('manager_id') is-invalid @enderror"
                                    id="manager_id" name="manager_id">
                                <option value="">-- Aucun --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('manager_id', $warehouse->manager_id ?? '') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-3">

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $warehouse->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Entrepôt actif</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                                   {{ old('is_default', $warehouse->is_default ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">Entrepôt par défaut</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="allow_negative_stock" name="allow_negative_stock" value="1"
                                   {{ old('allow_negative_stock', $warehouse->allow_negative_stock ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_negative_stock">
                                Autoriser stock négatif
                                <i class="ti ti-info-circle text-muted" data-bs-toggle="tooltip"
                                   title="Permet de sortir du stock même si la quantité disponible est insuffisante"></i>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ isset($warehouse) ? 'Mettre à jour' : 'Créer l\'entrepôt' }}
                            </button>
                            <a href="{{ route('stock.warehouses.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i> Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
