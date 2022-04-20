<?php
global $config;
/* kajaks for each kajak type */
$amount_kajaks = $config->getAmountKajaks();

/**
 * Create connection to mysql database.
 * Returns connection object if successful.
 *
 * @return mysqli|null
 */
function connect_to_database(): mysqli|null
{
    global $ERROR_DATABASE_CONNECTION;

    /* credentials to connect to database */
    $servername = get_env('MYSQL_SERVER');
    $username = get_env('MYSQL_USERNAME');
    $password = get_env('MYSQL_PASSWORD');
    $dbname = get_env('MYSQL_DATABASE');

    /* Create connection */
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        /* Check connection */
        if ($conn->connect_error) {
            echo $ERROR_DATABASE_CONNECTION;
            return null;
        }
    } catch (Exception) {
        echo $ERROR_DATABASE_CONNECTION;
        return null;
    }

    return $conn;
}

/**
 * Creates the reservation table if it doesn't exist.
 *
 * @param mysqli|null $conn
 * @return string|bool
 */
function prepare_reservation_table(mysqli|null $conn): string|bool
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS reservations
(
    id               INT(6) ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(30)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    phone            VARCHAR(20)     NOT NULL,
    address          VARCHAR(80)     NOT NULL,
    date             DATE            NOT NULL,
    reservation_date DATE            NOT NULL,
    from_time        TIME            NOT NULL,
    to_time          TIME            NOT NULL,
    single_kajak     NUMERIC         NOT NULL,
    double_kajak     NUMERIC         NOT NULL,
    price            NUMERIC         NOT NULL,
    archived         BOOLEAN         NOT NULL DEFAULT FALSE,
    cancelled        BOOLEAN         NOT NULL DEFAULT FALSE,
    CONSTRAINT NAME_CHECK CHECK (REGEXP_LIKE(name, '^[A-ZäÄöÖüÜßa-z]+ [A-ZäÄöÖüÜßa-z]+$'))
)");

    if ($sql === false) {
        return $ERROR_DATABASE_QUERY;
    }

    return $sql->execute();
}

/**
 * Get all reservations from database.
 *
 * @param mysqli|null $conn
 * @return array<string>
 */
function get_reservations(mysqli|null $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return [];
    }

    $sql = $conn->prepare("Select * From reservations WHERE date >=current_Date() Order BY Date;");
    $sql->execute();
    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * USE WITH CAUTION!
 * USED BY ADMIN.
 *
 * Drops the reservation table.
 *
 * @param mysqli $conn
 * @return void
 */
function drop_table(mysqli $conn)
{
    $sql = $conn->prepare("DROP TABLE reservations");
    $sql->execute();
}

/**
 * Returns the amount of kajaks of a kajak type.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array<string> $timeslot
 * @param string $kajak
 * @param int $requested_amount
 * @return bool
 */
function check_if_kajak_available(mysqli|null $conn, string $date, array $timeslot, string $kajak, int $requested_amount): bool
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return false;
    }

    global $amount_kajaks;

    /* if kajak type is not valid or the requested amount is above the max amount return false */
    if (!array_key_exists($kajak, $amount_kajaks) || ($requested_amount > $amount_kajaks[$kajak])) {
        return false;
    }

    if ($requested_amount === 0) {
        return true;
    }

    /* convert date to DateTime to be able to subtract one second */
    try {
        $timeslot[1] = new DateTime($timeslot[1]);
    } catch (Exception) {
        return false;
    }

    /* this is important to exclude the current time from the next timeslot */
    $timeslot[1]->modify("-1 second");
    $timeslot[1] = $timeslot[1]->format("H:i:s");

    $timeslots = array((string)$timeslot[0], $timeslot[1]);

    /* check the amount of kajaks in the timeslot and where the reservation is not archived */
    $sql = $conn->prepare("
        SELECT SUM($kajak) as amount FROM reservations
        WHERE date = ?
          AND reservations.archived = FALSE
          AND (reservations.from_time BETWEEN ? AND ?
          OR reservations.to_time BETWEEN ? AND ?)
    ");
    $sql->bind_param('sssss', $date, $timeslots[0], $timeslots[1], $timeslots[0], $timeslots[1]);

    $sql->execute();
    $result = $sql->get_result();

    /* check if there are more than 0 kajaks available */
    $amount = $result->fetch_assoc()["amount"];

    /* if null then no reservation on that day is found; therefore it's free */
    if ($amount === null) {
        return true;
    }

    return (int)$amount + $requested_amount < $amount_kajaks[$kajak];
}

/**
 * Insert reservation into database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $email
 * @param string $phone
 * @param string $date
 * @param array<string> $timeslot
 * @param array<string> $kajaks
 * @return bool|string
 */
function insert_reservation(mysqli|null $conn, string $name, string $email, string $phone, string $date, array $timeslot, array $kajaks): bool|string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return false;
    }

    $reservation_date = date('Y-m-d');
    $address = '';

    try {
        $sql = $conn->prepare("
INSERT INTO reservations (name, email, phone, date, address, reservation_date, from_time, to_time, single_kajak, double_kajak)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
");
        $sql->bind_param('ssssssssss', $name, $email, $phone, $date, $address, $reservation_date, $timeslot[0], $timeslot[1], $kajaks[0], $kajaks[1]);
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return false;
        }

        /* get the id of the reservation */
        $sql = $conn->prepare("SELECT LAST_INSERT_ID() as id");
        $sql->execute();
        $result = $sql->get_result();

        $reservation_id = $result->fetch_assoc()["id"];
        return $reservation_id ?? false;
    } catch (Exception) {
        return false;
    }
}

