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
| Get Poultry Batches
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        batch_name,
        bird_type,
        current_quantity,
        status
    FROM poultry_batches
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

    $productionDate = trim(
        $_POST['production_date'] ?? ''
    );

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

    if ($productionDate === '') {
        $errors[] = 'Production date is required.';
    }

    if (
        $eggsCollected === false ||
        $eggsCollected === null ||
        $eggsCollected < 0
    ) {
        $errors[] =
            'Eggs collected must be zero or greater.';
    }

    if (
        $brokenEggs === false ||
        $brokenEggs === null ||
        $brokenEggs < 0
    ) {
        $errors[] =
            'Broken eggs must be zero or greater.';
    }

    if (
        $eggsCollected !== false &&
        $eggsCollected !== null &&
        $brokenEggs !== false &&
        $brokenEggs !== null &&
        $brokenEggs > $eggsCollected
    ) {
        $errors[] =
            'Broken eggs cannot be greater than eggs collected.';
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Batch
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM poultry_batches
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $batchId
        ]);

        if (!$stmt->fetch()) {

            $errors[] =
                'Selected poultry batch does not exist.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Production Record
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

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

    if (empty($errors)) {

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
            $notes !== ''
                ? $notes
                : null,
            $id
        ]);


        redirect(
            'view.php?id=' . $id
        );
    }
}


$pageTitle = 'Edit Egg Production';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>
            Edit Egg Production
        </h2>

        <p>
            Update this egg production record.
        </p>

    </div>


    <a
        href="view.php?id=<?= (int) $id ?>"
        class="btn btn-secondary"
    >
        Back
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


            <!-- Poultry Batch -->

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

                        <?php

                        $selectedBatch =
                            $_POST['batch_id']
                            ?? $record['batch_id'];

                        ?>

                        <option
                            value="<?= (int) $batch['id'] ?>"
                            <?= (
                                (string) $selectedBatch
                                ===
                                (string) $batch['id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e(
                                $batch['batch_name']
                            ) ?>

                            <?php if (
                                !empty(
                                    $batch['bird_type']
                                )
                            ): ?>

                                -
                                <?= e(
                                    $batch['bird_type']
                                ) ?>

                            <?php endif; ?>

                            (
                            <?= number(
                                (int)
                                $batch['current_quantity']
                            ) ?>
                            birds
                            )

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Production Date -->

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


            <!-- Eggs Collected -->

            <div class="form-group">

                <label for="eggs_collected">
                    Eggs Collected *
                </label>

                <input
                    type="number"
                    id="eggs_collected"
                    name="eggs_collected"
                    min="0"
                    step="1"
                    value="<?= e(
                        $_POST['eggs_collected']
                        ?? $record['eggs_collected']
                    ) ?>"
                    required
                >

                <small>
                    Enter the total number of eggs collected.
                </small>

            </div>


            <!-- Broken Eggs -->

            <div class="form-group">

                <label for="broken_eggs">
                    Broken Eggs *
                </label>

                <input
                    type="number"
                    id="broken_eggs"
                    name="broken_eggs"
                    min="0"
                    step="1"
                    value="<?= e(
                        $_POST['broken_eggs']
                        ?? $record['broken_eggs']
                    ) ?>"
                    required
                >

                <small>
                    Broken eggs cannot exceed eggs collected.
                </small>

            </div>


            <!-- Good Eggs -->

            <div class="form-group">

                <label for="good_eggs">
                    Good Eggs
                </label>

                <?php

                $displayCollected = (int) (
                    $_POST['eggs_collected']
                    ?? $record['eggs_collected']
                );

                $displayBroken = (int) (
                    $_POST['broken_eggs']
                    ?? $record['broken_eggs']
                );

                $displayGood = max(
                    0,
                    $displayCollected - $displayBroken
                );

                ?>

                <input
                    type="text"
                    id="good_eggs"
                    value="<?= number($displayGood) ?>"
                    readonly
                >

                <small>
                    Eggs collected minus broken eggs.
                </small>

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
                    placeholder="Additional notes about this production record..."
                ><?= e(
                    $_POST['notes']
                    ?? $record['notes']
                    ?? ''
                ) ?></textarea>

            </div>

        </div>


        <!-- Buttons -->

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


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const collected =
            document.getElementById(
                'eggs_collected'
            );

        const broken =
            document.getElementById(
                'broken_eggs'
            );

        const good =
            document.getElementById(
                'good_eggs'
            );


        function calculateGoodEggs() {

            const collectedValue =
                parseInt(
                    collected.value,
                    10
                ) || 0;

            const brokenValue =
                parseInt(
                    broken.value,
                    10
                ) || 0;


            const goodValue =
                Math.max(
                    0,
                    collectedValue - brokenValue
                );


            good.value =
                goodValue.toLocaleString();
        }


        collected.addEventListener(
            'input',
            calculateGoodEggs
        );

        broken.addEventListener(
            'input',
            calculateGoodEggs
        );


        calculateGoodEggs();

    }
);

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>