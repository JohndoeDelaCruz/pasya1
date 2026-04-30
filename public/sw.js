const CACHE_NAME = 'pasya-farmer-v5';
const OFFLINE_URL = '/offline.html';

const NETWORK_ONLY_PATH_PREFIXES = [
    '/api',
    '/farmer',
    '/predictions',
    '/admin',
    '/dashboard',
    '/login',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/confirm-password',
    '/verify-email',
    '/email',
    '/logout',
    '/password',
    '/profile',
    '/farmer/profile'
];

const matchesPathPrefix = (pathname, prefix) => {
    return pathname === prefix || pathname.startsWith(`${prefix}/`);
};

const isNetworkOnlyPath = (pathname) => {
    return NETWORK_ONLY_PATH_PREFIXES.some((prefix) => matchesPathPrefix(pathname, prefix));
};

const shouldCacheResponse = (response) => {
    return response && response.status === 200 && !response.redirected;
};

// Assets to cache immediately on install
const PRECACHE_ASSETS = [
    '/offline.html',
    '/images/PASYA.png',
    '/images/titleh.png',
    '/manifest.json'
];

// Install event - cache core assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('PASYA: Caching core assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => {
                return self.skipWaiting();
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('PASYA: Removing old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const requestUrl = new URL(event.request.url);

    // Skip cross-origin requests
    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Keep dynamic data, auth, and account routes network-only.
    if (isNetworkOnlyPath(requestUrl.pathname)) {
        return;
    }

    // For page navigations, prefer fresh network HTML and fallback to cache/offline.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (shouldCacheResponse(response)) {
                        const responseClone = response.clone();
                        event.waitUntil(
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, responseClone);
                            })
                        );
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedPage = await caches.match(event.request);
                    return cachedPage || caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version and update cache in background
                    event.waitUntil(
                        fetch(event.request)
                            .then((response) => {
                                if (shouldCacheResponse(response)) {
                                    const responseClone = response.clone();
                                    caches.open(CACHE_NAME)
                                        .then((cache) => {
                                            cache.put(event.request, responseClone);
                                        });
                                }
                            })
                            .catch(() => {})
                    );
                    return cachedResponse;
                }

                // Not in cache - fetch from network
                return fetch(event.request)
                    .then((response) => {
                        // Cache successful responses
                        if (shouldCacheResponse(response)) {
                            const responseClone = response.clone();
                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, responseClone);
                                });
                        }
                        return response;
                    })
                    .catch(() => {
                        // Network failed - show offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
});

// Handle push notifications (for future use)
self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: '/images/icons/icon-192x192.png',
            badge: '/images/icons/icon-72x72.png',
            vibrate: [100, 50, 100],
            data: {
                url: data.url || '/farmer/dashboard'
            }
        };
        event.waitUntil(
            self.registration.showNotification(data.title || 'PASYA', options)
        );
    }
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
