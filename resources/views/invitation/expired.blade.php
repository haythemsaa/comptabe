<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-8">
                <!-- Warning Icon -->
                <div class="mx-auto w-16 h-16 bg-warning-100 dark:bg-warning-900/20 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-4">
                    Invitation Expirée
                </h1>

                <p class="text-secondary-400 mb-6">
                    Cette invitation n'est plus valide. Elle a peut-être expiré ou a déjà été utilisée.
                </p>

                <p class="text-secondary-400 mb-8">
                    Veuillez contacter la personne qui vous a invité pour demander une nouvelle invitation.
                </p>

                <a href="{{ route('login') }}" class="btn btn-primary">
                    Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
