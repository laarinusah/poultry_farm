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
        username
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


$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = (string)(
        $_POST['password'] ?? ''
    );

    $confirmPassword = (string)(
        $_POST['confirm_password'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($password === '') {

        $error = 'Please enter a new password.';

    } elseif (strlen($password) < 8) {

        $error =
            'Password must contain at least 8 characters.';

    } elseif ($password !== $confirmPassword) {

        $error =
            'The passwords do not match.';

    }


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        if ($passwordHash === false) {

            $error =
                'Unable to securely create the password.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET
                        password_hash = :password_hash
                    WHERE id = :id
                ");

                $stmt->execute([

                    ':password_hash' =>
                        $passwordHash,

                    ':id' =>
                        $userId

                ]);


                $success =
                    'Password changed successfully.';


            } catch (PDOException $e) {

                $error =
                    'Unable to change the password. Please try again.';

            }

        }

    }

}


$pageTitle = 'Change Password';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>🔐 Change Password</h2>

        <p>
            Update the login password for this user.
        </p>

    </div>


    <div>

        <a
            href="view.php?id=<?= (int)$user['id'] ?>"
            class="btn btn-secondary"
        >
            ← Back to User
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


<?php if ($success !== ''): ?>

    <div
        class="alert alert-success"
        style="
            padding:15px;
            margin-bottom:20px;
            border-radius:8px;
        "
    >

        ✅ <?= e($success) ?>

    </div>

<?php endif; ?>


<div class="dashboard-card">

    <!-- User Information -->

    <div
        style="
            padding:15px;
            margin-bottom:25px;
            border-radius:10px;
            background:#f8fafc;
        "
    >

        <strong>
            👤 <?= e($user['full_name']) ?>
        </strong>

        <br>

        <small>
            🔑 @<?= e($user['username']) ?>
        </small>

    </div>


    <form
        method="POST"
        action=""
    >


        <!-- New Password -->

        <div class="form-group">

            <label for="password">
                🔐 New Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                placeholder="Enter new password"
                required
            >

            <small>
                Password must contain at least 8 characters.
            </small>

        </div>


        <!-- Confirm Password -->

        <div class="form-group">

            <label for="confirm_password">
                🔐 Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                placeholder="Enter the password again"
                required
            >

        </div>


        <!-- Buttons -->

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
                class="btn btn-primary"
            >
                🔐 Change Password
            </button>


            <a
                href="view.php?id=<?= (int)$user['id'] ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>

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

.form-group small {
    display: block;
    margin-top: 6px;
    color: #6b7280;
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