const CACHE_NAME = 'finanzas-v2';
const STATIC_CACHE = 'finanzas-static-v2';
const DATA_CACHE = 'finanzas-data-v1';

// Recursos estaticos a cachear
const urlsToCache = [
    '/',
    '/manifest.json'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('Caching static assets');
                return cache.addAll(urlsToCache);
            })
    );
    // Activate immediately
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    const currentCaches = [STATIC_CACHE, DATA_CACHE];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    // Eliminar caches viejos excepto los actuales
                    if (!currentCaches.includes(cacheName) && !cacheName.startsWith('finanzas-')) {
                        return null;
                    }
                    if (cacheName !== STATIC_CACHE && cacheName !== DATA_CACHE &&
                        cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Take control immediately
    self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Para llamadas API
    if (url.pathname.startsWith('/api/')) {
        // Para GET requests de API - Network first, cache fallback
        if (event.request.method === 'GET') {
            event.respondWith(
                fetch(event.request)
                    .then((response) => {
                        // Solo cachear respuestas exitosas
                        if (response.status === 200) {
                            const responseToCache = response.clone();
                            caches.open(DATA_CACHE).then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                        }
                        return response;
                    })
                    .catch(() => {
                        // Sin conexion - intentar desde cache
                        return caches.match(event.request);
                    })
            );
            return;
        }

        // Para POST/PUT/DELETE - dejar que el interceptor de axios maneje offline
        return;
    }

    // Para assets de Vite/build
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(event.request)
                .then((response) => {
                    if (response) {
                        return response;
                    }
                    return fetch(event.request).then((networkResponse) => {
                        // Cachear los assets del build
                        if (networkResponse.status === 200) {
                            const responseToCache = networkResponse.clone();
                            caches.open(STATIC_CACHE).then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                        }
                        return networkResponse;
                    });
                })
        );
        return;
    }

    // Para todo lo demas - Network first, cache fallback
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response
                const responseToCache = response.clone();

                // Cache successful responses
                if (response.status === 200) {
                    caches.open(STATIC_CACHE)
                        .then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                }

                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Si es una navegacion, devolver la pagina principal cacheada
                    if (event.request.mode === 'navigate') {
                        return caches.match('/');
                    }
                    return new Response('Offline', { status: 503 });
                });
            })
    );
});

// Escuchar mensajes del cliente
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Background Sync para sincronizar operaciones pendientes
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-pending-operations') {
        event.waitUntil(
            // Notificar a los clientes que sincronicen
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({
                        type: 'SYNC_REQUESTED'
                    });
                });
            })
        );
    }
});
