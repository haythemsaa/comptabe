<div x-data="onboardingSurvey()"
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-secondary-900/75 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-secondary-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">

            <!-- Progress Indicator -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-secondary-200 dark:bg-secondary-700">
                <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 transition-all duration-300"
                     :style="`width: ${((currentStep + 1) / 3) * 100}%`"></div>
            </div>

            <!-- Header -->
            <div class="px-6 pt-8 pb-4 text-center bg-gradient-to-b from-primary-50 to-white dark:from-secondary-800 dark:to-secondary-800">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 mb-4">
                    <span class="text-3xl">👋</span>
                </div>
                <h3 class="text-2xl font-bold text-secondary-900 dark:text-secondary-100 mb-2">
                    Bienvenue sur ComptaBE !
                </h3>
                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                    Répondez à 3 questions rapides pour personnaliser votre expérience
                </p>
            </div>

            <!-- Content -->
            <div class="px-6 py-6">
                <!-- Question 1: Role -->
                <div x-show="currentStep === 0"
                     x-transition
                     class="space-y-4">
                    <div class="text-center mb-6">
                        <h4 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">
                            Quel est votre profil ?
                        </h4>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                            Nous adapterons l'interface à vos besoins
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button @click="selectRole('freelance')"
                                type="button"
                                :class="formData.role === 'freelance'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="relative flex flex-col items-center gap-3 p-4 rounded-xl border-2 transition-all">
                            <span class="text-3xl">👨‍💼</span>
                            <div class="text-center">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Indépendant</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Freelance, consultant</div>
                            </div>
                        </button>

                        <button @click="selectRole('tpe')"
                                type="button"
                                :class="formData.role === 'tpe'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="relative flex flex-col items-center gap-3 p-4 rounded-xl border-2 transition-all">
                            <span class="text-3xl">🏪</span>
                            <div class="text-center">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">TPE</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Très petite entreprise</div>
                            </div>
                        </button>

                        <button @click="selectRole('pme')"
                                type="button"
                                :class="formData.role === 'pme'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="relative flex flex-col items-center gap-3 p-4 rounded-xl border-2 transition-all">
                            <span class="text-3xl">🏢</span>
                            <div class="text-center">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">PME</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Petite/moyenne entreprise</div>
                            </div>
                        </button>

                        <button @click="selectRole('comptable')"
                                type="button"
                                :class="formData.role === 'comptable'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="relative flex flex-col items-center gap-3 p-4 rounded-xl border-2 transition-all">
                            <span class="text-3xl">🧮</span>
                            <div class="text-center">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Expert-comptable</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Fiduciaire, cabinet</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Question 2: Goals -->
                <div x-show="currentStep === 1"
                     x-transition
                     class="space-y-4">
                    <div class="text-center mb-6">
                        <h4 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">
                            Quels sont vos objectifs ?
                        </h4>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                            Sélectionnez un ou plusieurs objectifs (multiple choix)
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-primary-300 dark:hover:border-primary-500"
                               :class="formData.goals.includes('facturation')
                                   ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                   : 'border-secondary-300 dark:border-secondary-600'">
                            <input type="checkbox"
                                   value="facturation"
                                   x-model="formData.goals"
                                   class="sr-only">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                 :class="formData.goals.includes('facturation')
                                     ? 'bg-primary-500 text-white'
                                     : 'bg-secondary-200 dark:bg-secondary-700'">
                                <span class="text-xl">📄</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Facturation</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Gérer mes factures et devis</div>
                            </div>
                            <svg x-show="formData.goals.includes('facturation')" class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </label>

                        <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-primary-300 dark:hover:border-primary-500"
                               :class="formData.goals.includes('suivi_depenses')
                                   ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                   : 'border-secondary-300 dark:border-secondary-600'">
                            <input type="checkbox"
                                   value="suivi_depenses"
                                   x-model="formData.goals"
                                   class="sr-only">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                 :class="formData.goals.includes('suivi_depenses')
                                     ? 'bg-primary-500 text-white'
                                     : 'bg-secondary-200 dark:bg-secondary-700'">
                                <span class="text-xl">💰</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Suivi des dépenses</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Contrôler mes dépenses professionnelles</div>
                            </div>
                            <svg x-show="formData.goals.includes('suivi_depenses')" class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </label>

                        <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-primary-300 dark:hover:border-primary-500"
                               :class="formData.goals.includes('conformite_fiscale')
                                   ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                   : 'border-secondary-300 dark:border-secondary-600'">
                            <input type="checkbox"
                                   value="conformite_fiscale"
                                   x-model="formData.goals"
                                   class="sr-only">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                 :class="formData.goals.includes('conformite_fiscale')
                                     ? 'bg-primary-500 text-white'
                                     : 'bg-secondary-200 dark:bg-secondary-700'">
                                <span class="text-xl">✅</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Conformité fiscale</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">TVA, déclarations, taxes</div>
                            </div>
                            <svg x-show="formData.goals.includes('conformite_fiscale')" class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </label>

                        <label class="relative flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-primary-300 dark:hover:border-primary-500"
                               :class="formData.goals.includes('reporting')
                                   ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                   : 'border-secondary-300 dark:border-secondary-600'">
                            <input type="checkbox"
                                   value="reporting"
                                   x-model="formData.goals"
                                   class="sr-only">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full"
                                 :class="formData.goals.includes('reporting')
                                     ? 'bg-primary-500 text-white'
                                     : 'bg-secondary-200 dark:bg-secondary-700'">
                                <span class="text-xl">📊</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Reporting financier</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Tableaux de bord, analyses</div>
                            </div>
                            <svg x-show="formData.goals.includes('reporting')" class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </label>
                    </div>
                </div>

                <!-- Question 3: Experience Level -->
                <div x-show="currentStep === 2"
                     x-transition
                     class="space-y-4">
                    <div class="text-center mb-6">
                        <h4 class="text-lg font-semibold text-secondary-900 dark:text-secondary-100 mb-2">
                            Quel est votre niveau en comptabilité ?
                        </h4>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                            Nous ajusterons le niveau d'assistance et les explications
                        </p>
                    </div>

                    <div class="space-y-3">
                        <button @click="selectExperience('debutant')"
                                type="button"
                                :class="formData.experience_level === 'debutant'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30">
                                <span class="text-2xl">🌱</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Débutant</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Je découvre la comptabilité</div>
                            </div>
                        </button>

                        <button @click="selectExperience('intermediaire')"
                                type="button"
                                :class="formData.experience_level === 'intermediaire'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30">
                                <span class="text-2xl">📚</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Intermédiaire</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">J'ai quelques connaissances de base</div>
                            </div>
                        </button>

                        <button @click="selectExperience('expert')"
                                type="button"
                                :class="formData.experience_level === 'expert'
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 ring-2 ring-primary-500'
                                    : 'border-secondary-300 dark:border-secondary-600 hover:border-primary-300 dark:hover:border-primary-500'"
                                class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all text-left">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900/30">
                                <span class="text-2xl">🎓</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-secondary-900 dark:text-secondary-100">Expert</div>
                                <div class="text-xs text-secondary-600 dark:text-secondary-400">Je maîtrise la comptabilité</div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-secondary-50 dark:bg-secondary-800/50 flex items-center justify-between">
                <button @click="skip()"
                        type="button"
                        class="text-sm text-secondary-600 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-secondary-200 transition">
                    Passer
                </button>

                <div class="flex gap-2">
                    <button @click="prevStep()"
                            x-show="currentStep > 0"
                            type="button"
                            class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Précédent
                    </button>

                    <button @click="nextStep()"
                            x-show="currentStep < 2"
                            :disabled="!canProceed()"
                            type="button"
                            class="btn btn-primary">
                        Suivant
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <button @click="submit()"
                            x-show="currentStep === 2"
                            :disabled="!canProceed()"
                            type="button"
                            class="btn btn-primary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Terminer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('onboardingSurvey', () => ({
        showModal: false,
        currentStep: 0,
        formData: {
            role: '',
            goals: [],
            experience_level: '',
        },

        async init() {
            // Check if survey already completed
            const response = await axios.get('/onboarding/status');
            const status = response.data;

            // Show survey if not completed
            if (!status.tour_completed && !status.skipped && status.role === null) {
                setTimeout(() => {
                    this.showModal = true;
                }, 1000); // Delay 1 second to not overwhelm user
            }
        },

        selectRole(role) {
            this.formData.role = role;
        },

        selectExperience(level) {
            this.formData.experience_level = level;
        },

        canProceed() {
            switch (this.currentStep) {
                case 0:
                    return this.formData.role !== '';
                case 1:
                    return this.formData.goals.length > 0;
                case 2:
                    return this.formData.experience_level !== '';
                default:
                    return false;
            }
        },

        nextStep() {
            if (this.canProceed() && this.currentStep < 2) {
                this.currentStep++;
            }
        },

        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
            }
        },

        async submit() {
            if (!this.canProceed()) return;

            try {
                const response = await axios.post('/onboarding/survey', this.formData);

                this.showModal = false;
                window.showToast?.(response.data.message, 'success');

                // Reload page to apply personalization
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } catch (error) {
                console.error('Survey submission error:', error);
                window.showToast?.('Erreur lors de l\'enregistrement', 'error');
            }
        },

        async skip() {
            if (!confirm('Voulez-vous vraiment passer le questionnaire ? Vous pourrez le remplir plus tard dans les paramètres.')) {
                return;
            }

            try {
                await axios.post('/onboarding/skip');
                this.showModal = false;
                window.showToast?.('Questionnaire ignoré', 'info');
            } catch (error) {
                window.showToast?.('Erreur', 'error');
            }
        },
    }));
});
</script>
