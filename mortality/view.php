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
| Get Mortality Record
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        m.id,
        m.batch_id,
        m.mortality_date,
        m.quantity,
        m.reason,
        m.notes,
        m.recorded_by,
        m.created_at,

        pb.batch_name,
        pb.bird_type,
        pb.breed,
        pb.initial_quantity,
        pb.current_quantity,

        u.full_name AS recorded_by_name

    FROM mortality m

    INNER JOIN poultry_batches pb
        ON m.batch_id = pb.id

    LEFT JOIN users u
        ON m.recorded_by = u.id

    WHERE m.id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$record = $stmt->fetch();

if (!$record) {
    redirect('index.php');
}


$pageTitle = 'Mortality Details';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>💀 Mortality Details</h2>

        <p>
            Detailed information about this mortality record.
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



<!-- Summary -->

<div class="dashboard-grid">


    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            💀
        </div>

        <h6>Birds Lost</h6>

        <h3>
            <?= number((int) $record['quantity']) ?>
        </h3>

        <small>
            Recorded mortality
        </small>

    </div>



    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            🐔
        </div>

        <h6>Current Birds</h6>

        <h3>
            <?= number((int) $record['current_quantity']) ?>
        </h3>

        <small>
            Birds currently in batch
        </small>

    </div>



    <div class="dashboard-card">

        <div class="dashboard-card-icon">
            📅
        </div>

        <h6>Mortality Date</h6>

        <h3>
            <?= e($record['mortality_date']) ?>
        </h3>

        <small>
            Date recorded
        </small>

    </div>

</div>



<!-- Details -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>


    <h3>
        Mortality Information
    </h3>


    <div class="table-responsive">

        <table class="data-table">

            <tbody>


                <tr>

                    <th>
                        Mortality ID
                    </th>

                    <td>
                        #<?= (int) $record['id'] ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Mortality Date
                    </th>

                    <td>
                        <?= e(
                            $record['mortality_date']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Poultry Batch
                    </th>

                    <td>

                        <strong>
                            <?= e(
                                $record['batch_name']
                            ) ?>
                        </strong>

                    </td>

                </tr>


                <tr>

                    <th>
                        Bird Type
                    </th>

                    <td>
                        <?= e(
                            $record['bird_type']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Breed
                    </th>

                    <td>
                        <?= e(
                            $record['breed']
                            ?: '—'
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Initial Birds
                    </th>

                    <td>
                        <?= number(
                            (int) $record['initial_quantity']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Mortality Quantity
                    </th>

                    <td>

                        <strong>
                            <?= number(
                                (int) $record['quantity']
                            ) ?>
                        </strong>

                    </td>

                </tr>


                <tr>

                    <th>
                        Current Birds
                    </th>

                    <td>
                        <?= number(
                            (int) $record['current_quantity']
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Reason
                    </th>

                    <td>
                        <?= e(
                            $record['reason']
                            ?: 'Not specified'
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Notes
                    </th>

                    <td>

                        <?= nl2br(
                            e(
                                $record['notes']
                                ?: 'No notes'
                            )
                        ) ?>

                    </td>

                </tr>


                <tr>

                    <th>
                        Recorded By
                    </th>

                    <td>
                        <?= e(
                            $record['recorded_by_name']
                            ?? 'System'
                        ) ?>
                    </td>

                </tr>


                <tr>

                    <th>
                        Created At
                    </th>

                    <td>
                        <?= e(
                            $record['created_at']
                        ) ?>
                    </td>

                </tr>


            </tbody>

        </table>

    </div>

</div>



<!-- Actions -->

<div
    class="dashboard-card"
    style="margin-top: 24px;"
>


    <div class="action-buttons">

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
            ← Back to Mortality
        </a>

    </div>

</div>



<?php require_once __DIR__ . '/../includes/footer.php'; ?>