<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Record Crate Sale';

$errors = [];

$saleDate = date('Y-m-d');
$batchId = '';
$quantity = '';
$unitPrice = '';
$customerName = '';
$paymentMethod = 'cash';
$notes = '';

$stmt = $pdo->query("
    SELECT
        id,
        batch_name,
        bird_type,
        current_quantity
    FROM poultry_batches
    ORDER BY batch_name ASC
");

$batches = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $saleDate = trim($_POST['sale_date'] ?? '');
    $batchId = trim($_POST['batch_id'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $unitPrice = trim($_POST['unit_price'] ?? '');
    $customerName = trim($_POST['customer_name'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cash');
    $notes = trim($_POST['notes'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($saleDate === '') {
        $errors[] = 'Sale date is required.';
    }

    if (
        $quantity === ''
        || !ctype_digit($quantity)
        || (int) $quantity <= 0
    ) {
        $errors[] = 'Number of crates sold must be greater than zero.';
    }

    if (
        $unitPrice === ''
        || !is_numeric($unitPrice)
        || (float) $unitPrice <= 0
    ) {
        $errors[] = 'Price per crate must be greater than zero.';
    }

    $allowedPaymentMethods = [
        'cash',
        'mobile_money',
        'bank',
        'other'
    ];

    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $errors[] = 'Invalid payment method.';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Poultry Batch
    |--------------------------------------------------------------------------
    */

    if ($batchId !== '') {

        if (!ctype_digit($batchId)) {

            $errors[] = 'Invalid poultry batch selected.';

        } else {

            $batchCheck = $pdo->prepare("
                SELECT id
                FROM poultry_batches
                WHERE id = :id
                LIMIT 1
            ");

            $batchCheck->execute([
                ':id' => (int) $batchId
            ]);

            if (!$batchCheck->fetch()) {
                $errors[] = 'Selected poultry batch does not exist.';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Total
    |--------------------------------------------------------------------------
    */

    $totalAmount = 0;

    if (
        $quantity !== ''
        && is_numeric($quantity)
        && $unitPrice !== ''
        && is_numeric($unitPrice)
    ) {
        $totalAmount =
            (int) $quantity * (float) $unitPrice;
    }

    /*
    |--------------------------------------------------------------------------
    | Save Crate Sale
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO egg_sales (
                sale_date,
                batch_id,
                quantity,
                unit_price,
                total_amount,
                customer_name,
                payment_method,
                notes,
                recorded_by
            )
            VALUES (
                :sale_date,
                :batch_id,
                :quantity,
                :unit_price,
                :total_amount,
                :customer_name,
                :payment_method,
                :notes,
                :recorded_by
            )
        ");

        $stmt->execute([
            ':sale_date' => $saleDate,

            ':batch_id' => $batchId !== ''
                ? (int) $batchId
                : null,

            /*
             * IMPORTANT:
             * The existing database column is still called
             * "quantity", but it now represents CRATES SOLD.
             */
            ':quantity' => (int) $quantity,

            ':unit_price' => (float) $unitPrice,

            ':total_amount' => $totalAmount,

            ':customer_name' => $customerName !== ''
                ? $customerName
                : null,

            ':payment_method' => $paymentMethod,

            ':notes' => $notes !== ''
                ? $notes
                : null,

            ':recorded_by' => currentUserId()
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notify Other Users
        |--------------------------------------------------------------------------
        */

        if (function_exists('notifyOtherUsers')) {

            notifyOtherUsers(
                'New Crate Sale Recorded',
                sprintf(
                    '%s recorded a sale of %s crate%s for %s.',
                    $_SESSION['full_name'] ?? 'A user',
                    number((int) $quantity),
                    (int) $quantity === 1 ? '' : 's',
                    money($totalAmount)
                ),
                'sales',
                'index.php'
            );
        }

        header('Location: index.php?success=1');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>
            <i class="bi bi-plus-circle"></i>
            Record Crate Sale
        </h2>

        <p>
            Record a new crate sale from the farm.
        </p>

    </div>

    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            <i class="bi bi-arrow-left"></i>
            Back to Sales
        </a>

    </div>

</div>


<?php if (!empty($errors)): ?>

    <div class="alert alert-danger">

        <strong>
            Please correct the following:
        </strong>

        <ul>

            <?php foreach ($errors as $error): ?>

                <li>
                    <?= e($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>


<form
    method="POST"
    action=""
    class="dashboard-card"
>


    <!-- Sale Date -->

    <div class="form-group">

        <label for="sale_date">

            <i class="bi bi-calendar-event"></i>
            Sale Date

        </label>

        <input
            type="date"
            id="sale_date"
            name="sale_date"
            value="<?= e($saleDate) ?>"
            required
        >

    </div>


    <!-- Poultry Batch -->

    <div class="form-group">

        <label for="batch_id">

            <i class="bi bi-layers"></i>
            Poultry Batch

        </label>

        <select
            id="batch_id"
            name="batch_id"
        >

            <option value="">
                -- Not Specified --
            </option>

            <?php foreach ($batches as $batch): ?>

                <option
                    value="<?= (int) $batch['id'] ?>"
                    <?= (
                        (string) $batchId ===
                        (string) $batch['id']
                    )
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= e($batch['batch_name']) ?>

                    <?php if (!empty($batch['bird_type'])): ?>

                        -
                        <?= e($batch['bird_type']) ?>

                    <?php endif; ?>

                    (
                    <?= number(
                        (int) $batch['current_quantity']
                    ) ?>
                    birds
                    )

                </option>

            <?php endforeach; ?>

        </select>

        <small>
            Select the poultry batch associated with the eggs.
        </small>

    </div>


    <!-- Crates Sold -->

    <div class="form-group">

        <label for="quantity">

            <i class="bi bi-box-seam"></i>
            Crates Sold

        </label>

        <input
            type="number"
            id="quantity"
            name="quantity"
            value="<?= e($quantity) ?>"
            min="1"
            step="1"
            placeholder="Enter number of crates"
            required
        >

        <small>
            Enter the number of crates sold.
        </small>

    </div>


    <!-- Price Per Crate -->

    <div class="form-group">

        <label for="unit_price">

            <i class="bi bi-currency-dollar"></i>
            Price Per Crate

        </label>

        <input
            type="number"
            id="unit_price"
            name="unit_price"
            value="<?= e($unitPrice) ?>"
            min="0.01"
            step="0.01"
            placeholder="Enter price per crate"
            required
        >

        <small>
            Enter the selling price for one crate.
        </small>

    </div>


    <!-- Total Amount -->

    <div class="form-group">

        <label for="total_amount">

            <i class="bi bi-cash-stack"></i>
            Total Amount

        </label>

        <input
            type="text"
            id="total_amount"
            value="GHS 0.00"
            readonly
        >

        <small>
            Total amount is calculated automatically.
        </small>

    </div>


    <!-- Customer -->

    <div class="form-group">

        <label for="customer_name">

            <i class="bi bi-person"></i>
            Customer Name

        </label>

        <input
            type="text"
            id="customer_name"
            name="customer_name"
            value="<?= e($customerName) ?>"
            maxlength="150"
            placeholder="Enter customer name"
        >

    </div>


    <!-- Payment Method -->

    <div class="form-group">

        <label for="payment_method">

            <i class="bi bi-credit-card"></i>
            Payment Method

        </label>

        <select
            id="payment_method"
            name="payment_method"
        >

            <option
                value="cash"
                <?= $paymentMethod === 'cash'
                    ? 'selected'
                    : '' ?>
            >
                Cash
            </option>

            <option
                value="mobile_money"
                <?= $paymentMethod === 'mobile_money'
                    ? 'selected'
                    : '' ?>
            >
                Mobile Money
            </option>

            <option
                value="bank"
                <?= $paymentMethod === 'bank'
                    ? 'selected'
                    : '' ?>
            >
                Bank
            </option>

            <option
                value="other"
                <?= $paymentMethod === 'other'
                    ? 'selected'
                    : '' ?>
            >
                Other
            </option>

        </select>

    </div>


    <!-- Notes -->

    <div class="form-group">

        <label for="notes">

            <i class="bi bi-sticky"></i>
            Notes

        </label>

        <textarea
            id="notes"
            name="notes"
            rows="4"
            placeholder="Optional notes about this crate sale"
        ><?= e($notes) ?></textarea>

    </div>


    <!-- Buttons -->

    <div
        class="form-actions"
        style="
            display:flex;
            gap:12px;
            margin-top:25px;
            flex-wrap:wrap;
        "
    >

        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="bi bi-check-circle"></i>
            Save Crate Sale
        </button>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            Cancel
        </a>

    </div>

</form>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const quantity =
            document.getElementById('quantity');

        const unitPrice =
            document.getElementById('unit_price');

        const totalAmount =
            document.getElementById('total_amount');


        function calculateTotal() {

            const crates =
                parseFloat(quantity.value) || 0;

            const price =
                parseFloat(unitPrice.value) || 0;

            const total =
                crates * price;

            totalAmount.value =
                'GHS ' + total.toFixed(2);
        }


        quantity.addEventListener(
            'input',
            calculateTotal
        );


        unitPrice.addEventListener(
            'input',
            calculateTotal
        );


        calculateTotal();

    }
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>