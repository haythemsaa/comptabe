<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-500 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="card">
                <div class="card-body space-y-6">
                    <!-- Header -->
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">
                            Vérification en deux étapes
                        </h2>
                        <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                            Entrez le code de votre application d'authentification
                        </p>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('2fa.verify') }}" class="space-y-4" x-data="{ useRecovery: false }">
                        @csrf

                        <div x-show="!useRecovery">
                            <label for="code" class="form-label">Code à 6 chiffres</label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="form-input text-center text-3xl tracking-[0.5em] font-mono @error('code') form-input-error @enderror"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   placeholder="······"
                                   autofocus>
                        </div>

                        <div x-show="useRecovery" x-cloak>
                            <label for="recovery_code" class="form-label">Code de récupération</label>
                            <input type="text"
                                   id="recovery_code"
                                   name="code"
                                   class="form-input text-center text-xl tracking-wider font-mono uppercase @error('code') form-input-error @enderror"
                                   maxlength="9"
                                   placeholder="XXXX-XXXX"
                                   x-bind:disabled="!useRecovery">
                        </div>

                        @error('code')
                            <p class="form-error text-center">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Vérifier
                        </button>

                        <div class="text-center">
                            <button type="button"
                                    @click="useRecovery = !useRecovery"
                                    class="text-sm text-primary-600 hover:text-primary-700">
                                <span x-show="!useRecovery">Utiliser un code de récupération</span>
                                <span x-show="useRecovery" x-cloak>Utiliser l'application d'authentification</span>
                            </button>
                        </div>
                    </form>

                    <!-- Back to login -->
                    <div class="text-center pt-4 border-t border-secondary-200 dark:border-secondary-700">
                        <a href="{{ route('login') }}" class="text-sm text-secondary-500 hover:text-secondary-700">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
