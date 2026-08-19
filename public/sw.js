/*
 * Task365 service worker
 * ----------------------
 * Powers the "Install the app" (PWA) experience. Deliberately conservative:
 *   - navigations: network-first, branded offline page as a fallback
 *     (authenticated page bodies are NEVER cached — no stale user data)
 *   - only neutral app assets (icons, manifest) are cached
 *   - cross-origin (CDNs: Tailwind/Alpine/Chart.js/…) untouched
 */
const CACHE = 'task365-v1';
const CORE_ASSETS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/favicon.svg',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll(CORE_ASSETS))
            .then(() => self.skipWaiting())
            .catch(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return; // leave CDNs / APIs alone

    // App pages: always the network (fresh data + valid CSRF tokens).
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Neutral static assets only: serve from cache instantly, refresh in the
    // background. Private content (file downloads, storage) is never cached.
    if (!/^\/(icons|build|css|js|fonts)\//.test(url.pathname) && !['/favicon.svg', '/manifest.webmanifest', '/offline.html'].includes(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            const network = fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        const copy = response.clone();
                        caches.open(CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => cached);
            return cached || network;
        })
    );
});
