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

$stmt = $pdo->prepare("
    SELECT
        ep.*,
        pb.batch_name,
        pb.bird_type,
        pb.breed,
        u.full_name AS recorded_by_name
    FROM egg_production ep
    INNER JOIN poultry_batches pb
        ON ep.batch_id = pb.id
    LEFT JOIN users u
        ON ep.recorded_by = u.id
    WHERE ep.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {
    redirect('index.php');
}

$eggsCollected = (int) $record['eggs_collected'];
$brokenEggs = (int) $record['broken_eggs'];
$goodEggs = max(0, $eggsCollected - $brokenEggs);

$pageTitle = 'Egg Production Details';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>
        <h2>🥚 Egg Production Details</h2>
        <p>
            Production record for <?= e($record['batch_name']) ?>
        </p>
    </div>

    <div class="action-buttons">

        <a href="index.php" class="btn btn-secondary">
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


<div class="dashboard-grid">

    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Eggs Collected</h6>

        <h3>
            <?= number($eggsCollected) ?>
        </h3>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🥚
        </div>

        <h6>Good Eggs</h6>

        <h3>
            <?= number($goodEggs) ?>
        </h3>

    </div>


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💔
        </div>

        <h6>Broken Eggs</h6>

        <h3>
            <?= number($brokenEggs) ?>
        </h3>

    </div>

</div>


<div class="dashboard-card" style="margin-top: 24px;">

    <h3>Production Information</h3>

    <div class="table-responsive">

        <table class="data-table">

            <tbody>

                <tr>
                    <th>Production Date</th>
                    <td><?= e($record['production_date']) ?></td>
                </tr>

                <tr>
                    <th>Batch</th>
                    <td><?= e($record['batch_name']) ?></td>
                </tr>

                <tr>
                    <th>Bird Type</th>
                    <td><?= e($record['bird_type']) ?></td>
                </tr>

                <tr>
                    <th>Breed</th>
                    <td><?= e($record['breed'] ?: '—') ?></td>
                </tr>

                <tr>
                    <th>Eggs Collected</th>
                    <td><?= number($eggsCollected) ?></td>
                </tr>

                <tr>
                    <th>Broken Eggs</th>
                    <td><?= number($brokenEggs) ?></td>
                </tr>

                <tr>
                    <th>Good Eggs</th>
                    <td><?= number($goodEggs) ?></td>
                </tr>

                <tr>
                    <th>Recorded By</th>
                    <td><?= e($record['recorded_by_name'] ?? 'System') ?></td>
                </tr>

                <tr>
                    <th>Notes</th>
                    <td>
                        <?= nl2br(e($record['notes'] ?: 'No notes')) ?>
                    </td>
                </tr>

                <tr>
                    <th>Created At</th>
                    <td><?= e($record['created_at']) ?></td>
                </tr>

            </tbody>

        </table>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>