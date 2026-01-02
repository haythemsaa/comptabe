<x-app-layout>
    <x-slot name="title">Export Comptable</x-slot>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-download"></i> Export Comptable
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <p class="text-muted mb-4">
                                Exportez vos factures et écritures comptables vers votre logiciel de comptabilité préféré.
                            </p>

                            <form action="{{ route('accounting.export.generate') }}" method="POST" id="exportForm">
                                @csrf

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="format" class="form-label">Logiciel comptable *</label>
                                        <select class="form-select @error('format') is-invalid @enderror"
                                                id="format"
                                                name="format"
                                                required>
                                            <option value="">-- Sélectionnez un logiciel --</option>
                                            @foreach($formats as $key => $label)
                                                <option value="{{ $key }}" {{ old('format') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('format')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Choisissez le format compatible avec votre logiciel
                                        </small>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="type" class="form-label">Type d'export *</label>
                                        <select class="form-select @error('type') is-invalid @enderror"
                                                id="type"
                                                name="type"
                                                required>
                                            <option value="">-- Sélectionnez un type --</option>
                                            @foreach($types as $key => $label)
                                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Factures ou écritures comptables
                                        </small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="invoice_type" class="form-label">Type de factures</label>
                                        <select class="form-select @error('invoice_type') is-invalid @enderror"
                                                id="invoice_type"
                                                name="invoice_type">
                                            <option value="all" {{ old('invoice_type', 'all') == 'all' ? 'selected' : '' }}>
                                                Toutes les factures
                                            </option>
                                            <option value="sales" {{ old('invoice_type') == 'sales' ? 'selected' : '' }}>
                                                Factures de vente uniquement
                                            </option>
                                            <option value="purchases" {{ old('invoice_type') == 'purchases' ? 'selected' : '' }}>
                                                Factures d'achat uniquement
                                            </option>
                                        </select>
                                        @error('invoice_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="period" class="form-label">Période *</label>
                                        <select class="form-select @error('period') is-invalid @enderror"
                                                id="period"
                                                name="period"
                                                required>
                                            <option value="">-- Sélectionnez une période --</option>
                                            @foreach($periods as $key => $label)
                                                <option value="{{ $key }}" {{ old('period') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div id="customPeriodFields" class="row mb-3" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="date_from" class="form-label">Date de début *</label>
                                        <input type="date"
                                               class="form-control @error('date_from') is-invalid @enderror"
                                               id="date_from"
                                               name="date_from"
                                               value="{{ old('date_from') }}">
                                        @error('date_from')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date_to" class="form-label">Date de fin *</label>
                                        <input type="date"
                                               class="form-control @error('date_to') is-invalid @enderror"
                                               id="date_to"
                                               name="date_to"
                                               value="{{ old('date_to') }}">
                                        @error('date_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-download"></i> Générer l'export
                                        </button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-secondary ms-2">
                                            Annuler
                                        </a>
                                    </div>
                                    <div class="text-muted">
                                        <small>* Champs obligatoires</small>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="bi bi-info-circle"></i> Informations
                                    </h5>

                                    <h6 class="mt-3">Logiciels compatibles</h6>
                                    <ul class="small">
                                        <li><strong>Sage BOB 50</strong> - Format CSV</li>
                                        <li><strong>Winbooks</strong> - Format XML</li>
                                        <li><strong>Win Auditor</strong> - Format CSV</li>
                                        <li><strong>Horus</strong> - Format Excel/CSV</li>
                                        <li><strong>CSV Générique</strong> - Compatible tout logiciel</li>
                                        <li><strong>Excel Générique</strong> - Compatible tout logiciel</li>
                                    </ul>

                                    <h6 class="mt-3">Types d'export</h6>
                                    <ul class="small">
                                        <li><strong>Factures</strong> - Liste détaillée des factures avec montants et TVA</li>
                                        <li><strong>Écritures comptables</strong> - Écritures au format journal pour import direct</li>
                                    </ul>

                                    <h6 class="mt-3">Conseils</h6>
                                    <ul class="small">
                                        <li>Vérifiez le format supporté par votre logiciel comptable</li>
                                        <li>Exportez régulièrement (mensuellement recommandé)</li>
                                        <li>Conservez une copie de vos exports</li>
                                        <li>Testez avec une petite période avant un export complet</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('period');
    const customPeriodFields = document.getElementById('customPeriodFields');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    // Afficher/masquer les champs de période personnalisée
    periodSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customPeriodFields.style.display = 'flex';
            dateFromInput.required = true;
            dateToInput.required = true;
        } else {
            customPeriodFields.style.display = 'none';
            dateFromInput.required = false;
            dateToInput.required = false;
        }
    });

    // Initialiser l'affichage si "custom" est sélectionné (après validation échouée)
    if (periodSelect.value === 'custom') {
        customPeriodFields.style.display = 'flex';
        dateFromInput.required = true;
        dateToInput.required = true;
    }

    // Validation du formulaire
    const exportForm = document.getElementById('exportForm');
    exportForm.addEventListener('submit', function(e) {
        const format = document.getElementById('format').value;
        const type = document.getElementById('type').value;
        const period = periodSelect.value;

        if (!format || !type || !period) {
            e.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires');
            return false;
        }

        if (period === 'custom') {
            const dateFrom = dateFromInput.value;
            const dateTo = dateToInput.value;

            if (!dateFrom || !dateTo) {
                e.preventDefault();
                alert('Veuillez sélectionner les dates de début et de fin');
                return false;
            }

            if (new Date(dateTo) < new Date(dateFrom)) {
                e.preventDefault();
                alert('La date de fin doit être postérieure à la date de début');
                return false;
            }
        }

        // Afficher un message de chargement
        const submitBtn = exportForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Génération en cours...';
    });
});
</script>
@endpush
</x-app-layout>
