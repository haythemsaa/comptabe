<x-admin-layout>
    <x-slot name="title">Erreur #{{ $error->id }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.errors.index') }}" class="p-2 hover:bg-secondary-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span>Erreur #{{ $error->id }}</span>
            </div>
            <div class="flex gap-2">
                @if(!$error->resolved)
                    <button onclick="document.getElementById('resolveForm').classList.remove('hidden')" class="px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg font-medium transition-colors">
                        Marquer comme résolue
                    </button>
                @endif
                <form action="{{ route('admin.errors.destroy', $error) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette erreur?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-danger-500 hover:bg-danger-600 rounded-lg font-medium transition-colors">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <!-- Resolve Form (Hidden by default) -->
    <div id="resolveForm" class="hidden bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
        <form action="{{ route('admin.errors.resolve', $error) }}" method="POST">
            @csrf
            <h3 class="text-lg font-semibold mb-4">Marquer comme résolue</h3>
            <div class="mb-4">
                <label for="resolution_note" class="block text-sm font-medium text-secondary-300 mb-2">Note de résolution (optionnelle)</label>
                <textarea id="resolution_note" name="resolution_note" rows="3" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="Décrivez comment le problème a été résolu..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg font-medium transition-colors">
                    Confirmer
                </button>
                <button type="button" onclick="document.getElementById('resolveForm').classList.add('hidden')" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Error Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Error Details -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Détails de l'erreur
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-secondary-400">Message</label>
                        <p class="text-white mt-1 bg-secondary-900 rounded-lg p-3 font-mono text-sm">{{ $error->message }}</p>
                    </div>

                    @if($error->exception)
                        <div>
                            <label class="text-sm text-secondary-400">Exception</label>
                            <p class="text-danger-400 mt-1 bg-secondary-900 rounded-lg p-3 font-mono text-sm">{{ $error->exception }}</p>
                        </div>
                    @endif

                    @if($error->file)
                        <div>
                            <label class="text-sm text-secondary-400">Fichier</label>
                            <p class="text-white mt-1 bg-secondary-900 rounded-lg p-3 font-mono text-sm">
                                {{ $error->file }}
                                @if($error->line)
                                    <span class="text-primary-400">:{{ $error->line }}</span>
                                @endif
                            </p>
                        </div>
                    @endif

                    @if($error->url)
                        <div>
                            <label class="text-sm text-secondary-400">URL</label>
                            <p class="text-white mt-1 bg-secondary-900 rounded-lg p-3 font-mono text-sm break-all">
                                <span class="text-primary-400">{{ $error->method }}</span> {{ $error->url }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stack Trace -->
            @if($error->trace)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                        Stack Trace
                    </h3>
                    <div class="bg-secondary-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-xs text-secondary-300 font-mono whitespace-pre-wrap">{{ $error->trace }}</pre>
                    </div>
                </div>
            @endif

            <!-- Request Data -->
            @if($error->request_data)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Données de la requête
                    </h3>
                    <div class="bg-secondary-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-xs text-secondary-300 font-mono">{{ json_encode($error->request_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif

            <!-- Context -->
            @if($error->context)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Contexte
                    </h3>
                    <div class="bg-secondary-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-xs text-secondary-300 font-mono">{{ json_encode($error->context, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-semibold mb-4">Statut</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-secondary-400">Sévérité</label>
                        <p class="mt-1">
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                @if($error->severity === 'critical') bg-danger-500/20 text-danger-400
                                @elseif($error->severity === 'error') bg-warning-500/20 text-warning-400
                                @else bg-secondary-500/20 text-secondary-400
                                @endif">
                                {{ ucfirst($error->severity) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-secondary-400">Type</label>
                        <p class="text-white mt-1">{{ ucfirst($error->type) }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-secondary-400">Résolution</label>
                        <p class="mt-1">
                            @if($error->resolved)
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-success-500/20 text-success-400">Résolue</span>
                            @else
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-danger-500/20 text-danger-400">Non résolue</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-secondary-400">Occurrences</label>
                        <p class="text-white mt-1 font-bold">{{ $error->occurrences }}</p>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            @if($error->user)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Utilisateur</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center font-bold text-primary-400">
                            {{ $error->user->initials }}
                        </div>
                        <div>
                            <p class="font-medium text-white">{{ $error->user->full_name }}</p>
                            <p class="text-sm text-secondary-500">{{ $error->user->email }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Company Info -->
            @if($error->company)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Entreprise</h3>
                    <div>
                        <p class="font-medium text-white">{{ $error->company->name }}</p>
                        <p class="text-sm text-secondary-500">{{ $error->company->vat_number }}</p>
                    </div>
                </div>
            @endif

            <!-- Timestamps -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-semibold mb-4">Horodatage</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-secondary-400">Première occurrence</label>
                        <p class="text-white mt-1 text-sm">{{ $error->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-secondary-400">Dernière occurrence</label>
                        <p class="text-white mt-1 text-sm">
                            @if($error->last_occurred_at)
                                {{ $error->last_occurred_at->format('d/m/Y H:i:s') }}
                            @else
                                {{ $error->created_at->format('d/m/Y H:i:s') }}
                            @endif
                        </p>
                    </div>
                    @if($error->resolved)
                        <div>
                            <label class="text-sm text-secondary-400">Résolue le</label>
                            <p class="text-white mt-1 text-sm">{{ $error->resolved_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        @if($error->resolvedBy)
                            <div>
                                <label class="text-sm text-secondary-400">Résolue par</label>
                                <p class="text-white mt-1 text-sm">{{ $error->resolvedBy->full_name }}</p>
                            </div>
                        @endif
                        @if($error->resolution_note)
                            <div>
                                <label class="text-sm text-secondary-400">Note de résolution</label>
                                <p class="text-white mt-1 text-sm bg-secondary-900 rounded-lg p-2">{{ $error->resolution_note }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Additional Info -->
            @if($error->ip || $error->user_agent)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Informations techniques</h3>
                    <div class="space-y-3">
                        @if($error->ip)
                            <div>
                                <label class="text-sm text-secondary-400">Adresse IP</label>
                                <p class="text-white mt-1 text-sm font-mono">{{ $error->ip }}</p>
                            </div>
                        @endif
                        @if($error->user_agent)
                            <div>
                                <label class="text-sm text-secondary-400">User Agent</label>
                                <p class="text-white mt-1 text-xs break-all">{{ $error->user_agent }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
