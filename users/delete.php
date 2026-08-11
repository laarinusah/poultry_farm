<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$error = '';

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
        status
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
| Prevent Deleting Current Logged-In User
|--------------------------------------------------------------------------
*/

$currentUserId = currentUserId();

if (
    $currentUserId !== null
    &&
    $currentUserId === (int)$user['id']
) {
    $error =
        'You cannot delete the account you are currently logged into.';
}


/*
|--------------------------------------------------------------------------
| Delete User
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    $error === ''
) {

    $confirm = trim(
        (string)($_POST['confirm'] ?? '')
    );

    if ($confirm !== 'DELETE') {

        $error =
            'Please type DELETE to confirm the deletion.';

    } else {

        try {

            $stmt = $pdo->prepare("
                DELETE FROM users
                WHERE id = ?
            ");

            $stmt->execute([
                $userId
            ]);


            if ($stmt->rowCount() > 0) {

                header(
                    'Location: index.php?deleted=1'
                );

                exit;

            }

            $error =
                'The user could not be deleted.';

        } catch (PDOException $e) {

            /*
            |--------------------------------------------------------------------------
            | Foreign Key Protection
            |--------------------------------------------------------------------------
            */

            if (
                isset($e->errorInfo[1])
                &&
                (int)$e->errorInfo[1] === 1451
            ) {

                $error =
                    'This user has records connected to the farm system and cannot be deleted. '
                    . 'Please deactivate the account instead.';

            } else {

                $error =
                    'Unable to delete this user. Please try again.';
            }
        }
    }
}


$pageTitle = 'Delete User';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🗑️ Delete User</h2>

        <p>
            Permanently remove this user account.
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


<?php if ($error !== ''): ?>

    <div
        class="alert alert-danger"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ⚠️ <?= e($error) ?>

    </div>

<?php endif; ?>


<div class="dashboard-card">


    <!-- Warning -->

    <div
        style="
            padding:20px;
            margin-bottom:25px;
            border-radius:10px;
            background:#fef2f2;
            border:1px solid #fecaca;
        "
    >

        <h3
            style="
                margin-top:0;
                color:#991b1b;
            "
        >
            ⚠️ Warning
        </h3>

        <p
            style="
                margin-bottom:0;
                color:#7f1d1d;
            "
        >
            You are about to permanently delete this user account.
            This action cannot be undone.
        </p>

    </div>


    <!-- User Information -->

    <div
        style="
            padding:20px;
            margin-bottom:25px;
            border:1px solid #e5e7eb;
            border-radius:10px;
        "
    >

        <h3>
            👤 User Information
        </h3>


        <p>

            <strong>
                Full Name:
            </strong>

            <?= e($user['full_name']) ?>

        </p>


        <p>

            <strong>
                Username:
            </strong>

            <?= e($user['username']) ?>

        </p>


        <p>

            <strong>
                Email:
            </strong>

            <?= $user['email']
                ? e($user['email'])
                : 'Not provided'
            ?>

        </p>


        <p>

            <strong>
                Role:
            </strong>

            <?= e(
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        (string)$user['role']
                    )
                )
            ) ?>

        </p>


        <p style="margin-bottom:0;">

            <strong>
                Status:
            </strong>

            <?= e(
                ucfirst(
                    (string)$user['status']
                )
            ) ?>

        </p>

    </div>


    <?php if ($error === ''): ?>

        <form
            method="POST"
            action="delete.php?id=<?= (int)$user['id'] ?>"
        >

            <div class="form-group">

                <label for="confirm">

                    Type
                    <strong>DELETE</strong>
                    to confirm:

                </label>

                <input
                    type="text"
                    id="confirm"
                    name="confirm"
                    placeholder="Type DELETE"
                    autocomplete="off"
                    required
                >

            </div>


            <div
                style="
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                    margin-top:25px;
                "
            >

                <button
                    type="submit"
                    class="btn btn-danger"
                    onclick="
                        return confirm(
                            'Are you sure you want to permanently delete this user?'
                        );
                    "
                >
                    🗑️ Permanently Delete
                </button>


                <a
                    href="view.php?id=<?= (int)$user['id'] ?>"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    <?php else: ?>

        <div style="margin-top:20px;">

            <a
                href="index.php"
                class="btn btn-secondary"
            >
                ← Back to Users
            </a>

        </div>

    <?php endif; ?>


</div>


<style>

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    box-sizing: border-box;
}

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
}

.btn-danger:hover {
    background: #b91c1c;
    color: #ffffff;
}

@media (max-width: 768px) {

    .dashboard-card {
        padding: 15px;
    }

    .form-group input {
        font-size: 16px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>