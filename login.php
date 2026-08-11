<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('../dashboard/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            AND status = 'active'
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            $update = $pdo->prepare("
                UPDATE users
                SET last_login = NOW()
                WHERE id = ?
            ");

            $update->execute([
                $user['id']
            ]);

            redirect('../dashboard/index.php');

        } else {

            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Poultry Farm Management System</title>

</head>

<body>

    <h1>Poultry Farm Management System</h1>

    <h2>Login</h2>

    <?php if ($error): ?>

        <p>
            <?= e($error) ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <div>
            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                autocomplete="username"
            >
        </div>

        <br>

        <div>
            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </div>

        <br>

        <button type="submit">
            Login
        </button>

    </form>

</body>

</html>