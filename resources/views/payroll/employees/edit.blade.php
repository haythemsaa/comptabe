@php
    $title = 'Modifier l\'employé';
@endphp

<x-app-layout :title="$title">
    <div class="p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier l'employé</h1>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                    {{ $employee->full_name }}
                </p>
            </div>
            <a href="{{ route('payroll.employees.show', $employee) }}" class="btn btn-ghost">
                ← Retour aux détails
            </a>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('payroll.employees.update', $employee) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informations personnelles</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Employee Number -->
                        <div>
                            <label for="employee_number" class="label">Numéro d'employé *</label>
                            <input
                                type="text"
                                id="employee_number"
                                name="employee_number"
                                value="{{ old('employee_number', $employee->employee_number) }}"
                                class="input @error('employee_number') input-error @enderror"
                                required
                            />
                            @error('employee_number')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="label">Statut *</label>
                            <select
                                id="status"
                                name="status"
                                class="input @error('status') input-error @enderror"
                                required
                            >
                                <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="on_leave" {{ old('status', $employee->status) === 'on_leave' ? 'selected' : '' }}>En congé</option>
                                <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>Terminé</option>
                            </select>
                            @error('status')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="label">Prénom *</label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name', $employee->first_name) }}"
                                class="input @error('first_name') input-error @enderror"
                                required
                            />
                            @error('first_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="label">Nom *</label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name', $employee->last_name) }}"
                                class="input @error('last_name') input-error @enderror"
                                required
                            />
                            @error('last_name')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- National Number (Belgium) or CIN (Tunisia) -->
                        @if($company->country_code === 'TN')
                            <div>
                                <label for="cin" class="label">CIN (Carte d'Identité Nationale)</label>
                                <input
                                    type="text"
                                    id="cin"
                                    name="cin"
                                    value="{{ old('cin', $employee->cin) }}"
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
                                    value="{{ old('national_number', $employee->national_number) }}"
                                    class="input @error('national_number') input-error @enderror"
                                    placeholder="00.00.00-000.00"
                                />
                                @error('national_number')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Birth Date -->
                        <div>
                            <label for="birth_date" class="label">Date de naissance *</label>
                            <input
                                type="date"
                                id="birth_date"
                                name="birth_date"
                                value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"
                                class="input @error('birth_date') input-error @enderror"
                                required
                            />
                            @error('birth_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Contact</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div>
                            <label for="email" class="label">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $employee->email) }}"
                                class="input @error('email') input-error @enderror"
                            />
                            @error('email')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="label">Téléphone</label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="{{ old('phone', $employee->phone) }}"
                                class="input @error('phone') input-error @enderror"
                            />
                            @error('phone')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Adresse</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Street -->
                        <div>
                            <label for="street" class="label">Rue</label>
                            <input
                                type="text"
                                id="street"
                                name="street"
                                value="{{ old('street', $employee->street) }}"
                                class="input @error('street') input-error @enderror"
                            />
                            @error('street')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- House Number -->
                        <div>
                            <label for="house_number" class="label">Numéro</label>
                            <input
                                type="text"
                                id="house_number"
                                name="house_number"
                                value="{{ old('house_number', $employee->house_number) }}"
                                class="input @error('house_number') input-error @enderror"
                            />
                            @error('house_number')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Postal Code -->
                        <div>
                            <label for="postal_code" class="label">Code postal</label>
                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                value="{{ old('postal_code', $employee->postal_code) }}"
                                class="input @error('postal_code') input-error @enderror"
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
                                value="{{ old('city', $employee->city) }}"
                                class="input @error('city') input-error @enderror"
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
                                <option value="BE" {{ old('country_code', $employee->country_code ?? 'BE') === 'BE' ? 'selected' : '' }}>Belgique</option>
                                <option value="FR" {{ old('country_code', $employee->country_code) === 'FR' ? 'selected' : '' }}>France</option>
                                <option value="NL" {{ old('country_code', $employee->country_code) === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                <option value="LU" {{ old('country_code', $employee->country_code) === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                <option value="DE" {{ old('country_code', $employee->country_code) === 'DE' ? 'selected' : '' }}>Allemagne</option>
                            </select>
                            @error('country_code')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informations d'emploi</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Hire Date -->
                        <div>
                            <label for="hire_date" class="label">Date d'embauche *</label>
                            <input
                                type="date"
                                id="hire_date"
                                name="hire_date"
                                value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}"
                                class="input @error('hire_date') input-error @enderror"
                                required
                            />
                            @error('hire_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Termination Date -->
                        <div>
                            <label for="termination_date" class="label">Date de fin (si applicable)</label>
                            <input
                                type="date"
                                id="termination_date"
                                name="termination_date"
                                value="{{ old('termination_date', $employee->termination_date?->format('Y-m-d')) }}"
                                class="input @error('termination_date') input-error @enderror"
                            />
                            @error('termination_date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($company->country_code === 'TN')
                            <!-- CNSS Number (Tunisia) -->
                            <div>
                                <label for="cnss_number" class="label">Numéro CNSS</label>
                                <input
                                    type="text"
                                    id="cnss_number"
                                    name="cnss_number"
                                    value="{{ old('cnss_number', $employee->cnss_number) }}"
                                    class="input @error('cnss_number') input-error @enderror"
                                    placeholder="1234567890123"
                                />
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
                                    value="{{ old('rib', $employee->rib) }}"
                                    class="input @error('rib') input-error @enderror"
                                    placeholder="20 digits"
                                />
                                @error('rib')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <!-- IBAN (Belgium and others) -->
                            <div>
                                <label for="iban" class="label">IBAN</label>
                                <input
                                    type="text"
                                    id="iban"
                                    name="iban"
                                    value="{{ old('iban', $employee->iban) }}"
                                    class="input @error('iban') input-error @enderror"
                                    placeholder="BE68 5390 0754 7034"
                                />
                                @error('iban')
                                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('payroll.employees.show', $employee) }}" class="btn btn-ghost">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
