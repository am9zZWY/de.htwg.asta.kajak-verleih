<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/scripts/reservation.php';
require_once __DIR__ . '/scripts/helpers.php';

/* Setup the database connection and the reservation table */
$connection = connect_to_database();
prepare_reservation_table($connection);

include 'templates/head.php'
?>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<body>
<?php include 'templates/sidebar.php' ?>
<div class="section" id="booking">

    <div class="section-center">
        <div class="booking-cta">
            <a href="index.php" class="primary text-decoration-none">
                <h1>
                    Powered by AStA HTWG
                </h1>
            </a>
        </div>
        <?php
        global $timeslots_field;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ret_val = reservate_kajak($connection, $_POST);
            if ($ret_val === true) {
                ?>
                <div class="booking-cta">
                    <a href="index.php" class="primary text-decoration-none">
                        <h2>
                            Reservierung erfolgreich
                        </h2>
                    </a>
                </div>
                <?php
            } else {
                ?>
                <div class="booking-cta">
                    <a href="index.php" class="danger text-decoration-none">
                        <h2>
                            <?php echo $ret_val ?>
                        </h2>
                    </a>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="booking-form">
                            <form action="index.php" method="post" class="needs-validation">
                                <div class="form-floating mb-3">
                                    <input name="name" type="text" placeholder="Max Musterfrau" id="name"
                                           class="form-control"
                                           required>
                                    <label for="name">
                                        Name
                                    </label>
                                </div>

                                <div class="row my-2">
                                    <div class="col-sm-6">
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

                                    <div class="col-sm-6">
                                        <div class="mb-3 form-floating">
                                            <input name="phone" type="tel" placeholder="+49 (0) 123 456789" id="phone"
                                                   class="form-control"
                                                   required>
                                            <label for="phone">
                                                Telefonnummer
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row my-2">
                                    <div class="col-sm-6">
                                        <!-- select dates -->
                                        <div class="form-group form-floating">
                                            <select name="date" class="form-select" id="date" autocomplete="on"
                                                    required>
                                                <?php
                                                foreach (get_days() as $day) {
                                                    ?>
                                                    <option value=" <?php echo $day[1] ?>">
                                                        <?php echo $day[0] ?>
                                                    </option>
                                                <?php } ?>

                                            </select>
                                            <label for="date">
                                                Datum
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group form-floating">
                                            <span class="form-label">Zeitslots</span><br>
                                            <!-- time slots -->
                                            <?php
                                            global $timeslots_formatted;
                                            foreach ($timeslots_formatted as $index => $timeslot) { ?>
                                                <span class="form-check-label">
                                                <input type="checkbox" name="timeslots[]"
                                                       value="<?php echo $index ?>"
                                                       class="form-check-input">
                                                <?php echo $timeslot ?>
                                            </span>
                                                <br>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row my-2">
                                    <?php global $amount_kajaks ?>
                                    <div class="col-md-6">
                                        <img alt="Bild eines einzelnen Kajaks" src="resources/images/EinzelKajak.png" class="img-fluid"/>
                                        <div class="form-group form-floating">
                                            <input type="number" max="<?php echo $amount_kajaks["single_kajak"] ?>"
                                                   min="0" id="single-kajak" value="0"
                                                   name="single-kajak" class="form-control"/>
                                            <label class="form-check-label" for="single-kajak">
                                                Anzahl 1-Sitz Kajaks
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <img alt="Bild eines doppelten Kajaks" src="resources/images/doppelKajak.png" class="img-fluid"/>
                                        <div class="form-group form-floating">
                                            <input type="number" max="<?php echo $amount_kajaks["double_kajak"] ?>" min="0" id="double-kajak" value="0"
                                                   name="double-kajak" class="form-control"/>
                                            <label class="form-check-label" for="double-kajak">
                                                Anzahl 2-Sitz Kajaks
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <input type="submit" value="Anfrage senden" class="btn btn-primary"/>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="booking-cta">
                            <h2 class="primary">Reserviere Kajaks</h2>
                            <p>
                                Wir bieten für die HTWG Konstanz und für Universität Konstanz die Möglichkeit, Kajaks zu reservieren.
                                Bitte fülle das Formular aus, damit wir überprüfen können, ob an deinem gewünschten
                                Datum
                                und Zeit Kajaks frei sind.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
