<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Session Security
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';


/*
|--------------------------------------------------------------------------
| Check Whether User Is Logged In
|--------------------------------------------------------------------------
*/

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'])
        && is_numeric($_SESSION['user_id']);
}


/*
|--------------------------------------------------------------------------
| Current User ID
|--------------------------------------------------------------------------
*/

function currentUserId(): ?int
{
    if (!isLoggedIn()) {
        return null;
    }

    return (int) $_SESSION['user_id'];
}


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

function currentUser(): ?array
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    static $user = false;

    if ($user !== false) {
        return $user;
    }

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

    $stmt->execute([
        currentUserId()
    ]);

    $result = $stmt->fetch();

    if (!$result) {

        $user = null;

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Account Status
    |--------------------------------------------------------------------------
    */

    if ($result['status'] !== 'active') {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'] ?? '',
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        redirect('/auth/login.php?error=inactive');

        exit;
    }

    $user = $result;

    return $user;
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function requireLogin(): void
{
    if (!isLoggedIn()) {

        redirect('/auth/login.php');

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Verify User Still Exists and Is Active
    |--------------------------------------------------------------------------
    */

    $user = currentUser();

    if ($user === null) {

        redirect('/auth/login.php');

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Get Current User Role
|--------------------------------------------------------------------------
*/

function currentUserRole(): ?string
{
    $user = currentUser();

    return $user['role'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Check User Role
|--------------------------------------------------------------------------
*/

function hasRole(string ...$roles): bool
{
    $role = currentUserRole();

    if ($role === null) {
        return false;
    }

    return in_array(
        $role,
        $roles,
        true
    );
}


/*
|--------------------------------------------------------------------------
| Require Specific Role
|--------------------------------------------------------------------------
*/

function requireRole(string ...$roles): void
{
    requireLogin();

    if (!hasRole(...$roles)) {

        http_response_code(403);

        exit(
            '403 Forbidden - You do not have permission to access this page.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Admin Check
|--------------------------------------------------------------------------
*/

function isAdmin(): bool
{
    return hasRole('admin');
}


/*
|--------------------------------------------------------------------------
| Co-Owner Check
|--------------------------------------------------------------------------
*/

function isCoOwner(): bool
{
    return hasRole('co_owner');
}


/*
|--------------------------------------------------------------------------
| Employee Check
|--------------------------------------------------------------------------
*/

function isEmployee(): bool
{
    return hasRole('employee');
}