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

    <link
        rel="stylesheet"
        href="../public/assets/css/app.css"
    >

</head>

<body>

<div class="app-layout">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">

        <header class="topbar">

            <button
                class="menu-toggle"
                id="menuToggle"
                type="button"
            >
                ☰
            </button>

            <div class="topbar-title">

                <h1>
                    <?= e($pageTitle ?? 'Dashboard') ?>
                </h1>

            </div>

            <div class="user-menu">

                <span class="user-icon">
                    👤
                </span>

                <span>
                    <?= e($_SESSION['full_name'] ?? 'User') ?>
                </span>

            </div>

        </header>

        <div class="page-content">