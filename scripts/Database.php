<?php
/** @noinspection ForgottenDebugOutputInspection */

global $config;
/* kajaks for each kajak type */
$amount_kajaks = $config->getAmountKajaks();
$config_timeslots = $config->getTimeslots(true);

class ReturnValue
{
    public bool $status;
    public string $statusMessage;

    public function __construct($status, $statusMessage)
    {
        $this->status = $status;
        $this->statusMessage = $statusMessage;
    }

    public static function error($statusMessage = 'Aktion fehlgeschlagen'): ReturnValue
    {
        return new ReturnValue(false, $statusMessage);
    }

    public static function success($statusMessage = 'Aktion erfolgreich'): ReturnValue
    {
        return new ReturnValue(true, $statusMessage);
    }

    public function isSuccess(): bool
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->statusMessage;
    }
}

/**
 * Create connection to mysql database.
 * Returns connection object if successful.
 *
 * @return mysqli|null
 */
function connect_to_database(): ?mysqli
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
            error_log($ERROR_DATABASE_CONNECTION);
            return null;
        }
    } catch (Exception $e) {
        error_log($ERROR_DATABASE_CONNECTION);
        return null;
    }

    return $conn;
}

/**
 * Create the table for reservations.
 *
 * @param mysqli|null $conn
 */
function add_reservation_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
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
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Create table for kajaks.
 *
 * @param mysqli|null $conn
 * @return void
 */
function add_kajak_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
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
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Create table for kajak reservations.
 *
 * @param mysqli|null $conn
 * @return void
 */
function add_reservation_kajak_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS kajak_reservation
(
    reservation_id   VARCHAR(60)     NOT NULL,
    kajak_name       VARCHAR(30)     NOT NULL,
    PRIMARY KEY(reservation_id, kajak_name)
)");

    if ($sql === false) {
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Add a kajak to the database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $kind
 * @param int $amount_seats
 * @return void
 */
function add_kajak(?mysqli $conn, string $name, string $kind, int $amount_seats): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_TYPE_NOT_IN_CONFIG, $config;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->getKajakKinds();
    if (!in_array($kind, $kinds, true)) {
        error_log($ERROR_TYPE_NOT_IN_CONFIG);
        return;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare("
        INSERT INTO kajaks (kajak_name, kind, seats)
            VALUES (?, ?, ?);
        ");
        $sql->bind_param('sss', $name, $kind, $amount_seats);
        $sql->execute();
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}

/**
 * Remove kajak from database by name.
 *
 * @param mysqli|null $conn
 * @param string $kajak_name
 * @return void
 */
function remove_kajak(?mysqli $conn, string $kajak_name): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    try {
        $sql = $conn->prepare("DELETE FROM kajaks WHERE kajak_name = ?");
        $sql->bind_param('s', $kajak_name);
        $sql->execute();
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}

/**
 * Get all kajak.
 * @param mysqli|null $conn
 * @return array
 */
function get_kajaks(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM kajaks");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get all kajak kinds.
 * @param mysqli|null $conn
 * @return array
 */
function get_kajaks_kinds(?mysqli $conn): array
{
    return array_values(array_unique(array_map(static fn($kajak) => $kajak['kind'], get_kajaks($conn))));
}

/**
 * Get all reservations from database.
 *
 * @param mysqli|null $conn
 * @return array<string>
 */
function get_reservations(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM reservations WHERE reservation_date >=current_Date() ORDER BY Date;");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception $e) {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get a dictionary of kajaks which are mapped to their reservation id.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_reserved_kajaks_by_id(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM kajak_reservation");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    if ($result === false) {
        return [];
    }
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
 * @param mysqli|null $conn
 * @return void
 */
function drop_all_tables(?mysqli $conn): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("DROP TABLE reservations");
    $sql->execute();
    $sql = $conn->prepare("DROP TABLE kajak_reservation");
    $sql->execute();
    send_mail('', 'Tabellen gelöscht', 'Tabellen gelöscht! Bitte überprüfen, ob diese Aktion gewollt war');
}

/**
 * Returns the amount of kajaks of a kajak type.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array<string> $timeslot
 * @param string $kajak_kind
 * @param int $requested_amount
 * @return array
 */
function get_available_kajaks(?mysqli $conn, string $date, array $timeslot, string $kajak_kind, int $requested_amount): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    /* convert date to DateTime to be able to subtract one second */
    try {
        $timeslot[1] = new DateTime($timeslot[1]);
    } catch (Exception $e) {
        error_log($e);
        return [];
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

    if ($result === false) {
        return [];
    }

    /* fetch all names of available kajaks */
    $fetched_kajaks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    /* if requested amount exceeds the available kajaks then return false */
    if (count($fetched_kajaks) < $requested_amount) {
        return [];
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
 * @return string the reservation id or empty string if something fails.
 */
function insert_reservation(?mysqli $conn, string $name, string $email, string $phone, string $address, string $date, array $timeslot, array $kajak_names, int $price): string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return '';
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
            return '';
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
                return '';
            }
        }

        return $reservation_id;
    } catch (Exception $e) {
        error_log($e);
        return '';
    }
}

/**
 * Reservate a kajak.
 *
 * @param mysqli|null $conn
 * @param array $fields
 * @param bool $send_email
 * @return ReturnValue
 */
function reservate_kajak(?mysqli $conn, array $fields, bool $send_email = false): ReturnValue
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        ReturnValue::error($ERROR_DATABASE_CONNECTION);
    }

    global $INFO_RESERVATION_SUCCESS, $ERROR_RESERVATION, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;

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
    if (count($timeslots) === 0) {
        return ReturnValue::error($ERROR_RESERVATION_TIMESLOT_NOT_SELECTED);
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
        ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_SELECTED);
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
        if (count($available_kajaks) === 0) {
            ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_AVAILABLE);
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
    $kajak_names = array_map(static fn($available_kajak) => $available_kajak["kajak_name"], $reserved_kajaks);
    $reservation_id = insert_reservation($conn, $fullname, $email, $phone, $address, $date, $timeslots, $kajak_names, $price);
    if ($reservation_id === '') {
        return ReturnValue::error($ERROR_RESERVATION);
    }

    /* send email */
    if ($send_email) {
        $send_mail_status = send_reservation_email($reservation_id, $name, $email, $reserved_kajaks, $timeslots, $date, $price);
        if ($send_mail_status === false) {
            return ReturnValue::error($ERROR_SUCCESS_BUT_MAIL_NOT_SENT);
        }
    }
    return ReturnValue::success($INFO_RESERVATION_SUCCESS);
}

/**
 * Archive reservations by id.
 * USED BY ADMIN.
 *
 * @param mysqli|null $conn
 * @param array<string> $ids
 * @return void
 */
function archive_reservation(?mysqli $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
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
function cancel_reservation(?mysqli $conn, array $fields, bool $send_email = false): string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
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
                error_log($ERROR_MAIL_NOT_SENT);
                return $ERROR_MAIL_NOT_SENT;
            }
        }
        return $INFO_CANCELLATION_CANCELED;
    }

    error_log($ERROR_CANCELLATION);
    return $ERROR_CANCELLATION;
}

function database_is_connected()
{
    return false;
}