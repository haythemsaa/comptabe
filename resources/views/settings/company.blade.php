<x-app-layout>
    <x-slot name="title">Paramètres - Entreprise</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Paramètres</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Paramètres</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Configurez votre entreprise et vos préférences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:w-64 flex-shrink-0">
                <x-settings-nav active="company" />
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- Logo Section -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Logo de l'entreprise</h2>
                    </div>
                    <div class="card-body">
                        <div class="flex items-start gap-6">
                            <div class="w-24 h-24 bg-secondary-100 dark:bg-secondary-800 rounded-xl flex items-center justify-center overflow-hidden">
                                @if($company->logo_path)
                                    <img src="{{ Storage::url($company->logo_path) }}" alt="{{ $company->name }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-3xl font-bold text-secondary-400">{{ strtoupper(substr($company->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-4">
                                    Votre logo apparaîtra sur vos factures et documents. Format recommandé: PNG ou SVG, max 2 MB.
                                </p>
                                <div class="flex gap-3">
                                    <form action="{{ route('settings.logo.upload') }}" method="POST" enctype="multipart/form-data" class="inline">
                                        @csrf
                                        <label class="btn btn-secondary cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Télécharger
                                            <input type="file" name="logo" class="hidden" accept="image/*" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                    @if($company->logo_path)
                                        <form action="{{ route('settings.logo.delete') }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-outline">Supprimer</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Info Form -->
                <form action="{{ route('settings.company.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Informations de l'entreprise</h2>
                        </div>
                        <div class="card-body space-y-6">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label for="name" class="form-label">Nom de l'entreprise *</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $company->name) }}"
                                        required
                                        class="form-input @error('name') form-input-error @enderror"
                                    >
                                    @error('name')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="vat_number" class="form-label">Numéro de TVA *</label>
                                    <input
                                        type="text"
                                        id="vat_number"
                                        name="vat_number"
                                        value="{{ old('vat_number', $company->vat_number) }}"
                                        required
                                        class="form-input font-mono @error('vat_number') form-input-error @enderror"
                                    >
                                    @error('vat_number')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="enterprise_number" class="form-label">Numéro d'entreprise</label>
                                    <input
                                        type="text"
                                        id="enterprise_number"
                                        name="enterprise_number"
                                        value="{{ old('enterprise_number', $company->enterprise_number) }}"
                                        class="form-input font-mono"
                                    >
                                </div>

                                <div>
                                    <label for="legal_form" class="form-label">Forme juridique</label>
                                    <select name="legal_form" id="legal_form" class="form-select">
                                        <option value="">Sélectionner...</option>
                                        <option value="SRL" {{ old('legal_form', $company->legal_form) === 'SRL' ? 'selected' : '' }}>SRL</option>
                                        <option value="SA" {{ old('legal_form', $company->legal_form) === 'SA' ? 'selected' : '' }}>SA</option>
                                        <option value="SC" {{ old('legal_form', $company->legal_form) === 'SC' ? 'selected' : '' }}>SC</option>
                                        <option value="SNC" {{ old('legal_form', $company->legal_form) === 'SNC' ? 'selected' : '' }}>SNC</option>
                                        <option value="SCS" {{ old('legal_form', $company->legal_form) === 'SCS' ? 'selected' : '' }}>SCS</option>
                                        <option value="ASBL" {{ old('legal_form', $company->legal_form) === 'ASBL' ? 'selected' : '' }}>ASBL</option>
                                        <option value="PP" {{ old('legal_form', $company->legal_form) === 'PP' ? 'selected' : '' }}>Personne physique</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="border-secondary-200 dark:border-secondary-700">

                            <!-- Address -->
                            <div>
                                <h3 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-4">Adresse</h3>
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="col-span-2">
                                        <label for="street" class="form-label">Rue</label>
                                        <input
                                            type="text"
                                            id="street"
                                            name="street"
                                            value="{{ old('street', $company->street) }}"
                                            class="form-input"
                                        >
                                    </div>
                                    <div>
                                        <label for="house_number" class="form-label">Numéro</label>
                                        <input
                                            type="text"
                                            id="house_number"
                                            name="house_number"
                                            value="{{ old('house_number', $company->house_number) }}"
                                            class="form-input"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4 mt-4">
                                    <div>
                                        <label for="postal_code" class="form-label">Code postal</label>
                                        <input
                                            type="text"
                                            id="postal_code"
                                            name="postal_code"
                                            value="{{ old('postal_code', $company->postal_code) }}"
                                            class="form-input"
                                        >
                                    </div>
                                    <div class="col-span-2">
                                        <label for="city" class="form-label">Ville</label>
                                        <input
                                            type="text"
                                            id="city"
                                            name="city"
                                            value="{{ old('city', $company->city) }}"
                                            class="form-input"
                                        >
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label for="country_code" class="form-label">Pays</label>
                                    <select name="country_code" id="country_code" class="form-select w-auto">
                                        <option value="BE" {{ old('country_code', $company->country_code) === 'BE' ? 'selected' : '' }}>Belgique</option>
                                        <option value="FR" {{ old('country_code', $company->country_code) === 'FR' ? 'selected' : '' }}>France</option>
                                        <option value="NL" {{ old('country_code', $company->country_code) === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                        <option value="DE" {{ old('country_code', $company->country_code) === 'DE' ? 'selected' : '' }}>Allemagne</option>
                                        <option value="LU" {{ old('country_code', $company->country_code) === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="border-secondary-200 dark:border-secondary-700">

                            <!-- Contact -->
                            <div>
                                <h3 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-4">Contact</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="email" class="form-label">Email</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value="{{ old('email', $company->email) }}"
                                            class="form-input @error('email') form-input-error @enderror"
                                        >
                                        @error('email')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value="{{ old('phone', $company->phone) }}"
                                            class="form-input"
                                        >
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="website" class="form-label">Site web</label>
                                        <input
                                            type="url"
                                            id="website"
                                            name="website"
                                            value="{{ old('website', $company->website) }}"
                                            class="form-input"
                                        >
                                    </div>
                                </div>
                            </div>

                            <hr class="border-secondary-200 dark:border-secondary-700">

                            <!-- Bank Details -->
                            <div>
                                <h3 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-4">Coordonnées bancaires</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label for="iban" class="form-label">IBAN</label>
                                        <input
                                            type="text"
                                            id="iban"
                                            name="iban"
                                            value="{{ old('iban', $company->iban) }}"
                                            class="form-input font-mono @error('iban') form-input-error @enderror"
                                            placeholder="BE00 0000 0000 0000"
                                        >
                                        @error('iban')
                                            <p class="form-error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="bic" class="form-label">BIC</label>
                                        <input
                                            type="text"
                                            id="bic"
                                            name="bic"
                                            value="{{ old('bic', $company->bic) }}"
                                            class="form-input font-mono"
                                            placeholder="GEBABEBB"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
