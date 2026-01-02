<x-guest-layout>
    <x-slot name="title">Créer une entreprise</x-slot>

    <div class="animate-fade-in-up">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Créer votre entreprise</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">Configurez votre entreprise pour commencer à utiliser ComptaBE</p>
        </div>

        <form method="POST" action="{{ route('companies.store') }}" class="space-y-5" x-data="{ vatNumber: '' }">
            @csrf

            <!-- Company Name -->
            <div>
                <label for="name" class="form-label">Nom de l'entreprise *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="form-input @error('name') form-input-error @enderror"
                    placeholder="Ma Société SPRL"
                >
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- VAT Number -->
            <div>
                <label for="vat_number" class="form-label">Numéro de TVA belge *</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-secondary-500 font-medium">BE</span>
                    <input
                        type="text"
                        id="vat_number"
                        name="vat_number"
                        value="{{ old('vat_number') }}"
                        required
                        x-model="vatNumber"
                        x-mask="9999.999.999"
                        class="form-input pl-10 font-mono @error('vat_number') form-input-error @enderror"
                        placeholder="0123.456.789"
                    >
                </div>
                <p class="form-helper">10 chiffres, format: BE0123.456.789</p>
                @error('vat_number')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Legal Form -->
            <div>
                <label for="legal_form" class="form-label">Forme juridique</label>
                <select name="legal_form" id="legal_form" class="form-select">
                    <option value="">Sélectionner...</option>
                    <option value="SRL" {{ old('legal_form') === 'SRL' ? 'selected' : '' }}>SRL (Société à responsabilité limitée)</option>
                    <option value="SA" {{ old('legal_form') === 'SA' ? 'selected' : '' }}>SA (Société anonyme)</option>
                    <option value="SC" {{ old('legal_form') === 'SC' ? 'selected' : '' }}>SC (Société coopérative)</option>
                    <option value="SNC" {{ old('legal_form') === 'SNC' ? 'selected' : '' }}>SNC (Société en nom collectif)</option>
                    <option value="SCS" {{ old('legal_form') === 'SCS' ? 'selected' : '' }}>SCS (Société en commandite simple)</option>
                    <option value="ASBL" {{ old('legal_form') === 'ASBL' ? 'selected' : '' }}>ASBL</option>
                    <option value="PP" {{ old('legal_form') === 'PP' ? 'selected' : '' }}>Personne physique</option>
                </select>
            </div>

            <!-- Address -->
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

            <!-- Contact -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-input"
                        placeholder="contact@entreprise.be"
                    >
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

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-full">
                Créer l'entreprise
            </button>
        </form>

        <!-- Back Link -->
        @if(auth()->user()->companies->count() > 0)
            <p class="mt-6 text-center">
                <a href="{{ route('tenant.select') }}" class="text-sm text-secondary-500 hover:text-secondary-700">
                    ← Retour à la sélection
                </a>
            </p>
        @endif
    </div>
</x-guest-layout>
