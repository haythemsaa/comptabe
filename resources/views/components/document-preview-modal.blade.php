{{-- Document Preview Modal --}}
<div
    x-data="documentPreviewModal()"
    x-show="isOpen"
    x-cloak
    @open-document-preview.window="open($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-50 overflow-hidden"
    aria-labelledby="document-preview-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="absolute inset-0 bg-black/70"
    ></div>

    {{-- Modal Panel --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="relative flex flex-col h-full max-h-[95vh] w-full max-w-6xl mx-auto my-4 bg-white dark:bg-dark-400 rounded-2xl shadow-2xl overflow-hidden"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
            <div class="flex items-center gap-4">
                {{-- Document type badge --}}
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium"
                    :class="{
                        'bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300': document.type === 'invoice',
                        'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300': document.type === 'quote',
                        'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300': document.type === 'credit-note',
                        'bg-secondary-100 text-secondary-700 dark:bg-secondary-700 dark:text-secondary-300': !document.type
                    }"
                    x-text="getTypeLabel()"
                ></span>

                {{-- Document title --}}
                <h2 id="document-preview-title" class="text-lg font-semibold text-secondary-900 dark:text-white" x-text="document.title || 'Document'"></h2>

                {{-- Status badge --}}
                <span
                    x-show="document.status"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :class="getStatusClass()"
                    x-text="document.status"
                ></span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Actions --}}
                <template x-if="document.editUrl">
                    <a
                        :href="document.editUrl"
                        class="btn btn-secondary btn-sm"
                        title="Modifier"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="hidden sm:inline ml-1">Modifier</span>
                    </a>
                </template>

                <template x-if="document.downloadUrl">
                    <a
                        :href="document.downloadUrl"
                        class="btn btn-secondary btn-sm"
                        title="Télécharger PDF"
                        download
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="hidden sm:inline ml-1">PDF</span>
                    </a>
                </template>

                <template x-if="document.printUrl">
                    <button
                        @click="print()"
                        class="btn btn-secondary btn-sm"
                        title="Imprimer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </button>
                </template>

                {{-- Close button --}}
                <button
                    @click="close()"
                    class="p-2 rounded-lg text-secondary-400 hover:text-secondary-600 hover:bg-secondary-100 dark:hover:bg-secondary-700 dark:hover:text-secondary-200 transition-colors"
                    title="Fermer (Échap)"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-hidden flex">
            {{-- Main preview area --}}
            <div class="flex-1 flex items-center justify-center bg-secondary-100 dark:bg-dark-500 overflow-auto p-4">
                {{-- Loading state --}}
                <div x-show="loading" class="flex flex-col items-center justify-center gap-4">
                    <svg class="animate-spin h-10 w-10 text-primary-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-secondary-500 dark:text-secondary-400">Chargement du document...</span>
                </div>

                {{-- PDF Preview --}}
                <template x-if="!loading && document.pdfUrl">
                    <iframe
                        :src="document.pdfUrl"
                        class="w-full h-full rounded-lg border border-secondary-200 dark:border-secondary-700 bg-white"
                        title="Aperçu du document"
                    ></iframe>
                </template>

                {{-- Invoice HTML Preview --}}
                <template x-if="!loading && !document.pdfUrl && document.html">
                    <div
                        class="w-full max-w-3xl bg-white rounded-lg shadow-lg p-8 overflow-auto"
                        x-html="document.html"
                    ></div>
                </template>

                {{-- No preview available --}}
                <template x-if="!loading && !document.pdfUrl && !document.html">
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-secondary-500 dark:text-secondary-400">Aperçu non disponible</p>
                        <template x-if="document.downloadUrl">
                            <a
                                :href="document.downloadUrl"
                                class="btn btn-primary mt-4"
                                download
                            >
                                Télécharger le document
                            </a>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Sidebar with document info --}}
            <div
                x-show="showSidebar && document.metadata"
                class="w-80 border-l border-secondary-200 dark:border-secondary-700 overflow-y-auto bg-white dark:bg-dark-400"
            >
                <div class="p-4 space-y-6">
                    {{-- Document Details --}}
                    <div>
                        <h3 class="text-sm font-medium text-secondary-900 dark:text-white mb-3">Détails</h3>
                        <dl class="space-y-2">
                            <template x-for="(value, key) in document.metadata" :key="key">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-secondary-500 dark:text-secondary-400" x-text="key"></dt>
                                    <dd class="text-secondary-900 dark:text-white font-medium" x-text="value"></dd>
                                </div>
                            </template>
                        </dl>
                    </div>

                    {{-- Lines Preview --}}
                    <template x-if="document.lines && document.lines.length > 0">
                        <div>
                            <h3 class="text-sm font-medium text-secondary-900 dark:text-white mb-3">Lignes</h3>
                            <div class="space-y-2">
                                <template x-for="(line, index) in document.lines" :key="index">
                                    <div class="p-2 bg-secondary-50 dark:bg-dark-500 rounded-lg">
                                        <div class="text-sm font-medium text-secondary-900 dark:text-white" x-text="line.description"></div>
                                        <div class="flex justify-between text-xs text-secondary-500 dark:text-secondary-400 mt-1">
                                            <span x-text="line.quantity + ' x ' + line.unit_price + ' €'"></span>
                                            <span class="font-medium" x-text="line.total + ' €'"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Totals --}}
                    <template x-if="document.totals">
                        <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                            <dl class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-secondary-500 dark:text-secondary-400">Sous-total HT</dt>
                                    <dd class="text-secondary-900 dark:text-white" x-text="document.totals.subtotal + ' €'"></dd>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <dt class="text-secondary-500 dark:text-secondary-400">TVA</dt>
                                    <dd class="text-secondary-900 dark:text-white" x-text="document.totals.vat + ' €'"></dd>
                                </div>
                                <div class="flex justify-between text-sm font-bold">
                                    <dt class="text-secondary-900 dark:text-white">Total TTC</dt>
                                    <dd class="text-primary-600 dark:text-primary-400" x-text="document.totals.total + ' €'"></dd>
                                </div>
                            </dl>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-3 border-t border-secondary-200 dark:border-secondary-700 bg-secondary-50 dark:bg-dark-500">
            <div class="flex items-center gap-4">
                {{-- Toggle sidebar --}}
                <button
                    @click="showSidebar = !showSidebar"
                    class="text-sm text-secondary-500 hover:text-secondary-700 dark:text-secondary-400 dark:hover:text-secondary-200 flex items-center gap-1"
                    x-show="document.metadata"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    <span x-text="showSidebar ? 'Masquer les détails' : 'Afficher les détails'"></span>
                </button>
            </div>

            <div class="text-xs text-secondary-400 dark:text-secondary-500">
                Appuyez sur <kbd class="kbd">Échap</kbd> pour fermer
            </div>
        </div>
    </div>
