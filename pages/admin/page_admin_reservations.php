<?php
/* Connect to database */
$conn = connect_to_database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_items'], $_POST['id'])) {
        $ids = clean_array($_POST['id']);
        archive_reservation($conn, $ids);
    } else if (isset($_POST['delete_all'])) {
        drop_table($conn);
    }
}

/* Get all reservations from database */
$reservations = get_reservations($conn);

echo create_header('Reservierungen');
?>
<div class="container">
    <div class="row">
        <form method="post" class="needs-validation">
            <table class="table table-striped table-bordered table-sm table-light">
                <caption>Übersicht aller Reservierungen</caption>
                <tr>
                    <th>Löschen</th>
                    <th>Reservierungs ID</th>
                    <th>Name</th>
                    <th>Adresse</th>
                    <th>Handynummer</th>
                    <th>E-Mail Adresse</th>
                    <th>Datum</th>
                    <th>Zeitslot</th>
                    <th>Einzel Kajaks</th>
                    <th>Doppel Kajaks</th>
                </tr>

                <?php
                foreach ($reservations as $reservation) {
                    $is_archived = $reservation['archived'] === 1;
                    ?>
                    <tr class="reservation <?php echo $is_archived ? 'archived' : '' ?>">
                        <td class="text-center">
                            <?php
                            if (!$is_archived) {
                                ?>
                                <input class="form-check-input" type="checkbox"
                                       value="<?php echo $reservation['id'] ?>"
                                       name="id[]">
                            <?php } else { ?>
                                Gelöscht
                                <?php
                            }
                            ?>
                        </td>
                        <td><?php echo $reservation['id'] ?></td>
                        <td><?php echo $reservation['name'] ?></td>
                        <td><?php echo $reservation['address'] ?></td>
                        <td><?php echo $reservation['phone'] ?></td>
                        <td><?php echo $reservation['email'] ?></td>
                        <td><?php echo $reservation['date'] ?></td>
                        <td><?php echo $reservation['from_time'] . '—' . $reservation['to_time'] ?></td>
                        <td><?php echo $reservation['single_kajak'] ?></td>
                        <td><?php echo $reservation['double_kajak'] ?></td
                    </tr>
                    <?php
                }
                ?>
            </table>
            <div class="btn-group d-flex" role="group">
                <button type="submit" class="btn custom-btn mx-1" name="delete_items">Elemente löschen
                </button>
                <button type="submit" class="btn custom-btn mx-1" name="delete_all"> Tabelle löschen
                </button>
            </div>
        </form>
    </div>
</div>
