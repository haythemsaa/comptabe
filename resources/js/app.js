/**
 * ComptaBE - Application de comptabilité belge
 * Main JavaScript Entry Point
 */

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import mask from '@alpinejs/mask';
import persist from '@alpinejs/persist';
import { gsap } from 'gsap';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { French } from 'flatpickr/dist/l10n/fr.js';
import TomSelect from 'tom-select';
import ApexCharts from 'apexcharts';
import Sortable from 'sortablejs';
import { marked } from 'marked';
import './components/chat.js';
import './components/onboarding.js';
import './components/dashboard.js';

// ============================================
// ALPINE.JS SETUP
// ============================================

Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(mask);
Alpine.plugin(persist);

// Global Alpine store for app state
Alpine.store('app', {
    sidebarOpen: Alpine.$persist(true).as('sidebarOpen'),
    darkMode: Alpine.$persist(false).as('darkMode'),
    currentTenant: null,
    notifications: [],

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        document.documentElement.classList.toggle('dark', this.darkMode);
    },

    init() {
        // Initialize dark mode
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }
    }
});

// Alpine data components
Alpine.data('dropdown', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    },
    close() {
        this.open = false;
    }
}));

Alpine.data('modal', (initialOpen = false) => ({
    open: initialOpen,
    show() {
        this.open = true;
        document.body.style.overflow = 'hidden';
    },
    hide() {
        this.open = false;
        document.body.style.overflow = '';
    },
    toggle() {
        this.open ? this.hide() : this.show();
    }
}));

Alpine.data('tabs', (defaultTab = '') => ({
    activeTab: defaultTab,
    setTab(tab) {
        this.activeTab = tab;
    },
    isActive(tab) {
        return this.activeTab === tab;
    }
}));

