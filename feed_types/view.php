<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Get Feed Type ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Feed Type
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        feed_name,
        description,
        unit,
        created_at
    FROM feed_types
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$feed = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Check Record
|--------------------------------------------------------------------------
*/

if (!$feed) {
    header('Location: index.php');
    exit;
}


$pageTitle = 'View Feed Type';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🌾 Feed Type Details</h2>

        <p>
            View information about this feed type.
        </p>

    </div>

    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Feed Types
        </a>

    </div>

</div>


<div class="dashboard-card">

    <div class="details-grid">


        <!-- Feed Name -->

        <div class="detail-item">

            <span class="detail-label">
                🌾 Feed Name
            </span>

            <strong class="detail-value">
                <?= e($feed['feed_name']) ?>
            </strong>

        </div>


        <!-- Description -->

        <div class="detail-item">

            <span class="detail-label">
                📝 Description
            </span>

            <strong class="detail-value">

                <?=
                    $feed['description']
                    ? e($feed['description'])
                    : 'No description provided'
                ?>

            </strong>

        </div>


        <!-- Unit -->

        <div class="detail-item">

            <span class="detail-label">
                📦 Unit
            </span>

            <strong class="detail-value">
                <?= e($feed['unit'] ?? 'bag') ?>
            </strong>

        </div>


        <!-- ID -->

        <div class="detail-item">

            <span class="detail-label">
                🆔 Feed ID
            </span>

            <strong class="detail-value">
                <?= (int)$feed['id'] ?>
            </strong>

        </div>


        <!-- Created -->

        <div class="detail-item">

            <span class="detail-label">
                📅 Date Added
            </span>

            <strong class="detail-value">
                <?= e($feed['created_at']) ?>
            </strong>

        </div>


    </div>


    <!-- Actions -->

    <div class="action-buttons">

        <a
            href="edit.php?id=<?= (int)$feed['id'] ?>"
            class="btn btn-primary"
        >
            ✏️ Edit Feed Type
        </a>


        <a
            href="delete.php?id=<?= (int)$feed['id'] ?>"
            class="btn btn-danger"
            onclick="
                return confirm(
                    'Are you sure you want to delete this feed type?'
                );
            "
        >
            🗑️ Delete Feed Type
        </a>


        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back
        </a>

    </div>

</div>


<style>

.details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.detail-item {
    padding: 20px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.detail-label {
    display: block;
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
}

.detail-value {
    display: block;
    font-size: 17px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-danger {
    background: #dc2626;
    color: #fff;
}

.btn-danger:hover {
    background: #b91c1c;
}

@media (max-width: 768px) {

    .details-grid {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .action-buttons .btn {
        width: 100%;
        text-align: center;
        box-sizing: border-box;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>