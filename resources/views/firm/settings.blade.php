<x-firm-layout>
    <x-slot name="title">Parametres</x-slot>
    <x-slot name="header">Parametres du cabinet</x-slot>

    <div class="max-w-5xl">
        <form action="{{ route('firm.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Cabinet Information -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Informations du cabinet
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-secondary-300 mb-1">Nom du cabinet *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $firm->name) }}" required
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Cabinet Dupont & Associes">
                        @error('name')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="itaa_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero ITAA</label>
                        <input type="text" id="itaa_number" name="itaa_number" value="{{ old('itaa_number', $firm->itaa_number) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="123456">
                        @error('itaa_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ire_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero IRE</label>
                        <input type="text" id="ire_number" name="ire_number" value="{{ old('ire_number', $firm->ire_number) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="B00123">
                        @error('ire_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="vat_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero TVA</label>
                        <input type="text" id="vat_number" name="vat_number" value="{{ old('vat_number', $firm->vat_number) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="BE0123456789">
                        @error('vat_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="enterprise_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero d'entreprise</label>
                        <input type="text" id="enterprise_number" name="enterprise_number" value="{{ old('enterprise_number', $firm->enterprise_number) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="0123.456.789">
                        @error('enterprise_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Coordonnees de contact
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-secondary-300 mb-1">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $firm->email) }}" required
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="contact@cabinet.be">
                        @error('email')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-secondary-300 mb-1">Telephone</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $firm->phone) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="+32 2 123 45 67">
                        @error('phone')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="website" class="block text-sm font-medium text-secondary-300 mb-1">Site web</label>
                        <input type="url" id="website" name="website" value="{{ old('website', $firm->website) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="https://www.cabinet-exemple.be">
                        @error('website')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Adresse
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="street" class="block text-sm font-medium text-secondary-300 mb-1">Rue</label>
                        <input type="text" id="street" name="street" value="{{ old('street', $firm->street) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Rue de la Loi">
                        @error('street')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="house_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero</label>
                        <input type="text" id="house_number" name="house_number" value="{{ old('house_number', $firm->house_number) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="123">
                        @error('house_number')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="box" class="block text-sm font-medium text-secondary-300 mb-1">Boite</label>
                        <input type="text" id="box" name="box" value="{{ old('box', $firm->box) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="A">
                        @error('box')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-secondary-300 mb-1">Code postal</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $firm->postal_code) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="1000">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="city" class="block text-sm font-medium text-secondary-300 mb-1">Ville</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $firm->city) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Bruxelles">
                        @error('city')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country_code" class="block text-sm font-medium text-secondary-300 mb-1">Pays</label>
                        <select id="country_code" name="country_code"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="BE" {{ old('country_code', $firm->country_code) === 'BE' ? 'selected' : '' }}>Belgique</option>
                            <option value="FR" {{ old('country_code', $firm->country_code) === 'FR' ? 'selected' : '' }}>France</option>
                            <option value="LU" {{ old('country_code', $firm->country_code) === 'LU' ? 'selected' : '' }}>Luxembourg</option>
                            <option value="NL" {{ old('country_code', $firm->country_code) === 'NL' ? 'selected' : '' }}>Pays-Bas</option>
                        </select>
                        @error('country_code')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Peppol Settings -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Parametres Peppol
                        </h2>
                        <p class="text-sm text-secondary-400 mt-1">Configuration de la facturation electronique Peppol</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="peppol_id" class="block text-sm font-medium text-secondary-300 mb-1">
                            Identifiant Peppol
                            <span class="text-xs text-secondary-500">(genere automatiquement depuis le numero TVA)</span>
                        </label>
                        <input type="text" id="peppol_id" name="peppol_id" value="{{ old('peppol_id', $firm->peppol_id ?? $firm->generatePeppolId()) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="0208:0123456789">
                        @error('peppol_id')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="peppol_provider" class="block text-sm font-medium text-secondary-300 mb-1">Fournisseur Peppol</label>
                        <select id="peppol_provider" name="peppol_provider"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="">-- Selectionner --</option>
                            <option value="hermes" {{ old('peppol_provider', $firm->peppol_provider) === 'hermes' ? 'selected' : '' }}>Hermes</option>
                            <option value="unifiedpost" {{ old('peppol_provider', $firm->peppol_provider) === 'unifiedpost' ? 'selected' : '' }}>Unifiedpost</option>
                            <option value="basware" {{ old('peppol_provider', $firm->peppol_provider) === 'basware' ? 'selected' : '' }}>Basware</option>
                            <option value="other" {{ old('peppol_provider', $firm->peppol_provider) === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('peppol_provider')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="peppol_test_mode" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="peppol_test_mode" name="peppol_test_mode" value="1"
                                {{ old('peppol_test_mode', $firm->peppol_test_mode) ? 'checked' : '' }}
                                class="bg-secondary-700 border-secondary-600 rounded text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800">
                            <span class="text-sm font-medium text-secondary-300">Mode test</span>
                        </label>
                        <p class="text-xs text-secondary-500 mt-1 ml-6">Utiliser l'environnement de test Peppol</p>
                        @error('peppol_test_mode')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="peppol_api_key" class="block text-sm font-medium text-secondary-300 mb-1">Cle API Peppol</label>
                        <input type="text" id="peppol_api_key" name="peppol_api_key" value="{{ old('peppol_api_key', $firm->peppol_api_key) }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Votre cle API">
                        @error('peppol_api_key')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="peppol_api_secret" class="block text-sm font-medium text-secondary-300 mb-1">Secret API Peppol</label>
                        <input type="password" id="peppol_api_secret" name="peppol_api_secret" value="{{ old('peppol_api_secret') }}"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="{{ $firm->peppol_api_secret ? '••••••••••••••••' : 'Votre secret API' }}">
                        @error('peppol_api_secret')
                            <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                        @enderror
                        @if($firm->peppol_api_secret)
                            <p class="text-xs text-secondary-500 mt-1">Laisser vide pour conserver le secret actuel</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 p-4 bg-primary-500/10 border border-primary-500/30 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-primary-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm text-secondary-300">
                            <p class="font-medium text-white">A propos de Peppol</p>
                            <p class="mt-1">Peppol est le reseau europeen de facturation electronique standardisee. Configurez vos parametres Peppol pour envoyer et recevoir des factures electroniques conformes.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('firm.dashboard') }}" class="px-6 py-3 text-secondary-400 hover:text-white transition-colors">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-firm-layout>
