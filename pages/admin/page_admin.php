<?php
/* Connect to database */
$conn = connect_to_database();

if (($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['confirm']) && is_logged_in() && clean_string($_POST['confirm']) === '1') {
    if (isset($_POST['archive_items'], $_POST['id'])) {
        $ids = clean_array($_POST['id']);
        cancel_reservations($conn, $ids);
    } else if (isset($_POST['recover_items'], $_POST['id'])) {
        $ids = clean_array($_POST['id']);
        recover_reservations($conn, $ids);
    } else if (isset($_POST['drop_all'])) {
        drop_all_tables($conn);
    } else if (isset($_POST['remove_kajak'])) {
        $name = clean_string($_POST['kajak_name']);
        remove_kajak($conn, $name);
    } else if (isset($_POST['update_kajak']) || isset($_POST['add_kajak'])) {
        $name = clean_string($_POST['kajak_name']);
        $old_name = clean_string($_POST['kajak_old_name']) ?? $name;
        $kind = clean_string($_POST['kajak_kind']);
        $seats = (int)clean_string($_POST['kajak_seats']);
        $available = (int)clean_string($_POST['kajak_available']);
        $comment = clean_string($_POST['kajak_comment']);
        if (isset($_POST['update_kajak'])) {
            update_kajak($conn, $old_name, $name, $kind, $seats, $available, $comment);
        } else {
            add_kajak($conn, $name, $kind, $seats);
        }
    } else if (isset($_POST['remove_bad_person'])) {
        $name = clean_string($_POST['name']);
        $email = clean_string($_POST['email']);
        remove_bad_person($conn, $name, $email);
    } else if (isset($_POST['update_bad_person']) || isset($_POST['add_bad_person'])) {
        $name = clean_string($_POST['name']);
        $old_name = clean_string($_POST['old_name']) ?? $name;
        $email = clean_string($_POST['email']);
        $old_email = clean_string($_POST['old_email']) ?? $email;
        $comment = clean_string($_POST['comment']);
        if (isset($_POST['update_bad_person'])) {
            update_bad_person($conn, $name, $email, $comment, $old_name, $old_email);
        } else {
            add_bad_person($conn, $name, $email, $comment);
        }
    }
}

/* Get all reservations from database */
global $config;
$reservations = get_reservations($conn);
$kajaks_by_reservation_id = get_reserved_kajaks_by_id($conn);
$kajak_kinds = $config->getKajakKinds();
$kajaks = get_kajaks($conn);

