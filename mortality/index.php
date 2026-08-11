<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Get Mortality Records
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        m.id,
        m.batch_id,
        m.mortality_date,
        m.quantity,
        m.reason,
        m.notes,
        m.created_at,

        pb.batch_name,
        pb.bird_type,
        pb.breed,

        u.full_name AS recorded_by_name

    FROM mortality m

    INNER JOIN poultry_batches pb
        ON m.batch_id = pb.id

    LEFT JOIN users u
        ON m.recorded_by = u.id

    ORDER BY
        m.mortality_date DESC,
        m.id DESC
");

$records = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Summary Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0) AS total_mortality,
        COUNT(*) AS total_records
    FROM mortality
");

$summary = $stmt->fetch();

$totalMortality = (int) ($summary['total_mortality'] ?? 0);
$totalRecords = (int) ($summary['total_records'] ?? 0);


/*
|--------------------------------------------------------------------------
| Today's Mortality
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0) AS today_mortality
    FROM mortality
    WHERE mortality_date = CURDATE()
");

$today = $stmt->fetch();

$todayMortality = (int) ($today['today_mortality'] ?? 0);


$pageTitle = 'Mortality Management';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>💀 Mortality Management</h2>

        <p>
            Record and monitor bird mortality across your poultry batches.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="add.php"
            class="btn btn-danger"
        >
            ➕ Record Mortality
        </a>

    </div>

</div>



<!-- Summary Cards -->

<div class="dashboard-grid">


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💀
        </div>

        <h6>Total Mortality</h6>

        <h3>
            <?= number($totalMortality) ?>
        </h3>

        <small>
            Total birds lost
        </small>

    </div>



    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📅
        </div>

        <h6>Today's Mortality</h6>

        <h3>
            <?= number($todayMortality) ?>
        </h3>

        <small>
            Birds lost today
        </small>

    </div>



    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📋
        </div>

        <h6>Total Records</h6>

        <h3>
            <?= number($totalRecords) ?>
        </h3>

        <small>
            Mortality records
        </small>

    </div>

</div>



<!-- Mortality Records -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>


    <div class="page-header">

        <div>

            <h3>Mortality Records</h3>

            <p>
                Complete history of bird mortality.
            </p>

        </div>

    </div>



    <?php if (empty($records)): ?>


        <div class="empty-state">

            <div style="font-size: 45px;">
                💀
            </div>

            <h3>
                No Mortality Records
            </h3>

            <p>
                No bird mortality has been recorded yet.
            </p>

            <a
                href="add.php"
                class="btn btn-danger"
            >
                ➕ Record First Mortality
            </a>

        </div>


    <?php else: ?>


        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Date</th>

                        <th>Batch</th>

                        <th>Bird Type</th>

                        <th>Quantity</th>

                        <th>Reason</th>

                        <th>Recorded By</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($records as $record): ?>


                        <tr>


                            <td>
                                <?= (int) $record['id'] ?>
                            </td>


                            <td>
                                <?= e(
                                    $record['mortality_date']
                                ) ?>
                            </td>


                            <td>

                                <strong>
                                    <?= e(
                                        $record['batch_name']
                                    ) ?>
                                </strong>

                            </td>


                            <td>
                                <?= e(
                                    $record['bird_type']
                                ) ?>
                            </td>


                            <td>

                                <strong>
                                    <?= number(
                                        (int) $record['quantity']
                                    ) ?>
                                </strong>

                            </td>


                            <td>

                                <?= e(
                                    $record['reason']
                                    ?: '—'
                                ) ?>

                            </td>


                            <td>

                                <?= e(
                                    $record['recorded_by_name']
                                    ?? 'System'
                                ) ?>

                            </td>


                            <td>

                                <div class="action-buttons">


                                    <a
                                        href="view.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-sm btn-primary"
                                    >
                                        👁️ View
                                    </a>


                                    <a
                                        href="edit.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        ✏️ Edit
                                    </a>


                                    <a
                                        href="delete.php?id=<?= (int) $record['id'] ?>"
                                        class="btn btn-sm btn-danger"
                                    >
                                        🗑️ Delete
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