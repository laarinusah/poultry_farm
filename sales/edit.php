<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    redirect('index.php');
}

/*
|--------------------------------------------------------------------------
| Get Existing Sale
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM egg_sales
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$sale = $stmt->fetch();

if (!$sale) {
    redirect('index.php');
}

/*
|--------------------------------------------------------------------------
| Get Poultry Batches
|--------------------------------------------------------------------------
*/

$batchStmt = $pdo->query("
    SELECT
        id,
        batch_name,
        bird_type
    FROM poultry_batches
    ORDER BY batch_name ASC
");

$batches = $batchStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Default Form Values
|--------------------------------------------------------------------------
*/

$error = '';

$saleDate = $sale['sale_date'] ?? '';
$batchId = $sale['batch_id'] ?? '';
$quantity = $sale['quantity'] ?? '';
$unitPrice = $sale['unit_price'] ?? '';
$customerName = $sale['customer_name'] ?? '';
$paymentMethod = $sale['payment_method'] ?? 'cash';
$notes = $sale['notes'] ?? '';

/*
|--------------------------------------------------------------------------
| Handle Update
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $saleDate = trim($_POST['sale_date'] ?? '');
    $batchId = trim($_POST['batch_id'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $unitPrice = trim($_POST['unit_price'] ?? '');
    $customerName = trim($_POST['customer_name'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $notes = trim($_POST['notes'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($saleDate === '') {

        $error = 'Please select the sale date.';

    } elseif (
        !is_numeric($quantity) ||
        (int) $quantity <= 0
    ) {

        $error = 'Please enter a valid quantity greater than zero.';

    } elseif (
        !is_numeric($unitPrice) ||
        (float) $unitPrice < 0
    ) {

        $error = 'Please enter a valid unit price.';

    } elseif (
        !in_array(
            $paymentMethod,
            ['cash', 'mobile_money', 'bank', 'other'],
            true
        )
    ) {

        $error = 'Invalid payment method.';

    } else {

        $quantity = (int) $quantity;
        $unitPrice = (float) $unitPrice;

        /*
        |--------------------------------------------------------------------------
        | Validate Batch If Selected
        |--------------------------------------------------------------------------
        */

        if ($batchId !== '') {

            $batchCheck = $pdo->prepare("
                SELECT id
                FROM poultry_batches
                WHERE id = ?
                LIMIT 1
            ");

            $batchCheck->execute([
                (int) $batchId
            ]);

            if (!$batchCheck->fetch()) {
                $error = 'Selected poultry batch does not exist.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Total
        |--------------------------------------------------------------------------
        */

        $totalAmount = $quantity * $unitPrice;

        /*
        |--------------------------------------------------------------------------
        | Update Sale
        |--------------------------------------------------------------------------
        */

        if ($error === '') {

            $update = $pdo->prepare("
                UPDATE egg_sales
                SET
                    sale_date = :sale_date,
                    batch_id = :batch_id,
                    quantity = :quantity,
                    unit_price = :unit_price,
                    total_amount = :total_amount,
                    customer_name = :customer_name,
                    payment_method = :payment_method,
                    notes = :notes
                WHERE id = :id
            ");

            $update->execute([
                ':sale_date' => $saleDate,

                ':batch_id' =>
                    $batchId !== ''
                        ? (int) $batchId
                        : null,

                ':quantity' => $quantity,

                ':unit_price' => $unitPrice,

                ':total_amount' => $totalAmount,

                ':customer_name' =>
                    $customerName !== ''
                        ? $customerName
                        : null,

                ':payment_method' => $paymentMethod,

                ':notes' =>
                    $notes !== ''
                        ? $notes
                        : null,

                ':id' => $id
            ]);

            redirect('view.php?id=' . $id . '&updated=1');
        }
    }
}

$pageTitle = 'Edit Egg Sale';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>✏️ Edit Egg Sale</h2>

        <p>
            Update the details of this egg sale.
        </p>

    </div>

    <div>

        <a
            href="view.php?id=<?= (int) $sale['id'] ?>"
            class="btn btn-secondary"
        >
            ← Back to Sale
        </a>

    </div>

</div>


<?php if ($error !== ''): ?>

    <div
        class="alert alert-danger"
        style="
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        "
    >

        ⚠️ <?= e($error) ?>

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <form
        method="POST"
        action=""
        id="editSaleForm"
    >

        <div class="form-grid">

            <!-- Sale Date -->

            <div class="form-group">

                <label for="sale_date">
                    📅 Sale Date
                </label>

                <input
                    type="date"
                    id="sale_date"
                    name="sale_date"
                    value="<?= e((string) $saleDate) ?>"
                    required
                >

            </div>


            <!-- Poultry Batch -->

            <div class="form-group">

                <label for="batch_id">
                    🐔 Poultry Batch
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

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Quantity -->

            <div class="form-group">

                <label for="quantity">
                    🥚 Quantity Sold
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="1"
                    step="1"
                    value="<?= e((string) $quantity) ?>"
                    required
                >

            </div>


            <!-- Unit Price -->

            <div class="form-group">

                <label for="unit_price">
                    💵 Unit Price
                </label>

                <input
                    type="number"
                    id="unit_price"
                    name="unit_price"
                    min="0"
                    step="0.01"
                    value="<?= e((string) $unitPrice) ?>"
                    required
                >

                <small>
                    Enter the selling price per egg.
                </small>

            </div>


            <!-- Total Amount -->

            <div class="form-group">

                <label for="total_amount">
                    💰 Total Amount
                </label>

                <input
                    type="text"
                    id="total_amount"
                    value="GHS 0.00"
                    readonly
                >

                <small>
                    Automatically calculated.
                </small>

            </div>


            <!-- Customer -->

            <div class="form-group">

                <label for="customer_name">
                    👤 Customer Name
                </label>

                <input
                    type="text"
                    id="customer_name"
                    name="customer_name"
                    maxlength="150"
                    value="<?= e((string) $customerName) ?>"
                    placeholder="Enter customer name"
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
                    required
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

            <div class="form-group form-group-full">

                <label for="notes">
                    📝 Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="Enter any additional notes"
                ><?= e((string) $notes) ?></textarea>

            </div>

        </div>


        <!-- Form Actions -->

        <div class="form-actions">

            <a
                href="view.php?id=<?= (int) $sale['id'] ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                💾 Save Changes
            </button>

        </div>

    </form>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const quantityInput =
        document.getElementById('quantity');

    const unitPriceInput =
        document.getElementById('unit_price');

    const totalAmountInput =
        document.getElementById('total_amount');


    function calculateTotal() {

        const quantity =
            parseFloat(quantityInput.value) || 0;

        const unitPrice =
            parseFloat(unitPriceInput.value) || 0;

        const total =
            quantity * unitPrice;

        totalAmountInput.value =
            'GHS ' + total.toFixed(2);
    }


    quantityInput.addEventListener(
        'input',
        calculateTotal
    );

    unitPriceInput.addEventListener(
        'input',
        calculateTotal
    );


    calculateTotal();

});

</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
