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
        fu.batch_id,
        fu.feed_type_id,
        fu.usage_date,
        fu.quantity,
        fu.notes,
        fu.created_at,

        pb.batch_name,
        pb.bird_type,
        pb.current_quantity,

        ft.feed_name,
        ft.description AS feed_description,
        ft.unit,

        u.full_name AS recorded_by_name

    FROM feed_usage fu

    INNER JOIN poultry_batches pb
        ON fu.batch_id = pb.id

    INNER JOIN feed_types ft
        ON fu.feed_type_id = ft.id

    LEFT JOIN users u
        ON fu.recorded_by = u.id

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


$pageTitle = 'View Feed Usage';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🌾 Feed Usage Details</h2>

        <p>
            View the details of this feed usage record.
        </p>

    </div>


    <div class="action-buttons">

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back
        </a>

        <a
            href="edit.php?id=<?= (int) $record['id'] ?>"
            class="btn btn-warning"
        >
            ✏️ Edit
        </a>

    </div>

</div>



<div class="dashboard-card">


    <h3 style="margin-bottom: 20px;">
        Feed Usage #<?= (int) $record['id'] ?>
    </h3>


    <div class="details-grid">


        <div class="detail-item">

            <strong>
                📅 Usage Date
            </strong>

            <span>
                <?= e($record['usage_date']) ?>
            </span>

        </div>



        <div class="detail-item">

            <strong>
                🐔 Poultry Batch
            </strong>

            <span>

                <?= e($record['batch_name']) ?>

                <small>
                    (<?= e($record['bird_type']) ?>)
                </small>

            </span>

        </div>



        <div class="detail-item">

            <strong>
                🐔 Current Birds
            </strong>

            <span>

                <?= number(
                    (int) $record['current_quantity']
                ) ?>

                birds

            </span>

        </div>



        <div class="detail-item">

            <strong>
                🌾 Feed Type
            </strong>

            <span>
                <?= e($record['feed_name']) ?>
            </span>

        </div>



        <div class="detail-item">

            <strong>
                📦 Quantity Used
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
                👤 Recorded By
            </strong>

            <span>

                <?= e(
                    $record['recorded_by_name']
                    ?? 'System'
                ) ?>

            </span>

        </div>



        <div class="detail-item">

            <strong>
                🕐 Created At
            </strong>

            <span>
                <?= e($record['created_at']) ?>
            </span>

        </div>



        <div class="detail-item">

            <strong>
                📝 Notes
            </strong>

            <span>

                <?php if (!empty($record['notes'])): ?>

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


    <div class="action-buttons" style="margin-top: 25px;">

        <a
            href="edit.php?id=<?= (int) $record['id'] ?>"
            class="btn btn-warning"
        >
            ✏️ Edit Record
        </a>

        <a
            href="delete.php?id=<?= (int) $record['id'] ?>"
            class="btn btn-danger"
        >
            🗑️ Delete Record
        </a>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Feed Management
        </a>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>