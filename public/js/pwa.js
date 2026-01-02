/**
 * PWA Installation and Service Worker Registration
 * ComptaBE Progressive Web App
 */

// Variables globales
let deferredPrompt = null;
let swRegistration = null;

// TEMPORAIREMENT DÉSACTIVÉ - Service Worker désactivé pour résoudre problèmes de cache
// Enregistrer le Service Worker
if (false && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        registerServiceWorker();
    });
}

// UNREGISTER ALL EXISTING SERVICE WORKERS
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for (let registration of registrations) {
            registration.unregister().then(function(success) {
                console.log('[PWA] Service Worker désinstallé:', success);
            });
        }
    });
}

/**
 * Enregistrer le Service Worker
 */
async function registerServiceWorker() {
    try {
        swRegistration = await navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        });

        console.log('[PWA] Service Worker enregistré avec succès:', swRegistration);

        // Vérifier les mises à jour toutes les heures
        setInterval(() => {
            swRegistration.update();
        }, 60 * 60 * 1000);

        // Écouter les mises à jour du SW
        swRegistration.addEventListener('updatefound', () => {
            const newWorker = swRegistration.installing;

            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // Nouvelle version disponible
                    showUpdateNotification();
                }
            });
        });

    } catch (error) {
        console.error('[PWA] Erreur enregistrement Service Worker:', error);
    }
}

/**
 * Afficher notification de mise à jour disponible
 */
function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.id = 'pwa-update-notification';
    notification.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1f2937;
            color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 9999;
            max-width: 400px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideInUp 0.3s ease-out;
        ">
            <div style="flex: 1;">
                <div style="font-weight: 600; margin-bottom: 5px;">Nouvelle version disponible</div>
                <div style="font-size: 14px; opacity: 0.8;">Rafraîchissez la page pour mettre à jour l'application.</div>
            </div>
            <button onclick="window.location.reload()" style="
                background: #2563eb;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                white-space: nowrap;
            ">
                Mettre à jour
            </button>
            <button onclick="this.closest('#pwa-update-notification').remove()" style="
                background: transparent;
                color: white;
                border: none;
                padding: 5px;
                cursor: pointer;
                opacity: 0.6;
            ">
                ✕
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Ajouter l'animation CSS si elle n'existe pas
    if (!document.getElementById('pwa-animations')) {
        const style = document.createElement('style');
        style.id = 'pwa-animations';
        style.textContent = `
            @keyframes slideInUp {
                from {
                    transform: translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes slideInDown {
                from {
                    transform: translateY(-100px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Gérer le prompt d'installation PWA
 */
window.addEventListener('beforeinstallprompt', (e) => {
    // Empêcher le navigateur d'afficher le prompt automatiquement
    e.preventDefault();

    // Stocker l'événement pour l'utiliser plus tard
    deferredPrompt = e;

    console.log('[PWA] Prompt d\'installation disponible');

    // Afficher notre propre bouton d'installation
    showInstallButton();
});

/**
 * Afficher le bouton/banner d'installation personnalisé
 */
function showInstallButton() {
    // Vérifier si déjà installé
    if (window.matchMedia('(display-mode: standalone)').matches) {
        console.log('[PWA] Application déjà installée');
        return;
    }

    // Ne pas afficher si déjà fermé par l'utilisateur
    if (localStorage.getItem('pwa-install-dismissed') === 'true') {
        return;
    }

    const installBanner = document.createElement('div');
    installBanner.id = 'pwa-install-banner';
    installBanner.innerHTML = `
        <div style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInDown 0.3s ease-out;
        ">
            <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <div>
                    <div style="font-weight: 600; margin-bottom: 2px;">Installer ComptaBE</div>
                    <div style="font-size: 14px; opacity: 0.9;">Accédez rapidement à votre comptabilité depuis votre écran d'accueil</div>
                </div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button id="pwa-install-button" style="
                    background: white;
                    color: #667eea;
                    border: none;
                    padding: 10px 24px;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    white-space: nowrap;
                    transition: transform 0.2s;
                " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    Installer
                </button>
                <button id="pwa-dismiss-button" style="
                    background: transparent;
                    color: white;
                    border: 1px solid rgba(255,255,255,0.3);
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-weight: 500;
                    cursor: pointer;
                    white-space: nowrap;
                ">
                    Plus tard
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(installBanner);

    // Gérer le clic sur "Installer"
    document.getElementById('pwa-install-button').addEventListener('click', installPWA);

    // Gérer le clic sur "Plus tard"
    document.getElementById('pwa-dismiss-button').addEventListener('click', () => {
        installBanner.remove();
        localStorage.setItem('pwa-install-dismissed', 'true');
    });
}

/**
 * Installer la PWA
 */
async function installPWA() {
    if (!deferredPrompt) {
        console.log('[PWA] Prompt d\'installation non disponible');
        return;
    }

    // Afficher le prompt d'installation natif
    deferredPrompt.prompt();

    // Attendre la réponse de l'utilisateur
    const { outcome } = await deferredPrompt.userChoice;

    console.log(`[PWA] Choix utilisateur: ${outcome}`);

    // Supprimer le banner
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.remove();
    }

    // Réinitialiser
    deferredPrompt = null;

    if (outcome === 'accepted') {
        console.log('[PWA] Application installée avec succès');

        // Envoyer analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'pwa_install', {
                event_category: 'engagement',
                event_label: 'PWA Installed'
            });
        }
    }
}

/**
 * Détecter si l'app est déjà installée
 */
window.addEventListener('appinstalled', (evt) => {
    console.log('[PWA] Application installée');

    // Supprimer le banner si présent
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
        banner.remove();
    }

    // Afficher message de succès
    showToast('Application installée avec succès ! Vous pouvez maintenant y accéder depuis votre écran d\'accueil.', 'success');
});

/**
 * Détecter si l'app est lancée en mode standalone (installée)
 */
function isPWAInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
}

if (isPWAInstalled()) {
    console.log('[PWA] Application en mode standalone (installée)');
}

/**
 * Afficher un toast notification
 */
function showToast(message, type = 'info') {
    const colors = {
        success: { bg: '#10b981', icon: '✓' },
        error: { bg: '#ef4444', icon: '✕' },
        info: { bg: '#3b82f6', icon: 'ℹ' },
        warning: { bg: '#f59e0b', icon: '⚠' }
    };

    const color = colors[type] || colors.info;

    const toast = document.createElement('div');
    toast.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${color.bg};
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInUp 0.3s ease-out;
            max-width: 90%;
        ">
            <span style="font-size: 20px;">${color.icon}</span>
            <span style="font-weight: 500;">${message}</span>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(20px)';
        toast.style.transition = 'all 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/**
 * Vérifier le statut de connexion
 */
window.addEventListener('online', () => {
    console.log('[PWA] Connexion rétablie');
    showToast('Connexion Internet rétablie', 'success');
});

window.addEventListener('offline', () => {
    console.log('[PWA] Connexion perdue');
    showToast('Vous êtes hors ligne. Certaines fonctionnalités peuvent être limitées.', 'warning');
});

// Exposer les fonctions globalement pour debug
window.PWA = {
    install: installPWA,
    isInstalled: isPWAInstalled,
    registration: () => swRegistration,
    showInstallButton: showInstallButton
};

console.log('[PWA] Script chargé. Utilisez window.PWA pour debug.');
