<x-admin-layout>
    <x-slot name="title">Backups</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Gestion des Backups</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.backups.settings') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Total Backups</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Complétés</p>
            <p class="text-2xl font-bold text-success-400">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Échoués</p>
            <p class="text-2xl font-bold text-danger-400">{{ $stats['failed'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Taille Totale</p>
            <p class="text-2xl font-bold text-white">
                @php
                    $totalSize = $stats['total_size'];
                    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $i = 0;
                    while ($totalSize >= 1024 && $i < count($units) - 1) {
                        $totalSize /= 1024;
                        $i++;
                    }
                @endphp
                {{ round($totalSize, 2) }} {{ $units[$i] }}
            </p>
        </div>
    </div>

    <!-- Backup Types Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Dernier Backup</p>
            <p class="text-white">
                @if($stats['last_backup'])
                    {{ $stats['last_backup']->diffForHumans() }}
                @else
                    Aucun
                @endif
            </p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Backups Database</p>
            <p class="text-2xl font-bold text-primary-400">{{ $stats['database_backups'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Backups Files</p>
            <p class="text-2xl font-bold text-warning-400">{{ $stats['files_backups'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Backups Complets</p>
            <p class="text-2xl font-bold text-success-400">{{ $stats['full_backups'] }}</p>
        </div>
    </div>

    <!-- Create Backup Form -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">Créer un Nouveau Backup</h3>
        <form action="{{ route('admin.backups.create') }}" method="POST" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label for="type" class="block text-sm font-medium text-secondary-300 mb-2">Type de Backup</label>
                <select id="type" name="type" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                    <option value="database">Base de données uniquement</option>
                    <option value="files">Fichiers uniquement</option>
                    <option value="full">Complet (Database + Fichiers)</option>
                </select>
            </div>
            <div class="flex-1">
                <label for="retention_days" class="block text-sm font-medium text-secondary-300 mb-2">Rétention (jours)</label>
                <input type="number" id="retention_days" name="retention_days" value="30" min="1" max="365" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Créer Backup
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.backups.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <select name="type" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les types</option>
                <option value="database" {{ request('type') === 'database' ? 'selected' : '' }}>Database</option>
                <option value="files" {{ request('type') === 'files' ? 'selected' : '' }}>Files</option>
                <option value="full" {{ request('type') === 'full' ? 'selected' : '' }}>Complet</option>
            </select>

            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complétés</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoués</option>
                <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>En cours</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
            </select>

            <select name="source" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les sources</option>
                <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manuel</option>
                <option value="automatic" {{ request('source') === 'automatic' ? 'selected' : '' }}>Automatique</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                Filtrer
            </button>

            @if(request()->hasAny(['type', 'status', 'source']))
                <a href="{{ route('admin.backups.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif

            <div class="ml-auto">
                <form action="{{ route('admin.backups.delete-expired') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-danger-500 hover:bg-danger-600 rounded-lg transition-colors" onclick="return confirm('Supprimer tous les backups expirés?')">
                        Nettoyer Expirés
                    </button>
                </form>
            </div>
        </form>
    </div>

    <!-- Backups Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Taille</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Durée</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Créé</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Expire</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($backups as $backup)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <p class="text-white font-mono text-sm">{{ $backup->name }}</p>
                            @if($backup->creator)
                                <p class="text-xs text-secondary-500">par {{ $backup->creator->full_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($backup->type === 'database') bg-primary-500/20 text-primary-400
                                @elseif($backup->type === 'files') bg-warning-500/20 text-warning-400
                                @else bg-success-500/20 text-success-400
                                @endif">
                                {{ ucfirst($backup->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-white">{{ $backup->formatted_size }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $backup->status_color }}-500/20 text-{{ $backup->status_color }}-400">
                                {{ ucfirst($backup->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($backup->is_automatic)
                                <span class="px-2 py-1 text-xs rounded-full bg-purple-500/20 text-purple-400">Auto</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-secondary-700 text-secondary-400">Manuel</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-white">{{ $backup->formatted_duration }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-secondary-400">{{ $backup->created_at->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($backup->expires_at)
                                <p class="text-sm text-secondary-400">{{ $backup->expires_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-secondary-500">{{ $backup->expires_at->diffForHumans() }}</p>
                            @else
                                <p class="text-sm text-secondary-500">-</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($backup->isCompleted() && $backup->fileExists())
                                    <a href="{{ route('admin.backups.download', $backup) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Télécharger">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                @endif
                                <form action="{{ route('admin.backups.destroy', $backup) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce backup?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-danger-400" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-secondary-500">
                            Aucun backup trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($backups->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $backups->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
