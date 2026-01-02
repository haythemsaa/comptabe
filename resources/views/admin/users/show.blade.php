<x-admin-layout>
    <x-slot name="title">{{ $user->full_name }}</x-slot>
    <x-slot name="header">Détails Utilisateur</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 rounded-full {{ $user->is_superadmin ? 'bg-danger-500' : 'bg-primary-500/20' }} flex items-center justify-center text-2xl font-bold {{ $user->is_superadmin ? 'text-white' : 'text-primary-400' }}">
                        {{ $user->initials }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-2xl font-bold">{{ $user->full_name }}</h2>
                            @if($user->is_superadmin)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-danger-500/20 text-danger-400">Superadmin</span>
                            @endif
                        </div>
                        <p class="text-secondary-400">{{ $user->email }}</p>
                        @if($user->companies->count() > 0)
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach($user->companies as $company)
                                    <a href="{{ route('admin.companies.show', $company) }}" class="text-primary-400 hover:text-primary-300 text-sm">
                                        {{ $company->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                            Modifier
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.impersonate', $user) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-warning-500 hover:bg-warning-600 rounded-lg transition-colors">
                                    Impersonner
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Informations</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-secondary-400">Prénom</dt>
                        <dd class="font-medium">{{ $user->first_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Nom</dt>
                        <dd class="font-medium">{{ $user->last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Email</dt>
                        <dd class="font-medium">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Statut Email</dt>
                        <dd>
                            @if($user->email_verified_at)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">
                                    Vérifié le {{ $user->email_verified_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-warning-500/20 text-warning-400">Non vérifié</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Créé le</dt>
                        <dd class="font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-secondary-400">Modifié le</dt>
                        <dd class="font-medium">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Roles & Entreprises -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Roles & Entreprises</h3>

                <div class="mb-4">
                    <h4 class="text-sm text-secondary-400 mb-2">Statut Global</h4>
                    <div class="flex flex-wrap gap-2">
                        @if($user->is_superadmin)
                            <span class="px-3 py-1 bg-danger-500/20 text-danger-400 rounded-full text-sm font-medium">Superadmin</span>
                        @endif
                        @if($user->is_active)
                            <span class="px-3 py-1 bg-success-500/20 text-success-400 rounded-full text-sm font-medium">Actif</span>
                        @else
                            <span class="px-3 py-1 bg-secondary-500/20 text-secondary-400 rounded-full text-sm font-medium">Inactif</span>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-sm text-secondary-400 mb-2">Acces Entreprises</h4>
                    @forelse($user->companies as $company)
                        <div class="flex items-center justify-between p-3 bg-secondary-700 rounded-lg mb-2">
                            <div>
                                <a href="{{ route('admin.companies.show', $company) }}" class="font-medium text-white hover:text-primary-400">
                                    {{ $company->name }}
                                </a>
                                @if($company->pivot->is_default)
                                    <span class="ml-2 text-xs text-primary-400">(defaut)</span>
                                @endif
                            </div>
                            <span class="px-2 py-1 bg-primary-500/20 text-primary-400 rounded text-sm">
                                {{ ucfirst($company->pivot->role) }}
                            </span>
                        </div>
                    @empty
                        <span class="text-secondary-500">Aucune entreprise assignee</span>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Activité Récente</h3>
                @forelse($recentActivity as $log)
                    <div class="flex items-start gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-secondary-700' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-{{ $log->action_color }}-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="w-2 h-2 rounded-full bg-{{ $log->action_color }}-400"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white">{{ $log->description }}</p>
                            <p class="text-xs text-secondary-500">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-secondary-500 text-center py-4">Aucune activité récente</p>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Stats -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Statistiques</h3>
                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Dernière connexion</dt>
                        <dd class="font-medium">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->diffForHumans() }}
                            @else
                                <span class="text-secondary-500">Jamais</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Dernière IP</dt>
                        <dd class="font-mono text-sm">{{ $user->last_login_ip ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-secondary-400">Factures créées</dt>
                        <dd class="font-medium">{{ number_format($user->invoices()->count()) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 border-b border-secondary-700 pb-4">Actions Rapides</h3>
                <div class="space-y-3">
                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left flex items-center gap-3">
                            <svg class="w-5 h-5 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            <span>Réinitialiser mot de passe</span>
                        </button>
                    </form>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle-superadmin', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left flex items-center gap-3" onclick="return confirm('{{ $user->is_superadmin ? 'Retirer les droits superadmin?' : 'Promouvoir en superadmin?' }}')">
                                <svg class="w-5 h-5 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>{{ $user->is_superadmin ? 'Retirer Superadmin' : 'Promouvoir Superadmin' }}</span>
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.audit-logs.index', ['user' => $user->id]) }}" class="w-full px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg transition-colors text-left flex items-center gap-3 block">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Voir tous les logs</span>
                    </a>
                </div>
            </div>

            <!-- Back -->
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 text-secondary-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à la liste
            </a>
        </div>
    </div>
</x-admin-layout>
