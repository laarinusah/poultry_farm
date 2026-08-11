<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| Application Configuration
|--------------------------------------------------------------------------
*/

define(
    'APP_NAME',
    'Poultry Farm Management System'
);

define(
    'APP_URL',
    'http://localhost/poultry_farm'
);

define(
    'APP_VERSION',
    '1.0.0'
);


/*
|--------------------------------------------------------------------------
| Default Settings
|--------------------------------------------------------------------------
*/

define(
    'DEFAULT_FARM_NAME',
    'Poultry Farm'
);

define(
    'DEFAULT_CURRENCY',
    'GHS'
);

define(
    'DEFAULT_TIMEZONE',
    'Africa/Accra'
);


/*
|--------------------------------------------------------------------------
| Default Timezone
|--------------------------------------------------------------------------
|
| This is the fallback timezone.
| The actual farm timezone will be loaded from
| farm_settings after the database connection is available.
|
*/

date_default_timezone_set(
    DEFAULT_TIMEZONE
);