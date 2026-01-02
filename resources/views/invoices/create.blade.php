@php
    $productsData = $products->map(function($p) {
        return [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'description' => $p->description,
            'type' => $p->type,
            'unit_price' => floatval($p->unit_price),
            'unit' => $p->unit,
            'vat_rate' => floatval($p->vat_rate),
            'category' => $p->category,
        ];
    })->values()->toArray();
    $vatRatesData = $vatCodes->pluck('rate')->unique()->values()->toArray();

    // Prepare partners data for searchable select
    $partnersData = $partners->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'subtitle' => $p->vat_number ?? $p->email,
            'initials' => strtoupper(substr($p->name, 0, 2)),
            'peppol' => $p->peppol_capable ?? false,
        ];
    })->values()->toArray();
@endphp

<x-app-layout>
    <x-slot name="title">Nouvelle facture</x-slot>

    <script>
        window.invoiceForm = function() {
            return {
                partnerId: '{{ old('partner_id', '') }}',
                partnerPeppolCapable: false,
                lines: [],
                lineIdCounter: 0,
                vatRates: @json($vatRatesData),
                defaultVatRate: {{ $companyDefaultVatRate }},
                invoiceDate: '{{ old('invoice_date', date('Y-m-d')) }}',
                dueDate: '{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}',
                products: @json($productsData),
                productSearch: '',
                productDropdownOpen: null,

                // Currency management
                currency: '{{ old('currency', $companyCurrency) }}',
                currenciesConfig: @json(config('currencies.available')),
                currencyConfig: null,

                // Totals (calculated values, not getters)
                subtotal: 0,
                totalVat: 0,
                total: 0,

                init() {
                    // CRITICAL: Remove dates from localStorage draft to force fresh dates
                    const draftKey = 'comptabe_draft_invoice-form';
                    const draft = localStorage.getItem(draftKey);
                    if (draft) {
                        try {
                            const parsed = JSON.parse(draft);
                            // Remove date fields from draft data
                            if (parsed.data) {
                                delete parsed.data.invoice_date;
                                delete parsed.data.due_date;
                                localStorage.setItem(draftKey, JSON.stringify(parsed));
                                console.log('🗑️ Dates supprimées du brouillon');
                            }
                        } catch (e) {
                            console.warn('Erreur nettoyage brouillon:', e);
                        }
                    }

                    this.updateCurrencyFormat();

                    // Restore lines from Laravel old() values (after validation error)
                    @if(old('lines'))
                        const oldLines = @json(old('lines'));
                        if (oldLines && oldLines.length > 0) {
                            oldLines.forEach(line => {
                                this.lines.push({
                                    id: ++this.lineIdCounter,
                                    productId: line.product_id || '',
                                    productType: '',
                                    description: line.description || '',
                                    quantity: parseFloat(line.quantity) || 1,
                                    unit: line.unit || 'hour',
                                    unitPrice: parseFloat(line.unit_price) || 0,
                                    vatRate: parseFloat(line.vat_rate) || this.defaultVatRate,
                                    discount: parseFloat(line.discount_percent) || 0,
                                    accountId: line.account_id || ''
                                });
                            });
                        }
                    @endif

                    // Add only ONE line by default if no lines exist
                    if (this.lines.length === 0) {
                        this.addLine();
                    }

                    // Watch for line changes and recalculate totals
                    this.$watch('lines', (newValue, oldValue) => {
                        console.log('👀 Watch déclenché sur lines');
                        this.calculateTotals();
                    }, { deep: true });

                    // IMPORTANT: Force dates to be today + 15 days (override any draft)
                    // Use setTimeout to ensure this runs AFTER autoSave mixin restoration
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const today = '{{ date('Y-m-d') }}';
                            const dueIn15Days = '{{ date('Y-m-d', strtotime('+15 days')) }}';

                            // Only override if NOT from validation error (old() values)
                            @if(!old('invoice_date'))
                                this.invoiceDate = today;
                                // Also update the input field directly
                                const invoiceDateInput = document.querySelector('input[name="invoice_date"]');
                                if (invoiceDateInput) {
                                    invoiceDateInput.value = today;
                                }
                                console.log('📅 Date de facture forcée à aujourd\'hui:', today);
                            @endif

                            @if(!old('due_date'))
                                this.dueDate = dueIn15Days;
                                // Also update the input field directly
                                const dueDateInput = document.querySelector('input[name="due_date"]');
                                if (dueDateInput) {
                                    dueDateInput.value = dueIn15Days;
                                }
                                console.log('📅 Date d\'échéance forcée à +15 jours:', dueIn15Days);
                            @endif

                            // Force initial calculation
                            this.calculateTotals();
                        }, 100); // Wait 100ms to ensure mixin has finished
                    });
                },

                updateCurrencyFormat() {
                    this.currencyConfig = this.currenciesConfig[this.currency] || this.currenciesConfig['{{ $companyCurrency }}'];
                },

                addLine() {
                    this.lines.push({
                        id: ++this.lineIdCounter,
                        productId: '',
                        productType: '', // 'service' or 'product'
                        description: '',
                        quantity: 1,
                        unit: 'hour',
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

                checkPeppolStatus() {
                    const select = document.querySelector('[name="partner_id"]');
                    const option = select.options[select.selectedIndex];
                    this.partnerPeppolCapable = option?.dataset?.peppol === 'true';
                },

                setPaymentTerm(days) {
                    const date = new Date();
                    date.setDate(date.getDate() + days);
                    this.dueDate = date.toISOString().split('T')[0];
                },

                calculateTotals() {
                    // Calculate subtotal
                    this.subtotal = this.lines.reduce((sum, line) => sum + this.calculateLineTotal(line), 0);

                    // Calculate VAT
                    this.totalVat = this.lines.reduce((sum, line) => sum + this.calculateLineVat(line), 0);

                    // Calculate total
                    this.total = this.subtotal + this.totalVat;

                    console.log('💰 Totaux calculés - HT:', this.subtotal, 'TVA:', this.totalVat, 'TTC:', this.total);
                },

                calculateLineTotal(line) {
                    const subtotal = (line.quantity || 0) * (line.unitPrice || 0);
                    const discount = subtotal * ((line.discount || 0) / 100);
                    return subtotal - discount;
                },

                calculateLineVat(line) {
                    return this.calculateLineTotal(line) * ((line.vatRate || 0) / 100);
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
                    if (!this.currencyConfig) {
                        this.updateCurrencyFormat();
                    }
                    return new Intl.NumberFormat(this.currencyConfig.locale, {
                        style: 'currency',
                        currency: this.currency,
                        minimumFractionDigits: this.currencyConfig.decimal_places,
                        maximumFractionDigits: this.currencyConfig.decimal_places
                    }).format(value || 0);
                },

                // Unit mapping between Product model and invoice units
                mapProductUnit(productUnit) {
                    const unitMap = {
                        'unité': 'unit',
                        'heure': 'hour',
                        'jour': 'day',
                        'mois': 'month',
                        'an': 'year',
                        'kg': 'kg',
                        'm²': 'm2',
                        'forfait': 'forfait',
                    };
                    return unitMap[productUnit] || 'unit';
                },

                applyProduct(product, lineIndex) {
                    const line = this.lines[lineIndex];
                    if (line) {
                        line.productId = product.id;
                        line.productType = product.type; // 'service' or 'product'
                        line.description = product.name;
                        line.unitPrice = product.unit_price;
                        line.unit = this.mapProductUnit(product.unit);
                        line.vatRate = product.vat_rate;
                        // For services, set quantity to 1 and hide it
                        if (product.type === 'service') {
                            line.quantity = 1;
                        }
                    }
                    this.productDropdownOpen = null;
                    this.productSearch = '';
                },

                filteredProducts(search) {
                    if (!search || search.length < 1) return this.products.slice(0, 10);
                    const s = search.toLowerCase();
                    return this.products.filter(p =>
                        (p.name && p.name.toLowerCase().includes(s)) ||
                        (p.code && p.code.toLowerCase().includes(s)) ||
                        (p.category && p.category.toLowerCase().includes(s))
                    ).slice(0, 10);
                },

                toggleProductDropdown(lineIndex) {
                    if (this.productDropdownOpen === lineIndex) {
                        this.productDropdownOpen = null;
                    } else {
                        this.productDropdownOpen = lineIndex;
                        this.productSearch = '';
                    }
                }
            };
        };
    </script>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('invoices.index') }}" class="text-secondary-500 hover:text-secondary-700">Factures</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle facture</span>
    @endsection

    <form
        method="POST"
        action="{{ route('invoices.store') }}"
        x-data="{
            ...window.invoiceForm(),
            ...window.autoSaveMixin('invoice_create', 15000),
            showDraftModal: false
        }"
        x-init="
            init();
            initAutoSave();
            $watch('lines', () => markFormDirty(), true);
            if (hasFormDraft()) showDraftModal = true;
        "
        @input="markFormDirty()"
        @change="markFormDirty()"
        @submit="clearFormDraft()"
        class="space-y-6"
    >
        @csrf

        <!-- Draft Restore Modal -->
        <div x-show="showDraftModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div @click.away="showDraftModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-secondary-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary-900 dark:text-white">Brouillon trouvé</h3>
                        <p class="text-sm text-secondary-500" x-text="'Sauvegardé ' + formatAutoSaveTime()"></p>
                    </div>
                </div>
                <p class="text-secondary-600 dark:text-secondary-300 mb-6">
                    Un brouillon de facture a été trouvé. Voulez-vous restaurer vos modifications précédentes ?
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button"
                            @click="clearFormDraft(); showDraftModal = false"
                            class="btn btn-secondary">
                        Non, ignorer
                    </button>
                    <button type="button"
                            @click="restoreFormDraft(); showDraftModal = false"
                            class="btn btn-primary">
                        Oui, restaurer
                    </button>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouvelle facture de vente</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Créez une nouvelle facture pour votre client</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Auto-save status -->
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <template x-if="autoSave.isDirty && autoSave.autoSaveEnabled">
                        <span class="flex items-center gap-1 text-warning-500">
                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="hidden lg:inline">Modifications non sauvées</span>
                        </span>
                    </template>
                    <template x-if="!autoSave.isDirty && autoSave.lastSaved">
                        <span class="flex items-center gap-1 text-success-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="hidden lg:inline" x-text="formatAutoSaveTime()"></span>
                        </span>
                    </template>
                </div>

                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" name="action" value="draft" class="btn btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Brouillon
                </button>
                <button type="submit" name="action" value="create" class="btn btn-primary">
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
                                <x-searchable-select
                                    name="partner_id"
                                    label="Client"
                                    placeholder="Rechercher un client..."
                                    :options="$partnersData"
                                    :required="true"
                                    display-key="name"
                                    value-key="id"
                                    x-on:change="
                                        partnerId = $event.detail.value;
                                        partnerPeppolCapable = $event.detail.item?.peppol || false;
                                    "
                                />

                                <!-- Peppol Status -->
                                <div x-show="partnerPeppolCapable" x-transition class="mt-2 flex items-center gap-2 text-sm text-success-600 dark:text-success-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Ce client peut recevoir des factures via Peppol
                                </div>
                            </div>

                            <!-- Invoice Number -->
                            <div>
                                <label class="form-label">Numéro de facture</label>
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
                                <label class="form-label">Votre référence</label>
                                <input
                                    type="text"
                                    name="reference"
                                    value="{{ old('reference') }}"
                                    class="form-input"
                                    placeholder="Bon de commande, devis..."
                                >
                            </div>

                            <!-- Invoice Date -->
                            <div>
                                <label class="form-label flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Date de facture *
                                </label>
                                <input
                                    type="date"
                                    name="invoice_date"
                                    x-model="invoiceDate"
                                    required
                                    class="form-input transition-all focus:ring-2 focus:ring-primary-500 @error('invoice_date') form-input-error @enderror"
                                    data-datepicker
                                >
                                <p class="form-helper text-success-600 dark:text-success-400">
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Date du jour par défaut
                                </p>
                                @error('invoice_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

            <!-- Due Date -->
                            <div>
                                <label class="form-label">Date d'échéance</label>
                                <div class="flex gap-2">
                                    <input
                                        type="date"
                                        name="due_date"
                                        x-model="dueDate"
                                        class="form-input flex-1 @error('due_date') form-input-error @enderror"
                                        data-datepicker
                                    >
                                    <div class="flex gap-1">
                                        <button type="button" @click="setPaymentTerm(0)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors" title="Comptant">0j</button>
                                        <button type="button" @click="setPaymentTerm(15)" class="px-2 py-1 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:hover:bg-primary-800 text-primary-700 dark:text-primary-300 rounded transition-colors" title="15 jours (par défaut)">15j</button>
                                        <button type="button" @click="setPaymentTerm(30)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors" title="30 jours">30j</button>
                                        <button type="button" @click="setPaymentTerm(60)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors" title="60 jours">60j</button>
                                    </div>
                                </div>
                                <p class="form-helper text-success-600 dark:text-success-400">
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Échéance à 15 jours par défaut
                                </p>
                                @error('due_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Currency Selection -->
                            <div>
                                <label class="form-label">Devise *</label>
                                <select
                                    name="currency"
                                    x-model="currency"
                                    @change="updateCurrencyFormat()"
                                    class="form-input @error('currency') form-input-error @enderror"
                                    required
                                >
                                    @foreach(config('currencies.available') as $code => $curr)
                                        <option value="{{ $code }}" {{ old('currency', $companyCurrency) === $code ? 'selected' : '' }}>
                                            {{ $curr['symbol'] }} {{ $code }} - {{ $curr['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="form-helper">La devise par défaut est {{ $companyCurrency }}, mais vous pouvez facturer dans n'importe quelle devise</p>
                                @error('currency')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Lines -->
                <div class="card">
                    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes de facture</h2>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($products->count() > 0)
                                <a href="{{ route('products.index') }}" class="text-xs text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    {{ $products->count() }} produits
                                </a>
                            @else
                                <a href="{{ route('products.create') }}" class="text-xs text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Créer un produit
                                </a>
                            @endif
                            <button
                                type="button"
                                @click="addLine()"
                                class="btn btn-secondary btn-sm"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Ajouter ligne
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="space-y-4">
                            <template x-for="(line, index) in lines" :key="line.id">
                                <div
                                    class="p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl space-y-4 animate-fade-in"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform -translate-y-4"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                >
                                    <div class="flex items-start justify-between">
                                        <span class="text-sm font-medium text-secondary-500">Ligne <span x-text="index + 1"></span></span>
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

                                    <!-- Product Selection & Description -->
                                    <div class="space-y-2">
                                        <!-- Product Selector -->
                                        @if($products->count() > 0)
                                        <div class="relative">
                                            <label class="form-label flex items-center gap-2">
                                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                                Sélectionner un produit
                                            </label>
                                            <div class="flex gap-2">
                                                <div class="relative flex-1">
                                                    <input
                                                        type="text"
                                                        x-model="productSearch"
                                                        @focus="productDropdownOpen = index"
                                                        @input="productDropdownOpen = index"
                                                        class="form-input pr-10"
                                                        placeholder="Rechercher un produit ou service..."
                                                    >
                                                    <button
                                                        type="button"
                                                        @click="toggleProductDropdown(index)"
                                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                                                    >
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Product Dropdown -->
                                            <div
                                                x-show="productDropdownOpen === index"
                                                @click.away="productDropdownOpen = null"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                                class="absolute z-20 mt-1 w-full bg-white dark:bg-secondary-800 rounded-lg shadow-xl border border-secondary-200 dark:border-secondary-700 max-h-64 overflow-y-auto"
                                            >
                                                <template x-if="filteredProducts(productSearch).length === 0">
                                                    <div class="px-4 py-3 text-sm text-secondary-500 text-center">
                                                        Aucun produit trouvé
                                                    </div>
                                                </template>
                                                <template x-for="product in filteredProducts(productSearch)" :key="product.id">
                                                    <button
                                                        type="button"
                                                        @click="applyProduct(product, index)"
                                                        class="w-full px-4 py-3 text-left hover:bg-primary-50 dark:hover:bg-primary-900/20 flex items-center justify-between gap-3 border-b border-secondary-100 dark:border-secondary-700 last:border-0"
                                                    >
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <span x-show="product.code" class="text-xs font-mono px-1.5 py-0.5 bg-secondary-100 dark:bg-secondary-700 rounded text-secondary-600 dark:text-secondary-400" x-text="product.code"></span>
                                                                <span class="font-medium text-secondary-900 dark:text-white truncate" x-text="product.name"></span>
                                                            </div>
                                                            <div class="flex items-center gap-2 mt-0.5">
                                                                <span class="text-xs px-1.5 py-0.5 rounded-full" :class="product.type === 'service' ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300' : 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300'" x-text="product.type === 'service' ? 'Service' : 'Produit'"></span>
                                                                <span x-show="product.category" class="text-xs text-secondary-500" x-text="product.category"></span>
                                                            </div>
                                                        </div>
                                                        <div class="text-right flex-shrink-0">
                                                            <div class="font-semibold text-primary-600" x-text="formatCurrency(product.unit_price)"></div>
                                                            <div class="text-xs text-secondary-500">
                                                                <span x-text="product.vat_rate + '% TVA'"></span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </template>
                                                <div class="px-4 py-2 bg-secondary-50 dark:bg-secondary-900 border-t border-secondary-200 dark:border-secondary-700">
                                                    <a href="{{ route('products.create') }}" class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                        Créer un nouveau produit
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Description -->
                                        <div>
                                            <label class="form-label flex items-center gap-2">
                                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                                Description *
                                            </label>
                                            <textarea
                                                :name="`lines[${index}][description]`"
                                                x-model="line.description"
                                                required
                                                rows="2"
                                                class="form-input transition-all focus:ring-2 focus:ring-primary-500 resize-none"
                                                placeholder="Ex: Développement site web, Prestation conseil, Location conteneur..."
                                            ></textarea>
                                            <input type="hidden" :name="`lines[${index}][product_id]`" x-model="line.productId">
                                        </div>
                                    </div>

                                    <div class="grid gap-4" :class="line.productType === 'service' ? 'grid-cols-2 md:grid-cols-5' : 'grid-cols-2 md:grid-cols-6'">
                                        <!-- Quantity (hidden for services) -->
                                        <div x-show="line.productType !== 'service'">
                                            <label class="form-label">Quantité *</label>
                                            <input
                                                type="number"
                                                :name="`lines[${index}][quantity]`"
                                                x-model.number="line.quantity"
                                                @input="calculateTotals()"
                                                :required="line.productType !== 'service'"
                                                min="0.0001"
                                                step="0.0001"
                                                class="form-input transition-all focus:ring-2 focus:ring-primary-500"
                                            >
                                        </div>
                                        <!-- Hidden quantity field for services (always 1) -->
                                        <input
                                            x-show="line.productType === 'service'"
                                            type="hidden"
                                            :name="`lines[${index}][quantity]`"
                                            value="1"
                                        >

                                        <!-- Unit -->
                                        <div>
                                            <label class="form-label">Unité</label>
                                            <select
                                                :name="`lines[${index}][unit]`"
                                                x-model="line.unit"
                                                class="form-select"
                                            >
                                                <option value="unit">Pièce</option>
                                                <option value="hour">Heure</option>
                                                <option value="day">Jour</option>
                                                <option value="month">Mois</option>
                                                <option value="year">Année</option>
                                                <option value="km">Km</option>
                                                <option value="kg">Kg</option>
                                                <option value="m2">m²</option>
                                                <option value="forfait">Forfait</option>
                                            </select>
                                        </div>

                                        <!-- Unit Price -->
                                        <div>
                                            <label class="form-label">Prix unit. *</label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    :name="`lines[${index}][unit_price]`"
                                                    x-model.number="line.unitPrice"
                                                    @input="calculateTotals()"
                                                    required
                                                    min="0"
                                                    step="0.01"
                                                    class="form-input pr-8 transition-all focus:ring-2 focus:ring-primary-500"
                                                >
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">€</span>
                                            </div>
                                        </div>

                                        <!-- VAT Rate -->
                                        <div>
                                            <label class="form-label">TVA *</label>
                                            <select
                                                :name="`lines[${index}][vat_rate]`"
                                                x-model.number="line.vatRate"
                                                @change="calculateTotals()"
                                                required
                                                class="form-select transition-all focus:ring-2 focus:ring-primary-500"
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
                                                    @input="calculateTotals()"
                                                    min="0"
                                                    max="100"
                                                    step="0.01"
                                                    class="form-input pr-8 transition-all focus:ring-2 focus:ring-primary-500"
                                                    placeholder="0"
                                                >
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 text-sm">%</span>
                                            </div>
                                        </div>

                                        <!-- Line Total -->
                                        <div>
                                            <label class="form-label">Total HT</label>
                                            <div class="form-input bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/30 dark:to-primary-800/30 border-primary-200 dark:border-primary-700 font-bold text-primary-700 dark:text-primary-300 text-right tabular-nums transition-all" x-text="formatCurrency(calculateLineTotal(line))"></div>
                                        </div>
                                    </div>

                                    <!-- Account Selection -->
                                    <div class="mt-3 pt-3 border-t border-secondary-200 dark:border-secondary-700">
                                        <div class="flex items-center gap-4">
                                            <label class="form-label mb-0 text-sm whitespace-nowrap">Compte comptable</label>
                                            <select
                                                :name="`lines[${index}][account_id]`"
                                                x-model="line.accountId"
                                                class="form-select text-sm flex-1"
                                            >
                                                <option value="">Compte par défaut (7000)</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->account_number }} - {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Line Button (mobile friendly) -->
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
                        <label class="form-label">Notes (apparaîtront sur la facture)</label>
                        <textarea
                            name="notes"
                            rows="3"
                            class="form-input"
                            placeholder="Conditions de paiement, mentions légales..."
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Totals -->
            <div class="space-y-6">
                <!-- Totals Card -->
                <div class="card sticky top-24">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Récapitulatif</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <!-- Subtotal -->
                        <div class="flex items-center justify-between p-3 bg-secondary-50 dark:bg-secondary-800/50 rounded-lg">
                            <span class="text-secondary-600 dark:text-secondary-400 font-medium">Sous-total HT</span>
                            <span class="font-semibold text-lg text-secondary-900 dark:text-white transition-all" x-text="formatCurrency(subtotal)"></span>
                        </div>

                        <!-- VAT Breakdown -->
                        <div class="space-y-2">
                            <template x-for="(amounts, rate) in vatBreakdown" :key="rate">
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span class="text-secondary-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        TVA <span x-text="rate"></span>%
                                    </span>
                                    <span class="text-secondary-700 dark:text-secondary-300 font-medium transition-all" x-text="formatCurrency(amounts.vat)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- VAT Total -->
                        <div class="flex items-center justify-between border-t border-secondary-200 dark:border-secondary-700 pt-3 mt-3">
                            <span class="text-secondary-700 dark:text-secondary-300 font-semibold">Total TVA</span>
                            <span class="font-semibold text-lg text-secondary-900 dark:text-white transition-all" x-text="formatCurrency(totalVat)"></span>
                        </div>

                        <!-- Total TTC - Large and prominent -->
                        <div class="border-t-2 border-primary-200 dark:border-primary-800 pt-4 mt-4 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 p-4 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-lg font-bold text-secondary-900 dark:text-white">Total TTC</span>
                                </div>
                                <span class="text-3xl font-extrabold text-primary-600 dark:text-primary-400 transition-all tabular-nums" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>

                        <!-- Structured Communication -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <label class="form-label">Communication structurée</label>
                            <input
                                type="text"
                                name="structured_communication"
                                value="{{ $structuredCommunication }}"
                                class="form-input font-mono text-center"
                                readonly
                            >
                            <p class="form-helper">Générée automatiquement pour le paiement</p>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Créer la facture
                        </button>
                    </div>
                </div>

                <!-- Peppol Info -->
                <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center text-primary-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-primary-900 dark:text-primary-100">Envoi Peppol</h3>
                                <p class="mt-1 text-sm text-primary-700 dark:text-primary-300">
                                    Après création, vous pourrez envoyer cette facture directement via le réseau Peppol si le client est compatible.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</x-app-layout>
