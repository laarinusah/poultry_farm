<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Add Poultry Batch';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batchName = trim($_POST['batch_name'] ?? '');
    $birdType = trim($_POST['bird_type'] ?? '');
    $breed = trim($_POST['breed'] ?? '');

    $initialQuantity = (int) ($_POST['initial_quantity'] ?? 0);

    $dateStarted = $_POST['date_started'] ?? '';

    $expectedSaleDate = $_POST['expected_sale_date'] ?? '';

    $status = $_POST['status'] ?? 'active';

    $notes = trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($batchName === '') {
        $errors[] = 'Batch name is required.';
    }

    if ($birdType === '') {
        $errors[] = 'Bird type is required.';
    }

    if ($initialQuantity <= 0) {
        $errors[] = 'Initial bird quantity must be greater than zero.';
    }

    if ($dateStarted === '') {
        $errors[] = 'Start date is required.';
    }

    if (!in_array(
        $status,
        ['active', 'sold', 'completed'],
        true
    )) {
        $errors[] = 'Invalid batch status.';
    }


    /*
    |--------------------------------------------------------------------------
    | Save Poultry Batch
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO poultry_batches
                (
                    batch_name,
                    bird_type,
                    breed,
                    initial_quantity,
                    current_quantity,
                    date_started,
                    expected_sale_date,
                    status,
                    notes,
                    created_by
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");


            $stmt->execute([
                $batchName,
                $birdType,
                $breed ?: null,
                $initialQuantity,
                $initialQuantity,
                $dateStarted,
                $expectedSaleDate ?: null,
                $status,
                $notes ?: null,
                currentUserId()
            ]);


            /*
            |--------------------------------------------------------------------------
            | Notify Other Users
            |--------------------------------------------------------------------------
            |
            | This runs only after the poultry batch has been successfully
            | inserted into the database.
            |
            */

            notifyOtherUsers(
                'New Poultry Batch Added',
                sprintf(
                    '%s added a new %s poultry batch "%s" with %s birds.',
                    $_SESSION['full_name'] ?? 'A user',
                    $birdType,
                    $batchName,
                    number($initialQuantity)
                ),
                'batch',
                'index.php'
            );


            /*
            |--------------------------------------------------------------------------
            | Redirect After Successful Save
            |--------------------------------------------------------------------------
            */

            redirect('index.php');


        } catch (Throwable $e) {

            $errors[] =
                'Unable to save the poultry batch. '
                . $e->getMessage();
        }
    }
}


require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>Add New Poultry Batch</h2>

        <p>
            Register a new flock or poultry batch.
        </p>

    </div>

    <a
        href="index.php"
        class="btn btn-secondary"
    >
        ← Back to Batches
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


            <!-- Batch Name -->

            <div class="form-group">

                <label for="batch_name">
                    Batch Name *
                </label>

                <input
                    type="text"
                    id="batch_name"
                    name="batch_name"
                    placeholder="e.g. Layers Batch 001"
                    value="<?= e(
                        $_POST['batch_name'] ?? ''
                    ) ?>"
                    required
                >

            </div>


            <!-- Bird Type -->

            <div class="form-group">

                <label for="bird_type">
                    Bird Type *
                </label>

                <select
                    id="bird_type"
                    name="bird_type"
                    required
                >

                    <option value="">
                        Select bird type
                    </option>

                    <option
                        value="Layers"
                        <?= (
                            ($_POST['bird_type'] ?? '')
                            === 'Layers'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Layers
                    </option>

                    <option
                        value="Broilers"
                        <?= (
                            ($_POST['bird_type'] ?? '')
                            === 'Broilers'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Broilers
                    </option>

                    <option
                        value="Cockerels"
                        <?= (
                            ($_POST['bird_type'] ?? '')
                            === 'Cockerels'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Cockerels
                    </option>

                    <option
                        value="Other"
                        <?= (
                            ($_POST['bird_type'] ?? '')
                            === 'Other'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Other
                    </option>

                </select>

            </div>


            <!-- Breed -->

            <div class="form-group">

                <label for="breed">
                    Breed
                </label>

                <input
                    type="text"
                    id="breed"
                    name="breed"
                    placeholder="e.g. ISA Brown"
                    value="<?= e(
                        $_POST['breed'] ?? ''
                    ) ?>"
                >

            </div>


            <!-- Initial Quantity -->

            <div class="form-group">

                <label for="initial_quantity">
                    Initial Number of Birds *
                </label>

                <input
                    type="number"
                    id="initial_quantity"
                    name="initial_quantity"
                    min="1"
                    step="1"
                    value="<?= e(
                        $_POST['initial_quantity'] ?? ''
                    ) ?>"
                    required
                >

            </div>


            <!-- Start Date -->

            <div class="form-group">

                <label for="date_started">
                    Start Date *
                </label>

                <input
                    type="date"
                    id="date_started"
                    name="date_started"
                    value="<?= e(
                        $_POST['date_started']
                        ?? date('Y-m-d')
                    ) ?>"
                    required
                >

            </div>


            <!-- Expected Sale Date -->

            <div class="form-group">

                <label for="expected_sale_date">
                    Expected Sale Date
                </label>

                <input
                    type="date"
                    id="expected_sale_date"
                    name="expected_sale_date"
                    value="<?= e(
                        $_POST['expected_sale_date']
                        ?? ''
                    ) ?>"
                >

            </div>


            <!-- Status -->

            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="active"
                        <?= (
                            ($_POST['status'] ?? 'active')
                            === 'active'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="completed"
                        <?= (
                            ($_POST['status'] ?? '')
                            === 'completed'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Completed
                    </option>

                    <option
                        value="sold"
                        <?= (
                            ($_POST['status'] ?? '')
                            === 'sold'
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Sold
                    </option>

                </select>

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
                    placeholder="Additional notes about this batch..."
                ><?= e(
                    $_POST['notes'] ?? ''
                ) ?></textarea>

            </div>

        </div>


        <!-- Buttons -->

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
                Save Batch
            </button>

        </div>

    </form>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
