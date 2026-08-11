<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Get Record ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Existing Feed Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        batch_id,
        feed_type_id,
        usage_date,
        quantity,
        notes
    FROM feed_usage
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$record = $stmt->fetch();


if (!$record) {
    header('Location: index.php');
    exit;
}


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


$errors = [];


/*
|--------------------------------------------------------------------------
| Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchId = (int) ($_POST['batch_id'] ?? 0);

    $feedTypeId = (int) (
        $_POST['feed_type_id'] ?? 0
    );

    $usageDate = trim(
        $_POST['usage_date'] ?? ''
    );

    $quantity = (float) (
        $_POST['quantity'] ?? 0
    );

    $notes = trim(
        $_POST['notes'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($batchId <= 0) {
        $errors[] =
            'Please select a poultry batch.';
    }


    if ($feedTypeId <= 0) {
        $errors[] =
            'Please select a feed type.';
    }


    if ($usageDate === '') {
        $errors[] =
            'Please select the usage date.';
    }


    if ($quantity <= 0) {
        $errors[] =
            'Feed quantity must be greater than 0.';
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
            $errors[] =
                'Please enter a valid date.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Record
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE feed_usage

            SET
                batch_id = :batch_id,
                feed_type_id = :feed_type_id,
                usage_date = :usage_date,
                quantity = :quantity,
                notes = :notes

            WHERE id = :id
        ");


        $stmt->execute([

            ':batch_id' =>
                $batchId,

            ':feed_type_id' =>
                $feedTypeId,

            ':usage_date' =>
                $usageDate,

            ':quantity' =>
                $quantity,

            ':notes' =>
                $notes !== ''
                    ? $notes
                    : null,

            ':id' =>
                $id
        ]);


        header(
            'Location: view.php?id='
            . $id
            . '&updated=1'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Preserve Submitted Values
    |--------------------------------------------------------------------------
    */

    $record['batch_id'] =
        $batchId;

    $record['feed_type_id'] =
        $feedTypeId;

    $record['usage_date'] =
        $usageDate;

    $record['quantity'] =
        $quantity;

    $record['notes'] =
        $notes;
}


$pageTitle = 'Edit Feed Usage';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>✏️ Edit Feed Usage</h2>

        <p>
            Update this feed usage record.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="view.php?id=<?= (int) $id ?>"
            class="btn btn-secondary"
        >
            ← Back to Record
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


        <!-- Poultry Batch -->

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
                            (int) $record['batch_id']
                            === (int) $batch['id']
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= e(
                            $batch['batch_name']
                        ) ?>

                        -
                        <?= e(
                            $batch['bird_type']
                        ) ?>

                        (<?= number(
                            (int) $batch[
                                'current_quantity'
                            ]
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
                            (int) $record[
                                'feed_type_id'
                            ]
                            === (int) $feed['id']
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= e(
                            $feed['feed_name']
                        ) ?>

                        (<?= e(
                            $feed['unit']
                            ?? 'bag'
                        ) ?>)

                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <!-- Usage Date -->

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
                    $record['usage_date']
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
                value="<?= e(
                    (string) $record['quantity']
                ) ?>"
                required
            >

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
                placeholder="Optional notes..."
            ><?= e(
                $record['notes'] ?? ''
            ) ?></textarea>

        </div>



        <!-- Buttons -->

        <div class="action-buttons">

            <button
                type="submit"
                class="btn btn-primary"
            >
                💾 Update Feed Usage
            </button>


            <a
                href="view.php?id=<?= (int) $id ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>


    </form>

</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>