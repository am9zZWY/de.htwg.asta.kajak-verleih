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
    <style>
        div {
            border-radius: 5px;
            background-color: #f2f2f2;
            padding: 20px;
        }
    </style>
    <link rel="stylesheet" href="KajakStyle.css">
    <link rel="stylesheet" href="resources/bootstrap.min.css">
    <title>Kajak Verleihsystem von Coolen Typen</title>
</head>
<body>
<h1>
    Kajak Verleihsystem
</h1>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_string($_POST['name']);
    $email = clean_string($_POST['email']);
    $mobile = clean_string($_POST['mobile']);
    $date = clean_string($_POST['date']);
    $timeslots = clean_array($_POST['timeslots']);

    echo $date;
    var_dump($timeslots);
} else {
    // configs
    // all weekdays in german
    $weekdays = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
    // start date is today
    $date = date_create();
    // max days for calendar
    $max_days = 14;
    // timeslots
    $timeslots = array("9:00 - 13:00", "13:00 - 18:00");
    ?>
    <div>
        <form action="index.php" method="post">
            <!-- select dates -->
            <label for="date">Startdatum:</label>
            <select id="date" name="date">
                <?php for ($day = 0; $day < $max_days; $day++) { ?>
                    <option value="<?php echo $date->format('d-m-Y') ?>">
                        <?php echo $date->format('d-m-Y') ?>
                    </option>
                    <?php date_add($date, new DateInterval('P1D')); ?>
                <?php } ?>
            </select>

            <!-- time slots -->
            <?php foreach ($timeslots as $timeslot) { ?>
                <input type="checkbox" id="<?php echo "time_" . $timeslot ?>" name="timeslots[]" value="<?php echo $timeslot ?>">
                <label for="<?php echo "time_" . $timeslot ?>"><?php echo $timeslot ?></label>
            <?php } ?>

            <input type="submit" value="Anfrage senden"/>
        </form>
    </div>
<?php } ?>
</html>
