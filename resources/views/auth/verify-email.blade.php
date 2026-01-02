<x-guest-layout>
    <x-slot name="title">Vérification email</x-slot>

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
            <div class="w-20 h-20 bg-primary-500/10 dark:bg-primary-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 relative">
                <svg class="w-10 h-10 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <div class="absolute -top-1 -right-1 w-6 h-6 bg-warning-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white">Vérifiez votre email</h2>
            <p class="mt-2 text-secondary-600 dark:text-secondary-400">
                Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer ?
            </p>
        </div>

        <!-- Success Message -->
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 rounded-lg bg-success-500/10 border border-success-500/20 animate-fade-in">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium text-success-700 dark:text-success-400">Email envoyé !</p>
                        <p class="text-sm text-success-600 dark:text-success-400 mt-1">
                            Un nouveau lien de vérification a été envoyé à votre adresse email.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Email Address Info -->
        <div class="mb-6 p-4 rounded-lg bg-secondary-50 dark:bg-secondary-800/50 border border-secondary-200 dark:border-secondary-700">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-secondary-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Email envoyé à :</p>
                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1 font-mono">
                        {{ auth()->user()->email }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-4">
            <!-- Resend Verification Email -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button
                    type="submit"
                    class="btn-primary w-full justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Renvoyer l'email de vérification
                </button>
            </form>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-secondary-100 dark:bg-secondary-700 hover:bg-secondary-200 dark:hover:bg-secondary-600 rounded-lg text-secondary-700 dark:text-secondary-300 font-medium transition-colors inline-flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Se déconnecter
                </button>
            </form>
        </div>

        <!-- Help Section -->
        <div class="mt-8 p-5 rounded-lg bg-secondary-50 dark:bg-secondary-800/50 border border-secondary-200 dark:border-secondary-700">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-secondary-900 dark:text-white mb-2">Vous ne recevez pas l'email ?</h3>
                    <ul class="space-y-2 text-sm text-secondary-600 dark:text-secondary-400">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span>Vérifiez votre dossier <strong>spam</strong> ou <strong>courrier indésirable</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span>Assurez-vous que l'adresse email ci-dessus est correcte</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span>Attendez quelques minutes - l'email peut prendre du temps à arriver</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span>Ajoutez <span class="font-mono text-xs bg-secondary-200 dark:bg-secondary-700 px-1.5 py-0.5 rounded">noreply@comptabe.be</span> à vos contacts</span>
                        </li>
                    </ul>

                    <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                            Toujours des problèmes ?
                            <a href="mailto:support@comptabe.be" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                                Contactez le support
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 p-4 rounded-lg bg-warning-500/10 border border-warning-500/20">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm">
                    <p class="font-medium text-warning-700 dark:text-warning-400">Note de sécurité</p>
                    <p class="text-warning-600 dark:text-warning-400 mt-1">
                        Pour votre sécurité, vous devez vérifier votre email avant d'accéder à votre compte. Le lien de vérification expire après 60 minutes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
