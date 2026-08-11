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
| Get Feed Usage Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        fu.id,
        fu.usage_date,
        fu.quantity,
        fu.notes,

        pb.batch_name,
        pb.bird_type,

        ft.feed_name,
        ft.unit

    FROM feed_usage fu

    INNER JOIN poultry_batches pb
        ON fu.batch_id = pb.id

    INNER JOIN feed_types ft
        ON fu.feed_type_id = ft.id

    WHERE fu.id = :id

    LIMIT 1
");

$stmt->execute([
    ':id' => $id
]);

$record = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Record Not Found
|--------------------------------------------------------------------------
*/

if (!$record) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Delete Confirmation
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirm = $_POST['confirm'] ?? '';

    if ($confirm === 'yes') {

        $stmt = $pdo->prepare("
            DELETE FROM feed_usage
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);


        header(
            'Location: index.php?deleted=1'
        );

        exit;
    }


    header(
        'Location: view.php?id=' . $id
    );

    exit;
}


$pageTitle = 'Delete Feed Usage';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🗑️ Delete Feed Usage</h2>

        <p>
            Please confirm before deleting this record.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="view.php?id=<?= (int) $record['id'] ?>"
            class="btn btn-secondary"
        >
            ← Back
        </a>

    </div>

</div>



<div class="dashboard-card">


    <div
        class="alert alert-danger"
        style="margin-bottom: 20px;"
    >

        <strong>
            ⚠️ Warning
        </strong>

        <p style="margin-top: 8px; margin-bottom: 0;">

            You are about to permanently delete this
            feed usage record.

            This action cannot be undone.

        </p>

    </div>



    <div class="details-grid">


        <div class="detail-item">

            <strong>
                📅 Usage Date
            </strong>

            <span>
                <?= e(
                    $record['usage_date']
                ) ?>
            </span>

        </div>



        <div class="detail-item">

            <strong>
                🐔 Poultry Batch
            </strong>

            <span>

                <?= e(
                    $record['batch_name']
                ) ?>

                <small>
                    (<?= e(
                        $record['bird_type']
                    ) ?>)
                </small>

            </span>

        </div>



        <div class="detail-item">

            <strong>
                🌾 Feed Type
            </strong>

            <span>
                <?= e(
                    $record['feed_name']
                ) ?>
            </span>

        </div>



        <div class="detail-item">

            <strong>
                📦 Quantity
            </strong>

            <span>

                <?= number_format(
                    (float) $record['quantity'],
                    2
                ) ?>

                <?= e(
                    $record['unit'] ?? 'bag'
                ) ?>

            </span>

        </div>



        <div class="detail-item">

            <strong>
                📝 Notes
            </strong>

            <span>

                <?php if (
                    !empty($record['notes'])
                ): ?>

                    <?= nl2br(
                        e($record['notes'])
                    ) ?>

                <?php else: ?>

                    <span style="color:#777;">
                        No notes provided.
                    </span>

                <?php endif; ?>

            </span>

        </div>


    </div>



    <!-- Confirmation Form -->

    <form
        method="POST"
        action=""
        style="margin-top: 25px;"
    >

        <div class="action-buttons">

            <button
                type="submit"
                name="confirm"
                value="yes"
                class="btn btn-danger"
                onclick="return confirm(
                    'Are you sure you want to permanently delete this feed record?'
                );"
            >
                🗑️ Yes, Delete Record
            </button>


            <a
                href="view.php?id=<?= (int) $record['id'] ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>


</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>