</div>

<script>
    function documentPreviewModal() {
        return {
            isOpen: false,
            loading: false,
            showSidebar: true,
            document: {
                type: null,
                title: null,
                status: null,
                pdfUrl: null,
                html: null,
                downloadUrl: null,
                editUrl: null,
                printUrl: null,
                metadata: null,
                lines: null,
                totals: null
            },

            async open(detail) {
                this.isOpen = true;
                this.loading = true;
                document.body.style.overflow = 'hidden';

                // If full document data provided
                if (detail.document) {
                    this.document = { ...this.document, ...detail.document };
                    this.loading = false;
                    return;
                }

                // If URL provided, fetch document data
                if (detail.url) {
                    try {
                        const response = await fetch(detail.url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.document = { ...this.document, ...data };
                        }
                    } catch (error) {
                        console.error('Failed to load document:', error);
                        window.showToast('Erreur lors du chargement du document', 'error');
                    }
                }

                // Set basic info if provided
                if (detail.type) this.document.type = detail.type;
                if (detail.title) this.document.title = detail.title;
                if (detail.pdfUrl) this.document.pdfUrl = detail.pdfUrl;

                this.loading = false;
            },

            close() {
                this.isOpen = false;
                document.body.style.overflow = '';

                // Reset after animation
                setTimeout(() => {
                    this.document = {
                        type: null,
                        title: null,
                        status: null,
                        pdfUrl: null,
                        html: null,
                        downloadUrl: null,
                        editUrl: null,
                        printUrl: null,
                        metadata: null,
                        lines: null,
                        totals: null
                    };
                }, 300);
            },

            print() {
                if (this.document.printUrl) {
                    window.open(this.document.printUrl, '_blank');
                } else if (this.document.pdfUrl) {
                    window.open(this.document.pdfUrl, '_blank');
                }
            },

            getTypeLabel() {
                const labels = {
                    'invoice': 'Facture',
                    'quote': 'Devis',
                    'credit-note': 'Note de crédit',
                    'receipt': 'Reçu'
                };
                return labels[this.document.type] || 'Document';
            },

            getStatusClass() {
                const statusClasses = {
                    'draft': 'bg-secondary-100 text-secondary-700 dark:bg-secondary-700 dark:text-secondary-300',
                    'sent': 'bg-info-100 text-info-700 dark:bg-info-900/50 dark:text-info-300',
                    'paid': 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-300',
                    'overdue': 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-300',
                    'partial': 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-300',
                    'cancelled': 'bg-secondary-100 text-secondary-500 dark:bg-secondary-800 dark:text-secondary-400'
                };
                return statusClasses[this.document.status] || statusClasses['draft'];
            }
        };
    }
</script>
