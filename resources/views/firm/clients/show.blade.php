<x-firm-layout>
    <x-slot name="title">{{ $mandate->company->name }}</x-slot>
    <x-slot name="header">Détails du client</x-slot>

    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm">
        <ol class="inline-flex items-center space-x-1">
            <li>
                <a href="{{ route('firm.dashboard') }}" class="text-secondary-400 hover:text-white">Tableau de bord</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li>
                <a href="{{ route('firm.clients.index') }}" class="text-secondary-400 hover:text-white">Clients</a>
            </li>
            <li><span class="mx-2 text-secondary-500">/</span></li>
            <li class="text-white">{{ $mandate->company->name }}</li>
        </ol>
    </nav>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Client Header Card -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-primary-500/20 flex items-center justify-center font-bold text-2xl text-primary-400">
                            {{ strtoupper(substr($mandate->company->name, 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">{{ $mandate->company->name }}</h2>
                            <p class="text-secondary-400">{{ $mandate->company->vat_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $mandate->status_color }}-500/20 text-{{ $mandate->status_color }}-400">
                            {{ $mandate->status_label }}
                        </span>
                        <a href="{{ route('firm.clients.edit', $mandate) }}"
                            class="p-2 hover:bg-secondary-700 rounded-lg transition-colors" title="Modifier">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 rounded-lg bg-secondary-700/50">
                        <p class="text-sm text-secondary-400 mb-1">Type de mandat</p>
                        <p class="font-semibold">{{ $mandate->type_label }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-secondary-700/50">
                        <p class="text-sm text-secondary-400 mb-1">Gestionnaire</p>
                        <p class="font-semibold">{{ $mandate->manager->full_name ?? 'Non assigné' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-secondary-700/50">
                        <p class="text-sm text-secondary-400 mb-1">Date de début</p>
                        <p class="font-semibold">{{ $mandate->start_date?->format('d/m/Y') ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="border-t border-secondary-700 pt-6">
                    <h4 class="font-semibold mb-3">Coordonnées</h4>
                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-secondary-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-secondary-400">Email</p>
                                <a href="mailto:{{ $mandate->company->email }}" class="text-primary-400 hover:underline">
                                    {{ $mandate->company->email }}
                                </a>
                            </div>
                        </div>
                        @if($mandate->company->phone)
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-secondary-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <div>
                                    <p class="text-secondary-400">Téléphone</p>
                                    <p>{{ $mandate->company->phone }}</p>
                                </div>
                            </div>
                        @endif
                        @if($mandate->company->street)
                            <div class="flex items-start gap-2 md:col-span-2">
                                <svg class="w-5 h-5 text-secondary-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div>
                                    <p class="text-secondary-400">Adresse</p>
                                    <p>{{ $mandate->company->street }} {{ $mandate->company->house_number }}</p>
                                    <p>{{ $mandate->company->postal_code }} {{ $mandate->company->city }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div x-data="{ activeTab: 'tasks' }">
                <!-- Tab Navigation -->
                <div class="flex gap-2 border-b border-secondary-700 mb-6">
                    <button @click="activeTab = 'tasks'"
                        :class="activeTab === 'tasks' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                        Tâches ({{ $mandate->tasks->count() }})
                    </button>
                    <button @click="activeTab = 'documents'"
                        :class="activeTab === 'documents' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                        Documents ({{ $mandate->documents->count() }})
                    </button>
                    <button @click="activeTab = 'communications'"
                        :class="activeTab === 'communications' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                        Communications ({{ $mandate->communications->count() }})
                    </button>
                    <button @click="activeTab = 'activities'"
                        :class="activeTab === 'activities' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                        class="px-4 py-3 border-b-2 font-medium transition-colors">
                        Activité
                    </button>
                </div>

                <!-- Tasks Tab -->
                <div x-show="activeTab === 'tasks'" x-cloak class="bg-secondary-800 rounded-xl border border-secondary-700">
                    <div class="p-4 border-b border-secondary-700 flex items-center justify-between">
                        <h3 class="font-semibold">Tâches</h3>
                        <a href="{{ route('firm.tasks.create', ['mandate' => $mandate->id]) }}"
                            class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nouvelle tâche
                        </a>
                    </div>
                    <div class="divide-y divide-secondary-700">
                        @forelse($mandate->tasks as $task)
                            <div class="p-4 hover:bg-secondary-700/50 transition-colors">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-medium">{{ $task->title }}</h4>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                @if($task->status === 'completed') bg-success-500/20 text-success-400
                                                @elseif($task->status === 'in_progress') bg-warning-500/20 text-warning-400
                                                @else bg-secondary-700 text-secondary-300
                                                @endif">
                                                {{ ucfirst($task->status) }}
                                            </span>
                                        </div>
                                        @if($task->description)
                                            <p class="text-sm text-secondary-400 mb-2">{{ $task->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-xs text-secondary-500">
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $task->due_date->format('d/m/Y') }}
                                                </span>
                                            @endif
                                            @if($task->assignedUser)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    {{ $task->assignedUser->full_name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="#" class="p-2 hover:bg-secondary-600 rounded-lg transition-colors text-secondary-400 hover:text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p>Aucune tâche pour ce client</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Documents Tab -->
                <div x-show="activeTab === 'documents'" x-cloak class="bg-secondary-800 rounded-xl border border-secondary-700">
                    <div class="p-4 border-b border-secondary-700 flex items-center justify-between">
                        <h3 class="font-semibold">Documents</h3>
                        <button class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Upload
                        </button>
                    </div>
                    <div class="divide-y divide-secondary-700">
                        @forelse($mandate->documents as $document)
                            <div class="p-4 hover:bg-secondary-700/50 transition-colors flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-secondary-700 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">{{ $document->filename }}</p>
                                    <p class="text-sm text-secondary-400">{{ $document->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <button class="p-2 hover:bg-secondary-600 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="p-8 text-center text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <p>Aucun document</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Communications Tab -->
                <div x-show="activeTab === 'communications'" x-cloak class="bg-secondary-800 rounded-xl border border-secondary-700">
                    <div class="p-4 border-b border-secondary-700">
                        <h3 class="font-semibold">Communications</h3>
                    </div>
                    <div class="divide-y divide-secondary-700">
                        @forelse($mandate->communications as $communication)
                            <div class="p-4 hover:bg-secondary-700/50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-secondary-700 flex items-center justify-center text-sm font-medium">
                                        {{ $communication->sender->initials ?? '?' }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-medium">{{ $communication->sender->full_name ?? 'Inconnu' }}</p>
                                            <span class="text-xs text-secondary-500">{{ $communication->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-secondary-300">{{ $communication->message }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-secondary-500">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p>Aucune communication</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Activities Tab -->
                <div x-show="activeTab === 'activities'" x-cloak class="bg-secondary-800 rounded-xl border border-secondary-700">
                    <div class="p-4 border-b border-secondary-700">
                        <h3 class="font-semibold">Historique d'activité</h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-4">
                            @forelse($mandate->activities as $activity)
                                <div class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-full bg-secondary-700 my-2"></div>
                                        @endif
                                    </div>
                                    <div class="flex-1 pb-4">
                                        <p class="text-sm mb-1">
                                            <span class="font-medium">{{ $activity->user->full_name ?? 'Système' }}</span>
                                            <span class="text-secondary-400">{{ $activity->description }}</span>
                                        </p>
                                        <p class="text-xs text-secondary-500">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-secondary-500">
                                    <p>Aucune activité récente</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Services Card -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Services
                </h3>
                <div class="space-y-2 text-sm">
                    @foreach($mandate->services ?? [] as $service => $enabled)
                        @if($enabled)
                            <div class="flex items-center gap-2 text-secondary-300">
                                <svg class="w-4 h-4 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ ucfirst(str_replace('_', ' ', $service)) }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Billing Card -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Facturation
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-secondary-400 mb-1">Type</p>
                        <p class="font-medium">{{ ucfirst($mandate->billing_type) }}</p>
                    </div>
                    @if($mandate->billing_type === 'hourly' && $mandate->hourly_rate)
                        <div>
                            <p class="text-secondary-400 mb-1">Taux horaire</p>
                            <p class="font-medium">{{ number_format($mandate->hourly_rate, 2) }} €/h</p>
                        </div>
                    @endif
                    @if($mandate->billing_type === 'monthly' && $mandate->monthly_fee)
                        <div>
                            <p class="text-secondary-400 mb-1">Forfait mensuel</p>
                            <p class="font-medium">{{ number_format($mandate->monthly_fee, 2) }} €</p>
                        </div>
                    @endif
                    @if($mandate->billing_type === 'annual' && $mandate->annual_fee)
                        <div>
                            <p class="text-secondary-400 mb-1">Forfait annuel</p>
                            <p class="font-medium">{{ number_format($mandate->annual_fee, 2) }} €</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4">Statistiques</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-secondary-400">Tâches en cours</span>
                        <span class="font-semibold">{{ $mandate->pending_tasks_count }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-secondary-400">Tâches en retard</span>
                        <span class="font-semibold text-danger-400">{{ $mandate->overdue_tasks_count }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-secondary-400">Messages non lus</span>
                        <span class="font-semibold">{{ $mandate->unread_messages_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <h3 class="font-semibold mb-4">Actions rapides</h3>
                <div class="space-y-2">
                    <a href="{{ route('firm.tasks.create', ['mandate' => $mandate->id]) }}"
                        class="flex items-center gap-2 px-3 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer une tâche
                    </a>
                    <button class="w-full flex items-center gap-2 px-3 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Envoyer un email
                    </button>
                    <a href="{{ route('firm.clients.edit', $mandate) }}"
                        class="flex items-center gap-2 px-3 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier le mandat
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-firm-layout>
