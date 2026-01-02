<x-admin-layout>
    <x-slot name="title">Erreurs Système</x-slot>
    <x-slot name="header">Erreurs Système</x-slot>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Total Erreurs</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-secondary-700 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-warning-500/20 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Non Résolues</p>
                    <p class="text-2xl font-bold text-warning-400">{{ $stats['unresolved'] }}</p>
                </div>
                <div class="w-12 h-12 bg-warning-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-danger-500/20 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Critiques</p>
                    <p class="text-2xl font-bold text-danger-400">{{ $stats['critical'] }}</p>
                </div>
                <div class="w-12 h-12 bg-danger-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Aujourd'hui</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['today'] }}</p>
                </div>
                <div class="w-12 h-12 bg-secondary-700 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary-400 text-sm">Cette Semaine</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['week'] }}</p>
                </div>
                <div class="w-12 h-12 bg-secondary-700 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.errors.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (message, fichier, exception)..." class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="severity" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les sévérités</option>
                <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critique</option>
                <option value="error" {{ request('severity') === 'error' ? 'selected' : '' }}>Erreur</option>
                <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Avertissement</option>
            </select>
            <select name="type" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="unresolved" {{ request('status') === 'unresolved' ? 'selected' : '' }}>Non résolues</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolues</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'severity', 'type', 'status']))
                <a href="{{ route('admin.errors.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex gap-2" x-data="{ selectedErrors: [] }">
            <button @click="if(selectedErrors.length > 0 && confirm('Résoudre les erreurs sélectionnées?')) { $refs.bulkResolveForm.submit() }" class="px-4 py-2 bg-success-500/20 text-success-400 hover:bg-success-500/30 rounded-lg transition-colors">
                Résoudre sélectionnées
            </button>
            <button @click="if(selectedErrors.length > 0 && confirm('Supprimer les erreurs sélectionnées?')) { $refs.bulkDeleteForm.submit() }" class="px-4 py-2 bg-danger-500/20 text-danger-400 hover:bg-danger-500/30 rounded-lg transition-colors">
                Supprimer sélectionnées
            </button>
            <form action="{{ route('admin.errors.cleanup') }}" method="POST" class="inline" onsubmit="return confirm('Supprimer toutes les erreurs résolues de plus de 30 jours?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Nettoyer anciennes
                </button>
            </form>
        </div>
    </div>

    <!-- Errors Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" class="rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Sévérité</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Occurrences</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Dernière Occurrence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($errors as $error)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-4 py-4">
                            <input type="checkbox" value="{{ $error->id }}" class="rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($error->severity === 'critical') bg-danger-500/20 text-danger-400
                                @elseif($error->severity === 'error') bg-warning-500/20 text-warning-400
                                @else bg-secondary-500/20 text-secondary-400
                                @endif">
                                {{ ucfirst($error->severity) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.errors.show', $error) }}" class="text-white hover:text-primary-400">
                                <p class="font-medium">{{ Str::limit($error->message, 60) }}</p>
                                @if($error->exception)
                                    <p class="text-xs text-secondary-500">{{ class_basename($error->exception) }}</p>
                                @endif
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">
                                {{ ucfirst($error->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs font-bold rounded-full {{ $error->occurrences > 10 ? 'bg-danger-500/20 text-danger-400' : 'bg-secondary-700 text-white' }}">
                                {{ $error->occurrences }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-400">
                            @if($error->last_occurred_at)
                                <span title="{{ $error->last_occurred_at->format('d/m/Y H:i:s') }}">
                                    {{ $error->last_occurred_at->diffForHumans() }}
                                </span>
                            @else
                                {{ $error->created_at->diffForHumans() }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($error->resolved)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">
                                    Résolue
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">
                                    Non résolue
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.errors.show', $error) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir détails">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(!$error->resolved)
                                    <form action="{{ route('admin.errors.resolve', $error) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-success-400" title="Marquer comme résolue">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.errors.destroy', $error) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette erreur?')">
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
                        <td colspan="8" class="px-6 py-12 text-center text-secondary-500">
                            Aucune erreur trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($errors->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $errors->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
