<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Get Egg Sales
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        es.id,
        es.sale_date,
        es.quantity,
        es.unit_price,
        es.total_amount,
        es.customer_name,
        es.payment_method,
        es.notes,
        es.created_at,
        pb.batch_name
    FROM egg_sales es
    LEFT JOIN poultry_batches pb
        ON es.batch_id = pb.id
    ORDER BY
        es.sale_date DESC,
        es.id DESC
");

$sales = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Sales Summary
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(quantity), 0) AS total_quantity,
        COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM egg_sales
");

$summary = $stmt->fetch();

$totalQuantity = (int) (
    $summary['total_quantity'] ?? 0
);

$totalRevenue = (float) (
    $summary['total_revenue'] ?? 0
);

$pageTitle = 'Egg Sales';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>💰 Egg Sales</h2>

        <p>
            Record and manage all egg sales from the farm.
        </p>

    </div>

    <div class="action-buttons">

        <a
            href="add.php"
            class="btn btn-primary"
        >
            ➕ Record Egg Sale
        </a>

    </div>

</div>


<?php if (
    isset($_GET['deleted'])
    && $_GET['deleted'] === '1'
): ?>

    <div class="alert alert-success">
        ✅ Egg sale deleted successfully.
    </div>

<?php endif; ?>


<?php if (
    isset($_GET['updated'])
    && $_GET['updated'] === '1'
): ?>

    <div class="alert alert-success">
        ✅ Egg sale updated successfully.
    </div>

<?php endif; ?>


<?php if (
    isset($_GET['success'])
    && $_GET['success'] === '1'
): ?>

    <div class="alert alert-success">
        ✅ Egg sale recorded successfully.
    </div>

<?php endif; ?>


<!-- ==========================================================
     SUMMARY CARDS
=========================================================== -->

<div class="dashboard-grid">

    <!-- Crates Sold -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>
            Crates Sold
        </h6>

        <h3>
            <?= number($totalQuantity) ?>
        </h3>

        <small>
            Total crates sold
        </small>

    </div>


    <!-- Total Revenue -->

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💰
        </div>

        <h6>
            Total Revenue
        </h6>

        <h3>
            <?= money($totalRevenue) ?>
        </h3>

        <small>
            Total crate sales revenue
        </small>

    </div>

</div>


<!-- ==========================================================
     SALES RECORDS
=========================================================== -->

<div class="dashboard-card">

    <div class="page-header">

        <div>

            <h3>
                📋 Sales Records
            </h3>

        </div>

    </div>


    <?php if (empty($sales)): ?>

        <div class="alert alert-info">

            No crate sales have been recorded yet.

            <br><br>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                ➕ Record First Sale
            </a>

        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Date</th>

                        <th>Customer</th>

                        <th>Batch</th>

                        <th>Crates Sold</th>

                        <th>Price Per Crate</th>

                        <th>Total Revenue</th>

                        <th>Payment</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($sales as $sale): ?>

                        <tr>

                            <td>
                                <?= (int) $sale['id'] ?>
                            </td>


                            <td>
                                <?= e($sale['sale_date']) ?>
                            </td>


                            <td>

                                <?php if (
                                    !empty($sale['customer_name'])
                                ): ?>

                                    <?= e(
                                        $sale['customer_name']
                                    ) ?>

                                <?php else: ?>

                                    <span>
                                        Walk-in Customer
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= e(
                                    $sale['batch_name']
                                    ?? 'Not specified'
                                ) ?>

                            </td>


                            <td>

                                <?= number(
                                    (int) $sale['quantity']
                                ) ?>

                                crates

                            </td>


                            <td>

                                <?= money(
                                    (float) $sale['unit_price']
                                ) ?>

                            </td>


                            <td>

                                <strong>

                                    <?= money(
                                        (float) $sale['total_amount']
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?php

                                $paymentLabels = [

                                    'cash' =>
                                        'Cash',

                                    'mobile_money' =>
                                        'Mobile Money',

                                    'bank' =>
                                        'Bank',

                                    'other' =>
                                        'Other'

                                ];

                                $payment =
                                    $sale['payment_method']
                                    ?? 'cash';

                                echo e(
                                    $paymentLabels[$payment]
                                    ?? ucfirst($payment)
                                );

                                ?>

                            </td>


                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="view.php?id=<?= (int) $sale['id'] ?>"
                                        class="btn btn-secondary"
                                        title="View Sale"
                                    >
                                        👁️
                                    </a>


                                    <a
                                        href="edit.php?id=<?= (int) $sale['id'] ?>"
                                        class="btn btn-primary"
                                        title="Edit Sale"
                                    >
                                        ✏️
                                    </a>


                                    <a
                                        href="delete.php?id=<?= (int) $sale['id'] ?>"
                                        class="btn btn-danger"
                                        title="Delete Sale"
                                    >
                                        🗑️
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>


                <tfoot>

                    <tr>

                        <th colspan="4">
                            TOTAL
                        </th>

                        <th>

                            <?= number(
                                $totalQuantity
                            ) ?>

                            crates

                        </th>

                        <th>
                            —
                        </th>

                        <th>

                            <?= money(
                                $totalRevenue
                            ) ?>

                        </th>

                        <th colspan="2">
                            —
                        </th>

                    </tr>

                </tfoot>

            </table>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>