<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-lg w-full">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-secondary-700">
                    <h1 class="text-xl font-bold text-white">Finaliser votre abonnement</h1>
                    <p class="text-secondary-400 text-sm mt-1">Plan {{ $plan->name }}</p>
                </div>

                <!-- Order Summary -->
                <div class="p-6 border-b border-secondary-700 bg-secondary-900/30">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-secondary-400">Plan</span>
                        <span class="text-white font-medium">{{ $plan->name }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-secondary-400">Cycle</span>
                        <span class="text-white">{{ $billingCycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-secondary-400">Prix HTVA</span>
                        <span class="text-white">{{ number_format($amount, 2) }} €</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-secondary-400">TVA (21%)</span>
                        <span class="text-white">{{ number_format($amount * 0.21, 2) }} €</span>
                    </div>
                    <div class="border-t border-secondary-700 pt-4 flex justify-between items-center">
                        <span class="text-white font-medium">Total TTC</span>
                        <span class="text-xl font-bold text-primary-400">{{ number_format($amount * 1.21, 2) }} €</span>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="{{ route('subscription.process-payment') }}" method="POST" class="p-6">
                    @csrf

                    <!-- Payment Provider Selection -->
                    <div class="mb-6">
                        <label class="block text-white font-medium mb-3">Choisir votre moyen de paiement</label>
                        <div class="space-y-3">
                            <!-- Mollie Option -->
                            <label class="flex items-center gap-3 p-4 border border-secondary-600 rounded-lg cursor-pointer hover:border-primary-500 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-500/10">
                                <input type="radio" name="payment_provider" value="mollie" class="text-primary-500 focus:ring-primary-500" checked>
                                <div class="flex-1">
                                    <div class="text-white font-medium">Mollie</div>
                                    <div class="text-secondary-400 text-sm">Bancontact, Carte bancaire, SEPA</div>
                                </div>
                                <div class="text-xs bg-success-500/20 text-success-400 px-2 py-1 rounded">Recommandé Belgique</div>
                            </label>

                            <!-- Stripe Option -->
                            <label class="flex items-center gap-3 p-4 border border-secondary-600 rounded-lg cursor-pointer hover:border-primary-500 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-500/10">
                                <input type="radio" name="payment_provider" value="stripe" class="text-primary-500 focus:ring-primary-500">
                                <div class="flex-1">
                                    <div class="text-white font-medium">Stripe</div>
                                    <div class="text-secondary-400 text-sm">Carte bancaire internationale</div>
                                </div>
                                <svg class="w-8 h-8 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Type Selection -->
                    <div class="mb-6">
                        <label class="block text-white font-medium mb-3">Type de paiement</label>
                        <div class="space-y-3">
                            <!-- One-time Payment -->
                            <label class="flex items-center gap-3 p-4 border border-secondary-600 rounded-lg cursor-pointer hover:border-primary-500 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-500/10">
                                <input type="radio" name="payment_type" value="onetime" class="text-primary-500 focus:ring-primary-500" checked>
                                <div class="flex-1">
                                    <div class="text-white font-medium">Paiement unique ({{ $billingCycle === 'yearly' ? 'Annuel' : 'Mensuel' }})</div>
                                    <div class="text-secondary-400 text-sm">Vous serez facturé manuellement à chaque période</div>
                                </div>
                            </label>

                            <!-- Recurring Payment -->
                            <label class="flex items-center gap-3 p-4 border border-secondary-600 rounded-lg cursor-pointer hover:border-primary-500 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-500/10">
                                <input type="radio" name="payment_type" value="recurring" class="text-primary-500 focus:ring-primary-500">
                                <div class="flex-1">
                                    <div class="text-white font-medium">Abonnement récurrent</div>
                                    <div class="text-secondary-400 text-sm">Renouvellement automatique chaque {{ $billingCycle === 'yearly' ? 'année' : 'mois' }}</div>
                                </div>
                                <div class="text-xs bg-primary-500/20 text-primary-400 px-2 py-1 rounded">Auto-renewal</div>
                            </label>
                        </div>
                    </div>

                    @if($plan->trial_days > 0)
                        <div class="bg-info-500/20 border border-info-500/30 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-2 text-info-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Essai gratuit de {{ $plan->trial_days }} jours</span>
                            </div>
                            <p class="text-secondary-400 text-sm mt-1">
                                Vous ne serez pas débité avant la fin de votre période d'essai.
                            </p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-danger-500/20 border border-danger-500/30 rounded-lg p-4 mb-6">
                            <div class="text-danger-400 text-sm">
                                @foreach($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-danger-500/20 border border-danger-500/30 rounded-lg p-4 mb-6">
                            <div class="text-danger-400 text-sm">
                                {{ session('error') }}
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="w-full py-3 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors">
                        Confirmer et payer
                    </button>

                    <p class="text-center text-secondary-500 text-xs mt-4">
                        Paiement sécurisé. En continuant, vous acceptez nos
                        <a href="#" class="text-primary-400 hover:text-primary-300">conditions générales</a>
                        et notre
                        <a href="#" class="text-primary-400 hover:text-primary-300">politique de confidentialité</a>.
                    </p>
                </form>

                <!-- Back link -->
                <div class="p-6 pt-0 text-center">
                    <a href="{{ route('subscription.upgrade') }}" class="text-secondary-400 hover:text-white text-sm">
                        &larr; Retour au choix du plan
                    </a>
                </div>
            </div>

            <!-- Security notice -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center gap-2 text-secondary-500 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Paiement sécurisé SSL</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
