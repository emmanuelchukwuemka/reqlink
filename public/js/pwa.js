/**
 * ResQLink PWA + Push Notification Registration
 */
(function() {
    // Register service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => {
                console.log('[ResQLink] SW registered:', reg.scope);
                // Ask for push permission after SW ready
                if ('Notification' in window && 'PushManager' in window) {
                    requestPushPermission(reg);
                }
            })
            .catch(err => console.warn('[ResQLink] SW registration failed:', err));
    }

    function requestPushPermission(reg) {
        if (Notification.permission === 'granted') {
            subscribePush(reg);
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(perm => {
                if (perm === 'granted') subscribePush(reg);
            });
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
    }

    function subscribePush(reg) {
        reg.pushManager.getSubscription().then(existing => {
            if (existing) return; // already subscribed on this device

            fetch('/push/vapid-public-key')
                .then(res => res.json())
                .then(({ key }) => {
                    if (!key) return; // VAPID not configured server-side

                    return reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(key)
                    });
                })
                .then(sub => {
                    if (!sub) return;
                    const json = sub.toJSON();
                    fetch('/push/subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                        },
                        body: JSON.stringify({
                            endpoint: json.endpoint,
                            p256dh_key: json.keys.p256dh,
                            auth_token: json.keys.auth
                        })
                    }).catch(() => {});
                })
                .catch(err => console.warn('[ResQLink] Push subscribe failed:', err));
        }).catch(() => {});
    }
})();
