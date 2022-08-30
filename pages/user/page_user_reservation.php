<?php
declare(strict_types=1);
global $config;
echo create_header('Kajak Reservierung', '/');
$connection = $_SESSION['connection'];

/* prepare tables */
add_reservation_table($connection);
add_kajak_table($connection);
add_reservation_kajak_table($connection);
add_blacklist_table($connection);

/* create csrf token */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['token'] = get_random_token();
    $_SESSION['token_field'] = get_random_token();
}
?>

<div class="container my-2">
    <div class="row">
        <div class="col-lg-5 mx-auto">
            <div class="row content">
                <div class="content-wrapper">
                    <h3 class="content-header">Was bieten wir an?</h3>
                    <p>
                        Wir bieten für die HTWG Konstanz und für die Universität Konstanz die Möglichkeit, Kajaks zu
                        reservieren.<br/>
                        Bitte fülle das Formular aus, damit wir überprüfen können, ob zu deinem gewünschten
                        Zeitslot und Datum Kajaks frei sind.
                    </p>
                </div>
            </div>
            <div class="row content">
                <?php
                include __DIR__ . '/../../components/reservation/reservation_kajak_models.php' ?>
            </div>
        </div>

        <div class="col-lg-6 mx-auto">
            <div class="row content">
                <?php
                global $ERROR_RESERVATION;

                /* check if csrf token match */
                $token = clean_string($_POST[$_SESSION['token_field'] ?? ''] ?? '');

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token && $token === $_SESSION['token']) {
                    $ret_val = user_reservate_kajak($connection, $_POST, TRUE);
                }

                ?>
                <div class='custom-form'> <?php
                    if (isset($ret_val) && $ret_val->status) {
                        includeFileWithVariables(__DIR__ . '/../../components/reservation/reservation_kajak_success.php', ['message' => $ret_val->statusMessage]);
                    } else {
                        include __DIR__ . '/../../components/reservation/reservation_kajak.php';
                        if ((isset($ret_val) && !$ret_val->status) || ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$token || $token !== $_SESSION['token']))) {
                            ?>
                            <h3>
                            <?= $ret_val->statusMessage ?? $ERROR_RESERVATION ?>
                            </h3><?php
                        }
                    }
                    ?></div><?php
                ?>
            </div>
        </div>
    </div>
</div>
