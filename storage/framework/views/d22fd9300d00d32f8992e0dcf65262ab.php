<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Nouvelle facture d'achat <?php $__env->endSlot(); ?>

    <script>
        window.purchaseInvoiceForm = function() {
            return {
                partnerId: '',
                lines: [],
                lineIdCounter: 0,
                vatRates: <?php echo json_encode($vatCodes->pluck('rate')->unique()->values(), 15, 512) ?>,
                defaultVatRate: 21,
                invoiceDate: '<?php echo e(date('Y-m-d')); ?>',
                dueDate: '<?php echo e(date('Y-m-d', strtotime('+30 days'))); ?>',

                init() {
                    this.addLine();
                    this.$watch('lines', () => this.calculateTotals(), true);
                },

                addLine() {
                    this.lines.push({
                        id: ++this.lineIdCounter,
                        description: '',
                        quantity: 1,
                        unit: 'unit',
                        unitPrice: 0,
                        vatRate: this.defaultVatRate,
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

                setPaymentTerm(days) {
                    const date = new Date(this.invoiceDate);
                    date.setDate(date.getDate() + days);
                    this.dueDate = date.toISOString().split('T')[0];
                },

                calculateTotals() {
                    // Trigger reactivity
                },

                calculateLineTotal(line) {
                    return (line.quantity || 0) * (line.unitPrice || 0);
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
                    return new Intl.NumberFormat('<?php echo e($companyCountryCode === "TN" ? "fr-TN" : "fr-BE"); ?>', {
                        style: 'currency',
                        currency: '<?php echo e($companyCurrency); ?>',
                        minimumFractionDigits: <?php echo e($companyDecimalPlaces); ?>,
                        maximumFractionDigits: <?php echo e($companyDecimalPlaces); ?>

                    }).format(value || 0);
                }
            };
        };
    </script>

    <?php $__env->startSection('breadcrumb'); ?>
        <a href="<?php echo e(route('dashboard')); ?>" class="text-secondary-500 hover:text-primary-500 dark:text-secondary-400 dark:hover:text-primary-400">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="<?php echo e(route('purchases.index')); ?>" class="text-secondary-500 hover:text-primary-500 dark:text-secondary-400 dark:hover:text-primary-400">Factures d'achat</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Nouvelle facture</span>
    <?php $__env->stopSection(); ?>

    <form
        method="POST"
        action="<?php echo e(route('purchases.store')); ?>"
        x-data="window.purchaseInvoiceForm()"
        x-init="init()"
        class="space-y-6"
    >
        <?php echo csrf_field(); ?>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Nouvelle facture d'achat</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Enregistrez une facture reçue d'un fournisseur</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('purchases.index')); ?>" class="btn btn-secondary">Annuler</a>
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
                    Enregistrer
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Supplier & Info -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Informations de la facture</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Supplier -->
                            <div class="md:col-span-2">
                                <label class="form-label">Fournisseur *</label>
                                <select
                                    name="partner_id"
                                    x-model="partnerId"
                                    required
                                    class="form-select <?php $__errorArgs = ['partner_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                >
                                    <option value="">Sélectionner un fournisseur...</option>
                                    <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($partner->id); ?>">
                                            <?php echo e($partner->name); ?> <?php echo e($partner->vat_number ? '(' . $partner->vat_number . ')' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['partner_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="form-error"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Invoice Number -->
                            <div>
                                <label class="form-label">Numéro de facture fournisseur *</label>
                                <input
                                    type="text"
                                    name="invoice_number"
                                    value="<?php echo e(old('invoice_number')); ?>"
                                    required
                                    class="form-input <?php $__errorArgs = ['invoice_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    placeholder="Numéro sur la facture du fournisseur"
                                >
                                <?php $__errorArgs = ['invoice_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="form-error"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Reference -->
                            <div>
                                <label class="form-label">Référence interne</label>
                                <input
                                    type="text"
                                    name="reference"
                                    value="<?php echo e(old('reference')); ?>"
                                    class="form-input"
                                    placeholder="Bon de commande, projet..."
                                >
                            </div>

                            <!-- Invoice Date -->
                            <div>
                                <label class="form-label">Date de facture *</label>
                                <input
                                    type="date"
                                    name="invoice_date"
                                    x-model="invoiceDate"
                                    required
                                    class="form-input <?php $__errorArgs = ['invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                >
                                <?php $__errorArgs = ['invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="form-error"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="form-label">Date d'échéance</label>
                                <div class="flex gap-2">
                                    <input
                                        type="date"
                                        name="due_date"
                                        x-model="dueDate"
                                        class="form-input flex-1 <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> form-input-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    >
                                    <div class="flex gap-1">
                                        <button type="button" @click="setPaymentTerm(0)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors" title="Comptant">0j</button>
                                        <button type="button" @click="setPaymentTerm(30)" class="px-2 py-1 text-xs bg-primary-100 hover:bg-primary-200 dark:bg-primary-900 dark:hover:bg-primary-800 text-primary-700 dark:text-primary-300 rounded transition-colors" title="30 jours">30j</button>
                                        <button type="button" @click="setPaymentTerm(60)" class="px-2 py-1 text-xs bg-secondary-100 hover:bg-secondary-200 dark:bg-secondary-700 dark:hover:bg-secondary-600 rounded transition-colors" title="60 jours">60j</button>
                                    </div>
                                </div>
                                <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="form-error"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Lines -->
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
                                        <span class="text-sm font-medium text-secondary-500 dark:text-secondary-400">Ligne <span x-text="index + 1"></span></span>
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
                                            placeholder="Description du produit ou service"
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
                                            <label class="form-label">Prix unit. HT *</label>
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
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400"><?php echo e($companyCurrency); ?></span>
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
                                                <?php $__currentLoopData = $vatCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($vat->rate); ?>"><?php echo e($vat->rate); ?>%</option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>

                                        <!-- Account -->
                                        <div>
                                            <label class="form-label">Compte</label>
                                            <select
                                                :name="`lines[${index}][account_id]`"
                                                x-model="line.accountId"
                                                class="form-select"
                                            >
                                                <option value="">Par défaut</option>
                                                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($account->id); ?>"><?php echo e($account->account_number); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
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
                            placeholder="Notes internes sur cette facture..."
                        ><?php echo e(old('notes')); ?></textarea>
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

                        <!-- VAT Breakdown -->
                        <template x-for="(amounts, rate) in vatBreakdown" :key="rate">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-secondary-500 dark:text-secondary-400">TVA <span x-text="rate"></span>%</span>
                                <span class="text-secondary-600 dark:text-secondary-300" x-text="formatCurrency(amounts.vat)"></span>
                            </div>
                        </template>

                        <!-- VAT Total -->
                        <div class="flex items-center justify-between border-t border-secondary-100 dark:border-secondary-700 pt-2">
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
                            Enregistrer la facture
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div class="card bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-info-100 dark:bg-info-900/30 rounded-xl flex items-center justify-center text-info-600 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-info-900 dark:text-info-100">Facture d'achat</h3>
                                <p class="mt-1 text-sm text-info-700 dark:text-info-300">
                                    Cette facture sera enregistrée dans vos achats et la TVA sera comptabilisée comme déductible.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\compta\resources\views/invoices/create-purchase.blade.php ENDPATH**/ ?>