Alpine.data('toast', () => ({
    toasts: [],
    add(message, type = 'info', duration = 5000) {
        const id = Date.now();
        this.toasts.push({ id, message, type });
        if (duration > 0) {
            setTimeout(() => this.remove(id), duration);
        }
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
}));

// Auto-save mixin factory - returns methods and properties to spread into any Alpine component
window.autoSaveMixin = function(formId, saveInterval = 30000) {
    return {
        autoSave: {
            isDirty: false,
            lastSaved: null,
            saveTimer: null,
            formData: {},
            autoSaveEnabled: true,
        },

        initAutoSave() {
            this.autoSave.saveTimer = setInterval(() => {
                if (this.autoSave.isDirty && this.autoSave.autoSaveEnabled) {
                    this.saveFormDraft();
                }
            }, saveInterval);

            // Load draft on init
            this.loadFormDraft();

            // Save before leaving page
            window.addEventListener('beforeunload', () => {
                if (this.autoSave.isDirty) {
                    this.saveFormDraft();
                }
            });
        },

        markFormDirty() {
            this.autoSave.isDirty = true;
        },

        saveFormDraft() {
            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.querySelector('form');
            if (!form) return;

            const formData = new FormData(form);
            const data = {};

            formData.forEach((value, key) => {
                if (key !== '_token' && !key.includes('password')) {
                    if (key.endsWith('[]')) {
                        const cleanKey = key.slice(0, -2);
                        if (!data[cleanKey]) data[cleanKey] = [];
                        data[cleanKey].push(value);
                    } else {
                        data[key] = value;
                    }
                }
            });

            const draftKey = `comptabe_draft_${formId}`;
            const draft = {
                data: data,
                savedAt: new Date().toISOString(),
                url: window.location.pathname
            };

            try {
                localStorage.setItem(draftKey, JSON.stringify(draft));
                this.autoSave.lastSaved = new Date();
                this.autoSave.isDirty = false;
            } catch (e) {
                console.warn('Auto-save failed:', e);
            }
        },

        loadFormDraft() {
            const draftKey = `comptabe_draft_${formId}`;
            const draft = localStorage.getItem(draftKey);

            if (draft) {
                try {
                    const parsed = JSON.parse(draft);

                    // Check if draft is from the same URL
                    if (parsed.url !== window.location.pathname) {
                        return false;
                    }

                    // Check draft age - ignore drafts older than 24 hours
                    const draftAge = new Date() - new Date(parsed.savedAt);
                    const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                    if (draftAge > maxAge) {
                        // Draft is too old, remove it
                        localStorage.removeItem(draftKey);
                        console.log('🗑️ Old draft removed during load (older than 24h)');
                        return false;
                    }

                    // Draft is recent and valid, load it
                    this.autoSave.formData = parsed.data;
                    this.autoSave.lastSaved = new Date(parsed.savedAt);
                    console.log('✅ Recent draft loaded (age: ' + Math.floor(draftAge / (1000 * 60)) + ' minutes)');
                    return true;
                } catch (e) {
                    console.warn('Failed to load draft:', e);
                }
            }
            return false;
        },

        hasFormDraft() {
            const draftKey = `comptabe_draft_${formId}`;
            const draft = localStorage.getItem(draftKey);
            if (draft) {
                try {
                    const parsed = JSON.parse(draft);

                    // Check if draft is from the same URL
                    if (parsed.url !== window.location.pathname) {
                        return false;
                    }

                    // Check draft age - ignore drafts older than 24 hours
                    const draftAge = new Date() - new Date(parsed.savedAt);
                    const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                    if (draftAge > maxAge) {
                        // Draft is too old, remove it
                        localStorage.removeItem(draftKey);
                        console.log('🗑️ Old draft removed (older than 24h)');
                        return false;
                    }

                    return true;
                } catch (e) {
                    return false;
                }
            }
            return false;
        },

        restoreFormDraft() {
            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.querySelector('form');
            if (!form || !this.autoSave.formData) return;

            Object.entries(this.autoSave.formData).forEach(([key, value]) => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = value === 'on' || value === true;
                    } else if (input.type === 'radio') {
                        const radio = form.querySelector(`[name="${key}"][value="${value}"]`);
                        if (radio) radio.checked = true;
                    } else if (input.tagName === 'SELECT') {
                        input.value = value;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
        },

        clearFormDraft() {
            const draftKey = `comptabe_draft_${formId}`;
            localStorage.removeItem(draftKey);
            this.autoSave.formData = {};
            this.autoSave.lastSaved = null;
            this.autoSave.isDirty = false;
        },

        formatAutoSaveTime() {
            if (!this.autoSave.lastSaved) return '';
            const now = new Date();
            const diff = Math.floor((now - this.autoSave.lastSaved) / 1000);

            if (diff < 60) return 'À l\'instant';
            if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
            return this.autoSave.lastSaved.toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' });
        }
    };
};

// Auto-save component
Alpine.data('autoSave', (formId, saveInterval = 30000) => ({
    isDirty: false,
    lastSaved: null,
    saveTimer: null,
    formData: {},
    autoSaveEnabled: true,

    init() {
        this.loadDraft();
        this.startAutoSave();

        // Track form changes
        this.$el.addEventListener('input', () => this.markDirty());
        this.$el.addEventListener('change', () => this.markDirty());

        // Clear draft on successful submit
        this.$el.addEventListener('submit', (e) => {
            // Only clear if form will actually submit (no validation errors)
            if (this.$el.checkValidity()) {
                this.clearDraft();
            }
        });

        // Save before leaving page
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                this.saveDraft();
            }
        });
    },

    markDirty() {
        this.isDirty = true;
    },

    startAutoSave() {
        this.saveTimer = setInterval(() => {
            if (this.isDirty && this.autoSaveEnabled) {
                this.saveDraft();
            }
        }, saveInterval);
    },

    stopAutoSave() {
        if (this.saveTimer) {
            clearInterval(this.saveTimer);
            this.saveTimer = null;
        }
    },

    saveDraft() {
        const formData = new FormData(this.$el);
        const data = {};

        formData.forEach((value, key) => {
            // Skip CSRF token and sensitive fields
            if (key !== '_token' && !key.includes('password')) {
                // Handle array fields
                if (key.endsWith('[]')) {
                    const cleanKey = key.slice(0, -2);
                    if (!data[cleanKey]) data[cleanKey] = [];
                    data[cleanKey].push(value);
                } else {
                    data[key] = value;
                }
            }
        });

        const draftKey = `comptabe_draft_${formId}`;
        const draft = {
            data: data,
            savedAt: new Date().toISOString(),
            url: window.location.pathname
        };

        try {
            localStorage.setItem(draftKey, JSON.stringify(draft));
            this.lastSaved = new Date();
            this.isDirty = false;
            this.$dispatch('draft-saved', { formId, savedAt: this.lastSaved });
        } catch (e) {
            console.warn('Auto-save failed:', e);
        }
    },

    loadDraft() {
        const draftKey = `comptabe_draft_${formId}`;
        const draft = localStorage.getItem(draftKey);

        if (draft) {
            try {
                const parsed = JSON.parse(draft);

                // Check if draft is from the same URL
                if (parsed.url !== window.location.pathname) {
                    return false;
                }

                // Check draft age - ignore drafts older than 24 hours
                const draftAge = new Date() - new Date(parsed.savedAt);
                const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                if (draftAge > maxAge) {
                    // Draft is too old, remove it
                    localStorage.removeItem(draftKey);
                    console.log('🗑️ Old draft removed during load (older than 24h)');
                    return false;
                }

                // Draft is recent and valid, load it
                this.formData = parsed.data;
                this.lastSaved = new Date(parsed.savedAt);
                console.log('✅ Recent draft loaded (age: ' + Math.floor(draftAge / (1000 * 60)) + ' minutes)');
                this.$dispatch('draft-loaded', { formId, data: parsed.data });
                return true;
            } catch (e) {
                console.warn('Failed to load draft:', e);
            }
        }
        return false;
    },

    hasDraft() {
        const draftKey = `comptabe_draft_${formId}`;
        const draft = localStorage.getItem(draftKey);
        if (draft) {
            try {
                const parsed = JSON.parse(draft);

                // Check if draft is from the same URL
                if (parsed.url !== window.location.pathname) {
                    return false;
                }

                // Check draft age - ignore drafts older than 24 hours
                const draftAge = new Date() - new Date(parsed.savedAt);
                const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds

                if (draftAge > maxAge) {
                    // Draft is too old, remove it
                    localStorage.removeItem(draftKey);
                    console.log('🗑️ Old draft removed (older than 24h)');
                    return false;
                }

                return true;
            } catch (e) {
                return false;
            }
        }
        return false;
    },

    restoreDraft() {
        if (this.formData) {
            Object.entries(this.formData).forEach(([key, value]) => {
                const input = this.$el.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox') {
                        input.checked = value === 'on' || value === true;
                    } else if (input.type === 'radio') {
                        const radio = this.$el.querySelector(`[name="${key}"][value="${value}"]`);
                        if (radio) radio.checked = true;
                    } else if (input.tagName === 'SELECT') {
                        input.value = value;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
            this.$dispatch('draft-restored', { formId });
        }
    },

    clearDraft() {
        const draftKey = `comptabe_draft_${formId}`;
        localStorage.removeItem(draftKey);
        this.formData = {};
        this.lastSaved = null;
        this.isDirty = false;
        this.$dispatch('draft-cleared', { formId });
    },

    toggleAutoSave() {
        this.autoSaveEnabled = !this.autoSaveEnabled;
    },

    formatLastSaved() {
        if (!this.lastSaved) return '';
        const now = new Date();
        const diff = Math.floor((now - this.lastSaved) / 1000);

        if (diff < 60) return 'À l\'instant';
        if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
        return this.lastSaved.toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' });
    },

    destroy() {
        this.stopAutoSave();
    }
}));

// Invoice form component
Alpine.data('invoiceForm', () => ({
    lines: [],
    partnerId: null,
    partnerPeppolCapable: false,

    init() {
        if (this.lines.length === 0) {
            this.addLine();
        }
    },

    addLine() {
        this.lines.push({
            id: Date.now(),
            description: '',
            quantity: 1,
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

    calculateLineVat(line) {
        return this.calculateLineTotal(line) * (line.vatRate / 100);
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

    formatCurrency(amount) {
        return new Intl.NumberFormat('fr-BE', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    },

    async lookupPartner(vatNumber) {
        if (vatNumber.length < 10) return;

        try {
            const response = await fetch(`/api/partners/lookup?vat=${vatNumber}`);
            const data = await response.json();

            if (data.success) {
                this.partnerPeppolCapable = data.peppol_capable;
            }
        } catch (error) {
            console.error('Partner lookup failed:', error);
        }
    }
}));

// Bank reconciliation component
Alpine.data('bankReconciliation', () => ({
    transactions: [],
    selectedTransaction: null,
    matchSuggestions: [],

    async loadSuggestions(transactionId) {
        this.selectedTransaction = transactionId;

        try {
            const response = await fetch(`/api/bank/transactions/${transactionId}/suggestions`);
            this.matchSuggestions = await response.json();
        } catch (error) {
            console.error('Failed to load suggestions:', error);
        }
    },

    async matchWithInvoice(transactionId, invoiceId) {
        try {
            const response = await fetch(`/api/bank/transactions/${transactionId}/match`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ invoice_id: invoiceId })
            });

            if (response.ok) {
                // Refresh the transaction list
                this.$dispatch('transaction-matched');
            }
        } catch (error) {
            console.error('Match failed:', error);
        }
    }
}));

window.Alpine = Alpine;
Alpine.start();

// ============================================
// GSAP ANIMATIONS
// ============================================

// Page load animation
document.addEventListener('DOMContentLoaded', () => {
    // Counter animation
    document.querySelectorAll('[data-counter]').forEach(el => {
        const target = parseFloat(el.dataset.counter);
        const decimals = el.dataset.decimals || 0;
        const duration = el.dataset.duration || 2;
        const prefix = el.dataset.prefix || '';
        const suffix = el.dataset.suffix || '';

        gsap.to(el, {
            duration: duration,
            innerHTML: target,
            snap: { innerHTML: Math.pow(10, -decimals) },
            ease: 'power2.out',
            onUpdate: function() {
                el.innerHTML = prefix + parseFloat(el.innerHTML).toLocaleString('fr-BE', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;
            }
        });
    });
});

// Smooth scroll to element
window.scrollToElement = (selector) => {
    const element = document.querySelector(selector);
    if (element) {
        gsap.to(window, {
            duration: 0.8,
            scrollTo: { y: element, offsetY: 100 },
            ease: 'power3.inOut'
        });
    }
};

// ============================================
// FLATPICKR INITIALIZATION
// ============================================

window.initDatePicker = (selector, options = {}) => {
    const defaultOptions = {
        locale: French,
        dateFormat: 'd/m/Y',
        allowInput: true,
        disableMobile: true,
        ...options
    };

    return flatpickr(selector, defaultOptions);
};

// Auto-init date pickers
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-datepicker]').forEach(el => {
        initDatePicker(el, JSON.parse(el.dataset.datepicker || '{}'));
    });
});

