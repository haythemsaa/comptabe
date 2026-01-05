<x-app-layout>
    <x-slot name="title">Ecarts de change</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('currencies.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Devises</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Ecarts de change</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Ecarts de change</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Gains et pertes lies aux variations de taux</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('currencies.index') }}" class="btn btn-outline-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
                <a href="{{ route('currencies.exchange-differences', ['export' => 'pdf']) }}" class="btn btn-outline-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter PDF
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Gains de change</p>
                        <p class="text-xl font-bold text-success-600">+{{ number_format($totalGains, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-danger-100 text-danger-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Pertes de change</p>
                        <p class="text-xl font-bold text-danger-600">-{{ number_format(abs($totalLosses), 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon {{ $netDifference >= 0 ? 'bg-success-100 text-success-600' : 'bg-danger-100 text-danger-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Resultat net</p>
                        <p class="text-xl font-bold {{ $netDifference >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $netDifference >= 0 ? '+' : '' }}{{ number_format($netDifference, 2, ',', ' ') }} &euro;
                        </p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Transactions</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $differences->total() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select">
                        <option value="month" {{ request('period', 'month') == 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Cette annee</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Personnalise</option>
                    </select>
                </div>
                <div id="custom-dates" class="{{ request('period') == 'custom' ? '' : 'hidden' }} flex gap-2">
                    <div>
                        <label class="form-label">Du</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Au</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ request('currency') == $currency->id ? 'selected' : '' }}>{{ $currency->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="gain" {{ request('type') == 'gain' ? 'selected' : '' }}>Gains</option>
                        <option value="loss" {{ request('type') == 'loss' ? 'selected' : '' }}>Pertes</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>

        <!-- Repartition par devise -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Repartition par devise</h3>
                <div class="space-y-3">
                    @foreach($byCurrency as $code => $data)
                        <div class="flex items-center justify-between p-3 bg-secondary-50 dark:bg-secondary-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="badge badge-secondary">{{ $code }}</span>
                                <span class="text-sm text-secondary-600 dark:text-secondary-400">{{ $data['count'] }} transactions</span>
                            </div>
                            <span class="font-medium {{ $data['amount'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $data['amount'] >= 0 ? '+' : '' }}{{ number_format($data['amount'], 2, ',', ' ') }} &euro;
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Evolution mensuelle</h3>
                <div class="h-48">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Liste des ecarts -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Document</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Devise</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Montant devise</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Taux initial</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Taux paiement</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Ecart</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($differences as $diff)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $diff->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($diff->documentable)
                                        <a href="{{ $diff->document_url }}" class="text-primary-600 hover:text-primary-800">
                                            {{ $diff->documentable->number ?? '#' . $diff->documentable_id }}
                                        </a>
                                        <div class="text-xs text-secondary-500">{{ class_basename($diff->documentable_type) }}</div>
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge badge-secondary">{{ $diff->currency?->code ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ number_format($diff->original_amount, 2, ',', ' ') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ number_format($diff->original_rate, 4) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ number_format($diff->payment_rate, 4) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium {{ $diff->difference_amount >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    {{ $diff->difference_amount >= 0 ? '+' : '' }}{{ number_format($diff->difference_amount, 2, ',', ' ') }} &euro;
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-secondary-500">Aucun ecart de change</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($differences->hasPages())
                <div class="p-4 border-t border-secondary-200 dark:border-secondary-700">
                    {{ $differences->links() }}
                </div>
            @endif
        </div>

        <!-- Explication comptable -->
        <div class="card p-6">
            <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Traitement comptable</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                    <h4 class="font-medium text-success-700 dark:text-success-400 mb-2">Gains de change (compte 754)</h4>
                    <p class="text-sm text-success-600 dark:text-success-300">
                        Les gains de change sont comptabilises en produits financiers lorsque le taux de change au moment du paiement est plus favorable que lors de l'emission de la facture.
                    </p>
                </div>
                <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg">
                    <h4 class="font-medium text-danger-700 dark:text-danger-400 mb-2">Pertes de change (compte 654)</h4>
                    <p class="text-sm text-danger-600 dark:text-danger-300">
                        Les pertes de change sont comptabilisees en charges financieres lorsque le taux de change au moment du paiement est moins favorable que lors de l'emission de la facture.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.querySelector('select[name="period"]').addEventListener('change', function() {
            document.getElementById('custom-dates').classList.toggle('hidden', this.value !== 'custom');
        });

        const monthlyData = @json($monthlyData);

        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'Gains',
                    data: monthlyData.map(d => d.gains),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)'
                }, {
                    label: 'Pertes',
                    data: monthlyData.map(d => Math.abs(d.losses)),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: false },
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
