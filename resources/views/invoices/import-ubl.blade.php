<x-app-layout>
    <x-slot name="title">Importer une facture UBL</x-slot>

    <div class="max-w-4xl mx-auto" x-data="ublImport()">
        <!-- Step Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors"
                            :class="step >= 1 ? 'bg-primary-500 text-white' : 'bg-secondary-700 text-secondary-400'">
                            1
                        </div>
                        <span class="ml-3 text-sm font-medium" :class="step >= 1 ? 'text-white' : 'text-secondary-400'">Upload</span>
                    </div>

                    <!-- Divider -->
                    <div class="w-16 h-0.5 mx-4" :class="step >= 2 ? 'bg-primary-500' : 'bg-secondary-700'"></div>

                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors"
                            :class="step >= 2 ? 'bg-primary-500 text-white' : 'bg-secondary-700 text-secondary-400'">
                            2
                        </div>
                        <span class="ml-3 text-sm font-medium" :class="step >= 2 ? 'text-white' : 'text-secondary-400'">Vérification</span>
                    </div>

                    <!-- Divider -->
                    <div class="w-16 h-0.5 mx-4" :class="step >= 3 ? 'bg-primary-500' : 'bg-secondary-700'"></div>

                    <!-- Step 3 -->
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors"
                            :class="step >= 3 ? 'bg-primary-500 text-white' : 'bg-secondary-700 text-secondary-400'">
                            3
                        </div>
                        <span class="ml-3 text-sm font-medium" :class="step >= 3 ? 'text-white' : 'text-secondary-400'">Confirmation</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: File Upload -->
        <div x-show="step === 1" class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700">
                <h2 class="text-lg font-semibold">Importer une facture Peppol (UBL)</h2>
                <p class="mt-1 text-sm text-secondary-400">
                    Importez un fichier XML au format UBL 2.1 pour créer automatiquement une facture d'achat.
                </p>
            </div>

            <div class="p-6 space-y-6">
                @if(session('error'))
                    <div class="bg-danger-500/10 border border-danger-500/30 text-danger-400 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- File Upload Zone -->
                <div>
                    <label class="block text-sm font-medium mb-2">Fichier UBL (XML)</label>
                    <div
                        @drop.prevent="handleFileDrop($event)"
                        @dragover.prevent="dragover = true"
                        @dragleave.prevent="dragover = false"
                        :class="{'border-primary-500 bg-primary-500/10': dragover}"
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-600 border-dashed rounded-lg hover:border-primary-500 transition-colors cursor-pointer">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-secondary-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-secondary-400 justify-center">
                                <label for="ubl_file" class="relative cursor-pointer rounded-md font-medium text-primary-400 hover:text-primary-300">
                                    <span>Choisir un fichier</span>
                                    <input
                                        id="ubl_file"
                                        type="file"
                                        class="sr-only"
                                        accept=".xml"
                                        @change="handleFileSelect($event)">
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs text-secondary-500">Fichier XML jusqu'à 5 MB</p>
                        </div>
                    </div>
                    <p x-show="fileName" class="mt-2 text-sm text-secondary-400">
                        Fichier sélectionné: <span class="text-white font-medium" x-text="fileName"></span>
                    </p>
                </div>

                <!-- Format Info -->
                <div class="bg-primary-500/10 border border-primary-500/30 rounded-lg p-4">
                    <h4 class="font-medium text-primary-400 mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Formats supportés
                    </h4>
                    <ul class="text-sm text-primary-300 space-y-1 ml-7">
                        <li>• Peppol BIS Billing 3.0 (UBL 2.1)</li>
                        <li>• EN 16931 - Norme européenne de facturation</li>
                        <li>• Factur-X / ZUGFeRD (partie XML)</li>
                    </ul>
                </div>

                <!-- What will be imported -->
                <div class="bg-secondary-700/50 border border-secondary-600 rounded-lg p-4">
                    <h4 class="font-medium mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Ce qui sera importé
                    </h4>
                    <ul class="text-sm text-secondary-400 space-y-1 ml-7">
                        <li>• Informations du fournisseur (création automatique si nouveau)</li>
                        <li>• Numéro et date de facture</li>
                        <li>• Lignes de facture avec description, quantité et prix</li>
                        <li>• Taux de TVA et montants</li>
                        <li>• Référence de paiement (communication structurée)</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-secondary-700">
                    <a href="{{ route('purchases.index') }}" class="text-secondary-400 hover:text-white">
                        Annuler
                    </a>
                    <button
                        @click="parseUblFile()"
                        :disabled="!fileName || loading"
                        class="px-6 py-3 bg-primary-500 hover:bg-primary-600 disabled:bg-secondary-700 disabled:text-secondary-500 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Analyse...' : 'Continuer'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Preview & Validation -->
        <div x-show="step === 2" x-cloak class="bg-secondary-800 rounded-xl border border-secondary-700">
            <div class="px-6 py-4 border-b border-secondary-700">
                <h2 class="text-lg font-semibold">Vérification des données</h2>
                <p class="mt-1 text-sm text-secondary-400">
                    Vérifiez les données extraites avant l'import final. Vous pouvez les modifier si nécessaire.
                </p>
            </div>

            <div class="p-6 space-y-6">
                <!-- Validation Warnings -->
                <div x-show="validation.warnings.length > 0" class="bg-warning-500/10 border border-warning-500/30 rounded-lg p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-warning-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-medium text-warning-400 mb-2">Attention - Vérification requise</p>
                            <ul class="text-sm text-warning-300 space-y-1">
                                <template x-for="warning in validation.warnings" :key="warning">
                                    <li x-text="'• ' + warning"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Validation Errors -->
                <div x-show="validation.errors.length > 0" class="bg-danger-500/10 border border-danger-500/30 rounded-lg p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-danger-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-medium text-danger-400 mb-2">Erreurs - Correction requise</p>
                            <ul class="text-sm text-danger-300 space-y-1">
                                <template x-for="error in validation.errors" :key="error">
                                    <li x-text="'• ' + error"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Invoice Preview -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Supplier Information -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-lg">Fournisseur</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Nom</label>
                                <input
                                    type="text"
                                    x-model="preview.supplier.name"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Numéro TVA</label>
                                <input
                                    type="text"
                                    x-model="preview.supplier.vat"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Email</label>
                                <input
                                    type="email"
                                    x-model="preview.supplier.email"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div x-show="preview.supplier.isNew" class="bg-success-500/10 border border-success-500/30 rounded px-3 py-2">
                                <p class="text-sm text-success-400">✓ Nouveau fournisseur - Sera créé automatiquement</p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Information -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-lg">Facture</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Numéro</label>
                                <input
                                    type="text"
                                    x-model="preview.invoice.number"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Date</label>
                                <input
                                    type="date"
                                    x-model="preview.invoice.date"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Date d'échéance</label>
                                <input
                                    type="date"
                                    x-model="preview.invoice.dueDate"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-secondary-400 mb-1">Référence</label>
                                <input
                                    type="text"
                                    x-model="preview.invoice.reference"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Lines -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-lg">Lignes de facture</h3>
                    <div class="bg-secondary-700/50 rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-secondary-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-secondary-400 uppercase">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Qté</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Prix unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-400 uppercase">TVA %</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-secondary-400 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-secondary-700">
                                <template x-for="(line, index) in preview.lines" :key="index">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input
                                                type="text"
                                                x-model="line.description"
                                                class="w-full bg-secondary-600 border-secondary-500 rounded text-white text-sm">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                x-model.number="line.quantity"
                                                step="0.01"
                                                class="w-20 bg-secondary-600 border-secondary-500 rounded text-white text-sm text-right">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                x-model.number="line.unitPrice"
                                                step="0.01"
                                                class="w-24 bg-secondary-600 border-secondary-500 rounded text-white text-sm text-right">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                x-model.number="line.vatRate"
                                                step="0.01"
                                                class="w-16 bg-secondary-600 border-secondary-500 rounded text-white text-sm text-right">
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium" x-text="'€ ' + (line.quantity * line.unitPrice).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals Summary -->
                <div class="bg-secondary-700/50 rounded-lg p-4">
                    <div class="space-y-2 max-w-sm ml-auto">
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-400">Total HT:</span>
                            <span class="font-medium" x-text="'€ ' + preview.totals.exclVat.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-secondary-400">TVA:</span>
                            <span class="font-medium" x-text="'€ ' + preview.totals.vat.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-secondary-600">
                            <span>Total TTC:</span>
                            <span x-text="'€ ' + preview.totals.inclVat.toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-secondary-700">
                    <button
                        @click="step = 1"
                        class="text-secondary-400 hover:text-white inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Retour
                    </button>
                    <button
                        @click="importInvoice()"
                        :disabled="validation.errors.length > 0 || loading"
                        class="px-6 py-3 bg-primary-500 hover:bg-primary-600 disabled:bg-secondary-700 disabled:text-secondary-500 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Import...' : 'Importer la facture'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function ublImport() {
            return {
                step: 1,
                fileName: '',
                fileContent: null,
                dragover: false,
                loading: false,
                preview: {
                    supplier: {
                        name: '',
                        vat: '',
                        email: '',
                        isNew: false
                    },
                    invoice: {
                        number: '',
                        date: '',
                        dueDate: '',
                        reference: ''
                    },
                    lines: [],
                    totals: {
                        exclVat: 0,
                        vat: 0,
                        inclVat: 0
                    }
                },
                validation: {
                    warnings: [],
                    errors: []
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.readFile(file);
                    }
                },

                handleFileDrop(event) {
                    this.dragover = false;
                    const file = event.dataTransfer.files[0];
                    if (file && file.name.endsWith('.xml')) {
                        this.fileName = file.name;
                        this.readFile(file);
                    }
                },

                readFile(file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.fileContent = e.target.result;
                    };
                    reader.readAsText(file);
                },

                async parseUblFile() {
                    if (!this.fileContent) return;

                    this.loading = true;
                    this.validation.warnings = [];
                    this.validation.errors = [];

                    try {
                        // Send to backend for parsing
                        const response = await fetch('{{ route("purchases.import-ubl.parse") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ xml: this.fileContent })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.preview = data.data;
                            this.validation = data.validation || { warnings: [], errors: [] };
                            this.calculateTotals();
                            this.step = 2;
                        } else {
                            this.validation.errors.push(data.error || 'Erreur lors du parsing du fichier UBL');
                        }
                    } catch (error) {
                        this.validation.errors.push('Erreur de connexion au serveur');
                    } finally {
                        this.loading = false;
                    }
                },

                calculateTotals() {
                    this.preview.totals.exclVat = this.preview.lines.reduce((sum, line) =>
                        sum + (line.quantity * line.unitPrice), 0);

                    this.preview.totals.vat = this.preview.lines.reduce((sum, line) =>
                        sum + (line.quantity * line.unitPrice * line.vatRate / 100), 0);

                    this.preview.totals.inclVat = this.preview.totals.exclVat + this.preview.totals.vat;
                },

                async importInvoice() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route("purchases.import-ubl.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                preview: this.preview,
                                xml_content: this.fileContent
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.error || 'Erreur lors de l\'import');
                        }
                    } catch (error) {
                        alert('Erreur de connexion au serveur');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
