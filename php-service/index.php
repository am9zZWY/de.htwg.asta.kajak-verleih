<html lang="de">
<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" href="KajakStyle.css">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        div {
            border-radius: 5px;
            background-color: #f2f2f2;
            padding: 20px;
        }
    </style>
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
    </form>
</div>
</html>