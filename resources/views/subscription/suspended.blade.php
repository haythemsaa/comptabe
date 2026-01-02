<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-lg w-full">
            <div class="bg-secondary-800 rounded-xl border border-warning-500/50 p-8 text-center">
                <!-- Warning Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-warning-500/20 mb-6">
                    <svg class="w-8 h-8 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-white mb-4">Abonnement suspendu</h1>

                <p class="text-secondary-400 mb-6">
                    Votre abonnement a été temporairement suspendu.
                    @if($subscription?->suspension_reason)
                        <br><span class="text-warning-400">Raison: {{ $subscription->suspension_reason }}</span>
                    @endif
                </p>

                <div class="space-y-4">
                    <p class="text-sm text-secondary-500">
                        Pour réactiver votre compte, veuillez régulariser votre situation ou contacter notre support.
                    </p>

                    <div class="flex flex-col gap-3">
                        <a href="mailto:support@comptabe.be" class="w-full py-3 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors">
                            Contacter le support
                        </a>

                        <a href="{{ route('subscription.upgrade') }}" class="w-full py-3 bg-secondary-700 hover:bg-secondary-600 text-white rounded-lg font-medium transition-colors">
                            Voir les options d'abonnement
                        </a>
                    </div>
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
