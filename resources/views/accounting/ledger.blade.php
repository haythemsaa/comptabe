<x-app-layout>
    <x-slot name="title">Grand livre</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Grand livre</span>
    @endsection

    <div class="space-y-6">
        <!-- Header & Filters -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('accounting.ledger') }}" method="GET" class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="flex-1">
                            <label for="account" class="form-label">Compte</label>
                            <select name="account" id="account" class="form-select" onchange="this.form.submit()">
                                <option value="">Sélectionner un compte...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ request('account') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="form-label">Du</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-input">
                        </div>
                        <div>
                            <label for="date_to" class="form-label">Au</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-input">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtrer
                        </button>
                        @if($selectedAccount)
                            <button type="button" onclick="window.print()" class="btn btn-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Imprimer
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @if($selectedAccount)
            <!-- Account Info -->
            <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="text-sm text-primary-600 dark:text-primary-400">Compte sélectionné</div>
                            <div class="text-xl font-bold text-primary-900 dark:text-primary-100">
                                {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                            </div>
                            <div class="text-sm text-primary-600 dark:text-primary-400 mt-1">
                                Période du {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-primary-600 dark:text-primary-400">Solde final</div>
                            @php
                                $finalBalance = $movements->count() > 0 ? $movements->last()->running_balance : $openingBalance;
                            @endphp
                            <div class="text-2xl font-bold {{ $finalBalance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                                @currency(abs($finalBalance))
                                <span class="text-sm font-normal">{{ $finalBalance >= 0 ? 'D' : 'C' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">Mouvements</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>N° Pièce</th>
                                <th>Journal</th>
                                <th>Libellé</th>
                                <th class="text-right">Débit</th>
                                <th class="text-right">Crédit</th>
                                <th class="text-right">Solde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Opening Balance -->
                            <tr class="bg-secondary-50 dark:bg-secondary-800/50 font-medium">
                                <td>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</td>
                                <td colspan="3">Solde d'ouverture</td>
                                <td class="text-right">{{ $openingBalance > 0 ? number_format($openingBalance, 2, ',', ' ') . ' €' : '' }}</td>
                                <td class="text-right">{{ $openingBalance < 0 ? number_format(abs($openingBalance), 2, ',', ' ') . ' €' : '' }}</td>
                                <td class="text-right font-mono">
                                    {{ number_format(abs($openingBalance), 2, ',', ' ') }} €
                                    <span class="text-xs {{ $openingBalance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                                        {{ $openingBalance >= 0 ? 'D' : 'C' }}
                                    </span>
                                </td>
                            </tr>

                            @forelse($movements as $movement)
                                <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                    <td>{{ $movement->journalEntry->entry_date->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('accounting.entries.show', $movement->journalEntry) }}" class="font-mono text-primary-600 hover:text-primary-700">
                                            {{ $movement->journalEntry->entry_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary text-xs">
                                            {{ $movement->journalEntry->journal->code }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs">
                                        <div class="truncate" title="{{ $movement->description ?: $movement->journalEntry->description }}">
                                            {{ $movement->description ?: $movement->journalEntry->description }}
                                        </div>
                                    </td>
                                    <td class="text-right font-mono">
                                        {{ $movement->debit > 0 ? number_format($movement->debit, 2, ',', ' ') . ' €' : '' }}
                                    </td>
                                    <td class="text-right font-mono">
                                        {{ $movement->credit > 0 ? number_format($movement->credit, 2, ',', ' ') . ' €' : '' }}
                                    </td>
                                    <td class="text-right font-mono">
                                        {{ number_format(abs($movement->running_balance), 2, ',', ' ') }} €
                                        <span class="text-xs {{ $movement->running_balance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                                            {{ $movement->running_balance >= 0 ? 'D' : 'C' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-secondary-500">
                                        Aucun mouvement pour cette période
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-secondary-100 dark:bg-secondary-800 font-semibold">
                            <tr>
                                <td colspan="4">Totaux période</td>
                                <td class="text-right">@currency($movements->sum('debit'))</td>
                                <td class="text-right">@currency($movements->sum('credit'))</td>
                                <td class="text-right">
                                    @currency(abs($finalBalance))
                                    <span class="text-xs {{ $finalBalance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                                        {{ $finalBalance >= 0 ? 'D' : 'C' }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="card p-4">
                    <div class="text-sm text-secondary-500">Solde d'ouverture</div>
                    <div class="text-xl font-bold {{ $openingBalance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                        @currency(abs($openingBalance))
                        <span class="text-sm font-normal">{{ $openingBalance >= 0 ? 'D' : 'C' }}</span>
                    </div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-secondary-500">Total débits</div>
                    <div class="text-xl font-bold text-primary-600">@currency($movements->sum('debit'))</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-secondary-500">Total crédits</div>
                    <div class="text-xl font-bold text-success-600">@currency($movements->sum('credit'))</div>
                </div>
                <div class="card p-4">
                    <div class="text-sm text-secondary-500">Solde de clôture</div>
                    <div class="text-xl font-bold {{ $finalBalance >= 0 ? 'text-primary-600' : 'text-danger-600' }}">
                        @currency(abs($finalBalance))
                        <span class="text-sm font-normal">{{ $finalBalance >= 0 ? 'D' : 'C' }}</span>
                    </div>
                </div>
            </div>
        @else
            <!-- No Account Selected -->
            <div class="card">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-secondary-900 dark:text-white mb-2">Sélectionnez un compte</h3>
                    <p class="text-secondary-500">Choisissez un compte dans la liste pour afficher son grand livre</p>
                </div>
            </div>

            <!-- Quick Access by Class -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $classNames = [
                        '1' => ['name' => 'Fonds propres et dettes à long terme', 'color' => 'primary'],
                        '2' => ['name' => 'Actifs immobilisés', 'color' => 'success'],
                        '3' => ['name' => 'Stocks', 'color' => 'warning'],
                        '4' => ['name' => 'Créances et dettes à court terme', 'color' => 'danger'],
                        '5' => ['name' => 'Placements et disponibilités', 'color' => 'info'],
                        '6' => ['name' => 'Charges', 'color' => 'danger'],
                        '7' => ['name' => 'Produits', 'color' => 'success'],
                    ];
                    $accountsByClass = $accounts->groupBy(fn($a) => substr($a->code, 0, 1));
                @endphp

                @foreach($classNames as $classNumber => $classInfo)
                    @if(isset($accountsByClass[$classNumber]) && $accountsByClass[$classNumber]->count() > 0)
                        <div class="card">
                            <div class="card-header bg-{{ $classInfo['color'] }}-50 dark:bg-{{ $classInfo['color'] }}-900/20">
                                <h3 class="font-medium text-{{ $classInfo['color'] }}-900 dark:text-{{ $classInfo['color'] }}-100">
                                    Classe {{ $classNumber }} - {{ $classInfo['name'] }}
                                </h3>
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                                @foreach($accountsByClass[$classNumber]->take(10) as $account)
                                    <a
                                        href="{{ route('accounting.ledger', ['account' => $account->id]) }}"
                                        class="block px-4 py-2 text-sm hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors border-b border-secondary-100 dark:border-secondary-800 last:border-0"
                                    >
                                        <span class="font-mono text-secondary-500">{{ $account->code }}</span>
                                        <span class="ml-2">{{ $account->name }}</span>
                                    </a>
                                @endforeach
                                @if($accountsByClass[$classNumber]->count() > 10)
                                    <div class="px-4 py-2 text-sm text-secondary-500 text-center">
                                        +{{ $accountsByClass[$classNumber]->count() - 10 }} autres comptes
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { break-inside: avoid; box-shadow: none; border: 1px solid #e5e7eb; }
        }
    </style>
    @endpush
</x-app-layout>
