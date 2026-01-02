<x-admin-layout>
    <x-slot name="title">Abonnements</x-slot>
    <x-slot name="header">Gestion des abonnements</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">Total</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">En essai</div>
            <div class="text-2xl font-bold text-info-400 mt-1">{{ $stats['trialing'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">Actifs</div>
            <div class="text-2xl font-bold text-success-400 mt-1">{{ $stats['active'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">Impayés</div>
            <div class="text-2xl font-bold text-warning-400 mt-1">{{ $stats['past_due'] }}</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">MRR</div>
            <div class="text-2xl font-bold text-primary-400 mt-1">{{ number_format($stats['mrr'], 0) }} €</div>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="text-secondary-400 text-xs">ARR</div>
            <div class="text-2xl font-bold text-primary-400 mt-1">{{ number_format($stats['arr'], 0) }} €</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <a href="{{ route('admin.subscriptions.expiring-trials') }}" class="px-4 py-2 bg-info-500/20 text-info-400 hover:bg-info-500/30 rounded-lg transition-colors text-sm">
            Essais expirants
        </a>
        <a href="{{ route('admin.subscriptions.unsubscribed') }}" class="px-4 py-2 bg-warning-500/20 text-warning-400 hover:bg-warning-500/30 rounded-lg transition-colors text-sm">
            Sans abonnement
        </a>
        <a href="{{ route('admin.subscription-invoices.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-sm">
            Factures
        </a>
        <a href="{{ route('admin.subscription-plans.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-sm">
            Gérer les plans
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher entreprise..." class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400">
            </div>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white">
                <option value="">Tous les statuts</option>
                @foreach(\App\Models\Subscription::STATUSES as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="plan" class="bg-secondary-700 border-secondary-600 rounded-lg text-white">
                <option value="">Tous les plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan') === $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'status', 'plan']))
                <a href="{{ route('admin.subscriptions.index') }}" class="text-secondary-400 hover:text-white">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Échéance</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($subscriptions as $subscription)
                    <tr class="hover:bg-secondary-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.companies.show', $subscription->company) }}" class="text-white hover:text-primary-400 font-medium">
                                {{ $subscription->company->name }}
                            </a>
                            @if($subscription->company->vat_number)
                                <div class="text-xs text-secondary-400">{{ $subscription->company->vat_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-primary-500/20 text-primary-400 text-sm rounded">{{ $subscription->plan->name }}</span>
                            <div class="text-xs text-secondary-400 mt-1">{{ ucfirst($subscription->billing_cycle) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $subscription->status_color }}-500/20 text-{{ $subscription->status_color }}-400">
                                {{ $subscription->status_label }}
                            </span>
                            @if($subscription->onTrial())
                                <div class="text-xs text-secondary-400 mt-1">
                                    {{ $subscription->trial_days_remaining }}j restants
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">{{ number_format($subscription->amount, 2) }} €</span>
                            <span class="text-secondary-400">/{{ $subscription->billing_cycle === 'yearly' ? 'an' : 'mois' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-400">
                            @if($subscription->onTrial())
                                Essai: {{ $subscription->trial_ends_at->format('d/m/Y') }}
                            @elseif($subscription->current_period_end)
                                {{ $subscription->current_period_end->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-secondary-400 hover:text-white" title="Voir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="text-secondary-400 hover:text-white" title="Modifier">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-secondary-400">
                            Aucun abonnement trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
