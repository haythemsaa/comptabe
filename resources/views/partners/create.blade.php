<x-app-layout>
    <x-slot name="title">Nouveau partenaire</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('partners.index') }}" class="text-secondary-500 hover:text-secondary-700">Partenaires</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouveau</span>
    @endsection

    <form
        method="POST"
        action="{{ route('partners.store') }}"
        x-data="partnerForm()"
        class="space-y-6"
    >
        @csrf

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouveau partenaire</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Ajoutez un client ou fournisseur</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('partners.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations générales</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <!-- Partner Type -->
                        <div>
                            <label class="form-label">Type de partenaire *</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="is_customer"
                                        value="1"
                                        {{ old('is_customer', true) ? 'checked' : '' }}
                                        class="form-checkbox"
                                    >
                                    <span class="text-secondary-700 dark:text-secondary-300">Client</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="is_supplier"
                                        value="1"
                                        {{ old('is_supplier') ? 'checked' : '' }}
                                        class="form-checkbox"
                                    >
                                    <span class="text-secondary-700 dark:text-secondary-300">Fournisseur</span>
                                </label>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label for="name" class="form-label">Nom / Raison sociale *</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                class="form-input @error('name') form-input-error @enderror"
                                placeholder="Nom du partenaire"
                            >
                            @error('name')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- VAT Number -->
                            <div>
                                <label for="vat_number" class="form-label">Numéro de TVA</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="vat_number"
                                        name="vat_number"
                                        value="{{ old('vat_number') }}"
                                        x-model="vatNumber"
                                        @blur="checkVatNumber()"
                                        class="form-input @error('vat_number') form-input-error @enderror"
                                        placeholder="BE0123.456.789"
                                    >
                                    <div x-show="vatLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="w-5 h-5 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div x-show="vatValid === true" class="absolute right-3 top-1/2 -translate-y-1/2 text-success-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                @error('vat_number')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                                <p class="form-helper">Pour les entreprises belges et européennes</p>
                            </div>

                            <!-- Enterprise Number -->
                            <div>
                                <label for="enterprise_number" class="form-label">Numéro d'entreprise</label>
                                <input
                                    type="text"
                                    id="enterprise_number"
                                    name="enterprise_number"
                                    value="{{ old('enterprise_number') }}"
                                    class="form-input"
                                    placeholder="0123.456.789"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Adresse</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <label for="street" class="form-label">Rue</label>
                                <input
                                    type="text"
                                    id="street"
                                    name="street"
                                    value="{{ old('street') }}"
                                    class="form-input"
                                    placeholder="Rue de la Loi"
                                >
                            </div>
                            <div>
                                <label for="house_number" class="form-label">Numéro</label>
                                <input
                                    type="text"
                                    id="house_number"
                                    name="house_number"
                                    value="{{ old('house_number') }}"
                                    class="form-input"
                                    placeholder="1"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input
                                    type="text"
                                    id="postal_code"
                                    name="postal_code"
                                    value="{{ old('postal_code') }}"
                                    class="form-input"
                                    placeholder="1000"
                                >
                            </div>
                            <div class="col-span-2">
                                <label for="city" class="form-label">Ville</label>
                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    value="{{ old('city') }}"
                                    class="form-input"
                                    placeholder="Bruxelles"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="country_code" class="form-label">Pays</label>
                            <select name="country_code" id="country_code" class="form-select">
                                <option value="BE" {{ old('country_code', 'BE') === 'BE' ? 'selected' : '' }}>Belgique</option>
                                <option value="FR" {{ old('country_code') === 'FR' ? 'selected' : '' }}>France</option>
                                <option value="NL" {{ old('country_code') === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                                <option value="DE" {{ old('country_code') === 'DE' ? 'selected' : '' }}>Allemagne</option>
                                <option value="LU" {{ old('country_code') === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                <option value="GB" {{ old('country_code') === 'GB' ? 'selected' : '' }}>Royaume-Uni</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Contact</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-input @error('email') form-input-error @enderror"
                                    placeholder="contact@example.com"
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
                                    value="{{ old('phone') }}"
                                    class="form-input"
                                    placeholder="+32 2 123 45 67"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="website" class="form-label">Site web</label>
                            <input
                                type="url"
                                id="website"
                                name="website"
                                value="{{ old('website') }}"
                                class="form-input"
                                placeholder="https://www.example.com"
                            >
                        </div>

                        <div>
                            <label for="contact_person" class="form-label">Personne de contact</label>
                            <input
                                type="text"
                                id="contact_person"
                                name="contact_person"
                                value="{{ old('contact_person') }}"
                                class="form-input"
                                placeholder="Nom et prénom"
                            >
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Notes</h2>
                    </div>
                    <div class="card-body">
                        <textarea
                            name="notes"
                            rows="3"
                            class="form-input"
                            placeholder="Notes internes..."
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Peppol Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Facturation électronique</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div x-show="peppolCapable" x-transition class="p-4 bg-success-50 dark:bg-success-900/20 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center text-success-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-success-900 dark:text-success-100">Compatible Peppol</h3>
                                    <p class="text-sm text-success-700 dark:text-success-300">Ce partenaire peut recevoir des factures électroniques</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="!peppolCapable" class="p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-secondary-100 dark:bg-secondary-800 rounded-xl flex items-center justify-center text-secondary-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-secondary-700 dark:text-secondary-300">Statut inconnu</h3>
                                    <p class="text-sm text-secondary-500">Entrez un numéro de TVA pour vérifier</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="peppol_identifier" class="form-label">Identifiant Peppol</label>
                            <input
                                type="text"
                                id="peppol_identifier"
                                name="peppol_identifier"
                                value="{{ old('peppol_identifier') }}"
                                x-model="peppolIdentifier"
                                class="form-input font-mono"
                                placeholder="0208:0123456789"
                            >
                            <p class="form-helper">Format: scheme:identifier (ex: 0208:0123456789)</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Terms -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Conditions</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label for="payment_terms_days" class="form-label">Délai de paiement</label>
                            <select name="payment_terms_days" id="payment_terms_days" class="form-select">
                                <option value="">Par défaut (30 jours)</option>
                                <option value="0" {{ old('payment_terms_days') === '0' ? 'selected' : '' }}>Comptant</option>
                                <option value="14" {{ old('payment_terms_days') === '14' ? 'selected' : '' }}>14 jours</option>
                                <option value="30" {{ old('payment_terms_days') === '30' ? 'selected' : '' }}>30 jours</option>
                                <option value="45" {{ old('payment_terms_days') === '45' ? 'selected' : '' }}>45 jours</option>
                                <option value="60" {{ old('payment_terms_days') === '60' ? 'selected' : '' }}>60 jours</option>
                                <option value="90" {{ old('payment_terms_days') === '90' ? 'selected' : '' }}>90 jours</option>
                            </select>
                        </div>

                        <div>
                            <label for="default_vat_rate" class="form-label">Taux de TVA par défaut</label>
                            <select name="default_vat_rate" id="default_vat_rate" class="form-select">
                                <option value="">Standard (21%)</option>
                                @foreach($vatCodes ?? [] as $vat)
                                    <option value="{{ $vat->rate }}" {{ old('default_vat_rate') == $vat->rate ? 'selected' : '' }}>
                                        {{ $vat->rate }}% - {{ $vat->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bank Account -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Compte bancaire</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label for="iban" class="form-label">IBAN</label>
                            <input
                                type="text"
                                id="iban"
                                name="iban"
                                value="{{ old('iban') }}"
                                class="form-input font-mono @error('iban') form-input-error @enderror"
                                placeholder="BE00 0000 0000 0000"
                            >
                            @error('iban')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bic" class="form-label">BIC</label>
                            <input
                                type="text"
                                id="bic"
                                name="bic"
                                value="{{ old('bic') }}"
                                class="form-input font-mono"
                                placeholder="GEBABEBB"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function partnerForm() {
            return {
                vatNumber: '{{ old('vat_number') }}',
                vatLoading: false,
                vatValid: null,
                peppolCapable: false,
                peppolIdentifier: '{{ old('peppol_identifier') }}',

                async checkVatNumber() {
                    if (!this.vatNumber || this.vatNumber.length < 10) {
                        this.vatValid = null;
                        this.peppolCapable = false;
                        return;
                    }

                    this.vatLoading = true;

                    try {
                        const response = await fetch(`/api/vat/validate?vat_number=${encodeURIComponent(this.vatNumber)}`);
                        const data = await response.json();

                        this.vatValid = data.valid;
                        this.peppolCapable = data.peppol_capable || false;

                        if (data.peppol_identifier && !this.peppolIdentifier) {
                            this.peppolIdentifier = data.peppol_identifier;
                        }

                        // Auto-fill company data if available
                        if (data.company_name && !document.getElementById('name').value) {
                            document.getElementById('name').value = data.company_name;
                        }
                        if (data.address) {
                            if (data.address.street && !document.getElementById('street').value) {
                                document.getElementById('street').value = data.address.street;
                            }
                            if (data.address.postal_code && !document.getElementById('postal_code').value) {
                                document.getElementById('postal_code').value = data.address.postal_code;
                            }
                            if (data.address.city && !document.getElementById('city').value) {
                                document.getElementById('city').value = data.address.city;
                            }
                        }
                    } catch (error) {
                        console.error('VAT validation error:', error);
                        this.vatValid = null;
                    } finally {
                        this.vatLoading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
