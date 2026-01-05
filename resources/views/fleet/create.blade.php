<x-app-layout>
    <x-slot name="title">Nouveau vehicule</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('fleet.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Flotte</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Nouveau</span>
    @endsection

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Nouveau vehicule</h1>
            <p class="text-secondary-500 dark:text-secondary-400 mt-1">Ajoutez un vehicule a votre flotte</p>
        </div>

        <form action="{{ route('fleet.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Identification -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Identification</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Marque <span class="text-danger-500">*</span></label>
                        <input type="text" name="brand" value="{{ old('brand') }}" class="form-input @error('brand') border-danger-500 @enderror" required>
                        @error('brand')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Modele <span class="text-danger-500">*</span></label>
                        <input type="text" name="model" value="{{ old('model') }}" class="form-input @error('model') border-danger-500 @enderror" required>
                        @error('model')<p class="text-danger-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Annee</label>
                        <input type="number" name="year" value="{{ old('year', date('Y')) }}" min="1990" max="{{ date('Y') + 1 }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Plaque d'immatriculation</label>
                        <input type="text" name="license_plate" value="{{ old('license_plate') }}" class="form-input" placeholder="1-ABC-123">
                    </div>
                    <div>
                        <label class="form-label">Numero VIN</label>
                        <input type="text" name="vin" value="{{ old('vin') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Attribue a</label>
                        <select name="assigned_user_id" class="form-select">
                            <option value="">Non attribue</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Type et motorisation -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Type et motorisation</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Type <span class="text-danger-500">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="car" {{ old('type', 'car') == 'car' ? 'selected' : '' }}>Voiture</option>
                            <option value="van" {{ old('type') == 'van' ? 'selected' : '' }}>Utilitaire</option>
                            <option value="truck" {{ old('type') == 'truck' ? 'selected' : '' }}>Camion</option>
                            <option value="motorcycle" {{ old('type') == 'motorcycle' ? 'selected' : '' }}>Moto</option>
                            <option value="electric_bike" {{ old('type') == 'electric_bike' ? 'selected' : '' }}>Velo electrique</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Carburant <span class="text-danger-500">*</span></label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Essence</option>
                            <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="hybrid" {{ old('fuel_type') == 'hybrid' ? 'selected' : '' }}>Hybride</option>
                            <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electrique</option>
                            <option value="lpg" {{ old('fuel_type') == 'lpg' ? 'selected' : '' }}>GPL</option>
                            <option value="cng" {{ old('fuel_type') == 'cng' ? 'selected' : '' }}>GNC</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Propriete <span class="text-danger-500">*</span></label>
                        <select name="ownership" class="form-select" required>
                            <option value="owned" {{ old('ownership', 'owned') == 'owned' ? 'selected' : '' }}>Propriete</option>
                            <option value="leased" {{ old('ownership') == 'leased' ? 'selected' : '' }}>Leasing</option>
                            <option value="rented" {{ old('ownership') == 'rented' ? 'selected' : '' }}>Location</option>
                            <option value="employee_owned" {{ old('ownership') == 'employee_owned' ? 'selected' : '' }}>Vehicule employe</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Emission CO2 (g/km)</label>
                        <input type="number" name="co2_emission" value="{{ old('co2_emission') }}" min="0" max="500" class="form-input">
                        <p class="text-xs text-secondary-500 mt-1">Necessaire pour le calcul ATN</p>
                    </div>
                    <div>
                        <label class="form-label">Norme emission</label>
                        <select name="emission_standard" class="form-select">
                            <option value="">-</option>
                            <option value="euro6d" {{ old('emission_standard') == 'euro6d' ? 'selected' : '' }}>Euro 6d</option>
                            <option value="euro6" {{ old('emission_standard') == 'euro6' ? 'selected' : '' }}>Euro 6</option>
                            <option value="euro5" {{ old('emission_standard') == 'euro5' ? 'selected' : '' }}>Euro 5</option>
                            <option value="euro4" {{ old('emission_standard') == 'euro4' ? 'selected' : '' }}>Euro 4</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Puissance (kW)</label>
                        <input type="number" name="engine_power_kw" value="{{ old('engine_power_kw') }}" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Valeurs ATN -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Valeurs pour ATN belge</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Valeur catalogue</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="catalog_value" value="{{ old('catalog_value') }}" class="form-input pr-10">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                        </div>
                        <p class="text-xs text-secondary-500 mt-1">Prix catalogue TVAC du vehicule neuf</p>
                    </div>
                    <div>
                        <label class="form-label">Valeur options</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="options_value" value="{{ old('options_value', 0) }}" class="form-input pr-10">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                        </div>
                        <p class="text-xs text-secondary-500 mt-1">Valeur des options supplementaires</p>
                    </div>
                    <div>
                        <label class="form-label">Premiere immatriculation</label>
                        <input type="date" name="first_registration_date" value="{{ old('first_registration_date') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Date d'acquisition</label>
                        <input type="date" name="acquisition_date" value="{{ old('acquisition_date', date('Y-m-d')) }}" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Kilometrage et assurance -->
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Kilometrage et assurance</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Kilometrage initial</label>
                        <input type="number" name="odometer_start" value="{{ old('odometer_start', 0) }}" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Controle technique</label>
                        <input type="date" name="technical_inspection_date" value="{{ old('technical_inspection_date') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Compagnie d'assurance</label>
                        <input type="text" name="insurance_company" value="{{ old('insurance_company') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Numero de police</label>
                        <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Echeance assurance</label>
                        <input type="date" name="insurance_expiry_date" value="{{ old('insurance_expiry_date') }}" class="form-input">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('fleet.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Creer le vehicule</button>
            </div>
        </form>
    </div>
</x-app-layout>
