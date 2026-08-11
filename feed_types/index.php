<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$pageTitle = 'Feed Types';

/*
|--------------------------------------------------------------------------
| Get Feed Types
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        feed_name,
        description,
        unit,
        created_at
    FROM feed_types
    ORDER BY feed_name ASC
");

$feedTypes = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">

    <div>

        <h2>🌾 Feed Types</h2>

        <p>
            Manage the different types of feed used on the farm.
        </p>

    </div>

    <div>

        <a
            href="add.php"
            class="btn btn-primary"
        >
            ➕ Add Feed Type
        </a>

    </div>

</div>


<?php if (isset($_GET['added'])): ?>

    <div class="alert alert-success"
         style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
         ">

        ✅ Feed type added successfully.

    </div>

<?php endif; ?>


<?php if (isset($_GET['updated'])): ?>

    <div class="alert alert-success"
         style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
         ">

        ✅ Feed type updated successfully.

    </div>

<?php endif; ?>


<?php if (isset($_GET['deleted'])): ?>

    <div class="alert alert-success"
         style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
         ">

        ✅ Feed type deleted successfully.

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <div
        style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            gap:10px;
            flex-wrap:wrap;
        "
    >

        <div>

            <h3>🌾 Feed Type Records</h3>

            <p>
                View and manage all farm feed types.
            </p>

        </div>

    </div>


    <?php if (empty($feedTypes)): ?>

        <div
            style="
                text-align:center;
                padding:40px 20px;
            "
        >

            <div style="font-size:50px;">
                🌾
            </div>

            <h3>
                No Feed Types Found
            </h3>

            <p>
                You have not added any feed types yet.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                ➕ Add First Feed Type
            </a>

        </div>

    <?php else: ?>

        <div style="overflow-x:auto;">

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    min-width:800px;
                "
            >

                <thead>

                    <tr>

                        <th style="padding:12px; text-align:left;">
                            #
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Feed Name
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Description
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Unit
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Date Added
                        </th>

                        <th style="padding:12px; text-align:center;">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($feedTypes as $feed): ?>

                        <tr>

                            <td style="padding:12px;">
                                <?= (int)$feed['id'] ?>
                            </td>


                            <td style="padding:12px;">

                                <strong>
                                    🌾 <?= e($feed['feed_name']) ?>
                                </strong>

                            </td>


                            <td style="padding:12px;">

                                <?= e(
                                    $feed['description'] ?? '—'
                                ) ?>

                            </td>


                            <td style="padding:12px;">

                                <?= e(
                                    $feed['unit'] ?? 'bag'
                                ) ?>

                            </td>


                            <td style="padding:12px;">

                                <?= e(
                                    $feed['created_at']
                                ) ?>

                            </td>


                            <td
                                style="
                                    padding:12px;
                                    text-align:center;
                                    white-space:nowrap;
                                "
                            >

                                <a
                                    href="view.php?id=<?= (int)$feed['id'] ?>"
                                    class="btn btn-secondary"
                                >
                                    👁️ View
                                </a>


                                <a
                                    href="edit.php?id=<?= (int)$feed['id'] ?>"
                                    class="btn btn-primary"
                                >
                                    ✏️ Edit
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
                                    🗑️ Delete
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>