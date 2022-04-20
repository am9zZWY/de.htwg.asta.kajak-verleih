<?php
require __DIR__ . '/vendor/autoload.php';

/* Used to load credentials from .env file */

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require __DIR__ . '/scripts/script_helpers.php';
require __DIR__ . '/scripts/script_login.php';
require __DIR__ . '/scripts/script_reservation.php';
require __DIR__ . '/scripts/script_template_helpers.php';
require __DIR__ . '/scripts/script_email.php';
require __DIR__ . '/scripts/script_errors.php';
require __DIR__ . '/scripts/Config.php';

/* If session is not set start it */
session_start();


/* Get the current address because index.php acts as a router */
$URL = $_SERVER['REQUEST_URI'];
$PARSED_URL = strtolower(parse_url($URL, PHP_URL_PATH));

/* Send headers */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* Set up the database connection and the reservation table */
$connection = connect_to_database();
$_SESSION['connection'] = $connection;
prepare_reservation_table($connection);

/* Initialize Config Class*/
$_SESSION['config'] = new Config();
?>
<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kajak Verleihsystem des AStA der HTWG Konstanz">
    <link rel="stylesheet" href="/static/css/custom.css">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <title>Kajak Verleihsystem von Coolen Typen</title>
</head>
<body>
<?php
include 'templates/template_sidebar.php';
?>
<div class="container">
    <?php
    if ($PARSED_URL === '/about') {
        require("pages/user/page_user_agb.php");
    } else if ($PARSED_URL === '/kajaks') {
        require("pages/user/page_user_kajaks.php");
    } else if ($PARSED_URL === '/impressum') {
        require("pages/user/page_user_impressum.php");
    } else if ($PARSED_URL === '/login') {
        require("pages/admin/page_admin_login.php");
    } else if ($PARSED_URL === '/cancel') {
        require("pages/user/page_user_cancel.php");
    } else if ($PARSED_URL === '/') {
        require("pages/user/page_user_reservation.php");
    }

    if (is_logged_in()) {
        if ($PARSED_URL === '/reservations') {
            require("pages/admin/page_admin_reservations.php");
        } elseif ($PARSED_URL === '/how_to_admin') {
            require("pages/admin/page_admin_how_to.php");
        } elseif ($PARSED_URL === '/config') {
            require("pages/admin/page_admin_config.php");
        }
    }

    ?>
</div>
<?php require("templates/template_footer.php"); ?>
</body>
</html>
