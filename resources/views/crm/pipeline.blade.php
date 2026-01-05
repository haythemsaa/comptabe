<x-app-layout>
    <x-slot name="title">Pipeline CRM</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Pipeline CRM</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Pipeline Commercial</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    {{ $stats['total_open'] }} opportunités ouvertes -
                    <span class="text-success-600 font-semibold">{{ number_format($stats['weighted_amount'], 0, ',', ' ') }} EUR</span> pondéré
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('crm.dashboard') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('crm.opportunities.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle Opportunité
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total Pipeline</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['total_amount'], 0, ',', ' ') }} EUR</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Pondéré</div>
                <div class="text-2xl font-bold text-success-600">{{ number_format($stats['weighted_amount'], 0, ',', ' ') }} EUR</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Ce mois</div>
                <div class="text-2xl font-bold text-primary-600">{{ $stats['closing_this_month'] }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">En retard</div>
                <div class="text-2xl font-bold {{ $stats['overdue_count'] > 0 ? 'text-danger-600' : 'text-secondary-400' }}">{{ $stats['overdue_count'] }}</div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max" id="kanban-board">
                @foreach(\App\Models\Opportunity::STAGES as $stage => $config)
                    @if(!in_array($stage, ['won', 'lost']))
                    <div class="w-80 flex-shrink-0">
                        <!-- Column Header -->
                        <div class="bg-{{ $config['color'] }}-100 dark:bg-{{ $config['color'] }}-900/30 rounded-t-xl p-4 border-b-2 border-{{ $config['color'] }}-500">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300">{{ $config['label'] }}</span>
                                    <span class="px-2 py-0.5 bg-{{ $config['color'] }}-200 dark:bg-{{ $config['color'] }}-800 text-{{ $config['color'] }}-700 dark:text-{{ $config['color'] }}-300 text-xs rounded-full">
                                        {{ isset($opportunities[$stage]) ? $opportunities[$stage]->count() : 0 }}
                                    </span>
                                </div>
                                <span class="text-xs text-{{ $config['color'] }}-600 dark:text-{{ $config['color'] }}-400">
                                    {{ $config['probability'] }}%
                                </span>
                            </div>
                            @if(isset($stats['by_stage'][$stage]))
                            <div class="text-sm text-{{ $config['color'] }}-600 dark:text-{{ $config['color'] }}-400 mt-1">
                                {{ number_format($stats['by_stage'][$stage]['amount'], 0, ',', ' ') }} EUR
                            </div>
                            @endif
                        </div>

                        <!-- Column Body -->
                        <div
                            class="bg-secondary-50 dark:bg-secondary-800/50 rounded-b-xl p-3 min-h-[400px] space-y-3 kanban-column"
                            data-stage="{{ $stage }}"
                        >
                            @foreach($opportunities[$stage] ?? [] as $opportunity)
                            <div
                                class="bg-white dark:bg-secondary-800 rounded-lg shadow-sm border border-secondary-200 dark:border-secondary-700 p-4 cursor-move hover:shadow-md transition-shadow kanban-card"
                                data-id="{{ $opportunity->id }}"
                                draggable="true"
                            >
                                <!-- Card Header -->
                                <div class="flex items-start justify-between mb-2">
                                    <a href="{{ route('crm.opportunities.show', $opportunity) }}" class="font-medium text-secondary-900 dark:text-white hover:text-primary-600 line-clamp-1">
                                        {{ $opportunity->title }}
                                    </a>
                                    @if($opportunity->isOverdue())
                                    <span class="flex-shrink-0 w-2 h-2 bg-danger-500 rounded-full animate-pulse" title="En retard"></span>
                                    @endif
                                </div>

                                <!-- Partner -->
                                @if($opportunity->partner)
                                <div class="flex items-center gap-2 text-sm text-secondary-500 mb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span class="truncate">{{ $opportunity->partner->name }}</span>
                                </div>
                                @endif

                                <!-- Amount & Probability -->
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-bold text-secondary-900 dark:text-white">
                                        {{ number_format($opportunity->amount, 0, ',', ' ') }} EUR
                                    </span>
                                    <span class="px-2 py-0.5 bg-secondary-100 dark:bg-secondary-700 text-secondary-600 dark:text-secondary-400 text-xs rounded">
                                        {{ $opportunity->probability }}%
                                    </span>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-between text-xs text-secondary-500 pt-2 border-t border-secondary-100 dark:border-secondary-700">
                                    @if($opportunity->expected_close_date)
                                    <span class="{{ $opportunity->isOverdue() ? 'text-danger-600' : '' }}">
                                        {{ $opportunity->expected_close_date->format('d/m/Y') }}
                                    </span>
                                    @else
                                    <span>-</span>
                                    @endif

                                    @if($opportunity->assignedTo)
                                    <div class="flex items-center gap-1">
                                        <div class="w-5 h-5 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-primary-600">{{ substr($opportunity->assignedTo->first_name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach

                            <!-- Empty State -->
                            @if(!isset($opportunities[$stage]) || $opportunities[$stage]->isEmpty())
                            <div class="text-center py-8 text-secondary-400">
                                <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-sm">Aucune opportunité</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach

                <!-- Won/Lost Summary -->
                <div class="w-64 flex-shrink-0">
                    <div class="bg-success-100 dark:bg-success-900/30 rounded-xl p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold text-success-700 dark:text-success-300">Gagnées</span>
                        </div>
                        <a href="{{ route('crm.opportunities.index', ['stage' => 'won']) }}" class="text-2xl font-bold text-success-600 hover:underline">
                            {{ $opportunities['won']->count() ?? 0 }}
                        </a>
                    </div>

                    <div class="bg-danger-100 dark:bg-danger-900/30 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold text-danger-700 dark:text-danger-300">Perdues</span>
                        </div>
                        <a href="{{ route('crm.opportunities.index', ['stage' => 'lost']) }}" class="text-2xl font-bold text-danger-600 hover:underline">
                            {{ $opportunities['lost']->count() ?? 0 }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const columns = document.querySelectorAll('.kanban-column');

            columns.forEach(column => {
                new Sortable(column, {
                    group: 'opportunities',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    dragClass: 'shadow-lg',
                    onEnd: function(evt) {
                        const cardId = evt.item.dataset.id;
                        const newStage = evt.to.dataset.stage;
                        const newIndex = evt.newIndex;

                        // Update via AJAX
                        fetch(`/crm/opportunities/${cardId}/stage`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                stage: newStage,
                                sort_order: newIndex
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.showToast && window.showToast(`Opportunité déplacée vers ${data.stage_label}`, 'success');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            window.showToast && window.showToast('Erreur lors du déplacement', 'error');
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
