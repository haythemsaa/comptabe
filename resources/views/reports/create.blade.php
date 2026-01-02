@extends('layouts.app')

@section('title', 'Créer un rapport - ' . $reportInfo['name'])

@section('content')
<div class="py-6" x-data="reportBuilder()">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('reports.index') }}" class="hover:text-gray-700">Rapports</a>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-900">{{ $reportInfo['name'] }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900">{{ $reportInfo['name'] }}</h1>
            <p class="text-gray-600">{{ $reportInfo['description'] }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Configuration -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Période -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Période</h2>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" @click="setPeriod('month')"
                                :class="activePeriod === 'month' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Ce mois
                        </button>
                        <button type="button" @click="setPeriod('lastMonth')"
                                :class="activePeriod === 'lastMonth' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Mois dernier
                        </button>
                        <button type="button" @click="setPeriod('quarter')"
                                :class="activePeriod === 'quarter' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Ce trimestre
                        </button>
                        <button type="button" @click="setPeriod('lastQuarter')"
                                :class="activePeriod === 'lastQuarter' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Trimestre dernier
                        </button>
                        <button type="button" @click="setPeriod('year')"
                                :class="activePeriod === 'year' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Cette année
                        </button>
                        <button type="button" @click="setPeriod('lastYear')"
                                :class="activePeriod === 'lastYear' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Année dernière
                        </button>
                        <button type="button" @click="setPeriod('custom')"
                                :class="activePeriod === 'custom' ? 'bg-blue-100 text-blue-700 border-blue-300' : 'bg-gray-50 text-gray-700 border-gray-200'"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border">
                            Personnalisé
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                            <input type="date" x-model="form.date_from"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                            <input type="date" x-model="form.date_to"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Options spécifiques au type -->
                @if($type === 'general_ledger')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Options</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Compte (optionnel)</label>
                        <input type="text" x-model="form.options.account" placeholder="Ex: 40 pour les créances"
                               class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Laissez vide pour tous les comptes</p>
                    </div>
                </div>
                @endif

                @if($type === 'partner_statement')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Options</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Partenaire</label>
                        <select x-model="form.options.partner_id"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner un partenaire</option>
                            @foreach(\App\Models\Partner::where('company_id', auth()->user()->current_company_id)->get() as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if($type === 'vat_listing')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Options</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                        <select x-model="form.options.year"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                @endif

                @if($type === 'journal')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Options</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de journal</label>
                        <select x-model="form.options.journal_type"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Tous les journaux</option>
                            <option value="sales">Ventes</option>
                            <option value="purchases">Achats</option>
                            <option value="bank">Banque</option>
                            <option value="cash">Caisse</option>
                            <option value="general">Opérations diverses</option>
                        </select>
                    </div>
                </div>
                @endif

                <!-- Format d'export -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Format d'export</h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label :class="form.format === 'pdf' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                               class="relative flex flex-col items-center p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" x-model="form.format" value="pdf" class="sr-only">
                            <svg class="w-10 h-10 text-red-500 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zm-3 8v4h1v-2h1a1 1 0 001-1v-1a1 1 0 00-1-1h-2zm1 1h1v1h-1v-1zm-2 3H7v-4h1a2 2 0 012 2 2 2 0 01-2 2zm0-1a1 1 0 001-1 1 1 0 00-1-1v2zm6-3h-1v4h1v-1.5h1.5V13H16v-.5h1.5V12H16z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">PDF</span>
                            <span class="text-xs text-gray-500">Document</span>
                        </label>

                        <label :class="form.format === 'xlsx' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                               class="relative flex flex-col items-center p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" x-model="form.format" value="xlsx" class="sr-only">
                            <svg class="w-10 h-10 text-green-600 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 13h3v1h-1v3H9v-3H8v-1zm5 4h-1v-1h1v1zm0-2h-1v-1h1v1zm0-2h-1v-1h1v1zm2 4h-1v-1h1v1zm0-2h-1v-1h1v1zm0-2h-1v-1h1v1z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">Excel</span>
                            <span class="text-xs text-gray-500">Tableur</span>
                        </label>

                        <label :class="form.format === 'csv' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                               class="relative flex flex-col items-center p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" x-model="form.format" value="csv" class="sr-only">
                            <svg class="w-10 h-10 text-blue-500 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 17v-1h1v1H8zm3 0v-1h1v1h-1zm3 0v-1h1v1h-1zm-6-3v-1h1v1H8zm3 0v-1h1v1h-1zm3 0v-1h1v1h-1z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">CSV</span>
                            <span class="text-xs text-gray-500">Données</span>
                        </label>

                        <label :class="form.format === 'json' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                               class="relative flex flex-col items-center p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" x-model="form.format" value="json" class="sr-only">
                            <svg class="w-10 h-10 text-yellow-500 mb-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 3h2v2H5v5a2 2 0 01-2 2 2 2 0 012 2v5h2v2H5c-1.07-.27-2-.9-2-2v-4a2 2 0 00-2-2H0v-2h1a2 2 0 002-2V5a2 2 0 012-2zm14 0a2 2 0 012 2v4a2 2 0 002 2h1v2h-1a2 2 0 00-2 2v4a2 2 0 01-2 2h-2v-2h2v-5a2 2 0 012-2 2 2 0 01-2-2V5h-2V3h2z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">JSON</span>
                            <span class="text-xs text-gray-500">API</span>
                        </label>
                    </div>
                </div>

                <!-- Planification -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Planification</h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="form.scheduled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div x-show="form.scheduled" x-collapse>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fréquence</label>
                                <select x-model="form.schedule.frequency"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="daily">Quotidien</option>
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="monthly">Mensuel</option>
                                    <option value="quarterly">Trimestriel</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Envoyer par email</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" x-model="form.schedule.send_email" class="rounded text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Oui</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="form.schedule.send_email">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email(s) destinataire(s)</label>
                                <input type="text" x-model="form.schedule.emails" placeholder="email1@example.com, email2@example.com"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sauvegarder comme modèle -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Sauvegarder comme modèle</h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="form.save_template" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div x-show="form.save_template" x-collapse>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du rapport</label>
                                <input type="text" x-model="form.name" placeholder="Ex: Compte de résultat mensuel"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea x-model="form.description" rows="2" placeholder="Description optionnelle..."
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="form.is_public" class="rounded text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Partager avec l'équipe</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                    <div class="space-y-3">
                        <button @click="generate()"
                                :disabled="generating"
                                class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!generating">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Générer le rapport
                                </span>
                            </template>
                            <template x-if="generating">
                                <span class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Génération...
                                </span>
                            </template>
                        </button>

                        <button @click="preview()"
                                :disabled="previewing"
                                class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Aperçu
                        </button>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">Récapitulatif</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Type</dt>
                                <dd class="font-medium text-gray-900">{{ $reportInfo['name'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Période</dt>
                                <dd class="font-medium text-gray-900" x-text="form.date_from + ' - ' + form.date_to"></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Format</dt>
                                <dd class="font-medium text-gray-900" x-text="form.format.toUpperCase()"></dd>
                            </div>
                            <div class="flex justify-between" x-show="form.scheduled">
                                <dt class="text-gray-500">Planifié</dt>
                                <dd class="font-medium text-green-600">Oui</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Aide -->
                <div class="bg-blue-50 rounded-xl p-5">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Aide</h3>
                    <p class="text-sm text-blue-700">
                        {{ $reportInfo['description'] }}
                    </p>
                    @if($type === 'vat_summary')
                        <ul class="mt-3 text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li>Grilles 00 à 72 selon format belge</li>
                            <li>TVA due vs TVA déductible</li>
                            <li>Détail par taux</li>
                        </ul>
                    @elseif($type === 'profit_loss')
                        <ul class="mt-3 text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li>Revenus classes 70-76 (PCMN)</li>
                            <li>Charges classes 60-67 (PCMN)</li>
                            <li>Résultat d'exploitation et financier</li>
                        </ul>
                    @elseif($type === 'balance_sheet')
                        <ul class="mt-3 text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li>Actifs immobilisés et circulants</li>
                            <li>Capitaux propres</li>
                            <li>Dettes et provisions</li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal aperçu -->
    <div x-show="showPreview" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="showPreview = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Aperçu du rapport</h3>
                    <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto max-h-[60vh]">
                    <pre class="text-xs bg-gray-50 p-4 rounded-lg overflow-x-auto" x-text="JSON.stringify(previewData, null, 2)"></pre>
                </div>
                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button @click="showPreview = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                        Fermer
                    </button>
                    <button @click="showPreview = false; generate()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Générer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reportBuilder() {
    const now = new Date();
    const startOfYear = new Date(now.getFullYear(), 0, 1);

    return {
        type: '{{ $type }}',
        generating: false,
        previewing: false,
        showPreview: false,
        previewData: null,
        activePeriod: 'year',

        form: {
            type: '{{ $type }}',
            date_from: startOfYear.toISOString().split('T')[0],
            date_to: now.toISOString().split('T')[0],
            format: 'pdf',
            options: @json($defaultConfig['options'] ?? []),
            scheduled: false,
            schedule: {
                frequency: 'monthly',
                send_email: false,
                emails: ''
            },
            save_template: false,
            name: '',
            description: '',
            is_public: false
        },

        setPeriod(period) {
            this.activePeriod = period;
            const now = new Date();
            let from, to;

            switch(period) {
                case 'month':
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                    to = now;
                    break;
                case 'lastMonth':
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    to = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'quarter':
                    const q = Math.floor(now.getMonth() / 3);
                    from = new Date(now.getFullYear(), q * 3, 1);
                    to = now;
                    break;
                case 'lastQuarter':
                    const lq = Math.floor(now.getMonth() / 3) - 1;
                    from = new Date(now.getFullYear(), lq * 3, 1);
                    to = new Date(now.getFullYear(), (lq + 1) * 3, 0);
                    break;
                case 'year':
                    from = new Date(now.getFullYear(), 0, 1);
                    to = now;
                    break;
                case 'lastYear':
                    from = new Date(now.getFullYear() - 1, 0, 1);
                    to = new Date(now.getFullYear() - 1, 11, 31);
                    break;
                case 'custom':
                    return;
            }

            this.form.date_from = from.toISOString().split('T')[0];
            this.form.date_to = to.toISOString().split('T')[0];
        },

        async generate() {
            this.generating = true;

            try {
                const response = await fetch('{{ route("reports.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success && data.download_url) {
                    // Sauvegarder le template si demandé
                    if (this.form.save_template && this.form.name) {
                        await this.saveTemplate();
                    }
                    window.location.href = data.download_url;
                } else {
                    alert(data.error || 'Erreur lors de la génération');
                }
            } catch (error) {
                console.error(error);
                alert('Erreur lors de la génération du rapport');
            } finally {
                this.generating = false;
            }
        },

        async preview() {
            this.previewing = true;

            try {
                const response = await fetch('{{ route("reports.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.form.type,
                        date_from: this.form.date_from,
                        date_to: this.form.date_to,
                        options: this.form.options
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.previewData = data.data;
                    this.showPreview = true;
                } else {
                    alert(data.error || 'Erreur lors de l\'aperçu');
                }
            } catch (error) {
                console.error(error);
                alert('Erreur lors de l\'aperçu');
            } finally {
                this.previewing = false;
            }
        },

        async saveTemplate() {
            try {
                await fetch('{{ route("reports.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.form.name,
                        type: this.form.type,
                        description: this.form.description,
                        config: {
                            date_from: this.activePeriod,
                            date_to: 'today',
                            options: this.form.options
                        },
                        schedule: this.form.scheduled ? this.form.schedule : null,
                        is_public: this.form.is_public
                    })
                });
            } catch (error) {
                console.error('Erreur lors de la sauvegarde du template', error);
            }
        }
    }
}
</script>
@endpush
@endsection
