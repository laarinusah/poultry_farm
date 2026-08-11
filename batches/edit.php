<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM poultry_batches
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$batch = $stmt->fetch();

if (!$batch) {
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchName = trim($_POST['batch_name'] ?? '');
    $birdType = trim($_POST['bird_type'] ?? '');
    $breed = trim($_POST['breed'] ?? '');

    $initialQuantity = (int) ($_POST['initial_quantity'] ?? 0);
    $currentQuantity = (int) ($_POST['current_quantity'] ?? 0);

    $dateStarted = $_POST['date_started'] ?? '';
    $expectedSaleDate = $_POST['expected_sale_date'] ?? '';

    $status = $_POST['status'] ?? 'active';

    $notes = trim($_POST['notes'] ?? '');

    if ($batchName === '') {
        $errors[] = 'Batch name is required.';
    }

    if ($birdType === '') {
        $errors[] = 'Bird type is required.';
    }

    if ($initialQuantity <= 0) {
        $errors[] = 'Initial quantity must be greater than zero.';
    }

    if ($currentQuantity < 0) {
        $errors[] = 'Current quantity cannot be negative.';
    }

    if ($currentQuantity > $initialQuantity) {
        $errors[] = 'Current quantity cannot exceed initial quantity.';
    }

    if ($dateStarted === '') {
        $errors[] = 'Start date is required.';
    }

    if (!in_array($status, ['active', 'sold', 'completed'], true)) {
        $errors[] = 'Invalid status.';
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE poultry_batches

            SET
                batch_name = ?,
                bird_type = ?,
                breed = ?,
                initial_quantity = ?,
                current_quantity = ?,
                date_started = ?,
                expected_sale_date = ?,
                status = ?,
                notes = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $batchName,
            $birdType,
            $breed ?: null,
            $initialQuantity,
            $currentQuantity,
            $dateStarted,
            $expectedSaleDate ?: null,
            $status,
            $notes ?: null,
            $id
        ]);

        redirect('view.php?id=' . $id);
    }
}

$pageTitle = 'Edit Poultry Batch';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>
        <h2>Edit Poultry Batch</h2>

        <p>
            Update <?= e($batch['batch_name']) ?>
        </p>
    </div>

    <a href="view.php?id=<?= (int) $id ?>" class="btn btn-secondary">
        ← Back
    </a>

</div>


<div class="dashboard-card">

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <strong>Please correct the following:</strong>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li><?= e($error) ?></li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <form method="POST">

        <div class="form-grid">

            <div class="form-group">

                <label for="batch_name">
                    Batch Name *
                </label>

                <input
                    type="text"
                    id="batch_name"
                    name="batch_name"
                    value="<?= e($_POST['batch_name'] ?? $batch['batch_name']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="bird_type">
                    Bird Type *
                </label>

                <select
                    id="bird_type"
                    name="bird_type"
                    required
                >

                    <?php
                    $birdTypes = [
                        'Layers',
                        'Broilers',
                        'Cockerels',
                        'Other'
                    ];
                    ?>

                    <?php foreach ($birdTypes as $type): ?>

                        <option
                            value="<?= e($type) ?>"
                            <?= ($_POST['bird_type'] ?? $batch['bird_type']) === $type ? 'selected' : '' ?>
                        >
                            <?= e($type) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="breed">
                    Breed
                </label>

                <input
                    type="text"
                    id="breed"
                    name="breed"
                    value="<?= e($_POST['breed'] ?? $batch['breed']) ?>"
                >

            </div>


            <div class="form-group">

                <label for="initial_quantity">
                    Initial Birds *
                </label>

                <input
                    type="number"
                    id="initial_quantity"
                    name="initial_quantity"
                    min="1"
                    value="<?= e($_POST['initial_quantity'] ?? $batch['initial_quantity']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="current_quantity">
                    Current Birds *
                </label>

                <input
                    type="number"
                    id="current_quantity"
                    name="current_quantity"
                    min="0"
                    value="<?= e($_POST['current_quantity'] ?? $batch['current_quantity']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="date_started">
                    Start Date *
                </label>

                <input
                    type="date"
                    id="date_started"
                    name="date_started"
                    value="<?= e($_POST['date_started'] ?? $batch['date_started']) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="expected_sale_date">
                    Expected Sale Date
                </label>

                <input
                    type="date"
                    id="expected_sale_date"
                    name="expected_sale_date"
                    value="<?= e($_POST['expected_sale_date'] ?? $batch['expected_sale_date']) ?>"
                >

            </div>


            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <?php
                    $statuses = [
                        'active',
                        'completed',
                        'sold'
                    ];
                    ?>

                    <?php foreach ($statuses as $statusOption): ?>

                        <option
                            value="<?= e($statusOption) ?>"
                            <?= ($_POST['status'] ?? $batch['status']) === $statusOption ? 'selected' : '' ?>
                        >
                            <?= e(ucfirst($statusOption)) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group form-group-full">

                <label for="notes">
                    Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                ><?= e($_POST['notes'] ?? $batch['notes']) ?></textarea>

            </div>

        </div>


        <div class="form-actions">

            <a
                href="view.php?id=<?= (int) $id ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                Save Changes
            </button>

        </div>

    </form>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>