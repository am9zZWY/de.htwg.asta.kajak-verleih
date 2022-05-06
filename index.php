<?php
require __DIR__ . '/vendor/autoload.php';

/* used to load credentials from .env file */

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

/* initialize config */
require __DIR__ . '/scripts/Config.php';
$config = new Config();
/* general / important scripts */
require __DIR__ . '/scripts/General.php';
require __DIR__ . '/scripts/Errors.php';
/* reservation */
require __DIR__ . '/scripts/database/Blacklist.php';
require __DIR__ . '/scripts/database/General.php';
require __DIR__ . '/scripts/database/Kajak.php';
require __DIR__ . '/scripts/database/Reservation.php';
/* miscellaneous scripts */
require __DIR__ . '/scripts/Login.php';
require __DIR__ . '/scripts/Templates.php';
require __DIR__ . '/scripts/Email.php';

/* if session is not set start it */
session_start();

/* get the current address because index.php acts as a router */
$URL = $_SERVER['REQUEST_URI'];
$PARSED_URL = strtolower(parse_url($URL, PHP_URL_PATH));

/* send headers */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", FALSE);
header("Pragma: no-cache");

/* set up the database connection and the tables */
$connection = connect_to_database();
$_SESSION['connection'] = $connection;
add_reservation_table($connection);
add_kajak_table($connection);
add_reservation_kajak_table($connection);
add_blacklist_table($connection);

/* API */
if ($PARSED_URL === '/api') {
    require("pages/api/page_api.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kajak Verleihsystem des AStA der HTWG Konstanz">
    <link rel="stylesheet" href="/static/css/custom.css">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <title>Kajak Verleihsystem von Coolen Typen</title>
</head>
<body>
<div class="container p-0">
    <?php
    /* navigation bar */
    include 'components/component_navigation.php';
    ?>
</div>
<div class="container p-0">
    <?php
    if ($PARSED_URL === '/about' || $PARSED_URL === '/terms') {
        require("pages/user/page_user_agb.php");
    } else if ($PARSED_URL === '/privacy' || $PARSED_URL === '/dsgvo' || $PARSED_URL === '/datenschutz') {
        require("pages/user/page_user_privacy.php");
    } else if ($PARSED_URL === '/impressum') {
        require("pages/user/page_user_impressum.php");
    } else if ($PARSED_URL === '/login') {
        require("pages/admin/page_admin_login.php");
    } else if ($PARSED_URL === '/cancel') {
        require("pages/user/page_user_cancel.php");
    } else if ($PARSED_URL === '/') {
        require("pages/user/page_user_reservation.php");
    }

    /* show these pages only when logged in */
    if ($PARSED_URL === '/admin' && is_logged_in()) {
        require("pages/admin/page_admin.php");
    }

    ?>
</div>
<?php require("components/component_footer.php"); ?>
</body>
</html>
