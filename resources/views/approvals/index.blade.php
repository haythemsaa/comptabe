<x-app-layout>
    <x-slot name="title">Approbations</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">Workflows d'approbation</h2>
            <a href="{{ route('approvals.workflows') }}"
                class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Gérer les workflows
            </a>
        </div>
    </x-slot>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-400 mb-1">En attente</p>
                    <p class="text-3xl font-bold text-warning-400">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-warning-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-400 mb-1">Approuvés ce mois</p>
                    <p class="text-3xl font-bold text-success-400">{{ $stats['approved_this_month'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-success-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-400 mb-1">Rejetés ce mois</p>
                    <p class="text-3xl font-bold text-danger-400">{{ $stats['rejected_this_month'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-danger-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-secondary-400 mb-1">Temps moyen</p>
                    <p class="text-3xl font-bold">{{ $stats['avg_approval_time'] ?? '-' }}<span class="text-lg text-secondary-400">h</span></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'pending' }" class="mb-6">
        <div class="flex gap-2 border-b border-secondary-700">
            <button @click="activeTab = 'pending'"
                :class="activeTab === 'pending' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                class="px-4 py-3 border-b-2 font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                En attente de mon approbation ({{ $pendingApprovals->count() }})
            </button>
            <button @click="activeTab = 'myRequests'"
                :class="activeTab === 'myRequests' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                class="px-4 py-3 border-b-2 font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Mes demandes ({{ $myRequests->count() }})
            </button>
            <button @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'border-primary-500 text-white' : 'border-transparent text-secondary-400 hover:text-white'"
                class="px-4 py-3 border-b-2 font-medium transition-colors inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historique
            </button>
        </div>

        <!-- Pending Approvals Tab -->
        <div x-show="activeTab === 'pending'" x-cloak class="mt-6">
            @forelse($pendingApprovals as $request)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-4 hover:border-primary-500/50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold">{{ $request->workflow->name }}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $request->getStatusColor() }}-500/20 text-{{ $request->getStatusColor() }}-400">
                                    {{ $request->getStatusLabel() }}
                                </span>
                            </div>
                            <p class="text-sm text-secondary-400 mb-3">
                                Demandé par <span class="font-medium text-white">{{ $request->requester->full_name }}</span>
                                • {{ $request->created_at->diffForHumans() }}
                            </p>
                            <div class="grid md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-secondary-500 mb-1">Type</p>
                                    <p class="font-medium">{{ $request->workflow->getDocumentTypeLabel() }}</p>
                                </div>
                                <div>
                                    <p class="text-secondary-500 mb-1">Montant</p>
                                    <p class="font-medium">{{ number_format($request->amount, 2) }} €</p>
                                </div>
                                <div>
                                    <p class="text-secondary-500 mb-1">Étape actuelle</p>
                                    <p class="font-medium">{{ $request->current_step }} / {{ $request->steps->max('step_number') }}</p>
                                </div>
                            </div>
                            @if($request->notes)
                                <div class="mt-3 p-3 rounded-lg bg-secondary-700/50">
                                    <p class="text-sm text-secondary-300">{{ $request->notes }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('approvals.show', $request) }}"
                                class="px-4 py-2 bg-primary-500 hover:bg-primary-600 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                                Voir détails
                            </a>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-secondary-400 mb-2">
                            <span>Progression</span>
                            <span>{{ $request->getProgressPercentage() }}%</span>
                        </div>
                        <div class="w-full bg-secondary-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $request->getProgressPercentage() }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium text-secondary-400">Aucune demande en attente</p>
                    <p class="text-sm text-secondary-500 mt-1">Vous n'avez aucune approbation à traiter pour le moment</p>
                </div>
            @endforelse
        </div>

        <!-- My Requests Tab -->
        <div x-show="activeTab === 'myRequests'" x-cloak class="mt-6">
            @forelse($myRequests as $request)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6 mb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold">{{ $request->workflow->name }}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $request->getStatusColor() }}-500/20 text-{{ $request->getStatusColor() }}-400">
                                    {{ $request->getStatusLabel() }}
                                </span>
                            </div>
                            <p class="text-sm text-secondary-400 mb-3">
                                Soumis {{ $request->created_at->diffForHumans() }}
                                @if($request->completed_at)
                                    • Complété {{ $request->completed_at->diffForHumans() }}
                                @endif
                            </p>
                            <div class="grid md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-secondary-500 mb-1">Montant</p>
                                    <p class="font-medium">{{ number_format($request->amount, 2) }} €</p>
                                </div>
                                <div>
                                    <p class="text-secondary-500 mb-1">Progression</p>
                                    <p class="font-medium">{{ $request->current_step }} / {{ $request->steps->max('step_number') }} étapes</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('approvals.show', $request) }}"
                            class="px-4 py-2 bg-secondary-700 hover:bg-secondary-600 rounded-lg text-sm font-medium transition-colors">
                            Détails
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-medium text-secondary-400">Aucune demande</p>
                    <p class="text-sm text-secondary-500 mt-1">Vous n'avez pas encore soumis de demandes d'approbation</p>
                </div>
            @endforelse
        </div>

        <!-- History Tab -->
        <div x-show="activeTab === 'history'" x-cloak class="mt-6">
            @if(count($myDecisions) > 0)
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-secondary-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Demande</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Demandeur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Décision</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-700">
                            @foreach($myDecisions as $decision)
                                <tr class="hover:bg-secondary-700/50">
                                    <td class="px-6 py-4 text-sm">{{ $decision->decided_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('approvals.show', $decision->approval_request) }}" class="text-primary-400 hover:underline">
                                            {{ $decision->approval_request->workflow->name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $decision->approval_request->requester->full_name }}</td>
                                    <td class="px-6 py-4 text-sm font-medium">{{ number_format($decision->approval_request->amount, 2) }} €</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($decision->decision === 'approved') bg-success-500/20 text-success-400
                                            @elseif($decision->decision === 'rejected') bg-danger-500/20 text-danger-400
                                            @else bg-warning-500/20 text-warning-400
                                            @endif">
                                            {{ ucfirst($decision->decision) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium text-secondary-400">Aucun historique</p>
                    <p class="text-sm text-secondary-500 mt-1">Vous n'avez pas encore pris de décisions d'approbation</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
