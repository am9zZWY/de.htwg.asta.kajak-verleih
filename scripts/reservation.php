<?php
// CONFIG

// config for the database
$servername = "mysql-test-service:3306";
$username = "user";
$password = "password";
$dbname = "db";

// all weekdays in german
$newLocal = setlocale(LC_ALL, 'de_DE', 'de_DE.UTF-8');
// add two days to start date
$min_day = 3;
// max days for calendar
$max_days = 14;
// timeslots
$timeslots = array(array("09:00:00", "13:00:00"), array("13:00:00", "18:00:00"));
// converts timeslots to e.g. "09:00 - 13:00"
$timeslots_formatted = array_map(static function ($array) {
    $timeslot = array_map(static function ($time) {
        return date('H:i', strtotime($time));
    }, $array);
    return implode(' - ', $timeslot);
}, $timeslots);
// kajaks for each kajak type
$amount_kajaks = array("single_kajak" => 4, "double_kajak" => 2);
// weekdays in german
$weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

// Error messages
$ERROR_TIMESLOT_NOT_SELECTED = "Bitte wähle eine Zeit aus.";
$ERROR_KAJAK_TYPE_NOT_FOUND = "Kajaktyp nicht gefunden";
$ERROR_SINGLE_KAJAK_NOT_AVAILABLE = "Einzelkajak nicht verfügbar";
$ERROR_DOUBLE_KAJAK_NOT_AVAILABLE = "Doppelkajak nicht verfügbar";
$ERROR_KAJAK_NOT_AVAILABLE = "Kajaks nicht verfügbar";
$ERROR_KAJAK_NOT_SELECTED = "Bitte wähle einen Kajak aus.";
$ERROR_GENERAL = "Ein Fehler ist aufgetreten.";

/**
 * Returns the next max_days weekdays in a string
 * @return array<string>
 */

function get_days(): array
{
    global $min_day, $max_days, $weekdays;

    /* Create starting date */
    $date = date_create();
    date_add($date, new DateInterval("P${min_day}D"));

    $days = array();
    for ($i = 0; $i < $max_days; $i++) {
        $weekday = (int)$date->format('w');
        if ($weekday !== 0 && $weekday !== 6) {
            $days[$i] = array($weekdays[$weekday] . ' ' . $date->format('d.m.Y'), $date->format('Y-m-d'));
        }
        date_add($date, new DateInterval("P1D"));
    }
    return $days;
}

/**
 * Create connection to mysql database
 * Returns connection object if successful
 *
 * @return mysqli|void
 */
function connect_to_database()
{
    global $servername, $username, $password, $dbname;

    /* Create connection */
    $conn = new mysqli($servername, $username, $password, $dbname);
    /* Check connection */
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $_SESSION['conn_db'] = $conn;

    return $conn;
}

/**
 * Creates the reservation table if it doesn't exist.
 *
 * @param $conn
 * @return void
 */
function prepare_reservation_table($conn)
{
    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS reservations
(
    id           INT(6) ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(30)     NOT NULL,
    email        VARCHAR(50)     NOT NULL,
    phone        VARCHAR(20)     NOT NULL,
    date         DATE            NOT NULL,
    from_time    TIME            NOT NULL,
    to_time      TIME            NOT NULL,
    single_kajak NUMERIC         NOT NULL,
    double_kajak NUMERIC         NOT NULL,
    CONSTRAINT NAME_CHECK CHECK (REGEXP_LIKE(name, '^[A-Za-z ]+'))
)");
    $sql->execute();
}

