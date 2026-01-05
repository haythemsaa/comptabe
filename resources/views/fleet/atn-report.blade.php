<x-app-layout>
    <x-slot name="title">Rapport ATN {{ $year }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('fleet.index') }}" class="text-secondary-500 hover:text-primary-500 transition-colors">Flotte</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-700 dark:text-white font-medium">Rapport ATN</span>
    @endsection

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-secondary-800 dark:text-white">Rapport ATN {{ $year }}</h1>
                <p class="text-secondary-500 dark:text-secondary-400 mt-1">Avantage de Toute Nature - Vehicules de societe</p>
            </div>
            <div class="flex items-center gap-3">
                <form method="GET" class="flex items-center gap-2">
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
                <a href="{{ route('fleet.atn-report', ['year' => $year, 'export' => 'pdf']) }}" class="btn btn-outline-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter PDF
                </a>
            </div>
        </div>

        <!-- Totaux -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-primary-100 text-primary-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Vehicules attribues</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ $vehicles->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-success-100 text-success-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">ATN total annuel</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($totalAtnAnnual, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-warning-100 text-warning-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">ATN moyen/mois</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($totalAtnAnnual / 12, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
            <div class="card p-4">
                <div class="flex items-center gap-3">
                    <div class="stat-icon bg-info-100 text-info-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-secondary-500">Cotis. ONSS estimees</p>
                        <p class="text-xl font-bold text-secondary-800 dark:text-white">{{ number_format($totalAtnAnnual * 0.3307, 2, ',', ' ') }} &euro;</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info box -->
        <div class="card p-4 bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-info-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-info-700 dark:text-info-300">
                    <p class="font-medium">Calcul ATN Belgique {{ $year }}</p>
                    <p class="mt-1">L'ATN est calcule selon la formule: Valeur catalogue x % CO2 x 6/7 x coefficient d'age. Le pourcentage CO2 de reference pour {{ $year }} est de {{ $co2Reference }}% (diesel) / {{ $co2Reference - 4 }}% (essence/autres).</p>
                </div>
            </div>
        </div>

        <!-- Tableau detaille -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-50 dark:bg-secondary-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Vehicule</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase">Attribue a</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">Valeur catalogue</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">CO2</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">% CO2</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-secondary-500 uppercase">Coef. age</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">ATN/mois</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-secondary-500 uppercase">ATN/an</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                        @forelse($vehicles as $vehicle)
                            @php
                                $atn = $vehicle->calculateAtn($year);
                            @endphp
                            <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('fleet.show', $vehicle) }}" class="text-primary-600 hover:text-primary-800">
                                        <div class="font-medium">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                                        <div class="text-xs text-secondary-500">{{ $vehicle->license_plate }}</div>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-secondary-600 dark:text-secondary-400">
                                    {{ $vehicle->assignedUser?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-secondary-600 dark:text-secondary-400">
                                    {{ number_format($atn['catalog_value'] ?? 0, 2, ',', ' ') }} &euro;
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-secondary-600 dark:text-secondary-400">
                                    {{ $vehicle->co2_emission ?? '-' }} g/km
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="badge {{ ($atn['co2_percentage'] ?? 0) > 10 ? 'badge-warning' : 'badge-success' }}">
                                        {{ number_format($atn['co2_percentage'] ?? 0, 2) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-secondary-600 dark:text-secondary-400">
                                    {{ number_format($atn['age_coefficient'] ?? 1, 0) }}%
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-secondary-800 dark:text-white">
                                    {{ number_format($atn['monthly'] ?? 0, 2, ',', ' ') }} &euro;
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-primary-600">
                                    {{ number_format($atn['annual'] ?? 0, 2, ',', ' ') }} &euro;
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-secondary-500">
                                    Aucun vehicule attribue
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($vehicles->count() > 0)
                    <tfoot class="bg-secondary-100 dark:bg-secondary-800">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-sm font-medium text-secondary-700 dark:text-secondary-300 text-right">Total:</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-secondary-800 dark:text-white">{{ number_format($totalAtnAnnual / 12, 2, ',', ' ') }} &euro;</td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-primary-600">{{ number_format($totalAtnAnnual, 2, ',', ' ') }} &euro;</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Explication du calcul -->
        <div class="card p-6">
            <h3 class="text-lg font-medium text-secondary-800 dark:text-white mb-4">Methode de calcul ATN</h3>
            <div class="prose prose-sm dark:prose-invert max-w-none">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Formule de base</h4>
                        <div class="bg-secondary-100 dark:bg-secondary-800 rounded-lg p-4 font-mono text-sm">
                            ATN = Valeur catalogue × % CO2 × 6/7 × Coef. age
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Valeur minimum</h4>
                        <div class="bg-secondary-100 dark:bg-secondary-800 rounded-lg p-4">
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">L'ATN annuel ne peut etre inferieur a <strong>1.600 EUR</strong> (montant {{ $year }}).</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Pourcentage CO2</h4>
                        <ul class="text-sm text-secondary-600 dark:text-secondary-400 space-y-1">
                            <li>• Diesel: {{ $co2Reference }}% de reference</li>
                            <li>• Essence/Autres: {{ $co2Reference - 4 }}% de reference</li>
                            <li>• +0.1% par g/km au-dessus de la reference</li>
                            <li>• -0.1% par g/km en dessous (min 4%)</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">Coefficient d'age</h4>
                        <ul class="text-sm text-secondary-600 dark:text-secondary-400 space-y-1">
                            <li>• Annee 1: 100%</li>
                            <li>• Annee 2: 94%</li>
                            <li>• Annee 3: 88%</li>
                            <li>• Annee 4: 82%</li>
                            <li>• Annee 5+: 76% (minimum)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
