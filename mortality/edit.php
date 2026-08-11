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


/*
|--------------------------------------------------------------------------
| Get Existing Mortality Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM mortality
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {
    redirect('index.php');
}


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
        breed,
        current_quantity
    FROM poultry_batches
    WHERE status = 'active'
       OR id = " . (int) $record['batch_id'] . "
    ORDER BY batch_name ASC
");

$batches = $stmt->fetchAll();

$errors = [];


/*
|--------------------------------------------------------------------------
| Update Mortality
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newBatchId = filter_input(
        INPUT_POST,
        'batch_id',
        FILTER_VALIDATE_INT
    );

    $mortalityDate = trim(
        $_POST['mortality_date'] ?? ''
    );

    $newQuantity = filter_input(
        INPUT_POST,
        'quantity',
        FILTER_VALIDATE_INT
    );

    $reason = trim(
        $_POST['reason'] ?? ''
    );

    $notes = trim(
        $_POST['notes'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (!$newBatchId) {
        $errors[] = 'Please select a poultry batch.';
    }

    if ($mortalityDate === '') {
        $errors[] = 'Mortality date is required.';
    }

    if (
        $newQuantity === false ||
        $newQuantity === null ||
        $newQuantity <= 0
    ) {
        $errors[] =
            'Mortality quantity must be greater than zero.';
    }


    if (!$errors) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Original Mortality Record
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT *
                FROM mortality
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $oldRecord = $stmt->fetch();

            if (!$oldRecord) {
                throw new RuntimeException(
                    'Mortality record no longer exists.'
                );
            }


            $oldBatchId = (int) $oldRecord['batch_id'];
            $oldQuantity = (int) $oldRecord['quantity'];


            /*
            |--------------------------------------------------------------------------
            | Same Batch
            |--------------------------------------------------------------------------
            */

            if ($oldBatchId === $newBatchId) {

                /*
                Lock the batch
                */

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        current_quantity
                    FROM poultry_batches
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([$oldBatchId]);

                $batch = $stmt->fetch();

                if (!$batch) {
                    throw new RuntimeException(
                        'The selected poultry batch does not exist.'
                    );
                }


                $currentQuantity =
                    (int) $batch['current_quantity'];


                /*
                Current quantity already excludes old mortality.
                Add old mortality back first.
                */

                $restoredQuantity =
                    $currentQuantity + $oldQuantity;


                if ($newQuantity > $restoredQuantity) {

                    throw new RuntimeException(
                        'Mortality cannot be greater than the available birds in this batch.'
                    );
                }


                $newCurrentQuantity =
                    $restoredQuantity - $newQuantity;


                /*
                Update batch quantity
                */

                $stmt = $pdo->prepare("
                    UPDATE poultry_batches
                    SET current_quantity = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $newCurrentQuantity,
                    $oldBatchId
                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Batch Changed
            |--------------------------------------------------------------------------
            */

            else {

                /*
                Lock old batch
                */

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        current_quantity
                    FROM poultry_batches
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([$oldBatchId]);

                $oldBatch = $stmt->fetch();

                if (!$oldBatch) {
                    throw new RuntimeException(
                        'The original poultry batch no longer exists.'
                    );
                }


                /*
                Restore old mortality
                */

                $oldBatchCurrent =
                    (int) $oldBatch['current_quantity']
                    + $oldQuantity;


                $stmt = $pdo->prepare("
                    UPDATE poultry_batches
                    SET current_quantity = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $oldBatchCurrent,
                    $oldBatchId
                ]);


                /*
                Lock new batch
                */

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        current_quantity
                    FROM poultry_batches
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([$newBatchId]);

                $newBatch = $stmt->fetch();

                if (!$newBatch) {
                    throw new RuntimeException(
                        'The new poultry batch does not exist.'
                    );
                }


                $newBatchCurrent =
                    (int) $newBatch['current_quantity'];


                if ($newQuantity > $newBatchCurrent) {

                    throw new RuntimeException(
                        'Mortality cannot be greater than the current number of birds in the new batch.'
                    );
                }


                /*
                Apply new mortality
                */

                $newBatchCurrent -= $newQuantity;


                $stmt = $pdo->prepare("
                    UPDATE poultry_batches
                    SET current_quantity = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $newBatchCurrent,
                    $newBatchId
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Batch/Date
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM mortality
                WHERE batch_id = ?
                  AND mortality_date = ?
                  AND id != ?
                LIMIT 1
            ");

            $stmt->execute([
                $newBatchId,
                $mortalityDate,
                $id
            ]);

            if ($stmt->fetch()) {

                throw new RuntimeException(
                    'A mortality record already exists for this batch on this date.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Mortality Record
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE mortality

                SET
                    batch_id = ?,
                    mortality_date = ?,
                    quantity = ?,
                    reason = ?,
                    notes = ?

                WHERE id = ?
            ");

            $stmt->execute([
                $newBatchId,
                $mortalityDate,
                $newQuantity,
                $reason !== '' ? $reason : null,
                $notes !== '' ? $notes : null,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            redirect('view.php?id=' . $id);


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = $e->getMessage();
        }
    }
}


$pageTitle = 'Edit Mortality';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>✏️ Edit Mortality</h2>

        <p>
            Update this mortality record.
        </p>

    </div>


    <a
        href="view.php?id=<?= (int) $id ?>"
        class="btn btn-secondary"
    >
        ← Back
    </a>

</div>



<div class="dashboard-card">


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



    <form method="POST">


        <div class="form-grid">


            <!-- Batch -->

            <div class="form-group">

                <label for="batch_id">
                    Poultry Batch *
                </label>

                <select
                    name="batch_id"
                    id="batch_id"
                    required
                >

                    <option value="">
                        Select poultry batch
                    </option>


                    <?php foreach ($batches as $batch): ?>

                        <?php
                        $selectedBatch =
                            $_POST['batch_id']
                            ?? $record['batch_id'];
                        ?>


                        <option
                            value="<?= (int) $batch['id'] ?>"
                            <?= (
                                (int) $selectedBatch
                                === (int) $batch['id']
                            ) ? 'selected' : '' ?>
                        >

                            <?= e($batch['batch_name']) ?>

                            —

                            <?= e($batch['bird_type']) ?>

                            —

                            <?= number(
                                (int) $batch['current_quantity']
                            ) ?>

                            birds

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>



            <!-- Date -->

            <div class="form-group">

                <label for="mortality_date">
                    Mortality Date *
                </label>

                <input
                    type="date"
                    name="mortality_date"
                    id="mortality_date"
                    value="<?= e(
                        $_POST['mortality_date']
                        ?? $record['mortality_date']
                    ) ?>"
                    required
                >

            </div>



            <!-- Quantity -->

            <div class="form-group">

                <label for="quantity">
                    Number of Birds *
                </label>

                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    min="1"
                    step="1"
                    value="<?= e(
                        $_POST['quantity']
                        ?? $record['quantity']
                    ) ?>"
                    required
                >

            </div>



            <!-- Reason -->

            <div class="form-group">

                <label for="reason">
                    Reason
                </label>

                <input
                    type="text"
                    name="reason"
                    id="reason"
                    maxlength="255"
                    placeholder="e.g. Disease, weakness, accident"
                    value="<?= e(
                        $_POST['reason']
                        ?? $record['reason']
                        ?? ''
                    ) ?>"
                >

            </div>



            <!-- Notes -->

            <div class="form-group form-group-full">

                <label for="notes">
                    Notes
                </label>

                <textarea
                    name="notes"
                    id="notes"
                    rows="4"
                    placeholder="Additional information..."
                ><?= e(
                    $_POST['notes']
                    ?? $record['notes']
                    ?? ''
                ) ?></textarea>

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
                class="btn btn-warning"
            >
                💾 Save Changes
            </button>

        </div>


    </form>

</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>