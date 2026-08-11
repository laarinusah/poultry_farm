<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Sale
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        es.id,
        es.sale_date,
        es.batch_id,
        es.quantity,
        es.unit_price,
        es.total_amount,
        es.customer_name,
        es.payment_method,
        es.notes,
        es.created_at,

        pb.batch_name,
        pb.bird_type

    FROM egg_sales es

    LEFT JOIN poultry_batches pb
        ON es.batch_id = pb.id

    WHERE es.id = :id

    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$sale = $stmt->fetch();


if (!$sale) {
    header('Location: index.php');
    exit;
}


$pageTitle = 'View Egg Sale';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>👁️ Egg Sale Details</h2>

        <p>
            Complete information about this egg sale.
        </p>

    </div>


    <div
        style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        "
    >

        <a
            href="edit.php?id=<?= (int)$sale['id'] ?>"
            class="btn btn-primary"
        >
            ✏️ Edit
        </a>


        <a
            href="delete.php?id=<?= (int)$sale['id'] ?>"
            class="btn btn-danger"
            onclick="return confirm('Are you sure you want to delete this egg sale?');"
        >
            🗑️ Delete
        </a>


        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Sales
        </a>

    </div>

</div>



<div class="dashboard-card">


    <div class="details-grid">


        <!-- Sale ID -->

        <div class="detail-item">

            <strong>🆔 Sale ID</strong>

            <span>
                #<?= (int)$sale['id'] ?>
            </span>

        </div>



        <!-- Sale Date -->

        <div class="detail-item">

            <strong>📅 Sale Date</strong>

            <span>
                <?= e($sale['sale_date']) ?>
            </span>

        </div>



        <!-- Poultry Batch -->

        <div class="detail-item">

            <strong>🐔 Poultry Batch</strong>

            <span>

                <?php if (!empty($sale['batch_name'])): ?>

                    <?= e($sale['batch_name']) ?>

                    <?php if (!empty($sale['bird_type'])): ?>

                        <small>
                            (<?= e($sale['bird_type']) ?>)
                        </small>

                    <?php endif; ?>

                <?php else: ?>

                    Not specified

                <?php endif; ?>

            </span>

        </div>



        <!-- Quantity -->

        <div class="detail-item">

            <strong>🥚 Quantity Sold</strong>

            <span>
                <?= number((int)$sale['quantity']) ?>
                eggs
            </span>

        </div>



        <!-- Unit Price -->

        <div class="detail-item">

            <strong>💵 Unit Price</strong>

            <span>
                <?= money((float)$sale['unit_price']) ?>
            </span>

        </div>



        <!-- Total -->

        <div class="detail-item">

            <strong>💰 Total Amount</strong>

            <span>

                <strong>
                    <?= money((float)$sale['total_amount']) ?>
                </strong>

            </span>

        </div>



        <!-- Customer -->

        <div class="detail-item">

            <strong>👤 Customer</strong>

            <span>

                <?php if (!empty($sale['customer_name'])): ?>

                    <?= e($sale['customer_name']) ?>

                <?php else: ?>

                    Walk-in Customer

                <?php endif; ?>

            </span>

        </div>



        <!-- Payment Method -->

        <div class="detail-item">

            <strong>💳 Payment Method</strong>

            <span>

                <?php

                $paymentLabels = [
                    'cash' => 'Cash',
                    'mobile_money' => 'Mobile Money',
                    'bank' => 'Bank',
                    'other' => 'Other'
                ];

                echo e(
                    $paymentLabels[
                        $sale['payment_method']
                    ]
                    ?? ucfirst(
                        (string)$sale['payment_method']
                    )
                );

                ?>

            </span>

        </div>



        <!-- Notes -->

        <div
            class="detail-item"
            style="grid-column:1 / -1;"
        >

            <strong>📝 Notes</strong>

            <span>

                <?php if (!empty($sale['notes'])): ?>

                    <?= nl2br(
                        e($sale['notes'])
                    ) ?>

                <?php else: ?>

                    <span style="color:#777;">
                        No notes provided.
                    </span>

                <?php endif; ?>

            </span>

        </div>



        <!-- Recorded -->

        <div class="detail-item">

            <strong>🕒 Recorded At</strong>

            <span>
                <?= e($sale['created_at']) ?>
            </span>

        </div>


    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>