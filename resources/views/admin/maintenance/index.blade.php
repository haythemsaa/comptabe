<x-admin-layout>
    <x-slot name="title">Maintenance Système</x-slot>
    <x-slot name="header">Maintenance Système</x-slot>

    <!-- Maintenance Mode Status -->
    <div class="bg-secondary-800 rounded-xl border {{ $maintenanceStatus ? 'border-danger-500/50' : 'border-secondary-700' }} p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full {{ $maintenanceStatus ? 'bg-danger-500/20' : 'bg-success-500/20' }} flex items-center justify-center">
                    @if($maintenanceStatus)
                        <svg class="w-8 h-8 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <h3 class="text-xl font-semibold">
                        Mode Maintenance:
                        <span class="{{ $maintenanceStatus ? 'text-danger-400' : 'text-success-400' }}">
                            {{ $maintenanceStatus ? 'ACTIVÉ' : 'DÉSACTIVÉ' }}
                        </span>
                    </h3>
                    @if($maintenanceStatus && $maintenanceData)
                        <p class="text-sm text-secondary-400 mt-1">
                            Depuis {{ \Carbon\Carbon::createFromTimestamp($maintenanceData['time'])->diffForHumans() }}
                        </p>
                        @if(isset($maintenanceData['message']))
                            <p class="text-sm text-secondary-300 mt-2">Message: {{ $maintenanceData['message'] }}</p>
                        @endif
                        @if(isset($maintenanceData['secret']))
                            <p class="text-sm text-secondary-400 mt-1">Secret: <code class="text-primary-400">{{ $maintenanceData['secret'] }}</code></p>
                        @endif
                    @endif
                </div>
            </div>
            <div>
                @if($maintenanceStatus)
                    <form action="{{ route('admin.maintenance.disable') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-success-500 hover:bg-success-600 rounded-lg font-medium transition-colors">
                            Désactiver Maintenance
                        </button>
                    </form>
                @else
                    <button onclick="document.getElementById('enableMaintenanceModal').classList.remove('hidden')" class="px-6 py-3 bg-danger-500 hover:bg-danger-600 rounded-lg font-medium transition-colors">
                        Activer Maintenance
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Enable Maintenance Modal -->
    <div id="enableMaintenanceModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 max-w-lg w-full mx-4">
            <h3 class="text-xl font-semibold mb-4">Activer le Mode Maintenance</h3>
            <form action="{{ route('admin.maintenance.enable') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="message" class="block text-sm font-medium text-secondary-300 mb-2">Message personnalisé (optionnel)</label>
                        <textarea id="message" name="message" rows="3" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="L'application est en maintenance..."></textarea>
                    </div>
                    <div>
                        <label for="retry_after" class="block text-sm font-medium text-secondary-300 mb-2">Retry After (secondes)</label>
                        <input type="number" id="retry_after" name="retry_after" min="60" max="86400" value="3600" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                        <p class="text-xs text-secondary-500 mt-1">Temps suggéré avant nouvelle tentative (60-86400 secondes)</p>
                    </div>
                    <div>
                        <label for="secret" class="block text-sm font-medium text-secondary-300 mb-2">Secret (optionnel)</label>
                        <input type="text" id="secret" name="secret" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="Token secret pour accès admin">
                        <p class="text-xs text-secondary-500 mt-1">Permet d'accéder au site avec /?secret=VOTRE_TOKEN</p>
                    </div>
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-secondary-300 mb-2">URL de redirection (optionnel)</label>
                        <input type="url" id="redirect_url" name="redirect_url" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="https://status.example.com">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="submit" class="px-4 py-2 bg-danger-500 hover:bg-danger-600 rounded-lg font-medium transition-colors">
                        Activer
                    </button>
                    <button type="button" onclick="document.getElementById('enableMaintenanceModal').classList.add('hidden')" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Cache</p>
                    <p class="text-xl font-bold text-white">{{ $stats['cache_size'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Logs</p>
                    <p class="text-xl font-bold text-white">{{ $stats['log_size'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Storage</p>
                    <p class="text-xl font-bold text-white">{{ $stats['storage_size'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Base de données</p>
                    <p class="text-xl font-bold text-white">{{ $stats['database_size'] }}</p>
                </div>
                <svg class="w-10 h-10 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Informations Système</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-secondary-400">Version PHP</label>
                <p class="text-white mt-1 font-mono">{{ $stats['php_version'] }}</p>
            </div>
            <div>
                <label class="text-sm text-secondary-400">Version Laravel</label>
                <p class="text-white mt-1 font-mono">{{ $stats['laravel_version'] }}</p>
            </div>
            <div>
                <label class="text-sm text-secondary-400">Heure Serveur</label>
                <p class="text-white mt-1 font-mono">{{ $stats['server_time'] }}</p>
            </div>
            <div>
                <label class="text-sm text-secondary-400">Uptime</label>
                <p class="text-white mt-1 font-mono">{{ $stats['uptime'] }}</p>
            </div>
        </div>
    </div>

    <!-- Maintenance Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Cache Management -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Gestion du Cache
            </h3>
            <div class="space-y-2">
                <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="all">
                    <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                        Vider tous les caches
                    </button>
                </form>
                <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                        Cache configuration
                    </button>
                </form>
                <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="route">
                    <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                        Cache routes
                    </button>
                </form>
                <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="view">
                    <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                        Cache vues
                    </button>
                </form>
                <form action="{{ route('admin.maintenance.clear-cache') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="cache">
                    <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                        Cache applicatif
                    </button>
                </form>
            </div>
        </div>

        <!-- Optimization -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Optimisation
            </h3>
            <div class="space-y-2">
                <form action="{{ route('admin.maintenance.optimize') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors text-left">
                        Optimiser l'application
                    </button>
                </form>
                <p class="text-xs text-secondary-500 mt-2">
                    Cache la configuration, les routes et les vues pour de meilleures performances
                </p>
            </div>
        </div>

        <!-- Logs Management -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Gestion des Logs
            </h3>
            <form action="{{ route('admin.maintenance.clear-logs') }}" method="POST" onsubmit="return confirm('Supprimer tous les fichiers de logs?')">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-danger-500/20 text-danger-400 hover:bg-danger-500/30 rounded-lg transition-colors text-left">
                    Supprimer tous les logs
                </button>
            </form>
            <p class="text-xs text-secondary-500 mt-2">
                Supprime tous les fichiers .log du dossier storage/logs
            </p>
        </div>

        <!-- Database -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
                Base de Données
            </h3>
            <form action="{{ route('admin.maintenance.migrate') }}" method="POST" onsubmit="return confirm('Exécuter les migrations?')">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-warning-500/20 text-warning-400 hover:bg-warning-500/30 rounded-lg transition-colors text-left">
                    Exécuter migrations
                </button>
            </form>
            <p class="text-xs text-secondary-500 mt-2">
                Lance php artisan migrate
            </p>
        </div>

        <!-- Queue -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Files d'Attente
            </h3>
            <form action="{{ route('admin.maintenance.restart-queue') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left">
                    Redémarrer queue workers
                </button>
            </form>
            <p class="text-xs text-secondary-500 mt-2">
                Redémarre tous les workers de files d'attente
            </p>
        </div>
    </div>
</x-admin-layout>
