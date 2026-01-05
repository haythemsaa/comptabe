@extends('layouts.app')

@section('title', 'Modifier la déclaration TVA')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('vat.index') }}" class="text-muted">Déclarations TVA</a>
                <span class="text-muted">/</span> Modifier
            </h4>
        </div>
    </div>

    @if($declaration->status !== 'draft')
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-2"></i>
        Cette déclaration a déjà été soumise et ne peut plus être modifiée.
    </div>
    @endif

    <form action="{{ route('vat.update', $declaration) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Period -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Période</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Type de période</label>
                                <select class="form-select @error('period_type') is-invalid @enderror" name="period_type"
                                        {{ $declaration->status !== 'draft' ? 'disabled' : '' }}>
                                    <option value="monthly" {{ old('period_type', $declaration->period_type) === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                                    <option value="quarterly" {{ old('period_type', $declaration->period_type) === 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                                </select>
                                @error('period_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Année</label>
                                <select class="form-select @error('year') is-invalid @enderror" name="year"
                                        {{ $declaration->status !== 'draft' ? 'disabled' : '' }}>
                                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                                        <option value="{{ $y }}" {{ old('year', $declaration->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Période</label>
                                <select class="form-select @error('period') is-invalid @enderror" name="period"
                                        {{ $declaration->status !== 'draft' ? 'disabled' : '' }}>
                                    @for($p = 1; $p <= 12; $p++)
                                        <option value="{{ $p }}" {{ old('period', $declaration->period) == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endfor
                                </select>
                                @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amounts -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Montants</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">TVA sur ventes (collectée)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('vat_collected') is-invalid @enderror"
                                           name="vat_collected" step="0.01"
                                           value="{{ old('vat_collected', $declaration->vat_collected) }}"
                                           {{ $declaration->status !== 'draft' ? 'readonly' : '' }}>
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('vat_collected')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TVA sur achats (déductible)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('vat_deductible') is-invalid @enderror"
                                           name="vat_deductible" step="0.01"
                                           value="{{ old('vat_deductible', $declaration->vat_deductible) }}"
                                           {{ $declaration->status !== 'draft' ? 'readonly' : '' }}>
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('vat_deductible')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="bg-light rounded p-3 text-end">
                                    <small class="text-muted d-block">TVA à payer / à récupérer</small>
                                    @php $balance = ($declaration->vat_collected ?? 0) - ($declaration->vat_deductible ?? 0); @endphp
                                    <h3 class="mb-0 {{ $balance >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $balance >= 0 ? '' : '-' }}{{ number_format(abs($balance), 2, ',', ' ') }} €
                                    </h3>
                                    <small class="{{ $balance >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $balance >= 0 ? 'À payer' : 'À récupérer' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  name="notes" rows="3"
                                  {{ $declaration->status !== 'draft' ? 'readonly' : '' }}>{{ old('notes', $declaration->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Statut</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'accepted' => 'success', 'paid' => 'primary'];
                            $statusLabels = ['draft' => 'Brouillon', 'submitted' => 'Soumis', 'accepted' => 'Accepté', 'paid' => 'Payé'];
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-label-{{ $statusColors[$declaration->status] ?? 'secondary' }} fs-6">
                                {{ $statusLabels[$declaration->status] ?? ucfirst($declaration->status) }}
                            </span>
                        </div>
                        @if($declaration->submitted_at)
                        <small class="text-muted d-block">
                            Soumis le {{ $declaration->submitted_at->format('d/m/Y') }}
                        </small>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($declaration->status === 'draft')
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Enregistrer
                            </button>
                            <button type="submit" name="action" value="recalculate" class="btn btn-outline-secondary">
                                <i class="ti ti-calculator me-1"></i> Recalculer
                            </button>
                            @endif
                            <a href="{{ route('vat.show', $declaration) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
