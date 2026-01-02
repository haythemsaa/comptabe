import Alpine from 'alpinejs';

Alpine.data('onboardingTour', () => ({
    active: false,
    currentStep: 0,
    steps: [],
    overlay: null,
    tooltip: null,

    async init() {
        // Load onboarding status
        const response = await axios.get('/onboarding/status');
        const status = response.data;

        // Show tour if not completed and not skipped
        if (!status.tour_completed && !status.skipped && status.show_tour) {
            this.loadTourSteps(status.role);
            this.startTour();
        }
    },

    loadTourSteps(role) {
        // Base tour steps for all users
        const baseTours = [
            {
                element: '[data-tour="dashboard"]',
                title: 'Tableau de bord',
                description: 'Votre vue d\'ensemble avec les KPIs essentiels et les alertes importantes.',
                position: 'bottom',
            },
            {
                element: '[data-tour="quick-actions"]',
                title: 'Actions rapides',
                description: 'Créez rapidement une facture, un devis ou un nouveau client.',
                position: 'bottom',
            },
            {
                element: '[data-tour="invoices"]',
                title: 'Factures',
                description: 'Gérez toutes vos factures de vente et d\'achat. Créez, envoyez et suivez les paiements.',
                position: 'right',
            },
            {
                element: '[data-tour="partners"]',
                title: 'Clients & Fournisseurs',
                description: 'Centralisez vos contacts professionnels avec leur historique complet.',
                position: 'right',
            },
            {
                element: '[data-tour="bank"]',
                title: 'Banque',
                description: 'Connectez vos comptes bancaires et réconciliez automatiquement vos transactions.',
                position: 'right',
            },
            {
                element: '[data-tour="vat"]',
                title: 'TVA',
                description: 'Générez vos déclarations TVA automatiquement et exportez vers Intervat.',
                position: 'right',
            },
            {
                element: '[data-tour="reports"]',
                title: 'Rapports',
                description: 'Créez des rapports financiers personnalisés et exportez vers Excel ou PDF.',
                position: 'right',
            },
            {
                element: '[data-tour="chat"]',
                title: 'Assistant IA',
                description: 'Posez des questions en langage naturel ! L\'IA peut créer des factures, analyser vos données et plus encore.',
                position: 'left',
            },
        ];

        // Role-specific additional steps
        const roleSpecific = {
            freelance: [
                {
                    element: '[data-tour="time-tracking"]',
                    title: 'Suivi du temps',
                    description: 'Suivez votre temps de travail et générez des factures basées sur vos heures.',
                    position: 'right',
                },
            ],
            comptable: [
                {
                    element: '[data-tour="accounting"]',
                    title: 'Comptabilité',
                    description: 'Gérez le plan comptable, les écritures et le grand livre.',
                    position: 'right',
                },
            ],
        };

        this.steps = [...baseTours, ...(roleSpecific[role] || [])];
    },

    startTour() {
        this.active = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(0);

        // Track tour start
        axios.post('/onboarding/tour/start');
    },

    createOverlay() {
        // Create dark overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'onboarding-overlay';
        this.overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9998;
            transition: opacity 0.3s;
        `;
        document.body.appendChild(this.overlay);
        document.body.style.overflow = 'hidden';

        // Create tooltip container
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'onboarding-tooltip';
        this.tooltip.style.cssText = `
            position: fixed;
            z-index: 9999;
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: tooltip-in 0.3s ease-out;
        `;
        document.body.appendChild(this.tooltip);
    },

    showStep(index) {
        if (index < 0 || index >= this.steps.length) return;

        this.currentStep = index;
        const step = this.steps[index];

        // Find element
        const element = document.querySelector(step.element);
        if (!element) {
            console.warn(`Element not found for tour step: ${step.element}`);
            this.nextStep();
            return;
        }

        // Highlight element
        this.highlightElement(element);

        // Position tooltip
        this.positionTooltip(element, step.position);

        // Update tooltip content
        this.tooltip.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">${step.title}</h3>
                    <p class="text-sm text-gray-500 mt-1">Étape ${index + 1} sur ${this.steps.length}</p>
                </div>
                <button onclick="window.onboardingTour?.endTour()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <p class="text-gray-700 mb-6">${step.description}</p>

            <div class="flex items-center justify-between">
                <div class="flex gap-1">
                    ${this.steps.map((_, i) => `
                        <div class="w-2 h-2 rounded-full ${i === index ? 'bg-blue-600' : 'bg-gray-300'}"></div>
                    `).join('')}
                </div>

                <div class="flex gap-2">
                    ${index > 0 ? `
                        <button onclick="window.onboardingTour?.prevStep()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            Précédent
                        </button>
                    ` : ''}

                    ${index < this.steps.length - 1 ? `
                        <button onclick="window.onboardingTour?.nextStep()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                            Suivant
                        </button>
                    ` : `
                        <button onclick="window.onboardingTour?.completeTour()"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                            Terminer
                        </button>
                    `}
                </div>
            </div>
        `;

        // Track step seen
        axios.post(`/onboarding/tour/step/${index}`);
    },

    highlightElement(element) {
        // Remove previous highlight
        document.querySelectorAll('.onboarding-highlight').forEach(el => {
            el.classList.remove('onboarding-highlight');
        });

        // Add highlight to current element
        element.classList.add('onboarding-highlight');
        element.style.position = 'relative';
        element.style.zIndex = '9999';
        element.style.boxShadow = '0 0 0 4px rgba(59, 130, 246, 0.5), 0 0 0 99999px rgba(0, 0, 0, 0.7)';
        element.style.borderRadius = '8px';
        element.style.transition = 'all 0.3s';

        // Scroll into view
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    positionTooltip(element, position) {
        const rect = element.getBoundingClientRect();
        const tooltipRect = this.tooltip.getBoundingClientRect();

        let top, left;

        switch (position) {
            case 'bottom':
                top = rect.bottom + 16;
                left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'top':
                top = rect.top - tooltipRect.height - 16;
                left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                break;
            case 'left':
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.left - tooltipRect.width - 16;
                break;
            case 'right':
            default:
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.right + 16;
                break;
        }

        // Ensure tooltip stays within viewport
        const maxX = window.innerWidth - tooltipRect.width - 16;
        const maxY = window.innerHeight - tooltipRect.height - 16;
        left = Math.max(16, Math.min(left, maxX));
        top = Math.max(16, Math.min(top, maxY));

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
    },

    nextStep() {
        if (this.currentStep < this.steps.length - 1) {
            this.showStep(this.currentStep + 1);
        }
    },

    prevStep() {
        if (this.currentStep > 0) {
            this.showStep(this.currentStep - 1);
        }
    },

    async completeTour() {
        await axios.post('/onboarding/tour/complete');
        this.endTour();
        this.showCelebration();
        window.showToast?.('🎉 Tour terminé ! Vous gagnez 10 points !', 'success');
    },

    endTour() {
        this.active = false;

        // Remove highlight
        document.querySelectorAll('.onboarding-highlight').forEach(el => {
            el.classList.remove('onboarding-highlight');
            el.style.position = '';
            el.style.zIndex = '';
            el.style.boxShadow = '';
        });

        // Remove overlay and tooltip
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }

        document.body.style.overflow = '';
    },

    showCelebration() {
        // Simple confetti effect
        const duration = 3 * 1000;
        const animationEnd = Date.now() + duration;

        const interval = setInterval(() => {
            const timeLeft = animationEnd - Date.now();

            if (timeLeft <= 0) {
                return clearInterval(interval);
            }

            // Create confetti
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background: ${['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'][Math.floor(Math.random() * 5)]};
                top: -20px;
                left: ${Math.random() * 100}%;
                z-index: 10000;
                border-radius: 50%;
                animation: confetti-fall 3s ease-out forwards;
            `;
            document.body.appendChild(particle);

            setTimeout(() => particle.remove(), 3000);
        }, 100);
    }
}));

// Make available globally for onclick handlers
window.onboardingTour = null;
document.addEventListener('alpine:init', () => {
    Alpine.effect(() => {
        const element = document.querySelector('[x-data*="onboardingTour"]');
        if (element) {
            const component = Alpine.$data(element);
            if (component) {
                window.onboardingTour = component;
            }
        }
    });
});

// CSS animations
if (!document.getElementById('onboarding-styles')) {
    const style = document.createElement('style');
    style.id = 'onboarding-styles';
    style.textContent = `
        @keyframes tooltip-in {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes confetti-fall {
            to {
                top: 100vh;
                transform: translateX(${Math.random() * 200 - 100}px) rotate(${Math.random() * 720}deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}
