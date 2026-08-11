<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Current Page
|--------------------------------------------------------------------------
*/

$currentUri = $_SERVER['REQUEST_URI'] ?? '';

$currentPage = basename(
    parse_url($currentUri, PHP_URL_PATH) ?? ''
);


/*
|--------------------------------------------------------------------------
| Active Menu Helper
|--------------------------------------------------------------------------
*/

function sidebarActive(string $section): string
{
    global $currentUri;

    return str_contains(
        $currentUri,
        $section
    )
        ? 'active'
        : '';
}

?>

<aside class="sidebar" id="sidebar">


    <!-- ================================================================
         BRAND
    ================================================================= -->

    <div class="sidebar-brand">

        <div class="brand-icon">
            🐔
        </div>

        <div>

            <strong>
                <?= e(farmName()) ?>
            </strong>

            <small>
                Management System
            </small>

        </div>

    </div>


    <!-- ================================================================
         NAVIGATION
    ================================================================= -->

    <nav class="sidebar-nav">


        <!-- ============================================================
             MAIN
        ============================================================= -->

        <div class="nav-section">
            MAIN
        </div>


        <a
            href="../dashboard/index.php"
            class="<?= sidebarActive('/dashboard/') ?>"
        >

            <span>
                🏠
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- ============================================================
             FARM MANAGEMENT
        ============================================================= -->

        <div class="nav-section">
            FARM MANAGEMENT
        </div>


        <!-- Poultry Batches -->

        <a
            href="../batches/index.php"
            class="<?= sidebarActive('/batches/') ?>"
        >

            <span>
                🐔
            </span>

            <span>
                Poultry Batches
            </span>

        </a>


        <!-- Feed Types -->

        <a
            href="../feed_types/index.php"
            class="<?= sidebarActive('/feed_types/') ?>"
        >

            <span>
                🌾
            </span>

            <span>
                Feed Types
            </span>

        </a>


        <!-- Feed Usage -->

        <a
            href="../feed/index.php"
            class="<?= sidebarActive('/feed/') ?>"
        >

            <span>
                🌾
            </span>

            <span>
                Feed Usage
            </span>

        </a>


        <!-- Egg Production -->

        <a
            href="../eggs/index.php"
            class="<?= sidebarActive('/eggs/') ?>"
        >

            <span>
                🥚
            </span>

            <span>
                Egg Production
            </span>

        </a>


        <!-- Egg Sales -->

        <a
            href="../sales/index.php"
            class="<?= sidebarActive('/sales/') ?>"
        >

            <span>
                💰
            </span>

            <span>
                Egg Sales
            </span>

        </a>


        <!-- Mortality -->

        <a
            href="../mortality/index.php"
            class="<?= sidebarActive('/mortality/') ?>"
        >

            <span>
                💀
            </span>

            <span>
                Mortality
            </span>

        </a>


        <!-- Expenses -->

        <a
            href="../expenses/index.php"
            class="<?= sidebarActive('/expenses/') ?>"
        >

            <span>
                💸
            </span>

            <span>
                Expenses
            </span>

        </a>


        <!-- ============================================================
             ANALYTICS
        ============================================================= -->

        <div class="nav-section">
            ANALYTICS
        </div>


        <!-- Reports -->

        <a
            href="../reports/index.php"
            class="<?= sidebarActive('/reports/') ?>"
        >

            <span>
                📊
            </span>

            <span>
                Reports
            </span>

        </a>


        <!-- ============================================================
             SYSTEM
        ============================================================= -->

        <div class="nav-section">
            SYSTEM
        </div>


        <!-- Users -->

        <a
            href="../users/index.php"
            class="<?= sidebarActive('/users/') ?>"
        >

            <span>
                👥
            </span>

            <span>
                Users
            </span>

        </a>


        <!-- Settings -->

        <a
            href="../settings/index.php"
            class="<?= sidebarActive('/settings/') ?>"
        >

            <span>
                ⚙️
            </span>

            <span>
                Settings
            </span>

        </a>

    </nav>


    <!-- ================================================================
         FOOTER
    ================================================================= -->

    <div class="sidebar-footer">

        <a
            href="../auth/logout.php"
            class="logout-link"
        >

            <span>
                🚪
            </span>

            <span>
                Logout
            </span>

        </a>

    </div>


</aside>