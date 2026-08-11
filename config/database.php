<?php

declare(strict_types=1);

/**
 * Poultry Farm Management System
 * Database Configuration
 *
 * Local:
 *   XAMPP / MariaDB
 *
 * Production:
 *   Render + Aiven MySQL
 */

// ------------------------------------------------------------
// Database settings
// ------------------------------------------------------------

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'poultry_farm';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

$charset = 'utf8mb4';


// ------------------------------------------------------------
// Build DSN
// ------------------------------------------------------------

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";


// ------------------------------------------------------------
// PDO options
// ------------------------------------------------------------

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


// ------------------------------------------------------------
// Aiven SSL configuration
// ------------------------------------------------------------

$sslCa = getenv('DB_SSL_CA');

if ($sslCa) {

    // Render Secret Files are mounted under /etc/secrets/
    if (is_file($sslCa)) {

        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;

    }
}


// ------------------------------------------------------------
// Connect to database
// ------------------------------------------------------------

try {

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

} catch (PDOException $e) {

    error_log(
        'Database connection failed: ' .
        $e->getMessage()
    );

    die(
        'Database connection failed. Please check the database configuration.'
    );
}