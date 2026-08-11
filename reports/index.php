<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Reports';

/*
|--------------------------------------------------------------------------
| Date Filter
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? $today;


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $startDate = date('Y-m-01');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $endDate = $today;
}


/*
|--------------------------------------------------------------------------
| Egg Production
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(eggs_collected), 0) AS total_eggs,
        COALESCE(SUM(broken_eggs), 0) AS broken_eggs
    FROM egg_production
    WHERE production_date BETWEEN ? AND ?
");

$stmt->execute([
    $startDate,
    $endDate
]);

$eggData = $stmt->fetch(PDO::FETCH_ASSOC);

$totalEggs = (int)($eggData['total_eggs'] ?? 0);
$brokenEggs = (int)($eggData['broken_eggs'] ?? 0);

$goodEggs = max(
    0,
    $totalEggs - $brokenEggs
);


/*
|--------------------------------------------------------------------------
| Mortality
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0)
    FROM mortality
    WHERE mortality_date BETWEEN ? AND ?
");

$stmt->execute([
    $startDate,
    $endDate
]);

$totalMortality = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Feed Usage
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(quantity), 0)
    FROM feed_usage
    WHERE usage_date BETWEEN ? AND ?
");

$stmt->execute([
    $startDate,
    $endDate
]);

$totalFeed = (float)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Egg Sales
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount), 0)
    FROM egg_sales
    WHERE sale_date BETWEEN ? AND ?
");

$stmt->execute([
    $startDate,
    $endDate
]);

$totalSales = (float)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Expenses
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM expenses
    WHERE expense_date BETWEEN ? AND ?
");

$stmt->execute([
    $startDate,
    $endDate
]);

$totalExpenses = (float)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Net Income
|--------------------------------------------------------------------------
*/

$netIncome = $totalSales - $totalExpenses;


/*
|--------------------------------------------------------------------------
| Current Birds
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COALESCE(SUM(current_quantity), 0)
    FROM poultry_batches
    WHERE status = 'active'
");

$currentBirds = (int)$stmt->fetchColumn();


require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>📊 Farm Reports</h2>

        <p>
            View production, operational and financial performance.
        </p>

    </div>

</div>


<!-- DATE FILTER -->

<div class="dashboard-card">

    <h3>
        📅 Report Period
    </h3>

    <form
        method="GET"
        style="
            display:flex;
            gap:15px;
            flex-wrap:wrap;
            align-items:end;
        "
    >

        <div class="form-group">

            <label for="start_date">
                Start Date
            </label>

            <input
                type="date"
                id="start_date"
                name="start_date"
                value="<?= e($startDate) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="end_date">
                End Date
            </label>

            <input
                type="date"
                id="end_date"
                name="end_date"
                value="<?= e($endDate) ?>"
                required
            >

        </div>


        <button
            type="submit"
            class="btn btn-primary"
        >
            🔍 Generate Report
        </button>

    </form>

</div>


<!-- OPERATIONAL SUMMARY -->

<h3 class="section-title">
    🐔 Farm Operations
</h3>


<div class="dashboard-grid">


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🐔
        </div>

        <h6>Current Birds</h6>

        <h3>
            <?= number($currentBirds) ?>
        </h3>

        <small>
            Active birds
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Eggs Collected</h6>

        <h3>
            <?= number($totalEggs) ?>
        </h3>

        <small>
            Selected period
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Good Eggs</h6>

        <h3>
            <?= number($goodEggs) ?>
        </h3>

        <small>
            After broken eggs
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💔
        </div>

        <h6>Broken Eggs</h6>

        <h3>
            <?= number($brokenEggs) ?>
        </h3>

        <small>
            Selected period
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💀
        </div>

        <h6>Mortality</h6>

        <h3>
            <?= number($totalMortality) ?>
        </h3>

        <small>
            Birds lost
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🌾
        </div>

        <h6>Feed Used</h6>

        <h3>
            <?= number($totalFeed) ?>
        </h3>

        <small>
            Recorded quantity
        </small>

    </div>

</div>


<!-- FINANCIAL SUMMARY -->

<h3 class="section-title">
    💰 Financial Summary
</h3>


<div class="dashboard-grid">


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💰
        </div>

        <h6>Egg Sales</h6>

        <h3>
            <?= money($totalSales) ?>
        </h3>

        <small>
            Selected period
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💸
        </div>

        <h6>Expenses</h6>

        <h3>
            <?= money($totalExpenses) ?>
        </h3>

        <small>
            Selected period
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📈
        </div>

        <h6>Net Income</h6>

        <h3>
            <?= money($netIncome) ?>
        </h3>

        <small>
            Sales minus expenses
        </small>

    </div>

</div>


<!-- REPORT PERIOD -->

<div class="dashboard-card">

    <h3>
        📋 Report Information
    </h3>

    <p>
        Report from
        <strong><?= e($startDate) ?></strong>
        to
        <strong><?= e($endDate) ?></strong>.
    </p>

</div>


<style>

.section-title {
    margin-top: 30px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
}

.form-group input {
    padding: 11px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
}

@media (max-width: 768px) {

    .form-group {
        width: 100%;
    }

    .form-group input {
        width: 100%;
        box-sizing: border-box;
        font-size: 16px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>