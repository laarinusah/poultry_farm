<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Expense
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM expenses
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    header('Location: index.php');
    exit;
}

$error = '';

/*
|--------------------------------------------------------------------------
| Update Expense
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $expenseDate = trim($_POST['expense_date'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cash');

    if (
        $expenseDate === '' ||
        $category === '' ||
        $description === '' ||
        $amount === ''
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($amount) || (float)$amount <= 0) {
        $error = 'Please enter a valid expense amount.';
    } else {

        $stmt = $pdo->prepare("
            UPDATE expenses
            SET
                expense_date = ?,
                category = ?,
                description = ?,
                amount = ?,
                payment_method = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $expenseDate,
            $category,
            $description,
            (float)$amount,
            $paymentMethod,
            $id
        ]);

        header('Location: index.php?updated=1');
        exit;
    }
}

?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-container">

    <div class="page-header">

        <div>
            <h1>✏️ Edit Expense</h1>

            <p>
                Update this expense record.
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            ← Back to Expenses
        </a>

    </div>


    <?php if ($error): ?>

        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <div class="form-card">

        <form method="POST">

            <div class="form-group">

                <label for="expense_date">
                    📅 Expense Date
                </label>

                <input
                    type="date"
                    id="expense_date"
                    name="expense_date"
                    value="<?= htmlspecialchars($expense['expense_date']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="category">
                    📂 Category
                </label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    value="<?= htmlspecialchars($expense['category']) ?>"
                    placeholder="e.g. Feed, Medicine, Transport"
                    required
                >

            </div>


            <div class="form-group">

                <label for="description">
                    📝 Description
                </label>

                <input
                    type="text"
                    id="description"
                    name="description"
                    value="<?= htmlspecialchars($expense['description']) ?>"
                    placeholder="Enter expense description"
                    required
                >

            </div>


            <div class="form-group">

                <label for="amount">
                    💰 Amount (GHS)
                </label>

                <input
                    type="number"
                    id="amount"
                    name="amount"
                    value="<?= htmlspecialchars((string)$expense['amount']) ?>"
                    step="0.01"
                    min="0.01"
                    required
                >

            </div>


            <div class="form-group">

                <label for="payment_method">
                    💳 Payment Method
                </label>

                <select
                    id="payment_method"
                    name="payment_method"
                >

                    <option
                        value="cash"
                        <?= $expense['payment_method'] === 'cash' ? 'selected' : '' ?>
                    >
                        Cash
                    </option>

                    <option
                        value="mobile_money"
                        <?= $expense['payment_method'] === 'mobile_money' ? 'selected' : '' ?>
                    >
                        Mobile Money
                    </option>

                    <option
                        value="bank"
                        <?= $expense['payment_method'] === 'bank' ? 'selected' : '' ?>
                    >
                        Bank
                    </option>

                    <option
                        value="other"
                        <?= $expense['payment_method'] === 'other' ? 'selected' : '' ?>
                    >
                        Other
                    </option>

                </select>

            </div>


            <div class="form-actions">

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    💾 Update Expense
                </button>

            </div>

        </form>

    </div>

</div>


<style>

.page-container {
    padding: 25px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 20px;
}

.page-header h1 {
    margin: 0;
}

.page-header p {
    color: #777;
}

.form-card {
    max-width: 850px;
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 13px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

.btn {
    display: inline-block;
    padding: 11px 18px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-secondary {
    background: #e5e7eb;
    color: #111827;
}

.alert-error {
    max-width: 850px;
    padding: 15px;
    margin-bottom: 20px;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 8px;
}

@media (max-width: 768px) {

    .page-container {
        padding: 15px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-card {
        padding: 20px;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        text-align: center;
    }

}

</style>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>