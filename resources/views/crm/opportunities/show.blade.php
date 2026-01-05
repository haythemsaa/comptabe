<x-app-layout>
    <x-slot name="title">{{ $opportunity->title }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('crm.pipeline') }}" class="text-secondary-500 hover:text-secondary-700">CRM</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ Str::limit($opportunity->title, 30) }}</span>
    @endsection

    <div class="space-y-6" x-data="{ showActivityModal: false, activityType: 'call' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 text-sm rounded-full bg-{{ $opportunity->getStageColor() }}-100 dark:bg-{{ $opportunity->getStageColor() }}-900/30 text-{{ $opportunity->getStageColor() }}-700 dark:text-{{ $opportunity->getStageColor() }}-300 font-medium">
                        {{ $opportunity->getStageLabel() }}
                    </span>
                    @if($opportunity->isOverdue())
                    <span class="px-2 py-1 text-xs bg-danger-100 text-danger-700 rounded">En retard</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $opportunity->title }}</h1>
                @if($opportunity->partner)
                <a href="{{ route('partners.show', $opportunity->partner) }}" class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600">
                    {{ $opportunity->partner->name }}
                </a>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @if($opportunity->isOpen())
                <button @click="showActivityModal = true" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Activité
                </button>
                <form action="{{ route('crm.opportunities.mark-won', $opportunity) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Marquer comme gagnée ?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Gagnée
                    </button>
                </form>
                <button type="button" class="btn btn-danger" x-data x-on:click="$dispatch('open-lost-modal')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Perdue
                </button>
                @endif
                <a href="{{ route('crm.opportunities.edit', $opportunity) }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Key Metrics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="card p-4 text-center">
                        <p class="text-sm text-secondary-500">Montant</p>
                        <p class="text-xl font-bold text-secondary-900 dark:text-white">{{ number_format($opportunity->amount, 0, ',', ' ') }} EUR</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="text-sm text-secondary-500">Probabilité</p>
                        <p class="text-xl font-bold text-{{ $opportunity->probability >= 50 ? 'success' : 'warning' }}-600">{{ $opportunity->probability }}%</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="text-sm text-secondary-500">Pondéré</p>
                        <p class="text-xl font-bold text-primary-600">{{ number_format($opportunity->getWeightedAmount(), 0, ',', ' ') }} EUR</p>
                    </div>
                    <div class="card p-4 text-center">
                        <p class="text-sm text-secondary-500">Clôture</p>
                        <p class="text-xl font-bold {{ $opportunity->isOverdue() ? 'text-danger-600' : 'text-secondary-900 dark:text-white' }}">
                            {{ $opportunity->expected_close_date?->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                @if($opportunity->description)
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Description</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-secondary-600 dark:text-secondary-400 whitespace-pre-wrap">{{ $opportunity->description }}</p>
                    </div>
                </div>
                @endif

                <!-- Activities -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Activités</h3>
                        <button @click="showActivityModal = true" class="btn btn-sm btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    </div>
                    <div class="card-body">
                        @forelse($opportunity->activities as $activity)
                        <div class="flex gap-4 py-4 {{ !$loop->last ? 'border-b border-secondary-100 dark:border-secondary-700' : '' }}">
                            <div class="w-10 h-10 bg-{{ $activity->getTypeColor() }}-100 dark:bg-{{ $activity->getTypeColor() }}-900/30 rounded-lg flex items-center justify-center flex-shrink-0 {{ $activity->isCompleted() ? 'opacity-50' : '' }}">
                                <svg class="w-5 h-5 text-{{ $activity->getTypeColor() }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($activity->type === 'call')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    @elseif($activity->type === 'email')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    @elseif($activity->type === 'meeting')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    @endif
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-secondary-900 dark:text-white {{ $activity->isCompleted() ? 'line-through opacity-50' : '' }}">
                                            {{ $activity->subject }}
                                        </p>
                                        <p class="text-sm text-secondary-500">
                                            {{ $activity->getTypeLabel() }}
                                            @if($activity->due_date)
                                            - {{ $activity->due_date->format('d/m/Y H:i') }}
                                            @endif
                                            @if($activity->assignedTo)
                                            - {{ $activity->assignedTo->first_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($activity->isPending())
                                        <form action="{{ route('crm.activities.toggle', $activity) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-ghost text-success-600" title="Marquer comme terminée">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-xs text-success-600">Terminée</span>
                                        @endif
                                    </div>
                                </div>
                                @if($activity->description)
                                <p class="mt-2 text-sm text-secondary-600 dark:text-secondary-400">{{ $activity->description }}</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-secondary-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p>Aucune activité enregistrée</p>
                            <button @click="showActivityModal = true" class="mt-2 text-primary-600 hover:underline">Ajouter une activité</button>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Stage History -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Historique</h3>
                    </div>
                    <div class="card-body">
                        <div class="relative">
                            <div class="absolute left-4 top-0 bottom-0 w-px bg-secondary-200 dark:bg-secondary-700"></div>
                            @foreach($opportunity->stageHistory as $history)
                            <div class="relative flex gap-4 pb-4">
                                <div class="w-8 h-8 bg-white dark:bg-secondary-800 border-2 border-secondary-200 dark:border-secondary-700 rounded-full flex items-center justify-center z-10">
                                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-secondary-900 dark:text-white">
                                        {{ $history->getFromStageLabel() }} <span class="text-secondary-400">→</span> <strong>{{ $history->getToStageLabel() }}</strong>
                                    </p>
                                    <p class="text-xs text-secondary-500">
                                        {{ $history->created_at->format('d/m/Y H:i') }}
                                        @if($history->changedBy)
                                        par {{ $history->changedBy->first_name }}
                                        @endif
                                    </p>
                                    @if($history->notes)
                                    <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">{{ $history->notes }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Details Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Détails</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <p class="text-sm text-secondary-500">Source</p>
                            <p class="font-medium text-secondary-900 dark:text-white">{{ $opportunity->getSourceLabel() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-500">Responsable</p>
                            <p class="font-medium text-secondary-900 dark:text-white">{{ $opportunity->assignedTo?->full_name ?? 'Non assigné' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-secondary-500">Créée le</p>
                            <p class="font-medium text-secondary-900 dark:text-white">{{ $opportunity->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($opportunity->createdBy)
                        <div>
                            <p class="text-sm text-secondary-500">Créée par</p>
                            <p class="font-medium text-secondary-900 dark:text-white">{{ $opportunity->createdBy->full_name }}</p>
                        </div>
                        @endif
                        @if($opportunity->lost_reason)
                        <div class="p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                            <p class="text-sm text-danger-600 font-medium">Raison de perte</p>
                            <p class="text-danger-700 dark:text-danger-300">{{ $opportunity->lost_reason }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Notes Card -->
                @if($opportunity->notes)
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Notes</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-secondary-600 dark:text-secondary-400 whitespace-pre-wrap">{{ $opportunity->notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Actions Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Actions rapides</h3>
                    </div>
                    <div class="card-body space-y-2">
                        @if($opportunity->partner)
                        <a href="{{ route('invoices.create', ['partner' => $opportunity->partner_id]) }}" class="btn btn-secondary w-full justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Créer une facture
                        </a>
                        <a href="{{ route('quotes.create', ['partner' => $opportunity->partner_id]) }}" class="btn btn-secondary w-full justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Créer un devis
                        </a>
                        @endif
                        <form action="{{ route('crm.opportunities.destroy', $opportunity) }}" method="POST" onsubmit="return confirm('Supprimer cette opportunité ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost text-danger-600 w-full justify-start">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Modal -->
        <div x-show="showActivityModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showActivityModal = false"></div>
                <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full">
                    <form action="{{ route('crm.activities.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="related_type" value="opportunity">
                        <input type="hidden" name="related_id" value="{{ $opportunity->id }}">

                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Nouvelle Activité</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Type</label>
                                    <select name="type" x-model="activityType" class="form-select">
                                        @foreach(\App\Models\Activity::TYPES as $type => $config)
                                        <option value="{{ $type }}">{{ $config['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Sujet</label>
                                    <input type="text" name="subject" required class="form-input" placeholder="Ex: Appel de suivi">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Date/Heure</label>
                                    <input type="datetime-local" name="due_date" class="form-input">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Description</label>
                                    <textarea name="description" rows="2" class="form-textarea" placeholder="Détails..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900/50 flex justify-end gap-3 rounded-b-xl">
                            <button type="button" @click="showActivityModal = false" class="btn btn-secondary">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lost Reason Modal -->
        <div x-data="{ open: false }" @open-lost-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
                <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full">
                    <form action="{{ route('crm.opportunities.mark-lost', $opportunity) }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-secondary-900 dark:text-white mb-4">Marquer comme perdue</h3>
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-1">Raison de la perte</label>
                                <input type="text" name="lost_reason" required class="form-input" placeholder="Ex: Budget insuffisant, concurrent moins cher...">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900/50 flex justify-end gap-3 rounded-b-xl">
                            <button type="button" @click="open = false" class="btn btn-secondary">Annuler</button>
                            <button type="submit" class="btn btn-danger">Confirmer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
