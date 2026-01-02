<div x-data="onboardingChecklist()"
     x-show="show && !dismissed"
     x-transition
     class="fixed bottom-6 left-6 w-96 bg-white dark:bg-secondary-800 rounded-xl shadow-2xl border border-secondary-200 dark:border-secondary-700 z-40">

    <!-- Header -->
    <div class="p-4 border-b border-secondary-200 dark:border-secondary-700 bg-gradient-to-r from-primary-500 to-primary-600 rounded-t-xl">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">🎯</span>
                    <h3 class="text-lg font-bold text-white">Commencez avec ComptaBE</h3>
                </div>
                <p class="text-sm text-primary-100">
                    <span x-text="completedCount"></span> sur <span x-text="totalCount"></span> étapes complétées
                </p>
            </div>
            <button @click="dismiss()"
                    class="text-white hover:text-primary-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Progress Bar -->
        <div class="mt-3">
            <div class="h-3 bg-white/20 rounded-full overflow-hidden backdrop-blur-sm">
                <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all duration-500 ease-out"
                     :style="`width: ${progressPercentage}%`"
                     x-transition></div>
            </div>
            <div class="flex items-center justify-between mt-1">
                <span class="text-xs text-primary-100" x-text="`${progressPercentage}%`"></span>
                <span class="text-xs text-primary-100" x-show="progressPercentage === 100">
                    🎉 Terminé !
                </span>
            </div>
        </div>
    </div>

    <!-- Steps List -->
    <div class="p-4 max-h-96 overflow-y-auto">
        <template x-for="step in steps" :key="step.key">
            <div class="mb-3 p-3 rounded-lg border transition-all duration-300"
                 :class="step.completed
                    ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700'
                    : 'bg-secondary-50 dark:bg-secondary-700/50 border-secondary-200 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'">

                <div class="flex items-start gap-3">
                    <!-- Icon/Checkbox -->
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center transition-all"
                         :class="step.completed
                            ? 'bg-green-500 text-white scale-110'
                            : 'bg-secondary-200 dark:bg-secondary-600 text-secondary-400'">
                        <svg x-show="step.completed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-show="!step.completed" x-text="step.icon" class="text-lg"></span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-semibold text-sm text-secondary-900 dark:text-secondary-100"
                                :class="step.completed && 'line-through opacity-75'"
                                x-text="step.title"></h4>
                            <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="step.completed
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                    : 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300'">
                                <span x-text="`+${step.points}`"></span>
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </span>
                        </div>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1"
                           x-text="step.description"></p>

                        <!-- Next step indicator -->
                        <div x-show="nextStep && nextStep.key === step.key && !step.completed"
                             class="mt-2 flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400">
                            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span>Étape suivante</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Completion Message -->
        <div x-show="progressPercentage === 100"
             x-transition
             class="text-center py-4">
            <div class="text-4xl mb-2">🎉</div>
            <h4 class="font-bold text-lg text-secondary-900 dark:text-secondary-100 mb-1">
                Félicitations !
            </h4>
            <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-3">
                Vous avez terminé votre onboarding
            </p>
            <button @click="dismiss()"
                    class="btn btn-primary btn-sm">
                Parfait, merci !
            </button>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="p-4 border-t border-secondary-200 dark:border-secondary-700 bg-secondary-50 dark:bg-secondary-800/50 rounded-b-xl">
        <div class="flex items-center justify-between text-xs">
            <button @click="collapsed = !collapsed"
                    class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Besoin d'aide ?</span>
            </button>
            <button @click="skip()"
                    class="text-secondary-400 dark:text-secondary-500 hover:text-secondary-600 dark:hover:text-secondary-300 transition">
                Ignorer
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('onboardingChecklist', () => ({
        show: false,
        dismissed: false,
        progressPercentage: 0,
        completedCount: 0,
        totalCount: 0,
        steps: [],
        nextStep: null,
        collapsed: false,

        async init() {
            // Check if user has dismissed the checklist
            const dismissed = localStorage.getItem('onboarding_checklist_dismissed');
            if (dismissed === 'true') {
                this.dismissed = true;
                return;
            }

            // Load onboarding progress
            await this.loadProgress();

            // Show checklist if onboarding not complete
            if (this.progressPercentage < 100) {
                this.show = true;
            }

            // Refresh progress every 30 seconds
            setInterval(() => this.loadProgress(), 30000);
        },

        async loadProgress() {
            try {
                const response = await axios.get('/onboarding/progress');
                const data = response.data;

                this.progressPercentage = data.progress_percentage;
                this.completedCount = data.completed_count;
                this.totalCount = data.total_count;
                this.steps = data.steps;
                this.nextStep = data.next_step;

                // Auto-dismiss when complete
                if (data.is_complete && !this.dismissed) {
                    setTimeout(() => {
                        this.show = true; // Show completion message
                    }, 1000);
                }
            } catch (error) {
                console.error('Error loading onboarding progress:', error);
            }
        },

        dismiss() {
            this.dismissed = true;
            localStorage.setItem('onboarding_checklist_dismissed', 'true');
        },

        async skip() {
            if (!confirm('Voulez-vous vraiment ignorer l\'onboarding ? Vous pourrez le reprendre plus tard depuis les paramètres.')) {
                return;
            }

            try {
                await axios.post('/onboarding/skip');
                this.dismissed = true;
                window.showToast?.('Onboarding ignoré', 'info');
            } catch (error) {
                window.showToast?.('Erreur lors de l\'opération', 'error');
            }
        },
    }));
});
</script>
