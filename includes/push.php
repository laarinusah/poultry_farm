<?php

declare(strict_types=1);

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';


/**
 * Send a push notification to all registered devices
 * belonging to users other than the current user.
 */
function sendPushToOtherUsers(
    string $title,
    string $message,
    string $type = 'info',
    ?string $relatedUrl = null
): void {

    global $pdo;

    


    /*
    |--------------------------------------------------------------------------
    | Get VAPID configuration
    |--------------------------------------------------------------------------
    */

    $publicKey = defined('VAPID_PUBLIC_KEY')
        ? VAPID_PUBLIC_KEY
        : '';

    $privateKey = defined('VAPID_PRIVATE_KEY')
        ? VAPID_PRIVATE_KEY
        : '';

    if (
        $publicKey === ''
        || $privateKey === ''
    ) {

        error_log(
            'Push notification error: VAPID keys are missing.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Get active user subscriptions
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            ps.id,
            ps.user_id,
            ps.endpoint,
            ps.p256dh,
            ps.auth
        FROM push_subscriptions ps
        INNER JOIN users u
            ON u.id = ps.user_id
        WHERE u.status = 'active'
    ";

    $params = [];

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $subscriptions =
        $stmt->fetchAll(PDO::FETCH_ASSOC);


    if (!$subscriptions) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Web Push Client
    |--------------------------------------------------------------------------
    |
    | Minishlink may emit a recommendation about GMP/BCMath.
    | Capture that output so it cannot break HTTP headers/redirects.
    |--------------------------------------------------------------------------
    */

    ob_start();

    try {

        $webPush = new WebPush([
            'VAPID' => [
                'subject' =>
                    'mailto:betterlivlord@gmail.com',

                'publicKey' =>
                    $publicKey,

                'privateKey' =>
                    $privateKey,
            ],
        ]);

    } catch (Throwable $e) {

        ob_end_clean();

        error_log(
            'Push notification client error: '
            . $e->getMessage()
        );

        return;

    }

    /*
     * Discard any library notice/output.
     */
    ob_end_clean();


    /*
    |--------------------------------------------------------------------------
    | Notification Payload
    |--------------------------------------------------------------------------
    */

    $payload = json_encode(
        [
            'title' => $title,

            'message' => $message,

            'type' => $type,

            'related_url' => $relatedUrl,
        ],
        JSON_UNESCAPED_SLASHES
    );


    if ($payload === false) {

        error_log(
            'Push notification error: '
            . 'unable to encode payload.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Queue Notifications
    |--------------------------------------------------------------------------
    */

    foreach ($subscriptions as $row) {

        try {

            $subscription =
                Subscription::create([
                    'endpoint' =>
                        $row['endpoint'],

                    'keys' => [
                        'p256dh' =>
                            $row['p256dh'],

                        'auth' =>
                            $row['auth'],
                    ],
                ]);


            $webPush->queueNotification(
                $subscription,
                $payload
            );


        } catch (Throwable $e) {

            error_log(
                'Push subscription error: '
                . $e->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Send Queued Notifications
    |--------------------------------------------------------------------------
    */

    /*
     * Capture any output produced by the Web Push
     * library while flushing notifications.
     */
    ob_start();

    try {

        $reports = $webPush->flush();

    } catch (Throwable $e) {

        ob_end_clean();

        error_log(
            'Push notification flush error: '
            . $e->getMessage()
        );

        return;
    }

    /*
     * Discard library output.
     */
    ob_end_clean();


    /*
    |--------------------------------------------------------------------------
    | Process Delivery Reports
    |--------------------------------------------------------------------------
    */

    foreach ($reports as $report) {

        if (!$report->isSuccess()) {

            error_log(
                'Push notification failed: '
                . $report->getReason()
            );

            continue;
        }

        error_log(
            'Push notification sent successfully.'
        );
    }
}
