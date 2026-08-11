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
| Get Existing User
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


$error = '';


/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $role     = (string)($_POST['role'] ?? '');
    $status   = (string)($_POST['status'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($fullName === '') {

        $error = 'Please enter the full name.';

    } elseif (strlen($fullName) > 150) {

        $error = 'Full name cannot exceed 150 characters.';

    } elseif ($username === '') {

        $error = 'Please enter a username.';

    } elseif (strlen($username) > 100) {

        $error = 'Username cannot exceed 100 characters.';

    } elseif (
        $email !== ''
        &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid email address.';

    } elseif (
        !in_array(
            $role,
            ['admin', 'co_owner', 'employee'],
            true
        )
    ) {

        $error = 'Invalid user role selected.';

    } elseif (
        !in_array(
            $status,
            ['active', 'inactive'],
            true
        )
    ) {

        $error = 'Invalid user status selected.';

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Username
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->execute([
            $username,
            $userId
        ]);

        if ($stmt->fetch()) {

            $error = 'That username is already in use.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Email
    |--------------------------------------------------------------------------
    */

    if (
        $error === ''
        &&
        $email !== ''
    ) {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->execute([
            $email,
            $userId
        ]);

        if ($stmt->fetch()) {

            $error = 'That email address is already in use.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                UPDATE users
                SET
                    full_name = :full_name,
                    username = :username,
                    email = :email,
                    role = :role,
                    status = :status
                WHERE id = :id
            ");

            $stmt->execute([

                ':full_name' =>
                    $fullName,

                ':username' =>
                    $username,

                ':email' =>
                    $email !== ''
                        ? $email
                        : null,

                ':role' =>
                    $role,

                ':status' =>
                    $status,

                ':id' =>
                    $userId

            ]);


            header(
                'Location: view.php?id='
                . $userId
                . '&updated=1'
            );

            exit;

        } catch (PDOException $e) {

            if (
                isset($e->errorInfo[1])
                &&
                (int)$e->errorInfo[1] === 1062
            ) {

                $error =
                    'The username or email address is already in use.';

            } else {

                $error =
                    'Unable to update the user. Please try again.';

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values
    |--------------------------------------------------------------------------
    */

    $user['full_name'] = $fullName;
    $user['username']  = $username;
    $user['email']     = $email;
    $user['role']      = $role;
    $user['status']    = $status;

}


$pageTitle = 'Edit User';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>✏️ Edit User</h2>

        <p>
            Update this user's account information.
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


<div class="dashboard-card">

    <form
        method="POST"
        action=""
    >


        <!-- Full Name -->

        <div class="form-group">

            <label for="full_name">
                👤 Full Name
            </label>

            <input
                type="text"
                id="full_name"
                name="full_name"
                maxlength="150"
                value="<?= e(
                    $user['full_name']
                ) ?>"
                required
            >

        </div>


        <!-- Username -->

        <div class="form-group">

            <label for="username">
                🔑 Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                maxlength="100"
                value="<?= e(
                    $user['username']
                ) ?>"
                required
            >

        </div>


        <!-- Email -->

        <div class="form-group">

            <label for="email">
                📧 Email Address
            </label>

            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                value="<?= e(
                    $user['email'] ?? ''
                ) ?>"
            >

            <small>
                Optional.
            </small>

        </div>


        <!-- Role -->

        <div class="form-group">

            <label for="role">
                🛡️ Role
            </label>

            <select
                id="role"
                name="role"
                required
            >

                <option
                    value="employee"
                    <?= $user['role'] === 'employee'
                        ? 'selected'
                        : '' ?>
                >
                    Employee
                </option>

                <option
                    value="co_owner"
                    <?= $user['role'] === 'co_owner'
                        ? 'selected'
                        : '' ?>
                >
                    Co-Owner
                </option>

                <option
                    value="admin"
                    <?= $user['role'] === 'admin'
                        ? 'selected'
                        : '' ?>
                >
                    Administrator
                </option>

            </select>

        </div>


        <!-- Status -->

        <div class="form-group">

            <label for="status">
                ⚡ Account Status
            </label>

            <select
                id="status"
                name="status"
                required
            >

                <option
                    value="active"
                    <?= $user['status'] === 'active'
                        ? 'selected'
                        : '' ?>
                >
                    Active
                </option>

                <option
                    value="inactive"
                    <?= $user['status'] === 'inactive'
                        ? 'selected'
                        : '' ?>
                >
                    Inactive
                </option>

            </select>

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
                💾 Save Changes
            </button>


            <a
                href="view.php?id=<?= (int)$user['id'] ?>"
                class="btn btn-secondary"
            >
                Cancel
            </a>

        </div>

    </form>


    <!-- PASSWORD -->

    <div
        style="
            margin-top:30px;
            padding-top:20px;
            border-top:1px solid #e5e7eb;
        "
    >

        <h3>
            🔐 Password
        </h3>

        <p>
            To change this user's password, use the dedicated
            password management page.
        </p>

        <a
            href="password.php?id=<?= (int)$user['id'] ?>"
            class="btn btn-secondary"
        >
            🔐 Change Password
        </a>

    </div>

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

.form-group input,
.form-group select {
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

    .form-group input,
    .form-group select {
        font-size: 16px;
    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
