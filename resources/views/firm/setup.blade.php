<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Creer votre cabinet</h1>
                <p class="text-secondary-400">Configurez votre cabinet comptable pour commencer a gerer vos clients</p>
            </div>

            <!-- Form -->
            <div class="bg-secondary-800 rounded-2xl border border-secondary-700 p-8">
                <form action="{{ route('firm.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Cabinet Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Informations du cabinet
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-secondary-300 mb-1">Nom du cabinet *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Cabinet Dupont & Associes">
                                @error('name')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="itaa_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero ITAA</label>
                                <input type="text" id="itaa_number" name="itaa_number" value="{{ old('itaa_number') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="123456">
                                @error('itaa_number')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ire_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero IRE</label>
                                <input type="text" id="ire_number" name="ire_number" value="{{ old('ire_number') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="B00123">
                                @error('ire_number')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="vat_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero TVA</label>
                                <input type="text" id="vat_number" name="vat_number" value="{{ old('vat_number') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="BE0123456789">
                                @error('vat_number')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-secondary-300 mb-1">Email *</label>
                                <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="contact@cabinet.be">
                                @error('email')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-secondary-300 mb-1">Telephone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="+32 2 123 45 67">
                                @error('phone')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Adresse
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-3">
                                <label for="street" class="block text-sm font-medium text-secondary-300 mb-1">Rue</label>
                                <input type="text" id="street" name="street" value="{{ old('street') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Rue de la Loi">
                            </div>

                            <div>
                                <label for="house_number" class="block text-sm font-medium text-secondary-300 mb-1">Numero</label>
                                <input type="text" id="house_number" name="house_number" value="{{ old('house_number') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="123">
                            </div>

                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-secondary-300 mb-1">Code postal</label>
                                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="1000">
                            </div>

                            <div class="md:col-span-3">
                                <label for="city" class="block text-sm font-medium text-secondary-300 mb-1">Ville</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Bruxelles">
                            </div>
                        </div>
                    </div>

                    <!-- Trial Info -->
                    <div class="bg-primary-500/10 border border-primary-500/30 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Essai gratuit de 30 jours</h3>
                                <p class="text-sm text-secondary-400 mt-1">
                                    Votre cabinet beneficiera d'un essai gratuit de 30 jours avec toutes les fonctionnalites.
                                    Aucune carte de credit requise.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('dashboard') }}" class="text-secondary-400 hover:text-white transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Creer mon cabinet
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-secondary-500 text-sm mt-6">
                En creant votre cabinet, vous acceptez nos <a href="#" class="text-primary-400 hover:underline">conditions d'utilisation</a>
            </p>
        </div>
    </div>
</x-guest-layout>
