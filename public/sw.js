// Service Worker pour ComptaBE PWA - TEMPORAIREMENT DÉSACTIVÉ
// Version du cache - incrémenter pour forcer la mise à jour
const CACHE_VERSION = 'v2.0.0-fresh-rebuild';
const CACHE_NAME = `comptabe-${CACHE_VERSION}`;

// Ressources à mettre en cache lors de l'installation
const STATIC_ASSETS = [];

// Stratégies de cache
const CACHE_STRATEGIES = {
    // Network First: Toujours essayer le réseau d'abord
    NETWORK_FIRST: 'network-first',
    // Cache First: Utiliser le cache si disponible
    CACHE_FIRST: 'cache-first',
    // Stale While Revalidate: Servir du cache mais mettre à jour en arrière-plan
    STALE_WHILE_REVALIDATE: 'stale-while-revalidate',
    // Network Only: Ne jamais utiliser le cache
    NETWORK_ONLY: 'network-only',
    // Cache Only: Ne jamais aller sur le réseau
    CACHE_ONLY: 'cache-only',
};

// Configuration des routes avec stratégies
const ROUTE_STRATEGIES = {
    '/api/': CACHE_STRATEGIES.NETWORK_FIRST,
    '/images/': CACHE_STRATEGIES.CACHE_FIRST,
    '/css/': CACHE_STRATEGIES.STALE_WHILE_REVALIDATE,
    '/js/': CACHE_STRATEGIES.STALE_WHILE_REVALIDATE,
    '/build/': CACHE_STRATEGIES.STALE_WHILE_REVALIDATE,
};

// Installation du Service Worker
self.addEventListener('install', (event) => {
    console.log('[SW] Installation...', CACHE_VERSION);

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Cache ouvert, ajout des ressources statiques');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('[SW] Installation terminée');
                // Forcer l'activation immédiate
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Erreur lors de l\'installation:', error);
            })
    );
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    console.log('[SW] Activation...', CACHE_VERSION);

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                // Supprimer les anciens caches
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[SW] Suppression ancien cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Activation terminée');
                // Prendre le contrôle immédiatement
                return self.clients.claim();
            })
    );
});

// Interception des requêtes (Fetch)
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorer les requêtes non-HTTP
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Ignorer les requêtes vers d'autres domaines (CORS)
    if (url.origin !== location.origin) {
        return;
    }

    // Déterminer la stratégie selon l'URL
    const strategy = getStrategyForUrl(url.pathname);

    event.respondWith(
        handleFetchWithStrategy(request, strategy)
    );
});

// Déterminer la stratégie de cache selon l'URL
function getStrategyForUrl(pathname) {
    // TEMPORAIREMENT: Toujours utiliser le réseau sans cache
    return CACHE_STRATEGIES.NETWORK_ONLY;
}

// Gérer la requête selon la stratégie
async function handleFetchWithStrategy(request, strategy) {
    switch (strategy) {
        case CACHE_STRATEGIES.NETWORK_FIRST:
            return networkFirst(request);

        case CACHE_STRATEGIES.CACHE_FIRST:
            return cacheFirst(request);

        case CACHE_STRATEGIES.STALE_WHILE_REVALIDATE:
            return staleWhileRevalidate(request);

        case CACHE_STRATEGIES.NETWORK_ONLY:
            return fetch(request);

        case CACHE_STRATEGIES.CACHE_ONLY:
            return caches.match(request);

        default:
            return networkFirst(request);
    }
}

// Network First: Essayer réseau, puis cache si échec
async function networkFirst(request) {
    try {
        const response = await fetch(request);

        // Mettre en cache si succès
        if (response && response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        // Si réseau échoue, essayer le cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
            console.log('[SW] Servir depuis cache (offline):', request.url);
            return cachedResponse;
        }

        // Si rien dans le cache, retourner page offline
        if (request.mode === 'navigate') {
            return caches.match('/offline');
        }

        throw error;
    }
}

// Cache First: Utiliser cache si disponible, sinon réseau
async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const response = await fetch(request);

        // Mettre en cache si succès
        if (response && response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.error('[SW] Erreur fetch:', error);
        throw error;
    }
}

// Stale While Revalidate: Servir du cache mais mettre à jour en arrière-plan
async function staleWhileRevalidate(request) {
    const cachedResponse = await caches.match(request);

    // Lancer la mise à jour en arrière-plan
    const fetchPromise = fetch(request).then((response) => {
        if (response && response.status === 200) {
            // Clone immédiatement pour éviter "Response body is already used"
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((c) => c.put(request, responseClone));
        }
        return response;
    }).catch((error) => {
        console.error('[SW] Erreur fetch background:', error);
        return null; // Retourner null en cas d'erreur
    });

    // Retourner cache si disponible, sinon attendre le réseau
    return cachedResponse || fetchPromise;
}

// Gestion des messages (pour synchronisation, notifications, etc.)
self.addEventListener('message', (event) => {
    console.log('[SW] Message reçu:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => caches.delete(cacheName))
                );
            })
        );
    }
});

// Background Sync pour synchroniser les données offline
self.addEventListener('sync', (event) => {
    console.log('[SW] Sync event:', event.tag);

    if (event.tag === 'sync-invoices') {
        event.waitUntil(syncInvoices());
    }
});

// Fonction de synchronisation des factures
async function syncInvoices() {
    console.log('[SW] Synchronisation des factures...');

    // Récupérer les données en attente depuis IndexedDB
    // et les envoyer au serveur quand la connexion est rétablie

    try {
        // TODO: Implémenter la logique de sync avec IndexedDB
        console.log('[SW] Sync terminée');
    } catch (error) {
        console.error('[SW] Erreur sync:', error);
        throw error;
    }
}

// Push Notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification reçue');

    const data = event.data ? event.data.json() : {};
    const title = data.title || 'ComptaBE';
    const options = {
        body: data.body || 'Nouvelle notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/badge-72x72.png',
        data: data.url || '/',
        actions: [
            {
                action: 'open',
                title: 'Ouvrir'
            },
            {
                action: 'close',
                title: 'Fermer'
            }
        ],
        vibrate: [200, 100, 200],
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Clic sur notification
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification cliquée:', event.action);

    event.notification.close();

    if (event.action === 'open' || !event.action) {
        const url = event.notification.data || '/dashboard';

        event.waitUntil(
            clients.openWindow(url)
        );
    }
});

console.log('[SW] Service Worker chargé', CACHE_VERSION);
