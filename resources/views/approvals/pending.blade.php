<x-app-layout>
    <x-slot name="title">Approbations en attente</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Demandes en attente</h2>
            <a href="{{ route('approvals.index') }}"
                class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                ← Retour au tableau de bord
            </a>
        </div>
    </x-slot>

    <!-- Filter Bar -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6" x-data="{ showFilters: false }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm text-secondary-400">{{ $pendingApprovals->count() }} demande(s) en attente</span>
                <button @click="showFilters = !showFilters" class="text-sm text-primary-400 hover:text-primary-300">
                    <span x-show="!showFilters">Afficher les filtres</span>
                    <span x-show="showFilters" x-cloak>Masquer les filtres</span>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <select class="bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option>Trier par: Date (plus récent)</option>
                    <option>Trier par: Montant (croissant)</option>
                    <option>Trier par: Montant (décroissant)</option>
                    <option>Trier par: Urgence</option>
                </select>
            </div>
        </div>

        <!-- Filters (collapsed by default) -->
        <div x-show="showFilters" x-cloak class="mt-4 pt-4 border-t border-secondary-700">
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Type de document</label>
                    <select class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Tous</option>
                        <option>Factures d'achat</option>
                        <option>Factures de vente</option>
                        <option>Dépenses</option>
                        <option>Paiements</option>
                        <option>Écritures comptables</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Montant minimum</label>
                    <input type="number" step="0.01" placeholder="0.00"
                        class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Demandeur</label>
                    <select class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Tous</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests List -->
    <div class="space-y-4">
        @forelse($pendingApprovals as $request)
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 hover:border-primary-500/50 transition-all" x-data="{ expanded: false }">
                <!-- Main Content -->
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <!-- Header -->
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold">{{ $request->workflow->name }}</h3>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $request->getStatusColor() }}-500/20 text-{{ $request->getStatusColor() }}-400">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                        @if($request->isExpired())
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">
                                                Expiré
                                            </span>
                                        @elseif($request->expires_at)
                                            <span class="text-xs text-secondary-400">
                                                Expire {{ $request->expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-secondary-400">
                                        Demandé par <span class="font-medium text-white">{{ $request->requester->full_name }}</span>
                                        • {{ $request->created_at->diffForHumans() }}
                                        • {{ $request->workflow->getDocumentTypeLabel() }}
                                    </p>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="grid md:grid-cols-4 gap-4 mb-4">
                                <div class="p-3 rounded-lg bg-secondary-700/50">
                                    <p class="text-xs text-secondary-400 mb-1">Montant</p>
                                    <p class="text-lg font-bold">{{ number_format($request->amount, 2) }} €</p>
                                </div>
                                <div class="p-3 rounded-lg bg-secondary-700/50">
                                    <p class="text-xs text-secondary-400 mb-1">Étape actuelle</p>
                                    <p class="text-lg font-bold">{{ $request->current_step }} / {{ $request->steps->max('step_number') }}</p>
                                </div>
                                <div class="p-3 rounded-lg bg-secondary-700/50">
                                    <p class="text-xs text-secondary-400 mb-1">Progression</p>
                                    <p class="text-lg font-bold">{{ $request->getProgressPercentage() }}%</p>
                                </div>
                                <div class="p-3 rounded-lg bg-secondary-700/50">
                                    <p class="text-xs text-secondary-400 mb-1">Priorité</p>
                                    <p class="text-lg font-bold text-warning-400">Normale</p>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($request->notes)
                                <div class="p-4 rounded-lg bg-secondary-700/50 border-l-4 border-primary-500">
                                    <p class="text-sm font-medium text-secondary-400 mb-1">Notes de la demande:</p>
                                    <p class="text-sm text-secondary-200">{{ $request->notes }}</p>
                                </div>
                            @endif

                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs text-secondary-400 mb-2">
                                    <span>Progression du workflow</span>
                                    <span>{{ $request->getProgressPercentage() }}%</span>
                                </div>
                                <div class="w-full bg-secondary-700 rounded-full h-2.5">
                                    <div class="bg-primary-500 h-2.5 rounded-full transition-all"
                                        style="width: {{ $request->getProgressPercentage() }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('approvals.show', $request) }}"
                                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-sm font-medium transition-colors text-center whitespace-nowrap">
                                Voir détails
                            </a>
                            <button @click="expanded = !expanded"
                                class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                                <span x-show="!expanded">Actions rapides</span>
                                <span x-show="expanded" x-cloak>Masquer</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Actions (Expanded) -->
                    <div x-show="expanded" x-cloak x-transition class="mt-4 pt-4 border-t border-secondary-700">
                        <div class="grid md:grid-cols-2 gap-3">
                            <form action="{{ route('approvals.approve', $request) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-3 bg-success-500/20 hover:bg-success-500/30 text-success-400 rounded-lg font-medium transition-colors inline-flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Approuver rapidement
                                </button>
                            </form>
                            <button type="button"
                                onclick="if(confirm('Êtes-vous sûr de vouloir rejeter cette demande?')) { document.getElementById('reject-form-{{ $request->id }}').submit(); }"
                                class="w-full px-4 py-3 bg-danger-500/20 hover:bg-danger-500/30 text-danger-400 rounded-lg font-medium transition-colors inline-flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Rejeter
                            </button>
                            <form id="reject-form-{{ $request->id }}" action="{{ route('approvals.reject', $request) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="reason" value="Rejeté via action rapide">
                            </form>
                        </div>
                        <p class="text-xs text-secondary-500 mt-3 text-center">
                            Pour ajouter un commentaire ou déléguer, utilisez la vue détaillée
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-16 text-center">
                <svg class="w-20 h-20 mx-auto mb-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-secondary-300 mb-2">Aucune demande en attente</h3>
                <p class="text-secondary-500">Vous n'avez aucune approbation à traiter pour le moment</p>
                <a href="{{ route('approvals.index') }}"
                    class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                    Retour au tableau de bord
                </a>
            </div>
        @endforelse
    </div>
</x-app-layout>
