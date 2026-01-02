<x-app-layout>
    <x-slot name="title">Déclarations TVA</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">TVA</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Déclarations TVA</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Gérez vos déclarations TVA belges</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('vat.client-listing') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Listing clients
                </a>
                <a href="{{ route('vat.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle déclaration
                </a>
            </div>
        </div>

        <!-- Pending Periods Alert -->
        @if(count($pendingPeriods) > 0)
            <div class="card bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800">
                <div class="card-body">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-warning-900 dark:text-warning-100">Périodes manquantes</h3>
                            <p class="text-sm text-warning-700 dark:text-warning-300 mt-1">
                                Les déclarations suivantes n'ont pas encore été créées :
                            </p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($pendingPeriods as $period)
                                    <a
                                        href="{{ route('vat.create', ['type' => $period['type'], 'year' => $period['year'], 'period' => $period['period']]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-warning-100 dark:bg-warning-800 text-warning-800 dark:text-warning-200 rounded-lg text-sm hover:bg-warning-200 dark:hover:bg-warning-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        {{ $period['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('vat.client-listing') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Listing clients annuel</div>
                        <div class="text-sm text-secondary-500">Clients assujettis TVA</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('vat.intrastat') }}" class="card card-hover p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Intrastat</div>
                        <div class="text-sm text-secondary-500">Déclaration intracommunautaire</div>
                    </div>
                </div>
            </a>
            <div class="card p-4 bg-secondary-50 dark:bg-secondary-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-info-100 dark:bg-info-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-secondary-900 dark:text-white">Intervat</div>
                        <div class="text-sm text-secondary-500">Export fichiers XML</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Declarations Table -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-semibold text-secondary-900 dark:text-white">Déclarations</h2>
                <div class="text-sm text-secondary-500">{{ $declarations->total() }} déclaration(s)</div>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Type</th>
                            <th class="text-right">TVA due</th>
                            <th class="text-right">TVA déductible</th>
                            <th class="text-right">Solde</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($declarations as $declaration)
                            <tr class="group hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                <td>
                                    <div class="font-medium text-secondary-900 dark:text-white">
                                        @if($declaration->period_type === 'monthly')
                                            {{ $declaration->period_start->translatedFormat('F Y') }}
                                        @else
                                            T{{ ceil($declaration->period_start->month / 3) }} {{ $declaration->period_start->year }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-secondary-500">
                                        {{ $declaration->period_start->format('d/m') }} - {{ $declaration->period_end->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary text-xs">
                                        {{ $declaration->period_type === 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}
                                    </span>
                                </td>
                                <td class="text-right font-mono">@currency($declaration->output_vat)</td>
                                <td class="text-right font-mono">@currency($declaration->input_vat)</td>
                                <td class="text-right font-mono font-medium {{ $declaration->balance >= 0 ? 'text-danger-600' : 'text-success-600' }}">
                                    @if($declaration->balance >= 0)
                                        @currency($declaration->balance)
                                        <span class="text-xs text-secondary-500">à payer</span>
                                    @else
                                        @currency(abs($declaration->balance))
                                        <span class="text-xs text-secondary-500">crédit</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusConfig = [
                                            'draft' => ['label' => 'Brouillon', 'class' => 'warning'],
                                            'submitted' => ['label' => 'Soumise', 'class' => 'info'],
                                            'accepted' => ['label' => 'Acceptée', 'class' => 'success'],
                                            'rejected' => ['label' => 'Rejetée', 'class' => 'danger'],
                                        ];
                                        $status = $statusConfig[$declaration->status] ?? ['label' => $declaration->status, 'class' => 'secondary'];
                                    @endphp
                                    <span class="badge badge-{{ $status['class'] }} text-xs">{{ $status['label'] }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a
                                            href="{{ route('vat.show', $declaration) }}"
                                            class="btn-ghost btn-icon btn-sm"
                                            title="Voir"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        @if($declaration->status === 'draft')
                                            <a
                                                href="{{ route('vat.edit', $declaration) }}"
                                                class="btn-ghost btn-icon btn-sm"
                                                title="Modifier"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        <a
                                            href="{{ route('vat.export-intervat', $declaration) }}"
                                            class="btn-ghost btn-icon btn-sm"
                                            title="Export Intervat"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-secondary-300 dark:text-secondary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                    </svg>
                                    <p class="text-secondary-500 mb-4">Aucune déclaration TVA</p>
                                    <a href="{{ route('vat.create') }}" class="btn btn-primary">
                                        Créer une déclaration
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($declarations->hasPages())
                <div class="card-footer">
                    {{ $declarations->links() }}
                </div>
            @endif
        </div>

        <!-- Belgian VAT Info -->
        <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
            <div class="card-body">
                <h3 class="font-medium text-primary-900 dark:text-primary-100 mb-3">Rappel des obligations TVA belges</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-primary-700 dark:text-primary-300">
                    <div>
                        <h4 class="font-medium mb-2">Déclarations périodiques</h4>
                        <ul class="space-y-1">
                            <li>• <strong>Mensuelle</strong> : CA > 2.500.000 € ou régime optionnel</li>
                            <li>• <strong>Trimestrielle</strong> : CA ≤ 2.500.000 €</li>
                            <li>• Délai : 20 du mois suivant la période</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium mb-2">Listing clients annuel</h4>
                        <ul class="space-y-1">
                            <li>• Clients assujettis avec CA ≥ 250 €</li>
                            <li>• Délai : 31 mars de l'année suivante</li>
                            <li>• Format XML via Intervat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
