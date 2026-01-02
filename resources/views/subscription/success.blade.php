<x-app-layout>
    <x-slot name="title">Paiement Réussi</x-slot>

    <div class="max-w-2xl mx-auto py-12">
        <div class="bg-white dark:bg-secondary-800 rounded-xl shadow-sm p-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto w-16 h-16 bg-success-100 dark:bg-success-900/20 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white mb-4">
                Paiement Réussi !
            </h1>

            <p class="text-secondary-600 dark:text-secondary-400 mb-8">
                Votre abonnement a été activé avec succès.
            </p>

            @if($subscription)
            <div class="bg-secondary-50 dark:bg-secondary-900/50 rounded-lg p-6 mb-8 text-left">
                <h3 class="font-semibold text-secondary-900 dark:text-white mb-4">Détails de l'abonnement</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Plan</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">{{ $subscription->plan->name }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Montant</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">€{{ number_format($subscription->amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Cycle de facturation</span>
                        <span class="font-semibold text-secondary-900 dark:text-white capitalize">{{ $subscription->billing_cycle === 'monthly' ? 'Mensuel' : 'Annuel' }}</span>
                    </div>

                    @if($subscription->payment_provider)
                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Provider</span>
                        <span class="font-semibold text-secondary-900 dark:text-white capitalize">{{ $subscription->payment_provider }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Statut</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-success-100 text-success-800">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>

                    @if($subscription->next_payment_date)
                    <div class="flex justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Prochain paiement</span>
                        <span class="font-semibold text-secondary-900 dark:text-white">{{ $subscription->next_payment_date->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="flex gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    Accéder au Dashboard
                </a>
                <a href="{{ route('subscription.show') }}" class="btn btn-secondary">
                    Voir mon abonnement
                </a>
            </div>

            <p class="text-sm text-secondary-500 mt-6">
                Un email de confirmation vous a été envoyé.
            </p>
        </div>
    </div>
</x-app-layout>
