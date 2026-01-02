<x-app-layout>
    <x-slot name="title">Modifier {{ $recurringInvoice->name }}</x-slot>

    <script>
        window.recurringForm = function() {
            return {
                partnerId: '{{ $recurringInvoice->partner_id }}',
                frequency: '{{ $recurringInvoice->frequency }}',
                lines: @json($recurringInvoice->lines->map(fn($l) => [
                    'id' => $l->id,
                    'description' => $l->description,
                    'quantity' => floatval($l->quantity),
                    'unitPrice' => floatval($l->unit_price),
                    'vatRate' => floatval($l->vat_rate),
                    'discount' => floatval($l->discount_percent),
                    'accountId' => $l->account_id ?? ''
                ])),
                lineIdCounter: {{ $recurringInvoice->lines->count() }},
                vatRates: @json($vatCodes->pluck('rate')->unique()->values()),
                defaultVatRate: 21,

                init() {
                    if (this.lines.length === 0) {
                        this.addLine();
                    }
                    this.$watch('lines', () => this.calculateTotals(), true);
                },

                addLine() {
                    this.lines.push({
                        id: ++this.lineIdCounter,
                        description: '',
                        quantity: 1,
                        unitPrice: 0,
                        vatRate: this.defaultVatRate,
                        discount: 0,
                        accountId: ''
                    });
                },

                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                },

                duplicateLine(index) {
                    const line = {...this.lines[index], id: ++this.lineIdCounter};
                    this.lines.splice(index + 1, 0, line);
                },

                calculateTotals() {},

                calculateLineTotal(line) {
                    const subtotal = (line.quantity || 0) * (line.unitPrice || 0);
                    const discount = subtotal * ((line.discount || 0) / 100);
                    return subtotal - discount;
                },

                calculateLineVat(line) {
                    return this.calculateLineTotal(line) * ((line.vatRate || 0) / 100);
                },

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + this.calculateLineTotal(line), 0);
                },

                get totalVat() {
                    return this.lines.reduce((sum, line) => sum + this.calculateLineVat(line), 0);
                },

                get total() {
                    return this.subtotal + this.totalVat;
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('{{ $companyCountryCode === "TN" ? "fr-TN" : "fr-BE" }}', {
                        style: 'currency',
                        currency: '{{ $companyCurrency }}',
                        minimumFractionDigits: {{ $companyDecimalPlaces }},
                        maximumFractionDigits: {{ $companyDecimalPlaces }}
                    }).format(value || 0);
                }
            };
        };
    </script>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('recurring-invoices.index') }}" class="text-secondary-500 hover:text-secondary-700">Factures recurrentes</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Modifier</span>
    @endsection

    <form
        method="POST"
        action="{{ route('recurring-invoices.update', $recurringInvoice) }}"
        x-data="window.recurringForm()"
        x-init="init()"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier {{ $recurringInvoice->name }}</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Modifiez les details de la recurrence</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('recurring-invoices.show', $recurringInvoice) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations generales</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Client -->
                            <div class="md:col-span-2">
                                <label class="form-label">Client *</label>
                                <select
                                    name="partner_id"
                                    x-model="partnerId"
                                    required
                                    class="form-select"
                                >
                                    <option value="">Selectionner un client...</option>
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->id }}">
                                            {{ $partner->name }} {{ $partner->vat_number ? '(' . $partner->vat_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label class="form-label">Nom de la recurrence *</label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $recurringInvoice->name) }}"
                                    required
                                    class="form-input"
                                >
                            </div>

                            <!-- Reference Prefix -->
                            <div>
                                <label class="form-label">Prefixe de reference</label>
                                <input
                                    type="text"
                                    name="reference_prefix"
                                    value="{{ old('reference_prefix', $recurringInvoice->reference_prefix) }}"
                                    class="form-input"
                                >
                            </div>

                            <!-- Payment Terms -->
                            <div>
                                <label class="form-label">Delai de paiement (jours)</label>
                                <input
                                    type="number"
                                    name="payment_terms_days"
                                    value="{{ old('payment_terms_days', $recurringInvoice->payment_terms_days) }}"
                                    min="0"
                                    class="form-input"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Planification</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Frequency -->
                            <div>
                                <label class="form-label">Frequence *</label>
                                <select
                                    name="frequency"
                                    x-model="frequency"
                                    required
                                    class="form-select"
                                >
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="monthly">Mensuelle</option>
                                    <option value="quarterly">Trimestrielle</option>
                                    <option value="yearly">Annuelle</option>
                                </select>
                            </div>

                            <!-- Interval -->
                            <div>
                                <label class="form-label">Intervalle</label>
                                <input
                                    type="number"
                                    name="frequency_interval"
                                    value="{{ old('frequency_interval', $recurringInvoice->frequency_interval) }}"
                                    min="1"
                                    class="form-input"
                                >
                            </div>

                            <!-- Day of Month -->
                            <div x-show="frequency !== 'weekly'">
                                <label class="form-label">Jour du mois</label>
                                <input
                                    type="number"
                                    name="day_of_month"
                                    value="{{ old('day_of_month', $recurringInvoice->day_of_month) }}"
                                    min="1"
                                    max="28"
                                    class="form-input"
                                >
                            </div>

                            <!-- Day of Week -->
                            <div x-show="frequency === 'weekly'">
                                <label class="form-label">Jour de la semaine</label>
                                <select name="day_of_week" class="form-select">
                                    <option value="1" {{ $recurringInvoice->day_of_week == 1 ? 'selected' : '' }}>Lundi</option>
                                    <option value="2" {{ $recurringInvoice->day_of_week == 2 ? 'selected' : '' }}>Mardi</option>
                                    <option value="3" {{ $recurringInvoice->day_of_week == 3 ? 'selected' : '' }}>Mercredi</option>
                                    <option value="4" {{ $recurringInvoice->day_of_week == 4 ? 'selected' : '' }}>Jeudi</option>
                                    <option value="5" {{ $recurringInvoice->day_of_week == 5 ? 'selected' : '' }}>Vendredi</option>
                                    <option value="6" {{ $recurringInvoice->day_of_week == 6 ? 'selected' : '' }}>Samedi</option>
                                    <option value="0" {{ $recurringInvoice->day_of_week == 0 ? 'selected' : '' }}>Dimanche</option>
                                </select>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="form-label">Date de fin (optionnel)</label>
                                <input
                                    type="date"
                                    name="end_date"
                                    value="{{ old('end_date', $recurringInvoice->end_date?->format('Y-m-d')) }}"
                                    class="form-input"
                                >
                            </div>

                            <!-- Max Invoices -->
                            <div>
                                <label class="form-label">Nombre max de factures</label>
                                <input
                                    type="number"
                                    name="max_invoices"
                                    value="{{ old('max_invoices', $recurringInvoice->max_invoices) }}"
                                    min="1"
                                    class="form-input"
                                    placeholder="Illimite"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lines -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes de facture</h2>
                        <button
                            type="button"
                            @click="addLine()"
                            class="btn btn-secondary btn-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <template x-for="(line, index) in lines" :key="line.id">
                                <div class="p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl space-y-4">
                                    <div class="flex items-start justify-between">
                                        <span class="text-sm font-medium text-secondary-500">Ligne <span x-text="index + 1"></span></span>
                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="duplicateLine(index)"
                                                class="text-secondary-400 hover:text-primary-600 transition-colors"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeLine(index)"
                                                x-show="lines.length > 1"
                                                class="text-danger-500 hover:text-danger-700 transition-colors"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">Description *</label>
                                        <input
                                            type="text"
                                            :name="`lines[${index}][description]`"
                                            x-model="line.description"
                                            required
                                            class="form-input"
                                        >
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                        <div>
                                            <label class="form-label">Quantite *</label>
                                            <input
                                                type="number"
                                                :name="`lines[${index}][quantity]`"
                                                x-model.number="line.quantity"
                                                required
                                                min="0.0001"
                                                step="0.0001"
                                                class="form-input"
                                            >
                                        </div>

                                        <div>
                                            <label class="form-label">Prix unit. *</label>
                                            <input
                                                type="number"
                                                :name="`lines[${index}][unit_price]`"
                                                x-model.number="line.unitPrice"
                                                required
                                                min="0"
                                                step="0.01"
                                                class="form-input"
                                            >
                                        </div>

                                        <div>
                                            <label class="form-label">TVA *</label>
                                            <select
                                                :name="`lines[${index}][vat_rate]`"
                                                x-model.number="line.vatRate"
                                                required
                                                class="form-select"
                                            >
                                                @foreach($vatCodes as $vat)
                                                    <option value="{{ $vat->rate }}">{{ $vat->rate }}%</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="form-label">Remise</label>
                                            <input
                                                type="number"
                                                :name="`lines[${index}][discount_percent]`"
                                                x-model.number="line.discount"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                class="form-input"
                                            >
                                        </div>

                                        <div>
                                            <label class="form-label">Total HT</label>
                                            <div class="form-input bg-secondary-100 dark:bg-secondary-700 font-medium" x-text="formatCurrency(calculateLineTotal(line))"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button
                            type="button"
                            @click="addLine()"
                            class="mt-4 w-full py-3 border-2 border-dashed border-secondary-300 dark:border-secondary-600 rounded-xl text-secondary-500 hover:text-primary-600 hover:border-primary-300 transition-colors flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une ligne
                        </button>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Notes</h2>
                    </div>
                    <div class="card-body">
                        <textarea
                            name="notes"
                            rows="3"
                            class="form-input"
                        >{{ old('notes', $recurringInvoice->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Totals Card -->
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Montant par facture</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-800 pt-2">
                            <span class="text-secondary-600 dark:text-secondary-400">Total TVA</span>
                            <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(totalVat)"></span>
                        </div>
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-primary-600" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>

                <!-- Auto Send Options -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Options d'envoi</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_send" value="1" {{ $recurringInvoice->auto_send ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-secondary-700 dark:text-secondary-300">Envoyer automatiquement par email</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="auto_send_peppol" value="1" {{ $recurringInvoice->auto_send_peppol ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-secondary-700 dark:text-secondary-300">Envoyer via Peppol</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="include_structured_communication" value="1" {{ $recurringInvoice->include_structured_communication ? 'checked' : '' }} class="form-checkbox">
                            <span class="text-secondary-700 dark:text-secondary-300">Inclure communication structuree</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
