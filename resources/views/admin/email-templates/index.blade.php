<x-admin-layout>
    <x-slot name="title">Templates Email</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span>Templates Email</span>
            <div class="flex gap-2">
                <form action="{{ route('admin.email-templates.seed-defaults') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                        Charger templates par défaut
                    </button>
                </form>
                <a href="{{ route('admin.email-templates.create') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau Template
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Total</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Système</p>
            <p class="text-2xl font-bold text-primary-400">{{ $stats['system'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Personnalisés</p>
            <p class="text-2xl font-bold text-success-400">{{ $stats['custom'] }}</p>
        </div>
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4">
            <p class="text-secondary-400 text-sm">Actifs</p>
            <p class="text-2xl font-bold text-white">{{ $stats['active'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.email-templates.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="flex-1 min-w-[200px] bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">

            <select name="category" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                @endforeach
            </select>

            <select name="type" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les types</option>
                <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Système</option>
                <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Personnalisés</option>
            </select>

            <select name="status" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                Rechercher
            </button>

            @if(request()->hasAny(['search', 'category', 'type', 'status']))
                <a href="{{ route('admin.email-templates.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Templates Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Template</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Modifié</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($templates as $template)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-white">{{ $template->display_name }}</p>
                                <p class="text-sm text-secondary-500">{{ $template->subject }}</p>
                                <p class="text-xs text-secondary-600 font-mono">{{ $template->name }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($template->category)
                                <span class="px-2 py-1 text-xs rounded-full bg-primary-500/20 text-primary-400">
                                    {{ ucfirst($template->category) }}
                                </span>
                            @else
                                <span class="text-secondary-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($template->is_system)
                                <span class="px-2 py-1 text-xs rounded-full bg-warning-500/20 text-warning-400">Système</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-success-500/20 text-success-400">Personnalisé</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-secondary-400">{{ $template->updated_at->diffForHumans() }}</p>
                            @if($template->lastModifiedBy)
                                <p class="text-xs text-secondary-500">par {{ $template->lastModifiedBy->full_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($template->is_active)
                                <span class="px-2 py-1 text-xs rounded-full bg-success-500/20 text-success-400">Actif</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-secondary-700 text-secondary-400">Inactif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.email-templates.show', $template) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.email-templates.edit', $template) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.email-templates.duplicate', $template) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Dupliquer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </form>
                                @if(!$template->is_system)
                                    <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce template?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-danger-400" title="Supprimer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-secondary-500">
                            <p class="mb-4">Aucun template trouvé</p>
                            <form action="{{ route('admin.email-templates.seed-defaults') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                                    Charger les templates par défaut
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
