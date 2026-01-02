<x-app-layout>
    <x-slot name="title">Balance comptable</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('accounting.index') }}" class="text-secondary-500 hover:text-secondary-700">Comptabilité</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Balance</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Balance comptable</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Au {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
            </div>
            <form action="{{ route('accounting.balance') }}" method="GET" class="flex items-center gap-3">
                <input
                    type="date"
                    name="date"
                    value="{{ $date }}"
                    class="form-input"
                    onchange="this.form.submit()"
                >
                <button type="button" onclick="window.print()" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer
                </button>
            </form>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total débits</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">@currency($totals['debit'])</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total crédits</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">@currency($totals['credit'])</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Différence</div>
                <div class="text-2xl font-bold {{ abs($totals['debit'] - $totals['credit']) < 0.01 ? 'text-success-600' : 'text-danger-600' }}">
                    @currency(abs($totals['debit'] - $totals['credit']))
                </div>
            </div>
        </div>

        <!-- Balance Table -->
        @php
            $classNames = [
                '1' => 'Fonds propres et dettes à long terme',
                '2' => 'Actifs immobilisés',
                '3' => 'Stocks',
                '4' => 'Créances et dettes à court terme',
                '5' => 'Placements et disponibilités',
                '6' => 'Charges',
                '7' => 'Produits',
            ];
        @endphp

        @foreach($accounts as $classNumber => $classAccounts)
            <div class="card">
                <div class="card-header bg-secondary-50 dark:bg-secondary-800/50">
                    <h2 class="font-semibold text-secondary-900 dark:text-white">
                        Classe {{ $classNumber }} - {{ $classNames[$classNumber] ?? '' }}
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-24">Code</th>
                                <th>Libellé</th>
                                <th class="text-right">Débit</th>
                                <th class="text-right">Crédit</th>
                                <th class="text-right">Solde débiteur</th>
                                <th class="text-right">Solde créditeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classAccounts->sortBy('code') as $account)
                                <tr>
                                    <td class="font-mono">{{ $account->code }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td class="text-right">@currency($account->total_debit)</td>
                                    <td class="text-right">@currency($account->total_credit)</td>
                                    <td class="text-right font-medium {{ $account->balance > 0 ? 'text-primary-600' : '' }}">
                                        {{ $account->balance > 0 ? number_format($account->balance, 2, ',', ' ') . ' €' : '' }}
                                    </td>
                                    <td class="text-right font-medium {{ $account->balance < 0 ? 'text-success-600' : '' }}">
                                        {{ $account->balance < 0 ? number_format(abs($account->balance), 2, ',', ' ') . ' €' : '' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-secondary-50 dark:bg-secondary-800/50">
                            <tr class="font-semibold">
                                <td colspan="2">Total classe {{ $classNumber }}</td>
                                <td class="text-right">@currency($classAccounts->sum('total_debit'))</td>
                                <td class="text-right">@currency($classAccounts->sum('total_credit'))</td>
                                <td class="text-right">@currency($classAccounts->where('balance', '>', 0)->sum('balance'))</td>
                                <td class="text-right">@currency(abs($classAccounts->where('balance', '<', 0)->sum('balance')))</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Grand Total -->
        <div class="card bg-secondary-900 dark:bg-white text-white dark:text-secondary-900">
            <div class="card-body">
                <div class="grid grid-cols-6 gap-4 font-semibold">
                    <div class="col-span-2">TOTAL GÉNÉRAL</div>
                    <div class="text-right">@currency($totals['debit'])</div>
                    <div class="text-right">@currency($totals['credit'])</div>
                    <div class="text-right">@currency($accounts->flatten()->where('balance', '>', 0)->sum('balance'))</div>
                    <div class="text-right">@currency(abs($accounts->flatten()->where('balance', '<', 0)->sum('balance')))</div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { break-inside: avoid; }
        }
    </style>
    @endpush
</x-app-layout>
