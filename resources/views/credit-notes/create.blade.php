@php
    $partnersData = $partners->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'subtitle' => $p->vat_number ?? $p->email,
            'initials' => strtoupper(substr($p->name, 0, 2)),
        ];
    })->values()->toArray();
@endphp

<x-app-layout>
    <x-slot name="title">Nouvelle note de crédit</x-slot>

    <script>
        window.creditNoteForm = function() {
            return {
                partnerId: '{{ $invoice?->partner_id ?? '' }}',
                lines: [],
                lineIdCounter: 0,
                vatRates: @json($vatCodes->pluck('rate')->unique()->values()),
                defaultVatRate: 21,

                init() {
                    @if($invoice)
                        // Pre-fill from invoice
                        @foreach($invoice->lines as $line)
                            this.lines.push({
                                id: ++this.lineIdCounter,
                                description: @json($line->description),
                                quantity: {{ $line->quantity }},
                                unitPrice: {{ $line->unit_price }},
                                vatRate: {{ $line->vat_rate }},
                                discount: {{ $line->discount_percent ?? 0 }},
                                accountId: '{{ $line->account_id ?? '' }}'
                            });
                        @endforeach
                    @else
                        this.addLine();
                    @endif
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

                calculateTotals() {
                    // Trigger reactivity
                },

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
        <a href="{{ route('credit-notes.index') }}" class="text-secondary-500 hover:text-secondary-700">Notes de crédit</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle</span>
    @endsection

    <form
        method="POST"
        action="{{ route('credit-notes.store') }}"
        x-data="window.creditNoteForm()"
        x-init="init()"
        class="space-y-6"
    >
        @csrf

        @if($invoice)
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouvelle note de crédit</h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    @if($invoice)
                        Créer un avoir pour la facture {{ $invoice->invoice_number }}
                    @else
                        Créer une note de crédit pour votre client
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('credit-notes.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Créer
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Client & Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations générales</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Client -->
                            <div class="md:col-span-2">
@if($invoice)
                                    <label class="form-label">Client *</label>
                                    <div class="form-input bg-secondary-50 dark:bg-secondary-700 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 text-sm font-medium">
                                            {{ strtoupper(substr($invoice->partner->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $invoice->partner->name }}</div>
                                            <div class="text-xs text-secondary-500">{{ $invoice->partner->vat_number }}</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="partner_id" value="{{ $invoice->partner_id }}">
                                @else
                                    <x-searchable-select
                                        name="partner_id"
                                        label="Client"
                                        placeholder="Rechercher un client..."
                                        :options="$partnersData"
                                        :required="true"
                                        display-key="name"
                                        value-key="id"
                                        x-on:change="partnerId = $event.detail.value"
                                    />
                                @endif
                            </div>

                            <!-- Credit Note Number -->
                            <div>
                                <label class="form-label">Numéro note de crédit</label>
                                <input
                                    type="text"
                                    value="{{ $nextNumber }}"
                                    class="form-input bg-secondary-50"
                                    readonly
                                >
                                <p class="form-helper">Généré automatiquement</p>
                            </div>

                            <!-- Reference -->
                            <div>
                                <label class="form-label">Référence</label>
                                <input
                                    type="text"
                                    name="reference"
                                    value="{{ old('reference', $invoice ? 'Ref: ' . $invoice->invoice_number : '') }}"
                                    class="form-input"
                                    placeholder="Référence facture d'origine..."
                                >
                            </div>

                            <!-- Credit Note Date -->
                            <div>
                                <label class="form-label">Date de la note *</label>
                                <input
                                    type="date"
                                    name="credit_note_date"
                                    value="{{ old('credit_note_date', date('Y-m-d')) }}"
                                    required
                                    class="form-input @error('credit_note_date') form-input-error @enderror"
                                >
                                @error('credit_note_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Linked Invoice -->
                            @if($invoice)
                                <div>
                                    <label class="form-label">Facture liée</label>
                                    <div class="form-input bg-secondary-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $invoice->invoice_number }}
                                    </div>
                                </div>
                            @endif

                            <!-- Reason -->
                            <div class="md:col-span-2">
                                <label class="form-label">Motif de la note de crédit</label>
                                <textarea
                                    name="reason"
                                    rows="2"
                                    class="form-input"
                                    placeholder="Raison de l'émission de cette note de crédit..."
                                >{{ old('reason') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Note Lines -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes de la note de crédit</h2>
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
                                <div
                                    class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl space-y-4 border border-danger-200 dark:border-danger-800"
                                >
                                    <div class="flex items-start justify-between">
                                        <span class="text-sm font-medium text-danger-600 dark:text-danger-400">Ligne <span x-text="index + 1"></span> (avoir)</span>
                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="duplicateLine(index)"
                                                class="text-secondary-400 hover:text-primary-600 transition-colors"
                                                title="Dupliquer cette ligne"
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
                                                title="Supprimer cette ligne"
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
                                            placeholder="Description du produit ou service à créditer"
                                        >
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                        <!-- Quantity -->
                                        <div>
                                            <label class="form-label">Quantité *</label>
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
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400">€</span>
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
                                            <div class="form-input bg-danger-100 dark:bg-danger-900/30 font-medium text-danger-600">
                                                -<span x-text="formatCurrency(calculateLineTotal(line)).replace('-', '')"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Line Button -->
                        <button
                            type="button"
                            @click="addLine()"
                            class="mt-4 w-full py-3 border-2 border-dashed border-danger-300 dark:border-danger-600 rounded-xl text-danger-500 hover:text-danger-600 hover:border-danger-400 transition-colors flex items-center justify-center gap-2"
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
                        <label class="form-label">Notes internes</label>
                        <textarea
                            name="notes"
                            rows="3"
                            class="form-input"
                            placeholder="Notes internes concernant cette note de crédit..."
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Totals -->
            <div class="space-y-6">
                <!-- Totals Card -->
                <div class="card sticky top-24 border-danger-200 dark:border-danger-800">
                    <div class="card-header bg-danger-50 dark:bg-danger-900/20">
                        <h2 class="font-semibold text-danger-800 dark:text-danger-200">Montant à créditer</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <!-- Subtotal -->
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-danger-600">-<span x-text="formatCurrency(subtotal).replace('-', '')"></span></span>
                        </div>

                        <!-- VAT Breakdown -->
                        <template x-for="(amounts, rate) in vatBreakdown" :key="rate">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-secondary-500">TVA <span x-text="rate"></span>%</span>
                                <span class="text-danger-500">-<span x-text="formatCurrency(amounts.vat).replace('-', '')"></span></span>
                            </div>
                        </template>

                        <!-- VAT Total -->
                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-800 pt-2">
                            <span class="text-secondary-600 dark:text-secondary-400 font-medium">Total TVA</span>
                            <span class="font-medium text-danger-600">-<span x-text="formatCurrency(totalVat).replace('-', '')"></span></span>
                        </div>

                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-danger-600">-<span x-text="formatCurrency(total).replace('-', '')"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            Créer la note de crédit
                        </button>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-info-100 dark:bg-info-900/30 rounded-xl flex items-center justify-center text-info-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-info-900 dark:text-info-100">Note de crédit</h3>
                                <p class="mt-1 text-sm text-info-700 dark:text-info-300">
                                    Une note de crédit permet d'annuler partiellement ou totalement une facture émise. Elle sera automatiquement liée à la facture d'origine si spécifiée.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</x-app-layout>
