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
| Get Existing Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM egg_production
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
        current_quantity
    FROM poultry_batches
    WHERE status = 'active'
       OR id = {$record['batch_id']}
    ORDER BY batch_name ASC
");

$batches = $stmt->fetchAll();

$errors = [];


/*
|--------------------------------------------------------------------------
| Update Record
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


    if (!$batchId) {
        $errors[] = 'Please select a poultry batch.';
    }

    if ($productionDate === '') {
        $errors[] = 'Production date is required.';
    }

    if (
        $eggsCollected === false ||
        $eggsCollected === null ||
        $eggsCollected < 0
    ) {
        $errors[] = 'Eggs collected must be zero or greater.';
    }

    if (
        $brokenEggs === false ||
        $brokenEggs === null ||
        $brokenEggs < 0
    ) {
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
            LIMIT 1
        ");

        $stmt->execute([$batchId]);

        if (!$stmt->fetch()) {
            $errors[] = 'Selected poultry batch does not exist.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Date
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM egg_production
            WHERE batch_id = ?
              AND production_date = ?
              AND id != ?
            LIMIT 1
        ");

        $stmt->execute([
            $batchId,
            $productionDate,
            $id
        ]);

        if ($stmt->fetch()) {
            $errors[] =
                'A production record already exists for this batch on this date.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $stmt = $pdo->prepare("
            UPDATE egg_production

            SET
                batch_id = ?,
                production_date = ?,
                eggs_collected = ?,
                broken_eggs = ?,
                notes = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $batchId,
            $productionDate,
            $eggsCollected,
            $brokenEggs,
            $notes ?: null,
            $id
        ]);

        redirect('view.php?id=' . $id);
    }
}


$pageTitle = 'Edit Egg Production';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>✏️ Edit Egg Production</h2>

        <p>
            Update this production record.
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
                            <?= (
                                ($_POST['batch_id'] ?? $record['batch_id'])
                                == $batch['id']
                            ) ? 'selected' : '' ?>
                        >

                            <?= e($batch['batch_name']) ?>

                            —
                            <?= e($batch['bird_type']) ?>

                            (<?= number((int) $batch['current_quantity']) ?> birds)

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label for="production_date">
                    Production Date *
                </label>

                <input
                    type="date"
                    id="production_date"
                    name="production_date"
                    value="<?= e(
                        $_POST['production_date']
                        ?? $record['production_date']
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="eggs_collected">
                    Eggs Collected *
                </label>

                <input
                    type="number"
                    id="eggs_collected"
                    name="eggs_collected"
                    min="0"
                    value="<?= e(
                        $_POST['eggs_collected']
                        ?? $record['eggs_collected']
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="broken_eggs">
                    Broken Eggs *
                </label>

                <input
                    type="number"
                    id="broken_eggs"
                    name="broken_eggs"
                    min="0"
                    value="<?= e(
                        $_POST['broken_eggs']
                        ?? $record['broken_eggs']
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group form-group-full">

                <label for="notes">
                    Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
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
                class="btn btn-primary"
            >
                Save Changes
            </button>

        </div>

    </form>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>