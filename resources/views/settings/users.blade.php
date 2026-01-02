<x-app-layout>
    <x-slot name="title">Paramètres - Utilisateurs</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('settings.company') }}" class="text-secondary-500 hover:text-secondary-700">Paramètres</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Utilisateurs</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Paramètres</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Configurez votre entreprise et vos préférences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:w-64 flex-shrink-0">
                <x-settings-nav active="users" />
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- Header with actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Utilisateurs</h2>
                        <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                            Gérez les utilisateurs ayant accès à {{ $company->name }}.
                        </p>
                    </div>
                    <button type="button" onclick="openInviteModal()" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Inviter un utilisateur
                    </button>
                </div>

                <!-- Users List -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
                            @forelse($users as $user)
                                <div class="flex items-center justify-between p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <!-- Avatar -->
                                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                            @if($user->avatar)
                                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->full_name }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <span class="text-primary-600 dark:text-primary-400 font-semibold">
                                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- User Info -->
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-secondary-900 dark:text-white">
                                                    {{ $user->full_name }}
                                                </span>
                                                @if($user->id === auth()->id())
                                                    <span class="badge badge-primary text-xs">Vous</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-secondary-500 dark:text-secondary-400">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <!-- Role Badge -->
                                        @php
                                            $role = $user->pivot->role ?? 'member';
                                            $roleLabels = [
                                                'owner' => 'Propriétaire',
                                                'admin' => 'Administrateur',
                                                'accountant' => 'Comptable',
                                                'member' => 'Membre',
                                            ];
                                            $roleColors = [
                                                'owner' => 'primary',
                                                'admin' => 'warning',
                                                'accountant' => 'success',
                                                'member' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge badge-{{ $roleColors[$role] ?? 'secondary' }}">
                                            {{ $roleLabels[$role] ?? ucfirst($role) }}
                                        </span>

                                        <!-- Actions -->
                                        @if($user->id !== auth()->id())
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" class="p-2 text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-cloak
                                                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-secondary-800 rounded-lg shadow-lg border border-secondary-200 dark:border-secondary-700 py-1 z-10">
                                                    <button type="button" onclick="changeRole('{{ $user->id }}', '{{ $user->full_name }}', '{{ $role }}')"
                                                            class="w-full text-left px-4 py-2 text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-100 dark:hover:bg-secondary-700">
                                                        Changer le rôle
                                                    </button>
                                                    <form action="{{ route('settings.users.remove', $user) }}" method="POST"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir retirer cet utilisateur de l\'entreprise ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-900/20">
                                                            Retirer de l'entreprise
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <div class="w-16 h-16 mx-auto bg-secondary-100 dark:bg-secondary-800 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-1">Aucun utilisateur</h3>
                                    <p class="text-secondary-500 dark:text-secondary-400">
                                        Invitez des utilisateurs pour collaborer.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations -->
                @if(isset($pendingInvitations) && $pendingInvitations->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Invitations en attente</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="divide-y divide-secondary-200 dark:divide-secondary-700">
                            @foreach($pendingInvitations as $invitation)
                                <div class="flex items-center justify-between p-4">
                                    <div>
                                        <div class="font-medium text-secondary-900 dark:text-white">{{ $invitation->email }}</div>
                                        <div class="text-sm text-secondary-500">
                                            Invité le {{ $invitation->created_at->format('d/m/Y') }}
                                            @if($invitation->expires_at->isPast())
                                                <span class="text-danger-500">(Expiré)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <form action="{{ route('settings.invitations.cancel', $invitation) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-danger-600 hover:text-danger-700">
                                            Annuler
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Roles Description -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Rôles et permissions</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="badge badge-primary mt-0.5">Propriétaire</span>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    Accès complet à toutes les fonctionnalités. Peut gérer les utilisateurs, la facturation et supprimer l'entreprise.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="badge badge-warning mt-0.5">Administrateur</span>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    Peut gérer les utilisateurs, configurer l'entreprise et accéder à toutes les données.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="badge badge-success mt-0.5">Comptable</span>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    Peut créer des factures, gérer les partenaires et accéder aux rapports financiers.
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="badge badge-secondary mt-0.5">Membre</span>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    Accès en lecture seule. Peut voir les factures et les partenaires.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite User Modal -->
    <div x-data="{ show: false }" x-show="show" x-cloak @open-invite-modal.window="show = true" @close-modal.window="show = false"
         class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50" @click="show = false"></div>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Inviter un utilisateur</h3>

                <form action="{{ route('settings.users.invite') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="invite_email" class="form-label">Adresse email</label>
                            <input type="email" name="email" id="invite_email" required class="form-input" placeholder="email@exemple.com">
                        </div>
                        <div>
                            <label for="invite_role" class="form-label">Rôle</label>
                            <select name="role" id="invite_role" class="form-select">
                                <option value="member">Membre</option>
                                <option value="accountant">Comptable</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="show = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Envoyer l'invitation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Role Modal -->
    <div x-data="{ show: false, userId: '', userName: '', currentRole: '' }"
         x-show="show" x-cloak
         @open-role-modal.window="show = true; userId = $event.detail.userId; userName = $event.detail.userName; currentRole = $event.detail.currentRole"
         @close-modal.window="show = false"
         class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50" @click="show = false"></div>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">
                    Changer le rôle de <span x-text="userName"></span>
                </h3>

                <form :action="'/settings/users/' + userId + '/role'" method="POST">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="new_role" class="form-label">Nouveau rôle</label>
                        <select name="role" id="new_role" class="form-select" x-model="currentRole">
                            <option value="member">Membre</option>
                            <option value="accountant">Comptable</option>
                            <option value="admin">Administrateur</option>
                            <option value="owner">Propriétaire</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="show = false" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openInviteModal() {
            window.dispatchEvent(new CustomEvent('open-invite-modal'));
        }

        function changeRole(userId, userName, currentRole) {
            window.dispatchEvent(new CustomEvent('open-role-modal', {
                detail: { userId, userName, currentRole }
            }));
        }
    </script>
    @endpush
</x-app-layout>
