<?php

/**
 * Login as admin.
 *
 * @param string $username
 * @param string $password
 *
 * @return bool
 */
function login(string $username, string $password): bool
{
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE) {
        return TRUE;
    }

    if (!isset($_ENV['ADMIN_USERNAME'], $_ENV['ADMIN_PASSWORD'])) {
        return FALSE;
    }

    $admin_username = $_ENV['ADMIN_USERNAME'];
    $admin_password = $_ENV['ADMIN_PASSWORD'];
    $logged_in = $username === $admin_username && $password === $admin_password;

    $_SESSION['logged_in'] = $logged_in;
    return $logged_in;
}

/**
 * Logout.
 *
 * @return void
 */
function logout(): void
{
    unset($_SESSION['logged_in']);
}


/**
 * Check if admin is logged in.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE;
}