<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Record Egg Production';

$errors = [];

$productionDate = date('Y-m-d');
$batchId = '';
$eggsCollected = '';
$cratesRecorded = '';
$brokenEggs = '';
$notes = '';

/*
|--------------------------------------------------------------------------
| Load Active Poultry Batches
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
| Save Egg Production
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $productionDate = trim(
        $_POST['production_date'] ?? ''
    );

    $batchId = trim(
        $_POST['batch_id'] ?? ''
    );

    $eggsCollected = trim(
        $_POST['eggs_collected'] ?? ''
    );

    $cratesRecorded = trim(
        $_POST['crates_recorded'] ?? ''
    );

    $brokenEggs = trim(
        $_POST['broken_eggs'] ?? ''
    );

    $notes = trim(
        $_POST['notes'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validate Production Date
    |--------------------------------------------------------------------------
    */

    if ($productionDate === '') {

        $errors[] =
            'Production date is required.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Batch
    |--------------------------------------------------------------------------
    */

    if (
        $batchId === ''
        || !ctype_digit($batchId)
        || (int) $batchId <= 0
    ) {

        $errors[] =
            'Please select a poultry batch.';

    } else {

        $batchCheck = $pdo->prepare("
            SELECT
                id,
                batch_name
            FROM poultry_batches
            WHERE id = :id
            AND status = 'active'
            LIMIT 1
        ");

        $batchCheck->execute([
            ':id' => (int) $batchId
        ]);

        if (!$batchCheck->fetch()) {

            $errors[] =
                'Selected poultry batch does not exist or is not active.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Eggs Collected
    |--------------------------------------------------------------------------
    */

    if (
        $eggsCollected === ''
        || !ctype_digit($eggsCollected)
        || (int) $eggsCollected < 0
    ) {

        $errors[] =
            'Eggs collected must be zero or greater.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Daily Crates
    |--------------------------------------------------------------------------
    */

    if (
        $cratesRecorded === ''
        || !ctype_digit($cratesRecorded)
        || (int) $cratesRecorded < 0
    ) {

        $errors[] =
            'Daily crates must be zero or greater.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Broken Eggs
    |--------------------------------------------------------------------------
    */

    if (
        $brokenEggs === ''
        || !ctype_digit($brokenEggs)
        || (int) $brokenEggs < 0
    ) {

        $errors[] =
            'Broken eggs must be zero or greater.';
    }


    /*
    |--------------------------------------------------------------------------
    | Broken Eggs Cannot Exceed Eggs Collected
    |--------------------------------------------------------------------------
    */

    if (
        $eggsCollected !== ''
        && ctype_digit($eggsCollected)
        && $brokenEggs !== ''
        && ctype_digit($brokenEggs)
    ) {

        if (
            (int) $brokenEggs >
            (int) $eggsCollected
        ) {

            $errors[] =
                'Broken eggs cannot be greater than eggs collected.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save Production Record
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            $pdo->beginTransaction();

            $recordedBy = currentUserId();

            $stmt = $pdo->prepare("
                INSERT INTO egg_production (
                    batch_id,
                    production_date,
                    eggs_collected,
                    crates_recorded,
                    broken_eggs,
                    notes,
                    recorded_by
                )
                VALUES (
                    :batch_id,
                    :production_date,
                    :eggs_collected,
                    :crates_recorded,
                    :broken_eggs,
                    :notes,
                    :recorded_by
                )
            ");

            $stmt->execute([
                ':batch_id' =>
                    (int) $batchId,

                ':production_date' =>
                    $productionDate,

                ':eggs_collected' =>
                    (int) $eggsCollected,

                ':crates_recorded' =>
                    (int) $cratesRecorded,

                ':broken_eggs' =>
                    (int) $brokenEggs,

                ':notes' =>
                    $notes !== ''
                        ? $notes
                        : null,

                ':recorded_by' =>
                    $recordedBy
            ]);

            $pdo->commit();


            /*
            |--------------------------------------------------------------------------
            | Notify Other Partners
            |--------------------------------------------------------------------------
            */

            notifyOtherUsers(
                'New Egg Production Recorded',
                sprintf(
                    '%s recorded %s eggs and %s crates from poultry production.',
                    $_SESSION['full_name'] ?? 'A user',
                    number((int) $eggsCollected),
                    number((int) $cratesRecorded)
                ),
                'eggs',
                'index.php'
            );


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            redirect(
                'index.php?success=1'
            );

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }

            $errors[] =
                'Unable to save the egg production record. '
                . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>Record Egg Production</h2>

        <p>
            Record the daily egg production from the farm.
        </p>

    </div>

    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            Back to Egg Production
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

    <!-- ==========================================================
         PRODUCTION DATE
    =========================================================== -->

    <div class="form-group">

        <label for="production_date">
            Production Date
        </label>

        <input
            type="date"
            id="production_date"
            name="production_date"
            value="<?= e($productionDate) ?>"
            required
        >

        <small>
            Select the date the eggs were collected.
        </small>

    </div>


    <!-- ==========================================================
         POULTRY BATCH
    =========================================================== -->

    <div class="form-group">

        <label for="batch_id">
            Poultry Batch
        </label>

        <select
            id="batch_id"
            name="batch_id"
            required
        >

            <option value="">
                -- Select Poultry Batch --
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
            Select the poultry batch that produced these eggs.
        </small>

    </div>


    <!-- ==========================================================
         EGGS COLLECTED
    =========================================================== -->

    <div class="form-group">

        <label for="eggs_collected">
            Eggs Collected
        </label>

        <input
            type="number"
            id="eggs_collected"
            name="eggs_collected"
            value="<?= e($eggsCollected) ?>"
            min="0"
            step="1"
            placeholder="Enter total eggs collected"
            required
        >

        <small>
            Enter the total number of eggs collected for this production day.
        </small>

    </div>


    <!-- ==========================================================
         DAILY CRATES
    =========================================================== -->

    <div class="form-group">

        <label for="crates_recorded">
            Daily Crates
        </label>

        <input
            type="number"
            id="crates_recorded"
            name="crates_recorded"
            value="<?= e($cratesRecorded) ?>"
            min="0"
            step="1"
            placeholder="Enter number of crates"
            required
        >

        <small>
            Enter the number of crates recorded for this production day.
        </small>

    </div>


    <!-- ==========================================================
         BROKEN EGGS
    =========================================================== -->

    <div class="form-group">

        <label for="broken_eggs">
            Broken Eggs
        </label>

        <input
            type="number"
            id="broken_eggs"
            name="broken_eggs"
            value="<?= e($brokenEggs) ?>"
            min="0"
            step="1"
            placeholder="Enter number of broken eggs"
            required
        >

        <small>
            Enter the number of eggs damaged or broken.
        </small>

    </div>


    <!-- ==========================================================
         GOOD EGGS
    =========================================================== -->

    <div class="form-group">

        <label for="good_eggs">
            Good Eggs
        </label>

        <input
            type="text"
            id="good_eggs"
            value="0"
            readonly
        >

        <small>
            Good eggs are calculated automatically:
            eggs collected minus broken eggs.
        </small>

    </div>


    <!-- ==========================================================
         NOTES
    =========================================================== -->

    <div class="form-group">

        <label for="notes">
            Notes
        </label>

        <textarea
            id="notes"
            name="notes"
            rows="4"
            placeholder="Optional notes about this production record"
        ><?= e($notes) ?></textarea>

    </div>


    <!-- ==========================================================
         BUTTONS
    =========================================================== -->

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
            Save Egg Production
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
                    collectedValue -
                    brokenValue
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