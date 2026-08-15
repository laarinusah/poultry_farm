<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$errors = [];

/*
|--------------------------------------------------------------------------
| Get Active Poultry Batches
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
      AND current_quantity > 0
    ORDER BY batch_name ASC
");

$batches = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Save Mortality
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchId = filter_input(
        INPUT_POST,
        'batch_id',
        FILTER_VALIDATE_INT
    );

    $mortalityDate = trim(
        $_POST['mortality_date'] ?? ''
    );

    $quantity = filter_input(
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

    if (!$batchId) {
        $errors[] = 'Please select a poultry batch.';
    }

    if ($mortalityDate === '') {
        $errors[] = 'Mortality date is required.';
    }

    if (
        $quantity === false ||
        $quantity === null ||
        $quantity <= 0
    ) {
        $errors[] = 'Mortality quantity must be greater than zero.';
    }


    /*
    |--------------------------------------------------------------------------
    | Database Transaction
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Batch
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    batch_name,
                    current_quantity
                FROM poultry_batches
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([
                $batchId
            ]);

            $batch = $stmt->fetch();


            if (!$batch) {

                throw new RuntimeException(
                    'The selected poultry batch does not exist.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Check Available Birds
            |--------------------------------------------------------------------------
            */

            $currentQuantity =
                (int) $batch['current_quantity'];


            if ($quantity > $currentQuantity) {

                throw new RuntimeException(
                    'Mortality cannot be greater than the current number of birds in the batch.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Date
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT id
                FROM mortality
                WHERE batch_id = ?
                  AND mortality_date = ?
                LIMIT 1
            ");

            $stmt->execute([
                $batchId,
                $mortalityDate
            ]);


            if ($stmt->fetch()) {

                throw new RuntimeException(
                    'A mortality record already exists for this batch on this date.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Insert Mortality
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO mortality
                (
                    batch_id,
                    mortality_date,
                    quantity,
                    reason,
                    notes,
                    recorded_by
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $batchId,
                $mortalityDate,
                $quantity,
                $reason !== '' ? $reason : null,
                $notes !== '' ? $notes : null,
                currentUserId()
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Current Birds
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE poultry_batches
                SET current_quantity = current_quantity - ?
                WHERE id = ?
                  AND current_quantity >= ?
            ");

            $stmt->execute([
                $quantity,
                $batchId,
                $quantity
            ]);


            if ($stmt->rowCount() !== 1) {

                throw new RuntimeException(
                    'Unable to update the current bird quantity.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Commit Transaction
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | Notify Other Active Users
            |--------------------------------------------------------------------------
            |
            | Notification is sent AFTER commit so users are notified
            | only after the mortality record has actually been saved.
            |
            */

            $reasonText =
                $reason !== ''
                    ? ' Reason: ' . $reason . '.'
                    : '';

            notifyOtherUsers(
                'New Mortality Recorded',
                sprintf(
                    '%s recorded %d bird%s mortality in batch "%s" on %s.%s',
                    $_SESSION['full_name'] ?? 'A user',
                    $quantity,
                    $quantity === 1 ? '' : 's',
                    $batch['batch_name'],
                    $mortalityDate,
                    $reasonText
                ),
                'mortality',
                'index.php'
            );


            /*
            |--------------------------------------------------------------------------
            | Redirect
            |--------------------------------------------------------------------------
            */

            redirect('index.php');


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = $e->getMessage();
        }
    }
}


$pageTitle = 'Record Mortality';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>💀 Record Mortality</h2>

        <p>
            Record bird mortality and automatically update the batch quantity.
        </p>

    </div>

    <a
        href="index.php"
        class="btn btn-secondary"
    >
        ← Back
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

                        <option
                            value="<?= (int) $batch['id'] ?>"
                            <?= (
                                ($_POST['batch_id'] ?? '')
                                == $batch['id']
                            ) ? 'selected' : '' ?>
                        >

                            <?= e($batch['batch_name']) ?>

                            —

                            <?= e($batch['bird_type']) ?>

                            —

                            <?= number(
                                (int) $batch['current_quantity']
                            ) ?>

                            birds remaining

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
                        ?? date('Y-m-d')
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
                        $_POST['quantity'] ?? ''
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
                        $_POST['reason'] ?? ''
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
                    $_POST['notes'] ?? ''
                ) ?></textarea>

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
                class="btn btn-danger"
            >
                💀 Record Mortality
            </button>

        </div>


    </form>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>