/**
 * Reservate a kajak.
 *
 * @param mysqli|null $conn
 * @param array $fields
 * @param bool $send_email
 * @return true | string
 */
function reservate_kajak(mysqli|null $conn, array $fields, bool $send_email = false): bool|string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    global $timeslots;
    global $ERROR_RESERVATION, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_SINGLE_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_DOUBLE_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_MAIL_NOT_SENT;

    $name = clean_string($fields["name"]);
    $surname = clean_string($fields["surname"]);
    $fullname = $name . ' ' . $surname;
    $email = clean_string($fields['email']);
    $phone = clean_string($fields['phone']);
    $date = clean_string($fields['date']);

    $timeslot = clean_array($fields['timeslots'] ?? []);

    /* check if timeslot is selected */
    if (empty($timeslot)) {
        return $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED;
    }

    /* prepare timeslot */
    $min_time_index = $timeslot[0];
    $max_time_index = end($timeslot);
    $min_time = $timeslots[$min_time_index][0];
    $max_time = $timeslots[$max_time_index][1];
    $timeslot = array($min_time, $max_time);

    $amount_single_kajak = (int)clean_string($_POST['single_kajak']);
    $amount_double_kajak = (int)clean_string($_POST['double_kajak']);
    $amount_kajaks = array($amount_single_kajak, $amount_double_kajak);

    $single_kajak_available = check_if_kajak_available($conn, $date, $timeslot, "single_kajak", $amount_single_kajak);
    $double_kajak_available = check_if_kajak_available($conn, $date, $timeslot, "double_kajak", $amount_double_kajak);

    if (!$single_kajak_available && !$double_kajak_available) {
        /* check if kajaks are available */
        return $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE;
    }

    if (!$single_kajak_available) {
        /* check if reservation is available for single kajaks */
        return $ERROR_RESERVATION_SINGLE_KAJAK_NOT_AVAILABLE;
    }

    if (!$double_kajak_available) {
        /* check if reservation is available for double kajaks */
        return $ERROR_RESERVATION_DOUBLE_KAJAK_NOT_AVAILABLE;
    }

    if ($amount_single_kajak === 0 && $amount_double_kajak === 0) {
        /* check if any kajak is selected */
        return $ERROR_RESERVATION_KAJAK_NOT_SELECTED;
    }

    /* insert reservation into database and get reservation_id back */
    $reservation_id = insert_reservation($conn, $fullname, $email, $phone, $date, $timeslot, $amount_kajaks);
    if ($reservation_id === false) {
        return $ERROR_RESERVATION;
    }

    if ($send_email) {
        $send_mail_status = send_reservation_email($reservation_id, $name, $email, $amount_kajaks, $timeslot, $date);
        if ($send_mail_status === false) {
            return $ERROR_MAIL_NOT_SENT;
        }
    }
    return true;
}


/**
 * Archive reservations by id.
 * USED BY ADMIN.
 *
 * @param mysqli|null $conn
 * @param array<string> $ids
 * @return void
 */
function archive_reservation(mysqli|null $conn, array $ids)
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return;
    }

    $sql = "UPDATE reservations SET archived = TRUE WHERE id IN (" . implode(',', $ids) . ")";
    $conn->query($sql);
}

/**
 * Cancel reservation by id.
 *
 * @param mysqli|null $conn
 * @param array<string> $fields
 * @param bool $send_email
 * @return string
 */
function cancel_reservation(mysqli|null $conn, array $fields, bool $send_email = false): string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    global $ERROR_CANCELLATION, $ERROR_CANCELLATION_NOT_FOUND, $INFO_CANCELLATION_CANCELED, $ERROR_MAIL_NOT_SENT;

    /* prepare values */
    $reservation_id = clean_string($fields['id']);
    $email = clean_string($fields['email']);

    /* check if reservation exists and is valid */
    $sql = $conn->prepare("SELECT COUNT(*) as amount FROM reservations WHERE id = ? AND email = ? AND cancelled = 0 AND archived = 0");
    $sql->bind_param('ss', $reservation_id, $email);
    $sql->execute();
    $result = $sql->get_result();
    $amount = $result->fetch_assoc()["amount"];

    /* if reservation does not exist it might be already cancelled */
    if ($amount === null || (int)$amount === 0) {
        return $ERROR_CANCELLATION_NOT_FOUND;
    }

    /* cancel reservation */
    $sql = $conn->prepare("UPDATE reservations SET cancelled = TRUE WHERE id = ?");
    $sql->bind_param('s', $reservation_id);
    if ($sql->execute()) {
        if ($send_email) {
            $send_mail_status = send_cancellation_email($reservation_id, $email);
            if ($send_mail_status === false) {
                return $ERROR_MAIL_NOT_SENT;
            }
        }
        return $INFO_CANCELLATION_CANCELED;
    }


    return $ERROR_CANCELLATION;
}
