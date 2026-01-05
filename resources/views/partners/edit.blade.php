@extends('layouts.app')

@section('title', 'Modifier: ' . $partner->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('partners.index') }}" class="text-muted">Partenaires</a>
                <span class="text-muted">/</span> Modifier
            </h4>
        </div>
    </div>

    <form action="{{ route('partners.update', $partner) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- General Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name', $partner->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" name="type">
                                    <option value="client" {{ old('type', $partner->type) === 'client' ? 'selected' : '' }}>Client</option>
                                    <option value="supplier" {{ old('type', $partner->type) === 'supplier' ? 'selected' : '' }}>Fournisseur</option>
                                    <option value="both" {{ old('type', $partner->type) === 'both' ? 'selected' : '' }}>Les deux</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° TVA</label>
                                <input type="text" class="form-control @error('vat_number') is-invalid @enderror"
                                       name="vat_number" value="{{ old('vat_number', $partner->vat_number) }}"
                                       placeholder="BE0123456789">
                                @error('vat_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">N° Entreprise</label>
                                <input type="text" class="form-control @error('company_number') is-invalid @enderror"
                                       name="company_number" value="{{ old('company_number', $partner->company_number) }}">
                                @error('company_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email', $partner->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone', $partner->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Site web</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       name="website" value="{{ old('website', $partner->website) }}">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       name="address" value="{{ old('address', $partner->address) }}">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code postal</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                       name="postal_code" value="{{ old('postal_code', $partner->postal_code) }}">
                                @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       name="city" value="{{ old('city', $partner->city) }}">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pays</label>
                                <select class="form-select @error('country') is-invalid @enderror" name="country">
                                    <option value="BE" {{ old('country', $partner->country) === 'BE' ? 'selected' : '' }}>Belgique</option>
                                    <option value="FR" {{ old('country', $partner->country) === 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="LU" {{ old('country', $partner->country) === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                    <option value="NL" {{ old('country', $partner->country) === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                    <option value="DE" {{ old('country', $partner->country) === 'DE' ? 'selected' : '' }}>Allemagne</option>
                                </select>
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $partner->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Partenaire actif</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Conditions de paiement</label>
                            <select class="form-select" name="payment_terms">
                                <option value="0" {{ old('payment_terms', $partner->payment_terms) == 0 ? 'selected' : '' }}>Comptant</option>
                                <option value="15" {{ old('payment_terms', $partner->payment_terms) == 15 ? 'selected' : '' }}>15 jours</option>
                                <option value="30" {{ old('payment_terms', $partner->payment_terms) == 30 ? 'selected' : '' }}>30 jours</option>
                                <option value="45" {{ old('payment_terms', $partner->payment_terms) == 45 ? 'selected' : '' }}>45 jours</option>
                                <option value="60" {{ old('payment_terms', $partner->payment_terms) == 60 ? 'selected' : '' }}>60 jours</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('partners.show', $partner) }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
