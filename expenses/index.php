<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Get Expenses
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        expense_date,
        category,
        description,
        amount,
        payment_method,
        created_at
    FROM expenses
    ORDER BY expense_date DESC, id DESC
");

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Total Expenses
|--------------------------------------------------------------------------
*/

$totalStmt = $pdo->query("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM expenses
");

$totalExpenses = (float) $totalStmt->fetch()['total'];


$pageTitle = 'Expenses';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>💸 Expenses</h2>

        <p>
            Manage and track all farm expenses.
        </p>

    </div>


    <div>

        <a
            href="add.php"
            class="btn btn-primary"
        >
            ➕ Record Expense
        </a>

    </div>

</div>


<!-- SUCCESS: ADDED -->

<?php if (isset($_GET['added'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ Expense recorded successfully.

    </div>

<?php endif; ?>


<!-- SUCCESS: UPDATED -->

<?php if (isset($_GET['updated'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ Expense updated successfully.

    </div>

<?php endif; ?>


<!-- SUCCESS: DELETED -->

<?php if (isset($_GET['deleted'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ Expense deleted successfully.

    </div>

<?php endif; ?>


<!-- Total Expenses -->

<div class="dashboard-grid">

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💸
        </div>

        <h6>Total Expenses</h6>

        <h3>
            <?= money($totalExpenses) ?>
        </h3>

        <small>
            All recorded farm expenses
        </small>

    </div>

</div>


<!-- Expenses Table -->

<div class="dashboard-card">

    <div
        style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            gap:10px;
            flex-wrap:wrap;
        "
    >

        <div>

            <h3>
                📋 Expense Records
            </h3>

            <p>
                View all recorded expenses.
            </p>

        </div>

    </div>


    <?php if (empty($expenses)): ?>

        <div
            style="
                text-align:center;
                padding:40px 20px;
            "
        >

            <div style="font-size:50px;">
                💸
            </div>

            <h3>
                No Expenses Recorded
            </h3>

            <p>
                You have not recorded any farm expenses yet.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                ➕ Record First Expense
            </a>

        </div>

    <?php else: ?>


        <div style="overflow-x:auto;">

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    min-width:1000px;
                "
            >

                <thead>

                    <tr>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            #
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            Date
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            Category
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            Description
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            Amount
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:left;
                            "
                        >
                            Payment
                        </th>

                        <th
                            style="
                                padding:12px;
                                text-align:center;
                            "
                        >
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($expenses as $expense): ?>

                        <tr>

                            <!-- ID -->

                            <td
                                style="padding:12px;"
                            >
                                <?= (int)$expense['id'] ?>
                            </td>


                            <!-- DATE -->

                            <td
                                style="padding:12px;"
                            >
                                <?= e(
                                    $expense['expense_date']
                                ) ?>
                            </td>


                            <!-- CATEGORY -->

                            <td
                                style="padding:12px;"
                            >

                                <strong>
                                    <?= e(
                                        $expense['category']
                                    ) ?>
                                </strong>

                            </td>


                            <!-- DESCRIPTION -->

                            <td
                                style="padding:12px;"
                            >
                                <?= e(
                                    $expense['description']
                                ) ?>
                            </td>


                            <!-- AMOUNT -->

                            <td
                                style="
                                    padding:12px;
                                    font-weight:bold;
                                "
                            >

                                <?= money(
                                    (float)$expense['amount']
                                ) ?>

                            </td>


                            <!-- PAYMENT METHOD -->

                            <td
                                style="padding:12px;"
                            >

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

                                echo e(
                                    $paymentLabels[
                                        $expense['payment_method']
                                    ]
                                    ??
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            (string)$expense[
                                                'payment_method'
                                            ]
                                        )
                                    )
                                );

                                ?>

                            </td>


                            <!-- ACTIONS -->

                            <td
                                style="
                                    padding:12px;
                                    text-align:center;
                                    white-space:nowrap;
                                "
                            >

                                <!-- VIEW -->

                                <a
                                    href="view.php?id=<?= (int)$expense['id'] ?>"
                                    class="btn btn-secondary"
                                >
                                    👁️ View
                                </a>


                                <!-- EDIT -->

                                <a
                                    href="edit.php?id=<?= (int)$expense['id'] ?>"
                                    class="btn btn-primary"
                                >
                                    ✏️ Edit
                                </a>


                                <!-- DELETE -->

                                <a
                                    href="delete.php?id=<?= (int)$expense['id'] ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm(
                                        'Are you sure you want to delete this expense record?'
                                    );"
                                >
                                    🗑️ Delete
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<!-- Delete Button Styling -->

<style>

.btn-danger {
    background: #dc2626;
    color: #ffffff;
    border: none;
    margin-left: 5px;
}

.btn-danger:hover {
    background: #b91c1c;
}

@media (max-width: 768px) {

    .btn-danger,
    .btn-primary,
    .btn-secondary {
        display: inline-block;
        margin-top: 4px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>