@props(['formId'])

<div x-data="{ showRestoreModal: false }"
     @draft-loaded.window="if ($event.detail.formId === '{{ $formId }}') showRestoreModal = true"
     @draft-restored.window="if ($event.detail.formId === '{{ $formId }}') showRestoreModal = false"
     @draft-cleared.window="if ($event.detail.formId === '{{ $formId }}') showRestoreModal = false"
     class="relative">

    <!-- Auto-save Status Indicator -->
    <div class="flex items-center gap-2 text-sm text-secondary-500 dark:text-secondary-400">
        <!-- Saving indicator -->
        <template x-if="isDirty && autoSaveEnabled">
            <span class="flex items-center gap-1 text-warning-500">
                <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Modifications non sauvegardées</span>
            </span>
        </template>

        <!-- Saved indicator -->
        <template x-if="!isDirty && lastSaved && autoSaveEnabled">
            <span class="flex items-center gap-1 text-success-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Brouillon sauvegardé <span x-text="formatLastSaved()"></span></span>
            </span>
        </template>

        <!-- Toggle auto-save -->
        <button type="button"
                @click="toggleAutoSave()"
                class="ml-2 p-1 rounded hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors"
                :title="autoSaveEnabled ? 'Désactiver l\'auto-save' : 'Activer l\'auto-save'">
            <svg x-show="autoSaveEnabled" class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <svg x-show="!autoSaveEnabled" x-cloak class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </button>

        <!-- Manual save button -->
        <button type="button"
                @click="saveDraft()"
                :disabled="!isDirty"
                class="ml-1 p-1 rounded hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                title="Sauvegarder le brouillon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
        </button>

        <!-- Clear draft button -->
        <button type="button"
                x-show="lastSaved"
                @click="clearDraft()"
                class="ml-1 p-1 rounded hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors text-secondary-400 hover:text-danger-500"
                title="Supprimer le brouillon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>

    <!-- Restore Draft Modal -->
    <div x-show="showRestoreModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.away="showRestoreModal = false"
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
                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                        Un brouillon sauvegardé <span x-text="formatLastSaved()"></span> a été trouvé.
                    </p>
                </div>
            </div>
            <p class="text-secondary-600 dark:text-secondary-300 mb-6">
                Voulez-vous restaurer vos modifications précédentes ?
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button"
                        @click="clearDraft(); showRestoreModal = false"
                        class="btn btn-secondary">
                    Non, ignorer
                </button>
                <button type="button"
                        @click="restoreDraft(); showRestoreModal = false"
                        class="btn btn-primary">
                    Oui, restaurer
                </button>
            </div>
        </div>
    </div>
</div>
