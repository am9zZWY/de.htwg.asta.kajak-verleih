<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/scripts/database.php';
require_once __DIR__ . '/scripts/helpers.php';

$database = connect_to_database();
?>

<html lang="de" xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kajak Verleihsystem des AStA der HTWG Konstanz">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title>Kajak Verleihsystem von Coolen Typen</title>
</head>
<body>
<div class="section">
    <div class="section-center">
        <div class="booking-cta">
            <a href="index.php" class="link-primary text-decoration-none">
                <h1>
                    Powered by AStA HTWG
                </h1>
            </a>
        </div>
        <div class="container">
            <div class="row">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    var_dump($_POST);
                } else {
                // configs
                // all weekdays in german
                $newLocal = setlocale(LC_ALL, 'de_DE', 'de_DE.UTF-8');
                $weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
                // start date is today
                $date = date_create();
                // add two days to start date
                $min_day = 3;
                date_add($date, new DateInterval("P${min_day}D"));
                // max days for calendar
                $max_days = 14;
                // timeslots
                $timeslots = array("9:00 - 13:00", "13:00 - 18:00");
                ?>
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
                                        <select name="date" class="form-select" id="date" autocomplete="on" required>
                                            <?php for ($day = 0; $day < $max_days; $day++) {
                                                $weekday = (int)$date->format('w');
                                                if ($weekday !== 0 && $weekday !== 6) { ?>
                                                    <option value="<?php echo $date->format('Y-m-d') ?>">
                                                        <?php echo $weekdays[$weekday] . ' ' . $date->format('d.m.Y') ?>
                                                    </option>
                                                    <?php
                                                }
                                                date_add($date, new DateInterval('P1D'));
                                            } ?>
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
                                        <?php foreach ($timeslots as $timeslot) { ?>
                                            <label class="form-check-label">
                                                <input type="checkbox" name="timeslots[]"
                                                       value="<?php echo $timeslot ?>"
                                                       class="form-check-input">
                                                <?php echo $timeslot ?>
                                            </label>
                                            <br>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-6">
                                    <img alt="Bild eines einzelnen Kajaks" class="img-fluid"/>
                                    <div class="form-group form-floating">
                                        <input type="number" max="9" min="0" id="single-kajak" value="0"
                                               name="single-kajak" class="form-control"/>
                                        <label class="form-check-label" for="single-kajak">
                                            Anzahl 1-Sitz Kajaks
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <img alt="Bild eines doppelten Kajaks" class="img-fluid"/>
                                    <div class="form-group form-floating">
                                        <input type="number" max="9" min="0" id="double-kajak" value="0"
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
                        <h2>Reserviere Kajaks</h2>
                        <p>
                            Wir bieten dir die Möglichkeit, kostenlos Kajaks zu reservieren.
                            Bitte fülle das Formular aus, damit wir überprüfen können, ob an deinem gewünschten Datum
                            und Zeit Kajaks frei sind.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
</body>
</html>
