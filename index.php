<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/scripts/database.php';
?>

<html lang="de">
<html xmlns="http://www.w3.org/1999/html">
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
// all weekdays in german
$weekdays = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");

// start date is today
$date = date_create();

$max_days = 14;
?>
<div>
    <form action="index.php">
        <!-- select dates -->
        <label for="start_date">Startdatum:</label>
        <select id="start_date">
            <?php for ($day = 0; $day < $max_days; $day++) { ?>
                <option value="<?php echo $day ?>">
                    <?php echo $date->format('d-m-Y') ?>
                </option>
                <?php date_add($date, new DateInterval('P1D')); ?>
            <?php } ?>
        </select>

        <!-- time slot-->
        <label for="time">Zeitraum:</label>
        <input type="checkbox" id="time">9:00 - 13:00</input>
        <input type="checkbox" id="time">13:15 - 18:00</input>

        <input type="submit" value="Anfrage senden"/>
        <?php
        connect_to_database();
        ?>
    </form>
</div>
</html>