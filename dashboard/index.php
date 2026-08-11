<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

// Total birds currently in the farm
$stmt = $pdo->query("
    SELECT COALESCE(SUM(current_quantity), 0) AS total_birds
    FROM poultry_batches
    WHERE status = 'active'
");

$totalBirds = (int) $stmt->fetch()['total_birds'];


// Total eggs collected
$stmt = $pdo->query("
    SELECT COALESCE(SUM(eggs_collected), 0) AS total_eggs
    FROM egg_production
");

$totalEggs = (int) $stmt->fetch()['total_eggs'];


// Total broken eggs
$stmt = $pdo->query("
    SELECT COALESCE(SUM(broken_eggs), 0) AS total_broken_eggs
    FROM egg_production
");

$totalBrokenEggs = (int) $stmt->fetch()['total_broken_eggs'];


// Total mortality
$stmt = $pdo->query("
    SELECT COALESCE(SUM(quantity), 0) AS total_mortality
    FROM mortality
");

$totalMortality = (int) $stmt->fetch()['total_mortality'];


// Total feed used
$stmt = $pdo->query("
    SELECT COALESCE(SUM(quantity), 0) AS total_feed
    FROM feed_usage
");

$totalFeed = (float) $stmt->fetch()['total_feed'];


// Total egg sales revenue
$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM egg_sales
");

$totalRevenue = (float) $stmt->fetch()['total_revenue'];


// Total expenses
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) AS total_expenses
    FROM expenses
");

$totalExpenses = (float) $stmt->fetch()['total_expenses'];


// Net income
$netIncome = $totalRevenue - $totalExpenses;


/*
|--------------------------------------------------------------------------
| Page Layout
|--------------------------------------------------------------------------
*/

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';

?>

<!-- =========================
     DASHBOARD CARDS
========================== -->

<div class="dashboard-grid">

    <!-- Total Birds -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🐔
        </div>

        <h6>Total Birds</h6>

        <h3>
            <?= number($totalBirds) ?>
        </h3>

        <small>
            Currently active birds
        </small>

    </div>


    <!-- Eggs Collected -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Eggs Collected</h6>

        <h3>
            <?= number($totalEggs) ?>
        </h3>

        <small>
            Total eggs collected
        </small>

    </div>


    <!-- Broken Eggs -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Broken Eggs</h6>

        <h3>
            <?= number($totalBrokenEggs) ?>
        </h3>

        <small>
            Total broken eggs
        </small>

    </div>


    <!-- Mortality -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💀
        </div>

        <h6>Mortality</h6>

        <h3>
            <?= number($totalMortality) ?>
        </h3>

        <small>
            Total bird deaths
        </small>

    </div>


    <!-- Feed Used -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🌾
        </div>

        <h6>Feed Used</h6>

        <h3>
            <?= number($totalFeed) ?>
        </h3>

        <small>
            Bags recorded
        </small>

    </div>


    <!-- Egg Revenue -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💰
        </div>

        <h6>Egg Revenue</h6>

        <h3>
            <?= money($totalRevenue) ?>
        </h3>

        <small>
            Total egg sales
        </small>

    </div>


    <!-- Expenses -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💸
        </div>

        <h6>Expenses</h6>

        <h3>
            <?= money($totalExpenses) ?>
        </h3>

        <small>
            Total expenses
        </small>

    </div>


    <!-- Net Income -->
    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📈
        </div>

        <h6>Net Income</h6>

        <h3>
            <?= money($netIncome) ?>
        </h3>

        <small>
            Revenue minus expenses
        </small>

    </div>

</div>


<!-- =========================
     DASHBOARD INFORMATION
========================== -->

<div class="dashboard-card" style="margin-top: 24px;">

    <h3>
        Welcome to your Poultry Farm Dashboard
    </h3>

    <p>
        Monitor your birds, egg production, broken eggs,
        mortality, feed usage, sales and expenses from one place.
    </p>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>