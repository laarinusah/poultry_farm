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

/* Total active birds */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(current_quantity), 0)
    FROM poultry_batches
    WHERE status = 'active'
");

$totalBirds = (int) $stmt->fetchColumn();


/* Total eggs collected */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(eggs_collected), 0)
    FROM egg_production
");

$totalEggs = (int) $stmt->fetchColumn();


/* Total broken eggs */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(broken_eggs), 0)
    FROM egg_production
");

$totalBrokenEggs = (int) $stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Crates
|--------------------------------------------------------------------------
|
| Example:
|
| Today = 30 crates
| Tomorrow = 45 crates
| Total = 75 crates
|
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(crates_recorded), 0)
    FROM egg_production
");

$totalCrates = (int) $stmt->fetchColumn();


/* Crates recorded today */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(crates_recorded), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$cratesToday = (int) $stmt->fetchColumn();


/* Eggs recorded today */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(eggs_collected), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$eggsToday = (int) $stmt->fetchColumn();


/* Broken eggs recorded today */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(broken_eggs), 0)
    FROM egg_production
    WHERE production_date = CURDATE()
");

$brokenEggsToday = (int) $stmt->fetchColumn();


/* Total mortality */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(quantity), 0)
    FROM mortality
");

$totalMortality = (int) $stmt->fetchColumn();


/* Total feed used */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(quantity), 0)
    FROM feed_usage
");

$totalFeed = (float) $stmt->fetchColumn();


/* Total egg sales revenue */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(total_amount), 0)
    FROM egg_sales
");

$totalRevenue = (float) $stmt->fetchColumn();


/* Total expenses */
$stmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0)
    FROM expenses
");

$totalExpenses = (float) $stmt->fetchColumn();


/* Net income */
$netIncome = $totalRevenue - $totalExpenses;


$pageTitle = 'Dashboard';

require_once __DIR__ . '/../includes/header.php';

?>


<!-- =========================================================
     DASHBOARD CARDS
========================================================= -->

<div class="dashboard-grid">


    <!-- Total Birds -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            <i class="bi bi-feather"></i>
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
            <i class="bi bi-egg"></i>
        </div>

        <h6>Eggs Collected</h6>

        <h3>
            <?= number($totalEggs) ?>
        </h3>

        <small>
            Total eggs collected
        </small>

    </div>


    <!-- =====================================================
         CRATES TODAY
    ====================================================== -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📦
        </div>

        <h6>Crates Today</h6>

        <h3>
            <?= number($cratesToday) ?>
        </h3>

        <small>
            Crates recorded today
        </small>

    </div>


    <!-- =====================================================
         TOTAL CRATES
    ====================================================== -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📦
        </div>

        <h6>Total Crates</h6>

        <h3>
            <?= number($totalCrates) ?>
        </h3>

        <small>
            All crates recorded
        </small>

    </div>


    <!-- Broken Eggs -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            <i class="bi bi-egg"></i>
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
            <i class="bi bi-exclamation-triangle"></i>
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
            <i class="bi bi-basket"></i>
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
            <i class="bi bi-cash-stack"></i>
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
            <i class="bi bi-wallet2"></i>
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
            <i class="bi bi-graph-up-arrow"></i>
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


<!-- =========================================================
     TODAY'S PRODUCTION
========================================================= -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>

    <h3>
        Today's Production
    </h3>

    <div class="dashboard-grid">

        <div class="dashboard-card">

            <h6>🥚 Eggs Today</h6>

            <h3>
                <?= number($eggsToday) ?>
            </h3>

        </div>


        <div class="dashboard-card">

            <h6>📦 Crates Today</h6>

            <h3>
                <?= number($cratesToday) ?>
            </h3>

        </div>


        <div class="dashboard-card">

            <h6>💔 Broken Eggs Today</h6>

            <h3>
                <?= number($brokenEggsToday) ?>
            </h3>

        </div>

    </div>

</div>


<!-- =========================================================
     INFORMATION
========================================================= -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>

    <h3>
        Welcome to your Poultry Farm Dashboard
    </h3>

    <p>
        Monitor your birds, egg production, crates,
        broken eggs, mortality, feed usage, sales
        and expenses from one place.
    </p>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>