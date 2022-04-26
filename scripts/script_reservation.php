<?php
global $config;
/* kajaks for each kajak type */
$amount_kajaks = $config->getAmountKajaks();
$config_timeslots = $config->getTimeslots(true);

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
 * Create the table for reservations.
 *
 * @param mysqli|null $conn
 * @return string|bool
 */
function add_reservation_table(mysqli|null $conn): string|bool
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS reservations
(
    reservation_id   VARCHAR(60)     NOT NULL PRIMARY KEY,
    name             VARCHAR(30)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    phone            VARCHAR(20)     NOT NULL,
    address          VARCHAR(200)    NOT NULL,
    date             DATE            NOT NULL,
    reservation_date DATE            NOT NULL,
    from_time        TIME            NOT NULL,
    to_time          TIME            NOT NULL,    
    price            NUMERIC         NOT NULL DEFAULT 0,
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
 * Create table for kajaks.
 *
 * @param mysqli|null $conn
 * @return string|bool
 */
function add_kajak_table(mysqli|null $conn): string|bool
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS kajaks
(
    kajak_name       VARCHAR(30)     NOT NULL PRIMARY KEY,
    kind             VARCHAR(30)     NOT NULL,
    seats            INT             NOT NULL DEFAULT 0,
    available        BOOLEAN         NOT NULL DEFAULT TRUE,
    comment          VARCHAR(200)    NOT NULL DEFAULT ''
)");

    if ($sql === false) {
        return $ERROR_DATABASE_QUERY;
    }

    return $sql->execute();
}

/**
 * Create table for kajak reservations.
 *
 * @param mysqli|null $conn
 * @return string|bool
 */
function add_reservation_kajak_table(mysqli|null $conn): string|bool
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS kajak_reservation
(
    reservation_id   VARCHAR(60)     NOT NULL,
    kajak_name       VARCHAR(30)     NOT NULL,
    PRIMARY KEY(reservation_id, kajak_name)
)");

    if ($sql === false) {
        return $ERROR_DATABASE_QUERY;
    }

    return $sql->execute();
}

/**
 * Add a kajak to the database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $kind
 * @param int $amount_seats
 * @return string|bool
 */
