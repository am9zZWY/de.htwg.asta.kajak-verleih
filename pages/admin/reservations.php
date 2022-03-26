<?php
session_start();
require __DIR__ . '/../../scripts/reservation.php';
require __DIR__ . '/../../scripts/helpers.php';

include '../../templates/head.php';

/* Connect to database */
$conn = connect_to_database();
?>

<body>
<?php include '../../templates/admin_sidebar.php' ?>

<div class="section" id="booking">
    <div class="section-center">
        <div class="booking-cta">
            <div class="container">
                <h2>Liste mit allen aktuelle Reservierung</h2>
                <form method="post">
                    <table class="table table-striped table-bordered table-sm table-light">
                        <tr>
                            <th>Löschen</th>
                            <th>Reservierungs ID</th>
                            <th>Name</th>
                            <!-- <th>Nachname</th> -->
                            <!-- <th>Adresse</th> -->
                            <th>Handynummer</th>
                            <th>E-mail</th>
                            <th>Datum</th>
                            <th>Zeitslot</th>
                            <th>Einzel Kajaks</th>
                            <th>Doppel Kajaks</th>
                        </tr>

                        <?php
                        /* Get all reservations from database */
                        $reservations = get_reservations($conn);

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
                    <button type="submit">Löschen</button>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $ids = clean_array($_POST['id']);
                        delete_reservation($conn, $ids);
                        // TODO: add reload
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>