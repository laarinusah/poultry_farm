<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Escape HTML Output Safely
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Farm Settings
|--------------------------------------------------------------------------
|
| Reads the settings saved in the farm_settings table.
| The result is cached during the current request so we
| don't query the database repeatedly.
|
*/

function farmSettings(): array
{
    global $pdo;

    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = [
        'farm_name' =>
            defined('DEFAULT_FARM_NAME')
                ? DEFAULT_FARM_NAME
                : 'Poultry Farm',

        'farm_location' =>
            'Ghana',

        'phone' =>
            null,

        'email' =>
            null,

        'currency' =>
            defined('DEFAULT_CURRENCY')
                ? DEFAULT_CURRENCY
                : 'GHS',

        'timezone' =>
            defined('DEFAULT_TIMEZONE')
                ? DEFAULT_TIMEZONE
                : 'Africa/Accra'
    ];


    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */

    if (!isset($pdo)) {
        return $settings;
    }


    try {

        $stmt = $pdo->query("
            SELECT
                farm_name,
                farm_location,
                phone,
                email,
                currency,
                timezone
            FROM farm_settings
            ORDER BY id ASC
            LIMIT 1
        ");

        $databaseSettings =
            $stmt->fetch(PDO::FETCH_ASSOC);


        if ($databaseSettings) {

            $settings = array_merge(
                $settings,
                $databaseSettings
            );
        }

    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Keep Default Settings
        |--------------------------------------------------------------------------
        |
        | If the database is temporarily unavailable,
        | the application can still use the defaults.
        |
        */

    }


    return $settings;
}


/*
|--------------------------------------------------------------------------
| Farm Name
|--------------------------------------------------------------------------
*/

function farmName(): string
{
    return (string) (
        farmSettings()['farm_name']
        ?? 'Poultry Farm'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Location
|--------------------------------------------------------------------------
*/

function farmLocation(): string
{
    return (string) (
        farmSettings()['farm_location']
        ?? 'Ghana'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Phone
|--------------------------------------------------------------------------
*/

function farmPhone(): ?string
{
    $phone = farmSettings()['phone'] ?? null;

    return $phone !== null && $phone !== ''
        ? (string)$phone
        : null;
}


/*
|--------------------------------------------------------------------------
| Farm Email
|--------------------------------------------------------------------------
*/

function farmEmail(): ?string
{
    $email = farmSettings()['email'] ?? null;

    return $email !== null && $email !== ''
        ? (string)$email
        : null;
}


/*
|--------------------------------------------------------------------------
| Farm Currency
|--------------------------------------------------------------------------
*/

function farmCurrency(): string
{
    return (string) (
        farmSettings()['currency']
        ?? 'GHS'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Timezone
|--------------------------------------------------------------------------
*/

function farmTimezone(): string
{
    return (string) (
        farmSettings()['timezone']
        ?? 'Africa/Accra'
    );
}


/*
|--------------------------------------------------------------------------
| Format Money
|--------------------------------------------------------------------------
|
| Example:
|
| GHS 1,500.00
| USD 1,500.00
| EUR 1,500.00
|
*/

function money(float|int $amount): string
{
    return farmCurrency()
        . ' '
        . number_format(
            (float)$amount,
            2
        );
}


/*
|--------------------------------------------------------------------------
| Format Numbers
|--------------------------------------------------------------------------
*/

function number(int|float $value): string
{
    return number_format(
        (float)$value
    );
}
/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

/**
 * Send a notification to another user.
 *
 * The current user is excluded automatically.
 */
require_once __DIR__ . '/push.php';
function notifyOtherUsers(
    string $title,
    string $message,
    string $type = 'info',
    ?string $relatedUrl = null
): void {

    global $pdo;

    $currentUserId = currentUserId();

    $stmt = $pdo->prepare("
        INSERT INTO notifications
        (
            user_id,
            title,
            message,
            type,
            related_url
        )
        SELECT
            id,
            ?,
            ?,
            ?,
            ?
        FROM users
        WHERE status = 'active'
        AND id != ?
    ");

        $stmt->execute([
        $title,
        $message,
        $type,
        $relatedUrl,
        $currentUserId ?? 0
    ]);

    /*
    |--------------------------------------------------------------------------
    | Send Push Notification
    |--------------------------------------------------------------------------
    */

    sendPushToOtherUsers(
        $title,
        $message,
        $type,
        $relatedUrl
    );
}


/**
 * Get the number of unread notifications
 * for the current user.
 */
function unreadNotificationCount(): int
{
    global $pdo;

    $userId = currentUserId();

    if (!$userId) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ");

    $stmt->execute([
        $userId
    ]);

    return (int) $stmt->fetchColumn();
}


/**
 * Get recent notifications
 * for the current user.
 */
function getRecentNotifications(int $limit = 10): array
{
    global $pdo;

    $userId = currentUserId();

    if (!$userId) {
        return [];
    }

    $limit = max(1, min($limit, 50));

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            message,
            type,
            related_url,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT {$limit}
    ");

    $stmt->execute([
        $userId
    ]);

    return $stmt->fetchAll();
}


/**
 * Mark one notification as read.
 */
function markNotificationAsRead(int $notificationId): bool
{
    global $pdo;

    $userId = currentUserId();

    if (!$userId) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ");

    return $stmt->execute([
        $notificationId,
        $userId
    ]);
}


/**
 * Mark all notifications as read.
 */
function markAllNotificationsAsRead(): bool
{
    global $pdo;

    $userId = currentUserId();

    if (!$userId) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
        AND is_read = 0
    ");

    return $stmt->execute([
        $userId
    ]);
}