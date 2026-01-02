<x-firm-layout title="Equipe" header="Collaborateurs">

    <!-- Header with Invite button -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-secondary-400">{{ $members->count() }} collaborateur(s)</p>
        </div>
        <button @click="showInviteModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Inviter un collaborateur
        </button>
    </div>

    @if($members->count() > 0)
        <!-- Team Members Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($members as $member)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 hover:border-secondary-600 transition-colors">
                    <!-- Member Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <!-- Avatar with Initials -->
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center font-bold text-xl shadow-lg shadow-primary-500/20">
                                {{ $member->initials ?? strtoupper(substr($member->name ?? 'U', 0, 2)) }}
                            </div>

                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-white">{{ $member->full_name ?? $member->name }}</h3>
                                <p class="text-sm text-secondary-400">{{ $member->email }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($member->pivot->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-success-500/20 text-success-400">
                                Actif
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-secondary-600 text-secondary-400">
                                Inactif
                            </span>
                        @endif
                    </div>

                    <!-- Member Details -->
                    <div class="space-y-3 pt-4 border-t border-secondary-700">
                        <!-- Role -->
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs text-secondary-500">Role</p>
                                <p class="text-sm text-white capitalize">
                                    @switch($member->pivot->role)
                                        @case('owner')
                                            Proprietaire
                                            @break
                                        @case('admin')
                                            Administrateur
                                            @break
                                        @case('accountant')
                                            Comptable
                                            @break
                                        @case('assistant')
                                            Assistant
                                            @break
                                        @default
                                            {{ $member->pivot->role }}
                                    @endswitch
                                </p>
                            </div>
                        </div>

                        <!-- Joined Date -->
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs text-secondary-500">Membre depuis</p>
                                <p class="text-sm text-white">
                                    {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($member->pivot->role !== 'owner')
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-secondary-700">
                            <button
                                @click="editMember({{ $member->id }}, '{{ $member->pivot->role }}', {{ $member->pivot->is_active ? 'true' : 'false' }})"
                                class="flex-1 px-3 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm font-medium transition-colors"
                            >
                                Modifier
                            </button>
                            <button
                                @click="removeMember({{ $member->id }}, '{{ $member->full_name ?? $member->name }}')"
                                class="px-3 py-2 bg-danger-500/20 hover:bg-danger-500/30 text-danger-400 rounded-lg text-sm font-medium transition-colors"
                            >
                                Retirer
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-12 text-center">
            <div class="text-secondary-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-xl font-medium text-white">Aucun collaborateur</p>
                <p class="mt-2 text-secondary-400">Commencez par inviter des membres dans votre cabinet</p>
                <button @click="showInviteModal = true" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-primary-500 hover:bg-primary-600 rounded-lg text-white font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Inviter un collaborateur
                </button>
            </div>
        </div>
    @endif

    <!-- Invite Modal -->
    <div
        x-data="{ showInviteModal: false, showEditModal: false, editUserId: null, editRole: '', editIsActive: true }"
        @keydown.escape.window="showInviteModal = false; showEditModal = false"
    >
        <!-- Invite Modal -->
        <div
            x-show="showInviteModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div
                    x-show="showInviteModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showInviteModal = false"
                    class="fixed inset-0 bg-black/70 transition-opacity"
                ></div>

                <!-- Modal Content -->
                <div
                    x-show="showInviteModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-secondary-800 rounded-xl border border-secondary-700 shadow-xl max-w-md w-full p-6"
                >
                    <h3 class="text-xl font-semibold text-white mb-4">Inviter un collaborateur</h3>

                    <form action="{{ route('firm.team.invite') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-secondary-300 mb-2">
                                Adresse email
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="collaborateur@example.com"
                            >
                            <p class="mt-1 text-xs text-secondary-500">
                                Une invitation sera envoyee a cette adresse
                            </p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-secondary-300 mb-2">
                                Role
                            </label>
                            <select
                                id="role"
                                name="role"
                                required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="accountant">Comptable</option>
                                <option value="assistant">Assistant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-4">
                            <button
                                type="button"
                                @click="showInviteModal = false"
                                class="flex-1 px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors"
                            >
                                Envoyer l'invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            x-show="showEditModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div
                    x-show="showEditModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showEditModal = false"
                    class="fixed inset-0 bg-black/70 transition-opacity"
                ></div>

                <!-- Modal Content -->
                <div
                    x-show="showEditModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-secondary-800 rounded-xl border border-secondary-700 shadow-xl max-w-md w-full p-6"
                >
                    <h3 class="text-xl font-semibold text-white mb-4">Modifier le collaborateur</h3>

                    <form :action="`{{ route('firm.team.index') }}/${editUserId}`" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Role -->
                        <div>
                            <label for="edit_role" class="block text-sm font-medium text-secondary-300 mb-2">
                                Role
                            </label>
                            <select
                                id="edit_role"
                                name="role"
                                x-model="editRole"
                                required
                                class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="accountant">Comptable</option>
                                <option value="assistant">Assistant</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    x-model="editIsActive"
                                    class="rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500 focus:ring-offset-0"
                                >
                                <span class="text-sm font-medium text-secondary-300">Membre actif</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-4">
                            <button
                                type="button"
                                @click="showEditModal = false"
                                class="flex-1 px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg font-medium transition-colors"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editMember(userId, role, isActive) {
            const event = new CustomEvent('edit-member', {
                detail: { userId, role, isActive }
            });
            window.dispatchEvent(event);
        }

        function removeMember(userId, userName) {
            if (confirm(`Etes-vous sur de vouloir retirer ${userName} de votre cabinet ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('firm.team.index') }}/${userId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.addEventListener('edit-member', (event) => {
            const { userId, role, isActive } = event.detail;

            // Get the Alpine.js component
            const component = document.querySelector('[x-data]').__x;
            if (component) {
                component.$data.editUserId = userId;
                component.$data.editRole = role;
                component.$data.editIsActive = isActive;
                component.$data.showEditModal = true;
            }
        });
    </script>
    @endpush

</x-firm-layout>
