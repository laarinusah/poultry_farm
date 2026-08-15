<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Egg Production';

/*
|--------------------------------------------------------------------------
| Get Egg Production Records
|--------------------------------------------------------------------------
|
| Daily Crates:
|   crates_recorded for that particular production record.
|
| Total Crates:
|   Cumulative crates recorded up to and including that record.
|
*/

$stmt = $pdo->query("
    SELECT
        ep.id,
        ep.batch_id,
        ep.production_date,
        ep.eggs_collected,
        ep.crates_recorded,
        ep.broken_eggs,
        ep.notes,
        ep.created_at,

        pb.batch_name,

        u.full_name AS recorded_by_name,

        (
            SELECT COALESCE(
                SUM(ep2.crates_recorded),
                0
            )
            FROM egg_production ep2
            WHERE
                ep2.production_date < ep.production_date
                OR (
                    ep2.production_date = ep.production_date
                    AND ep2.id <= ep.id
                )
        ) AS cumulative_crates

    FROM egg_production ep

    INNER JOIN poultry_batches pb
        ON ep.batch_id = pb.id

    LEFT JOIN users u
        ON ep.recorded_by = u.id

    ORDER BY
        ep.production_date DESC,
        ep.id DESC
");

$records = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Summary Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(
            SUM(eggs_collected),
            0
        ) AS total_eggs,

        COALESCE(
            SUM(crates_recorded),
            0
        ) AS total_crates,

        COALESCE(
            SUM(broken_eggs),
            0
        ) AS total_broken,

        COUNT(*) AS total_records

    FROM egg_production
");

$summary = $stmt->fetch();

$totalEggs = (int) (
    $summary['total_eggs'] ?? 0
);

$totalCrates = (int) (
    $summary['total_crates'] ?? 0
);

$totalBroken = (int) (
    $summary['total_broken'] ?? 0
);

$totalRecords = (int) (
    $summary['total_records'] ?? 0
);

$goodEggs = max(
    0,
    $totalEggs - $totalBroken
);

/*
|--------------------------------------------------------------------------
| Crates Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(
            SUM(crates_recorded),
            0
        ) AS crates_today

    FROM egg_production

    WHERE production_date = CURDATE()
");

$todaySummary = $stmt->fetch();

$cratesToday = (int) (
    $todaySummary['crates_today'] ?? 0
);

/*
|--------------------------------------------------------------------------
| Eggs Today
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(
            SUM(eggs_collected),
            0
        ) AS eggs_today

    FROM egg_production

    WHERE production_date = CURDATE()
");

$todayEggSummary = $stmt->fetch();

$eggsToday = (int) (
    $todayEggSummary['eggs_today'] ?? 0
);

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>Egg Production</h2>

        <p>
            Record and monitor daily egg production.
        </p>

    </div>

    <a
        href="add.php"
        class="btn btn-primary"
    >
        Record Egg Production
    </a>

</div>


<?php if (
    isset($_GET['success'])
    && $_GET['success'] === '1'
): ?>

    <div class="alert alert-success">
        Egg production recorded successfully.
    </div>

<?php endif; ?>


<?php if (
    isset($_GET['updated'])
    && $_GET['updated'] === '1'
): ?>

    <div class="alert alert-success">
        Egg production record updated successfully.
    </div>

<?php endif; ?>


<?php if (
    isset($_GET['deleted'])
    && $_GET['deleted'] === '1'
): ?>

    <div class="alert alert-success">
        Egg production record deleted successfully.
    </div>

<?php endif; ?>


<!-- ==========================================================
     SUMMARY CARDS
=========================================================== -->

<div class="dashboard-grid">


    <!-- Total Eggs -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Eggs
        </div>

        <h6>
            Total Eggs
        </h6>

        <h3>
            <?= number($totalEggs) ?>
        </h3>

        <small>
            Eggs collected
        </small>

    </div>


    <!-- Total Crates -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Crates
        </div>

        <h6>
            Total Crates
        </h6>

        <h3>
            <?= number($totalCrates) ?>
        </h3>

        <small>
            All crates recorded
        </small>

    </div>


    <!-- Crates Today -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Today
        </div>

        <h6>
            Crates Today
        </h6>

        <h3>
            <?= number($cratesToday) ?>
        </h3>

        <small>
            Crates recorded today
        </small>

    </div>


    <!-- Good Eggs -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Good
        </div>

        <h6>
            Good Eggs
        </h6>

        <h3>
            <?= number($goodEggs) ?>
        </h3>

        <small>
            Collected minus broken
        </small>

    </div>


    <!-- Broken Eggs -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Broken
        </div>

        <h6>
            Broken Eggs
        </h6>

        <h3>
            <?= number($totalBroken) ?>
        </h3>

        <small>
            Damaged eggs recorded
        </small>

    </div>


    <!-- Production Records -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            Records
        </div>

        <h6>
            Production Records
        </h6>

        <h3>
            <?= number($totalRecords) ?>
        </h3>

        <small>
            Records entered
        </small>

    </div>

</div>


<!-- ==========================================================
     TODAY'S PRODUCTION
=========================================================== -->

<div
    class="dashboard-card"
    style="margin-top:24px;"
>

    <h3>
        Today's Production
    </h3>

    <p>
        <?= number($eggsToday) ?> eggs
        collected today from
        <?= number($cratesToday) ?> crates.
    </p>

</div>


<!-- ==========================================================
     PRODUCTION TABLE
=========================================================== -->

<div
    class="dashboard-card"
    style="margin-top:24px;"
>

    <div class="page-header">

        <div>

            <h3>
                Production Records
            </h3>

            <p>
                Daily egg production history.
            </p>

        </div>

    </div>


    <?php if (empty($records)): ?>

        <div class="empty-state">

            <div style="font-size:45px;">
                Eggs
            </div>

            <h3>
                No Egg Production Records
            </h3>

            <p>
                Start recording your daily egg production.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                Record First Production
            </a>

        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>Date</th>

                        <th>Batch</th>

                        <th>Eggs Collected</th>

                        <th>Daily Crates</th>

                        <th>Broken Eggs</th>

                        <th>Total Crates</th>

                        <th>Recorded By</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($records as $record): ?>

                        <?php

                        $collected =
                            (int) $record['eggs_collected'];

                        $dailyCrates =
                            (int) $record['crates_recorded'];

                        $broken =
                            (int) $record['broken_eggs'];

                        $good =
                            max(
                                0,
                                $collected - $broken
                            );

                        $cumulativeCrates =
                            (int) (
                                $record['cumulative_crates']
                                ?? 0
                            );

                        ?>

                        <tr>

                            <!-- Date -->

                            <td>
                                <?= e(
                                    $record['production_date']
                                ) ?>
                            </td>


                            <!-- Batch -->

                            <td>

                                <strong>
                                    <?= e(
                                        $record['batch_name']
                                    ) ?>
                                </strong>

                            </td>


                            <!-- Eggs Collected -->

                            <td>

                                <?= number(
                                    $collected
                                ) ?>

                            </td>


                            <!-- Daily Crates -->

                            <td>

                                <strong>
                                    <?= number(
                                        $dailyCrates
                                    ) ?>
                                </strong>

                            </td>


                            <!-- Broken Eggs -->

                            <td>

                                <?= number(
                                    $broken
                                ) ?>

                            </td>


                            <!-- Total Crates -->

                            <td>

                                <strong>
                                    <?= number(
                                        $cumulativeCrates
                                    ) ?>
                                </strong>

                            </td>


                            <!-- Recorded By -->

                            <td>

                                <?= e(
                                    $record['recorded_by_name']
                                    ?? 'System'
                                ) ?>

                            </td>


                            <!-- Actions -->

                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="view.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-small btn-secondary"
                                    >
                                        View
                                    </a>


                                    <a
                                        href="edit.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-small btn-warning"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="delete.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-small btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this production record?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
