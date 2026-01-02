<x-admin-layout>
    <x-slot name="title">Logs d'Audit</x-slot>
    <x-slot name="header">Logs d'Audit</x-slot>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="action" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les actions</option>
                <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Création</option>
                <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Modification</option>
                <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Suppression</option>
                <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Connexion</option>
                <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Déconnexion</option>
                <option value="impersonate" {{ request('action') === 'impersonate' ? 'selected' : '' }}>Impersonnation</option>
            </select>
            <select name="user" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="Date début">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" placeholder="Date fin">
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'action', 'user', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Export Button -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg font-medium transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter CSV
        </a>
    </div>

    <!-- Logs Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">IP</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4 text-sm text-secondary-300">
                            <span title="{{ $log->created_at->format('d/m/Y H:i:s') }}">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->user)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full {{ $log->user->is_superadmin ? 'bg-danger-500' : 'bg-primary-500/20' }} flex items-center justify-center text-xs font-bold {{ $log->user->is_superadmin ? 'text-white' : 'text-primary-400' }}">
                                        {{ $log->user->initials }}
                                    </div>
                                    <a href="{{ route('admin.users.show', $log->user) }}" class="text-sm text-white hover:text-primary-400">
                                        {{ $log->user->full_name }}
                                    </a>
                                </div>
                            @else
                                <span class="text-sm text-secondary-500">Système</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $log->action_color }}-500/20 text-{{ $log->action_color }}-400">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-300 max-w-md truncate">
                            {{ $log->description }}
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-400">
                            @if($log->company)
                                <a href="{{ route('admin.companies.show', $log->company) }}" class="hover:text-primary-400">
                                    {{ $log->company->name }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-secondary-400">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.audit-logs.show', $log) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors inline-block" title="Détails">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-secondary-500">
                            Aucun log trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
