<x-admin-layout>
    <x-slot name="title">Sante Systeme</x-slot>
    <x-slot name="header">Sante du Systeme</x-slot>

    <!-- Overall Status -->
    <div class="mb-6 p-6 rounded-xl border {{ $overallStatus === 'ok' ? 'bg-success-500/10 border-success-500/30' : ($overallStatus === 'warning' ? 'bg-warning-500/10 border-warning-500/30' : 'bg-danger-500/10 border-danger-500/30') }}">
        <div class="flex items-center gap-4">
            @if($overallStatus === 'ok')
                <div class="w-16 h-16 bg-success-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-success-400">Systeme Operationnel</h2>
                    <p class="text-secondary-400">Tous les services fonctionnent normalement</p>
                </div>
            @elseif($overallStatus === 'warning')
                <div class="w-16 h-16 bg-warning-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-warning-400">Attention Requise</h2>
                    <p class="text-secondary-400">Certains services necessitent votre attention</p>
                </div>
            @else
                <div class="w-16 h-16 bg-danger-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-danger-400">Problemes Detectes</h2>
                    <p class="text-secondary-400">Des erreurs critiques necessitent une intervention</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Health Checks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($checks as $name => $check)
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 {{ $check['status'] === 'ok' ? 'bg-success-500/20' : ($check['status'] === 'warning' ? 'bg-warning-500/20' : 'bg-danger-500/20') }}">
                        @if($check['status'] === 'ok')
                            <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif($check['status'] === 'warning')
                            <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium capitalize">{{ str_replace('_', ' ', $name) }}</h3>
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $check['status'] === 'ok' ? 'bg-success-500/20 text-success-400' : ($check['status'] === 'warning' ? 'bg-warning-500/20 text-warning-400' : 'bg-danger-500/20 text-danger-400') }}">
                                {{ $check['status'] === 'ok' ? 'OK' : ($check['status'] === 'warning' ? 'Attention' : 'Erreur') }}
                            </span>
                        </div>
                        <p class="text-sm text-secondary-300 mt-1">{{ $check['message'] }}</p>
                        <p class="text-xs text-secondary-500 mt-1">{{ $check['details'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- System Info & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- System Info -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Informations Systeme</h3>
            <dl class="space-y-3">
                <div class="flex justify-between py-2 border-b border-secondary-700">
                    <dt class="text-secondary-400">Hostname</dt>
                    <dd class="font-mono text-sm">{{ $systemInfo['hostname'] }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-secondary-700">
                    <dt class="text-secondary-400">Serveur Web</dt>
                    <dd class="font-mono text-sm">{{ $systemInfo['server_software'] }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-secondary-700">
                    <dt class="text-secondary-400">PHP SAPI</dt>
                    <dd class="font-mono text-sm">{{ $systemInfo['php_sapi'] }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-secondary-700">
                    <dt class="text-secondary-400">Extensions chargees</dt>
                    <dd class="font-mono text-sm">{{ $systemInfo['loaded_extensions'] }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-secondary-400">Uptime</dt>
                    <dd class="font-mono text-sm">{{ $systemInfo['uptime'] }}</dd>
                </div>
            </dl>
        </div>

        <!-- Quick Actions -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4">Actions Rapides</h3>
            <div class="space-y-3">
                <form action="{{ route('admin.settings.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Vider le cache</span>
                        </div>
                        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>

                <a href="{{ route('admin.system.logs') }}" class="w-full flex items-center justify-between px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Voir les logs</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.system.phpinfo') }}" class="w-full flex items-center justify-between px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>PHP Info</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <a href="{{ route('admin.exports.index') }}" class="w-full flex items-center justify-between px-4 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Exporter des donnees</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Errors -->
    @if(count($recentErrors) > 0)
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Erreurs Recentes
            </h3>
            <div class="space-y-3">
                @foreach($recentErrors as $error)
                    <div class="p-3 bg-danger-500/10 border border-danger-500/30 rounded-lg">
                        <div class="flex items-start gap-3">
                            <span class="text-xs text-danger-400 whitespace-nowrap">{{ $error['date'] }}</span>
                            <p class="text-sm text-danger-300 flex-1">{{ $error['message'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('admin.system.logs') }}" class="text-primary-400 hover:text-primary-300 text-sm">
                    Voir tous les logs &rarr;
                </a>
            </div>
        </div>
    @endif
</x-admin-layout>
