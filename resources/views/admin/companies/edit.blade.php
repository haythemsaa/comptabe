<x-admin-layout>
    <x-slot name="title">Modifier {{ $company->name }}</x-slot>
    <x-slot name="header">Modifier l'Entreprise</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.companies.update', $company) }}" method="POST" class="space-y-6" x-data="{ countryCode: '{{ old('country_code', $company->country_code ?? 'BE') }}' }">
            @csrf
            @method('PUT')

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Informations Générales</h2>

                <div>
                    <label for="name" class="block text-sm font-medium text-secondary-300 mb-2">Nom de l'entreprise</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" required>
                    @error('name')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="vat_number" class="block text-sm font-medium text-secondary-300 mb-2">Numéro de TVA</label>
                    <input type="text" name="vat_number" id="vat_number" value="{{ old('vat_number', $company->vat_number) }}" placeholder="BE0123456789" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                    @error('vat_number')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-secondary-300 mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('email')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-secondary-300 mb-2">Téléphone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="website" class="block text-sm font-medium text-secondary-300 mb-2">Site Web</label>
                    <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}" placeholder="https://" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                    @error('website')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Adresse</h2>

                <div>
                    <label for="street" class="block text-sm font-medium text-secondary-300 mb-2">Rue</label>
                    <input type="text" name="street" id="street" value="{{ old('street', $company->street) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                    @error('street')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-secondary-300 mb-2">Code Postal</label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $company->postal_code) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <label for="city" class="block text-sm font-medium text-secondary-300 mb-2">Ville</label>
                        <input type="text" name="city" id="city" value="{{ old('city', $company->city) }}" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        @error('city')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="country_code" class="block text-sm font-medium text-secondary-300 mb-2">Pays</label>
                    <select name="country_code" id="country_code" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" x-model="countryCode">
                        <option value="BE" {{ old('country_code', $company->country_code) === 'BE' ? 'selected' : '' }}>🇧🇪 Belgique</option>
                        <option value="TN" {{ old('country_code', $company->country_code) === 'TN' ? 'selected' : '' }}>🇹🇳 Tunisie</option>
                        <option value="FR" {{ old('country_code', $company->country_code) === 'FR' ? 'selected' : '' }}>🇫🇷 France</option>
                        <option value="NL" {{ old('country_code', $company->country_code) === 'NL' ? 'selected' : '' }}>🇳🇱 Pays-Bas</option>
                        <option value="LU" {{ old('country_code', $company->country_code) === 'LU' ? 'selected' : '' }}>🇱🇺 Luxembourg</option>
                        <option value="DE" {{ old('country_code', $company->country_code) === 'DE' ? 'selected' : '' }}>🇩🇪 Allemagne</option>
                    </select>
                    @error('country_code')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Champs spécifiques Tunisie -->
                <div x-show="countryCode === 'TN'" x-cloak class="space-y-4 p-4 bg-warning-500/10 border border-warning-500/30 rounded-lg">
                    <p class="text-sm font-medium text-warning-400 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Champs obligatoires pour la Tunisie
                    </p>

                    <div>
                        <label for="matricule_fiscal" class="block text-sm font-medium text-secondary-300 mb-2">
                            Matricule Fiscal
                            <span class="text-xs text-secondary-400">(ex: 1234567/A/M/000)</span>
                        </label>
                        <input type="text" name="matricule_fiscal" id="matricule_fiscal" value="{{ old('matricule_fiscal', $company->matricule_fiscal) }}" placeholder="1234567/A/M/000" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                        @error('matricule_fiscal')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cnss_employer_number" class="block text-sm font-medium text-secondary-300 mb-2">
                            Numéro Employeur CNSS
                            <span class="text-xs text-secondary-400">(Caisse Nationale de Sécurité Sociale)</span>
                        </label>
                        <input type="text" name="cnss_employer_number" id="cnss_employer_number" value="{{ old('cnss_employer_number', $company->cnss_employer_number) }}" placeholder="123456" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                        @error('cnss_employer_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Champs spécifiques France -->
                <div x-show="countryCode === 'FR'" x-cloak class="space-y-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <p class="text-sm font-medium text-blue-400 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Champs obligatoires pour la France
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="siret" class="block text-sm font-medium text-secondary-300 mb-2">
                                SIRET
                                <span class="text-xs text-secondary-400">(14 chiffres)</span>
                            </label>
                            <input type="text" name="siret" id="siret" value="{{ old('siret', $company->siret) }}" placeholder="12345678901234" maxlength="14" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                            @error('siret')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="siren" class="block text-sm font-medium text-secondary-300 mb-2">
                                SIREN
                                <span class="text-xs text-secondary-400">(9 chiffres)</span>
                            </label>
                            <input type="text" name="siren" id="siren" value="{{ old('siren', $company->siren) }}" placeholder="123456789" maxlength="9" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                            @error('siren')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ape_code" class="block text-sm font-medium text-secondary-300 mb-2">
                                Code APE/NAF
                                <span class="text-xs text-secondary-400">(ex: 6201Z)</span>
                            </label>
                            <input type="text" name="ape_code" id="ape_code" value="{{ old('ape_code', $company->ape_code) }}" placeholder="6201Z" maxlength="6" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                            @error('ape_code')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="urssaf_number" class="block text-sm font-medium text-secondary-300 mb-2">
                                Numéro URSSAF
                                <span class="text-xs text-secondary-400">(Numéro d'affiliation)</span>
                            </label>
                            <input type="text" name="urssaf_number" id="urssaf_number" value="{{ old('urssaf_number', $company->urssaf_number) }}" placeholder="Numéro URSSAF" maxlength="20" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                            @error('urssaf_number')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="convention_collective" class="block text-sm font-medium text-secondary-300 mb-2">
                                Convention Collective
                                <span class="text-xs text-secondary-400">(IDCC - ex: Syntec, Métallurgie, etc.)</span>
                            </label>
                            <input type="text" name="convention_collective" id="convention_collective" value="{{ old('convention_collective', $company->convention_collective) }}" placeholder="ex: IDCC 1486 - Bureaux d'études techniques" maxlength="100" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                            @error('convention_collective')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 space-y-6">
                <h2 class="text-lg font-semibold border-b border-secondary-700 pb-4">Configuration Peppol</h2>

                <div>
                    <label for="peppol_id" class="block text-sm font-medium text-secondary-300 mb-2">Peppol ID</label>
                    <input type="text" name="peppol_id" id="peppol_id" value="{{ old('peppol_id', $company->peppol_id) }}" placeholder="0208:0123456789" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-500 focus:border-primary-500 focus:ring-primary-500">
                    @error('peppol_id')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="peppol_provider" class="block text-sm font-medium text-secondary-300 mb-2">Provider Peppol</label>
                    <select name="peppol_provider" id="peppol_provider" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sélectionner un provider</option>
                        <option value="storecove" {{ old('peppol_provider', $company->peppol_provider) === 'storecove' ? 'selected' : '' }}>Storecove</option>
                        <option value="billit" {{ old('peppol_provider', $company->peppol_provider) === 'billit' ? 'selected' : '' }}>Billit</option>
                        <option value="unifiedpost" {{ old('peppol_provider', $company->peppol_provider) === 'unifiedpost' ? 'selected' : '' }}>Unifiedpost</option>
                        <option value="basware" {{ old('peppol_provider', $company->peppol_provider) === 'basware' ? 'selected' : '' }}>Basware</option>
                        <option value="avalara" {{ old('peppol_provider', $company->peppol_provider) === 'avalara' ? 'selected' : '' }}>Avalara</option>
                    </select>
                    @error('peppol_provider')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-3 p-4 bg-secondary-700 rounded-xl cursor-pointer">
                    <input type="checkbox" name="peppol_test_mode" value="1" {{ old('peppol_test_mode', $company->peppol_test_mode) ? 'checked' : '' }} class="text-warning-500 focus:ring-warning-500 bg-secondary-600 border-secondary-500 rounded">
                    <div>
                        <span class="font-medium">Mode Test</span>
                        <p class="text-sm text-secondary-400">Utiliser l'environnement de test Peppol</p>
                    </div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.companies.show', $company) }}" class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
