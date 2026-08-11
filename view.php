<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();


/*
|--------------------------------------------------------------------------
| Get User ID
|--------------------------------------------------------------------------
*/

$userId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$userId || $userId < 1) {

    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        username,
        email,
        role,
        status,
        last_login,
        created_at,
        updated_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Role Labels
|--------------------------------------------------------------------------
*/

$roleLabels = [

    'admin' =>
        'Administrator',

    'co_owner' =>
        'Co-Owner',

    'employee' =>
        'Employee'

];

$roleLabel =
    $roleLabels[$user['role']]
    ??
    ucfirst((string)$user['role']);


/*
|--------------------------------------------------------------------------
| Page Title
|--------------------------------------------------------------------------
*/

$pageTitle = 'View User';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>👤 User Details</h2>

        <p>
            View information about this user account.
        </p>

    </div>


    <div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Users
        </a>

    </div>

</div>


<!-- USER PROFILE -->

<div class="dashboard-card">

    <div
        style="
            display:flex;
            align-items:center;
            gap:20px;
            margin-bottom:30px;
            flex-wrap:wrap;
        "
    >

        <div
            style="
                width:80px;
                height:80px;
                border-radius:50%;
                display:flex;
                align-items:center;
                justify-content:center;
                font-size:38px;
                background:#eef2ff;
            "
        >
            👤
        </div>


        <div>

            <h2 style="margin:0 0 5px 0;">
                <?= e($user['full_name']) ?>
            </h2>

            <p style="margin:0;">
                @<?= e($user['username']) ?>
            </p>

        </div>

    </div>


    <!-- INFORMATION -->

    <div class="user-details-grid">


        <!-- Full Name -->

        <div class="detail-box">

            <span class="detail-label">
                👤 Full Name
            </span>

            <strong>
                <?= e($user['full_name']) ?>
            </strong>

        </div>


        <!-- Username -->

        <div class="detail-box">

            <span class="detail-label">
                🔑 Username
            </span>

            <strong>
                <?= e($user['username']) ?>
            </strong>

        </div>


        <!-- Email -->

        <div class="detail-box">

            <span class="detail-label">
                📧 Email
            </span>

            <strong>

                <?= $user['email']
                    ? e($user['email'])
                    : 'Not provided'
                ?>

            </strong>

        </div>


        <!-- Role -->

        <div class="detail-box">

            <span class="detail-label">
                🛡️ Role
            </span>

            <strong>
                <?= e($roleLabel) ?>
            </strong>

        </div>


        <!-- Status -->

        <div class="detail-box">

            <span class="detail-label">
                ⚡ Account Status
            </span>


            <?php if ($user['status'] === 'active'): ?>

                <span
                    class="status-active"
                >
                    Active
                </span>

            <?php else: ?>

                <span
                    class="status-inactive"
                >
                    Inactive
                </span>

            <?php endif; ?>

        </div>


        <!-- Last Login -->

        <div class="detail-box">

            <span class="detail-label">
                🕐 Last Login
            </span>

            <strong>

                <?= $user['last_login']
                    ? e($user['last_login'])
                    : 'Never'
                ?>

            </strong>

        </div>


        <!-- Created -->

        <div class="detail-box">

            <span class="detail-label">
                📅 Account Created
            </span>

            <strong>
                <?= e($user['created_at']) ?>
            </strong>

        </div>


        <!-- Updated -->

        <div class="detail-box">

            <span class="detail-label">
                🔄 Last Updated
            </span>

            <strong>
                <?= e($user['updated_at']) ?>
            </strong>

        </div>

    </div>


    <!-- ACTIONS -->

    <div
        style="
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:30px;
            padding-top:20px;
            border-top:1px solid #e5e7eb;
        "
    >

        <a
            href="edit.php?id=<?= (int)$user['id'] ?>"
            class="btn btn-primary"
        >
            ✏️ Edit User
        </a>


        <a
            href="password.php?id=<?= (int)$user['id'] ?>"
            class="btn btn-secondary"
        >
            🔐 Change Password
        </a>


        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Users
        </a>

    </div>

</div>


<style>

.user-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.detail-box {
    padding: 18px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #fafafa;
}

.detail-label {
    display: block;
    margin-bottom: 8px;
    color: #6b7280;
    font-size: 14px;
}

.detail-box strong {
    display: block;
    font-size: 16px;
    word-break: break-word;
}

.status-active,
.status-inactive {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

@media (max-width: 768px) {

    .user-details-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-card {
        padding: 15px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>