function get_reservations($conn): array
{
    $sql = $conn->prepare("Select * From reservations WHERE date >=current_Date() Order BY Date ASC;");
    $sql->execute();
    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * USE WITH CAUTION!
 *
 * Drops the reservation table.
 *
 * @param $conn
 * @return void
 */
function drop_table($conn)
{
    $sql = $conn->prepare("DROP TABLE reservations");
    $sql->execute();
}

/**
 * Returns the amount of kajaks of a kajak type
 * @param $conn
 * @param $date
 * @param $timeslot
 * @param string $kajak
 * @param int $requested_amount
 * @return bool
 */
function check_if_kajak_available($conn, $date, $timeslot, string $kajak, int $requested_amount): bool
{
    global $amount_kajaks;

    if (!array_key_exists($kajak, $amount_kajaks) || ($requested_amount !== -1 && $requested_amount > $amount_kajaks[$kajak])) {
        return false;
    }

    if ($requested_amount === 0) {
        return true;
    }

    /* Convert date to DateTime to be able to subtract one second */
    try {
        $timeslot[1] = new DateTime($timeslot[1]);
    } catch (Exception) {
        return false;
    }

    /* This is important to exclude the current time from the next timeslot */
    $timeslot[1]->modify("-1 second");
    $timeslot[1] = $timeslot[1]->format("H:i:s");

    $timeslots = array((string)$timeslot[0], $timeslot[1]);

    /* Prepare statement */
    $sql = $conn->prepare("
        SELECT SUM($kajak) as amount FROM reservations
        WHERE date = ?
          AND reservations.from_time BETWEEN ? AND ?
          OR reservations.to_time BETWEEN ? AND ?
    ");
    $sql->bind_param('sssss', $date, $timeslots[0], $timeslots[1], $timeslots[0], $timeslots[1]);

    $sql->execute();
    $result = $sql->get_result();

    /* Check if there are more than 0 kajaks available */
    $amount = $result->fetch_assoc()["amount"];

    /* If null then no reservation on that day is found; therefore it's free */
    if ($amount === null) {
        return true;
    }

    return (int)$amount + $requested_amount < $amount_kajaks[$kajak];
}

/**
 * Insert reservation into database.
 *
 * @param $conn
 * @param $name
 * @param $email
 * @param $phone
 * @param $date
 * @param $timeslot
 * @param $kajaks
 * @return bool
 */
function insert_reservation($conn, $name, $email, $phone, $date, $timeslot, $kajaks): bool
{
    $sql = $conn->prepare("INSERT INTO reservations (name, email, phone, date, from_time, to_time, single_kajak, double_kajak)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param('ssssssss', $name, $email, $phone, $date, $timeslot[0], $timeslot[1], $kajaks[0], $kajaks[1]);
    return $sql->execute();
}

/**
 * Delete reservations by id.
 *
 * @param $conn
 * @param $ids
 * @return void
 */
function delete_reservation($conn, $ids)
{
    $sql = "DELETE FROM reservations WHERE id IN(".implode(',', $ids).")";
    $conn->query($sql);
}

/**
 * Main function to reservate a kajak
 *
 * @param $conn
 * @param $fields
 * @return true | string
 */
function reservate_kajak($conn, $fields): bool|string
{
    global $timeslots;
    global $ERROR_GENERAL, $ERROR_KAJAK_NOT_AVAILABLE, $ERROR_SINGLE_KAJAK_NOT_AVAILABLE, $ERROR_DOUBLE_KAJAK_NOT_AVAILABLE, $ERROR_KAJAK_NOT_SELECTED, $ERROR_TIMESLOT_NOT_SELECTED;

    $name = clean_string($fields['name']);
    $email = clean_string($fields['email']);
    $phone = clean_string($fields['phone']);
    $date = clean_string($fields['date']);

    $timeslot = clean_array($fields['timeslots'] ?? []);

    /* Check if timeslot is selected */
    if (count($timeslot) === 0) {
        return $ERROR_TIMESLOT_NOT_SELECTED;
    }

    /* Prepare timeslot */
    $min_time_index = $timeslot[0];
    $max_time_index = end($timeslot);
    $min_time = $timeslots[$min_time_index][0];
    $max_time = $timeslots[$max_time_index][1];
    $timeslot = array($min_time, $max_time);

    $amount_single_kajak = (int)clean_string($_POST['single-kajak']);
    $amount_double_kajak = (int)clean_string($_POST['double-kajak']);
    $amount_kajaks = array($amount_single_kajak, $amount_double_kajak);

    $single_kajak_available = check_if_kajak_available($conn, $date, $timeslot, "single_kajak", $amount_single_kajak);
    $double_kajak_available = check_if_kajak_available($conn, $date, $timeslot, "double_kajak", $amount_double_kajak);

    if (!$single_kajak_available && !$double_kajak_available) {
        /* Check if kajaks are available */
        return $ERROR_KAJAK_NOT_AVAILABLE;
    }

    if (!$single_kajak_available) {
        /* Check if reservation is available for single kajaks */
        return $ERROR_SINGLE_KAJAK_NOT_AVAILABLE;
    }

    if (!$double_kajak_available) {
        /* Check if reservation is available for double kajaks */
        return $ERROR_DOUBLE_KAJAK_NOT_AVAILABLE;
    }

    if ($amount_single_kajak === 0 && $amount_double_kajak === 0) {
        /* Check if any kajak is selected */
        return $ERROR_KAJAK_NOT_SELECTED;
    }

    if (insert_reservation($conn, $name, $email, $phone, $date, $timeslot, $amount_kajaks) === false) {
        return $ERROR_GENERAL;
    }
    return true;
}