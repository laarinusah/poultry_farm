self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    let data;

    try {
        data = event.data.json();
    } catch (error) {
        data = {
            title: 'Golden Eggs Poultry Farm',
            message: event.data.text()
        };
    }

    const title =
        data.title ||
        'Golden Eggs Poultry Farm';

    const options = {
        body:
            data.message ||
            'You have a new farm notification.',

        icon:
            '/public/assets/images/icon-192.png',

        badge:
            '/public/assets/images/icon-192.png',

        data: {
            url:
                data.related_url ||
                '/dashboard/index.php'
        },

        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification(
            title,
            options
        )
    );
});


self.addEventListener(
    'notificationclick',
    function (event) {

        event.notification.close();

        const url =
            event.notification.data &&
            event.notification.data.url
                ? event.notification.data.url
                : '/dashboard/index.php';

        event.waitUntil(
            clients.matchAll({
                type: 'window',
                includeUncontrolled: true
            }).then(function (clientList) {

                for (const client of clientList) {

                    if (
                        'focus' in client
                    ) {
                        client.navigate(url);
                        return client.focus();
                    }
                }

                if (
                    clients.openWindow
                ) {
                    return clients.openWindow(url);
                }

            })
        );
    }
);