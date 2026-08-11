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
| Get Mortality Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        m.id,
        m.batch_id,
        m.mortality_date,
        m.quantity,
        m.reason,
        m.notes,
        pb.batch_name,
        pb.bird_type
    FROM mortality m
    INNER JOIN poultry_batches pb
        ON m.batch_id = pb.id
    WHERE m.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {
    redirect('index.php');
}


$errors = [];


/*
|--------------------------------------------------------------------------
| Delete Mortality
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirmation = trim(
        $_POST['confirmation'] ?? ''
    );

    if ($confirmation !== 'DELETE') {

        $errors[] =
            'Please type DELETE exactly to confirm.';
    }


    if (!$errors) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Mortality Record
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    batch_id,
                    quantity
                FROM mortality
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $mortality = $stmt->fetch();

            if (!$mortality) {

                throw new RuntimeException(
                    'This mortality record no longer exists.'
                );
            }


            $batchId =
                (int) $mortality['batch_id'];

            $quantity =
                (int) $mortality['quantity'];


            /*
            |--------------------------------------------------------------------------
            | Lock Poultry Batch
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    current_quantity
                FROM poultry_batches
                WHERE id = ?
                FOR UPDATE
            ");

            $stmt->execute([$batchId]);

            $batch = $stmt->fetch();

            if (!$batch) {

                throw new RuntimeException(
                    'The poultry batch associated with this record no longer exists.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Restore Birds
            |--------------------------------------------------------------------------
            */

            $currentQuantity =
                (int) $batch['current_quantity'];

            $restoredQuantity =
                $currentQuantity + $quantity;


            /*
            |--------------------------------------------------------------------------
            | Update Batch
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE poultry_batches
                SET current_quantity = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $restoredQuantity,
                $batchId
            ]);


            /*
            |--------------------------------------------------------------------------
            | Delete Mortality Record
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                DELETE FROM mortality
                WHERE id = ?
            ");

            $stmt->execute([$id]);


            if ($stmt->rowCount() !== 1) {

                throw new RuntimeException(
                    'The mortality record could not be deleted.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            redirect('index.php');


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $errors[] = $e->getMessage();
        }
    }
}


$pageTitle = 'Delete Mortality';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🗑️ Delete Mortality</h2>

        <p>
            Remove this mortality record and restore the birds to the batch.
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



    <div class="alert alert-danger">

        <h3>
            ⚠️ Confirm Deletion
        </h3>


        <p>
            You are about to permanently delete this mortality record.
        </p>


        <p>

            <strong>Batch:</strong>
            <?= e($record['batch_name']) ?>

            <br>

            <strong>Bird Type:</strong>
            <?= e($record['bird_type']) ?>

            <br>

            <strong>Date:</strong>
            <?= e($record['mortality_date']) ?>

            <br>

            <strong>Birds Lost:</strong>
            <?= number((int) $record['quantity']) ?>

            <br>

            <strong>Reason:</strong>
            <?= e($record['reason'] ?: 'Not specified') ?>

        </p>


        <p>
            <strong>Important:</strong>
            Deleting this record will automatically restore
            <?= number((int) $record['quantity']) ?>
            birds to the selected batch.
        </p>

    </div>



    <form method="POST">


        <div class="form-group">

            <label for="confirmation">

                Type
                <strong>DELETE</strong>
                to confirm:

            </label>


            <input
                type="text"
                name="confirmation"
                id="confirmation"
                autocomplete="off"
                placeholder="Type DELETE"
                required
            >

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
                class="btn btn-danger"
            >
                🗑️ Permanently Delete
            </button>

        </div>


    </form>

</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>