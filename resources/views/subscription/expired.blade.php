<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-lg w-full">
            <div class="bg-secondary-800 rounded-xl border border-danger-500/50 p-8 text-center">
                <!-- Expired Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-danger-500/20 mb-6">
                    <svg class="w-8 h-8 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-4">Abonnement expiré</h1>

                <p class="text-secondary-400 mb-6">
                    @if($subscription?->status === 'cancelled')
                        Votre abonnement a été annulé.
                        @if($subscription->cancelled_at)
                            <br><span class="text-sm">Date d'annulation: {{ $subscription->cancelled_at->format('d/m/Y') }}</span>
                        @endif
                    @else
                        Votre période d'essai ou votre abonnement a expiré.
                    @endif
                </p>

                <div class="bg-secondary-900/50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-secondary-400">
                        Vos données sont conservées pendant 90 jours. Réactivez votre abonnement pour y accéder à nouveau.
                    </p>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('subscription.upgrade') }}" class="block w-full py-3 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors">
                        Réactiver mon abonnement
                    </a>

                    @if($plans->where('price_monthly', 0)->count() > 0)
                        <form action="{{ route('subscription.select-plan') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plans->where('price_monthly', 0)->first()->id }}">
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <button type="submit" class="w-full py-3 bg-secondary-700 hover:bg-secondary-600 text-white rounded-lg font-medium transition-colors">
                                Continuer avec le plan gratuit
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-8 pt-6 border-t border-secondary-700">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-secondary-500 hover:text-white text-sm">
                        Se déconnecter
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
