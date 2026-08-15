<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$errors = [];

/*
|--------------------------------------------------------------------------
| Load Poultry Batches
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
| Load Feed Types
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        feed_name,
        description,
        unit
    FROM feed_types
    ORDER BY feed_name ASC
");

$feedTypes = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchId = (int) ($_POST['batch_id'] ?? 0);
    $feedTypeId = (int) ($_POST['feed_type_id'] ?? 0);
    $usageDate = trim($_POST['usage_date'] ?? '');
    $quantity = (float) ($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($batchId <= 0) {
        $errors[] = 'Please select a poultry batch.';
    }

    if ($feedTypeId <= 0) {
        $errors[] = 'Please select a feed type.';
    }

    if ($usageDate === '') {
        $errors[] = 'Please select the feed usage date.';
    }

    if ($quantity <= 0) {
        $errors[] = 'Feed quantity must be greater than 0.';
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Date
    |--------------------------------------------------------------------------
    */

    if ($usageDate !== '') {

        $date = DateTime::createFromFormat(
            'Y-m-d',
            $usageDate
        );

        if (
            !$date ||
            $date->format('Y-m-d') !== $usageDate
        ) {
            $errors[] = 'Please enter a valid date.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Insert Record
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Logged-in User
            |--------------------------------------------------------------------------
            */

            $recordedBy = currentUserId();


            /*
            |--------------------------------------------------------------------------
            | Get Batch Name
            |--------------------------------------------------------------------------
            */

            $batchStmt = $pdo->prepare("
                SELECT
                    batch_name
                FROM poultry_batches
                WHERE id = ?
                LIMIT 1
            ");

            $batchStmt->execute([
                $batchId
            ]);

            $batch = $batchStmt->fetch();


            if (!$batch) {

                throw new RuntimeException(
                    'Selected poultry batch was not found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Get Feed Type Name
            |--------------------------------------------------------------------------
            */

            $feedStmt = $pdo->prepare("
                SELECT
                    feed_name,
                    unit
                FROM feed_types
                WHERE id = ?
                LIMIT 1
            ");

            $feedStmt->execute([
                $feedTypeId
            ]);

            $feedType = $feedStmt->fetch();


            if (!$feedType) {

                throw new RuntimeException(
                    'Selected feed type was not found.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Insert Feed Usage
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO feed_usage
                (
                    batch_id,
                    feed_type_id,
                    usage_date,
                    quantity,
                    notes,
                    recorded_by
                )
                VALUES
                (
                    :batch_id,
                    :feed_type_id,
                    :usage_date,
                    :quantity,
                    :notes,
                    :recorded_by
                )
            ");


            $stmt->execute([
                ':batch_id'     => $batchId,
                ':feed_type_id' => $feedTypeId,
                ':usage_date'   => $usageDate,
                ':quantity'     => $quantity,
                ':notes'        => $notes !== ''
                    ? $notes
                    : null,
                ':recorded_by'  => $recordedBy
            ]);


            /*
            |--------------------------------------------------------------------------
            | Notify Other Users
            |--------------------------------------------------------------------------
            */

            notifyOtherUsers(
                'New Feed Usage Recorded',
                sprintf(
                    '%s recorded %.2f %s of %s feed for batch "%s" on %s.',
                    $_SESSION['full_name'] ?? 'A user',
                    $quantity,
                    $feedType['unit'] ?? 'units',
                    $feedType['feed_name'],
                    $batch['batch_name'],
                    $usageDate
                ),
                'feed',
                'index.php'
            );


            /*
            |--------------------------------------------------------------------------
            | Redirect After Successful Save
            |--------------------------------------------------------------------------
            */

            redirect('index.php?success=1');


        } catch (Throwable $e) {

            $errors[] = $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Record Feed Usage';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🌾 Record Feed Usage</h2>

        <p>
            Record feed consumed by a poultry batch.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Feed Management
        </a>

    </div>

</div>



<?php if (!empty($errors)): ?>

    <div class="alert alert-danger">

        <strong>
            Please correct the following:
        </strong>

        <ul style="margin-bottom: 0;">

            <?php foreach ($errors as $error): ?>

                <li>
                    <?= e($error) ?>
                </li>

            <?php endforeach; ?>

        </ul>

    </div>

<?php endif; ?>



<div class="dashboard-card">


    <form
        method="POST"
        action=""
    >


        <!-- Batch -->

        <div class="form-group">

            <label for="batch_id">
                🐔 Poultry Batch
            </label>

            <select
                name="batch_id"
                id="batch_id"
                class="form-control"
                required
            >

                <option value="">
                    -- Select Poultry Batch --
                </option>


                <?php foreach ($batches as $batch): ?>

                    <option
                        value="<?= (int) $batch['id'] ?>"
                        <?= (
                            (int) ($_POST['batch_id'] ?? 0)
                            === (int) $batch['id']
                        ) ? 'selected' : '' ?>
                    >

                        <?= e($batch['batch_name']) ?>

                        -
                        <?= e($batch['bird_type']) ?>

                        (<?= number(
                            (int) $batch['current_quantity']
                        ) ?> birds)

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- Feed Type -->

        <div class="form-group">

            <label for="feed_type_id">
                🌾 Feed Type
            </label>

            <select
                name="feed_type_id"
                id="feed_type_id"
                class="form-control"
                required
            >

                <option value="">
                    -- Select Feed Type --
                </option>


                <?php foreach ($feedTypes as $feed): ?>

                    <option
                        value="<?= (int) $feed['id'] ?>"
                        <?= (
                            (int) ($_POST['feed_type_id'] ?? 0)
                            === (int) $feed['id']
                        ) ? 'selected' : '' ?>
                    >

                        <?= e($feed['feed_name']) ?>

                        (<?= e($feed['unit'] ?? 'bag') ?>)

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- Date -->

        <div class="form-group">

            <label for="usage_date">
                📅 Usage Date
            </label>

            <input
                type="date"
                name="usage_date"
                id="usage_date"
                class="form-control"
                value="<?= e(
                    $_POST['usage_date']
                    ?? date('Y-m-d')
                ) ?>"
                required
            >

        </div>



        <!-- Quantity -->

        <div class="form-group">

            <label for="quantity">
                📦 Quantity Used
            </label>

            <input
                type="number"
                name="quantity"
                id="quantity"
                class="form-control"
                min="0.01"
                step="0.01"
                placeholder="Enter quantity used"
                value="<?= e(
                    $_POST['quantity'] ?? ''
                ) ?>"
                required
            >

            <small>
                Enter the amount of feed used.
            </small>

        </div>



        <!-- Notes -->

        <div class="form-group">

            <label for="notes">
                📝 Notes
            </label>

            <textarea
                name="notes"
                id="notes"
                class="form-control"
                rows="4"
                placeholder="Optional notes about this feed usage..."
            ><?= e(
                $_POST['notes'] ?? ''
            ) ?></textarea>

        </div>



        <!-- Buttons -->

        <div class="action-buttons">

            <button
                type="submit"
                class="btn btn-primary"
            >
                💾 Save Feed Usage
            </button>


            <a
                href="index.php"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>


    </form>

</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>