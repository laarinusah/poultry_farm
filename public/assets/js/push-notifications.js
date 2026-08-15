(function () {

    'use strict';

    /*
    |--------------------------------------------------------------------------
    | Push Notification Setup
    |--------------------------------------------------------------------------
    */

    const publicKey =
        window.VAPID_PUBLIC_KEY || '';

    /*
    |--------------------------------------------------------------------------
    | Check Browser Support
    |--------------------------------------------------------------------------
    */

    if (
        !('serviceWorker' in navigator) ||
        !('PushManager' in window) ||
        !('Notification' in window)
    ) {
        console.log(
            'This browser does not support push notifications.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Convert VAPID Public Key
    |--------------------------------------------------------------------------
    */

    function urlBase64ToUint8Array(base64String) {

        const padding =
            '='.repeat(
                (4 - base64String.length % 4) % 4
            );

        const base64 =
            (
                base64String +
                padding
            )
                .replace(/-/g, '+')
                .replace(/_/g, '/');

        const rawData =
            window.atob(base64);

        return Uint8Array.from(
            [...rawData].map(
                char => char.charCodeAt(0)
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Save Subscription
    |--------------------------------------------------------------------------
    */

    async function saveSubscription(subscription) {

        const response =
            await fetch(
                '/api/save_push_subscription.php',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    credentials: 'same-origin',

                    body:
                        JSON.stringify(
                            subscription
                        )
                }
            );

        if (!response.ok) {

            throw new Error(
                'Unable to save push subscription.'
            );
        }

        return response.json();
    }


    /*
    |--------------------------------------------------------------------------
    | Register Push Notifications
    |--------------------------------------------------------------------------
    */

    async function registerPushNotifications() {

        if (!publicKey) {

            console.error(
                'VAPID public key is missing.'
            );

            return;
        }

        try {

            /*
            |--------------------------------------------------------------
            | Register Service Worker
            |--------------------------------------------------------------
            */

            const registration =
                await navigator.serviceWorker.register(
                    '/public/service-worker.js'
                );


            /*
            |--------------------------------------------------------------
            | Request Notification Permission
            |--------------------------------------------------------------
            */

            let permission =
                Notification.permission;

            if (permission === 'default') {

                permission =
                    await Notification.requestPermission();
            }

            if (permission !== 'granted') {

                console.log(
                    'Notification permission was not granted.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------
            | Check Existing Subscription
            |--------------------------------------------------------------
            */

            let subscription =
                await registration.pushManager.getSubscription();


            /*
            |--------------------------------------------------------------
            | Create New Subscription
            |--------------------------------------------------------------
            */

            if (!subscription) {

                subscription =
                    await registration.pushManager.subscribe(
                        {
                            userVisibleOnly: true,

                            applicationServerKey:
                                urlBase64ToUint8Array(
                                    publicKey
                                )
                        }
                    );
            }


            /*
            |--------------------------------------------------------------
            | Save Subscription
            |--------------------------------------------------------------
            */

            const result =
                await saveSubscription(
                    subscription.toJSON()
                );

            console.log(
                'Push notification subscription saved:',
                result
            );

        } catch (error) {

            console.error(
                'Push notification setup failed:',
                error
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Start
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'load',
        function () {

            registerPushNotifications();

        }
    );

})();