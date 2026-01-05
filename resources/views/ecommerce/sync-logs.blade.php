<x-app-layout>
    <x-slot name="title">Logs de synchronisation</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('ecommerce.connections') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">E-commerce</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Logs</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Logs de synchronisation</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Historique des synchronisations e-commerce</p>
            </div>
            <a href="{{ route('ecommerce.connections') }}" class="btn btn-outline-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Total syncs</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $logs->total() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Reussies</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $successCount }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-danger-100 text-danger-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Echouees</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $failedCount }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-warning-100 text-warning-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Derniere sync</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $lastSync ? $lastSync->diffForHumans() : 'Jamais' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Connexion</label>
                    <select name="connection" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($connections as $conn)
                            <option value="{{ $conn->id }}" {{ request('connection') == $conn->id ? 'selected' : '' }}>{{ $conn->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="orders" {{ request('type') == 'orders' ? 'selected' : '' }}>Commandes</option>
                        <option value="products" {{ request('type') == 'products' ? 'selected' : '' }}>Produits</option>
                        <option value="customers" {{ request('type') == 'customers' ? 'selected' : '' }}>Clients</option>
                        <option value="stock" {{ request('type') == 'stock' ? 'selected' : '' }}>Stock</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Reussi</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partiel</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Echoue</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['connection', 'type', 'status']))
                    <a href="{{ route('ecommerce.sync-logs') }}" class="btn btn-outline-secondary">Reinitialiser</a>
                @endif
            </form>
        </div>

        <!-- Liste des logs -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Connexion</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Traites</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Crees</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Erreurs</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Duree</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-800 dark:text-white">
                                    {{ $log->connection?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeLabels = [
                                            'orders' => 'Commandes',
                                            'products' => 'Produits',
                                            'customers' => 'Clients',
                                            'stock' => 'Stock',
                                            'full' => 'Complete',
                                        ];
                                    @endphp
                                    <span class="badge badge-secondary">{{ $typeLabels[$log->sync_type] ?? $log->sync_type }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($log->status == 'success')
                                        <span class="badge badge-success">Reussi</span>
                                    @elseif($log->status == 'partial')
                                        <span class="badge badge-warning">Partiel</span>
                                    @elseif($log->status == 'failed')
                                        <span class="badge badge-danger">Echoue</span>
                                    @else
                                        <span class="badge badge-info">En cours</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-secondary-600 dark:text-secondary-400">
                                    {{ $log->items_processed ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-success-600">
                                    {{ $log->items_created ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-danger-600">
                                    {{ $log->items_failed ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    @if($log->duration_seconds)
                                        {{ $log->duration_seconds }}s
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($log->error_message || $log->details)
                                        <button type="button" onclick="showLogDetails({{ json_encode($log) }})" class="text-secondary-500 hover:text-primary-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-secondary-500">Aucun log de synchronisation</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Details -->
    <div id="log-detail-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('log-detail-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-2xl w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Details du log</h3>
                <div id="log-detail-content" class="space-y-4">
                    <!-- Content filled by JS -->
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('log-detail-modal').classList.add('hidden')" class="btn btn-outline-secondary">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showLogDetails(log) {
            let content = '';

            if (log.error_message) {
                content += `
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                        <h4 class="font-medium text-danger-700 dark:text-danger-400 mb-2">Erreur</h4>
                        <p class="text-sm text-danger-600 dark:text-danger-300">${log.error_message}</p>
                    </div>
                `;
            }

            if (log.details) {
                let details = typeof log.details === 'string' ? JSON.parse(log.details) : log.details;
                content += `
                    <div class="p-4 bg-secondary-50 dark:bg-secondary-700 rounded-lg">
                        <h4 class="font-medium text-secondary-700 dark:text-secondary-300 mb-2">Details</h4>
                        <pre class="text-xs text-secondary-600 dark:text-secondary-400 overflow-auto max-h-64">${JSON.stringify(details, null, 2)}</pre>
                    </div>
                `;
            }

            content += `
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-secondary-500">Debut:</span>
                        <span class="text-secondary-800 dark:text-white ml-2">${new Date(log.started_at || log.created_at).toLocaleString('fr-BE')}</span>
                    </div>
                    <div>
                        <span class="text-secondary-500">Fin:</span>
                        <span class="text-secondary-800 dark:text-white ml-2">${log.completed_at ? new Date(log.completed_at).toLocaleString('fr-BE') : '-'}</span>
                    </div>
                </div>
            `;

            document.getElementById('log-detail-content').innerHTML = content;
            document.getElementById('log-detail-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
