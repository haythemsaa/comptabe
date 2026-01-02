<div
    x-data="commandPalette()"
    @open-command-palette.window="open()"
    @close-command-palette.window="close()"
    @keydown.escape.window="close()"
    x-cloak
>
    <!-- Backdrop -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        @click="close()"
    ></div>

    <!-- Command Palette -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-x-4 top-[10%] md:inset-x-auto md:left-1/2 md:-translate-x-1/2 z-50 md:w-full md:max-w-2xl"
        role="dialog"
        aria-modal="true"
        aria-label="Palette de commandes"
    >
        <div class="bg-white dark:bg-secondary-800 rounded-2xl shadow-2xl overflow-hidden border border-secondary-200 dark:border-secondary-700">
            <!-- Search Input -->
            <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-secondary-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    x-ref="searchInput"
                    x-model="query"
                    @input="search()"
                    @keydown.down.prevent="navigateDown()"
                    @keydown.up.prevent="navigateUp()"
                    @keydown.enter.prevent="executeSelected()"
                    type="text"
                    class="w-full pl-12 pr-4 py-4 text-lg bg-transparent border-0 border-b border-secondary-200 dark:border-secondary-700 focus:ring-0 focus:border-primary-500 text-secondary-900 dark:text-white placeholder-secondary-400"
                    placeholder="Rechercher une commande, page ou action..."
                >
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                    <kbd class="hidden sm:inline-block px-2 py-1 text-xs font-medium bg-secondary-100 dark:bg-secondary-700 text-secondary-500 rounded">Esc</kbd>
                </div>
            </div>

            <!-- Results -->
            <div class="max-h-96 overflow-y-auto">
                <!-- Quick Actions -->
                <template x-if="!query && quickActions.length">
                    <div class="p-2">
                        <div class="px-3 py-2 text-xs font-semibold text-secondary-500 uppercase tracking-wider">Actions rapides</div>
                        <template x-for="(action, index) in quickActions" :key="action.id">
                            <button
                                @click="execute(action)"
                                @mouseenter="selectedIndex = index"
                                :class="{'bg-primary-50 dark:bg-primary-900/20': selectedIndex === index}"
                                class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-700/50 transition-colors text-left"
                            >
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center"
                                    :class="action.iconBg || 'bg-secondary-100 dark:bg-secondary-700'"
                                >
                                    <span x-html="action.icon" class="w-5 h-5" :class="action.iconColor || 'text-secondary-600 dark:text-secondary-400'"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-secondary-900 dark:text-white" x-text="action.title"></div>
                                    <div class="text-sm text-secondary-500 truncate" x-text="action.description"></div>
                                </div>
                                <template x-if="action.shortcut">
                                    <kbd class="px-2 py-1 text-xs bg-secondary-100 dark:bg-secondary-700 text-secondary-500 rounded" x-text="action.shortcut"></kbd>
                                </template>
                            </button>
                        </template>
                    </div>
                </template>

                <!-- Search Results -->
                <template x-if="query && categorizedResults.length">
                    <div class="p-2">
                        <template x-for="(group, groupIndex) in groupedResults" :key="groupIndex">
                            <div class="mb-2">
                                <div class="px-3 py-2 text-xs font-semibold text-secondary-500 uppercase tracking-wider" x-text="group.category"></div>
                                <template x-for="(result, index) in group.items" :key="result.id">
                                    <button
                                        @click="execute(result)"
                                        @mouseenter="selectedIndex = getGlobalIndex(groupIndex, index)"
                                        :class="{'bg-primary-50 dark:bg-primary-900/20': selectedIndex === getGlobalIndex(groupIndex, index)}"
                                        class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-700/50 transition-colors text-left"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                                            :class="result.iconBg || 'bg-secondary-100 dark:bg-secondary-700'"
                                        >
                                            <span x-html="result.icon" class="w-5 h-5" :class="result.iconColor || 'text-secondary-600 dark:text-secondary-400'"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-secondary-900 dark:text-white" x-html="highlightMatch(result.title, query)"></div>
                                            <div class="text-sm text-secondary-500 truncate" x-text="result.subtitle || result.description"></div>
                                        </div>
                                        <template x-if="result.description && result.subtitle">
                                            <span class="text-xs text-secondary-400 truncate max-w-[120px]" x-text="result.description"></span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- No Results -->
                <template x-if="query && !categorizedResults.length && !isLoading">
                    <div class="p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-secondary-300 dark:text-secondary-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-secondary-500 dark:text-secondary-400">Aucun résultat pour "<span x-text="query" class="font-medium"></span>"</p>
                        <p class="text-sm text-secondary-400 mt-1">Essayez une autre recherche</p>
                    </div>
                </template>

                <!-- Loading -->
                <template x-if="isLoading">
                    <div class="p-8 text-center">
                        <svg class="animate-spin w-8 h-8 mx-auto text-primary-500 mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-secondary-500">Recherche en cours...</p>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 bg-secondary-50 dark:bg-secondary-900 border-t border-secondary-200 dark:border-secondary-700 flex items-center justify-between text-xs text-secondary-500">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-secondary-200 dark:bg-secondary-700 rounded">↑</kbd>
                        <kbd class="px-1.5 py-0.5 bg-secondary-200 dark:bg-secondary-700 rounded">↓</kbd>
                        naviguer
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-secondary-200 dark:bg-secondary-700 rounded">↵</kbd>
                        ouvrir
                    </span>
                </div>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-secondary-200 dark:bg-secondary-700 rounded">?</kbd>
                    raccourcis
                </span>
            </div>
        </div>
    </div>
