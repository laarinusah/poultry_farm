<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Get Feed Type ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Check Feed Type Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM feed_types
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$feed = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$feed) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Delete Feed Type
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM feed_types
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header('Location: index.php?deleted=1');
exit;