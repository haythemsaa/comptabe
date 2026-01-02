<x-app-layout>
    <x-slot name="title">Nouvelle déclaration TVA</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('vat.index') }}" class="text-secondary-500 hover:text-secondary-700">TVA</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle déclaration</span>
    @endsection

    <form method="POST" action="{{ route('vat.store') }}" class="space-y-6" x-data="vatDeclaration(@js($vatData['grids']))">
        @csrf
        <input type="hidden" name="period_start" value="{{ $periodStart->format('Y-m-d') }}">
        <input type="hidden" name="period_end" value="{{ $periodEnd->format('Y-m-d') }}">
        <input type="hidden" name="period_type" value="{{ $periodType }}">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Déclaration TVA</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Période : {{ $periodStart->translatedFormat('F Y') }}
                    ({{ $periodStart->format('d/m') }} - {{ $periodEnd->format('d/m/Y') }})
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('vat.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl text-danger-700 dark:text-danger-300">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Section I: Opérations à la sortie -->
                <div class="card">
                    <div class="card-header bg-primary-50 dark:bg-primary-900/20">
                        <h2 class="font-semibold text-primary-900 dark:text-primary-100">
                            I. Opérations à la sortie
                        </h2>
                    </div>
                    <div class="card-body space-y-6">
                        <!-- Opérations soumises à la TVA -->
                        <div>
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">A. Opérations soumises à la TVA belge</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-primary text-xs mr-2">01</span>
                                        Opérations au taux de 6%
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[01]"
                                            x-model.number="grids['01']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-primary text-xs mr-2">02</span>
                                        Opérations au taux de 12%
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[02]"
                                            x-model.number="grids['02']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-primary text-xs mr-2">03</span>
                                        Opérations au taux de 21%
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[03]"
                                            x-model.number="grids['03']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opérations intracommunautaires -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">B. Opérations intracommunautaires exemptées</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-success text-xs mr-2">44</span>
                                        Services intracommunautaires
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[44]"
                                            x-model.number="grids['44']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-success text-xs mr-2">46</span>
                                        Livraisons intracommunautaires
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[46]"
                                            x-model.number="grids['46']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Autres opérations -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">C. Autres opérations</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-secondary text-xs mr-2">47</span>
                                        Autres opérations exemptées
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[47]"
                                            x-model.number="grids['47']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-secondary text-xs mr-2">00</span>
                                        Régime particulier
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[00]"
                                            x-model.number="grids['00']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes de crédit -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">D. Notes de crédit</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-danger text-xs mr-2">48</span>
                                        Notes de crédit émises
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[48]"
                                            x-model.number="grids['48']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-danger text-xs mr-2">49</span>
                                        Notes de crédit reçues
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[49]"
                                            x-model.number="grids['49']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section II: Opérations à l'entrée -->
                <div class="card">
                    <div class="card-header bg-success-50 dark:bg-success-900/20">
                        <h2 class="font-semibold text-success-900 dark:text-success-100">
                            II. Opérations à l'entrée
                        </h2>
                    </div>
                    <div class="card-body space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">
                                    <span class="badge badge-success text-xs mr-2">81</span>
                                    Marchandises et matières premières
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="grid_values[81]"
                                        x-model.number="grids['81']"
                                        step="0.01"
                                        class="form-input text-right pr-8"
                                    >
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span class="badge badge-success text-xs mr-2">82</span>
                                    Services et biens divers
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="grid_values[82]"
                                        x-model.number="grids['82']"
                                        step="0.01"
                                        class="form-input text-right pr-8"
                                    >
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">
                                    <span class="badge badge-success text-xs mr-2">83</span>
                                    Biens d'investissement
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        name="grid_values[83]"
                                        x-model.number="grids['83']"
                                        step="0.01"
                                        class="form-input text-right pr-8"
                                    >
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-info text-xs mr-2">86</span>
                                        Acquisitions intracom. (services)
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[86]"
                                            x-model.number="grids['86']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-info text-xs mr-2">87</span>
                                        Autres opérations à l'entrée
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[87]"
                                            x-model.number="grids['87']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-info text-xs mr-2">88</span>
                                        Opérations cocontractant
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[88]"
                                            x-model.number="grids['88']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section III: TVA -->
                <div class="card">
                    <div class="card-header bg-warning-50 dark:bg-warning-900/20">
                        <h2 class="font-semibold text-warning-900 dark:text-warning-100">
                            III. Calcul de la TVA
                        </h2>
                    </div>
                    <div class="card-body space-y-6">
                        <!-- TVA due -->
                        <div>
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">A. TVA due</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-warning text-xs mr-2">54</span>
                                        TVA sur opérations (01+02+03)
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[54]"
                                            x-model.number="grids['54']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-warning text-xs mr-2">55</span>
                                        TVA sur grille 86
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[55]"
                                            x-model.number="grids['55']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-warning text-xs mr-2">56</span>
                                        TVA sur grille 87
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[56]"
                                            x-model.number="grids['56']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-warning text-xs mr-2">57</span>
                                        TVA sur grille 88
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[57]"
                                            x-model.number="grids['57']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TVA déductible -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">B. TVA déductible</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-success text-xs mr-2">59</span>
                                        TVA déductible
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[59]"
                                            x-model.number="grids['59']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Régularisations -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <h3 class="font-medium text-secondary-900 dark:text-white mb-4">C. Régularisations</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-danger text-xs mr-2">61</span>
                                        En faveur de l'État
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[61]"
                                            x-model.number="grids['61']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">
                                        <span class="badge badge-success text-xs mr-2">62</span>
                                        En faveur du déclarant
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            name="grid_values[62]"
                                            x-model.number="grids['62']"
                                            step="0.01"
                                            class="form-input text-right pr-8"
                                        >
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Summary -->
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Résumé</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-secondary-600">TVA due</span>
                            <span class="font-mono font-bold text-danger-600" x-text="formatCurrency(totalOutputVat)"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-secondary-600">TVA déductible</span>
                            <span class="font-mono font-bold text-success-600" x-text="formatCurrency(totalInputVat)"></span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-secondary-900 dark:text-white">Solde</span>
                                <span
                                    class="font-mono text-xl font-bold"
                                    :class="balance >= 0 ? 'text-danger-600' : 'text-success-600'"
                                    x-text="formatCurrency(Math.abs(balance))"
                                ></span>
                            </div>
                            <p
                                class="text-sm mt-1"
                                :class="balance >= 0 ? 'text-danger-600' : 'text-success-600'"
                                x-text="balance >= 0 ? 'À payer à l\\'État' : 'Crédit TVA'"
                            ></p>
                        </div>

                        <!-- Hidden inputs for calculated grids -->
                        <input type="hidden" name="grid_values[71]" :value="balance >= 0 ? balance : 0">
                        <input type="hidden" name="grid_values[72]" :value="balance < 0 ? Math.abs(balance) : 0">
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer la déclaration
                        </button>
                    </div>
                </div>

                <!-- Data Source -->
                <div class="card bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
                    <div class="card-body">
                        <h3 class="font-medium text-info-900 dark:text-info-100 mb-2">Données sources</h3>
                        <p class="text-sm text-info-700 dark:text-info-300 mb-3">
                            Les valeurs ont été pré-remplies automatiquement à partir de :
                        </p>
                        <ul class="text-sm text-info-700 dark:text-info-300 space-y-1">
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $vatData['invoices']['sales']->count() }} factures de vente
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $vatData['invoices']['purchases']->count() }} factures d'achat
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function vatDeclaration(initialGrids = {}) {
            return {
                grids: initialGrids,

                get totalOutputVat() {
                    return (parseFloat(this.grids['54']) || 0) +
                           (parseFloat(this.grids['55']) || 0) +
                           (parseFloat(this.grids['56']) || 0) +
                           (parseFloat(this.grids['57']) || 0) +
                           Math.max(0, (parseFloat(this.grids['61']) || 0) - (parseFloat(this.grids['62']) || 0));
                },

                get totalInputVat() {
                    return (parseFloat(this.grids['59']) || 0) +
                           Math.max(0, (parseFloat(this.grids['62']) || 0) - (parseFloat(this.grids['61']) || 0));
                },

                get balance() {
                    return this.totalOutputVat - this.totalInputVat;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-BE', {
                        style: 'currency',
                        currency: 'EUR'
                    }).format(amount);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