</div>

<script>
function commandPalette() {
    return {
        isOpen: false,
        query: '',
        results: [],
        categorizedResults: [],
        selectedIndex: 0,
        isLoading: false,
        searchTimeout: null,
        recentSearches: [],

        init() {
            // Load recent searches from localStorage
            const stored = localStorage.getItem('recentSearches');
            if (stored) {
                this.recentSearches = JSON.parse(stored);
            }

            // Global keyboard shortcut (Cmd+K / Ctrl+K)
            document.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.open();
                }
            });
        },

        quickActions: [
            {
                id: 'new-invoice',
                title: 'Nouvelle facture',
                description: 'Créer une nouvelle facture de vente',
                icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                iconBg: 'bg-primary-100 dark:bg-primary-900/30',
                iconColor: 'text-primary-600',
                shortcut: 'Ctrl+N',
                url: '/invoices/create'
            },
            {
                id: 'new-partner',
                title: 'Nouveau client',
                description: 'Ajouter un nouveau client ou fournisseur',
                icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>',
                iconBg: 'bg-success-100 dark:bg-success-900/30',
                iconColor: 'text-success-600',
                url: '/partners/create'
            },
            {
                id: 'new-quote',
                title: 'Nouveau devis',
                description: 'Créer un nouveau devis',
                icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>',
                iconBg: 'bg-info-100 dark:bg-info-900/30',
                iconColor: 'text-info-600',
                url: '/quotes/create'
            },
            {
                id: 'bank-reconciliation',
                title: 'Réconciliation bancaire',
                description: 'Rapprocher les transactions bancaires',
                icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                iconBg: 'bg-warning-100 dark:bg-warning-900/30',
                iconColor: 'text-warning-600',
                url: '/bank/reconciliation'
            }
        ],

        open() {
            this.isOpen = true;
            this.query = '';
            this.results = [];
            this.categorizedResults = [];
            this.selectedIndex = 0;
            this.$nextTick(() => this.$refs.searchInput.focus());
        },

        close() {
            this.isOpen = false;
            this.query = '';
            this.categorizedResults = [];
        },

        async search() {
            if (!this.query.trim()) {
                this.categorizedResults = [];
                this.selectedIndex = 0;
                this.isLoading = false;
                return;
            }

            // Debounce search
            clearTimeout(this.searchTimeout);
            this.isLoading = true;

            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/search?q=${encodeURIComponent(this.query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Search failed');
                    }

                    const data = await response.json();

                    // Transform API results to component format
                    this.categorizedResults = data.results.map(category => ({
                        category: category.category,
                        items: category.items.map(item => ({
                            ...item,
                            icon: this.getIconForType(item.type),
                            iconBg: this.getIconBgForType(item.type),
                            iconColor: this.getIconColorForType(item.type)
                        }))
                    }));

                    this.selectedIndex = 0;
                    this.isLoading = false;
                } catch (error) {
                    console.error('Search error:', error);
                    this.categorizedResults = [];
                    this.isLoading = false;
                }
            }, 300); // 300ms debounce
        },

        getIconForType(type) {
            const icons = {
                'invoice': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'quote': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>',
                'partner': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                'product': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                'employee': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'action': '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
            };
            return icons[type] || icons['action'];
        },

        getIconBgForType(type) {
            const colors = {
                'invoice': 'bg-primary-100 dark:bg-primary-900/30',
                'quote': 'bg-info-100 dark:bg-info-900/30',
                'partner': 'bg-success-100 dark:bg-success-900/30',
                'product': 'bg-warning-100 dark:bg-warning-900/30',
                'employee': 'bg-purple-100 dark:bg-purple-900/30',
                'action': 'bg-secondary-100 dark:bg-secondary-700'
            };
            return colors[type] || colors['action'];
        },

        getIconColorForType(type) {
            const colors = {
                'invoice': 'text-primary-600 dark:text-primary-400',
                'quote': 'text-info-600 dark:text-info-400',
                'partner': 'text-success-600 dark:text-success-400',
                'product': 'text-warning-600 dark:text-warning-400',
                'employee': 'text-purple-600 dark:text-purple-400',
                'action': 'text-secondary-600 dark:text-secondary-400'
            };
            return colors[type] || colors['action'];
        },

        get groupedResults() {
            if (!this.query) {
                return [{
                    category: 'Actions rapides',
                    items: this.quickActions
                }];
            }
            return this.categorizedResults;
        },

        get totalResults() {
            return this.groupedResults.reduce((sum, group) => sum + group.items.length, 0);
        },

        getGlobalIndex(groupIndex, itemIndex) {
            let index = 0;
            for (let i = 0; i < groupIndex; i++) {
                index += this.groupedResults[i].items.length;
            }
            return index + itemIndex;
        },

        navigateDown() {
            if (this.totalResults === 0) return;
            this.selectedIndex = (this.selectedIndex + 1) % this.totalResults;
        },

        navigateUp() {
            if (this.totalResults === 0) return;
            this.selectedIndex = (this.selectedIndex - 1 + this.totalResults) % this.totalResults;
        },

        executeSelected() {
            let currentIndex = 0;
            for (const group of this.groupedResults) {
                for (const item of group.items) {
                    if (currentIndex === this.selectedIndex) {
                        this.execute(item);
                        return;
                    }
                    currentIndex++;
                }
            }
        },

        execute(item) {
            // Save to recent searches
            if (this.query && item.type !== 'action') {
                this.addRecentSearch({
                    query: this.query,
                    result: item.title,
                    url: item.url,
                    type: item.type,
                    timestamp: Date.now()
                });
            }

            this.close();

            if (item.url) {
                window.location.href = item.url;
            }
        },

        addRecentSearch(search) {
            // Remove duplicate
            this.recentSearches = this.recentSearches.filter(s => s.url !== search.url);

            // Add to front
            this.recentSearches.unshift(search);

            // Keep only last 10
            this.recentSearches = this.recentSearches.slice(0, 10);

            // Save to localStorage
            localStorage.setItem('recentSearches', JSON.stringify(this.recentSearches));
        },

        highlightMatch(text, query) {
            if (!query) return text;
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 rounded px-0.5">$1</mark>');
        }
    };
}
</script>
