<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Expense
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        expense_date,
        category,
        description,
        amount,
        payment_method,
        recorded_by,
        created_at
    FROM expenses
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$expense = $stmt->fetch();

if (!$expense) {
    header('Location: index.php');
    exit;
}


$pageTitle = 'View Expense';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>👁️ Expense Details</h2>

        <p>
            Complete information about this farm expense.
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
            href="edit.php?id=<?= (int)$expense['id'] ?>"
            class="btn btn-primary"
        >
            ✏️ Edit
        </a>

        <a
            href="delete.php?id=<?= (int)$expense['id'] ?>"
            class="btn btn-danger"
            onclick="return confirm('Are you sure you want to delete this expense?');"
        >
            🗑️ Delete
        </a>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Expenses
        </a>

    </div>

</div>


<div class="dashboard-card">

    <div class="details-grid">


        <!-- ID -->

        <div class="detail-item">

            <strong>🆔 Expense ID</strong>

            <span>
                #<?= (int)$expense['id'] ?>
            </span>

        </div>


        <!-- Date -->

        <div class="detail-item">

            <strong>📅 Expense Date</strong>

            <span>
                <?= e($expense['expense_date']) ?>
            </span>

        </div>


        <!-- Category -->

        <div class="detail-item">

            <strong>🏷️ Category</strong>

            <span>
                <?= e($expense['category']) ?>
            </span>

        </div>


        <!-- Amount -->

        <div class="detail-item">

            <strong>💰 Amount</strong>

            <span>

                <strong>
                    <?= money((float)$expense['amount']) ?>
                </strong>

            </span>

        </div>


        <!-- Description -->

        <div
            class="detail-item"
            style="grid-column:1 / -1;"
        >

            <strong>📝 Description</strong>

            <span>
                <?= e($expense['description']) ?>
            </span>

        </div>


        <!-- Payment -->

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
                        $expense['payment_method']
                    ]
                    ??
                    ucfirst(
                        (string)$expense['payment_method']
                    )
                );

                ?>

            </span>

        </div>


        <!-- Recorded By -->

        <div class="detail-item">

            <strong>👤 Recorded By</strong>

            <span>

                <?php if (!empty($expense['recorded_by'])): ?>

                    User #<?= (int)$expense['recorded_by'] ?>

                <?php else: ?>

                    System

                <?php endif; ?>

            </span>

        </div>


        <!-- Created -->

        <div class="detail-item">

            <strong>🕒 Recorded At</strong>

            <span>
                <?= e($expense['created_at']) ?>
            </span>

        </div>


    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>