<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__, ['../.env'], false);
$dotenv->safeLoad();

function login (string $username, string $password): bool {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }

    if (!isset($_ENV['ADMIN_USERNAME']) || !isset($_ENV['ADMIN_PASSWORD'])) {
        return false;
    }

    $admin_username = $_ENV['ADMIN_USERNAME'];
    $admin_password = $_ENV['ADMIN_PASSWORD'];
    $logged_in = $username === $admin_username && $password === $admin_password;

    $_SESSION['logged_in'] = $logged_in;
    return $logged_in;
}