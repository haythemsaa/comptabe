<x-admin-layout>
    <x-slot name="title">Modifier le plan - {{ $subscriptionPlan->name }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.subscription-plans.index') }}" class="text-secondary-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span>Modifier le plan</span>
        </div>
    </x-slot>

    <form action="{{ route('admin.subscription-plans.update', $subscriptionPlan) }}" method="POST" class="max-w-4xl">
        @csrf
        @method('PUT')

        @if($subscribersCount > 0)
            <div class="mb-6 p-4 bg-warning-500/10 border border-warning-500/30 rounded-xl">
                <div class="flex items-center gap-2 text-warning-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="font-medium">{{ $subscribersCount }} abonné(s) actif(s) sur ce plan</span>
                </div>
                <p class="text-secondary-400 text-sm mt-1">Les modifications de limites et fonctionnalités affecteront tous les abonnés existants.</p>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Basic Info -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">Informations générales</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-secondary-300 mb-2">Nom du plan *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $subscriptionPlan->name) }}" required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            @error('name')
                                <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="slug" class="block text-sm font-medium text-secondary-300 mb-2">Slug *</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $subscriptionPlan->slug) }}" required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white font-mono">
                            @error('slug')
                                <p class="text-danger-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-secondary-300 mb-2">Description</label>
                        <textarea name="description" id="description" rows="2"
                            class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">{{ old('description', $subscriptionPlan->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-secondary-300 mb-2">Ordre d'affichage</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $subscriptionPlan->sort_order) }}" min="0"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subscriptionPlan->is_active) ? 'checked' : '' }}
                                    class="rounded text-primary-500 focus:ring-primary-500">
                                <span class="text-white">Plan actif</span>
                            </label>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $subscriptionPlan->is_featured) ? 'checked' : '' }}
                                    class="rounded text-primary-500 focus:ring-primary-500">
                                <span class="text-white">Plan recommandé</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">Tarification</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="price_monthly" class="block text-sm font-medium text-secondary-300 mb-2">Prix mensuel (€) *</label>
                            <input type="number" name="price_monthly" id="price_monthly" value="{{ old('price_monthly', $subscriptionPlan->price_monthly) }}" required
                                min="0" step="0.01"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="price_yearly" class="block text-sm font-medium text-secondary-300 mb-2">Prix annuel (€) *</label>
                            <input type="number" name="price_yearly" id="price_yearly" value="{{ old('price_yearly', $subscriptionPlan->price_yearly) }}" required
                                min="0" step="0.01"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="trial_days" class="block text-sm font-medium text-secondary-300 mb-2">Jours d'essai *</label>
                            <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', $subscriptionPlan->trial_days) }}" required
                                min="0"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Limits -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">Limites</h2>
                    <p class="text-secondary-400 text-sm mt-1">-1 = Illimité</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <label for="max_users" class="block text-sm font-medium text-secondary-300 mb-2">Utilisateurs</label>
                            <input type="number" name="max_users" id="max_users" value="{{ old('max_users', $subscriptionPlan->max_users) }}" required
                                min="-1"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="max_invoices_per_month" class="block text-sm font-medium text-secondary-300 mb-2">Factures/mois</label>
                            <input type="number" name="max_invoices_per_month" id="max_invoices_per_month" value="{{ old('max_invoices_per_month', $subscriptionPlan->max_invoices_per_month) }}" required
                                min="-1"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="max_clients" class="block text-sm font-medium text-secondary-300 mb-2">Clients</label>
                            <input type="number" name="max_clients" id="max_clients" value="{{ old('max_clients', $subscriptionPlan->max_clients) }}" required
                                min="-1"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="max_products" class="block text-sm font-medium text-secondary-300 mb-2">Produits</label>
                            <input type="number" name="max_products" id="max_products" value="{{ old('max_products', $subscriptionPlan->max_products) }}" required
                                min="-1"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                        <div>
                            <label for="max_storage_mb" class="block text-sm font-medium text-secondary-300 mb-2">Stockage (MB)</label>
                            <input type="number" name="max_storage_mb" id="max_storage_mb" value="{{ old('max_storage_mb', $subscriptionPlan->max_storage_mb) }}" required
                                min="-1"
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                <div class="p-6 border-b border-secondary-700">
                    <h2 class="text-lg font-semibold text-white">Fonctionnalités</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_peppol" value="1" {{ old('feature_peppol', $subscriptionPlan->feature_peppol) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Peppol</span>
                                <p class="text-xs text-secondary-400">Envoi électronique</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_recurring_invoices" value="1" {{ old('feature_recurring_invoices', $subscriptionPlan->feature_recurring_invoices) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Factures récurrentes</span>
                                <p class="text-xs text-secondary-400">Automatisation</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_credit_notes" value="1" {{ old('feature_credit_notes', $subscriptionPlan->feature_credit_notes) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Notes de crédit</span>
                                <p class="text-xs text-secondary-400">Remboursements</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_quotes" value="1" {{ old('feature_quotes', $subscriptionPlan->feature_quotes) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Devis</span>
                                <p class="text-xs text-secondary-400">Propositions commerciales</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_multi_currency" value="1" {{ old('feature_multi_currency', $subscriptionPlan->feature_multi_currency) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Multi-devises</span>
                                <p class="text-xs text-secondary-400">EUR, USD, GBP...</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_api_access" value="1" {{ old('feature_api_access', $subscriptionPlan->feature_api_access) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Accès API</span>
                                <p class="text-xs text-secondary-400">Intégrations</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_custom_branding" value="1" {{ old('feature_custom_branding', $subscriptionPlan->feature_custom_branding) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Branding personnalisé</span>
                                <p class="text-xs text-secondary-400">Logo, couleurs</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_advanced_reports" value="1" {{ old('feature_advanced_reports', $subscriptionPlan->feature_advanced_reports) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Rapports avancés</span>
                                <p class="text-xs text-secondary-400">Analytics détaillés</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-secondary-700/50 rounded-lg hover:bg-secondary-700 transition-colors">
                            <input type="checkbox" name="feature_priority_support" value="1" {{ old('feature_priority_support', $subscriptionPlan->feature_priority_support) ? 'checked' : '' }}
                                class="rounded text-primary-500 focus:ring-primary-500">
                            <div>
                                <span class="text-white">Support prioritaire</span>
                                <p class="text-xs text-secondary-400">Réponse rapide</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.subscription-plans.index') }}" class="text-secondary-400 hover:text-white">
                        Annuler
                    </a>
                    @if($subscribersCount === 0)
                        <form action="{{ route('admin.subscription-plans.destroy', $subscriptionPlan) }}" method="POST"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce plan ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger-400 hover:text-danger-300">
                                Supprimer
                            </button>
                        </form>
                    @endif
                </div>
                <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                    Enregistrer
                </button>
            </div>
        </div>
    </form>
</x-admin-layout>
