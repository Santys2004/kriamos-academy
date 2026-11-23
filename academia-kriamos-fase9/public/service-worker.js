const APP_CACHE = 'kriamos-app-shell-fase9-v1';
const MATERIAL_CACHE = 'kriamos-offline-materials-v1';

const APP_SHELL = [
  './',
  './index.php',
  './index.php?page=home',
  './index.php?page=offline',
  './offline.html',
  './manifest.json',
  './assets/css/kriamos.css',
  './assets/js/app.js',
  './assets/js/pwa.js',
  './assets/img/logo-kriamos.png',
  './assets/img/hero-galpon.jpg',
  './assets/img/pollito.jpg'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(APP_CACHE).then(cache => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys
        .filter(key => key !== APP_CACHE && key !== MATERIAL_CACHE)
        .map(key => caches.delete(key))
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(response => {
          const copy = response.clone();
          caches.open(APP_CACHE).then(cache => cache.put(request, copy));
          return response;
        })
        .catch(() => caches.match(request).then(cached => cached || caches.match('./offline.html')))
    );
    return;
  }

  if (url.pathname.includes('/uploads/materials/')) {
    event.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(response => {
          if (response && response.ok) {
            const copy = response.clone();
            caches.open(MATERIAL_CACHE).then(cache => cache.put(request, copy));
          }
          return response;
        });
      })
    );
    return;
  }

  if (url.pathname.includes('/assets/') || url.pathname.endsWith('/manifest.json')) {
    event.respondWith(
      caches.match(request).then(cached => cached || fetch(request).then(response => {
        if (response && response.ok) {
          const copy = response.clone();
          caches.open(APP_CACHE).then(cache => cache.put(request, copy));
        }
        return response;
      }).catch(() => caches.match('./offline.html')))
    );
  }
});
