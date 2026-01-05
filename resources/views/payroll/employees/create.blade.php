@php
    $title = 'Nouvel employé';
@endphp

<x-app-layout :title="$title">
    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('payroll.employees.index') }}" class="text-secondary-500 hover:text-secondary-700">Employés</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouveau</span>
    @endsection

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    Nouvel employé
                </h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    Remplissez les informations pour ajouter un nouveau membre à l'équipe
                </p>
            </div>
            <a href="{{ route('payroll.employees.index') }}" class="btn btn-ghost">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la liste
            </a>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('payroll.employees.store') }}" class="space-y-6" x-data="employeeForm()">
            @csrf

            <!-- Identification -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Identification</h2>
                            <p class="text-xs text-secondary-500">Numéro d'employé et statut</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Employee Number -->
                        <div>
                            <label for="employee_number" class="label">Numéro d'employé <span class="text-danger-500">*</span></label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="employee_number"
                                    name="employee_number"
                                    value="{{ old('employee_number') }}"
                                    x-ref="employeeNumber"
                                    class="input pr-10 @error('employee_number') input-error @enderror"
                                    required
                                />
                                <button type="button" @click="generateNumber()" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-secondary-400 hover:text-primary-600" title="Générer automatiquement">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            @error('employee_number')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-secondary-500 mt-1">Cliquez sur l'icône pour générer automatiquement</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="label">Statut <span class="text-danger-500">*</span></label>
                            <select
                                id="status"
                                name="status"
                                class="input @error('status') input-error @enderror"
                                required
                            >
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>En congé</option>
                                <option value="terminated" {{ old('status') === 'terminated' ? 'selected' : '' }}>Terminé</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hire Date -->
                        <div>
                            <label for="hire_date" class="label">Date d'embauche <span class="text-danger-500">*</span></label>
                            <input
                                type="date"
                                id="hire_date"
                                name="hire_date"
                                value="{{ old('hire_date', date('Y-m-d')) }}"
                                class="input @error('hire_date') input-error @enderror"
                                required
                            />
                            @error('hire_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Informations personnelles</h2>
                            <p class="text-xs text-secondary-500">Identité et état civil</p>
                        </div>
                    </div>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Gender -->
                        <div>
                            <label for="gender" class="label">Genre</label>
                            <select
                                id="gender"
                                name="gender"
                                class="input @error('gender') input-error @enderror"
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>Homme</option>
                                <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Femme</option>
                                <option value="X" {{ old('gender') === 'X' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('gender')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="label">Prénom <span class="text-danger-500">*</span></label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                class="input @error('first_name') input-error @enderror"
                                required
                            />
                            @error('first_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="label">Nom <span class="text-danger-500">*</span></label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                class="input @error('last_name') input-error @enderror"
                                required
                            />
                            @error('last_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Maiden Name -->
                        <div>
                            <label for="maiden_name" class="label">Nom de jeune fille</label>
                            <input
                                type="text"
                                id="maiden_name"
                                name="maiden_name"
                                value="{{ old('maiden_name') }}"
                                class="input @error('maiden_name') input-error @enderror"
                            />
                            @error('maiden_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nationality -->
                        <div>
                            <label for="nationality" class="label">Nationalité</label>
                            <select
                                id="nationality"
                                name="nationality"
                                class="input @error('nationality') input-error @enderror"
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="BE" {{ old('nationality', $company->country_code) === 'BE' ? 'selected' : '' }}>Belge</option>
                                <option value="FR" {{ old('nationality') === 'FR' ? 'selected' : '' }}>Française</option>
                                <option value="NL" {{ old('nationality') === 'NL' ? 'selected' : '' }}>Néerlandaise</option>
                                <option value="LU" {{ old('nationality') === 'LU' ? 'selected' : '' }}>Luxembourgeoise</option>
                                <option value="DE" {{ old('nationality') === 'DE' ? 'selected' : '' }}>Allemande</option>
                                <option value="TN" {{ old('nationality', $company->country_code) === 'TN' ? 'selected' : '' }}>Tunisienne</option>
                                <option value="MA" {{ old('nationality') === 'MA' ? 'selected' : '' }}>Marocaine</option>
                                <option value="DZ" {{ old('nationality') === 'DZ' ? 'selected' : '' }}>Algérienne</option>
                                <option value="IT" {{ old('nationality') === 'IT' ? 'selected' : '' }}>Italienne</option>
                                <option value="ES" {{ old('nationality') === 'ES' ? 'selected' : '' }}>Espagnole</option>
                                <option value="PT" {{ old('nationality') === 'PT' ? 'selected' : '' }}>Portugaise</option>
                                <option value="PL" {{ old('nationality') === 'PL' ? 'selected' : '' }}>Polonaise</option>
                                <option value="RO" {{ old('nationality') === 'RO' ? 'selected' : '' }}>Roumaine</option>
                                <option value="OTHER" {{ old('nationality') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('nationality')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- National Number / CIN -->
                        @if($company->country_code === 'TN')
                            <div>
                                <label for="cin" class="label">CIN (Carte d'Identité Nationale)</label>
                                <input
                                    type="text"
                                    id="cin"
                                    name="cin"
                                    value="{{ old('cin') }}"
                                    class="input @error('cin') input-error @enderror"
                                    placeholder="12345678"
                                />
                                @error('cin')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div>
                                <label for="national_number" class="label">Numéro de registre national</label>
                                <input
                                    type="text"
                                    id="national_number"
                                    name="national_number"
                                    value="{{ old('national_number') }}"
                                    class="input @error('national_number') input-error @enderror"
                                    placeholder="00.00.00-000.00"
                                    x-mask="99.99.99-999.99"
                                />
                                @error('national_number')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Birth Date -->
                        <div>
                            <label for="birth_date" class="label">Date de naissance <span class="text-danger-500">*</span></label>
                            <input
                                type="date"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date') }}"
                                class="input @error('birth_date') input-error @enderror"
                                required
                            />
                            @error('birth_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birth Place -->
                        <div>
                            <label for="birth_place" class="label">Lieu de naissance</label>
                            <input
                                type="text"
                                id="birth_place"
                                name="birth_place"
                                value="{{ old('birth_place') }}"
                                class="input @error('birth_place') input-error @enderror"
                                placeholder="Ville"
                            />
                            @error('birth_place')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birth Country -->
                        <div>
                            <label for="birth_country" class="label">Pays de naissance</label>
                            <select
                                id="birth_country"
                                name="birth_country"
                                class="input @error('birth_country') input-error @enderror"
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="BE" {{ old('birth_country', $company->country_code) === 'BE' ? 'selected' : '' }}>Belgique</option>
                                <option value="FR" {{ old('birth_country') === 'FR' ? 'selected' : '' }}>France</option>
                                <option value="NL" {{ old('birth_country') === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                <option value="LU" {{ old('birth_country') === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                <option value="DE" {{ old('birth_country') === 'DE' ? 'selected' : '' }}>Allemagne</option>
                                <option value="TN" {{ old('birth_country', $company->country_code) === 'TN' ? 'selected' : '' }}>Tunisie</option>
                                <option value="MA" {{ old('birth_country') === 'MA' ? 'selected' : '' }}>Maroc</option>
                                <option value="DZ" {{ old('birth_country') === 'DZ' ? 'selected' : '' }}>Algérie</option>
                                <option value="OTHER" {{ old('birth_country') === 'OTHER' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('birth_country')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Coordonnées</h2>
                            <p class="text-xs text-secondary-500">Email et téléphone</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Email -->
                        <div>
                            <label for="email" class="label">Email professionnel</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="input pl-10 @error('email') input-error @enderror"
                                    placeholder="prenom.nom@entreprise.com"
                                />
                            </div>
                            @error('email')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="label">Téléphone fixe</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    class="input pl-10 @error('phone') input-error @enderror"
                                    placeholder="{{ $company->country_code === 'TN' ? '+216 XX XXX XXX' : '+32 X XXX XX XX' }}"
                                />
                            </div>
                            @error('phone')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mobile -->
                        <div>
                            <label for="mobile" class="label">Téléphone mobile</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    type="tel"
                                    id="mobile"
                                    name="mobile"
                                    value="{{ old('mobile') }}"
                                    class="input pl-10 @error('mobile') input-error @enderror"
                                    placeholder="{{ $company->country_code === 'TN' ? '+216 XX XXX XXX' : '+32 4XX XX XX XX' }}"
                                />
                            </div>
                            @error('mobile')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Adresse de domicile</h2>
                            <p class="text-xs text-secondary-500">Adresse de résidence principale</p>
                        </div>
                    </div>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Street -->
                        <div class="md:col-span-6">
                            <label for="street" class="label">Rue</label>
                            <input
                                type="text"
                                id="street"
                                name="street"
                                value="{{ old('street') }}"
                                class="input @error('street') input-error @enderror"
                                placeholder="Nom de la rue"
                            />
                            @error('street')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- House Number -->
                        <div class="md:col-span-3">
                            <label for="house_number" class="label">Numéro</label>
                            <input
                                type="text"
                                id="house_number"
                                name="house_number"
                                value="{{ old('house_number') }}"
                                class="input @error('house_number') input-error @enderror"
                                placeholder="123"
                            />
                            @error('house_number')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Box -->
                        <div class="md:col-span-3">
                            <label for="box" class="label">Boîte</label>
                            <input
                                type="text"
                                id="box"
                                name="box"
                                value="{{ old('box') }}"
                                class="input @error('box') input-error @enderror"
                                placeholder="A, B, 1..."
                            />
                            @error('box')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Postal Code -->
                        <div>
                            <label for="postal_code" class="label">Code postal</label>
                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                value="{{ old('postal_code') }}"
                                class="input @error('postal_code') input-error @enderror"
                                placeholder="{{ $company->country_code === 'TN' ? '1000' : '1000' }}"
                            />
                            @error('postal_code')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City -->
                        <div>
                            <label for="city" class="label">Ville</label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                value="{{ old('city') }}"
                                class="input @error('city') input-error @enderror"
                                placeholder="{{ $company->country_code === 'TN' ? 'Tunis' : 'Bruxelles' }}"
                            />
                            @error('city')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country_code" class="label">Pays</label>
                            <select
                                id="country_code"
                                name="country_code"
                                class="input @error('country_code') input-error @enderror"
                            >
                                <option value="BE" {{ old('country_code', $company->country_code) === 'BE' ? 'selected' : '' }}>Belgique</option>
                                <option value="FR" {{ old('country_code') === 'FR' ? 'selected' : '' }}>France</option>
                                <option value="NL" {{ old('country_code') === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                <option value="LU" {{ old('country_code') === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                <option value="DE" {{ old('country_code') === 'DE' ? 'selected' : '' }}>Allemagne</option>
                                <option value="TN" {{ old('country_code', $company->country_code) === 'TN' ? 'selected' : '' }}>Tunisie</option>
                            </select>
                            @error('country_code')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banking Information -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Informations bancaires</h2>
                            <p class="text-xs text-secondary-500">Pour le versement du salaire</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($company->country_code === 'TN')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- CNSS Number (Tunisia) -->
                            <div>
                                <label for="cnss_number" class="label">Numéro CNSS</label>
                                <input
                                    type="text"
                                    id="cnss_number"
                                    name="cnss_number"
                                    value="{{ old('cnss_number') }}"
                                    class="input @error('cnss_number') input-error @enderror"
                                    placeholder="1234567890123"
                                />
                                <p class="text-xs text-secondary-500 mt-1">Numéro de sécurité sociale tunisienne</p>
                                @error('cnss_number')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- RIB (Tunisia) -->
                            <div>
                                <label for="rib" class="label">RIB (Relevé d'Identité Bancaire)</label>
                                <input
                                    type="text"
                                    id="rib"
                                    name="rib"
                                    value="{{ old('rib') }}"
                                    class="input @error('rib') input-error @enderror"
                                    placeholder="20 chiffres"
                                />
                                <p class="text-xs text-secondary-500 mt-1">20 caractères</p>
                                @error('rib')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- IBAN -->
                            <div>
                                <label for="iban" class="label">IBAN</label>
                                <input
                                    type="text"
                                    id="iban"
                                    name="iban"
                                    value="{{ old('iban') }}"
                                    class="input font-mono @error('iban') input-error @enderror"
                                    placeholder="BE68 5390 0754 7034"
                                />
                                <p class="text-xs text-secondary-500 mt-1">Numéro de compte international</p>
                                @error('iban')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- BIC -->
                            <div>
                                <label for="bic" class="label">BIC / SWIFT</label>
                                <input
                                    type="text"
                                    id="bic"
                                    name="bic"
                                    value="{{ old('bic') }}"
                                    class="input font-mono @error('bic') input-error @enderror"
                                    placeholder="GEBABEBB"
                                />
                                <p class="text-xs text-secondary-500 mt-1">Code d'identification de la banque</p>
                                @error('bic')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card">
                <div class="card-header border-b border-secondary-100 dark:border-secondary-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Contact d'urgence</h2>
                            <p class="text-xs text-secondary-500">Personne à contacter en cas d'urgence</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Emergency Contact Name -->
                        <div>
                            <label for="emergency_contact_name" class="label">Nom complet</label>
                            <input
                                type="text"
                                id="emergency_contact_name"
                                name="emergency_contact_name"
                                value="{{ old('emergency_contact_name') }}"
                                class="input @error('emergency_contact_name') input-error @enderror"
                                placeholder="Prénom et nom"
                            />
                            @error('emergency_contact_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Emergency Contact Phone -->
                        <div>
                            <label for="emergency_contact_phone" class="label">Téléphone</label>
                            <input
                                type="tel"
                                id="emergency_contact_phone"
                                name="emergency_contact_phone"
                                value="{{ old('emergency_contact_phone') }}"
                                class="input @error('emergency_contact_phone') input-error @enderror"
                                placeholder="{{ $company->country_code === 'TN' ? '+216 XX XXX XXX' : '+32 4XX XX XX XX' }}"
                            />
                            @error('emergency_contact_phone')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Emergency Contact Relationship -->
                        <div>
                            <label for="emergency_contact_relationship" class="label">Lien de parenté</label>
                            <select
                                id="emergency_contact_relationship"
                                name="emergency_contact_relationship"
                                class="input @error('emergency_contact_relationship') input-error @enderror"
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="spouse" {{ old('emergency_contact_relationship') === 'spouse' ? 'selected' : '' }}>Conjoint(e)</option>
                                <option value="parent" {{ old('emergency_contact_relationship') === 'parent' ? 'selected' : '' }}>Parent</option>
                                <option value="child" {{ old('emergency_contact_relationship') === 'child' ? 'selected' : '' }}>Enfant</option>
                                <option value="sibling" {{ old('emergency_contact_relationship') === 'sibling' ? 'selected' : '' }}>Frère/Soeur</option>
                                <option value="friend" {{ old('emergency_contact_relationship') === 'friend' ? 'selected' : '' }}>Ami(e)</option>
                                <option value="other" {{ old('emergency_contact_relationship') === 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('emergency_contact_relationship')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
                <p class="text-sm text-secondary-500">
                    <span class="text-danger-500">*</span> Champs obligatoires
                </p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('payroll.employees.index') }}" class="btn btn-ghost">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Créer l'employé
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function employeeForm() {
            return {
                async generateNumber() {
                    try {
                        const response = await fetch('{{ route("payroll.employees.generate-number") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        if (data.employee_number) {
                            this.$refs.employeeNumber.value = data.employee_number;
                        }
                    } catch (error) {
                        // Fallback: generate locally
                        const year = new Date().getFullYear();
                        const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
                        this.$refs.employeeNumber.value = `EMP-${year}-${random}`;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