// ============================================
// TOM SELECT INITIALIZATION
// ============================================

window.initSelect = (selector, options = {}) => {
    const defaultOptions = {
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        ...options
    };

    return new TomSelect(selector, defaultOptions);
};

// Auto-init selects
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-select]').forEach(el => {
        initSelect(el, JSON.parse(el.dataset.select || '{}'));
    });
});

// ============================================
// CHARTS (APEXCHARTS)
// ============================================

window.createChart = (selector, options) => {
    const defaultOptions = {
        chart: {
            fontFamily: 'Inter, system-ui, sans-serif',
            toolbar: { show: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        colors: ['#0ea5e9', '#22c55e', '#f59e0b', '#ef4444'],
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            theme: 'light',
            style: { fontSize: '12px' }
        },
        ...options
    };

    const chart = new ApexCharts(document.querySelector(selector), defaultOptions);
    chart.render();
    return chart;
};

// ============================================
// SORTABLE (DRAG & DROP)
// ============================================

window.initSortable = (selector, options = {}) => {
    const element = document.querySelector(selector);
    if (!element) return null;

    return new Sortable(element, {
        animation: 150,
        ghostClass: 'opacity-50',
        ...options
    });
};

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format Belgian VAT number
window.formatVatNumber = (value) => {
    const digits = value.replace(/\D/g, '');
    if (digits.length <= 4) return 'BE' + digits;
    if (digits.length <= 7) return 'BE' + digits.slice(0, 4) + '.' + digits.slice(4);
    return 'BE' + digits.slice(0, 4) + '.' + digits.slice(4, 7) + '.' + digits.slice(7, 10);
};

// Format IBAN
window.formatIBAN = (value) => {
    const clean = value.replace(/\s/g, '').toUpperCase();
    return clean.match(/.{1,4}/g)?.join(' ') || clean;
};

// Generate structured communication (Belgian format)
window.generateStructuredCommunication = () => {
    const random = Math.floor(Math.random() * 9999999999).toString().padStart(10, '0');
    const base = random.slice(0, 10);
    const modulo = parseInt(base) % 97;
    const checkDigits = modulo === 0 ? '97' : modulo.toString().padStart(2, '0');
    const full = base + checkDigits;
    return `+++${full.slice(0, 3)}/${full.slice(3, 7)}/${full.slice(7)}+++`;
};

// Validate Belgian VAT number
window.validateBelgianVAT = (vat) => {
    const clean = vat.replace(/[^0-9]/g, '');
    if (clean.length !== 10) return false;

    const base = parseInt(clean.slice(0, 8));
    const check = parseInt(clean.slice(8));

    return (97 - (base % 97)) === check;
};

// Copy to clipboard
window.copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (err) {
        console.error('Copy failed:', err);
        return false;
    }
};

