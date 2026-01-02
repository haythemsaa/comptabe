@extends('layouts.app')

@section('title', $report->name)

@section('content')
<div class="py-6" x-data="reportDetail()">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('reports.index') }}" class="hover:text-gray-700">Rapports</a>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-900">{{ $report->name }}</span>
            </nav>

            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <button @click="toggleFavorite"
                            class="mr-3 text-{{ $report->is_favorite ? 'yellow-400' : 'gray-300' }} hover:text-yellow-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $report->name }}</h1>
                        <div class="flex items-center mt-1 space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $reportInfo['name'] ?? $report->type }}
                            </span>
                            @if($report->schedule)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Planifié ({{ $report->schedule['frequency'] }})
                                </span>
                            @endif
                            @if($report->is_public)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Partagé
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                            Actions
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            <a href="{{ route('reports.duplicate', $report) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Dupliquer
                            </a>
                            <a href="{{ route('reports.executions', $report) }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Historique
                            </a>
                            <button @click="showEdit = true; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Modifier
                            </button>
                            <hr class="my-1">
                            <button @click="deleteReport"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Supprimer
                            </button>
                        </div>
                    </div>

                    <button @click="showGenerate = true"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Générer
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Info principale -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                @if($report->description)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                        <p class="text-gray-600">{{ $report->description }}</p>
                    </div>
                @endif

                <!-- Configuration -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuration</h2>

                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">Type</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $reportInfo['name'] ?? $report->type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Format par défaut</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ strtoupper($report->config['format'] ?? 'PDF') }}</dd>
                        </div>
                        @if($report->config['date_from'] ?? null)
                            <div>
                                <dt class="text-sm text-gray-500">Période de début</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $report->config['date_from'])) }}</dd>
                            </div>
                        @endif
                        @if($report->config['date_to'] ?? null)
                            <div>
                                <dt class="text-sm text-gray-500">Période de fin</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $report->config['date_to'])) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if(!empty($report->config['options']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Options</h3>
                            <pre class="text-xs bg-gray-50 p-3 rounded-lg overflow-x-auto">{{ json_encode($report->config['options'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>

                <!-- Planification -->
                @if($report->schedule)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Planification</h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Fréquence</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ ucfirst($report->schedule['frequency']) }}</dd>
                            </div>
                            @if($report->next_run)
                                <div>
                                    <dt class="text-sm text-gray-500">Prochaine exécution</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $report->next_run->format('d/m/Y') }}</dd>
                                </div>
                            @endif
                            @if($report->schedule['send_email'] ?? false)
                                <div class="col-span-2">
                                    <dt class="text-sm text-gray-500">Envoi par email</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $report->schedule['emails'] ?? 'Email du compte' }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                <!-- Dernières exécutions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Dernières générations</h2>
                        <a href="{{ route('reports.executions', $report) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            Voir tout
                        </a>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($report->executions as $execution)
                            <div class="p-4 hover:bg-gray-50 flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        {{ $execution->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                    <div class="flex items-center mt-1 space-x-3">
                                        @if($execution->status === 'completed')
                                            <span class="inline-flex items-center text-xs text-green-600">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Succès
                                            </span>
                                        @elseif($execution->status === 'failed')
                                            <span class="inline-flex items-center text-xs text-red-600">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                Échec
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs text-yellow-600">
                                                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                </svg>
                                                En cours
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-500">{{ strtoupper($execution->format) }}</span>
                                        @if($execution->execution_time_ms)
                                            <span class="text-xs text-gray-500">{{ number_format($execution->execution_time_ms / 1000, 1) }}s</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($execution->status === 'completed' && $execution->fileExists())
                                        <span class="text-xs text-gray-500">{{ $execution->formatted_file_size }}</span>
                                        <a href="{{ route('reports.download', $execution) }}"
                                           class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2">Aucune génération</p>
                                <button @click="showGenerate = true"
                                        class="mt-3 text-sm text-blue-600 hover:text-blue-800">
                                    Générer maintenant
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Informations</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Créé par</dt>
                            <dd class="text-gray-900">{{ $report->user?->name ?? 'Système' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Créé le</dt>
                            <dd class="text-gray-900">{{ $report->created_at->format('d/m/Y') }}</dd>
                        </div>
                        @if($report->last_generated_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Dernière génération</dt>
                                <dd class="text-gray-900">{{ $report->last_generated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Nombre d'exécutions</dt>
                            <dd class="text-gray-900">{{ $report->executions->count() }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Actions rapides -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-5 text-white">
                    <h3 class="font-semibold mb-4">Générer maintenant</h3>
                    <div class="space-y-2">
                        <a href="{{ route('reports.execute', $report) }}?format=pdf"
                           class="block w-full text-center px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            Télécharger en PDF
                        </a>
                        <a href="{{ route('reports.execute', $report) }}?format=xlsx"
                           class="block w-full text-center px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-sm">
                            Télécharger en Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Générer -->
    <div x-show="showGenerate" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showGenerate = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Générer le rapport</h3>

                <form @submit.prevent="generate">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                                <input type="date" x-model="generateForm.date_from" class="w-full rounded-lg border-gray-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                                <input type="date" x-model="generateForm.date_to" class="w-full rounded-lg border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" x-model="generateForm.format" value="pdf" class="text-blue-600">
                                    <span class="ml-2 text-sm">PDF</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="generateForm.format" value="xlsx" class="text-blue-600">
                                    <span class="ml-2 text-sm">Excel</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="generateForm.format" value="csv" class="text-blue-600">
                                    <span class="ml-2 text-sm">CSV</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showGenerate = false"
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
function reportDetail() {
    const now = new Date();
    const startOfYear = new Date(now.getFullYear(), 0, 1);

    return {
        showGenerate: false,
        showEdit: false,
        generating: false,

        generateForm: {
            format: 'pdf',
            date_from: startOfYear.toISOString().split('T')[0],
            date_to: now.toISOString().split('T')[0]
        },

        async generate() {
            this.generating = true;

            try {
                const params = new URLSearchParams({
                    format: this.generateForm.format,
                    date_from: this.generateForm.date_from,
                    date_to: this.generateForm.date_to
                });

                window.location.href = '{{ route("reports.execute", $report) }}?' + params.toString();
            } finally {
                this.generating = false;
            }
        },

        async toggleFavorite() {
            try {
                await fetch('{{ route("reports.favorite", $report) }}', {
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
        },

        async deleteReport() {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')) {
                return;
            }

            try {
                const response = await fetch('{{ route("reports.destroy", $report) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.href = '{{ route("reports.index") }}';
                }
            } catch (error) {
                console.error(error);
                alert('Erreur lors de la suppression');
            }
        }
    }
}
</script>
@endpush
@endsection
