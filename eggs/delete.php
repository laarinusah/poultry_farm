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
| Get Production Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        ep.id,
        ep.production_date,
        ep.eggs_collected,
        ep.broken_eggs,
        pb.batch_name
    FROM egg_production ep
    INNER JOIN poultry_batches pb
        ON ep.batch_id = pb.id
    WHERE ep.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {
    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirmation = $_POST['confirmation'] ?? '';

    if ($confirmation !== 'DELETE') {
        redirect('index.php');
    }

    $stmt = $pdo->prepare("
        DELETE FROM egg_production
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    redirect('index.php');
}


$pageTitle = 'Delete Egg Production';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>🗑️ Delete Egg Production</h2>

        <p>
            This action cannot be undone.
        </p>

    </div>

</div>


<div class="dashboard-card">

    <div class="alert alert-danger">

        <h3>⚠️ Confirm Deletion</h3>

        <p>
            You are about to delete the following production record:
        </p>

        <p>

            <strong>Batch:</strong>
            <?= e($record['batch_name']) ?>

            <br>

            <strong>Date:</strong>
            <?= e($record['production_date']) ?>

            <br>

            <strong>Eggs Collected:</strong>
            <?= number((int) $record['eggs_collected']) ?>

            <br>

            <strong>Broken Eggs:</strong>
            <?= number((int) $record['broken_eggs']) ?>

        </p>

        <p>
            The record will be permanently removed.
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
                id="confirmation"
                name="confirmation"
                autocomplete="off"
                required
            >

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
                Permanently Delete
            </button>

        </div>

    </form>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>