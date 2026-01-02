<x-admin-layout>
    <x-slot name="title">Abonnement - {{ $subscription->company->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Détails de l'abonnement</span>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Company & Plan -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $subscription->company->name }}</h2>
                            <p class="text-secondary-400 mt-1">{{ $subscription->company->vat_number }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $subscription->status_color }}-500/20 text-{{ $subscription->status_color }}-400">
                            {{ $subscription->status_label }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <div class="text-secondary-400 text-sm">Plan</div>
                            <div class="text-white font-medium mt-1">{{ $subscription->plan->name }}</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Cycle</div>
                            <div class="text-white font-medium mt-1">{{ ucfirst($subscription->billing_cycle) }}</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Montant</div>
                            <div class="text-white font-medium mt-1">{{ number_format($subscription->amount, 2) }} €</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Créé le</div>
                            <div class="text-white font-medium mt-1">{{ $subscription->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>

                    @if($subscription->onTrial())
                        <div class="mt-6 p-4 bg-info-500/10 border border-info-500/30 rounded-lg">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="text-info-400 font-medium">Période d'essai</div>
                                    <div class="text-secondary-400 text-sm">
                                        Expire le {{ $subscription->trial_ends_at->format('d/m/Y') }}
                                        ({{ $subscription->trial_days_remaining }} jours restants)
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($subscription->current_period_end)
                        <div class="mt-6 p-4 bg-secondary-700/50 rounded-lg">
                            <div class="text-secondary-400 text-sm">Période actuelle</div>
                            <div class="text-white mt-1">
                                {{ $subscription->current_period_start?->format('d/m/Y') ?? '-' }}
                                -
                                {{ $subscription->current_period_end->format('d/m/Y') }}
                            </div>
                        </div>
                    @endif

                    @if($subscription->admin_notes)
                        <div class="mt-6 p-4 bg-secondary-700/50 rounded-lg">
                            <div class="text-secondary-400 text-sm">Notes admin</div>
                            <div class="text-white mt-1">{{ $subscription->admin_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Utilisation</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <div class="text-secondary-400 text-sm">Factures ce mois</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ $usage['invoices'] ?? 0 }}</div>
                            <div class="text-xs text-secondary-500">/ {{ $subscription->plan->max_invoices_per_month < 0 ? '∞' : $subscription->plan->max_invoices_per_month }}</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Clients</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ $usage['clients'] ?? 0 }}</div>
                            <div class="text-xs text-secondary-500">/ {{ $subscription->plan->max_clients < 0 ? '∞' : $subscription->plan->max_clients }}</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Utilisateurs</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ $usage['users'] ?? 0 }}</div>
                            <div class="text-xs text-secondary-500">/ {{ $subscription->plan->max_users < 0 ? '∞' : $subscription->plan->max_users }}</div>
                        </div>
                        <div>
                            <div class="text-secondary-400 text-sm">Stockage</div>
                            <div class="text-2xl font-bold text-white mt-1">{{ number_format($usage['storage_mb'] ?? 0, 0) }} MB</div>
                            <div class="text-xs text-secondary-500">/ {{ $subscription->plan->max_storage_mb }} MB</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices History -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700 flex items-center justify-between">
                    <h3 class="font-semibold text-white">Historique de facturation</h3>
                    <form action="{{ route('admin.subscriptions.generate-invoice', $subscription) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-primary-500 hover:bg-primary-600 rounded text-sm transition-colors">
                            Générer facture
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-secondary-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Facture</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-700">
                            @forelse($subscription->invoices as $invoice)
                                <tr class="hover:bg-secondary-700/50">
                                    <td class="px-6 py-4 text-white font-medium">{{ $invoice->invoice_number }}</td>
                                    <td class="px-6 py-4 text-secondary-400">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-white">{{ number_format($invoice->total, 2) }} €</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $invoice->status_color }}-500/20 text-{{ $invoice->status_color }}-400">
                                            {{ $invoice->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.subscription-invoices.show', $invoice) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-secondary-400">
                                        Aucune facture
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="flex items-center gap-2 w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>

                    @if($subscription->onTrial())
                        <form action="{{ route('admin.subscriptions.extend-trial', $subscription) }}" method="POST" x-data="{ days: 7 }">
                            @csrf
                            <div class="flex gap-2">
                                <input type="number" name="days" x-model="days" min="1" max="90" class="flex-1 bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm">
                                <button type="submit" class="px-3 py-2 bg-info-500/20 text-info-400 hover:bg-info-500/30 rounded-lg text-sm transition-colors">
                                    Prolonger essai
                                </button>
                            </div>
                        </form>
                    @endif

                    @if($subscription->status === 'suspended')
                        <form action="{{ route('admin.subscriptions.reactivate', $subscription) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 bg-success-500/20 text-success-400 hover:bg-success-500/30 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Réactiver
                            </button>
                        </form>
                    @elseif(!in_array($subscription->status, ['cancelled', 'expired']))
                        <form action="{{ route('admin.subscriptions.suspend', $subscription) }}" method="POST" x-data="{ reason: '' }">
                            @csrf
                            <input type="text" name="reason" x-model="reason" placeholder="Raison (optionnel)" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm mb-2">
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 bg-warning-500/20 text-warning-400 hover:bg-warning-500/30 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Suspendre
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.companies.show', $subscription->company) }}" class="flex items-center gap-2 w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Voir l'entreprise
                    </a>
                </div>
            </div>

            <!-- Company Users -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Utilisateurs ({{ $subscription->company->users->count() }})</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($subscription->company->users->take(5) as $user)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-secondary-700 flex items-center justify-center text-xs font-medium text-white">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-white text-sm truncate">{{ $user->full_name }}</div>
                                    <div class="text-secondary-400 text-xs truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        @endforeach
                        @if($subscription->company->users->count() > 5)
                            <div class="text-secondary-400 text-sm">
                                + {{ $subscription->company->users->count() - 5 }} autres
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Plan Features -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h3 class="font-semibold text-white">Fonctionnalités du plan</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        @foreach($subscription->plan->getFeaturesList() as $feature)
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-secondary-300">{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
