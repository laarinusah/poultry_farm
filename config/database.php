<?php

declare(strict_types=1);

/**
 * Poultry Farm Management System
 * Database Configuration
 */

$host = '127.0.0.1';
$db   = 'poultry_farm';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


try {

    /*
    |--------------------------------------------------------------------------
    | Create Database Connection
    |--------------------------------------------------------------------------
    */

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );


    /*
    |--------------------------------------------------------------------------
    | Load Farm Timezone
    |--------------------------------------------------------------------------
    |
    | The timezone is stored in farm_settings.
    | We read it once when the database connection is created.
    |
    */

    $timezone = 'Africa/Accra';


    try {

        $stmt = $pdo->query("
            SELECT timezone
            FROM farm_settings
            ORDER BY id ASC
            LIMIT 1
        ");

        $farmTimezone = $stmt->fetchColumn();


        /*
        |--------------------------------------------------------------------------
        | Validate Timezone
        |--------------------------------------------------------------------------
        */

        if (
            is_string($farmTimezone)
            &&
            $farmTimezone !== ''
            &&
            in_array(
                $farmTimezone,
                timezone_identifiers_list(),
                true
            )
        ) {

            $timezone = $farmTimezone;

        }

    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        |
        | If farm_settings does not exist or cannot be read,
        | continue using Africa/Accra.
        |
        */

        $timezone = 'Africa/Accra';
    }


    /*
    |--------------------------------------------------------------------------
    | Apply Farm Timezone
    |--------------------------------------------------------------------------
    */

    date_default_timezone_set(
        $timezone
    );


} catch (PDOException $e) {

    error_log(
        $e->getMessage()
    );

    die(
        'Database connection failed. Please check your MySQL server and configuration.'
    );
}