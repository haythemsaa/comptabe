<x-app-layout>
    <x-slot name="title">{{ $asset->name }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('assets.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Immobilisations</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">{{ $asset->reference ?? $asset->name }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">{{ $asset->name }}</h1>
                    @switch($asset->status)
                        @case('draft')
                            <span class="badge badge-secondary">Brouillon</span>
                            @break
                        @case('active')
                            <span class="badge badge-success">Actif</span>
                            @break
                        @case('fully_depreciated')
                            <span class="badge badge-warning">Totalement amorti</span>
                            @break
                        @case('disposed')
                            <span class="badge badge-danger">Cede</span>
                            @break
                        @case('sold')
                            <span class="badge badge-info">Vendu</span>
                            @break
                    @endswitch
                </div>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">{{ $asset->category->name ?? 'Sans categorie' }} {{ $asset->reference ? '- ' . $asset->reference : '' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($asset->status == 'draft')
                    <form action="{{ route('assets.activate', $asset) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Activer
                        </button>
                    </form>
                @endif
                @if($asset->status == 'active')
                    <button type="button" onclick="document.getElementById('dispose-modal').classList.remove('hidden')" class="btn btn-outline-danger btn-sm">
                        Ceder / Vendre
                    </button>
                @endif
                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-secondary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Resume financier -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Resume financier</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                            <p class="text-sm text-secondary-500 mb-1">Valeur d'acquisition</p>
                            <p class="text-xl font-semibold text-secondary-800 dark:text-white">{{ number_format($asset->acquisition_cost, 2, ',', ' ') }} &euro;</p>
                        </div>
                        <div class="text-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                            <p class="text-sm text-secondary-500 mb-1">Amort. cumules</p>
                            <p class="text-xl font-semibold text-warning-500">{{ number_format($asset->accumulated_depreciation, 2, ',', ' ') }} &euro;</p>
                        </div>
                        <div class="text-center p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                            <p class="text-sm text-secondary-500 mb-1">Valeur nette comptable</p>
                            <p class="text-xl font-semibold text-primary-500">{{ number_format($asset->current_value, 2, ',', ' ') }} &euro;</p>
                        </div>
                        <div class="text-center p-4 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                            <p class="text-sm text-secondary-500 mb-1">Valeur residuelle</p>
                            <p class="text-xl font-semibold text-secondary-800 dark:text-white">{{ number_format($asset->residual_value, 2, ',', ' ') }} &euro;</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    @php
                        $depreciationProgress = $asset->acquisition_cost > 0 ? ($asset->accumulated_depreciation / ($asset->acquisition_cost - $asset->residual_value)) * 100 : 0;
                        $depreciationProgress = min(100, max(0, $depreciationProgress));
                    @endphp
                    <div class="mt-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-secondary-500">Progression de l'amortissement</span>
                            <span class="font-medium text-secondary-800 dark:text-white">{{ number_format($depreciationProgress, 1) }}%</span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $depreciationProgress }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Tableau d'amortissement -->
                <div class="card">
                    <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-secondary-800 dark:text-white">Tableau d'amortissement</h3>
                        @if($asset->status == 'active' && $asset->depreciations->where('status', 'planned')->count() > 0)
                            <form action="{{ route('assets.post-depreciation', $asset) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Comptabiliser prochain amortissement
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-secondary-50 dark:bg-secondary-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Annee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Periode</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Dotation</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Cumul</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">VNC</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                @forelse($asset->depreciations as $depreciation)
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                        <td class="px-4 py-3 text-sm font-medium text-secondary-800 dark:text-white">{{ $depreciation->year_number }}</td>
                                        <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                            {{ $depreciation->period_start->format('d/m/Y') }} - {{ $depreciation->period_end->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-secondary-800 dark:text-white">{{ number_format($depreciation->depreciation_amount, 2, ',', ' ') }} &euro;</td>
                                        <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">{{ number_format($depreciation->accumulated_depreciation, 2, ',', ' ') }} &euro;</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">{{ number_format($depreciation->book_value, 2, ',', ' ') }} &euro;</td>
                                        <td class="px-4 py-3 text-center">
                                            @switch($depreciation->status)
                                                @case('planned')
                                                    <span class="badge badge-secondary">Planifie</span>
                                                    @break
                                                @case('posted')
                                                    <span class="badge badge-success">Comptabilise</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-danger">Annule</span>
                                                    @break
                                            @endswitch
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-secondary-500">Aucun amortissement planifie</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Informations -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Informations</h3>
                    <dl class="space-y-3">
                        @if($asset->serial_number)
                            <div class="flex justify-between">
                                <dt class="text-sm text-secondary-500">Numero de serie</dt>
                                <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->serial_number }}</dd>
                            </div>
                        @endif
                        @if($asset->location)
                            <div class="flex justify-between">
                                <dt class="text-sm text-secondary-500">Emplacement</dt>
                                <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->location }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Date acquisition</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->acquisition_date->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Mise en service</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->service_date->format('d/m/Y') }}</dd>
                        </div>
                        @if($asset->partner)
                            <div class="flex justify-between">
                                <dt class="text-sm text-secondary-500">Fournisseur</dt>
                                <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->partner->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Amortissement -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Amortissement</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Methode</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">
                                @switch($asset->depreciation_method)
                                    @case('linear') Lineaire @break
                                    @case('degressive') Degressif @break
                                    @case('units_of_production') Unites de production @break
                                @endswitch
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Duree</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->useful_life }} ans</dd>
                        </div>
                        @if($asset->depreciation_method == 'degressive' && $asset->degressive_rate)
                            <div class="flex justify-between">
                                <dt class="text-sm text-secondary-500">Coefficient</dt>
                                <dd class="text-sm font-medium text-secondary-800 dark:text-white">{{ $asset->degressive_rate }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-secondary-500">Dotation annuelle</dt>
                            <dd class="text-sm font-medium text-secondary-800 dark:text-white">
                                {{ number_format(($asset->acquisition_cost - $asset->residual_value) / $asset->useful_life, 2, ',', ' ') }} &euro;
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Historique -->
                <div class="card p-6">
                    <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Historique</h3>
                    <div class="space-y-3">
                        @forelse($asset->logs->take(5) as $log)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-primary-500 mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-secondary-800 dark:text-white">{{ $log->description ?? ucfirst($log->event) }}</p>
                                    <p class="text-xs text-secondary-500">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-secondary-500">Aucun evenement</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cession -->
    <div id="dispose-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('dispose-modal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-secondary-800 dark:text-white mb-4">Cession de l'immobilisation</h3>
                <form action="{{ route('assets.dispose', $asset) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Type de cession</label>
                            <select name="disposal_type" class="form-select" required>
                                <option value="disposed">Mise au rebut</option>
                                <option value="sold">Vente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Date de cession</label>
                            <input type="date" name="disposal_date" value="{{ date('Y-m-d') }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Prix de cession (si vente)</label>
                            <input type="number" step="0.01" name="disposal_amount" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea name="disposal_notes" rows="2" class="form-input"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('dispose-modal').classList.add('hidden')" class="btn btn-outline-secondary">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer la cession</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
