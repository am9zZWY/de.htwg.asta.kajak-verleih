<?php
global $config;
create_header('Kajak Reservierung', '/');
$connection = $_SESSION['connection'];
$kajaks = $config->getKajaks(true);
$prices = $config->getPrices(false);
?>

<div class="container my-2">
    <div class="row">
        <div class="col-lg-5 mx-auto">
            <div class="row content">
                <div class="header-wrapper">
                    <h3>Was bieten wir an?</h3>
                    <p>
                        Wir bieten für die HTWG Konstanz und für Universität Konstanz die Möglichkeit, Kajaks zu
                        reservieren.
                        Bitte fülle das Formular aus, damit wir überprüfen können, ob an deinem gewünschten
                        Datum
                        und Zeit Kajaks frei sind.
                    </p>
                </div>
            </div>
            <div class="row content">
                <div class="header-wrapper">
                    <h3>Welche Kajak-Modelle gibt es?</h3>
                    Es gibt <?php echo count($kajaks) ?> Kajak-Modelle:
                    <?php
                    foreach ($kajaks as $kajak) {
                        ?>
                        <div>
                            <strong><?php echo $kajak->name ?></strong>
                            <img alt="Bild von <?php echo $kajak->name ?>" src="<?php echo $kajak->img ?>"
                                 class="img-fluid" style="width: 300px; height: 200px;"/>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mx-auto">
            <div class="row content">
                <div class="custom-form">
                    <form action="/" method="post" class="needs-validation">
                        <div class="row my-2">
                            <div class="col-sm-6">
                                <div class="mb-3 form-floating">
                                    <input name="name" type="text" placeholder="Max"
                                           value="<?php echo get_post_field('name') ?>"
                                           id="name"
                                           class="form-control"
                                           required>
                                    <label for="name">
                                        Vorname
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="mb-3 form-floating">
                                    <input name="surname" type="text" placeholder="Mustermann"
                                           value="<?php echo get_post_field('surname') ?>"
                                           id="surname"
                                           class="form-control"
                                           required>
                                    <label for="surname">
                                        Nachname
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row my-2">
                            <div class="col-sm-6">
                                <div class="mb-3 form-floating">
                                    <input name="email" type="email" placeholder="ma391mus@htwg-konstanz.de"
                                           value="<?php echo get_post_field('email') ?>"
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
                                    <input name="phone" type="tel" placeholder="+49 (0) 123 456789"
                                           value="<?php echo get_post_field('phone') ?>"
                                           id="phone"
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
                                <div class="mb-3 form-floating">
                                    <input name="street" type="text" placeholder="Straße, Hausnummer"
                                           id="street"
                                           class="form-control"
                                           required>
                                    <label for="street">
                                        Straße, Hausnummer
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="mb-3 form-floating">
                                    <input name="plz" type="text" placeholder="PLZ" id="plz"
                                           class="form-control"
                                           required>
                                    <label for="plz">
                                        Postleitzahl
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row my-2">
                            <div class="col-sm-6">
                                <div class="form-group form-floating">
                                    <input name="city" type="text" placeholder="Stadt"
                                           id="city"
                                           class="form-control"
                                           required>
                                    <label for="city">
                                        Stadt
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group form-floating">
                                    <select name="country" class="form-select" id="country" autocomplete="on"
                                            required>
                                        <option>Deutschland</option>
                                        <option>Schweiz</option>
                                        <option>Österreich</option>
                                    </select>
                                    <label for="country">
                                        Land
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="row my-2">
                            <div class="col-sm-6">
                                <!-- select dates -->
                                <div class="form-group form-floating">
                                    <?php
                                    /* is put here for better debug, better NOT move it further down */
                                    $days = $config->getDays();
                                    ?>
                                    <select name="date" class="form-select" id="date" autocomplete="on"
                                            required>
                                        <?php
                                        foreach ($days as $day) {
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
                                    <!-- select time slots -->
                                    <?php
                                    foreach ($config->getFormattedTimeslots() as $index => $timeslot) { ?>
                                        <span class="form-check-label">
                                                <input type="checkbox" name="timeslots[]"
                                                       value="<?php echo $index ?>"
                                                       class="form-check-input timeslot">
                                                <?php echo $timeslot ?>
                                            </span>
                                        <br>
                                    <?php } ?>
                                    <div class="row mt-2">
                                        <p>Ein Zeitslot kostet <strong>pro Person 5€</strong><br>
                                            Beide Zeitslots kosten <strong>pro Person 8€</strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row my-2">
                                <?php global $amount_kajaks;
                                foreach ($kajaks as $kajak) {
                                    ?>
                                    <div class="col-md-6">
                                        <div class="form-group form-floating">
                                            <input type="number" max="<?php echo $kajak->amount ?>"
                                                   min="0" id="<?php echo $kajak->intName ?>"
                                                   value="<?php echo get_post_field($kajak->intName, 0) ?>"
                                                   name="<?php echo $kajak->intName ?>" class="form-control"/>
                                            <label class="form-check-label" for="<?php echo $kajak->intName ?>">
                                                Anzahl <?php echo $kajak->seats ?>-Sitz Kajaks
                                            </label>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>

                            <div class="row my-2">
                                <div class="col-md-6">
                                    <label>
                                        <input type="checkbox" name="is_studi" value="1"
                                               required <?php echo get_post_field('is_studi') === '1' ? 'checked' : '' ?>
                                               class="form-check-input">
                                        Hiermit bestätige ich, dass ich eine studierende Person an der HTWG
                                        Konstanz oder der Universität Konstanz bin.
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label>
                                        <input type="checkbox" name="terms" value="1"
                                               required <?php echo get_post_field('terms') === '1' ? 'checked' : '' ?>
                                               class="form-check-input">
                                        Ich habe die <a href="/about">Nutzungsbedingungen</a> gelesen und
                                        akzeptiere sie.
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <span id="calculated-price"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <button type="submit" class="btn custom-btn">Anfrage senden</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        const calculated_price_element = document.getElementById('calculated-price');
                        const calculate_price = () => {
                            <?php
                            foreach ($kajaks as $kajak) {
                            ?>const <?php echo 'amount_' . $kajak->intName ?> = parseInt(document.getElementById('<?php echo $kajak->intName ?>').value);
                            <?php
                            } ?>

                            const amount_timeslots = Array.from(document.getElementsByClassName('timeslot')).filter(timeslot => timeslot.checked).length;
                            const kaution = <?php echo (int)$prices->kaution->price ?>;

                            let price;
                            if (amount_timeslots === 0) {
                                price = 0;
                            } else if (amount_timeslots === 1) {
                                price = <?php echo (int)$prices->single->price ?>;
                            } else {
                                price = <?php echo (int)$prices->complete->price ?>;
                            }

                            calculated_price_element.innerHTML = '<strong>Bitte bringe ' + ((<?php
                                foreach ($kajaks as $kajak) {
                                ?><?php echo 'amount_' . $kajak->intName ?> + <?php
                                } ?>0) * price + kaution) + '€ in Bar mit.</strong>';
                        }


                        document.getElementById('single_kajak').addEventListener('change', calculate_price)
                        document.getElementById('double_kajak').addEventListener('change', calculate_price)
                        Array.from(document.getElementsByClassName('timeslot')).forEach((timeslot) => timeslot.addEventListener('change', calculate_price))
                    </script>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $ret_val = reservate_kajak($connection, $_POST, true);
                        if ($ret_val === true) {
                            ?>
                            <h3>
                                Reservierung erfolgreich
                            </h3>
                            <?php
                        } else {
                            ?>
                            <h2>
                                <?php echo $ret_val ?>
                            </h2>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
