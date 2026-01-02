<x-app-layout>
    <x-slot name="title">Déclaration TVA - {{ $declaration->period_start->translatedFormat('F Y') }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('vat.index') }}" class="text-secondary-500 hover:text-secondary-700">TVA</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">{{ $declaration->period_start->translatedFormat('F Y') }}</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                        Déclaration TVA - {{ $declaration->period_start->translatedFormat('F Y') }}
                    </h1>
                    @php
                        $statusConfig = [
                            'draft' => ['label' => 'Brouillon', 'class' => 'warning'],
                            'submitted' => ['label' => 'Soumise', 'class' => 'info'],
                            'accepted' => ['label' => 'Acceptée', 'class' => 'success'],
                            'rejected' => ['label' => 'Rejetée', 'class' => 'danger'],
                        ];
                        $status = $statusConfig[$declaration->status] ?? ['label' => $declaration->status, 'class' => 'secondary'];
                    @endphp
                    <span class="badge badge-{{ $status['class'] }}">{{ $status['label'] }}</span>
                </div>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Période du {{ $declaration->period_start->format('d/m/Y') }} au {{ $declaration->period_end->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if($declaration->status === 'draft')
                    <a href="{{ route('vat.edit', $declaration) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <form action="{{ route('vat.submit', $declaration) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Marquer cette déclaration comme soumise ?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Valider
                        </button>
                    </form>
                @endif
                <a href="{{ route('vat.export-intervat', $declaration) }}" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Intervat
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-6">
                <div class="text-sm text-secondary-500 mb-1">TVA due</div>
                <div class="text-3xl font-bold text-danger-600">@currency($declaration->output_vat)</div>
            </div>
            <div class="card p-6">
                <div class="text-sm text-secondary-500 mb-1">TVA déductible</div>
                <div class="text-3xl font-bold text-success-600">@currency($declaration->input_vat)</div>
            </div>
            <div class="card p-6">
                <div class="text-sm text-secondary-500 mb-1">Solde</div>
                <div class="text-3xl font-bold {{ $declaration->balance >= 0 ? 'text-danger-600' : 'text-success-600' }}">
                    @currency(abs($declaration->balance))
                </div>
                <div class="text-sm {{ $declaration->balance >= 0 ? 'text-danger-600' : 'text-success-600' }}">
                    {{ $declaration->balance >= 0 ? 'À payer' : 'Crédit TVA' }}
                </div>
            </div>
            <div class="card p-6">
                <div class="text-sm text-secondary-500 mb-1">Type</div>
                <div class="text-xl font-bold text-secondary-900 dark:text-white">
                    {{ $declaration->period_type === 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}
                </div>
                <div class="text-sm text-secondary-500">
                    Délai : {{ $declaration->period_end->addDays(20)->format('d/m/Y') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grid Values -->
            <div class="lg:col-span-2 space-y-6">
                @php
                    $grids = $declaration->grid_values ?? [];
                @endphp

                <!-- Section I: Opérations à la sortie -->
                <div class="card">
                    <div class="card-header bg-primary-50 dark:bg-primary-900/20">
                        <h2 class="font-semibold text-primary-900 dark:text-primary-100">I. Opérations à la sortie</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-16">Grille</th>
                                    <th>Description</th>
                                    <th class="text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-primary text-xs">00</span></td>
                                    <td>Opérations soumises à un régime particulier</td>
                                    <td class="text-right font-mono">@currency($grids['00'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary text-xs">01</span></td>
                                    <td>Opérations au taux de 6%</td>
                                    <td class="text-right font-mono">@currency($grids['01'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary text-xs">02</span></td>
                                    <td>Opérations au taux de 12%</td>
                                    <td class="text-right font-mono">@currency($grids['02'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-primary text-xs">03</span></td>
                                    <td>Opérations au taux de 21%</td>
                                    <td class="text-right font-mono">@currency($grids['03'] ?? 0)</td>
                                </tr>
                                <tr class="bg-secondary-50 dark:bg-secondary-800/30">
                                    <td><span class="badge badge-success text-xs">44</span></td>
                                    <td>Services intracommunautaires</td>
                                    <td class="text-right font-mono">@currency($grids['44'] ?? 0)</td>
                                </tr>
                                <tr class="bg-secondary-50 dark:bg-secondary-800/30">
                                    <td><span class="badge badge-success text-xs">46</span></td>
                                    <td>Livraisons intracommunautaires exonérées</td>
                                    <td class="text-right font-mono">@currency($grids['46'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-secondary text-xs">47</span></td>
                                    <td>Autres opérations exemptées</td>
                                    <td class="text-right font-mono">@currency($grids['47'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-danger text-xs">48</span></td>
                                    <td>Notes de crédit émises</td>
                                    <td class="text-right font-mono">@currency($grids['48'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-danger text-xs">49</span></td>
                                    <td>Notes de crédit reçues</td>
                                    <td class="text-right font-mono">@currency($grids['49'] ?? 0)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section II: Opérations à l'entrée -->
                <div class="card">
                    <div class="card-header bg-success-50 dark:bg-success-900/20">
                        <h2 class="font-semibold text-success-900 dark:text-success-100">II. Opérations à l'entrée</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-16">Grille</th>
                                    <th>Description</th>
                                    <th class="text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success text-xs">81</span></td>
                                    <td>Achats de marchandises et matières premières</td>
                                    <td class="text-right font-mono">@currency($grids['81'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success text-xs">82</span></td>
                                    <td>Achats de services et biens divers</td>
                                    <td class="text-right font-mono">@currency($grids['82'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success text-xs">83</span></td>
                                    <td>Achats de biens d'investissement</td>
                                    <td class="text-right font-mono">@currency($grids['83'] ?? 0)</td>
                                </tr>
                                <tr class="bg-secondary-50 dark:bg-secondary-800/30">
                                    <td><span class="badge badge-info text-xs">86</span></td>
                                    <td>Acquisitions intracommunautaires (services)</td>
                                    <td class="text-right font-mono">@currency($grids['86'] ?? 0)</td>
                                </tr>
                                <tr class="bg-secondary-50 dark:bg-secondary-800/30">
                                    <td><span class="badge badge-info text-xs">87</span></td>
                                    <td>Autres opérations à l'entrée</td>
                                    <td class="text-right font-mono">@currency($grids['87'] ?? 0)</td>
                                </tr>
                                <tr class="bg-secondary-50 dark:bg-secondary-800/30">
                                    <td><span class="badge badge-info text-xs">88</span></td>
                                    <td>Opérations régime du cocontractant</td>
                                    <td class="text-right font-mono">@currency($grids['88'] ?? 0)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section III: TVA -->
                <div class="card">
                    <div class="card-header bg-warning-50 dark:bg-warning-900/20">
                        <h2 class="font-semibold text-warning-900 dark:text-warning-100">III. Calcul de la TVA</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-16">Grille</th>
                                    <th>Description</th>
                                    <th class="text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-warning text-xs">54</span></td>
                                    <td>TVA sur opérations (grilles 01, 02, 03)</td>
                                    <td class="text-right font-mono">@currency($grids['54'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning text-xs">55</span></td>
                                    <td>TVA sur grille 86</td>
                                    <td class="text-right font-mono">@currency($grids['55'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning text-xs">56</span></td>
                                    <td>TVA sur grille 87</td>
                                    <td class="text-right font-mono">@currency($grids['56'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning text-xs">57</span></td>
                                    <td>TVA sur grille 88</td>
                                    <td class="text-right font-mono">@currency($grids['57'] ?? 0)</td>
                                </tr>
                                <tr class="bg-success-50 dark:bg-success-900/20">
                                    <td><span class="badge badge-success text-xs">59</span></td>
                                    <td>TVA déductible</td>
                                    <td class="text-right font-mono font-medium text-success-600">@currency($grids['59'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-danger text-xs">61</span></td>
                                    <td>Régularisations en faveur de l'État</td>
                                    <td class="text-right font-mono">@currency($grids['61'] ?? 0)</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success text-xs">62</span></td>
                                    <td>Régularisations en faveur du déclarant</td>
                                    <td class="text-right font-mono">@currency($grids['62'] ?? 0)</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-secondary-100 dark:bg-secondary-800">
                                <tr class="font-semibold">
                                    <td><span class="badge badge-danger text-xs">71</span></td>
                                    <td>Solde en faveur de l'État</td>
                                    <td class="text-right font-mono text-danger-600">@currency($grids['71'] ?? 0)</td>
                                </tr>
                                <tr class="font-semibold">
                                    <td><span class="badge badge-success text-xs">72</span></td>
                                    <td>Solde en faveur du déclarant</td>
                                    <td class="text-right font-mono text-success-600">@currency($grids['72'] ?? 0)</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Historique</h3>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 bg-success-100 dark:bg-success-900/30 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-secondary-900 dark:text-white">Créée</div>
                                    <div class="text-sm text-secondary-500">{{ $declaration->created_at->format('d/m/Y à H:i') }}</div>
                                </div>
                            </div>

                            @if($declaration->submitted_at)
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 bg-info-100 dark:bg-info-900/30 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-secondary-900 dark:text-white">Soumise</div>
                                        <div class="text-sm text-secondary-500">{{ $declaration->submitted_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="font-semibold text-secondary-900 dark:text-white">Actions</h3>
                    </div>
                    <div class="card-body space-y-2">
                        <a href="{{ route('vat.export-intervat', $declaration) }}" class="btn btn-secondary w-full justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Télécharger fichier Intervat
                        </a>
                        <button type="button" onclick="window.print()" class="btn btn-secondary w-full justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Imprimer
                        </button>
                    </div>
                </div>

                <!-- Intervat Info -->
                <div class="card bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
                    <div class="card-body">
                        <h3 class="font-medium text-info-900 dark:text-info-100 mb-2">Dépôt via Intervat</h3>
                        <p class="text-sm text-info-700 dark:text-info-300 mb-3">
                            Déposez votre déclaration sur le portail Intervat du SPF Finances.
                        </p>
                        <a
                            href="https://eservices.minfin.fgov.be/intervat"
                            target="_blank"
                            class="btn btn-info btn-sm w-full"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Accéder à Intervat
                        </a>
                    </div>
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
