<?php
/* Connect to database */
$conn = connect_to_database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (is_logged_in()) {

        if (isset($_POST['delete_items'], $_POST['id'])) {
            $ids = clean_array($_POST['id']);
            archive_reservation($conn, $ids);
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
}

/* Get all reservations from database */
global $config;
$reservations = get_reservations($conn);
$kajaks_by_reservation_id = get_reservated_kajaks_by_id($conn);
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
                           class=" form-control"
                           required>
                    <label for="filter">
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
                                $is_archived = $reservation['archived'] === 1;
                                $is_cancelled = $reservation['cancelled'] === 1;
                                ?>
                                <tr class="reservation reservation-row <?php echo $is_archived || $is_cancelled ? 'archived' : '' ?>">
                                    <td class="text-center">
                                        <?php
                                        if ($is_archived) { ?>
                                            Gelöscht
                                            <?php
                                        } else if ($is_cancelled) { ?>
                                            Storniert
                                            <?php
                                        } else { ?>
                                            <input class="form-check-input" type="checkbox"
                                                   value="<?php echo $reservation['reservation_id'] ?>"
                                                   name="id[]">
                                            <?php
                                        }
                                        ?>
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
                        <button type="submit" class="btn custom-btn mx-1" name="delete_items">Reservierungen stornieren
                        </button>
                        <button type="submit" class="btn custom-btn mx-1" name="drop_all">
                            Tabellen löschen
                        </button>
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
                            <label for="date">
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
                               required>
                        <label for="filter">
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
                                    $is_available = $kajak['available'] === 1;
                                    $kajak_name = $kajak['kajak_name'];
                                    ?>
                                    <tr class="kajak-row">
                                        <td><?php echo $kajak_name ?></td>
                                        <td><?php echo $kajak['kind'] ?></td>
                                        <td><?php echo $kajak['seats'] ?></td>
                                        <td><?php echo $is_available ? 'Ja' : 'Nein' ?></td>
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
                        </div>
                    </form>
                </div>
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
