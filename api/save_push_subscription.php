<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

$input = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data.'
    ]);

    exit;
}

$endpoint = trim(
    (string) ($input['endpoint'] ?? '')
);

$p256dh = trim(
    (string) ($input['keys']['p256dh'] ?? '')
);

$auth = trim(
    (string) ($input['keys']['auth'] ?? '')
);

if (
    $endpoint === ''
    || $p256dh === ''
    || $auth === ''
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Incomplete push subscription.'
    ]);

    exit;
}

$userId = currentUserId();

if (!$userId) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in.'
    ]);

    exit;
}

$userAgent = substr(
    $_SERVER['HTTP_USER_AGENT'] ?? '',
    0,
    500
);

try {

    $stmt = $pdo->prepare("
        INSERT INTO push_subscriptions
        (
            user_id,
            endpoint,
            p256dh,
            auth,
            user_agent
        )
        VALUES
        (
            :user_id,
            :endpoint,
            :p256dh,
            :auth,
            :user_agent
        )
        ON DUPLICATE KEY UPDATE
            p256dh = VALUES(p256dh),
            auth = VALUES(auth),
            user_agent = VALUES(user_agent),
            updated_at = CURRENT_TIMESTAMP
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':endpoint' => $endpoint,
        ':p256dh' => $p256dh,
        ':auth' => $auth,
        ':user_agent' => $userAgent
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Push notifications enabled successfully.'
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to save push subscription.'
    ]);
}