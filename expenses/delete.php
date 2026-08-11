<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check Expense Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM expenses
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Delete Expense
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    DELETE FROM expenses
    WHERE id = ?
");

$stmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| Return to Expenses
|--------------------------------------------------------------------------
*/

header('Location: index.php?deleted=1');
exit;