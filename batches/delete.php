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
| Check Batch
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, batch_name
    FROM poultry_batches
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$batch = $stmt->fetch();

if (!$batch) {
    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| Delete Batch
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirmation = $_POST['confirmation'] ?? '';

    if ($confirmation !== 'DELETE') {
        redirect('index.php');
    }

    $stmt = $pdo->prepare("
        DELETE FROM poultry_batches
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    redirect('index.php');
}


$pageTitle = 'Delete Poultry Batch';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>Delete Poultry Batch</h2>

        <p>
            This action cannot be undone.
        </p>

    </div>

</div>


<div class="dashboard-card">

    <div class="alert alert-danger">

        <h3>⚠️ Warning</h3>

        <p>
            You are about to permanently delete:
        </p>

        <strong>
            <?= e($batch['batch_name']) ?>
        </strong>

        <p style="margin-top: 12px;">
            Any related egg production, mortality and feed
            records connected to this batch may also be deleted.
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
                required
                autocomplete="off"
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