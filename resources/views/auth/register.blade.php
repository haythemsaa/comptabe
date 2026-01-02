<x-guest-layout>
    <x-slot name="title">Inscription</x-slot>

    <div class="animate-fade-in-up">
        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-secondary-900">ComptaBE</h1>
            </div>
        </div>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Créer un compte</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">Commencez gratuitement</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name fields -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="form-label">Prénom</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="{{ old('first_name') }}"
                        required
                        autofocus
                        class="form-input @error('first_name') form-input-error @enderror"
                        placeholder="Jean"
                    >
                    @error('first_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="form-label">Nom</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="{{ old('last_name') }}"
                        required
                        class="form-input @error('last_name') form-input-error @enderror"
                        placeholder="Dupont"
                    >
                    @error('last_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="form-label">Adresse email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    class="form-input @error('email') form-input-error @enderror"
                    placeholder="vous@entreprise.be"
                >
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label">Mot de passe</label>
                <div class="relative" x-data="{ show: false }">
                    <input
                        :type="show ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="form-input pr-12 @error('password') form-input-error @enderror"
                        placeholder="Minimum 8 caractères"
                    >
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                    >
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="form-input"
                    placeholder="Confirmer votre mot de passe"
                >
            </div>

            <!-- Terms -->
            <div class="flex items-start gap-3">
                <input type="checkbox" name="terms" required class="form-checkbox mt-0.5">
                <span class="text-sm text-secondary-600 dark:text-secondary-400">
                    J'accepte les <a href="#" class="text-primary-600 hover:underline">conditions d'utilisation</a>
                    et la <a href="#" class="text-primary-600 hover:underline">politique de confidentialité</a>
                </span>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-full">
                Créer mon compte
            </button>
        </form>

        <!-- Login Link -->
        <p class="mt-8 text-center text-sm text-secondary-600 dark:text-secondary-400">
            Déjà un compte?
            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                Se connecter
            </a>
        </p>
    </div>
</x-guest-layout>
