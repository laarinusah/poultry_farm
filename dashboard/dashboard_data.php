<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Total Active Birds
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(current_quantity), 0) AS total_birds
    FROM poultry_batches
    WHERE status = 'active'
");

$totalBirds = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Egg Production
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(eggs_collected), 0) AS total_eggs,
        COALESCE(SUM(broken_eggs), 0) AS total_broken
    FROM egg_production
");

$eggData = $stmt->fetch();

$totalEggs = (int) ($eggData['total_eggs'] ?? 0);
$totalBroken = (int) ($eggData['total_broken'] ?? 0);


/*
|--------------------------------------------------------------------------
| Crates - Total
|--------------------------------------------------------------------------
|
| This adds all crates ever recorded.
|
| Example:
|
| Day 1 = 30
| Day 2 = 45
| Day 3 = 25
|
| Total = 100
|
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(crates_recorded), 0)
    FROM egg_production
");

$totalCrates = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Crates - Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(crates_recorded), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$cratesToday = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Eggs - Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(eggs_collected), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$eggsToday = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Broken Eggs - Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(broken_eggs), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$brokenEggsToday = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Mortality
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0)
    FROM mortality
");

$totalMortality = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Mortality Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0)
    FROM mortality
    WHERE mortality_date = CURDATE()
");

$mortalityToday = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Feed Used
|--------------------------------------------------------------------------
*/

$feedUsed = 0;

try {

    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(quantity), 0)
        FROM feed_usage
    ");

    $feedUsed = (float) $stmt->fetchColumn();

} catch (Throwable $e) {

    $feedUsed = 0;
}


/*
|--------------------------------------------------------------------------
| Egg Revenue
|--------------------------------------------------------------------------
*/

$eggRevenue = 0;

try {

    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(total_amount), 0)
        FROM egg_sales
    ");

    $eggRevenue = (float) $stmt->fetchColumn();

} catch (Throwable $e) {

    $eggRevenue = 0;
}


/*
|--------------------------------------------------------------------------
| Expenses
|--------------------------------------------------------------------------
*/

$totalExpenses = 0;

try {

    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(amount), 0)
        FROM expenses
    ");

    $totalExpenses = (float) $stmt->fetchColumn();

} catch (Throwable $e) {

    $totalExpenses = 0;
}


/*
|--------------------------------------------------------------------------
| Net Income
|--------------------------------------------------------------------------
*/

$netIncome = $eggRevenue - $totalExpenses;