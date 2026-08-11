<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Feed Usage Records
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        fu.id,
        fu.batch_id,
        fu.feed_type_id,
        fu.usage_date,
        fu.quantity,
        fu.notes,
        fu.created_at,

        pb.batch_name,
        pb.bird_type,

        ft.feed_name,
        ft.unit,

        u.full_name AS recorded_by_name

    FROM feed_usage fu

    INNER JOIN poultry_batches pb
        ON fu.batch_id = pb.id

    INNER JOIN feed_types ft
        ON fu.feed_type_id = ft.id

    LEFT JOIN users u
        ON fu.recorded_by = u.id

    ORDER BY
        fu.usage_date DESC,
        fu.id DESC
");

$records = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Total Feed Used
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0) AS total_feed,
        COUNT(*) AS total_records
    FROM feed_usage
");

$summary = $stmt->fetch();

$totalFeed = (float) ($summary['total_feed'] ?? 0);
$totalRecords = (int) ($summary['total_records'] ?? 0);


/*
|--------------------------------------------------------------------------
| Today's Feed Usage
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0) AS today_feed
    FROM feed_usage
    WHERE usage_date = CURDATE()
");

$today = $stmt->fetch();

$todayFeed = (float) ($today['today_feed'] ?? 0);


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Feed Management';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🌾 Feed Management</h2>

        <p>
            Record and monitor feed used by your poultry batches.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="add.php"
            class="btn btn-primary"
        >
            ➕ Record Feed Usage
        </a>

    </div>

</div>



<!-- Summary Cards -->

<div class="dashboard-grid">


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🌾
        </div>

        <h6>Total Feed Used</h6>

        <h3>
            <?= number_format($totalFeed, 2) ?>
        </h3>

        <small>
            Total quantity recorded
        </small>

    </div>



    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📅
        </div>

        <h6>Today's Usage</h6>

        <h3>
            <?= number_format($todayFeed, 2) ?>
        </h3>

        <small>
            Feed used today
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
            Feed usage records
        </small>

    </div>

</div>



<!-- Feed Records -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>


    <div class="page-header">

        <div>

            <h3>Feed Usage Records</h3>

            <p>
                Complete history of feed consumption.
            </p>

        </div>

    </div>



    <?php if (empty($records)): ?>


        <div class="empty-state">

            <div style="font-size: 45px;">
                🌾
            </div>

            <h3>
                No Feed Records
            </h3>

            <p>
                No feed usage has been recorded yet.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                ➕ Record First Feed Usage
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

                        <th>Feed Type</th>

                        <th>Quantity</th>

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
                                <?= e($record['usage_date']) ?>
                            </td>


                            <td>

                                <strong>
                                    <?= e($record['batch_name']) ?>
                                </strong>

                                <br>

                                <small>
                                    <?= e($record['bird_type']) ?>
                                </small>

                            </td>


                            <td>
                                <?= e($record['feed_name']) ?>
                            </td>


                            <td>

                                <strong>
                                    <?= number_format(
                                        (float) $record['quantity'],
                                        2
                                    ) ?>
                                </strong>

                                <?= e(
                                    $record['unit'] ?? 'bag'
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