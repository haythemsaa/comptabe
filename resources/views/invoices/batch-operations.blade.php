<x-app-layout>
    <x-slot name="title">Opérations en lot - Factures</x-slot>
    <x-slot name="header">
        <h2 class="text-2xl font-bold">Opérations en lot</h2>
    </x-slot>

    <div x-data="batchOperations()" class="space-y-6">
        <!-- Action Bar -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Selection Info -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            @change="toggleSelectAll($event.target.checked)"
                            :checked="selectedInvoices.length === invoices.length && invoices.length > 0"
                            :indeterminate="selectedInvoices.length > 0 && selectedInvoices.length < invoices.length"
                            class="w-5 h-5 rounded bg-secondary-700 border-secondary-600 text-primary-500 focus:ring-primary-500 focus:ring-offset-secondary-800">
                        <span class="text-sm text-secondary-400">
                            <span x-text="selectedInvoices.length"></span> / <span x-text="invoices.length"></span> sélectionnées
                        </span>
                    </div>
                    <button
                        @click="clearSelection()"
                        x-show="selectedInvoices.length > 0"
                        class="text-sm text-primary-400 hover:text-primary-300">
                        Tout désélectionner
                    </button>
                </div>

                <!-- Batch Actions -->
                <div class="flex flex-wrap gap-3" x-show="selectedInvoices.length > 0" x-cloak>
                    <!-- Send Batch -->
                    <button
                        @click="showBatchModal('send')"
                        :disabled="!canSendBatch()"
                        class="px-4 py-2 bg-primary-500 hover:bg-primary-600 disabled:bg-secondary-700 disabled:text-secondary-500 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Envoyer (<span x-text="selectedInvoices.length"></span>)
                    </button>

                    <!-- Mark as Paid Batch -->
                    <button
                        @click="showBatchModal('paid')"
                        class="px-4 py-2 bg-success-500 hover:bg-success-600 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Marquer payées
                    </button>

                    <!-- Send Reminder Batch -->
                    <button
                        @click="showBatchModal('reminder')"
                        :disabled="!canSendReminders()"
                        class="px-4 py-2 bg-warning-500 hover:bg-warning-600 disabled:bg-secondary-700 disabled:text-secondary-500 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Envoyer relances
                    </button>

                    <!-- Export Batch -->
                    <button
                        @click="showBatchModal('export')"
                        class="px-4 py-2 bg-secondary-600 hover:bg-secondary-500 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Exporter
                    </button>

                    <!-- Delete Batch -->
                    <button
                        @click="showBatchModal('delete')"
                        :disabled="!canDeleteBatch()"
                        class="px-4 py-2 bg-danger-500/20 hover:bg-danger-500/30 disabled:bg-secondary-700 disabled:text-secondary-500 text-danger-400 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
            <div class="grid md:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium mb-2">Statut</label>
                    <select x-model="filters.status" @change="applyFilters()" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                        <option value="">Tous</option>
                        <option value="draft">Brouillon</option>
                        <option value="validated">Validé</option>
                        <option value="sent">Envoyé</option>
                        <option value="partial">Partiellement payé</option>
                        <option value="overdue">En retard</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium mb-2">Date début</label>
                    <input type="date" x-model="filters.dateFrom" @change="applyFilters()" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Date fin</label>
                    <input type="date" x-model="filters.dateTo" @change="applyFilters()" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                </div>

                <!-- Amount Filter -->
                <div>
                    <label class="block text-sm font-medium mb-2">Montant min (€)</label>
                    <input type="number" x-model.number="filters.minAmount" @change="applyFilters()" step="0.01" class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white">
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-secondary-800 rounded-xl border border-secondary-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-secondary-700">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    @change="toggleSelectAll($event.target.checked)"
                                    :checked="selectedInvoices.length === filteredInvoices.length && filteredInvoices.length > 0"
                                    class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Numéro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Échéance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Dû</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-secondary-400 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-secondary-700">
                        <template x-for="invoice in filteredInvoices" :key="invoice.id">
                            <tr class="hover:bg-secondary-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        :value="invoice.id"
                                        @change="toggleInvoice(invoice.id, $event.target.checked)"
                                        :checked="selectedInvoices.includes(invoice.id)"
                                        class="w-5 h-5 rounded bg-secondary-600 border-secondary-500 text-primary-500">
                                </td>
                                <td class="px-6 py-4">
                                    <a :href="`/invoices/${invoice.id}`" class="text-primary-400 hover:text-primary-300 font-medium" x-text="invoice.number"></a>
                                </td>
                                <td class="px-6 py-4 text-sm" x-text="invoice.partner"></td>
                                <td class="px-6 py-4 text-sm text-secondary-400" x-text="invoice.date"></td>
                                <td class="px-6 py-4 text-sm">
                                    <span :class="{'text-danger-400': invoice.overdue, 'text-secondary-400': !invoice.overdue}" x-text="invoice.dueDate"></span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium" x-text="'€ ' + invoice.total.toFixed(2)"></td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <span :class="{'text-danger-400': invoice.due > 0, 'text-success-400': invoice.due === 0}" x-text="'€ ' + invoice.due.toFixed(2)"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full"
                                        :class="{
                                            'bg-secondary-600 text-secondary-300': invoice.status === 'draft',
                                            'bg-primary-500/20 text-primary-400': invoice.status === 'validated',
                                            'bg-warning-500/20 text-warning-400': invoice.status === 'sent',
                                            'bg-success-500/20 text-success-400': invoice.status === 'paid',
                                            'bg-danger-500/20 text-danger-400': invoice.status === 'overdue'
                                        }"
                                        x-text="invoice.statusLabel">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredInvoices.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-secondary-400">
                                Aucune facture trouvée
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid md:grid-cols-4 gap-6">
            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-400">Factures sélectionnées</p>
                        <p class="text-2xl font-bold text-white mt-1" x-text="selectedInvoices.length"></p>
                    </div>
                    <div class="w-12 h-12 bg-primary-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-400">Montant total</p>
                        <p class="text-2xl font-bold text-white mt-1">€ <span x-text="selectedTotal.toFixed(2)"></span></p>
                    </div>
                    <div class="w-12 h-12 bg-success-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-400">Montant dû</p>
                        <p class="text-2xl font-bold text-warning-400 mt-1">€ <span x-text="selectedDue.toFixed(2)"></span></p>
                    </div>
                    <div class="w-12 h-12 bg-warning-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-secondary-800 rounded-xl border border-secondary-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-secondary-400">En retard</p>
                        <p class="text-2xl font-bold text-danger-400 mt-1" x-text="selectedOverdueCount"></p>
                    </div>
                    <div class="w-12 h-12 bg-danger-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Action Modal -->
        <div
            x-show="modal.show"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape="modal.show = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="modal.show = false"></div>

                <div class="relative bg-secondary-800 rounded-xl border border-secondary-700 max-w-2xl w-full p-6">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold" x-text="modal.title"></h3>
                        <button @click="modal.show = false" class="text-secondary-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="space-y-4">
                        <p class="text-secondary-400" x-text="modal.message"></p>

                        <!-- Export Options -->
                        <div x-show="modal.action === 'export'" class="space-y-3">
                            <label class="flex items-center gap-3 p-3 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer">
                                <input type="radio" x-model="modal.exportFormat" value="pdf" class="text-primary-500">
                                <div>
                                    <p class="font-medium">PDF</p>
                                    <p class="text-sm text-secondary-400">Fichier PDF avec toutes les factures</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer">
                                <input type="radio" x-model="modal.exportFormat" value="excel" class="text-primary-500">
                                <div>
                                    <p class="font-medium">Excel</p>
                                    <p class="text-sm text-secondary-400">Tableau Excel avec détails</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-lg bg-secondary-700/50 hover:bg-secondary-700 cursor-pointer">
                                <input type="radio" x-model="modal.exportFormat" value="zip" class="text-primary-500">
                                <div>
                                    <p class="font-medium">ZIP (PDFs individuels)</p>
                                    <p class="text-sm text-secondary-400">Archive avec chaque facture en PDF</p>
                                </div>
                            </label>
                        </div>

                        <!-- Reminder Options -->
                        <div x-show="modal.action === 'reminder'" class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium mb-2">Message de relance</label>
                                <textarea
                                    x-model="modal.reminderMessage"
                                    rows="4"
                                    class="w-full bg-secondary-700 border-secondary-600 rounded-lg text-white"
                                    placeholder="Votre message personnalisé..."></textarea>
                            </div>
                            <label class="flex items-center gap-3">
                                <input type="checkbox" x-model="modal.attachPdf" class="rounded text-primary-500">
                                <span class="text-sm">Joindre le PDF de la facture</span>
                            </label>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-secondary-700">
                        <button @click="modal.show = false" class="px-6 py-3 bg-secondary-700 hover:bg-secondary-600 rounded-lg font-medium transition-colors">
                            Annuler
                        </button>
                        <button
                            @click="executeBatchAction()"
                            class="px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2"
                            :class="{
                                'bg-primary-500 hover:bg-primary-600': modal.action === 'send',
                                'bg-success-500 hover:bg-success-600': modal.action === 'paid',
                                'bg-warning-500 hover:bg-warning-600': modal.action === 'reminder',
                                'bg-secondary-600 hover:bg-secondary-500': modal.action === 'export',
                                'bg-danger-500 hover:bg-danger-600': modal.action === 'delete'
                            }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="modal.confirmText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function batchOperations() {
            return {
                invoices: @json($invoices ?? []),
                selectedInvoices: [],
                filters: {
                    status: '',
                    dateFrom: '',
                    dateTo: '',
                    minAmount: ''
                },
                modal: {
                    show: false,
                    action: '',
                    title: '',
                    message: '',
                    confirmText: '',
                    exportFormat: 'pdf',
                    reminderMessage: '',
                    attachPdf: true
                },

                get filteredInvoices() {
                    return this.invoices.filter(inv => {
                        if (this.filters.status && inv.status !== this.filters.status) return false;
                        if (this.filters.dateFrom && inv.date < this.filters.dateFrom) return false;
                        if (this.filters.dateTo && inv.date > this.filters.dateTo) return false;
                        if (this.filters.minAmount && inv.total < this.filters.minAmount) return false;
                        return true;
                    });
                },

                get selectedTotal() {
                    return this.selectedInvoices.reduce((sum, id) => {
                        const inv = this.invoices.find(i => i.id === id);
                        return sum + (inv?.total || 0);
                    }, 0);
                },

                get selectedDue() {
                    return this.selectedInvoices.reduce((sum, id) => {
                        const inv = this.invoices.find(i => i.id === id);
                        return sum + (inv?.due || 0);
                    }, 0);
                },

                get selectedOverdueCount() {
                    return this.selectedInvoices.filter(id => {
                        const inv = this.invoices.find(i => i.id === id);
                        return inv?.overdue;
                    }).length;
                },

                toggleSelectAll(checked) {
                    this.selectedInvoices = checked ? this.filteredInvoices.map(i => i.id) : [];
                },

                toggleInvoice(id, checked) {
                    if (checked) {
                        this.selectedInvoices.push(id);
                    } else {
                        this.selectedInvoices = this.selectedInvoices.filter(i => i !== id);
                    }
                },

                clearSelection() {
                    this.selectedInvoices = [];
                },

                applyFilters() {
                    // Filters are reactive
                },

                canSendBatch() {
                    return this.selectedInvoices.some(id => {
                        const inv = this.invoices.find(i => i.id === id);
                        return inv && inv.status === 'validated';
                    });
                },

                canSendReminders() {
                    return this.selectedInvoices.some(id => {
                        const inv = this.invoices.find(i => i.id === id);
                        return inv && inv.due > 0;
                    });
                },

                canDeleteBatch() {
                    return this.selectedInvoices.every(id => {
                        const inv = this.invoices.find(i => i.id === id);
                        return inv && inv.status === 'draft';
                    });
                },

                showBatchModal(action) {
                    const configs = {
                        send: {
                            title: 'Envoyer les factures',
                            message: `Vous êtes sur le point d'envoyer ${this.selectedInvoices.length} facture(s). Les factures seront envoyées par email aux clients.`,
                            confirmText: 'Envoyer'
                        },
                        paid: {
                            title: 'Marquer comme payées',
                            message: `Marquer ${this.selectedInvoices.length} facture(s) comme entièrement payées ?`,
                            confirmText: 'Confirmer'
                        },
                        reminder: {
                            title: 'Envoyer des relances',
                            message: `Envoyer une relance pour ${this.selectedInvoices.length} facture(s) impayée(s) ?`,
                            confirmText: 'Envoyer relances'
                        },
                        export: {
                            title: 'Exporter les factures',
                            message: `Exporter ${this.selectedInvoices.length} facture(s) sélectionnée(s).`,
                            confirmText: 'Exporter'
                        },
                        delete: {
                            title: 'Supprimer les factures',
                            message: `⚠️ Attention : ${this.selectedInvoices.length} facture(s) brouillon vont être définitivement supprimées. Cette action est irréversible.`,
                            confirmText: 'Supprimer'
                        }
                    };

                    this.modal = {
                        ...this.modal,
                        ...configs[action],
                        show: true,
                        action
                    };
                },

                executeBatchAction() {
                    // Submit form based on action
                    const form = document.createElement('form');
                    form.method = 'POST';

                    const routes = {
                        send: '/invoices/batch/send',
                        paid: '/invoices/batch/mark-paid',
                        reminder: '/invoices/batch/send-reminders',
                        export: '/invoices/batch/export',
                        delete: '/invoices/batch/delete'
                    };

                    form.action = routes[this.modal.action];

                    // CSRF token
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    // Invoice IDs
                    this.selectedInvoices.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'invoice_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    // Additional params
                    if (this.modal.action === 'export') {
                        const format = document.createElement('input');
                        format.type = 'hidden';
                        format.name = 'format';
                        format.value = this.modal.exportFormat;
                        form.appendChild(format);
                    }

                    if (this.modal.action === 'reminder') {
                        const message = document.createElement('input');
                        message.type = 'hidden';
                        message.name = 'message';
                        message.value = this.modal.reminderMessage;
                        form.appendChild(message);

                        const attach = document.createElement('input');
                        attach.type = 'hidden';
                        attach.name = 'attach_pdf';
                        attach.value = this.modal.attachPdf ? '1' : '0';
                        form.appendChild(attach);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
