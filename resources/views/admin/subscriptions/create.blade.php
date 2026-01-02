<x-admin-layout>
    <x-slot name="title">Créer un abonnement - {{ $company->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.companies.show', $company) }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Créer un abonnement</span>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.subscriptions.store', $company) }}" method="POST">
            @csrf

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">{{ $company->name }}</h2>
                    <p class="text-secondary-400 text-sm">{{ $company->vat_number }}</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Plan Selection -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-300 mb-3">Sélectionner un plan</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($plans as $plan)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="plan_id" value="{{ $plan->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                                    <div class="p-4 border border-secondary-600 rounded-lg peer-checked:border-primary-500 peer-checked:ring-2 peer-checked:ring-primary-500/20 transition-all">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="text-white font-medium">{{ $plan->name }}</div>
                                                <div class="text-secondary-400 text-sm mt-1">
                                                    @if($plan->isFree())
                                                        Gratuit
                                                    @else
                                                        {{ number_format($plan->price_monthly, 2) }} €/mois
                                                    @endif
                                                </div>
                                            </div>
                                            @if($plan->is_featured)
                                                <span class="px-2 py-0.5 bg-primary-500/20 text-primary-400 text-xs rounded">Recommandé</span>
                                            @endif
                                        </div>
                                        <div class="mt-3 text-xs text-secondary-500">
                                            {{ $plan->getLimitLabel('max_users') }} utilisateurs,
                                            {{ $plan->getLimitLabel('max_invoices_per_month') }} factures/mois
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('plan_id')
                            <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Billing Cycle -->
                    <div>
                        <label class="block text-sm font-medium text-secondary-300 mb-3">Cycle de facturation</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="billing_cycle" value="monthly" checked class="text-primary-500 focus:ring-primary-500">
                                <span class="text-white">Mensuel</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="billing_cycle" value="yearly" class="text-primary-500 focus:ring-primary-500">
                                <span class="text-white">Annuel</span>
                                <span class="text-success-400 text-xs">(-20%)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Start Trial -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="start_trial" value="1" checked class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Commencer par une période d'essai</span>
                                <p class="text-secondary-400 text-sm">La durée dépend du plan sélectionné</p>
                            </div>
                        </label>
                    </div>

                    @if($company->subscription)
                        <div class="p-4 bg-warning-500/10 border border-warning-500/30 rounded-lg">
                            <div class="flex items-center gap-2 text-warning-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span class="font-medium">Attention</span>
                            </div>
                            <p class="text-secondary-400 text-sm mt-1">
                                Cette entreprise a déjà un abonnement ({{ $company->subscription->plan->name }} - {{ $company->subscription->status_label }}).
                                La création d'un nouvel abonnement annulera l'abonnement existant.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="p-6 border-t border-secondary-700 flex items-center justify-between">
                    <a href="{{ route('admin.companies.show', $company) }}" class="text-secondary-400 hover:text-white">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                        Créer l'abonnement
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
