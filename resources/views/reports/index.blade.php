@extends('layouts.app')

@section('title', 'Rapports')

@section('content')
<div class="py-6" x-data="reportsDashboard()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Rapports</h1>
                <p class="text-gray-600">Générez et personnalisez vos rapports comptables</p>
            </div>
            <div class="flex space-x-3">
                <button @click="showQuickGenerate = true"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Génération rapide
                </button>
                <a href="{{ route('reports.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau rapport
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Rapports sauvegardés</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $savedReports->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Planifiés</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $scheduledReports->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Favoris</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $savedReports->where('is_favorite', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Téléchargements</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $recentExecutions->where('status', 'completed')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Types de rapports -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Types de rapports</h2>
                    </div>
                    <div class="p-4">
                        @foreach($reportTypes as $category => $types)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                                    @switch($category)
                                        @case('financial') Financiers @break
                                        @case('tax') Fiscaux @break
                                        @case('operational') Opérationnels @break
                                        @case('accounting') Comptables @break
                                        @default {{ ucfirst($category) }}
                                    @endswitch
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($types as $typeKey => $type)
                                        <a href="{{ route('reports.create', ['type' => $typeKey]) }}"
                                           class="flex items-start p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors group">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center">
                                                    @switch($category)
                                                        @case('financial')
                                                            <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                            </svg>
                                                            @break
                                                        @case('tax')
                                                            <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                            </svg>
                                                            @break
                                                        @case('operational')
                                                            <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                            </svg>
                                                            @break
                                                        @default
                                                            <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                    @endswitch
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">{{ $type['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $type['description'] }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Rapports sauvegardés -->
                @if($savedReports->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Mes rapports</h2>
                            <span class="text-sm text-gray-500">{{ $savedReports->count() }} rapport(s)</span>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($savedReports as $report)
                                <div class="p-4 hover:bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <button @click="toggleFavorite({{ $report->id }})"
                                                class="mr-3 text-{{ $report->is_favorite ? 'yellow-400' : 'gray-300' }} hover:text-yellow-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                        <div>
                                            <a href="{{ route('reports.show', $report) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                                {{ $report->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">
                                                {{ \App\Services\Reports\ReportBuilderService::REPORT_TYPES[$report->type]['name'] ?? $report->type }}
                                                @if($report->schedule)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Planifié
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-gray-400">
                                            {{ $report->executions_count }} exécution(s)
                                        </span>
                                        <a href="{{ route('reports.execute', $report) }}?format=pdf"
                                           class="text-sm text-blue-600 hover:text-blue-800">
                                            Générer
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne droite -->
            <div class="space-y-6">
                <!-- Dernières exécutions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Historique récent</h2>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        @forelse($recentExecutions as $execution)
                            <div class="p-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $execution->report?->name ?? 'Rapport' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $execution->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($execution->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ strtoupper($execution->format) }}
                                            </span>
                                            @if($execution->fileExists())
                                                <a href="{{ route('reports.download', $execution) }}"
                                                   class="text-blue-600 hover:text-blue-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        @elseif($execution->status === 'failed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Échec
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                En cours
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500 text-sm">
                                Aucun rapport généré récemment
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Raccourcis rapides -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-sm p-5 text-white">
                    <h3 class="font-semibold mb-4">Rapports rapides</h3>
                    <div class="space-y-3">
                        <button @click="quickGenerate('profit_loss', 'month')"
                                class="w-full text-left px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            Compte de résultat (ce mois)
                        </button>
                        <button @click="quickGenerate('vat_summary', 'quarter')"
                                class="w-full text-left px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            TVA (ce trimestre)
                        </button>
                        <button @click="quickGenerate('aged_receivables', 'today')"
                                class="w-full text-left px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            Balance âgée clients
                        </button>
                        <button @click="quickGenerate('trial_balance', 'year')"
                                class="w-full text-left px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            Balance des comptes (année)
                        </button>
                    </div>
                </div>

                <!-- Rapports planifiés -->
                @if($scheduledReports->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Planifiés</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach($scheduledReports->take(5) as $report)
                                <div class="p-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $report->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ ucfirst($report->schedule['frequency'] ?? 'N/A') }}
                                        @if($report->next_run)
                                            - Prochain: {{ $report->next_run->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal génération rapide -->
    <div x-show="showQuickGenerate" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showQuickGenerate = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Génération rapide</h3>

                <form @submit.prevent="submitQuickGenerate">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de rapport</label>
                            <select x-model="quickForm.type" class="w-full rounded-lg border-gray-300">
                                @foreach($reportTypes as $category => $types)
                                    <optgroup label="{{ ucfirst($category) }}">
                                        @foreach($types as $key => $type)
                                            <option value="{{ $key }}">{{ $type['name'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                                <input type="date" x-model="quickForm.date_from" class="w-full rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                                <input type="date" x-model="quickForm.date_to" class="w-full rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" x-model="quickForm.format" value="pdf" class="text-blue-600">
                                    <span class="ml-2 text-sm">PDF</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="quickForm.format" value="xlsx" class="text-blue-600">
                                    <span class="ml-2 text-sm">Excel</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="quickForm.format" value="csv" class="text-blue-600">
                                    <span class="ml-2 text-sm">CSV</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showQuickGenerate = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" :disabled="generating"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!generating">Générer</span>
                            <span x-show="generating">Génération...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reportsDashboard() {
    return {
        showQuickGenerate: false,
        generating: false,
        quickForm: {
            type: 'profit_loss',
            date_from: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
            date_to: new Date().toISOString().split('T')[0],
            format: 'pdf'
        },

        quickGenerate(type, period) {
            const now = new Date();
            let dateFrom, dateTo;

            switch(period) {
                case 'month':
                    dateFrom = new Date(now.getFullYear(), now.getMonth(), 1);
                    dateTo = now;
                    break;
                case 'quarter':
                    const quarter = Math.floor(now.getMonth() / 3);
                    dateFrom = new Date(now.getFullYear(), quarter * 3, 1);
                    dateTo = now;
                    break;
                case 'year':
                    dateFrom = new Date(now.getFullYear(), 0, 1);
                    dateTo = now;
                    break;
                case 'today':
                    dateFrom = new Date(now.getFullYear(), 0, 1);
                    dateTo = now;
                    break;
            }

            this.quickForm.type = type;
            this.quickForm.date_from = dateFrom.toISOString().split('T')[0];
            this.quickForm.date_to = dateTo.toISOString().split('T')[0];
            this.showQuickGenerate = true;
        },

        async submitQuickGenerate() {
            this.generating = true;

            try {
                const response = await fetch('{{ route("reports.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.quickForm)
                });

                const data = await response.json();

                if (data.success && data.download_url) {
                    window.location.href = data.download_url;
                    this.showQuickGenerate = false;
                } else {
                    alert(data.error || 'Erreur lors de la génération');
                }
            } catch (error) {
                console.error(error);
                alert('Erreur lors de la génération');
            } finally {
                this.generating = false;
            }
        },

        async toggleFavorite(reportId) {
            try {
                await fetch(`/reports/${reportId}/favorite`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                window.location.reload();
            } catch (error) {
                console.error(error);
            }
        }
    }
}
</script>
@endpush
@endsection
