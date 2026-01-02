@extends('layouts.app')

@section('title', 'Déclarations TVA')

@section('content')
<div x-data="vatDeclarations()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-secondary-900 dark:text-white">Déclarations TVA</h1>
            <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">
                Générez et gérez vos déclarations TVA automatiquement
            </p>
        </div>

        <div class="flex gap-3">
            <select x-model="selectedYear"
                    @change="loadDeclarations()"
                    class="input">
                @for($year = now()->year; $year >= now()->year - 5; $year--)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>

            <button @click="showGenerateModal = true"
                    class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Générer une déclaration
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total déclarations --}}
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-primary-100 dark:bg-primary-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Déclarations</p>
                    <p class="text-2xl font-semibold text-secondary-900 dark:text-white" x-text="stats.total_declarations || 0"></p>
                </div>
            </div>
        </div>

        {{-- TVA collectée --}}
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-success-100 dark:bg-success-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">TVA collectée</p>
                    <p class="text-2xl font-semibold text-secondary-900 dark:text-white" x-text="formatCurrency(stats.total_vat_collected || 0)"></p>
                </div>
            </div>
        </div>

        {{-- TVA déductible --}}
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-info-100 dark:bg-info-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">TVA déductible</p>
                    <p class="text-2xl font-semibold text-secondary-900 dark:text-white" x-text="formatCurrency(stats.total_vat_deductible || 0)"></p>
                </div>
            </div>
        </div>

        {{-- Solde --}}
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-warning-100 dark:bg-warning-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Solde TVA</p>
                    <p class="text-2xl font-semibold" :class="(stats.total_balance || 0) >= 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'" x-text="formatCurrency(stats.total_balance || 0)"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Declarations Table --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
            <h2 class="text-lg font-semibold text-secondary-900 dark:text-white">Déclarations {{ selectedYear }}</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-secondary-50 dark:bg-secondary-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Période</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">TVA collectée</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">TVA déductible</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Solde</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-secondary-500 dark:text-secondary-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-secondary-900 divide-y divide-secondary-200 dark:divide-secondary-700">
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex items-center justify-center">
                                    <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="!loading && declarations.length === 0">
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-secondary-500 dark:text-secondary-400">
                                <svg class="mx-auto h-12 w-12 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2">Aucune déclaration pour cette année</p>
                                <button @click="showGenerateModal = true" class="mt-4 btn btn-primary btn-sm">
                                    Générer une déclaration
                                </button>
                            </td>
                        </tr>
                    </template>

                    <template x-for="declaration in declarations" :key="declaration.id">
                        <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-secondary-900 dark:text-white" x-text="declaration.period"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-secondary-600 dark:text-secondary-400" x-text="declaration.period_type === 'monthly' ? 'Mensuelle' : 'Trimestrielle'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-600 dark:text-secondary-400">
                                <span x-text="formatDate(declaration.start_date)"></span> - <span x-text="formatDate(declaration.end_date)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-secondary-900 dark:text-white">
                                <span x-text="formatCurrency(declaration.total_vat_collected)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-secondary-900 dark:text-white">
                                <span x-text="formatCurrency(declaration.total_vat_deductible)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <span :class="declaration.grid_71 >= 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400'" x-text="formatCurrency(declaration.grid_71)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'badge badge-secondary': declaration.status === 'draft',
                                    'badge badge-info': declaration.status === 'validated',
                                    'badge badge-success': declaration.status === 'submitted' || declaration.status === 'accepted',
                                    'badge badge-danger': declaration.status === 'rejected'
                                }" x-text="getStatusLabel(declaration.status)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="'/vat/declarations/' + declaration.id" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                        Voir
                                    </a>
                                    <template x-if="declaration.xml_content">
                                        <a :href="'/vat/declarations/' + declaration.id + '/download-xml'" class="text-secondary-600 hover:text-secondary-900 dark:text-secondary-400 dark:hover:text-secondary-300">
                                            XML
                                        </a>
                                    </template>
                                    <template x-if="declaration.status === 'draft'">
                                        <button @click="deleteDeclaration(declaration.id)" class="text-danger-600 hover:text-danger-900 dark:text-danger-400 dark:hover:text-danger-300">
                                            Supprimer
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Generate Modal --}}
    <div x-show="showGenerateModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showGenerateModal = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-secondary-500 bg-opacity-75" @click="showGenerateModal = false"></div>

            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-secondary-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="generateDeclaration()">
                    <div class="px-6 py-4 bg-white dark:bg-secondary-800">
                        <h3 class="text-lg font-medium text-secondary-900 dark:text-white">Générer une déclaration TVA</h3>
                        <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">Sélectionnez la période pour laquelle générer la déclaration</p>

                        <div class="mt-6 space-y-4">
                            <div>
                                <label class="label">Année</label>
                                <select x-model="generateForm.year" class="input" required>
                                    @for($year = now()->year; $year >= now()->year - 2; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div>
                                <label class="label">Type de période</label>
                                <select x-model="generateForm.periodType" @change="generateForm.periodNumber = 1" class="input" required>
                                    <option value="quarterly">Trimestrielle</option>
                                    <option value="monthly">Mensuelle</option>
                                </select>
                            </div>

                            <div>
                                <label class="label" x-text="generateForm.periodType === 'monthly' ? 'Mois' : 'Trimestre'"></label>
                                <select x-model="generateForm.periodNumber" class="input" required>
                                    <template x-if="generateForm.periodType === 'quarterly'">
                                        <template x-for="q in [1,2,3,4]" :key="q">
                                            <option :value="q" x-text="'Q' + q"></option>
                                        </template>
                                    </template>
                                    <template x-if="generateForm.periodType === 'monthly'">
                                        <template x-for="m in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="m">
                                            <option :value="m" x-text="getMonthName(m)"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-700 flex justify-end gap-3">
                        <button type="button" @click="showGenerateModal = false" class="btn btn-secondary">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="generating">
                            <span x-show="!generating">Générer</span>
                            <span x-show="generating" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Génération...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function vatDeclarations() {
    return {
        selectedYear: {{ $currentYear ?? now()->year }},
        declarations: @json($declarations ?? []),
        stats: @json($stats ?? []),
        loading: false,
        showGenerateModal: false,
        generating: false,
        generateForm: {
            year: {{ now()->year }},
            periodType: 'quarterly',
            periodNumber: {{ now()->quarter }}
        },

        async loadDeclarations() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/vat/declarations', {
                    params: { year: this.selectedYear }
                });
                this.declarations = response.data.data;
            } catch (error) {
                console.error('Error loading declarations:', error);
                window.showToast?.('Erreur lors du chargement des déclarations', 'error');
            } finally {
                this.loading = false;
            }
        },

        async generateDeclaration() {
            this.generating = true;

            const period = this.generateForm.periodType === 'quarterly'
                ? `${this.generateForm.year}-Q${this.generateForm.periodNumber}`
                : `${this.generateForm.year}-${String(this.generateForm.periodNumber).padStart(2, '0')}`;

            try {
                const response = await axios.post('/vat/declarations/generate', { period });

                if (response.data.success || response.data.declaration) {
                    window.showToast?.('Déclaration générée avec succès', 'success');
                    this.showGenerateModal = false;

                    const declarationId = response.data.declaration?.id || response.data.data?.id;
                    if (declarationId) {
                        window.location.href = `/vat/declarations/${declarationId}`;
                    } else {
                        await this.loadDeclarations();
                    }
                }
            } catch (error) {
                console.error('Error generating declaration:', error);
                const message = error.response?.data?.message || 'Erreur lors de la génération';
                window.showToast?.(message, 'error');
            } finally {
                this.generating = false;
            }
        },

        async deleteDeclaration(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette déclaration ?')) {
                return;
            }

            try {
                await axios.delete(`/vat/declarations/${id}`);
                window.showToast?.('Déclaration supprimée', 'success');
                await this.loadDeclarations();
            } catch (error) {
                console.error('Error deleting declaration:', error);
                window.showToast?.('Erreur lors de la suppression', 'error');
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-BE', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount || 0);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('fr-BE');
        },

        getStatusLabel(status) {
            const labels = {
                'draft': 'Brouillon',
                'validated': 'Validée',
                'submitted': 'Soumise',
                'rejected': 'Rejetée',
                'accepted': 'Acceptée'
            };
            return labels[status] || status;
        },

        getMonthName(month) {
            const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
            return months[month - 1];
        }
    };
}
</script>
@endpush
@endsection
