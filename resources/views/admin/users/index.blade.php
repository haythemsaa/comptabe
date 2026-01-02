<x-admin-layout>
    <x-slot name="title">Gestion des Utilisateurs</x-slot>
    <x-slot name="header">Utilisateurs</x-slot>

    <!-- Filters -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-4 mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher (nom, email)..." class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <select name="role" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>Superadmins</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Utilisateurs</option>
            </select>
            <select name="company" class="bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                <option value="">Toutes les entreprises</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors">
                Rechercher
            </button>
            @if(request()->hasAny(['search', 'role', 'company']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Actions -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Nouvel Utilisateur
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Entreprise</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Rôles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Dernière Connexion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-secondary-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-secondary-700">
                @forelse($users as $user)
                    <tr class="hover:bg-secondary-700/50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full {{ $user->is_superadmin ? 'bg-danger-500' : 'bg-primary-500/20' }} flex items-center justify-center font-bold {{ $user->is_superadmin ? 'text-white' : 'text-primary-400' }}">
                                    {{ $user->initials }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-white hover:text-primary-400">
                                        {{ $user->full_name }}
                                    </a>
                                    <p class="text-sm text-secondary-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->companies->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->companies->take(2) as $company)
                                        <a href="{{ route('admin.companies.show', $company) }}" class="text-sm text-secondary-300 hover:text-primary-400">
                                            {{ $company->name }}
                                        </a>
                                        @if(!$loop->last), @endif
                                    @endforeach
                                    @if($user->companies->count() > 2)
                                        <span class="text-xs text-secondary-500">+{{ $user->companies->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-secondary-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($user->is_superadmin)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">Superadmin</span>
                                @endif
                                @foreach($user->companies as $company)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">
                                        {{ ucfirst($company->pivot->role) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-secondary-400">
                            @if($user->last_login_at)
                                <span title="{{ $user->last_login_at->format('d/m/Y H:i') }}">
                                    {{ $user->last_login_at->diffForHumans() }}
                                </span>
                            @else
                                <span class="text-secondary-500">Jamais</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->email_verified_at)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">Vérifié</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-warning-500/20 text-warning-400">Non vérifié</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-warning-400" title="Impersonner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @if(!$user->is_superadmin)
                                        <form action="{{ route('admin.users.toggle-superadmin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Promouvoir cet utilisateur en superadmin?')">
                                            @csrf
                                            <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-danger-400" title="Promouvoir Superadmin">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.toggle-superadmin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Retirer les droits superadmin?')">
                                            @csrf
                                            <button type="submit" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-secondary-400" title="Retirer Superadmin">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016zM12 9v2m0 4h.01"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-secondary-500">
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-secondary-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
