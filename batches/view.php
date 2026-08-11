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
        pb.*,
        u.full_name AS created_by_name
    FROM poultry_batches pb
    LEFT JOIN users u
        ON pb.created_by = u.id
    WHERE pb.id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$batch = $stmt->fetch();

if (!$batch) {
    redirect('index.php');
}

$pageTitle = 'Batch Details';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>
        <h2><?= e($batch['batch_name']) ?></h2>
        <p>Detailed information about this poultry batch.</p>
    </div>

    <div class="action-buttons">

        <a href="index.php" class="btn btn-secondary">
            ← Back
        </a>

        <a href="edit.php?id=<?= (int) $batch['id'] ?>" class="btn btn-warning">
            ✏️ Edit
        </a>

    </div>

</div>


<div class="dashboard-grid">

    <div class="dashboard-card">
        <div class="dashboard-card-icon">🐔</div>
        <h6>Bird Type</h6>
        <h3><?= e($batch['bird_type']) ?></h3>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-icon">🧬</div>
        <h6>Breed</h6>
        <h3><?= e($batch['breed'] ?: '—') ?></h3>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-icon">🐣</div>
        <h6>Initial Birds</h6>
        <h3><?= number((int) $batch['initial_quantity']) ?></h3>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-icon">🐔</div>
        <h6>Current Birds</h6>
        <h3><?= number((int) $batch['current_quantity']) ?></h3>
    </div>

</div>


<div class="dashboard-card" style="margin-top: 24px;">

    <h3>Batch Information</h3>

    <div class="table-responsive">

        <table class="data-table">

            <tbody>

                <tr>
                    <th>Batch Name</th>
                    <td><?= e($batch['batch_name']) ?></td>
                </tr>

                <tr>
                    <th>Bird Type</th>
                    <td><?= e($batch['bird_type']) ?></td>
                </tr>

                <tr>
                    <th>Breed</th>
                    <td><?= e($batch['breed'] ?: '—') ?></td>
                </tr>

                <tr>
                    <th>Initial Quantity</th>
                    <td><?= number((int) $batch['initial_quantity']) ?></td>
                </tr>

                <tr>
                    <th>Current Quantity</th>
                    <td><?= number((int) $batch['current_quantity']) ?></td>
                </tr>

                <tr>
                    <th>Start Date</th>
                    <td><?= e($batch['date_started']) ?></td>
                </tr>

                <tr>
                    <th>Expected Sale Date</th>
                    <td><?= e($batch['expected_sale_date'] ?: '—') ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td><?= e(ucfirst($batch['status'])) ?></td>
                </tr>

                <tr>
                    <th>Created By</th>
                    <td><?= e($batch['created_by_name'] ?: 'System') ?></td>
                </tr>

                <tr>
                    <th>Notes</th>
                    <td><?= nl2br(e($batch['notes'] ?: 'No notes')) ?></td>
                </tr>

            </tbody>

        </table>

    </div>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>