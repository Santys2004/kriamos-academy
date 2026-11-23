const KRIAMOS_CACHE = 'kriamos-biogestion-ica-v10';
const OFFLINE_URL = '/offline.html';

const CORE_ASSETS = [
    '/',
    '/offline.html',
    '/manifest.json',
    '/assets/css/kriamos.css',
    '/assets/js/app.js',
    '/assets/js/offline/indexeddb.js',
    '/assets/js/offline/sync-manager.js',
    '/assets/img/logo-kriamos.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(KRIAMOS_CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== KRIAMOS_CACHE)
                    .map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                const responseClone = response.clone();
                caches.open(KRIAMOS_CACHE).then((cache) => {
                    cache.put(request, responseClone);
                });
                return response;
            })
            .catch(() => {
                return caches.match(request).then((cached) => {
                    if (cached) {
                        return cached;
                    }

                    if (request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }

                    return new Response('', { status: 503, statusText: 'Offline' });
                });
            })
    );
});

self.addEventListener('sync', (event) => {
    if (event.tag === 'kriamos-sync-queue') {
        event.waitUntil(
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => client.postMessage({ type: 'SYNC_QUEUE' }));
            })
        );
    }
});
