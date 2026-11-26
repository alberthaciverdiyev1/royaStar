/* Service worker for Web Push notifications (RoyaStar). */

self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
    var payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch (e) {
        payload = { body: event.data ? event.data.text() : '' };
    }

    var title = payload.title || 'RoyaStar';
    var options = {
        body: payload.body || '',
        icon: payload.icon || '/images/notifications/icon-192.png',
        badge: payload.badge || '/images/notifications/badge-96.png',
        tag: payload.tag || undefined,
        data: { url: payload.url || '/notifications' },
        vibrate: [80, 40, 80],
        requireInteraction: false
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = (event.notification.data && event.notification.data.url) || '/notifications';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];

                if ('focus' in client) {
                    if ('navigate' in client) {
                        client.navigate(url);
                    }
                    return client.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
