<x-app-layout>
    <x-slot name="title">Écriture {{ $entry->entry_number }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.entries') }}" class="text-secondary-500 hover:text-secondary-700">Écritures</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $entry->entry_number }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $entry->entry_number }}</h1>
                    @php
                        $statusConfig = [
                            'draft' => ['label' => 'Brouillon', 'class' => 'warning'],
                            'posted' => ['label' => 'Validée', 'class' => 'success'],
                            'cancelled' => ['label' => 'Annulée', 'class' => 'danger'],
                        ];
                        $status = $statusConfig[$entry->status] ?? ['label' => $entry->status, 'class' => 'secondary'];
                    @endphp
                    <span class="badge badge-{{ $status['class'] }}">{{ $status['label'] }}</span>
                </div>
                <p class="text-secondary-600 dark:text-secondary-400">{{ $entry->description }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($entry->status === 'draft')
                    <a href="{{ route('accounting.entries.edit', $entry) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <form action="{{ route('accounting.entries.post', $entry) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Valider cette écriture ? Cette action est irréversible.')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Valider
                        </button>
                    </form>
                @endif
                <button type="button" onclick="window.print()" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Entry Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Entry Lines -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes d'écriture</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-8">#</th>
                                    <th class="w-24">Compte</th>
                                    <th>Libellé</th>
                                    <th class="text-right">Débit</th>
                                    <th class="text-right">Crédit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entry->lines->sortBy('line_number') as $line)
                                    <tr>
                                        <td class="text-secondary-400">{{ $line->line_number }}</td>
                                        <td>
                                            <a href="{{ route('accounting.ledger', ['account' => $line->account_id]) }}" class="font-mono text-primary-600 hover:text-primary-700">
                                                {{ $line->account->code }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $line->account->name }}</div>
                                            @if($line->description)
                                                <div class="text-sm text-secondary-500">{{ $line->description }}</div>
                                            @endif
                                        </td>
                                        <td class="text-right font-mono">
                                            @if($line->debit > 0)
                                                <span class="text-primary-600 font-medium">{{ number_format($line->debit, 2, ',', ' ') }} €</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-mono">
                                            @if($line->credit > 0)
                                                <span class="text-success-600 font-medium">{{ number_format($line->credit, 2, ',', ' ') }} €</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-secondary-50 dark:bg-secondary-800/50">
                                <tr class="font-semibold">
                                    <td colspan="3">Total</td>
                                    <td class="text-right font-mono text-primary-600">@currency($entry->lines->sum('debit'))</td>
                                    <td class="text-right font-mono text-success-600">@currency($entry->lines->sum('credit'))</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Balance Check -->
                @php
                    $difference = $entry->lines->sum('debit') - $entry->lines->sum('credit');
                    $isBalanced = abs($difference) < 0.01;
                @endphp
                <div class="card {{ $isBalanced ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-800' : 'bg-danger-50 dark:bg-danger-900/20 border-danger-200 dark:border-danger-800' }}">
                    <div class="card-body">
                        <div class="flex items-center gap-3">
                            @if($isBalanced)
                                <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-success-900 dark:text-success-100">Écriture équilibrée</div>
                                    <div class="text-sm text-success-700 dark:text-success-300">Les débits et crédits sont égaux</div>
                                </div>
                            @else
                                <svg class="w-8 h-8 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <div class="font-medium text-danger-900 dark:text-danger-100">Écriture non équilibrée</div>
                                    <div class="text-sm text-danger-700 dark:text-danger-300">Différence : @currency(abs($difference))</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Related Documents -->
                @if($entry->invoice || $entry->bankTransaction)
                    <div class="card">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Documents liés</h2>
                        </div>
                        <div class="divide-y divide-secondary-100 dark:divide-secondary-800">
                            @if($entry->invoice)
                                <a href="{{ route('invoices.show', $entry->invoice) }}" class="flex items-center gap-4 p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-secondary-900 dark:text-white">Facture {{ $entry->invoice->invoice_number }}</div>
                                        <div class="text-sm text-secondary-500">{{ $entry->invoice->partner->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-medium">@currency($entry->invoice->total_amount)</div>
                                        <div class="text-xs text-secondary-500">{{ $entry->invoice->invoice_date->format('d/m/Y') }}</div>
                                    </div>
                                </a>
                            @endif

                            @if($entry->bankTransaction)
                                <a href="{{ route('bank.transactions.show', $entry->bankTransaction) }}" class="flex items-center gap-4 p-4 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-secondary-900 dark:text-white">Transaction bancaire</div>
                                        <div class="text-sm text-secondary-500">{{ $entry->bankTransaction->description }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-mono font-medium {{ $entry->bankTransaction->amount >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                            @currency($entry->bankTransaction->amount)
                                        </div>
                                        <div class="text-xs text-secondary-500">{{ $entry->bankTransaction->transaction_date->format('d/m/Y') }}</div>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Entry Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <div class="text-sm text-secondary-500">Journal</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="badge badge-{{ $entry->journal->color ?? 'primary' }}">{{ $entry->journal->code }}</span>
                                <span class="font-medium">{{ $entry->journal->name }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="text-sm text-secondary-500">Date</div>
                            <div class="font-medium mt-1">{{ $entry->entry_date->format('d/m/Y') }}</div>
                        </div>

                        @if($entry->reference)
                            <div>
                                <div class="text-sm text-secondary-500">Référence</div>
                                <div class="font-medium mt-1">{{ $entry->reference }}</div>
                            </div>
                        @endif

                        <div>
                            <div class="text-sm text-secondary-500">Montant total</div>
                            <div class="text-2xl font-bold text-secondary-900 dark:text-white mt-1">@currency($entry->total_amount)</div>
                        </div>

                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="text-sm text-secondary-500">Créé le</div>
                            <div class="text-sm mt-1">{{ $entry->created_at->format('d/m/Y à H:i') }}</div>
                        </div>

                        @if($entry->posted_at)
                            <div>
                                <div class="text-sm text-secondary-500">Validé le</div>
                                <div class="text-sm mt-1">{{ $entry->posted_at->format('d/m/Y à H:i') }}</div>
                            </div>
                        @endif

                        @if($entry->created_by)
                            <div>
                                <div class="text-sm text-secondary-500">Créé par</div>
                                <div class="text-sm mt-1">{{ $entry->creator->name ?? 'Système' }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Actions</h2>
                    </div>
                    <div class="card-body space-y-2">
                        <a href="{{ route('accounting.entries.duplicate', $entry) }}" class="btn btn-secondary w-full justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Dupliquer
                        </a>

                        @if($entry->status === 'posted')
                            <a href="{{ route('accounting.entries.reverse', $entry) }}" class="btn btn-secondary w-full justify-start">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Extourner
                            </a>
                        @endif

                        @if($entry->status === 'draft')
                            <form action="{{ route('accounting.entries.destroy', $entry) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="btn btn-danger w-full justify-start"
                                    onclick="return confirm('Supprimer cette écriture ?')"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex gap-2">
                    @if($previousEntry = $entry->previousEntry())
                        <a href="{{ route('accounting.entries.show', $previousEntry) }}" class="btn btn-secondary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Précédente
                        </a>
                    @endif
                    @if($nextEntry = $entry->nextEntry())
                        <a href="{{ route('accounting.entries.show', $nextEntry) }}" class="btn btn-secondary flex-1 justify-end">
                            Suivante
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { break-inside: avoid; box-shadow: none; }
        }
    </style>
    @endpush
</x-app-layout>
