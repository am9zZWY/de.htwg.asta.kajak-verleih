<?php
require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/scripts/reservation.php';
require __DIR__ . '/scripts/helpers.php';
require __DIR__ . '/scripts/template_helpers.php';
require __DIR__ . '/scripts/login.php';

/* Used to load credentials from .env file */

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

/* If session is not set start it */
session_start();

$URL = $_SERVER['REQUEST_URI'];

$PARSED_URL = parse_url($URL, PHP_URL_PATH);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* Setup the database connection and the reservation table */
$connection = connect_to_database();
$_SESSION['connection'] = $connection;
prepare_reservation_table($connection);

function logged_in(): bool
{
    return true;
}

include 'templates/head.php'
?>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<body>
<?php include 'templates/sidebar.php' ?>
<div class="bg">
    <div class="section-center">
        <?php
        if ($PARSED_URL === '/') {
            require("pages/user/page_user_reservation.php");
        } else if ($PARSED_URL === '/about') {
            require("pages/user/about.php");
        } else if ($PARSED_URL === '/kajaks') {
            require("pages/user/kajaks.php");
        } else if ($PARSED_URL === '/impressum') {
            require("pages/user/impressum.php");
        } else if ($PARSED_URL === '/login') {
            require("pages/admin/login.php");
        }

        if (logged_in()) {
            if ($PARSED_URL === '/reservations') {
                require("pages/admin/reservations.php");
            }
        }
        ?>
    </div>

</div>
</body>
</html>
