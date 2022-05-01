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
    } else if (isset($_POST['delete_kajak'])) {
        $name = clean_string($_POST['name']);
        remove_kajak($conn, $name);
    } else if (isset($_POST['add_kajak'])) {
        $name = clean_string($_POST['name']);
        $kind = clean_string($_POST['kind']);
        $seats = (int)clean_string($_POST['seats']);
        add_kajak($conn, $name, $kind, $seats);
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
            <form method="post" class="needs-validation">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="mb-3 form-floating">
                            <input name="name" type="text" placeholder="Max"
                                   value="<?php echo get_post_field('name') ?>"
                                   id="name"
                                   class="form-control"
                                   required>
                            <label for="name">
                                Kajak-Name
                            </label>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group form-floating">
                            <select name="kind" class="form-select" id="kind" autocomplete="on"
                                    required>
                                <?php
                                foreach ($kajak_kinds as $kajak_kind) {
                                    ?>
                                    <option value="<?php echo $kajak_kind ?>">
                                        <?php echo $kajak_kind ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <label for="kind">
                                Typ
                            </label>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="mb-3 form-floating">
                            <input type="number"
                                   min="0" id="seats"
                                   value="<?php echo get_post_field('seats', 0) ?>"
                                   name="seats"
                                   class="amount-kajak form-control"/>
                            <label class="form-check-label" for="seats">
                                Anzahl der Sitze
                            </label>
                        </div>
                    </div>
                </div>

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
                    <form method="post" class="needs-validation">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm table-light" id="reservations">
                                <caption>Auflistung aller Kajaks</caption>
                                <tr>
                                    <th>Name</th>
                                    <th>Typ</th>
                                    <th>Anzahl der Sitze</th>
                                    <th>Verfügbar</th>
                                    <th>Kommentar</th>
                                </tr>
                                <?php
                                foreach ($kajaks as $kajak) {
                                    $is_unavailable = $kajak['available'] === 0;
                                    $kajak_name = $kajak['kajak_name'];
                                    ?>
                                    <tr class="kajak kajak-row <?php echo $is_unavailable ? 'unavailable' : '' ?>">
                                        <td><?php echo $kajak_name ?></td>
                                        <td><?php echo $kajak['kind'] ?></td>
                                        <td><?php echo $kajak['seats'] ?></td>
                                        <td><?php echo $is_unavailable ? 'Nein' : 'Ja' ?></td>
                                        <td><?php echo $kajak['comment'] ?></td>
                                    </tr>
                                    <?php
                                } ?>
                            </table>
                        </div>

                        <div class="btn-group d-flex" role="group">
                            <button type="submit" class="btn custom-btn mx-1" name="delete_kajak">Kajak löschen
                            </button>
                            <button type="submit" class="btn custom-btn mx-1" name="add_kajak">Kajak hinzufügen
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
            </form>
        </div>
        <script>
            const reservationFilter = document.getElementById('reservation-filter')
            const kajakFilter = document.getElementById('kajak-filter')

            const filter = (inputElement, elements) => {
                return Array.from(elements).forEach((row) => {
                    if (row.textContent.toLowerCase().includes(inputElement.value.toLowerCase())) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                })
            }

            function filterReservationTable() {
                const reservations = document.getElementsByClassName('reservation-row')
                filter(reservationFilter, reservations);
            }

            function filterKajakTable() {
                const kajaks = document.getElementsByClassName('kajak-row')
                filter(kajakFilter, kajaks);
            }
        </script>
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
        <div class="col content">
            <div class="content-wrapper">
                <h4>Datenbank Status</h4>

            </div>
        </div>
    </div>
</div>
