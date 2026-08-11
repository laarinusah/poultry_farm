<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Poultry Batches';

$stmt = $pdo->query("
    SELECT
        pb.id,
        pb.batch_name,
        pb.bird_type,
        pb.breed,
        pb.initial_quantity,
        pb.current_quantity,
        pb.date_started,
        pb.expected_sale_date,
        pb.status,
        pb.notes,
        u.full_name AS created_by_name
    FROM poultry_batches pb
    LEFT JOIN users u
        ON pb.created_by = u.id
    ORDER BY pb.created_at DESC
");

$batches = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>
        <h2>Poultry Batches</h2>

        <p>
            Manage your poultry flocks and batches.
        </p>
    </div>

    <a
        href="add.php"
        class="btn btn-primary"
    >
        + Add New Batch
    </a>

</div>


<div class="dashboard-card">

    <?php if (empty($batches)): ?>

        <div class="empty-state">

            <div style="font-size: 45px;">
                🐔
            </div>

            <h3>No Poultry Batches Yet</h3>

            <p>
                Start by adding your first poultry batch.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                + Add First Batch
            </a>

        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>Batch</th>
                        <th>Bird Type</th>
                        <th>Breed</th>
                        <th>Initial Birds</th>
                        <th>Current Birds</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach ($batches as $batch): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= e($batch['batch_name']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= e($batch['bird_type']) ?>
                        </td>

                        <td>
                            <?= e($batch['breed'] ?: '—') ?>
                        </td>

                        <td>
                            <?= number((int) $batch['initial_quantity']) ?>
                        </td>

                        <td>
                            <?= number((int) $batch['current_quantity']) ?>
                        </td>

                        <td>
                            <?= e($batch['date_started']) ?>
                        </td>

                        <td>

                            <?php if ($batch['status'] === 'active'): ?>

                                <span class="status-badge status-active">
                                    Active
                                </span>

                            <?php elseif ($batch['status'] === 'sold'): ?>

                                <span class="status-badge status-sold">
                                    Sold
                                </span>

                            <?php else: ?>

                                <span class="status-badge status-completed">
                                    Completed
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <div class="action-buttons">

                                <a
                                    href="view.php?id=<?= (int) $batch['id'] ?>"
                                    class="btn btn-small btn-secondary"
                                >
                                    View
                                </a>

                                <a
                                    href="edit.php?id=<?= (int) $batch['id'] ?>"
                                    class="btn btn-small btn-warning"
                                >
                                    Edit
                                </a>

                                <a
                                    href="delete.php?id=<?= (int) $batch['id'] ?>"
                                    class="btn btn-small btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this batch? This will also remove its related production records.');"
                                >
                                    Delete
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>