<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        full_name,
        username,
        email,
        role,
        status,
        last_login,
        created_at
    FROM users
    ORDER BY id DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Page Title
|--------------------------------------------------------------------------
*/

$pageTitle = 'Users';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>👥 Users</h2>

        <p>
            Manage users and their access to the Poultry Farm Management System.
        </p>

    </div>


    <div>

        <a
            href="add.php"
            class="btn btn-primary"
        >
            ➕ Add User
        </a>

    </div>

</div>


<?php if (isset($_GET['added'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ User added successfully.

    </div>

<?php endif; ?>


<?php if (isset($_GET['updated'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ User updated successfully.

    </div>

<?php endif; ?>


<?php if (isset($_GET['deleted'])): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ User deleted successfully.

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

            <h3>
                👥 User Accounts
            </h3>

            <p>
                View and manage all registered users.
            </p>

        </div>

    </div>


    <?php if (empty($users)): ?>

        <div
            style="
                text-align:center;
                padding:40px 20px;
            "
        >

            <div style="font-size:50px;">
                👥
            </div>

            <h3>
                No Users Found
            </h3>

            <p>
                No user accounts have been created yet.
            </p>

            <a
                href="add.php"
                class="btn btn-primary"
            >
                ➕ Add First User
            </a>

        </div>

    <?php else: ?>


        <div style="overflow-x:auto;">

            <table
                style="
                    width:100%;
                    border-collapse:collapse;
                    min-width:1000px;
                "
            >

                <thead>

                    <tr>

                        <th style="padding:12px; text-align:left;">
                            #
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Name
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Username
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Email
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Role
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Status
                        </th>

                        <th style="padding:12px; text-align:left;">
                            Last Login
                        </th>

                        <th style="padding:12px; text-align:center;">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($users as $user): ?>

                        <tr>

                            <!-- ID -->

                            <td style="padding:12px;">

                                <?= (int)$user['id'] ?>

                            </td>


                            <!-- Name -->

                            <td style="padding:12px;">

                                <strong>
                                    <?= e($user['full_name']) ?>
                                </strong>

                            </td>


                            <!-- Username -->

                            <td style="padding:12px;">

                                <?= e($user['username']) ?>

                            </td>


                            <!-- Email -->

                            <td style="padding:12px;">

                                <?= $user['email']
                                    ? e($user['email'])
                                    : '—'
                                ?>

                            </td>


                            <!-- Role -->

                            <td style="padding:12px;">

                                <?php

                                $roleLabels = [

                                    'admin' =>
                                        'Administrator',

                                    'co_owner' =>
                                        'Co-Owner',

                                    'employee' =>
                                        'Employee'

                                ];

                                ?>

                                <?= e(
                                    $roleLabels[
                                        $user['role']
                                    ]
                                    ??
                                    ucfirst(
                                        (string)$user['role']
                                    )
                                ) ?>

                            </td>


                            <!-- Status -->

                            <td style="padding:12px;">

                                <?php if (
                                    $user['status'] === 'active'
                                ): ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            padding:5px 10px;
                                            border-radius:20px;
                                            background:#dcfce7;
                                            color:#166534;
                                            font-size:13px;
                                            font-weight:600;
                                        "
                                    >
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            padding:5px 10px;
                                            border-radius:20px;
                                            background:#fee2e2;
                                            color:#991b1b;
                                            font-size:13px;
                                            font-weight:600;
                                        "
                                    >
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- Last Login -->

                            <td style="padding:12px;">

                                <?= $user['last_login']
                                    ? e($user['last_login'])
                                    : 'Never'
                                ?>

                            </td>


                            <!-- Actions -->

                            <td
                                style="
                                    padding:12px;
                                    text-align:center;
                                    white-space:nowrap;
                                "
                            >

                                <!-- View -->

                                <a
                                    href="view.php?id=<?= (int)$user['id'] ?>"
                                    class="btn btn-secondary"
                                >
                                    👁️ View
                                </a>


                                <!-- Edit -->

                                <a
                                    href="edit.php?id=<?= (int)$user['id'] ?>"
                                    class="btn btn-primary"
                                >
                                    ✏️ Edit
                                </a>


                                <!-- Delete -->

                                <a
                                    href="delete.php?id=<?= (int)$user['id'] ?>"
                                    class="btn btn-danger"
                                    onclick="
                                        return confirm(
                                            'Are you sure you want to delete this user?'
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


<style>

/*
|--------------------------------------------------------------------------
| Delete Button
|--------------------------------------------------------------------------
*/

.btn-danger {

    display: inline-block;

    padding: 8px 12px;

    background: #dc2626;

    color: #ffffff;

    border: none;

    border-radius: 6px;

    text-decoration: none;

    cursor: pointer;

    font-size: 14px;

    font-weight: 600;

    margin-left: 3px;
}


.btn-danger:hover {

    background: #b91c1c;

    color: #ffffff;

}


/*
|--------------------------------------------------------------------------
| Mobile
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .dashboard-card {

        padding: 15px;

    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>