echo create_header('Dashboard');
?>
<div class="container my-2">
    <div class="row content">
        <div class="content-wrapper">
            <h4>Reservierungen</h4>
            <div class="col">
                <div class="mb-3 form-floating">
                    <input id="reservation-filter"
                           name="filter" type="text" placeholder="bsp. Reservierungsnummer"
                           onkeyup="filterReservationTable()"
                           class=" form-control">
                    <label for="reservation-filter">
                        Filter
                    </label>
                </div>
            </div>
            <div class="col">
                <form method="post" class="needs-validation">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm table-light" id="reservations">
                            <caption>Auflistung aller Reservierungen</caption>
                            <tr>
                                <th>Löschen</th>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Adresse</th>
                                <th>Handynummer</th>
                                <th>E-Mail Adresse</th>
                                <th>Datum</th>
                                <th>Zeitslot</th>
                                <th>Kajaks</th>
                                <th>Preis</th>
                            </tr>

                            <?php
                            foreach ($reservations as $reservation) {
                                $is_cancelled = $reservation['cancelled'] === 1;
                                ?>
                                <tr class="reservation reservation-row <?php echo $is_cancelled ? 'cancelled' : '' ?>">
                                    <td class="text-center">
                                        <?php
                                        if ($is_cancelled) { ?>
                                            Storniert
                                            <?php
                                        } ?>
                                        <input class="form-check-input" type="checkbox"
                                               id="reservation-checkbox-<?php echo $reservation['reservation_id'] ?>"
                                               value="<?php echo $reservation['reservation_id'] ?>"
                                               name="id[]">

                                    </td>
                                    <td><?php echo $reservation['reservation_id'] ?></td>
                                    <td><?php echo $reservation['name'] ?></td>
                                    <td><?php echo $reservation['address'] ?></td>
                                    <td><?php echo $reservation['phone'] ?></td>
                                    <td><?php echo $reservation['email'] ?></td>
                                    <td><?php echo date_create($reservation['date'])->format('d.m.Y') ?></td>
                                    <td><?php echo $reservation['from_time'] . '–' . $reservation['to_time'] ?></td>
                                    <td><?php echo implode(', ', $kajaks_by_reservation_id[$reservation['reservation_id']] ?? []) ?></td>
                                    <td><?php echo $reservation['price'] ?>€</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </div>
                    <div class="btn-group d-flex" role="group">
                        <button type="submit" class="btn custom-btn mx-1" name="archive_items">Reservierungen stornieren
                        </button>
                        <button type="submit" class="btn custom-btn mx-1" name="recover_items">Reservierungen
                            wiederherstellen
                        </button>
                        <button type="submit" class="btn custom-btn mx-1" name="drop_all">
                            Tabellen löschen
                        </button>
                        <div class="mx-1">
                            <input type="checkbox" name="confirm" value="1" id="confirm"
                                   class="form-check-input">
                            <label for="confirm">
                                Bestätigen
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row content">
        <div class="content-wrapper">
            <h4>Kajaks verwalten</h4>
            <div class="col">
                <div class="mb-3 form-floating">
                    <input id="kajak-filter"
                           name="filter" type="text" placeholder="bsp. Kajakname"
                           onkeyup="filterKajakTable()"
                           class="form-control"
                    >
                    <label for="kajak-filter">
                        Filter
                    </label>
                </div>
            </div>
            <div class="col">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm table-light" id="reservations">
                        <caption>Auflistung aller Kajaks</caption>
                        <tr>
                            <th>Name</th>
                            <th>Typ</th>
                            <th>Anzahl der Sitze</th>
                            <th>Verfügbar</th>
                            <th>Kommentar</th>
                            <th>Aktionen</th>
                        </tr>
                        <?php
                        $seats_per_kajak = $config->getSeatsPerKajak();
                        foreach ($kajaks as $kajak) {
                            $is_unavailable = $kajak['available'] === 0;
                            $kajak_name = $kajak['kajak_name'];
                            ?>
                            <tr class="kajak kajak-row <?php echo $is_unavailable ? 'unavailable' : '' ?>">
                                <form method="post">
                                    <input type="hidden" value="<?php echo $kajak_name ?>"
                                           name="kajak_old_name"/>
                                    <input type="hidden" name="confirm" value="1">
                                    <td>
                                        <input class="form-control" name="kajak_name" type="text"
                                               value="<?php echo $kajak_name ?>"/>
                                    </td>
                                    <td>
                                        <select autocomplete="on" class="form-select" class="form-select" id="kind"
                                                name="kajak_kind">
                                            <?php
                                            foreach ($kajak_kinds as $kajak_kind) {
                                                ?>
                                                <option value="<?php echo $kajak_kind ?>"
                                                    <?php if ($kajak['kind'] === $kajak_kind) {
                                                        echo 'selected';
                                                    } ?>>
                                                    <?php echo $kajak_kind ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" min="1"
                                               name="kajak_seats"
                                               type="number"
                                               value="<?php echo $kajak['seats'] ?>"/>
                                    </td>
                                    <td>
                                        <select name="kajak_available" class="form-select">
                                            <option value="1" <?php if (!$is_unavailable) {
                                                echo 'selected';
                                            } ?>>
                                                Ja
                                            </option>
                                            <option value="0" <?php if ($is_unavailable) {
                                                echo 'selected';
                                            } ?>>
                                                Nein
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" name="kajak_comment" type="text"
                                               value="<?php echo $kajak['comment'] ?>"/>
                                    </td>
                                    <td>
                                        <div class="btn-group d-flex">
                                            <button type="submit" class="btn custom-btn mx-1" name="update_kajak">
                                                Aktualisieren
                                            </button>
                                            <button type="submit" class="btn custom-btn danger mx-1"
                                                    name="remove_kajak">
                                                Löschen
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                            <?php
                        } ?>
                        <tr class="kajak kajak-row">
                            <form method="post" class="needs-validation">
                                <input type="hidden" name="confirm" value="1">
                                <td>
                                    <input class="form-control" type="text" name="kajak_name" required/>
                                </td>
                                <td>
                                    <select class="form-select" name="kajak_kind" class="form-select" id="kind"
                                            autocomplete="on"
                                            required>
                                        <?php
                                        foreach ($kajak_kinds as $kajak_kind) {
                                            ?>
                                            <option value="<?php echo $kajak_kind ?>">
                                                <?php echo $kajak_kind ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="number" name="kajak_seats" min="1" value="1"
                                           required/>
                                </td>
                                <td>
                                    <select class="form-select" required>
                                        <option selected>Ja</option>
                                        <option>Nein</option>
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="kajak_comment"/>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="submit" class="btn custom-btn mx-1" name="add_kajak">
                                            Hinzufügen
                                        </button>
                                    </div>
                                </td>
                            </form>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row content">
        <div class="col">
            <div class="content-wrapper">
                <h4>Blacklist</h4>
                <div class="col">
                    <div class="mb-3 form-floating">
                        <input id="blacklist-filter"
                               name="filter" type="text" placeholder="bsp. Marcel Müller"
                               onkeyup="filterBlacklistTable()"
                               class="form-control"
                        >
                        <label for="blacklist-filter">
                            Filter
                        </label>
                    </div>
                </div>
                <div class="col">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm table-light" id="reservations">
                            <caption>Auflistung aller gesperrten Personen</caption>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Kommentar</th>
                                <th>Aktionen</th>
                            </tr>
                            <?php
                            $blacklist = get_blacklist($conn);
                            foreach ($blacklist as $person) {
                                $name = $person['name'];
                                $email = $person['email'];
                                ?>
                                <tr class="blacklist blacklist-row">
                                    <form method="post">
                                        <input type="hidden" value="<?php echo $name ?>"
                                               name="old_name"/>
                                        <input type="hidden" value="<?php echo $email ?>"
                                               name="old_email"/>
                                        <input type="hidden" name="confirm" value="1">
                                        <td>
                                            <input class="form-control" name="name" type="text"
                                                   value="<?php echo $name ?>"/>
                                        </td>
                                        <td>
                                            <input class="form-control" name="email" type="email"
                                                   value="<?php echo $email ?>"/>
                                        </td>
                                        <td>
                                            <input class="form-control" name="comment" type="text"
                                                   value="<?php echo $person['comment'] ?>"/>
                                        </td>
                                        <td>
                                            <div class="btn-group d-flex">
                                                <button type="submit" class="btn custom-btn mx-1"
                                                        name="update_bad_person">
                                                    Aktualisieren
                                                </button>
                                                <button type="submit" class="btn custom-btn danger mx-1"
                                                        name="remove_bad_person">
                                                    Löschen
                                                </button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                                <?php
                            } ?>
                            <tr class="blacklist blacklist-row">
                                <form method="post" class="needs-validation">
                                    <input type="hidden" name="confirm" value="1">
                                    <td>
                                        <input class="form-control" name="name" type="text">
                                    </td>
                                    <td>
                                        <input class="form-control" name="email" type="email">
                                    </td>
                                    <td>
                                        <input class="form-control" name="comment" type="text">
                                    </td>
                                    <td>
                                        <div class="btn-group d-flex">
                                            <button type="submit" class="btn custom-btn mx-1"
                                                    name="add_bad_person">
                                                Hinzufügen
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col content">
            <div class="content-wrapper">
                <h4>Aktuelle Konfiguration</h4>
                <ul>
                    <!-- PRICES -->
                    <li>
                        <strong>Preise:</strong>
                        <ul>
                            <?php
                            foreach ($config->getPrices() as $price) {
                                ?>
                                <li>
                                    <?php echo $price->description ?>: <?php echo $price->value ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </li>

                    <!-- KAJAKS -->
                    <li>
                        <strong>Kajaks:</strong>
                        <ul>
                            <?php
                            foreach ($config->getKajaks() as $kajak) {
                                ?>
                                <li>
                                    <?php echo $kajak->name ?>:
                                    <ul>
                                        <li>
                                            Sitze: <?php echo $kajak->seats ?>
                                        </li>
                                        <li>
                                            Anzahl: <?php echo $kajak->amount ?>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>

                    <!-- TIMESLOTS -->
                    <li>
                        <strong>Timeslots:</strong>
                        <ul>
                            <?php
                            foreach ($config->getTimeslots() as $timeslot) {
                                ?>
                                <li>
                                    <?php echo $timeslot->name ?>: <?php echo $config->formatTimeslot($timeslot) ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>

                    <!-- UNSORTIERT -->
                    <li>
                        <strong>Unsortiert:</strong>
                        <ul>
                            <li>
                                <?php $days = $config->getDays(); ?>
                                Reservierungszeitraum: <?php echo $days->min_days . ' - ' . $days->max_days ?> Tage
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <script>
        const reservationFilter = document.getElementById('reservation-filter')
        const kajakFilter = document.getElementById('kajak-filter')
        const blacklistFilter = document.getElementById('blacklist-filter')

        const filter = (inputElement, elements) => {
            return Array.from(elements).forEach((row) => {
                if (row.innerHTML.toLowerCase().includes(inputElement.value.toLowerCase())) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            })
        }

        function filterReservationTable() {
            filter(reservationFilter, document.getElementsByClassName('reservation-row'));
        }

        function filterKajakTable() {
            filter(kajakFilter, document.getElementsByClassName('kajak-row'));
        }

        function filterBlacklistTable() {
            filter(blacklistFilter, document.getElementsByClassName('blacklist-row'));
        }
    </script>
</div>
