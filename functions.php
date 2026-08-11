<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Escape HTML Output Safely
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Farm Settings
|--------------------------------------------------------------------------
|
| Reads the settings saved in the farm_settings table.
| The result is cached during the current request so we
| don't query the database repeatedly.
|
*/

function farmSettings(): array
{
    global $pdo;

    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings = [
        'farm_name' =>
            defined('DEFAULT_FARM_NAME')
                ? DEFAULT_FARM_NAME
                : 'Poultry Farm',

        'farm_location' =>
            'Ghana',

        'phone' =>
            null,

        'email' =>
            null,

        'currency' =>
            defined('DEFAULT_CURRENCY')
                ? DEFAULT_CURRENCY
                : 'GHS',

        'timezone' =>
            defined('DEFAULT_TIMEZONE')
                ? DEFAULT_TIMEZONE
                : 'Africa/Accra'
    ];


    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */

    if (!isset($pdo)) {
        return $settings;
    }


    try {

        $stmt = $pdo->query("
            SELECT
                farm_name,
                farm_location,
                phone,
                email,
                currency,
                timezone
            FROM farm_settings
            ORDER BY id ASC
            LIMIT 1
        ");

        $databaseSettings =
            $stmt->fetch(PDO::FETCH_ASSOC);


        if ($databaseSettings) {

            $settings = array_merge(
                $settings,
                $databaseSettings
            );
        }

    } catch (Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Keep Default Settings
        |--------------------------------------------------------------------------
        |
        | If the database is temporarily unavailable,
        | the application can still use the defaults.
        |
        */

    }


    return $settings;
}


/*
|--------------------------------------------------------------------------
| Farm Name
|--------------------------------------------------------------------------
*/

function farmName(): string
{
    return (string) (
        farmSettings()['farm_name']
        ?? 'Poultry Farm'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Location
|--------------------------------------------------------------------------
*/

function farmLocation(): string
{
    return (string) (
        farmSettings()['farm_location']
        ?? 'Ghana'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Phone
|--------------------------------------------------------------------------
*/

function farmPhone(): ?string
{
    $phone = farmSettings()['phone'] ?? null;

    return $phone !== null && $phone !== ''
        ? (string)$phone
        : null;
}


/*
|--------------------------------------------------------------------------
| Farm Email
|--------------------------------------------------------------------------
*/

function farmEmail(): ?string
{
    $email = farmSettings()['email'] ?? null;

    return $email !== null && $email !== ''
        ? (string)$email
        : null;
}


/*
|--------------------------------------------------------------------------
| Farm Currency
|--------------------------------------------------------------------------
*/

function farmCurrency(): string
{
    return (string) (
        farmSettings()['currency']
        ?? 'GHS'
    );
}


/*
|--------------------------------------------------------------------------
| Farm Timezone
|--------------------------------------------------------------------------
*/

function farmTimezone(): string
{
    return (string) (
        farmSettings()['timezone']
        ?? 'Africa/Accra'
    );
}


/*
|--------------------------------------------------------------------------
| Format Money
|--------------------------------------------------------------------------
|
| Example:
|
| GHS 1,500.00
| USD 1,500.00
| EUR 1,500.00
|
*/

function money(float|int $amount): string
{
    return farmCurrency()
        . ' '
        . number_format(
            (float)$amount,
            2
        );
}


/*
|--------------------------------------------------------------------------
| Format Numbers
|--------------------------------------------------------------------------
*/

function number(int|float $value): string
{
    return number_format(
        (float)$value
    );
}