// Debounce function
window.debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Format file size
window.formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// ============================================
// AXIOS SETUP
// ============================================

import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios = axios;
window.marked = marked;

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

// Track key sequences for vim-like navigation
let keySequence = '';
let keySequenceTimeout = null;

document.addEventListener('keydown', (e) => {
    // Skip if user is typing in an input/textarea
    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) ||
                     e.target.isContentEditable;

    // Ctrl/Cmd + K - Open command palette
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.dispatchEvent(new CustomEvent('open-command-palette'));
        return;
    }

    // Ctrl/Cmd + N - New invoice
    if ((e.ctrlKey || e.metaKey) && e.key === 'n' && !isTyping) {
        e.preventDefault();
        window.location.href = '/invoices/create';
        return;
    }

    // Ctrl/Cmd + S - Save form
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const form = document.querySelector('form[data-autosave], form:not([data-no-shortcut])');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
        return;
    }

    // Ctrl/Cmd + Shift + L - Toggle dark mode
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'L') {
        e.preventDefault();
        Alpine.store('app').toggleDarkMode();
        return;
    }

    // ? - Show keyboard shortcuts
    if (e.key === '?' && !isTyping) {
        e.preventDefault();
        document.dispatchEvent(new CustomEvent('open-shortcuts'));
        return;
    }

    // Escape - Close modals/panels
    if (e.key === 'Escape') {
        document.dispatchEvent(new CustomEvent('close-modal'));
        document.dispatchEvent(new CustomEvent('close-command-palette'));
        return;
    }

    // Vim-like navigation (g + key)
    if (!isTyping) {
        clearTimeout(keySequenceTimeout);

        if (e.key === 'g') {
            keySequence = 'g';
            keySequenceTimeout = setTimeout(() => keySequence = '', 1000);
            return;
        }

        if (keySequence === 'g') {
            keySequence = '';
            switch (e.key.toLowerCase()) {
                case 'd': // Go to Dashboard
                    window.location.href = '/dashboard';
                    break;
                case 'i': // Go to Invoices
                    window.location.href = '/invoices';
                    break;
                case 'c': // Go to Clients
                    window.location.href = '/partners?type=customer';
                    break;
                case 'p': // Go to Purchases
                    window.location.href = '/purchases';
                    break;
                case 'b': // Go to Bank
                    window.location.href = '/bank';
                    break;
                case 's': // Go to Settings
                    window.location.href = '/settings';
                    break;
            }
        }
    }
});

console.log('ComptaBE - Application initialized');
