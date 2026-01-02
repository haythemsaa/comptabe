<x-app-layout>
    <x-slot name="title">Déclaration Intrastat</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('vat.index') }}" class="text-secondary-500 hover:text-secondary-700">TVA</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Intrastat</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Déclaration Intrastat</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Statistiques des échanges intracommunautaires - {{ Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                </p>
            </div>
            <form action="{{ route('vat.intrastat') }}" method="GET" class="flex items-center gap-2">
                <select name="month" class="form-select" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Arrivées (achats)</div>
                <div class="text-2xl font-bold text-primary-600">{{ $arrivals->count() }}</div>
                <div class="text-sm text-secondary-500">opérations</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Valeur arrivées</div>
                <div class="text-2xl font-bold text-primary-600">@currency($arrivals->sum('total_excl_vat'))</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Expéditions (ventes)</div>
                <div class="text-2xl font-bold text-success-600">{{ $dispatches->count() }}</div>
                <div class="text-sm text-secondary-500">opérations</div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500">Valeur expéditions</div>
                <div class="text-2xl font-bold text-success-600">@currency($dispatches->sum('total_excl_vat'))</div>
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
                        <h3 class="font-medium text-info-900 dark:text-info-100">Obligations Intrastat</h3>
                        <ul class="text-sm text-info-700 dark:text-info-300 mt-2 space-y-1">
                            <li>• <strong>Arrivées</strong> : Obligatoire si vos achats intracommunautaires dépassent 1.500.000 €/an</li>
                            <li>• <strong>Expéditions</strong> : Obligatoire si vos ventes intracommunautaires dépassent 1.000.000 €/an</li>
                            <li>• Délai : 20ème jour ouvrable du mois suivant</li>
                            <li>• Déclaration via <a href="https://www.nbb.be/fr/statistiques/commerce-exterieur/intrastat" target="_blank" class="underline">la Banque Nationale de Belgique</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'dispatches' }">
            <div class="flex border-b border-secondary-200 dark:border-secondary-700 mb-6">
                <button
                    type="button"
                    @click="activeTab = 'dispatches'"
                    class="px-4 py-2 font-medium text-sm border-b-2 transition-colors"
                    :class="activeTab === 'dispatches' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                    </svg>
                    Expéditions ({{ $dispatches->count() }})
                </button>
                <button
                    type="button"
                    @click="activeTab = 'arrivals'"
                    class="px-4 py-2 font-medium text-sm border-b-2 transition-colors"
                    :class="activeTab === 'arrivals' ? 'border-primary-500 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700'"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    Arrivées ({{ $arrivals->count() }})
                </button>
            </div>

            <!-- Dispatches Tab -->
            <div x-show="activeTab === 'dispatches'" x-transition>
                <div class="card">
                    <div class="card-header bg-success-50 dark:bg-success-900/20">
                        <h2 class="font-semibold text-success-900 dark:text-success-100">
                            Expéditions vers l'UE (Ventes intracommunautaires)
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Pays</th>
                                    <th class="text-right">Valeur facture</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dispatches as $invoice)
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-primary-600 hover:text-primary-700">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $invoice->partner->name }}</div>
                                            <div class="text-xs text-secondary-500 font-mono">{{ $invoice->partner->vat_number }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $countryCode = substr($invoice->partner->vat_number ?? '', 0, 2);
                                            @endphp
                                            <span class="badge badge-secondary">{{ $countryCode }}</span>
                                        </td>
                                        <td class="text-right font-mono font-medium">
                                            @currency($invoice->total_excl_vat)
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-secondary-500">
                                            Aucune vente intracommunautaire pour cette période
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($dispatches->count() > 0)
                                <tfoot class="bg-success-50 dark:bg-success-900/20">
                                    <tr class="font-semibold">
                                        <td colspan="4">Total expéditions</td>
                                        <td class="text-right font-mono">@currency($dispatches->sum('total_excl_vat'))</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Arrivals Tab -->
            <div x-show="activeTab === 'arrivals'" x-transition>
                <div class="card">
                    <div class="card-header bg-primary-50 dark:bg-primary-900/20">
                        <h2 class="font-semibold text-primary-900 dark:text-primary-100">
                            Arrivées de l'UE (Achats intracommunautaires)
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Facture</th>
                                    <th>Fournisseur</th>
                                    <th>Pays d'origine</th>
                                    <th class="text-right">Valeur facture</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($arrivals as $invoice)
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-primary-600 hover:text-primary-700">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="font-medium">{{ $invoice->partner->name }}</div>
                                            <div class="text-xs text-secondary-500 font-mono">{{ $invoice->partner->vat_number }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $countryCode = substr($invoice->partner->vat_number ?? '', 0, 2);
                                            @endphp
                                            <span class="badge badge-secondary">{{ $countryCode }}</span>
                                        </td>
                                        <td class="text-right font-mono font-medium">
                                            @currency($invoice->total_excl_vat)
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-secondary-500">
                                            Aucun achat intracommunautaire pour cette période
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($arrivals->count() > 0)
                                <tfoot class="bg-primary-50 dark:bg-primary-900/20">
                                    <tr class="font-semibold">
                                        <td colspan="4">Total arrivées</td>
                                        <td class="text-right font-mono">@currency($arrivals->sum('total_excl_vat'))</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Dispatches by Country -->
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Expéditions par pays</h3>
                </div>
                <div class="card-body">
                    @php
                        $dispatchesByCountry = $dispatches->groupBy(fn($i) => substr($i->partner->vat_number ?? 'XX', 0, 2))
                            ->map(fn($group) => $group->sum('total_excl_vat'))
                            ->sortDesc();
                    @endphp
                    @if($dispatchesByCountry->count() > 0)
                        <div class="space-y-3">
                            @foreach($dispatchesByCountry as $country => $total)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center font-bold text-xs text-success-600">
                                            {{ $country }}
                                        </span>
                                        <span class="text-secondary-700 dark:text-secondary-300">{{ $country }}</span>
                                    </div>
                                    <span class="font-mono font-medium">@currency($total)</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-secondary-500 text-center py-4">Aucune expédition</p>
                    @endif
                </div>
            </div>

            <!-- Arrivals by Country -->
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-secondary-900 dark:text-white">Arrivées par pays</h3>
                </div>
                <div class="card-body">
                    @php
                        $arrivalsByCountry = $arrivals->groupBy(fn($i) => substr($i->partner->vat_number ?? 'XX', 0, 2))
                            ->map(fn($group) => $group->sum('total_excl_vat'))
                            ->sortDesc();
                    @endphp
                    @if($arrivalsByCountry->count() > 0)
                        <div class="space-y-3">
                            @foreach($arrivalsByCountry as $country => $total)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center font-bold text-xs text-primary-600">
                                            {{ $country }}
                                        </span>
                                        <span class="text-secondary-700 dark:text-secondary-300">{{ $country }}</span>
                                    </div>
                                    <span class="font-mono font-medium">@currency($total)</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-secondary-500 text-center py-4">Aucune arrivée</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
