<x-admin-layout>
    <x-slot name="title">Plans d'abonnement</x-slot>
    <x-slot name="header">Plans d'abonnement</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="text-secondary-400 text-sm">Plans actifs</div>
            <div class="text-3xl font-bold text-white mt-1">{{ $stats['active'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="text-secondary-400 text-sm">Total plans</div>
            <div class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="text-secondary-400 text-sm">Abonnés actifs</div>
            <div class="text-3xl font-bold text-primary-400 mt-1">{{ $stats['subscribers'] }}</div>
        </div>
    </div>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold">Liste des plans</h2>
        <a href="{{ route('admin.subscription-plans.create') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
            + Nouveau plan
        </a>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $plan)
            <div class="bg-secondary-800 rounded-xl border {{ $plan->is_featured ? 'border-primary-500' : 'border-secondary-700' }} overflow-hidden relative">
                @if($plan->is_featured)
                    <div class="absolute top-0 right-0 bg-primary-500 text-xs px-3 py-1 rounded-bl-lg font-medium">
                        Recommandé
                    </div>
                @endif

                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">{{ $plan->name }}</h3>
                        @if(!$plan->is_active)
                            <span class="px-2 py-1 text-xs bg-secondary-700 text-secondary-400 rounded">Inactif</span>
                        @endif
                    </div>

                    <div class="mb-4">
                        @if($plan->isFree())
                            <div class="text-3xl font-bold text-white">Gratuit</div>
                        @else
                            <div class="text-3xl font-bold text-white">{{ number_format($plan->price_monthly, 2) }} €</div>
                            <div class="text-secondary-400 text-sm">/mois</div>
                            @if($plan->yearly_discount > 0)
                                <div class="text-success-400 text-xs mt-1">
                                    {{ number_format($plan->price_yearly, 2) }} €/an (-{{ $plan->yearly_discount }}%)
                                </div>
                            @endif
                        @endif
                    </div>

                    <p class="text-secondary-400 text-sm mb-4">{{ $plan->description }}</p>

                    <!-- Limits -->
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Utilisateurs</span>
                            <span class="text-white">{{ $plan->getLimitLabel('max_users') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Factures/mois</span>
                            <span class="text-white">{{ $plan->getLimitLabel('max_invoices_per_month') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Clients</span>
                            <span class="text-white">{{ $plan->getLimitLabel('max_clients') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary-400">Stockage</span>
                            <span class="text-white">{{ $plan->max_storage_mb >= 1000 ? ($plan->max_storage_mb / 1000) . ' GB' : $plan->max_storage_mb . ' MB' }}</span>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="border-t border-secondary-700 pt-4 mb-4">
                        <div class="text-xs text-secondary-400 mb-2">Fonctionnalités</div>
                        <div class="flex flex-wrap gap-1">
                            @if($plan->feature_peppol)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Peppol</span>
                            @endif
                            @if($plan->feature_recurring_invoices)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Récurrent</span>
                            @endif
                            @if($plan->feature_quotes)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Devis</span>
                            @endif
                            @if($plan->feature_api_access)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">API</span>
                            @endif
                            @if($plan->feature_priority_support)
                                <span class="px-2 py-0.5 bg-success-500/20 text-success-400 text-xs rounded">Support VIP</span>
                            @endif
                        </div>
                    </div>

                    <!-- Trial -->
                    @if($plan->trial_days > 0)
                        <div class="text-xs text-secondary-400 mb-4">
                            {{ $plan->trial_days }} jours d'essai gratuit
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="flex-1 px-3 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-center text-sm transition-colors">
                            Modifier
                        </a>
                        <form action="{{ route('admin.subscription-plans.toggle-active', $plan) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-2 {{ $plan->is_active ? 'bg-warning-500/20 text-warning-400 hover:bg-warning-500/30' : 'bg-success-500/20 text-success-400 hover:bg-success-500/30' }} rounded-lg text-sm transition-colors">
                                {{ $plan->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                    </div>

                    <!-- Subscribers count -->
                    <div class="mt-4 pt-4 border-t border-secondary-700 text-center">
                        <span class="text-secondary-400 text-sm">{{ $plan->subscriptions()->whereNotIn('status', ['cancelled', 'expired'])->count() }} abonnés</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-admin-layout>
