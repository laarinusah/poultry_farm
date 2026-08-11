<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$error = '';

/*
|--------------------------------------------------------------------------
| Add User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role     = (string)($_POST['role'] ?? 'employee');
    $status   = (string)($_POST['status'] ?? 'active');


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

    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 8) {

        $error = 'Password must contain at least 8 characters.';

    } elseif (!in_array($role, ['admin', 'co_owner', 'employee'], true)) {

        $error = 'Invalid user role selected.';

    } elseif (!in_array($status, ['active', 'inactive'], true)) {

        $error = 'Invalid user status selected.';

    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Username
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        if ($stmt->fetch()) {

            $error = 'That username is already in use.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    if ($error === '' && $email !== '') {

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = 'That email address is already in use.';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {

            $error = 'Unable to securely create the password.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO users
                    (
                        full_name,
                        username,
                        email,
                        password_hash,
                        role,
                        status
                    )
                    VALUES
                    (
                        :full_name,
                        :username,
                        :email,
                        :password_hash,
                        :role,
                        :status
                    )
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

                    ':password_hash' =>
                        $passwordHash,

                    ':role' =>
                        $role,

                    ':status' =>
                        $status

                ]);

                header(
                    'Location: index.php?added=1'
                );

                exit;

            } catch (PDOException $e) {

                /*
                |--------------------------------------------------------------------------
                | Handle Unique Username / Email Errors
                |--------------------------------------------------------------------------
                */

                if ((int)$e->errorInfo[1] === 1062) {

                    $error =
                        'The username or email address is already in use.';

                } else {

                    $error =
                        'Unable to create the user. Please try again.';
                }

            }

        }

    }

}


$pageTitle = 'Add User';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>👥 Add User</h2>

        <p>
            Create a new user account for the farm management system.
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

    <form
        method="POST"
        action=""
        autocomplete="off"
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
                    $_POST['full_name'] ?? ''
                ) ?>"
                placeholder="e.g. John Doe"
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
                    $_POST['username'] ?? ''
                ) ?>"
                placeholder="e.g. john123"
                required
            >

            <small>
                Enter a unique username for login.
            </small>

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
                    $_POST['email'] ?? ''
                ) ?>"
                placeholder="e.g. john@example.com"
            >

            <small>
                Optional.
            </small>

        </div>


        <!-- Password -->

        <div class="form-group">

            <label for="password">
                🔐 Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                placeholder="Minimum 8 characters"
                required
            >

            <small>
                Password must contain at least 8 characters.
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
                    <?= ($_POST['role'] ?? 'employee') === 'employee'
                        ? 'selected'
                        : '' ?>
                >
                    Employee
                </option>

                <option
                    value="co_owner"
                    <?= ($_POST['role'] ?? '') === 'co_owner'
                        ? 'selected'
                        : '' ?>
                >
                    Co-Owner
                </option>

                <option
                    value="admin"
                    <?= ($_POST['role'] ?? '') === 'admin'
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
                    <?= ($_POST['status'] ?? 'active') === 'active'
                        ? 'selected'
                        : '' ?>
                >
                    Active
                </option>

                <option
                    value="inactive"
                    <?= ($_POST['status'] ?? '') === 'inactive'
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
                💾 Create User
            </button>


            <a
                href="index.php"
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

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius
}