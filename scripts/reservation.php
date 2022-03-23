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
$timeslots = array("9:00 - 13:00", "13:00 - 18:00");
// kajaks for each kajak type
$amount_kajaks = array("single_kajak" => 4, "double_kajak" => 2);


$weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

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
 * @return bool
 */
function check_if_reservation_available($conn, $date, $timeslot, string $kajak): bool
{
    global $amount_kajaks;

    /* Convert date to DateTime to be able to subtract one second */
    try {
        $timeslot[1] = new DateTime($timeslot[1]);
    } catch (Exception $e) {
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
    return (int)$amount < $amount_kajaks[$kajak];
}

/**
 * Reservate a kajak
 *
 * @param $conn
 * @param $name
 * @param $email
 * @param $phone
 * @param $date
 * @param $timeslot
 * @param $kajaks
 * @return false|mixed
 */
function insert_reservation($conn, $name, $email, $phone, $date, $timeslot, $kajaks): mixed
{
    /* Check if reservation is available for both kajaks */
    if (!check_if_reservation_available($conn, $date, $timeslot, "single_kajak") || !check_if_reservation_available($conn, $date, $timeslot, "double_kajak")) {
        return false;
    }

    $sql = "INSERT INTO reservations (name, email, phone, date, from_time, to_time, single_kajak, double_kajak)
    VALUES ('$name', '$email', '$phone', '$date', '$timeslot[0]', '$timeslot[1]', '$kajaks[0]', '$kajaks[1]')";

    // TODO: replace with prepared statement
    return $conn->query($sql);
}

/**
 * Main function to reservate a kajak
 *
 * @param $conn
 * @param $fields
 * @return false|mixed
 */
function reservate_kajak($conn, $fields): mixed
{
    global $timeslots;

    // TODO: Refactor this
    // THATS REALLY UGLY CODE :(
    $_POST_name = clean_string($fields['name']);
    $_POST_email = clean_string($fields['email']);
    $_POST_phone = clean_string($fields['phone']);
    $_POST_message = clean_string($fields['date']);
    $_POST_timeslots = clean_array($fields['timeslots']);
    if (count($_POST_timeslots) === 1) {
        if ($_POST_timeslots[0] === $timeslots[0]) {
            $_POST_timeslots = array("9:00:00", "13:00:00");
        } else if ($_POST_timeslots[0] === $timeslots[1]) {
            $_POST_timeslots = array("13:00:00", "18:00:00");
        }
    } else if (count($_POST_timeslots) === 2) {
        $_POST_timeslots = array("9:00:00", "18:00:00");
    }

    $_POST_single_kajak = clean_string($_POST['single-kajak']);
    $_POST_double_kajak = clean_string($_POST['double-kajak']);
    $_POST_kajaks = array((int)$_POST_single_kajak, (int)$_POST_double_kajak);

    return insert_reservation($conn, $_POST_name, $_POST_email, $_POST_phone, $_POST_message, $_POST_timeslots, $_POST_kajaks);
}