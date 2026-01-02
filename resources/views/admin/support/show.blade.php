<x-admin-layout>
    <x-slot name="title">Ticket {{ $ticket->ticket_number }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.support.index') }}" class="p-2 hover:bg-secondary-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <span>Ticket {{ $ticket->ticket_number }}</span>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Info -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="mb-4">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-white mb-2">{{ $ticket->subject }}</h2>
                            <div class="flex items-center gap-2 text-sm text-secondary-400">
                                <span>Créé {{ $ticket->created_at->diffForHumans() }}</span>
                                @if($ticket->company)
                                    <span>•</span>
                                    <span>{{ $ticket->company->name }}</span>
                                @endif
                                @if($ticket->user)
                                    <span>•</span>
                                    <span>{{ $ticket->user->full_name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 text-xs rounded-full bg-{{ $ticket->status_color }}-500/20 text-{{ $ticket->status_color }}-400">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <span class="px-3 py-1 text-xs rounded-full bg-{{ $ticket->priority_color }}-500/20 text-{{ $ticket->priority_color }}-400">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="prose prose-invert max-w-none">
                    <p class="text-secondary-300">{{ $ticket->description }}</p>
                </div>

                @if($ticket->resolution_note)
                    <div class="mt-6 p-4 bg-success-500/10 border border-success-500/30 rounded-lg">
                        <p class="text-sm font-medium text-success-400 mb-2">Note de résolution</p>
                        <p class="text-secondary-300">{{ $ticket->resolution_note }}</p>
                    </div>
                @endif
            </div>

            <!-- Messages -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Conversation</h3>

                <div class="space-y-4 mb-6">
                    @forelse($ticket->messages as $message)
                        <div class="flex gap-4 {{ $message->is_internal_note ? 'bg-warning-500/10 p-4 rounded-lg border border-warning-500/30' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-primary-500 flex items-center justify-center text-white font-bold">
                                    {{ substr($message->user->full_name ?? 'U', 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="font-medium text-white">{{ $message->user->full_name ?? 'Utilisateur' }}</p>
                                        <p class="text-xs text-secondary-400">{{ $message->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if($message->is_internal_note)
                                        <span class="px-2 py-1 text-xs rounded-full bg-warning-500/20 text-warning-400">
                                            Note interne
                                        </span>
                                    @endif
                                </div>
                                <p class="text-secondary-300">{{ $message->message }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-secondary-500 py-8">Aucun message pour le moment</p>
                    @endforelse
                </div>

                <!-- Add Message Form -->
                <form action="{{ route('admin.support.add-message', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="message" class="block text-sm font-medium text-secondary-300 mb-2">Ajouter un message</label>
                        <textarea id="message" name="message" rows="4" required class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500" placeholder="Votre message..."></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-secondary-300">
                            <input type="checkbox" name="is_internal_note" value="1" class="rounded bg-secondary-700 border-secondary-600 text-warning-500 focus:ring-warning-500">
                            <span>Note interne (visible uniquement par les admins)</span>
                        </label>

                        <button type="submit" class="px-6 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                            Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Update Status -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Statut</h3>
                <form action="{{ route('admin.support.update-status', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <select name="status" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Ouvert</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>En cours</option>
                            <option value="waiting_customer" {{ $ticket->status === 'waiting_customer' ? 'selected' : '' }}>En attente client</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Résolu</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Fermé</option>
                        </select>
                    </div>

                    <div>
                        <label for="resolution_note" class="block text-sm font-medium text-secondary-300 mb-2">Note de résolution (optionnel)</label>
                        <textarea id="resolution_note" name="resolution_note" rows="3" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white placeholder-secondary-400 focus:border-primary-500 focus:ring-primary-500">{{ $ticket->resolution_note }}</textarea>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                        Mettre à jour
                    </button>
                </form>
            </div>

            <!-- Update Priority -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Priorité</h3>
                <form action="{{ route('admin.support.update-priority', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    <select name="priority" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" onchange="this.form.submit()">
                        <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Basse</option>
                        <option value="normal" {{ $ticket->priority === 'normal' ? 'selected' : '' }}>Normale</option>
                        <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>Haute</option>
                        <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </form>
            </div>

            <!-- Assign Ticket -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Assignation</h3>
                <form action="{{ route('admin.support.assign', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    <select name="assigned_to" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white focus:border-primary-500 focus:ring-primary-500" onchange="this.form.submit()">
                        <option value="">Non assigné</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                {{ $admin->full_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Ticket Info -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Informations</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-secondary-400">Catégorie</dt>
                        <dd class="text-white">{{ ucfirst($ticket->category) }}</dd>
                    </div>
                    <div>
                        <dt class="text-secondary-400">Créé le</dt>
                        <dd class="text-white">{{ $ticket->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($ticket->first_response_at)
                        <div>
                            <dt class="text-secondary-400">Première réponse</dt>
                            <dd class="text-white">{{ $ticket->first_response_at->format('d/m/Y H:i') }}</dd>
                            <dd class="text-xs text-success-400">{{ $ticket->response_time }}h</dd>
                        </div>
                    @endif
                    @if($ticket->resolved_at)
                        <div>
                            <dt class="text-secondary-400">Résolu le</dt>
                            <dd class="text-white">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</dd>
                            <dd class="text-xs text-success-400">{{ $ticket->resolution_time }}h</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-admin-layout>
