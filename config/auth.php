<?php

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function cekLogin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function cekRole(array $roles): void
{
    cekLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        header('Location: /dashboard.php');
        exit;
    }
}
