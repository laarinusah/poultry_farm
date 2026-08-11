<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$error = '';
$success = '';


/*
|--------------------------------------------------------------------------
| Get Current Settings
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
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

$settings = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Create Default Settings If None Exist
|--------------------------------------------------------------------------
*/

if (!$settings) {

    $stmt = $pdo->prepare("
        INSERT INTO farm_settings
        (
            farm_name,
            farm_location,
            phone,
            email,
            currency,
            timezone
        )
        VALUES
        (
            :farm_name,
            :farm_location,
            NULL,
            NULL,
            :currency,
            :timezone
        )
    ");

    $stmt->execute([

        ':farm_name' =>
            'Poultry Farm',

        ':farm_location' =>
            'Ghana',

        ':currency' =>
            'GHS',

        ':timezone' =>
            'Africa/Accra'

    ]);


    $settings = [

        'id' =>
            (int)$pdo->lastInsertId(),

        'farm_name' =>
            'Poultry Farm',

        'farm_location' =>
            'Ghana',

        'phone' =>
            null,

        'email' =>
            null,

        'currency' =>
            'GHS',

        'timezone' =>
            'Africa/Accra'

    ];
}


/*
|--------------------------------------------------------------------------
| Update Settings
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $farmName = trim(
        (string)($_POST['farm_name'] ?? '')
    );

    $farmLocation = trim(
        (string)($_POST['farm_location'] ?? '')
    );

    $phone = trim(
        (string)($_POST['phone'] ?? '')
    );

    $email = trim(
        (string)($_POST['email'] ?? '')
    );

    $currency = trim(
        (string)($_POST['currency'] ?? '')
    );

    $timezone = trim(
        (string)($_POST['timezone'] ?? '')
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($farmName === '') {

        $error =
            'Please enter the farm name.';

    } elseif (strlen($farmName) > 150) {

        $error =
            'Farm name cannot exceed 150 characters.';

    } elseif ($farmLocation === '') {

        $error =
            'Please enter the farm location.';

    } elseif (strlen($farmLocation) > 255) {

        $error =
            'Farm location cannot exceed 255 characters.';

    } elseif (
        $email !== ''
        &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    } elseif ($currency === '') {

        $error =
            'Please select a currency.';

    } elseif (
        !in_array(
            $currency,
            [
                'GHS',
                'USD',
                'EUR',
                'GBP',
                'NGN',
                'XOF'
            ],
            true
        )
    ) {

        $error =
            'Invalid currency selected.';

    } elseif ($timezone === '') {

        $error =
            'Please select a timezone.';

    } elseif (
        !in_array(
            $timezone,
            timezone_identifiers_list(),
            true
        )
    ) {

        $error =
            'Invalid timezone selected.';

    }


    /*
    |--------------------------------------------------------------------------
    | Save Settings
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        try {

            $stmt = $pdo->prepare("
                UPDATE farm_settings
                SET
                    farm_name = :farm_name,
                    farm_location = :farm_location,
                    phone = :phone,
                    email = :email,
                    currency = :currency,
                    timezone = :timezone
                WHERE id = :id
            ");

            $stmt->execute([

                ':farm_name' =>
                    $farmName,

                ':farm_location' =>
                    $farmLocation,

                ':phone' =>
                    $phone !== ''
                        ? $phone
                        : null,

                ':email' =>
                    $email !== ''
                        ? $email
                        : null,

                ':currency' =>
                    $currency,

                ':timezone' =>
                    $timezone,

                ':id' =>
                    (int)$settings['id']

            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Local Values
            |--------------------------------------------------------------------------
            */

            $settings['farm_name'] =
                $farmName;

            $settings['farm_location'] =
                $farmLocation;

            $settings['phone'] =
                $phone !== ''
                    ? $phone
                    : null;

            $settings['email'] =
                $email !== ''
                    ? $email
                    : null;

            $settings['currency'] =
                $currency;

            $settings['timezone'] =
                $timezone;


            $success =
                'Farm settings updated successfully.';


        } catch (PDOException $e) {

            $error =
                'Unable to save the farm settings. Please try again.';
        }
    }
}


