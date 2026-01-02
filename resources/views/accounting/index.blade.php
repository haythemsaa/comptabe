<x-app-layout>
    <x-slot name="title">Comptabilité</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Comptabilité</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Comptabilité</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    @if($currentYear)
                        Exercice {{ $currentYear->year }}
                    @else
                        Aucun exercice actif
                    @endif
                </p>
            </div>
            <a href="{{ route('accounting.entries.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle écriture
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Écritures</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">{{ number_format($stats['total_entries']) }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total débits</div>
                <div class="text-2xl font-bold text-primary-600">@currency($stats['total_debit'])</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total crédits</div>
                <div class="text-2xl font-bold text-success-600">@currency($stats['total_credit'])</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Non équilibrées</div>
                <div class="text-2xl font-bold {{ $stats['unbalanced'] > 0 ? 'text-danger-600' : 'text-success-600' }}">{{ $stats['unbalanced'] }}</div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('accounting.chart') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Plan comptable</div>
                        <div class="text-sm text-secondary-500">PCMN belge</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('accounting.journals') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Journaux</div>
                        <div class="text-sm text-secondary-500">{{ $journals->count() }} journaux</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('accounting.balance') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Balance</div>
                        <div class="text-sm text-secondary-500">Vue d'ensemble</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('accounting.ledger') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-danger-100 dark:bg-danger-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Grand livre</div>
                        <div class="text-sm text-secondary-500">Par compte</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Entries -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Écritures récentes</h2>
                        <a href="{{ route('accounting.entries') }}" class="text-sm text-primary-600 hover:text-primary-700">Voir tout</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N°</th>
                                    <th>Description</th>
                                    <th class="text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentEntries as $entry)
                                    <tr class="animate-fade-in">
                                        <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="font-mono text-sm">{{ $entry->entry_number }}</span>
                                            <div class="text-xs text-secondary-500">{{ $entry->journal->name }}</div>
                                        </td>
                                        <td>
                                            <div class="max-w-xs truncate">{{ $entry->description }}</div>
                                        </td>
                                        <td class="text-right font-medium">@currency($entry->total_amount)</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-8 text-secondary-500">
                                            Aucune écriture
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Journals -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Journaux</h2>
                    </div>
                    <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                        @forelse($journals as $journal)
                            <a href="{{ route('accounting.entries', ['journal' => $journal->id]) }}" class="block p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-{{ $journal->color ?? 'primary' }}-100 dark:bg-{{ $journal->color ?? 'primary' }}-900/30 rounded-lg flex items-center justify-center">
                                            <span class="text-sm font-bold text-{{ $journal->color ?? 'primary' }}-600">{{ strtoupper(substr($journal->code, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-secondary-900 dark:text-white">{{ $journal->name }}</div>
                                            <div class="text-xs text-secondary-500">{{ $journal->code }}</div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-secondary-500">{{ $journal->entries_count ?? 0 }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-secondary-500">
                                Aucun journal configuré
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
