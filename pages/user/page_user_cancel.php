<?php
create_header('Stornierung');
$connection = $_SESSION['connection'];

$URL = $_SERVER['REQUEST_URI'];
$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}
$reservation_id = clean_string($params['id'] ?? '');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-5 ms-auto">
            <div class="custom-form">
                <form method="post" class="needs-validation">
                    <div class="col-sm">
                        <div class="mb-3 form-floating">
                            <input name="id" type="text" placeholder="12345"
                                   id="id"
                                   class="form-control"
                                   value="<?php echo $reservation_id; ?>"
                                   required>
                            <label for="id">
                                Reservierungsnummer
                            </label>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="mb-3 form-floating">
                            <input name="email" type="email" placeholder="ma391mus@htwg-konstanz.de"
                                   id="email"
                                   class="form-control"
                                   required>
                            <label for="email">
                                E-Mail
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Stornieren</button>
                </form>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    ?>
                    <h2>
                        <?php echo cancel_reservation($connection, $_POST, true) ?>
                    </h2>
                <?php }
                ?>
            </div>
        </div>
        <div class="col-lg-3 me-auto">
            <div class="text-light header-wrapper">
                <h2>Was bieten wir an?</h2>
                <p>
                    Wir bieten für die HTWG Konstanz und für Universität Konstanz die Möglichkeit, Kajaks zu
                    reservieren.
                    Bitte fülle das Formular aus, damit wir überprüfen können, ob an deinem gewünschten
                    Datum
                    und Zeit Kajaks frei sind.
                </p>
            </div>
        </div>
    </div>
</div>