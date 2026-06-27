const CACHE = 'resqlink-v1';

const PRECACHE = [
    '/',
    '/css/auth.css',
    '/css/dashboard.css',
    '/css/chat.css',
    '/css/landing.css',
    '/js/theme.js',
    '/js/chat.js',
    '/images/logo.png',
];

// Install: cache core assets
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Activate: clear old caches
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Fetch: cache-first for static, network-first for API
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);

    // Skip non-GET and cross-origin
    if (e.request.method !== 'GET' || url.origin !== location.origin) return;

    // API routes: network-first, don't cache
    if (url.pathname.startsWith('/emergency') ||
        url.pathname.startsWith('/chat') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/push') ||
        url.pathname.startsWith('/bed') ||
        url.pathname.startsWith('/map/live')) {
        e.respondWith(
            fetch(e.request).catch(() =>
                new Response(JSON.stringify({ offline: true, error: 'No connection' }), {
                    headers: { 'Content-Type': 'application/json' }
                })
            )
        );
        return;
    }

    // Static assets: cache-first
    e.respondWith(
        caches.match(e.request).then(cached => {
            if (cached) return cached;
            return fetch(e.request).then(res => {
                if (res.ok && res.type !== 'opaque') {
                    caches.open(CACHE).then(c => c.put(e.request, res.clone()));
                }
                return res;
            }).catch(() => {
                // Offline fallback for navigation requests
                if (e.request.mode === 'navigate') {
                    return caches.match('/');
                }
            });
        })
    );
});

// Push notifications
self.addEventListener('push', e => {
    let data = {};
    try { data = e.data ? e.data.json() : {}; } catch (_) {}

    const title = data.title || 'ResQLink Alert';
    const options = {
        body: data.body || 'You have a new emergency notification',
        icon: '/images/logo.png',
        badge: '/images/logo.png',
        tag: data.tag || 'resqlink-alert',
        requireInteraction: data.urgent ?? false,
        data: { url: data.url || '/dashboard' },
        actions: data.actions || []
    };

    e.waitUntil(self.registration.showNotification(title, options));
});

// Notification click: open the app
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const targetUrl = e.notification.data?.url || '/dashboard';
    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(wins => {
            const match = wins.find(w => w.url.includes(location.origin));
            if (match) { match.focus(); match.navigate(targetUrl); }
            else clients.openWindow(targetUrl);
        })
    );
});
