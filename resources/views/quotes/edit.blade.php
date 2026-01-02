<x-app-layout>
    <x-slot name="title">Modifier {{ $quote->quote_number }}</x-slot>

    <script>
        window.quoteForm = function() {
            return {
                partnerId: '{{ $quote->partner_id }}',
                lines: @json($quote->lines->map(fn($l) => [
                    'id' => $l->id,
                    'description' => $l->description,
                    'quantity' => floatval($l->quantity),
                    'unitPrice' => floatval($l->unit_price),
                    'vatRate' => floatval($l->vat_rate),
                    'discount' => floatval($l->discount_percent),
                    'accountId' => $l->account_id ?? ''
                ])),
                lineIdCounter: {{ $quote->lines->count() }},
                vatRates: @json($vatCodes->pluck('rate')->unique()->values()),
                defaultVatRate: 21,
                quoteDate: '{{ $quote->quote_date->format('Y-m-d') }}',
                validUntil: '{{ $quote->valid_until?->format('Y-m-d') ?? '' }}',

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

                setValidityDays(days) {
                    const date = new Date(this.quoteDate);
                    date.setDate(date.getDate() + days);
                    this.validUntil = date.toISOString().split('T')[0];
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

                get vatBreakdown() {
                    const breakdown = {};
                    this.lines.forEach(line => {
                        const rate = line.vatRate || 0;
                        if (!breakdown[rate]) {
                            breakdown[rate] = { base: 0, vat: 0 };
                        }
                        breakdown[rate].base += this.calculateLineTotal(line);
                        breakdown[rate].vat += this.calculateLineVat(line);
                    });
                    return breakdown;
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
        <a href="{{ route('quotes.index') }}" class="text-secondary-500 hover:text-secondary-700">Devis</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Modifier {{ $quote->quote_number }}</span>
    @endsection

    <form
        method="POST"
        action="{{ route('quotes.update', $quote) }}"
        x-data="window.quoteForm()"
        x-init="init()"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier {{ $quote->quote_number }}</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Modifiez les details du devis</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('quotes.show', $quote) }}" class="btn btn-secondary">Annuler</a>
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
                <!-- Client & Info -->
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
                                    class="form-select @error('partner_id') form-input-error @enderror"
                                >
                                    <option value="">Selectionner un client...</option>
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->id }}">
                                            {{ $partner->name }} {{ $partner->vat_number ? '(' . $partner->vat_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('partner_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quote Number -->
                            <div>
                                <label class="form-label">Numero de devis</label>
                                <input
                                    type="text"
                                    value="{{ $quote->quote_number }}"
                                    class="form-input bg-secondary-50"
                                    readonly
                                >
                            </div>

                            <!-- Reference -->
                            <div>
                                <label class="form-label">Reference</label>
                                <input
                                    type="text"
                                    name="reference"
                                    value="{{ old('reference', $quote->reference) }}"
                                    class="form-input"
                                    placeholder="Votre reference..."
                                >
                            </div>

                            <!-- Quote Date -->
                            <div>
                                <label class="form-label">Date du devis *</label>
                                <input
                                    type="date"
                                    name="quote_date"
                                    x-model="quoteDate"
                                    required
                                    class="form-input @error('quote_date') form-input-error @enderror"
                                >
                                @error('quote_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Valid Until -->
                            <div>
                                <label class="form-label">Valide jusqu'au</label>
                                <div class="flex gap-2">
                                    <input
                                        type="date"
                                        name="valid_until"
                                        x-model="validUntil"
                                        class="form-input flex-1"
                                    >
                                    <div class="flex gap-1">
                                        <button type="button" @click="setValidityDays(15)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors">15j</button>
                                        <button type="button" @click="setValidityDays(30)" class="px-2 py-1 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:hover:bg-primary-800 text-primary-700 dark:text-primary-300 rounded transition-colors">30j</button>
                                        <button type="button" @click="setValidityDays(60)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors">60j</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quote Lines -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes du devis</h2>
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
                                                title="Dupliquer"
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
                                                title="Supprimer"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label class="form-label">Description *</label>
                                        <input
                                            type="text"
                                            :name="`lines[${index}][description]`"
                                            x-model="line.description"
                                            required
                                            class="form-input"
                                            placeholder="Description du produit ou service"
                                        >
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                        <!-- Quantity -->
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

                                        <!-- Unit Price -->
                                        <div>
                                            <label class="form-label">Prix unit. *</label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    :name="`lines[${index}][unit_price]`"
                                                    x-model.number="line.unitPrice"
                                                    required
                                                    min="0"
                                                    step="0.01"
                                                    class="form-input pr-8"
                                                >
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">EUR</span>
                                            </div>
                                        </div>

                                        <!-- VAT Rate -->
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

                                        <!-- Discount -->
                                        <div>
                                            <label class="form-label">Remise</label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    :name="`lines[${index}][discount_percent]`"
                                                    x-model.number="line.discount"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    class="form-input pr-8"
                                                >
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">%</span>
                                            </div>
                                        </div>

                                        <!-- Line Total -->
                                        <div>
                                            <label class="form-label">Total HT</label>
                                            <div class="form-input bg-secondary-100 dark:bg-secondary-700 font-medium" x-text="formatCurrency(calculateLineTotal(line))"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Line Button -->
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

                <!-- Notes & Terms -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Notes et conditions</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                rows="3"
                                class="form-input"
                                placeholder="Notes pour le client..."
                            >{{ old('notes', $quote->notes) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Conditions generales</label>
                            <textarea
                                name="terms"
                                rows="3"
                                class="form-input"
                                placeholder="Conditions de vente..."
                            >{{ old('terms', $quote->terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Totals Card -->
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Recapitulatif</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                        </div>

                        <template x-for="(amounts, rate) in vatBreakdown" :key="rate">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-secondary-500">TVA <span x-text="rate"></span>%</span>
                                <span class="text-secondary-600 dark:text-secondary-400" x-text="formatCurrency(amounts.vat)"></span>
                            </div>
                        </template>

                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-800 pt-2">
                            <span class="text-secondary-600 dark:text-secondary-400 font-medium">Total TVA</span>
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
            </div>
        </div>
    </form>
</x-app-layout>
