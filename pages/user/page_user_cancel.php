<?php
echo create_header('Stornierung');
$connection = $_SESSION['connection'];

global $URL;
$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}
$reservation_id = clean_string($params['id'] ?? '');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-5 content mx-auto">
            <div class="custom-form">
                <form method="post" class="needs-validation">
                    <div class="col-sm">
                        <div class="mb-3 form-floating">
                            <input name="id" type="text" placeholder="12345"
                                   id="id"
                                   class="form-control"
                                   readonly
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
                    <button type="submit" class="btn custom-btn">Stornieren</button>
                </form>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    ?>
                    <h2>
                        <?php echo cancel_reservation($connection, $_POST, TRUE) ?>
                    </h2>
                <?php }
                ?>
            </div>
        </div>
    </div>
</div>