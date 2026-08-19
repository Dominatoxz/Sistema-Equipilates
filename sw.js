self.addEventListener('push', function(event) {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body,
        icon: data.icon || '🔵',
        badge: 'assets/logo_equipilates.png',
        data: data.data,
        vibrate: [100, 50, 100]
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});