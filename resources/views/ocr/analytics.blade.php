<x-app-layout>
    <x-slot name="title">Analytiques OCR & IA</x-slot>

    <div class="space-y-6" x-data="ocrAnalyticsApp()">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                    Analytiques OCR & IA
                </h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Suivi des performances d'extraction automatique de factures
                </p>
            </div>
            <div class="flex gap-2">
                <button @click="refreshStats" class="btn btn-secondary btn-sm">
                    <svg class="w-4 h-4 mr-1" :class="{'animate-spin': refreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </button>
                <a href="{{ route('ocr.analytics.export') }}" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
            </div>
        </div>

        <!-- Real-time Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">En traitement</p>
                            <p class="text-3xl font-bold" x-text="realtimeStats.processing">{{ $stats['total_scans'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm">En file d'attente</p>
                            <p class="text-3xl font-bold" x-text="realtimeStats.queued">0</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-green-500 to-green-600 text-white">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">Aujourd'hui</p>
                            <p class="text-3xl font-bold" x-text="realtimeStats.today_scans">0</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">Auto-créées aujourd'hui</p>
                            <p class="text-3xl font-bold" x-text="realtimeStats.today_auto_created">0</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-lg">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Statistiques Globales</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Total scans</span>
                        <span class="text-xl font-bold">{{ number_format($stats['total_scans']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Complétés</span>
                        <span class="text-xl font-bold text-success-600">{{ number_format($stats['completed']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Auto-créés</span>
                        <span class="text-xl font-bold text-primary-600">{{ number_format($stats['auto_created']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-secondary-600 dark:text-secondary-400">Échoués</span>
                        <span class="text-xl font-bold text-danger-600">{{ number_format($stats['failed']) }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Performance</h2>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Confiance moyenne</span>
                            <span class="text-xl font-bold">{{ number_format($stats['avg_confidence'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $stats['avg_confidence'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Taux auto-création</span>
                            <span class="text-xl font-bold">{{ number_format($stats['auto_creation_rate'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-success-600 h-2 rounded-full" style="width: {{ $stats['auto_creation_rate'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Taux de succès</span>
                            <span class="text-xl font-bold">{{ number_format($stats['success_rate'], 1) }}%</span>
                        </div>
                        <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                            <div class="bg-info-600 h-2 rounded-full" style="width: {{ $stats['success_rate'] }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-secondary-200 dark:border-secondary-700">
                        <span class="text-secondary-600 dark:text-secondary-400">Temps moyen</span>
                        <span class="text-xl font-bold">{{ number_format($performance['avg_processing_time'], 1) }}s</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Distribution Confiance</h2>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex items-center justify-between p-3 bg-success-50 dark:bg-success-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-success-500 rounded-full"></div>
                            <span class="text-sm font-medium">Haute (≥85%)</span>
                        </div>
                        <span class="text-lg font-bold text-success-600">{{ $performance['confidence_distribution']['high'] }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-warning-500 rounded-full"></div>
                            <span class="text-sm font-medium">Moyenne (70-84%)</span>
                        </div>
                        <span class="text-lg font-bold text-warning-600">{{ $performance['confidence_distribution']['medium'] }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-danger-500 rounded-full"></div>
                            <span class="text-sm font-medium">Faible (<70%)</span>
                        </div>
                        <span class="text-lg font-bold text-danger-600">{{ $performance['confidence_distribution']['low'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scans Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Scans Récents</h2>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Fichier</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Confiance</th>
                                <th>Facture</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentScans as $scan)
                                <tr>
                                    <td class="whitespace-nowrap">
                                        {{ $scan->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ Str::limit($scan->original_filename, 30) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ ucfirst($scan->document_type) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge
                                            @if($scan->status === 'completed') badge-success
                                            @elseif($scan->status === 'failed') badge-danger
                                            @elseif($scan->status === 'processing') badge-warning
                                            @else badge-secondary
                                            @endif
                                        ">
                                            {{ ucfirst($scan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($scan->overall_confidence)
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                                    <div class="
                                                        @if($scan->overall_confidence >= 0.85) bg-success-600
                                                        @elseif($scan->overall_confidence >= 0.70) bg-warning-600
                                                        @else bg-danger-600
                                                        @endif
                                                        h-2 rounded-full"
                                                        style="width: {{ $scan->overall_confidence * 100 }}%"
                                                    ></div>
                                                </div>
                                                <span class="text-sm font-medium">{{ round($scan->overall_confidence * 100) }}%</span>
                                            </div>
                                        @else
                                            <span class="text-secondary-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($scan->createdInvoice)
                                            <a href="{{ route('invoices.show', $scan->createdInvoice) }}" class="text-primary-600 hover:underline">
                                                {{ $scan->createdInvoice->invoice_number }}
                                            </a>
                                        @else
                                            <span class="text-secondary-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @if($scan->status === 'failed')
                                                <button
                                                    @click="retryScan({{ $scan->id }})"
                                                    class="btn btn-sm btn-secondary"
                                                    title="Réessayer"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-secondary-500">
                                        Aucun scan pour le moment
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function ocrAnalyticsApp() {
            return {
                refreshing: false,
                realtimeStats: {
                    processing: 0,
                    queued: 0,
                    today_scans: 0,
                    today_auto_created: 0,
                },

                init() {
                    this.loadRealtimeStats();
                    setInterval(() => this.loadRealtimeStats(), 30000); // Refresh every 30s
                },

                async loadRealtimeStats() {
                    try {
                        const response = await fetch('{{ route('ocr.analytics.realtime') }}');
                        const data = await response.json();
                        this.realtimeStats = data;
                    } catch (error) {
                        console.error('Failed to load realtime stats:', error);
                    }
                },

                async refreshStats() {
                    this.refreshing = true;
                    await this.loadRealtimeStats();
                    setTimeout(() => {
                        this.refreshing = false;
                        window.location.reload();
                    }, 500);
                },

                async retryScan(scanId) {
                    if (!confirm('Voulez-vous vraiment réessayer ce scan ?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`/ocr/analytics/retry/${scanId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            window.showToast(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Retry failed:', error);
                        window.showToast('Erreur lors de la réessai', 'error');
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
