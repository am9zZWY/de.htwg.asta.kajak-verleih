<?php
require __DIR__ . '/vendor/autoload.php';

/* used to load credentials from .env file */

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$ENVIRONMENT = isset($_ENV['ENVIRONMENT']) ? $_ENV['ENVIRONMENT'] : 'PROD';
error_reporting($ENVIRONMENT === 'DEV' ? E_ALL : 0);
ini_set('display_errors', $ENVIRONMENT === 'DEV');

/* initialize config */
require __DIR__ . '/scripts/Config.php';
$config = new Config();
/* general / important scripts */
require __DIR__ . '/scripts/Router.php';
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

/* set up the database connection and the tables */
$connection = connect_to_database();
$_SESSION['connection'] = $connection;
add_reservation_table($connection);
add_kajak_table($connection);
add_reservation_kajak_table($connection);
add_blacklist_table($connection);

route($PARSED_URL, FALSE);
?>
<!DOCTYPE html>
<html lang='de' xmlns='http://www.w3.org/1999/html'>
<head>
    <meta content='text/html; charset=utf-8' http-equiv='Content-Type'/>
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <meta content='Kajak Verleihsystem des AStA der HTWG Konstanz' name='description'>
    <link href='/static/css/custom.css' rel='stylesheet'>
    <link href='/static/css/bootstrap.min.css' rel='stylesheet'>
    <title>Kajak Verleihsystem von Coolen Typen</title>
</head>
<body>
<div class="container p-0">
    <?php
    /* navigation bar */
    include __DIR__ . '/components/component_navigation.php';
    ?>
</div>
<div class="container p-0">
    <?php
    route($PARSED_URL, TRUE);
    ?>
</div>
<?php
include __DIR__ . '/components/component_footer.php'; ?>
</body>
</html>
