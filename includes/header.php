<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Professional Poultry Farm Management System"
    >

    <title>
        <?= e($pageTitle ?? APP_NAME) ?>
    </title>


    <!-- ==========================================================
         BOOTSTRAP ICONS
    =========================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- ==========================================================
         APPLICATION CSS
    =========================================================== -->

    <link
        rel="stylesheet"
        href="../public/assets/css/app.css"
    >

</head>


<body>


<div class="app-layout">


    <!-- ==========================================================
         SIDEBAR
    =========================================================== -->

    <?php require __DIR__ . '/sidebar.php'; ?>


    <!-- ==========================================================
         MAIN CONTENT
    =========================================================== -->

    <main class="main-content">


        <!-- ======================================================
             TOP BAR
        ======================================================= -->

        <header class="topbar">


            <!-- Mobile Menu Button -->

            <button
                class="menu-toggle"
                id="menuToggle"
                onclick="document.getElementById('sidebar').classList.toggle('mobile-open');" type="button"
                aria-label="Open navigation menu"
                aria-expanded="false"
            >

                <i class="bi bi-list"></i>

            </button>


            <!-- Page Title -->

            <div class="topbar-title">

                <h1>
                    <?= e($pageTitle ?? 'Dashboard') ?>
                </h1>

            </div>


            <!-- Current User -->

            <div class="user-menu">

                <span
                    class="user-icon"
                    aria-hidden="true"
                >

                    <i class="bi bi-person-circle"></i>

                </span>


                <span>

                    <?= e($_SESSION['full_name'] ?? 'User') ?>

                </span>

            </div>


        </header>


        <!-- ======================================================
             PAGE CONTENT
        ======================================================= -->

        <div class="page-content">
