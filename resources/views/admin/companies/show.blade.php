<x-admin-layout>
    <x-slot name="title">{{ $company->name }}</x-slot>
    <x-slot name="header">Détails Entreprise</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Company Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 rounded-xl bg-primary-500/20 flex items-center justify-center text-2xl font-bold text-primary-400">
                        {{ strtoupper(substr($company->name, 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-2xl font-bold">{{ $company->name }}</h2>
                            @if($company->trashed())
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">Suspendue</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">Active</span>
                            @endif
                        </div>
                        @if($company->vat_number)
                            <p class="font-mono text-secondary-400">{{ $company->vat_number }}</p>
                        @endif
                        @if($company->email)
                            <p class="text-secondary-400">{{ $company->email }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.companies.edit', $company) }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                            Modifier
                        </a>
                        @if(!$company->trashed())
                            <form action="{{ route('admin.companies.impersonate', $company) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-warning-500 hover:bg-warning-600 rounded-lg transition-colors">
                                    Impersonner
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Informations</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-secondary-400">Nom</dt>
                        <dd class="font-medium">{{ $company->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Numéro TVA</dt>
                        <dd class="font-mono">{{ $company->vat_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Email</dt>
                        <dd>{{ $company->email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Téléphone</dt>
                        <dd>{{ $company->phone ?? '-' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-sm text-secondary-400">Adresse</dt>
                        <dd>
                            @if($company->street || $company->city)
                                {{ $company->street }}<br>
                                {{ $company->postal_code }} {{ $company->city }}<br>
                                {{ $company->getCountryConfig()['name'] ?? $company->country_code ?? '-' }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Créé le</dt>
                        <dd class="font-medium">{{ $company->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Modifié le</dt>
                        <dd class="font-medium">{{ $company->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Peppol Settings -->
            @if($company->peppol_id || $company->peppol_provider)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Configuration Peppol</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-secondary-400">Peppol ID</dt>
                            <dd class="font-mono">{{ $company->peppol_id ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-secondary-400">Provider</dt>
                            <dd>{{ ucfirst($company->peppol_provider ?? '-') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-secondary-400">Mode Test</dt>
                            <dd>
                                @if($company->peppol_test_mode)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-warning-500/20 text-warning-400">Test</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">Production</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-secondary-400">Connecté le</dt>
                            <dd>{{ $company->peppol_connected_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif

            <!-- Users -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-center justify-between mb-4 border-b border-secondary-700 pb-4">
                    <h3 class="font-semibold">Utilisateurs ({{ $company->users->count() }})</h3>
                </div>
                <div class="space-y-3">
                    @forelse($company->users as $user)
                        <div class="flex items-center gap-3 p-3 bg-secondary-700/50 rounded-lg">
                            <div class="w-10 h-10 rounded-full {{ $user->is_superadmin ? 'bg-danger-500' : 'bg-primary-500/20' }} flex items-center justify-center font-bold {{ $user->is_superadmin ? 'text-white' : 'text-primary-400' }}">
                                {{ $user->initials }}
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-white hover:text-primary-400">
                                    {{ $user->full_name }}
                                </a>
                                <p class="text-sm text-secondary-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex gap-2">
                                @if($user->is_superadmin)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">Superadmin</span>
                                @endif
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">
                                    {{ ucfirst($user->pivot->role) }}
                                </span>
                                @if($user->pivot->is_default)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">Defaut</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary-500 text-center py-4">Aucun utilisateur</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Subscription -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-4 border-b border-secondary-700 flex items-center justify-between">
                    <h3 class="font-semibold">Abonnement</h3>
                    @if($company->subscription)
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @if($company->subscription->status === 'active') bg-success-500/20 text-success-400
                            @elseif($company->subscription->status === 'trialing') bg-primary-500/20 text-primary-400
                            @elseif($company->subscription->status === 'past_due') bg-warning-500/20 text-warning-400
                            @else bg-danger-500/20 text-danger-400
                            @endif">
                            {{ $company->subscription->status_label ?? ucfirst($company->subscription->status) }}
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-secondary-600 text-secondary-400">
                            Aucun
                        </span>
                    @endif
                </div>
                <div class="p-4">
                    @if($company->subscription)
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-secondary-400">Plan</span>
                                <span class="font-medium text-white">{{ $company->subscription->plan?->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-secondary-400">Cycle</span>
                                <span class="text-white">{{ $company->subscription->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-secondary-400">Montant</span>
                                <span class="font-medium text-white">{{ number_format($company->subscription->amount, 2) }} €/{{ $company->subscription->billing_cycle === 'yearly' ? 'an' : 'mois' }}</span>
                            </div>
                            @if($company->subscription->status === 'trialing' && $company->subscription->trial_ends_at)
                                <div class="flex justify-between">
                                    <span class="text-secondary-400">Fin d'essai</span>
                                    <span class="text-warning-400">{{ $company->subscription->trial_ends_at->format('d/m/Y') }}</span>
                                </div>
                            @elseif($company->subscription->current_period_end)
                                <div class="flex justify-between">
                                    <span class="text-secondary-400">Prochaine facturation</span>
                                    <span class="text-white">{{ $company->subscription->current_period_end->format('d/m/Y') }}</span>
                                </div>
                            @endif

                            <!-- Usage summary -->
                            @php
                                $usage = $company->getCurrentUsage();
                                $plan = $company->plan;
                            @endphp
                            @if($plan)
                                <div class="pt-3 border-t border-secondary-700">
                                    <div class="text-xs text-secondary-400 mb-2">Utilisation</div>
                                    <div class="space-y-2">
                                        @if($plan->max_invoices_per_month > 0)
                                            <div>
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span class="text-secondary-400">Factures/mois</span>
                                                    <span class="text-white">{{ $usage['invoices'] }}/{{ $plan->max_invoices_per_month == -1 ? '∞' : $plan->max_invoices_per_month }}</span>
                                                </div>
                                                @if($plan->max_invoices_per_month != -1)
                                                    <div class="w-full bg-secondary-700 rounded-full h-1.5">
                                                        <div class="h-1.5 rounded-full {{ $usage['invoices'] >= $plan->max_invoices_per_month ? 'bg-danger-500' : 'bg-primary-500' }}"
                                                            style="width: {{ min(100, ($usage['invoices'] / $plan->max_invoices_per_month) * 100) }}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @if($plan->max_clients > 0)
                                            <div>
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span class="text-secondary-400">Clients</span>
                                                    <span class="text-white">{{ $usage['clients'] }}/{{ $plan->max_clients == -1 ? '∞' : $plan->max_clients }}</span>
                                                </div>
                                                @if($plan->max_clients != -1)
                                                    <div class="w-full bg-secondary-700 rounded-full h-1.5">
                                                        <div class="h-1.5 rounded-full {{ $usage['clients'] >= $plan->max_clients ? 'bg-danger-500' : 'bg-primary-500' }}"
                                                            style="width: {{ min(100, ($usage['clients'] / $plan->max_clients) * 100) }}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('admin.subscriptions.show', $company->subscription) }}"
                            class="mt-4 w-full px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Gérer l'abonnement
                        </a>
                    @else
                        <p class="text-secondary-400 text-sm mb-4">Cette entreprise n'a pas d'abonnement actif.</p>
                        <a href="{{ route('admin.subscriptions.create', ['company' => $company->id]) }}"
                            class="w-full px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Créer un abonnement
                        </a>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Statistiques</h3>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Utilisateurs</dt>
                        <dd class="font-bold text-primary-400">{{ $company->users->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Clients</dt>
                        <dd class="font-bold text-primary-400">{{ number_format($company->clients()->count()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Factures</dt>
                        <dd class="font-bold text-primary-400">{{ number_format($company->invoices()->count()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Produits</dt>
                        <dd class="font-bold text-primary-400">{{ number_format($company->products->count()) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.modules.assign-form', $company) }}" class="w-full px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors flex items-center justify-center gap-2 block">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Gérer les modules
                    </a>

                    @if(!$company->subscription)
                        <a href="{{ route('admin.subscriptions.create', ['company' => $company->id]) }}" class="w-full px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg transition-colors flex items-center justify-center gap-2 block">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Créer un abonnement
                        </a>
                    @endif

                    <form action="{{ route('admin.companies.impersonate', $company) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-warning-500 hover:bg-warning-600 rounded-lg transition-colors font-medium flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Se connecter en tant que
                        </button>
                    </form>

                    @if($company->trashed())
                        <form action="{{ route('admin.companies.restore', $company) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg transition-colors font-medium">
                                Réactiver l'entreprise
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.companies.suspend', $company) }}" method="POST" onsubmit="return confirm('Suspendre cette entreprise? Les utilisateurs ne pourront plus y accéder.')">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-danger-500 hover:bg-danger-600 rounded-lg transition-colors font-medium">
                                Suspendre l'entreprise
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.audit-logs.index', ['company' => $company->id]) }}" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors flex items-center justify-center gap-2 block">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Voir les logs d'audit
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Activité Récente</h3>
                @forelse($recentActivity as $log)
                    <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-b border-secondary-700' : '' }}">
                        <div class="w-6 h-6 rounded-full bg-{{ $log->action_color }}-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-{{ $log->action_color }}-400"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">{{ $log->description }}</p>
                            <p class="text-xs text-secondary-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary-500 text-center py-4">Aucune activité</p>
                @endforelse
            </div>

            <!-- Back -->
            <a href="{{ route('admin.companies.index') }}" class="flex items-center gap-2 text-secondary-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la liste
            </a>
        </div>
    </div>
</x-admin-layout>
