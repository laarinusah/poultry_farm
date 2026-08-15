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

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef3f8;
            font-family: Arial, Helvetica, sans-serif;
            color: #17324d;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 40px 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
            border: 1px solid #e1e8ef;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .farm-icon {
            font-size: 55px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            margin: 0;
            font-size: 25px;
            line-height: 1.3;
            color: #17324d;
        }

        .login-header p {
            margin: 8px 0 0;
            color: #718096;
            font-size: 14px;
        }

        .login-title {
            text-align: center;
            font-size: 22px;
            margin-bottom: 25px;
            color: #17324d;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334e68;
        }

        .form-group input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 9px;
            font-size: 16px;
            outline: none;
            transition: 0.2s;
            background: #fff;
        }

        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .login-button {
            width: 100%;
            border: none;
            border-radius: 9px;
            padding: 14px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .login-button:hover {
            background: #1d4ed8;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #718096;
            font-size: 13px;
        }

        @media (max-width: 480px) {

            body {
                padding: 15px;
            }

            .login-card {
                padding: 30px 22px;
            }

            .login-header h1 {
                font-size: 21px;
            }

        }

    </style>

</head>

<body>

    <div class="login-wrapper">

        <div class="login-card">

            <div class="login-header">

                <div class="farm-icon">
                    🐔
                </div>

                <h1>
                    Poultry Farm Management System
                </h1>

                <p>
                    Farm Management Portal
                </p>

            </div>

            <h2 class="login-title">
                🔐 Login
            </h2>

            <?php if ($error): ?>

                <div class="error-message">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        value="<?= e($_POST['username'] ?? '') ?>"
                        placeholder="Enter your username"
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >

                </div>

                <button
                    type="submit"
                    class="login-button"
                >
                    🔐 Login
                </button>

            </form>

            <div class="login-footer">
                Poultry Farm Management System
            </div>

        </div>

    </div>

</body>

</html>