<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check that the sale exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM egg_sales
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$sale = $stmt->fetch();

if (!$sale) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete Sale
|--------------------------------------------------------------------------
*/

$delete = $pdo->prepare("
    DELETE FROM egg_sales
    WHERE id = :id
");

$delete->execute([
    ':id' => $id
]);

/*
|--------------------------------------------------------------------------
| Return to Sales
|--------------------------------------------------------------------------
*/

header('Location: index.php?deleted=1');
exit;