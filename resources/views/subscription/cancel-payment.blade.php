<x-app-layout>
    <x-slot name="title">Paiement Annulé</x-slot>

    <div class="max-w-2xl mx-auto py-12">
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm p-8 text-center">
            <!-- Warning Icon -->
            <div class="mx-auto w-16 h-16 bg-warning-100 dark:bg-warning-900/20 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white mb-4">
                Paiement Annulé
            </h1>

            <p class="text-secondary-600 dark:text-secondary-400 mb-8">
                Vous avez annulé le processus de paiement. Aucun montant n'a été débité.
            </p>

            <div class="bg-secondary-50 dark:bg-secondary-900/50 rounded-lg p-6 mb-8">
                <h3 class="font-semibold text-secondary-900 dark:text-white mb-2">Que souhaitez-vous faire ?</h3>
                <ul class="text-left space-y-2 text-secondary-600 dark:text-secondary-400">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Réessayer le paiement avec un autre moyen de paiement
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Choisir un autre plan d'abonnement
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Contacter le support si vous rencontrez des difficultés
                    </li>
                </ul>
            </div>

            <div class="flex gap-4 justify-center flex-wrap">
                <a href="{{ route('subscription.payment') }}" class="btn btn-primary">
                    Réessayer le Paiement
                </a>
                <a href="{{ route('subscription.upgrade') }}" class="btn btn-secondary">
                    Choisir un Autre Plan
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline">
                    Retour au Dashboard
                </a>
            </div>

            <p class="text-sm text-secondary-500 mt-6">
                Besoin d'aide ? <a href="mailto:support@comptabe.com" class="text-primary-600 hover:underline">Contactez notre support</a>
            </p>
        </div>
    </div>
</x-app-layout>
