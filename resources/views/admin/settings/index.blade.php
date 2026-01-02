<x-admin-layout>
    <x-slot name="title">Paramètres Système</x-slot>
    <x-slot name="header">Paramètres Système</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Info -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Informations Système</h2>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Version Laravel</dt>
                    <dd class="font-mono">{{ app()->version() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Version PHP</dt>
                    <dd class="font-mono">{{ PHP_VERSION }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Environnement</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ app()->environment('production') ? 'bg-danger-500/20 text-danger-400' : 'bg-success-500/20 text-success-400' }}">
                            {{ app()->environment() }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Mode Debug</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ config('app.debug') ? 'bg-warning-500/20 text-warning-400' : 'bg-success-500/20 text-success-400' }}">
                            {{ config('app.debug') ? 'Activé' : 'Désactivé' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Timezone</dt>
                    <dd class="font-mono">{{ config('app.timezone') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Locale</dt>
                    <dd>{{ config('app.locale') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Cache Management -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Gestion du Cache</h2>
            <div class="space-y-4">
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-left flex items-center gap-4">
                        <div class="w-10 h-10 bg-warning-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium">Vider tout le cache</span>
                            <p class="text-sm text-secondary-400">Configuration, vues, routes, données</p>
                        </div>
                    </button>
                </form>

                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="views">
                    <button type="submit" class="w-full px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-left flex items-center gap-4">
                        <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium">Cache des vues</span>
                            <p class="text-sm text-secondary-400">Vider les vues Blade compilées</p>
                        </div>
                    </button>
                </form>

                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="w-full px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-left flex items-center gap-4">
                        <div class="w-10 h-10 bg-success-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium">Cache de configuration</span>
                            <p class="text-sm text-secondary-400">Recharger la configuration</p>
                        </div>
                    </button>
                </form>
            </div>
        </div>

        <!-- Database Stats -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Base de Données</h2>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Connexion</dt>
                    <dd class="font-mono">{{ config('database.default') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Entreprises</dt>
                    <dd class="font-bold text-primary-400">{{ number_format($stats['companies'] ?? 0) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Utilisateurs</dt>
                    <dd class="font-bold text-primary-400">{{ number_format($stats['users'] ?? 0) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Factures</dt>
                    <dd class="font-bold text-primary-400">{{ number_format($stats['invoices'] ?? 0) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Logs d'Audit</dt>
                    <dd class="font-bold text-primary-400">{{ number_format($stats['audit_logs'] ?? 0) }}</dd>
                </div>
            </dl>
        </div>

        <!-- Maintenance -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Maintenance</h2>
            <div class="space-y-4">
                @if(app()->isDownForMaintenance())
                    <div class="p-4 bg-warning-500/20 border border-warning-500/50 rounded-xl">
                        <p class="text-warning-400 font-medium">Mode maintenance actif</p>
                        <p class="text-sm text-secondary-400 mt-1">L'application est actuellement en maintenance.</p>
                    </div>
                    <form action="{{ route('admin.settings.maintenance') }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="up">
                        <button type="submit" class="w-full px-4 py-3 bg-success-500 hover:bg-success-600 rounded-xl transition-colors font-medium">
                            Désactiver le mode maintenance
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.settings.maintenance') }}" method="POST" onsubmit="return confirm('Activer le mode maintenance? Les utilisateurs ne pourront plus accéder à l\'application.')">
                        @csrf
                        <input type="hidden" name="action" value="down">
                        <button type="submit" class="w-full px-4 py-3 bg-danger-500 hover:bg-danger-600 rounded-xl transition-colors font-medium">
                            Activer le mode maintenance
                        </button>
                    </form>
                @endif

                <hr class="border-secondary-700">

                <form action="{{ route('admin.settings.run-migrations') }}" method="POST" onsubmit="return confirm('Exécuter les migrations en attente?')">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-left flex items-center gap-4">
                        <div class="w-10 h-10 bg-danger-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium">Exécuter les migrations</span>
                            <p class="text-sm text-secondary-400">Appliquer les migrations en attente</p>
                        </div>
                    </button>
                </form>
            </div>
        </div>

        <!-- Queue Status -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Files d'Attente</h2>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Driver</dt>
                    <dd class="font-mono">{{ config('queue.default') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Jobs en attente</dt>
                    <dd class="font-bold">{{ $stats['pending_jobs'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Jobs échoués</dt>
                    <dd class="font-bold {{ ($stats['failed_jobs'] ?? 0) > 0 ? 'text-danger-400' : '' }}">{{ $stats['failed_jobs'] ?? 0 }}</dd>
                </div>
            </dl>

            @if(($stats['failed_jobs'] ?? 0) > 0)
                <form action="{{ route('admin.settings.retry-failed-jobs') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-warning-500 hover:bg-warning-600 rounded-lg transition-colors font-medium">
                        Réessayer les jobs échoués
                    </button>
                </form>
            @endif
        </div>

        <!-- Storage -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h2 class="text-lg font-semibold mb-4 border-b border-secondary-700 pb-4">Stockage</h2>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Driver par défaut</dt>
                    <dd class="font-mono">{{ config('filesystems.default') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-secondary-400">Espace utilisé (uploads)</dt>
                    <dd class="font-mono">{{ $stats['storage_used'] ?? 'N/A' }}</dd>
                </div>
            </dl>

            <div class="mt-4">
                <a href="{{ route('admin.settings.storage-link') }}" class="w-full px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-xl transition-colors text-left flex items-center gap-4 block">
                    <div class="w-10 h-10 bg-primary-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-medium">Créer lien symbolique</span>
                        <p class="text-sm text-secondary-400">Lier storage/app/public à public/storage</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
