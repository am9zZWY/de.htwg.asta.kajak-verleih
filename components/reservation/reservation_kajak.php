<?php
global $config;
$connection = $_SESSION['connection'];
$available_kajaks = get_kajak_with_real_amount($connection);
$kajaks = $config->get_kajaks();
?>


<form action='/' method='post' class='needs-validation'>
    <input type='hidden'
           name="<?= $_SESSION['token_field'] ?? '' ?>"
           value="<?= $_SESSION['token'] ?? '' ?>">
    <div class='row my-2'>
        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='name' type='text' placeholder='Max'
                       value="<?= get_post_field('name') ?>"
                       id='name'
                       class='form-control'
                       required>
                <label for='name'>
                    Vorname
                </label>
            </div>
        </div>

        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='surname' type='text' placeholder='Mustermann'
                       value="<?= get_post_field('surname') ?>"
                       id='surname'
                       class='form-control'
                       required>
                <label for='surname'>
                    Nachname
                </label>
            </div>
        </div>
    </div>

    <div class='row my-2'>
        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='email' type='email' placeholder='ma391mus@htwg-konstanz.de'
                       value="<?= get_post_field('email') ?>"
                       id='email'
                       class='form-control'
                       required>
                <label for='email'>
                    HTWG / Uni E-Mail
                </label>
            </div>
        </div>

        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='phone' type='tel' placeholder='+49 (0) 123 456789'
                       value="<?= get_post_field('phone') ?>"
                       id='phone'
                       class='form-control'
                       required>
                <label for='phone'>
                    Telefonnummer
                </label>
            </div>
        </div>
    </div>

    <div class='row my-2'>
        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='street' type='text' placeholder='Straße, Hausnummer'
                       value="<?= get_post_field('street') ?>"
                       id='street'
                       class='form-control'
                       required>
                <label for='street'>
                    Straße, Hausnummer
                </label>
            </div>
        </div>

        <div class='col-sm-6'>
            <div class='mb-3 form-floating'>
                <input name='plz' type='text' placeholder='PLZ' id='plz'
                       value="<?= get_post_field('plz') ?>"
                       class='form-control'
                       required>
                <label for='plz'>
                    Postleitzahl
                </label>
            </div>
        </div>
    </div>

    <div class='row my-2'>
        <div class='col-sm-6'>
            <div class='form-group form-floating'>
                <input name='city' type='text' placeholder='Stadt'
                       value="<?= get_post_field('city') ?>"
                       id='city'
                       class='form-control'
                       required>
                <label for='city'>
                    Stadt
                </label>
            </div>
        </div>

        <div class='col-sm-6'>
            <div class='form-group form-floating'>
                <?php
                $countries = ['Deutschland', 'Schweiz', 'Österreich'];
                $selected_country = get_post_field('country')
                ?>
                <select name="country" class="form-select" id="country" autocomplete="on"
                        required>
                    <?php
                    foreach ($countries as $country) {
                        ?>
                        <option <?= $country === $selected_country ? 'selected' : '' ?>><?= $country ?></option>
                        <?php
                    }
                    ?>
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
                $days = $config->get_formatted_days();
                $selected_date = get_post_field('date');
                ?>
                <select name="date" class="form-select" id="date" autocomplete="on"
                        required>
                    <?php
                    foreach ($days as $day) {
                        ?>
                        <option class="day"
                                value="<?= $day[1] ?>" <?= $day[1] === $selected_date ? 'selected' : '' ?>>
                            <?= $day[0] ?>
                        </option>
                        <?php
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
                <!-- select time slots -->
                <?php
                $selected_timeslots = get_post_fields('timeslots');
                foreach ($config->get_formatted_timeslots() as $index => $timeslot) { ?>
                    <span class="form-check-label">
                                                <input type="checkbox" name="timeslots[]"
                                                       value="<?= $index ?>"
                                                       <?= in_array((string)($index), $selected_timeslots, TRUE) ? 'checked' : '' ?>
                                                       class="form-check-input timeslot">
                                                <?= $timeslot ?>
                                            </span>
                    <br>
                    <?php
                } ?>
                <div class="row mt-2">
                    <p>
                        <?php
                        foreach ($config->get_prices() as $price) {
                            echo $price['description'] . ': <strong>' . $price['value'] . '€</strong>';
                            ?>
                            <br>
                            <?php
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row my-2">
            <?php
            foreach ($available_kajaks as $kajak) {
                ?>
                <div class="col-md-6">
                    <div class="form-group form-floating">
                        <input type="number" max="<?= $kajak->amount ?? 0 ?>"
                               min="0" id="<?= $kajak->kind ?>"
                               value="<?= get_post_field($kajak->kind, 0) ?>"
                               name="<?= $kajak->kind ?>"
                               class="amount-kajak form-control"/>
                        <label class="form-check-label" for="<?= $kajak->kind ?>">
                            Anzahl der <?= $kajak->name ?>s
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
                           required <?= get_post_field('is_studi') === '1' ? 'checked' : '' ?>
                           class="form-check-input">
                    Hiermit bestätige ich, dass ich Angehörige*r an der HTWG
                    Konstanz oder der Universität Konstanz bin.
                </label>
            </div>
            <div class="col-md-6">
                <label>
                    <input type="checkbox" name="terms" value="1"
                           required <?= get_post_field('terms') === '1' ? 'checked' : '' ?>
                           class="form-check-input">
                    Ich habe
                    die <a href="/terms" class="text-danger"
                           target="_blank"><strong>Nutzungsbedingungen</strong></a>
                    und
                    die <a href="/privacy" class="text-danger" target="_blank"><strong>Datenschutzerklärung</strong></a>
                    gelesen
                    und akzeptiere sie.
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
                <button type="submit" class="btn custom-btn">Jetzt buchen</button>
            </div>
        </div>
    </div>
</form>
<script>
    /* script to calculate the price */
    /* don't event think about modifying this script to lower the price, it won't work :) */
    const calculated_price_element = document.getElementById('calculated-price');
    const calculate_price = () => {
        /* get necessary fields and information */
        const amount_kajaks_by_kind = Array.from(document.getElementsByClassName('amount-kajak')).reduce((carry, kajak) => ([
            ...carry,
            {
                kind: kajak.name,
                amount: parseInt(kajak.value)
            }
        ]), []);
        const timeslots = Array.from(document.getElementsByClassName('timeslot')).map(timeslot => timeslot.checked);
        /* encode everything and send some magic :) */
        const encoded = btoa(JSON.stringify({
            amount_kajaks: amount_kajaks_by_kind,
            "<?= $_SESSION['token_field'] ?>": "<?= $_SESSION['token'] ?>",
            timeslots
        }))

        const XMLHttpReq = new XMLHttpRequest();
        XMLHttpReq.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                calculated_price_element.innerHTML = '<strong>Bitte bringe ' + this.responseText + ' in Bar mit.</strong>';
            }
        };
        /* send request to own api to calculate price */
        XMLHttpReq.open('GET', '/api?price&payload_price=' + encoded, true);
        XMLHttpReq.send();
    }

    Array.from(document.getElementsByClassName('amount-kajak')).forEach(kajak => kajak.addEventListener('change', calculate_price))
    Array.from(document.getElementsByClassName('timeslot')).forEach(timeslot => timeslot.addEventListener('change', calculate_price))
</script>
<script>
    const textInputs = Array.from(document.getElementsByTagName('input'))
    textInputs.forEach(e => {
        if (e.type === 'text' || e.type === 'email') {
            e.addEventListener('blur', () => {
                e.value = e.value.trim();
            })
        }
    })
</script>