function add_kajak(mysqli|null $conn, string $name, string $kind, int $amount_seats): string|bool
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_TYPE_NOT_IN_CONFIG, $config;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->getKajakKinds();
    if (!in_array($kind, $kinds, true)) {
        return $ERROR_TYPE_NOT_IN_CONFIG;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare("
        INSERT INTO kajaks (kajak_name, kind, seats)
            VALUES (?, ?, ?);
        ");
        $sql->bind_param('sss', $name, $kind, $amount_seats);
        $result_execute = $sql->execute();
        return $result_execute !== false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove kajak from database by name.
 *
 * @param mysqli|null $conn
 * @param string $kajak_name
 * @return bool
 */
function remove_kajak(mysqli|null $conn, string $kajak_name): bool
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    try {
        $sql = $conn->prepare("DELETE FROM kajaks WHERE kajak_name = ?");
        $sql->bind_param('s', $kajak_name);
        return $sql->execute();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get all kajak.
 * @param mysqli|null $conn
 * @return array|bool
 */
function get_kajaks(mysqli|null $conn): array|bool
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        return $ERROR_DATABASE_CONNECTION;
    }

    try {
        $sql = $conn->prepare("SELECT * FROM kajaks");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception) {
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get all kajak kinds.
 * @param mysqli|null $conn
 * @return array|bool
 */
function get_kajaks_kinds(mysqli|null $conn): array|bool
{
    return array_values(array_unique(array_map(static fn($kajak) => $kajak['kind'], get_kajaks($conn))));
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

    try {
        $sql = $conn->prepare("SELECT * FROM reservations WHERE reservation_date >=current_Date() ORDER BY Date;");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception) {
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function get_reserved_kajaks_by_id(mysqli|null $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM kajak_reservation");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception) {
        return [];
    }

    $result = $sql->get_result();
    $kajak_reservation_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $kajaks_by_reservation_id = array();
    foreach ($kajak_reservation_list as $item) {
        $kajak_name = $item["kajak_name"];
        $reservation_id = $item["reservation_id"];
        if (!array_key_exists($reservation_id, $kajaks_by_reservation_id)) {
            $kajaks_by_reservation_id[$reservation_id] = array();
        }
        $kajaks_by_reservation_id[$reservation_id][] = $kajak_name;
    }
    return $kajaks_by_reservation_id;
}

/**
 * USE WITH CAUTION!
 * USED BY ADMIN.
 *
 * Drops all tables.
 *
 * @param mysqli $conn
 * @return void
 */
function drop_all_tables(mysqli $conn): void
{
    $sql = $conn->prepare("DROP TABLE reservations");
    $sql->execute();
    $sql = $conn->prepare("DROP TABLE kajak_reservation");
    $sql->execute();
}

/**
 * Returns the amount of kajaks of a kajak type.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array<string> $timeslot
 * @param string $kajak_kind
 * @param int $requested_amount
 * @return bool|array
 */
function get_available_kajaks(mysqli|null $conn, string $date, array $timeslot, string $kajak_kind, int $requested_amount): bool|array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return false;
    }

    /* if 0 kajaks where requested, they are available */
    if ($requested_amount === 0) {
        return true;
    }

    /* convert date to DateTime to be able to subtract one second */
    try {
        $timeslot[1] = new DateTime($timeslot[1]);
    } catch (Exception) {
        return false;
    }

    /* it is important to exclude the current time from the next timeslot */
    $timeslot[1]->modify("-1 second");
    $timeslot[1] = $timeslot[1]->format("H:i:s");

    $timeslots = array((string)$timeslot[0], $timeslot[1]);

    /* select all the kajak names of a type that are available in the requested timeslot */
    $sql = $conn->prepare("
        SELECT kajak_name, seats
FROM kajaks
WHERE kajak_name NOT IN (SELECT kajak_reservation.kajak_name
                         FROM kajak_reservation
                                  INNER JOIN reservations
                                             ON reservations.reservation_id = kajak_reservation.reservation_id
                         WHERE reservations.date = ?
                           AND reservations.cancelled = '0'
                           AND reservations.archived = '0'
                           AND (reservations.from_time BETWEEN ? AND ?
                             OR reservations.to_time BETWEEN ? AND ?))
  AND kajaks.kind = ? AND kajaks.available = 1");
    $sql->bind_param('ssssss', $date, $timeslots[0], $timeslots[1], $timeslots[0], $timeslots[1], $kajak_kind);

    $sql->execute();
    $result = $sql->get_result();

    /* fetch all names of available kajaks */
    $fetched_kajaks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    /* if requested amount exceeds the available kajaks then return false */
    if (count($fetched_kajaks) < $requested_amount) {
        return false;
    }

    /* return so much names as the user requested */
    return array_slice($fetched_kajaks, 0, $requested_amount);
}

/**
 * Insert reservation into database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $email
 * @param string $phone
 * @param string $address
 * @param string $date
 * @param array<string> $timeslot
 * @param array<string> $kajak_names
 * @param int $price
 * @return bool|string
 */
function insert_reservation(mysqli|null $conn, string $name, string $email, string $phone, string $address, string $date, array $timeslot, array $kajak_names, int $price): bool|string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return false;
    }

    $reservation_date = date('Y-m-d');
    $reservation_id = uniqid('', true);

    try {
        $sql = $conn->prepare("
INSERT INTO reservations (reservation_id, name, email, phone, date, address, reservation_date, from_time, to_time, price)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? ,?);
");
        $sql->bind_param('ssssssssss', $reservation_id, $name, $email, $phone, $date, $address, $reservation_date, $timeslot[0], $timeslot[1], $price);
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return false;
        }

        /* assign each kajak the reservation id */
        foreach ($kajak_names as $kajak_name) {
            $sql = $conn->prepare("
INSERT INTO kajak_reservation (kajak_name, reservation_id)
    VALUES (?, ?);
");
            $sql->bind_param('ss', $kajak_name, $reservation_id);
            $result_execute = $sql->execute();
            if ($result_execute === false) {
                return false;
            }
        }

        return $reservation_id;
    } catch (Exception $e) {
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

    global $ERROR_RESERVATION, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;

    $name = clean_string($fields["name"]);
    $fullname = $name . ' ' . clean_string($fields["surname"]);
    $email = clean_string($fields['email']);
    $phone = clean_string($fields['phone']);
    $address = clean_string($fields['street'] . ' ' . $fields['plz'] . ', ' . $fields['city'] . ', ' . $fields['country']);
    $date = clean_string($fields['date']);

    /****** prepare timeslots ******/
    $timeslots = clean_array($fields['timeslots'] ?? []);
    $amount_timeslots = count($timeslots);

    /* check if timeslot is selected */
    if (empty($timeslots)) {
        return $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED;
    }

    global $config_timeslots;
    $min_time_index = $timeslots[0];
    $max_time_index = end($timeslots);
    $min_time = $config_timeslots[$min_time_index][0];
    $max_time = $config_timeslots[$max_time_index][1];
    $timeslots = array($min_time, $max_time);

    /* get all kajak kinds */
    $kajak_kinds = get_kajaks_kinds($conn);

    /* check if more than 0 kajaks where selected */
    $amount_kajaks = array_map(static function ($kajak_kind) {
        if (!isset($_POST[$kajak_kind])) {
            return 0;
        }
        return (int)clean_string($_POST[$kajak_kind]);
    }, $kajak_kinds);
    $sum_kajaks = array_sum($amount_kajaks);

    /* throw error if no kajak was selected */
    if ($sum_kajaks === 0) {
        return $ERROR_RESERVATION_KAJAK_NOT_SELECTED;
    }

    /* check for each kind, if it is available */
    $reserved_kajaks = array();
    foreach ($kajak_kinds as $kajak_kind) {
        $requested_amount = !isset($_POST[$kajak_kind]) ? 0 : (int)clean_string($_POST[$kajak_kind]);
        /* skip 0 requested kajaks */
        if ($requested_amount === 0) {
            break;
        }
        $available_kajaks = get_available_kajaks($conn, $date, $timeslots, $kajak_kind, $requested_amount);
        if ($available_kajaks === false) {
            return $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE;
        }

        /* skip 0 requested kajaks */
        if ($available_kajaks === true) {
            break;
        }

        /* this will be a matrix which is then flattened */
        $reserved_kajaks[] = $available_kajaks;
    }

    /* flatten array */
    $reserved_kajaks = array_merge(...$reserved_kajaks);

    /* calculate price */
    global $config;
    $price = $config->calculatePrice($amount_timeslots, $sum_kajaks);

    /* insert reservation into database and get reservation_id back */
    $kajak_names = array_map(static function ($available_kajak) {
        return $available_kajak["kajak_name"];
    }, $reserved_kajaks);
    $reservation_id = insert_reservation($conn, $fullname, $email, $phone, $address, $date, $timeslots, $kajak_names, $price);
    if ($reservation_id === false) {
        return $ERROR_RESERVATION;
    }

    /* send email */
    if ($send_email) {
        $send_mail_status = send_reservation_email($reservation_id, $name, $email, $reserved_kajaks, $timeslots, $date, $price);
        if ($send_mail_status === false) {
            return $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;
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
function archive_reservation(mysqli|null $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        echo $ERROR_DATABASE_CONNECTION;
        return;
    }

    /* concat all strings in array to one string */
    $ids_as_string = implode(',', $ids);
    $sql = $conn->prepare("UPDATE reservations SET archived = TRUE WHERE find_in_set(reservation_id, ?)");
    $sql->bind_param("s", $ids_as_string);
    $sql->execute();
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
    $sql = $conn->prepare("SELECT COUNT(*) as amount FROM reservations WHERE reservation_id = ? AND email = ? AND cancelled = 0 AND archived = 0");
    $sql->bind_param('ss', $reservation_id, $email);
    $sql->execute();
    $result = $sql->get_result();
    $amount = $result->fetch_assoc()["amount"];

    /* if reservation does not exist it might be already cancelled */
    if ($amount === null || (int)$amount === 0) {
        return $ERROR_CANCELLATION_NOT_FOUND;
    }

    /* cancel reservation */
    $sql = $conn->prepare("UPDATE reservations SET cancelled = TRUE WHERE reservation_id = ?");
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