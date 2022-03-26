<?php
/* Connect to database */
$conn = connect_to_database();

/* Get all reservations from database */
$reservations = get_reservations($conn);

create_header('Reservierungen');
?>
<div class="container">
    <div class="row">
        <form method="post" class="needs-validation">
            <table class="table table-striped table-bordered table-sm table-light">
                <tr>
                    <th>Löschen</th>
                    <th>Reservierungs ID</th>
                    <th>Name</th>
                    <!-- <th>Nachname</th> -->
                    <!-- <th>Adresse</th> -->
                    <th>Handynummer</th>
                    <th>E-Mail Adresse</th>
                    <th>Datum</th>
                    <th>Zeitslot</th>
                    <th>Einzel Kajaks</th>
                    <th>Doppel Kajaks</th>
                </tr>

                <?php
                foreach ($reservations as $reservation) {
                    ?>
                    <tr>
                        <td class="text-center">
                            <input class="form-check-input" type="checkbox"
                                   value="<?php echo $reservation['id'] ?>"
                                   name="id[]">
                        </td>
                        <td><?php echo $reservation['id'] ?></td>
                        <td><?php echo $reservation['name'] ?></td>
                        <!-- <td><?php echo $reservation['addresse'] ?></td> -->
                        <td><?php echo $reservation['phone'] ?></td>
                        <td><?php echo $reservation['email'] ?></td>
                        <td><?php echo $reservation['date'] ?></td>
                        <td>Ganzen Tag</td>
                        <td><?php echo $reservation['single_kajak'] ?></td>
                        <td><?php echo $reservation['double_kajak'] ?></td
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
            if (count($reservations) > 0) {
                ?>
                <button type="submit" class="btn btn-primary submit-btn">Löschen</button>
                <?php
            } else {
                ?>
                <h3>Keine Reservierungen vorhanden</h3>
                <?php
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ids = clean_array($_POST['id']);
                delete_reservation($conn, $ids);
                // TODO: add reload
            }
            ?>
        </form>
    </div>
</div>
