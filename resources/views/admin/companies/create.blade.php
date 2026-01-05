<x-admin-layout>
    <x-slot name="title">Nouvelle Entreprise</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.companies.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <span>Nouvelle Entreprise</span>
        </div>
    </x-slot>

    <form action="{{ route('admin.companies.store') }}" method="POST" class="space-y-6" x-data="{ country: 'BE' }">
        @csrf

        @if(session('error'))
            <div class="bg-danger-500/20 border border-danger-500 text-danger-400 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Company Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">Informations Entreprise</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Nom de l'entreprise *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                            @error('name') <span class="text-danger-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Pays *</label>
                            <select name="country_code" x-model="country" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                                @foreach($countries as $code => $name)
                                    <option value="{{ $code }}" {{ old('country_code', 'BE') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">N° TVA</label>
                            <input type="text" name="vat_number" value="{{ old('vat_number') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500"
                                placeholder="BE0123456789">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Site web</label>
                            <input type="url" name="website" value="{{ old('website') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500"
                                placeholder="https://example.com">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">Adresse</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Rue</label>
                            <input type="text" name="street" value="{{ old('street') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Code postal</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Ville</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <!-- Country Specific Fields -->
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6" x-show="country === 'TN'" x-cloak>
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">
                        <span class="flex items-center gap-2">
                            <span class="fi fi-tn"></span>
                            Informations Tunisie
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Matricule Fiscal</label>
                            <input type="text" name="matricule_fiscal" value="{{ old('matricule_fiscal') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500"
                                placeholder="1234567ABC000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">N° Employeur CNSS</label>
                            <input type="text" name="cnss_employer_number" value="{{ old('cnss_employer_number') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6" x-show="country === 'FR'" x-cloak>
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">
                        <span class="flex items-center gap-2">
                            <span class="fi fi-fr"></span>
                            Informations France
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">SIRET</label>
                            <input type="text" name="siret" value="{{ old('siret') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">SIREN</label>
                            <input type="text" name="siren" value="{{ old('siren') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Code APE</label>
                            <input type="text" name="ape_code" value="{{ old('ape_code') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">N° URSSAF</label>
                            <input type="text" name="urssaf_number" value="{{ old('urssaf_number') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Convention Collective</label>
                            <input type="text" name="convention_collective" value="{{ old('convention_collective') }}"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                </div>

                <!-- Owner User -->
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">Propriétaire (Utilisateur)</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Prénom *</label>
                            <input type="text" name="owner_first_name" value="{{ old('owner_first_name') }}" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Nom *</label>
                            <input type="text" name="owner_last_name" value="{{ old('owner_last_name') }}" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Email *</label>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-secondary-500 mt-1">Si l'email existe déjà, l'utilisateur sera associé à cette entreprise.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Mot de passe *</label>
                            <input type="password" name="owner_password" required
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-secondary-500 mt-1">Minimum 8 caractères. Ignoré si l'utilisateur existe.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Modules & Subscription -->
            <div class="space-y-6">
                <!-- Subscription -->
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">Abonnement</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Plan</label>
                            <select name="subscription_plan_id"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                                <option value="">-- Pas d'abonnement --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} - {{ number_format($plan->price, 2) }} EUR/mois</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-300 mb-2">Jours d'essai</label>
                            <input type="number" name="trial_days" value="{{ old('trial_days', 14) }}" min="0" max="90"
                                class="w-full bg-secondary-700 border border-secondary-600 rounded-xl px-4 py-3 text-white focus:border-primary-500 focus:ring-primary-500">
                            <p class="text-xs text-secondary-500 mt-1">0 = activation immédiate</p>
                        </div>
                    </div>
                </div>

                <!-- Modules -->
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold text-lg mb-6 pb-4 border-b border-secondary-700">Modules</h3>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @php $currentCategory = null; @endphp
                        @foreach($modules as $module)
                            @if($module->category !== $currentCategory)
                                @php $currentCategory = $module->category; @endphp
                                <div class="text-xs font-semibold text-secondary-500 uppercase tracking-wider mt-4 first:mt-0">
                                    {{ $module->category }}
                                </div>
                            @endif
                            <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-secondary-700 cursor-pointer {{ $module->is_core ? 'opacity-50' : '' }}">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                                    {{ $module->is_core ? 'checked disabled' : '' }}
                                    class="w-4 h-4 rounded border-secondary-600 text-primary-500 focus:ring-primary-500">
                                <div class="flex-1">
                                    <div class="font-medium text-sm">{{ $module->name }}</div>
                                    @if($module->is_core)
                                        <span class="text-xs text-success-400">Module de base (inclus)</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-secondary-500 mt-4">Les modules de base sont automatiquement inclus.</p>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.companies.index') }}" class="px-6 py-3 bg-secondary-700 text-white rounded-xl hover:bg-secondary-600 transition">
                Annuler
            </a>
            <button type="submit" class="px-6 py-3 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition">
                Créer l'entreprise
            </button>
        </div>
    </form>
</x-admin-layout>
