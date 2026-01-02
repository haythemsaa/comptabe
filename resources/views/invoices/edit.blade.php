<x-app-layout>
    <x-slot name="title">Modifier facture {{ $invoice->invoice_number }}</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('invoices.index') }}" class="text-secondary-500 hover:text-secondary-700">Factures</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('invoices.show', $invoice) }}" class="text-secondary-500 hover:text-secondary-700">{{ $invoice->invoice_number }}</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Modifier</span>
    @endsection

    <form
        method="POST"
        action="{{ route('invoices.update', $invoice) }}"
        x-data="invoiceEditForm()"
        x-init="init()"
        class="space-y-6"
    >
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Modifier {{ $invoice->invoice_number }}</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Modifiez les informations de la facture</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
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
                                <label class="form-label">Client *</label>
                                <select
                                    name="partner_id"
                                    x-model="partnerId"
                                    @change="checkPeppolStatus()"
                                    required
                                    class="form-select @error('partner_id') form-input-error @enderror"
                                >
                                    <option value="">Sélectionner un client...</option>
                                    @foreach($partners as $partner)
                                        <option
                                            value="{{ $partner->id }}"
                                            data-peppol="{{ $partner->peppol_capable ? 'true' : 'false' }}"
                                            {{ old('partner_id', $invoice->partner_id) == $partner->id ? 'selected' : '' }}
                                        >
                                            {{ $partner->name }} {{ $partner->vat_number ? '(' . $partner->vat_number . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('partner_id')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror

                                <!-- Peppol Status -->
                                <div x-show="partnerPeppolCapable" x-transition class="mt-2 flex items-center gap-2 text-sm text-success-600">
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
                                    value="{{ $invoice->invoice_number }}"
                                    class="form-input bg-secondary-50"
                                    readonly
                                >
                                <p class="form-helper">Non modifiable</p>
                            </div>

                            <!-- Reference -->
                            <div>
                                <label class="form-label">Votre référence</label>
                                <input
                                    type="text"
                                    name="reference"
                                    value="{{ old('reference', $invoice->reference) }}"
                                    class="form-input"
                                    placeholder="Bon de commande, devis..."
                                >
                            </div>

                            <!-- Invoice Date -->
                            <div>
                                <label class="form-label">Date de facture *</label>
                                <input
                                    type="date"
                                    name="invoice_date"
                                    value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}"
                                    required
                                    class="form-input @error('invoice_date') form-input-error @enderror"
                                >
                                @error('invoice_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="form-label">Date d'échéance</label>
                                <input
                                    type="date"
                                    name="due_date"
                                    value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                                    class="form-input @error('due_date') form-input-error @enderror"
                                >
                                @error('due_date')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Lines -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Lignes de facture</h2>
                        <div class="flex items-center gap-2">
                            @if($products->count() > 0)
                                <a href="{{ route('products.index') }}" class="text-xs text-primary-600 hover:text-primary-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    {{ $products->count() }} produits
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

                                    <!-- Hidden line ID for existing lines -->
                                    <input type="hidden" :name="`lines[${index}][id]`" :value="line.dbId || ''">

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
                                            <div class="relative">
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
                                            </div>
                                        </div>
                                        @endif

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
                                            <input type="hidden" :name="`lines[${index}][product_id]`" x-model="line.productId">
                                        </div>
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
                                            <label class="form-label">Prix unitaire *</label>
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
                                            <div class="form-input bg-secondary-100 dark:bg-secondary-700 font-medium" x-text="formatCurrency(calculateLineTotal(line))"></div>
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
                        >{{ old('notes', $invoice->notes) }}</textarea>
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
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">Sous-total HT</span>
                            <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                        </div>

                        <!-- VAT -->
                        <div class="flex items-center justify-between">
                            <span class="text-secondary-600 dark:text-secondary-400">TVA</span>
                            <span class="font-medium text-secondary-900 dark:text-white" x-text="formatCurrency(totalVat)"></span>
                        </div>

                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-secondary-900 dark:text-white">Total TTC</span>
                                <span class="text-2xl font-bold text-primary-600" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>

                        <!-- Structured Communication -->
                        <div class="pt-4 border-t border-secondary-200 dark:border-secondary-700">
                            <label class="form-label">Communication structurée</label>
                            <input
                                type="text"
                                name="structured_communication"
                                value="{{ $invoice->structured_communication }}"
                                class="form-input font-mono text-center"
                                readonly
                            >
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

                <!-- Warning -->
                <div class="card bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-800">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-warning-100 dark:bg-warning-900/30 rounded-xl flex items-center justify-center text-warning-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-warning-900 dark:text-warning-100">Facture brouillon</h3>
                                <p class="mt-1 text-sm text-warning-700 dark:text-warning-300">
                                    Cette facture est encore modifiable. Une fois validée, elle ne pourra plus être modifiée.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function invoiceEditForm() {
            return {
                partnerId: '{{ old('partner_id', $invoice->partner_id) }}',
                partnerPeppolCapable: {{ $invoice->partner->peppol_capable ? 'true' : 'false' }},
                lines: @json($invoice->lines->map(function($line) {
                    return [
                        'id' => uniqid(),
                        'dbId' => $line->id,
                        'productId' => $line->product_id ?? '',
                        'description' => $line->description,
                        'quantity' => $line->quantity,
                        'unit' => $line->unit ?? 'hour',
                        'unitPrice' => $line->unit_price,
                        'vatRate' => $line->vat_rate,
                        'discount' => $line->discount_percent ?? 0
                    ];
                })),
                products: @json($products->map(fn($p) => [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'description' => $p->description,
                    'type' => $p->type,
                    'unit_price' => floatval($p->unit_price),
                    'unit' => $p->unit,
                    'vat_rate' => floatval($p->vat_rate),
                    'category' => $p->category,
                ])),
                productSearch: '',
                productDropdownOpen: null,

                init() {
                    if (this.lines.length === 0) {
                        this.addLine();
                    }
                },

                addLine() {
                    this.lines.push({
                        id: Date.now(),
                        dbId: null,
                        productId: '',
                        description: '',
                        quantity: 1,
                        unit: 'hour',
                        unitPrice: 0,
                        vatRate: 21,
                        discount: 0
                    });
                },

                removeLine(index) {
                    if (this.lines.length > 1) {
                        this.lines.splice(index, 1);
                    }
                },

                calculateLineTotal(line) {
                    const subtotal = line.quantity * line.unitPrice;
                    const discount = subtotal * (line.discount / 100);
                    return subtotal - discount;
                },

                get subtotal() {
                    return this.lines.reduce((sum, line) => sum + this.calculateLineTotal(line), 0);
                },

                get totalVat() {
                    return this.lines.reduce((sum, line) => {
                        const lineTotal = this.calculateLineTotal(line);
                        return sum + (lineTotal * line.vatRate / 100);
                    }, 0);
                },

                get total() {
                    return this.subtotal + this.totalVat;
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('{{ $companyCountryCode === "TN" ? "fr-TN" : "fr-BE" }}', {
                        style: 'currency',
                        currency: '{{ $companyCurrency }}',
                        minimumFractionDigits: {{ $companyDecimalPlaces }},
                        maximumFractionDigits: {{ $companyDecimalPlaces }}
                    }).format(amount);
                },

                checkPeppolStatus() {
                    const select = document.querySelector('select[name="partner_id"]');
                    const option = select.options[select.selectedIndex];
                    this.partnerPeppolCapable = option?.dataset?.peppol === 'true';
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
                        line.description = product.name;
                        line.unitPrice = product.unit_price;
                        line.unit = this.mapProductUnit(product.unit);
                        line.vatRate = product.vat_rate;
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
            }
        }
    </script>
    @endpush
</x-app-layout>
