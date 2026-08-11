<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Record Egg Production';

$errors = [];

/*
|--------------------------------------------------------------------------
| Get Active Batches
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        batch_name,
        bird_type,
        current_quantity
    FROM poultry_batches
    WHERE status = 'active'
    ORDER BY batch_name ASC
");

$batches = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Save Production
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchId = filter_input(
        INPUT_POST,
        'batch_id',
        FILTER_VALIDATE_INT
    );

    $productionDate = $_POST['production_date'] ?? '';

    $eggsCollected = filter_input(
        INPUT_POST,
        'eggs_collected',
        FILTER_VALIDATE_INT
    );

    $brokenEggs = filter_input(
        INPUT_POST,
        'broken_eggs',
        FILTER_VALIDATE_INT
    );

    $notes = trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (!$batchId) {
        $errors[] = 'Please select a poultry batch.';
    }

    if ($productionDate === '') {
        $errors[] = 'Production date is required.';
    }

    if ($eggsCollected === false || $eggsCollected === null || $eggsCollected < 0) {
        $errors[] = 'Eggs collected must be zero or greater.';
    }

    if ($brokenEggs === false || $brokenEggs === null || $brokenEggs < 0) {
        $errors[] = 'Broken eggs must be zero or greater.';
    }

    if (
        $eggsCollected !== false &&
        $eggsCollected !== null &&
        $brokenEggs !== false &&
        $brokenEggs !== null &&
        $brokenEggs > $eggsCollected
    ) {
        $errors[] = 'Broken eggs cannot be greater than eggs collected.';
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Batch
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM poultry_batches
            WHERE id = ?
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([$batchId]);

        if (!$stmt->fetch()) {
            $errors[] = 'The selected batch does not exist or is not active.';
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Daily Record
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM egg_production
            WHERE batch_id = ?
            AND production_date = ?
            LIMIT 1
        ");

        $stmt->execute([
            $batchId,
            $productionDate
        ]);

        if ($stmt->fetch()) {
            $errors[] = 'A production record already exists for this batch on this date.';
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $stmt = $pdo->prepare("
            INSERT INTO egg_production
            (
                batch_id,
                production_date,
                eggs_collected,
                broken_eggs,
                notes,
                recorded_by
            )
            VALUES
            (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $batchId,
            $productionDate,
            $eggsCollected,
            $brokenEggs,
            $notes ?: null,
            currentUserId()
        ]);

        redirect('index.php');

    }

}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>🥚 Record Egg Production</h2>

        <p>
            Enter the daily egg production for a poultry batch.
        </p>

    </div>

    <a
        href="index.php"
        class="btn btn-secondary"
    >
        ← Back to Production
    </a>

</div>


<div class="dashboard-card">

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <strong>Please correct the following:</strong>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <?php if (empty($batches)): ?>

        <div class="empty-state">

            <div style="font-size: 45px;">
                🐔
            </div>

            <h3>No Active Poultry Batches</h3>

            <p>
                You need an active poultry batch before recording egg production.
            </p>

            <a
                href="../batches/add.php"
                class="btn btn-primary"
            >
                + Add Poultry Batch
            </a>

        </div>

    <?php else: ?>

        <form method="POST">

            <div class="form-grid">

                <!-- Batch -->

                <div class="form-group">

                    <label for="batch_id">
                        Poultry Batch *
                    </label>

                    <select
                        id="batch_id"
                        name="batch_id"
                        required
                    >

                        <option value="">
                            Select batch
                        </option>

                        <?php foreach ($batches as $batch): ?>

                            <option
                                value="<?= (int) $batch['id'] ?>"
                                <?= (($_POST['batch_id'] ?? '') == $batch['id']) ? 'selected' : '' ?>
                            >

                                <?= e($batch['batch_name']) ?>

                                —
                                <?= e($batch['bird_type']) ?>

                                (<?= number((int) $batch['current_quantity']) ?> birds)

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Date -->

                <div class="form-group">

                    <label for="production_date">
                        Production Date *
                    </label>

                    <input
                        type="date"
                        id="production_date"
                        name="production_date"
                        value="<?= e($_POST['production_date'] ?? date('Y-m-d')) ?>"
                        required
                    >

                </div>


                <!-- Eggs -->

                <div class="form-group">

                    <label for="eggs_collected">
                        Eggs Collected *
                    </label>

                    <input
                        type="number"
                        id="eggs_collected"
                        name="eggs_collected"
                        min="0"
                        value="<?= e($_POST['eggs_collected'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Broken -->

                <div class="form-group">

                    <label for="broken_eggs">
                        Broken Eggs *
                    </label>

                    <input
                        type="number"
                        id="broken_eggs"
                        name="broken_eggs"
                        min="0"
                        value="<?= e($_POST['broken_eggs'] ?? '0') ?>"
                        required
                    >

                </div>


                <!-- Notes -->

                <div class="form-group form-group-full">

                    <label for="notes">
                        Notes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Enter any notes about today's production..."
                    ><?= e($_POST['notes'] ?? '') ?></textarea>

                </div>

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
                    Save Production
                </button>

            </div>

        </form>

    <?php endif; ?>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>