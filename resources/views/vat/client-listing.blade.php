<x-app-layout>
    <x-slot name="title">Listing clients annuel</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('vat.index') }}" class="text-secondary-500 hover:text-secondary-700">TVA</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Listing clients</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Listing clients annuel</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Clients assujettis à la TVA - Année {{ $year }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('vat.client-listing') }}" method="GET" class="flex items-center gap-2">
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
                @if($clients->count() > 0)
                    <a href="{{ route('vat.export-client-listing', ['year' => $year]) }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Intervat
                    </a>
                @endif
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Nombre de clients</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white">{{ $clients->count() }}</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total chiffre d'affaires</div>
                <div class="text-2xl font-bold text-primary-600">@currency($clients->sum('total_excl'))</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Total TVA</div>
                <div class="text-2xl font-bold text-success-600">@currency($clients->sum('total_vat'))</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Délai de dépôt</div>
                <div class="text-xl font-bold text-secondary-900 dark:text-white">31/03/{{ $year + 1 }}</div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="card bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
            <div class="card-body">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-info-900 dark:text-info-100">Rappel des obligations</h3>
                        <ul class="text-sm text-info-700 dark:text-info-300 mt-2 space-y-1">
                            <li>• Le listing reprend tous les clients assujettis à la TVA avec un CA ≥ 250 €</li>
                            <li>• À déposer via Intervat avant le 31 mars de l'année suivante</li>
                            <li>• Seuls les clients avec un numéro de TVA valide sont inclus dans l'export</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-secondary-900 dark:text-white">
                    Clients assujettis TVA (CA ≥ 250 €)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>N° TVA</th>
                            <th class="text-center">Factures</th>
                            <th class="text-right">Chiffre d'affaires</th>
                            <th class="text-right">TVA facturée</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td>
                                    <a href="{{ route('partners.show', $client['partner']) }}" class="font-medium text-primary-600 hover:text-primary-700">
                                        {{ $client['partner']->name }}
                                    </a>
                                </td>
                                <td class="font-mono">
                                    @if($client['vat_number'])
                                        {{ $client['vat_number'] }}
                                    @else
                                        <span class="text-secondary-400">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $client['invoice_count'] }}</span>
                                </td>
                                <td class="text-right font-mono font-medium">
                                    @currency($client['total_excl'])
                                </td>
                                <td class="text-right font-mono">
                                    @currency($client['total_vat'])
                                </td>
                                <td class="text-center">
                                    @if($client['vat_number'])
                                        <span class="badge badge-success text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Inclus
                                        </span>
                                    @else
                                        <span class="badge badge-warning text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            N° TVA manquant
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-secondary-500">Aucun client assujetti pour l'année {{ $year }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($clients->count() > 0)
                        <tfoot class="bg-secondary-100 dark:bg-secondary-800">
                            <tr class="font-semibold">
                                <td colspan="2">Total</td>
                                <td class="text-center">{{ $clients->sum('invoice_count') }}</td>
                                <td class="text-right font-mono">@currency($clients->sum('total_excl'))</td>
                                <td class="text-right font-mono">@currency($clients->sum('total_vat'))</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Clients without VAT number warning -->
        @php
            $clientsWithoutVat = $clients->filter(fn($c) => !$c['vat_number']);
        @endphp
        @if($clientsWithoutVat->count() > 0)
            <div class="card bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800">
                <div class="card-body">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-warning-900 dark:text-warning-100">Clients sans numéro de TVA</h3>
                            <p class="text-sm text-warning-700 dark:text-warning-300 mt-1">
                                {{ $clientsWithoutVat->count() }} client(s) n'ont pas de numéro de TVA et ne seront pas inclus dans l'export Intervat.
                                Veuillez compléter leurs fiches si ce sont des assujettis.
                            </p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($clientsWithoutVat->take(5) as $client)
                                    <a
                                        href="{{ route('partners.edit', $client['partner']) }}"
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-warning-100 dark:bg-warning-800 text-warning-800 dark:text-warning-200 rounded text-sm hover:bg-warning-200"
                                    >
                                        {{ $client['partner']->name }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                @endforeach
                                @if($clientsWithoutVat->count() > 5)
                                    <span class="text-sm text-warning-600">+{{ $clientsWithoutVat->count() - 5 }} autres</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
