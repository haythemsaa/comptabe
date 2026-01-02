@props(['id' => 'keyboard-shortcuts-modal'])

<div
    x-data="{ open: false }"
    @keydown.window.prevent.?="open = true"
    @keydown.window.shift.?="open = true"
    @open-shortcuts.window="open = true"
    @keydown.escape.window="open = false"
    x-cloak
>
    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <!-- Modal Content -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="shortcuts-title"
    >
        <div
            class="bg-white dark:bg-secondary-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="shortcuts-title" class="text-lg font-semibold text-secondary-900 dark:text-white">Raccourcis clavier</h2>
                        <p class="text-sm text-secondary-500">Naviguez plus rapidement</p>
                    </div>
                </div>
                <button
                    @click="open = false"
                    class="p-2 text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-700 transition-colors"
                    aria-label="Fermer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Navigation -->
                    <div>
                        <h3 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase tracking-wider mb-3">Navigation</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Recherche rapide</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">K</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Tableau de bord</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">G</kbd>
                                    <span class="text-secondary-400">puis</span>
                                    <kbd class="kbd">D</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Factures</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">G</kbd>
                                    <span class="text-secondary-400">puis</span>
                                    <kbd class="kbd">I</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Clients</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">G</kbd>
                                    <span class="text-secondary-400">puis</span>
                                    <kbd class="kbd">C</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div>
                        <h3 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase tracking-wider mb-3">Actions</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Nouvelle facture</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">N</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Sauvegarder</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">S</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Imprimer</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">P</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Fermer modal</span>
                                <kbd class="kbd">Esc</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Selection -->
                    <div>
                        <h3 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase tracking-wider mb-3">Sélection</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Tout sélectionner</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">A</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Sélection multiple</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">Clic</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Plage de sélection</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Shift</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">Clic</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aide -->
                    <div>
                        <h3 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase tracking-wider mb-3">Aide</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Afficher les raccourcis</span>
                                <kbd class="kbd">?</kbd>
                            </div>
                            <div class="flex items-center justify-between py-2">
                                <span class="text-secondary-600 dark:text-secondary-400">Mode sombre</span>
                                <div class="flex items-center gap-1">
                                    <kbd class="kbd">Ctrl</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">Shift</kbd>
                                    <span class="text-secondary-400">+</span>
                                    <kbd class="kbd">L</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-900 border-t border-secondary-200 dark:border-secondary-700">
                <p class="text-sm text-secondary-500 dark:text-secondary-400 text-center">
                    Appuyez sur <kbd class="kbd kbd-sm">?</kbd> n'importe où pour afficher ce menu
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .kbd {
        @apply px-2 py-1 text-xs font-semibold bg-secondary-100 dark:bg-secondary-700 text-secondary-700 dark:text-secondary-300 rounded border border-secondary-300 dark:border-secondary-600 shadow-sm;
    }
    .kbd-sm {
        @apply px-1.5 py-0.5 text-[10px];
    }
</style>
