<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$password = 'Admin@12345';

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare("
    INSERT INTO users
    (
        full_name,
        username,
        email,
        password_hash,
        role,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    'System Administrator',
    'admin',
    'admin@poultryfarm.local',
    $passwordHash,
    'admin',
    'active'
]);

echo 'Admin account created successfully.';