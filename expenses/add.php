<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$error = '';

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $expenseDate = $_POST['expense_date'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? 'cash';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($expenseDate === '') {

        $error = 'Please select the expense date.';

    } elseif ($category === '') {

        $error = 'Please enter the expense category.';

    } elseif ($description === '') {

        $error = 'Please enter a description.';

    } elseif (!is_numeric($amount) || (float)$amount <= 0) {

        $error = 'Please enter a valid expense amount.';

    } elseif (
        !in_array(
            $paymentMethod,
            ['cash', 'mobile_money', 'bank', 'other'],
            true
        )
    ) {

        $error = 'Invalid payment method.';

    } else {

        $amount = (float)$amount;


        /*
        |--------------------------------------------------------------------------
        | Insert Expense
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO expenses
            (
                expense_date,
                category,
                description,
                amount,
                payment_method
            )
            VALUES
            (
                :expense_date,
                :category,
                :description,
                :amount,
                :payment_method
            )
        ");

        $stmt->execute([

            ':expense_date' => $expenseDate,

            ':category' => $category,

            ':description' => $description,

            ':amount' => $amount,

            ':payment_method' => $paymentMethod

        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        header('Location: index.php?added=1');
        exit;
    }
}


$pageTitle = 'Add Expense';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>➕ Record Expense</h2>

        <p>
            Record a new farm expense.
        </p>

    </div>


    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Expenses
        </a>

    </div>

</div>


<?php if ($error): ?>

    <div
        class="alert alert-danger"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ⚠️ <?= e($error) ?>

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <form
        method="POST"
        action=""
    >


        <!-- Expense Date -->

        <div class="form-group">

            <label for="expense_date">
                📅 Expense Date
            </label>

            <input
                type="date"
                id="expense_date"
                name="expense_date"
                value="<?= e(
                    $_POST['expense_date']
                    ?? date('Y-m-d')
                ) ?>"
                required
            >

        </div>



        <!-- Category -->

        <div class="form-group">

            <label for="category">
                🏷️ Expense Category
            </label>

            <select
                id="category"
                name="category"
                required
            >

                <option value="">
                    -- Select Category --
                </option>

                <?php

                $categories = [
                    'Feed',
                    'Medication',
                    'Vaccination',
                    'Labour',
                    'Electricity',
                    'Water',
                    'Transportation',
                    'Equipment',
                    'Repairs',
                    'Packaging',
                    'Farm Maintenance',
                    'Other'
                ];

                ?>

                <?php foreach ($categories as $item): ?>

                    <option
                        value="<?= e($item) ?>"
                        <?= (
                            ($_POST['category'] ?? '')
                            === $item
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= e($item) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- Description -->

        <div class="form-group">

            <label for="description">
                📝 Description
            </label>

            <input
                type="text"
                id="description"
                name="description"
                maxlength="255"
                value="<?= e(
                    $_POST['description']
                    ?? ''
                ) ?>"
                placeholder="e.g. Purchased 20 bags of feed"
                required
            >

        </div>



        <!-- Amount -->

        <div class="form-group">

            <label for="amount">
                💰 Amount
            </label>

            <input
                type="number"
                id="amount"
                name="amount"
                min="0.01"
                step="0.01"
                value="<?= e(
                    $_POST['amount']
                    ?? ''
                ) ?>"
                placeholder="Enter amount in GHS"
                required
            >

        </div>



        <!-- Payment Method -->

        <div class="form-group">

            <label for="payment_method">
                💳 Payment Method
            </label>

            <select
                id="payment_method"
                name="payment_method"
            >

                <option value="cash">
                    Cash
                </option>

                <option value="mobile_money">
                    Mobile Money
                </option>

                <option value="bank">
                    Bank
                </option>

                <option value="other">
                    Other
                </option>

            </select>

        </div>



        <!-- Buttons -->

        <div
            style="
                display:flex;
                gap:10px;
                flex-wrap:wrap;
                margin-top:20px;
            "
        >

            <button
                type="submit"
                class="btn btn-primary"
            >
                💾 Save Expense
            </button>


            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>