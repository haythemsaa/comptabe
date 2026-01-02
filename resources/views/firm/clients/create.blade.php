<x-firm-layout>
    <x-slot name="title">Ajouter un client</x-slot>
    <x-slot name="header">Nouveau client</x-slot>

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm">
        <ol class="inline-flex items-center space-x-1">
            <li>
                <a href="{{ route('firm.dashboard') }}" class="text-secondary-400 hover:text-white">Tableau de bord</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li>
                <a href="{{ route('firm.clients.index') }}" class="text-secondary-400 hover:text-white">Clients</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li class="text-white">Ajouter</li>
        </ol>
    </nav>

    <form action="{{ route('firm.clients.store') }}" method="POST" class="max-w-5xl" x-data="clientForm()">
        @csrf

        <!-- Company Information -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Informations de l'entreprise
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Company Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">
                        Nom de l'entreprise <span class="text-danger-400">*</span>
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Acme SPRL">
                    @error('company_name')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- VAT Number -->
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Numéro TVA <span class="text-danger-400">*</span>
                    </label>
                    <input type="text" name="company_vat" value="{{ old('company_vat') }}" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="BE0123456789"
                        x-on:blur="checkVatNumber($event.target.value)">
                    @error('company_vat')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                    <p x-show="vatChecking" class="mt-1 text-sm text-secondary-400">
                        <span class="inline-flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Vérification du numéro TVA...
                        </span>
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Email <span class="text-danger-400">*</span>
                    </label>
                    <input type="email" name="company_email" value="{{ old('company_email') }}" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="contact@acme.be">
                    @error('company_email')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium mb-2">Téléphone</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone') }}"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="+32 2 123 45 67">
                    @error('company_phone')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Street + Number -->
                <div>
                    <label class="block text-sm font-medium mb-2">Rue et numéro</label>
                    <div class="flex gap-2">
                        <input type="text" name="company_street" value="{{ old('company_street') }}"
                            class="flex-1 bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Rue de la Loi">
                        <input type="text" name="company_house_number" value="{{ old('company_house_number') }}"
                            class="w-20 bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="42">
                    </div>
                </div>

                <!-- Postal Code + City -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Code postal et ville</label>
                    <div class="flex gap-2">
                        <input type="text" name="company_postal_code" value="{{ old('company_postal_code') }}"
                            class="w-32 bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="1000">
                        <input type="text" name="company_city" value="{{ old('company_city') }}"
                            class="flex-1 bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Bruxelles">
                    </div>
                </div>
            </div>
        </div>

        <!-- Mandate Configuration -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Configuration du mandat
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Mandate Type -->
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Type de mandat <span class="text-danger-400">*</span>
                    </label>
                    <select name="mandate_type" required
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sélectionnez un type</option>
                        @foreach(\App\Models\ClientMandate::TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}" {{ old('mandate_type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('mandate_type')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Manager -->
                <div>
                    <label class="block text-sm font-medium mb-2">Gestionnaire du dossier</label>
                    <select name="manager_user_id"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Attribuer plus tard</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('manager_user_id') == $user->id || ($loop->first && !old('manager_user_id')) ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_user_id')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Services inclus
            </h3>

            <div class="grid md:grid-cols-2 gap-4">
                @foreach(\App\Models\ClientMandate::DEFAULT_SERVICES as $service => $defaultValue)
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer transition-colors">
                        <input type="checkbox" name="services[{{ $service }}]" value="1"
                            {{ old("services.{$service}", $defaultValue) ? 'checked' : '' }}
                            class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800">
                        <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $service)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Billing Configuration -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Facturation
            </h3>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Billing Type -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">
                        Type de facturation <span class="text-danger-400">*</span>
                    </label>
                    <select name="billing_type" required x-model="billingType"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sélectionnez un type</option>
                        <option value="hourly">Horaire</option>
                        <option value="monthly">Mensuelle (forfait)</option>
                        <option value="annual">Annuelle (forfait)</option>
                        <option value="project">Par projet</option>
                    </select>
                    @error('billing_type')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hourly Rate -->
                <div x-show="billingType === 'hourly'">
                    <label class="block text-sm font-medium mb-2">Taux horaire (€)</label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate') }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="75.00">
                    @error('hourly_rate')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monthly Fee -->
                <div x-show="billingType === 'monthly'">
                    <label class="block text-sm font-medium mb-2">Forfait mensuel (€)</label>
                    <input type="number" name="monthly_fee" value="{{ old('monthly_fee') }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="500.00">
                    @error('monthly_fee')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Annual Fee -->
                <div x-show="billingType === 'annual'" class="md:col-span-2">
                    <label class="block text-sm font-medium mb-2">Forfait annuel (€)</label>
                    <input type="number" name="annual_fee" value="{{ old('annual_fee') }}" step="0.01" min="0"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="5000.00">
                    @error('annual_fee')
                        <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('firm.clients.index') }}"
                class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                Annuler
            </a>
            <button type="submit"
                class="px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Ajouter le client
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        function clientForm() {
            return {
                billingType: '{{ old('billing_type', '') }}',
                vatChecking: false,

                async checkVatNumber(vatNumber) {
                    // Clean VAT number
                    const cleanVat = vatNumber.replace(/[^A-Z0-9]/g, '');

                    if (cleanVat.length < 10) return;

                    this.vatChecking = true;

                    try {
                        // Call VIES validation API (if implemented)
                        const response = await fetch(`/api/partners/validate-vat?vat=${cleanVat}`);
                        const data = await response.json();

                        if (data.valid) {
                            console.log('VAT number valid:', data);
                            // Could auto-fill company name if available
                        }
                    } catch (error) {
                        console.error('VAT validation error:', error);
                    } finally {
                        this.vatChecking = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-firm-layout>
