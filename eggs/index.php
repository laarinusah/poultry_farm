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
*/

$stmt = $pdo->query("
    SELECT
        ep.id,
        ep.batch_id,
        ep.production_date,
        ep.eggs_collected,
        ep.broken_eggs,
        ep.notes,
        ep.created_at,
        pb.batch_name,
        u.full_name AS recorded_by_name
    FROM egg_production ep
    INNER JOIN poultry_batches pb
        ON ep.batch_id = pb.id
    LEFT JOIN users u
        ON ep.recorded_by = u.id
    ORDER BY ep.production_date DESC, ep.id DESC
");

$records = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Summary Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(eggs_collected), 0) AS total_eggs,
        COALESCE(SUM(broken_eggs), 0) AS total_broken,
        COUNT(*) AS total_records
    FROM egg_production
");

$summary = $stmt->fetch();

$totalEggs = (int) $summary['total_eggs'];
$totalBroken = (int) $summary['total_broken'];
$totalRecords = (int) $summary['total_records'];

$goodEggs = max(0, $totalEggs - $totalBroken);

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>
        <h2>🥚 Egg Production</h2>

        <p>
            Record and monitor daily egg production.
        </p>
    </div>

    <a
        href="add.php"
        class="btn btn-primary"
    >
        + Record Egg Production
    </a>

</div>


<!-- ==========================
     SUMMARY CARDS
========================== -->

<div class="dashboard-grid">

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Total Eggs</h6>

        <h3>
            <?= number($totalEggs) ?>
        </h3>

        <small>
            Eggs collected
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
            Collected minus broken
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💔
        </div>

        <h6>Broken Eggs</h6>

        <h3>
            <?= number($totalBroken) ?>
        </h3>

        <small>
            Damaged eggs recorded
        </small>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📋
        </div>

        <h6>Production Records</h6>

        <h3>
            <?= number($totalRecords) ?>
        </h3>

        <small>
            Records entered
        </small>

    </div>

</div>


<!-- ==========================
     PRODUCTION TABLE
========================== -->

<div class="dashboard-card" style="margin-top: 24px;">

    <div class="page-header">

        <div>
            <h3>Production Records</h3>

            <p>
                Daily egg production history.
            </p>
        </div>

    </div>


    <?php if (empty($records)): ?>

        <div class="empty-state">

            <div style="font-size: 45px;">
                🥚
            </div>

            <h3>No Egg Production Records</h3>

            <p>
                Start recording your daily egg production.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                + Record First Production
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
                        <th>Broken Eggs</th>
                        <th>Good Eggs</th>
                        <th>Recorded By</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($records as $record): ?>

                    <?php
                    $collected = (int) $record['eggs_collected'];
                    $broken = (int) $record['broken_eggs'];
                    $good = max(0, $collected - $broken);
                    ?>

                    <tr>

                        <td>
                            <?= e($record['production_date']) ?>
                        </td>

                        <td>

                            <strong>
                                <?= e($record['batch_name']) ?>
                            </strong>

                        </td>

                        <td>
                            <?= number($collected) ?>
                        </td>

                        <td>
                            <?= number($broken) ?>
                        </td>

                        <td>
                            <?= number($good) ?>
                        </td>

                        <td>
                            <?= e($record['recorded_by_name'] ?? 'System') ?>
                        </td>

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


<?php require_once __DIR__ . '/../includes/footer.php'; ?>