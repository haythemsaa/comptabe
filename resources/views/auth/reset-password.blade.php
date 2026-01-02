<x-guest-layout>
    <x-slot name="title">Réinitialiser le mot de passe</x-slot>

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

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary-500/10 dark:bg-primary-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouveau mot de passe</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                Choisissez un nouveau mot de passe sécurisé pour votre compte
            </p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-6" x-data="passwordReset()">
            @csrf

            <!-- Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email -->
            <div>
                <label for="email" class="form-label">Adresse email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="form-input pl-10 @error('email') form-input-error @enderror"
                        placeholder="vous@entreprise.be"
                    >
                </div>
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        x-model="password"
                        @input="checkPasswordStrength"
                        class="form-input pl-10 pr-12 @error('password') form-input-error @enderror"
                        placeholder="Minimum 8 caractères"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>

                <!-- Password Strength Indicator -->
                <div class="mt-2" x-show="password.length > 0" x-cloak>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex-1 h-2 bg-secondary-200 dark:bg-secondary-700 rounded-full overflow-hidden">
                            <div
                                class="h-full transition-all duration-300"
                                :class="{
                                    'bg-danger-500 w-1/4': strength === 'weak',
                                    'bg-warning-500 w-2/4': strength === 'medium',
                                    'bg-success-500 w-3/4': strength === 'strong',
                                    'bg-success-600 w-full': strength === 'very-strong'
                                }"
                            ></div>
                        </div>
                        <span class="text-xs font-medium" :class="{
                            'text-danger-500': strength === 'weak',
                            'text-warning-500': strength === 'medium',
                            'text-success-500': strength === 'strong',
                            'text-success-600': strength === 'very-strong'
                        }" x-text="strengthLabel"></span>
                    </div>

                    <!-- Password Requirements -->
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-2" :class="password.length >= 8 ? 'text-success-600' : 'text-secondary-500'">
                            <svg class="w-4 h-4" :class="password.length >= 8 ? 'opacity-100' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Au moins 8 caractères</span>
                        </div>
                        <div class="flex items-center gap-2" :class="hasUpperCase ? 'text-success-600' : 'text-secondary-500'">
                            <svg class="w-4 h-4" :class="hasUpperCase ? 'opacity-100' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Une lettre majuscule</span>
                        </div>
                        <div class="flex items-center gap-2" :class="hasLowerCase ? 'text-success-600' : 'text-secondary-500'">
                            <svg class="w-4 h-4" :class="hasLowerCase ? 'opacity-100' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Une lettre minuscule</span>
                        </div>
                        <div class="flex items-center gap-2" :class="hasNumber ? 'text-success-600' : 'text-secondary-500'">
                            <svg class="w-4 h-4" :class="hasNumber ? 'opacity-100' : 'opacity-30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Un chiffre</span>
                        </div>
                    </div>
                </div>

                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div>
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <input
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="form-input pl-10 pr-12"
                        placeholder="Retapez votre mot de passe"
                    >
                    <button
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                    >
                        <svg x-show="!showPasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPasswordConfirmation" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="btn-primary w-full justify-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Réinitialiser le mot de passe
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la connexion
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        function passwordReset() {
            return {
                password: '',
                showPassword: false,
                showPasswordConfirmation: false,
                strength: 'weak',
                strengthLabel: 'Faible',
                hasUpperCase: false,
                hasLowerCase: false,
                hasNumber: false,

                checkPasswordStrength() {
                    this.hasUpperCase = /[A-Z]/.test(this.password);
                    this.hasLowerCase = /[a-z]/.test(this.password);
                    this.hasNumber = /[0-9]/.test(this.password);
                    const hasSpecial = /[^A-Za-z0-9]/.test(this.password);

                    let score = 0;
                    if (this.password.length >= 8) score++;
                    if (this.hasUpperCase) score++;
                    if (this.hasLowerCase) score++;
                    if (this.hasNumber) score++;
                    if (hasSpecial) score++;
                    if (this.password.length >= 12) score++;

                    if (score <= 2) {
                        this.strength = 'weak';
                        this.strengthLabel = 'Faible';
                    } else if (score <= 3) {
                        this.strength = 'medium';
                        this.strengthLabel = 'Moyen';
                    } else if (score <= 4) {
                        this.strength = 'strong';
                        this.strengthLabel = 'Fort';
                    } else {
                        this.strength = 'very-strong';
                        this.strengthLabel = 'Très fort';
                    }
                }
            }
        }
    </script>
    @endpush
</x-guest-layout>
