<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-secondary-900 via-secondary-800 to-secondary-900 flex flex-col items-center justify-center py-12 px-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-500/20 mb-4">
                    <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Choisissez votre plan</h1>
                <p class="text-secondary-400">Commencez avec un essai gratuit ou sélectionnez le plan adapté à vos besoins</p>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                @foreach($plans as $plan)
                    <div class="bg-secondary-800 rounded-xl border {{ $plan->is_featured ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-secondary-700' }} overflow-hidden relative">
                        @if($plan->is_featured)
                            <div class="absolute top-0 right-0 bg-primary-500 text-xs px-3 py-1 rounded-bl-lg font-medium text-white">
                                Recommandé
                            </div>
                        @endif

                        <div class="p-6">
                            <h3 class="text-xl font-bold text-white mb-2">{{ $plan->name }}</h3>

                            <div class="mb-4">
                                @if($plan->isFree())
                                    <div class="text-3xl font-bold text-white">Gratuit</div>
                                @else
                                    <div class="text-3xl font-bold text-white">{{ number_format($plan->price_monthly, 2) }} €</div>
                                    <div class="text-secondary-400 text-sm">/mois HTVA</div>
                                    @if($plan->yearly_discount > 0)
                                        <div class="text-success-400 text-xs mt-1">
                                            {{ number_format($plan->price_yearly, 2) }} €/an (-{{ $plan->yearly_discount }}%)
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <p class="text-secondary-400 text-sm mb-4">{{ $plan->description }}</p>

                            <!-- Limits -->
                            <ul class="space-y-2 text-sm mb-6">
                                <li class="flex items-center gap-2 text-secondary-300">
                                    <svg class="w-4 h-4 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $plan->getLimitLabel('max_users') }} utilisateurs
                                </li>
                                <li class="flex items-center gap-2 text-secondary-300">
                                    <svg class="w-4 h-4 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $plan->getLimitLabel('max_invoices_per_month') }} factures/mois
                                </li>
                                <li class="flex items-center gap-2 text-secondary-300">
                                    <svg class="w-4 h-4 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $plan->getLimitLabel('max_clients') }} clients
                                </li>
                            </ul>

                            <!-- CTA -->
                            @if($plan->isFree())
                                <form action="{{ route('subscription.select-plan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="billing_cycle" value="monthly">
                                    <button type="submit" class="w-full py-3 bg-secondary-700 hover:bg-secondary-600 text-white rounded-lg font-medium transition-colors">
                                        Commencer gratuitement
                                    </button>
                                </form>
                            @elseif($plan->trial_days > 0)
                                <form action="{{ route('subscription.start-trial') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full py-3 {{ $plan->is_featured ? 'bg-primary-500 hover:bg-primary-600' : 'bg-secondary-700 hover:bg-secondary-600' }} text-white rounded-lg font-medium transition-colors">
                                        Essai gratuit {{ $plan->trial_days }}j
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('subscription.select-plan') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="billing_cycle" value="monthly">
                                    <button type="submit" class="w-full py-3 {{ $plan->is_featured ? 'bg-primary-500 hover:bg-primary-600' : 'bg-secondary-700 hover:bg-secondary-600' }} text-white rounded-lg font-medium transition-colors">
                                        Sélectionner
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Footer -->
            <div class="text-center text-secondary-500 text-sm">
                <p>Tous les prix sont hors TVA. TVA belge de 21% applicable.</p>
                <p class="mt-2">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-primary-400 hover:text-primary-300">
                        Se déconnecter
                    </a>
                </p>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