$pageTitle = 'Farm Settings';

require_once __DIR__ . '/../includes/header.php';

?>


<div class="page-header">

    <div>

        <h2>⚙️ Farm Settings</h2>

        <p>
            Manage the basic information and preferences
            for your poultry farm.
        </p>

    </div>

</div>


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


<div class="settings-layout">


    <!-- Farm Information -->

    <div class="dashboard-card">

        <div class="section-heading">

            <div class="section-icon">
                🏠
            </div>

            <div>

                <h3>
                    Farm Information
                </h3>

                <p>
                    Basic information about your poultry farm.
                </p>

            </div>

        </div>


        <form method="POST">


            <!-- Farm Name -->

            <div class="form-group">

                <label for="farm_name">
                    🏠 Farm Name
                </label>

                <input
                    type="text"
                    id="farm_name"
                    name="farm_name"
                    maxlength="150"
                    value="<?= e(
                        (string)$settings['farm_name']
                    ) ?>"
                    placeholder="e.g. Laar Poultry Farm"
                    required
                >

            </div>


            <!-- Location -->

            <div class="form-group">

                <label for="farm_location">
                    📍 Farm Location
                </label>

                <input
                    type="text"
                    id="farm_location"
                    name="farm_location"
                    maxlength="255"
                    value="<?= e(
                        (string)$settings['farm_location']
                    ) ?>"
                    placeholder="e.g. Sunyani, Ghana"
                    required
                >

            </div>


            <!-- Phone -->

            <div class="form-group">

                <label for="phone">
                    📞 Phone Number
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    maxlength="50"
                    value="<?= e(
                        (string)(
                            $settings['phone'] ?? ''
                        )
                    ) ?>"
                    placeholder="e.g. 0240000000"
                >

                <small>
                    Optional
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
                        (string)(
                            $settings['email'] ?? ''
                        )
                    ) ?>"
                    placeholder="e.g. info@poultryfarm.com"
                >

                <small>
                    Optional
                </small>

            </div>


            <!-- Currency -->

            <div class="form-group">

                <label for="currency">
                    💰 Currency
                </label>

                <select
                    id="currency"
                    name="currency"
                    required
                >

                    <option
                        value="GHS"
                        <?= $settings['currency'] === 'GHS'
                            ? 'selected'
                            : '' ?>
                    >
                        GHS — Ghana Cedi
                    </option>

                    <option
                        value="USD"
                        <?= $settings['currency'] === 'USD'
                            ? 'selected'
                            : '' ?>
                    >
                        USD — US Dollar
                    </option>

                    <option
                        value="EUR"
                        <?= $settings['currency'] === 'EUR'
                            ? 'selected'
                            : '' ?>
                    >
                        EUR — Euro
                    </option>

                    <option
                        value="GBP"
                        <?= $settings['currency'] === 'GBP'
                            ? 'selected'
                            : '' ?>
                    >
                        GBP — British Pound
                    </option>

                    <option
                        value="NGN"
                        <?= $settings['currency'] === 'NGN'
                            ? 'selected'
                            : '' ?>
                    >
                        NGN — Nigerian Naira
                    </option>

                    <option
                        value="XOF"
                        <?= $settings['currency'] === 'XOF'
                            ? 'selected'
                            : '' ?>
                    >
                        XOF — West African CFA Franc
                    </option>

                </select>

            </div>


            <!-- Timezone -->

            <div class="form-group">

                <label for="timezone">
                    🌍 Timezone
                </label>

                <select
                    id="timezone"
                    name="timezone"
                    required
                >

                    <option
                        value="Africa/Accra"
                        <?= $settings['timezone'] === 'Africa/Accra'
                            ? 'selected'
                            : '' ?>
                    >
                        Africa/Accra — Ghana
                    </option>

                    <option
                        value="Africa/Lagos"
                        <?= $settings['timezone'] === 'Africa/Lagos'
                            ? 'selected'
                            : '' ?>
                    >
                        Africa/Lagos — Nigeria
                    </option>

                    <option
                        value="Africa/Abidjan"
                        <?= $settings['timezone'] === 'Africa/Abidjan'
                            ? 'selected'
                            : '' ?>
                    >
                        Africa/Abidjan — Côte d'Ivoire
                    </option>

                    <option
                        value="Africa/Nairobi"
                        <?= $settings['timezone'] === 'Africa/Nairobi'
                            ? 'selected'
                            : '' ?>
                    >
                        Africa/Nairobi — Kenya
                    </option>

                    <option
                        value="Europe/London"
                        <?= $settings['timezone'] === 'Europe/London'
                            ? 'selected'
                            : '' ?>
                    >
                        Europe/London
                    </option>

                    <option
                        value="America/New_York"
                        <?= $settings['timezone'] === 'America/New_York'
                            ? 'selected'
                            : '' ?>
                    >
                        America/New_York
                    </option>

                </select>

            </div>


            <!-- Save -->

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
                    💾 Save Settings
                </button>

            </div>

        </form>

    </div>


    <!-- Current Settings -->

    <div class="dashboard-card">

        <div class="section-heading">

            <div class="section-icon">
                📋
            </div>

            <div>

                <h3>
                    Current Settings
                </h3>

                <p>
                    Current farm configuration.
                </p>

            </div>

        </div>


        <div class="setting-item">

            <span>
                🏠 Farm Name
            </span>

            <strong>
                <?= e(
                    (string)$settings['farm_name']
                ) ?>
            </strong>

        </div>


        <div class="setting-item">

            <span>
                📍 Location
            </span>

            <strong>
                <?= e(
                    (string)$settings['farm_location']
                ) ?>
            </strong>

        </div>


        <div class="setting-item">

            <span>
                📞 Phone
            </span>

            <strong>
                <?= $settings['phone']
                    ? e((string)$settings['phone'])
                    : 'Not provided'
                ?>
            </strong>

        </div>


        <div class="setting-item">

            <span>
                📧 Email
            </span>

            <strong>
                <?= $settings['email']
                    ? e((string)$settings['email'])
                    : 'Not provided'
                ?>
            </strong>

        </div>


        <div class="setting-item">

            <span>
                💰 Currency
            </span>

            <strong>
                <?= e(
                    (string)$settings['currency']
                ) ?>
            </strong>

        </div>


        <div class="setting-item">

            <span>
                🌍 Timezone
            </span>

            <strong>
                <?= e(
                    (string)$settings['timezone']
                ) ?>
            </strong>

        </div>


        <div
            style="
                margin-top:25px;
                padding:15px;
                border-radius:8px;
                background:#f8fafc;
            "
        >

            <strong>
                💡 Tip
            </strong>

            <p style="margin-bottom:0;">

                These settings are used throughout the
                farm management system for displaying
                farm information, currency and dates.

            </p>

        </div>

    </div>

</div>


<style>

.settings-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.5fr)
        minmax(280px, 1fr);

    gap: 20px;

}


.section-heading {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 25px;

}


.section-heading h3 {

    margin: 0 0 5px 0;

}


.section-heading p {

    margin: 0;

    color: #6b7280;

}


.section-icon {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f3f4f6;

    border-radius: 10px;

    font-size: 22px;

}


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


.form-group input:focus,
.form-group select:focus {

    outline: none;

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.1);

}


.form-group small {

    display: block;

    margin-top: 6px;

    color: #6b7280;

}


.setting-item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    padding: 15px 0;

    border-bottom: 1px solid #e5e7eb;

}


.setting-item:last-of-type {

    border-bottom: none;

}


.setting-item span {

    color: #6b7280;

}


.setting-item strong {

    text-align: right;

    word-break: break-word;

}


@media (max-width: 900px) {

    .settings-layout {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .dashboard-card {

        padding: 15px;

    }


    .setting-item {

        align-items: flex-start;

        flex-direction: column;

        gap: 5px;

    }


    .setting-item strong {

        text-align: left;

    }


    .section-heading {

        align-items: flex-start;

    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>